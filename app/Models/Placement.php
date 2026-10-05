<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Placement extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'mentor_id',
        'academic_advisor_id',
        'pembimbing_id',
        'certificate_number',
        'certificate_hash',
    ];

    /**
     * Hash acak untuk QR verifikasi sertifikat (/verify-certificate/{hash}).
     * Dibuat otomatis bila masih kosong, agar QR tidak pernah berisi ID angka yang mudah ditebak.
     */
    public function ensureCertificateHash(): string
    {
        if (empty($this->certificate_hash)) {
            // Atomic: hanya mengisi jika masih kosong, supaya dua cetak bersamaan
            // tidak saling menimpa hash (QR yang sudah tercetak tetap valid).
            static::whereKey($this->getKey())
                ->where(fn ($q) => $q->whereNull('certificate_hash')->orWhere('certificate_hash', ''))
                ->update(['certificate_hash' => Str::random(32)]);

            $this->certificate_hash = static::whereKey($this->getKey())->value('certificate_hash');
            $this->syncOriginalAttribute('certificate_hash');
        }

        return $this->certificate_hash;
    }

    /**
     * Kode verifikasi sertifikat yang mudah dibaca & dicocokkan manual,
     * mis. "SXGZ-95Jf-b0sc-csj7-GQKJ-OwGH-3pZo-VaID" (huruf besar/kecil tetap sama dengan hash asli).
     */
    public function getVerificationCodeAttribute(): string
    {
        return implode('-', str_split((string) $this->certificate_hash, 4));
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Apakah penempatan sudah memiliki mentor lapangan teknis (mentor_id / pembimbing_id legacy).
     */
    public function hasFieldMentor(): bool
    {
        return ! empty($this->mentor_id) || ! empty($this->pembimbing_id);
    }

    /**
     * Apakah penempatan sudah memiliki Dosen Pembimbing Lapangan (DPL kampus).
     */
    public function hasAcademicAdvisor(): bool
    {
        return ! empty($this->academic_advisor_id);
    }

    /**
     * ID instansi (agency_profile_id) tempat mahasiswa ditempatkan, via unit pada pengajuan.
     */
    public function agencyProfileId(): ?int
    {
        $agencyId = $this->application?->unit?->agency_profile_id;

        return $agencyId !== null ? (int) $agencyId : null;
    }

    /**
     * Fallback verifikator logbook: selama mahasiswa aktif belum memiliki mentor teknis
     * (baru terikat di level unit/instansi), Admin Dinas instansi terkait — atau Super Admin —
     * boleh sementara melihat & memvalidasi logbooknya. Setelah mentor ditetapkan, hak validasi
     * kembali sepenuhnya ke mentor.
     */
    public function allowsAgencyAdminLogbookFallback(?User $user): bool
    {
        if (! $user || $this->hasFieldMentor()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->role === 'admin'
            && $user->agency_profile_id !== null
            && (int) $user->agency_profile_id === $this->agencyProfileId();
    }

    /**
     * Apakah user adalah mentor teknis yang ditugaskan pada penempatan ini.
     */
    public function isAssignedFieldMentor(?User $user): bool
    {
        return $user !== null
            && ((int) $this->mentor_id === (int) $user->id || (int) $this->pembimbing_id === (int) $user->id)
            && ! empty($user->id);
    }

    /**
     * Apakah user adalah Kepala / Koordinator Unit tempat mahasiswa ditempatkan.
     */
    public function isUnitHead(?User $user): bool
    {
        $headId = $this->application?->unit?->head_user_id;

        return $user !== null && $headId !== null && (int) $headId === (int) $user->id;
    }

    /**
     * Siapa yang boleh meninjau & memvalidasi logbook dari sisi instansi (kolom status/feedback):
     *  - mentor teknis yang ditugaskan; ATAU
     *  - selama mentor belum ditunjuk (fallback): Admin Dinas instansi terkait, Super Admin,
     *    atau Kepala Unit tempat mahasiswa ditempatkan.
     */
    public function canFieldReviewLogbook(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->hasFieldMentor()) {
            return $this->isAssignedFieldMentor($user);
        }

        return $this->allowsAgencyAdminLogbookFallback($user) || $this->isUnitHead($user);
    }

    // Relasi ke User (Pembimbing Lapangan Dinas / Mentor)
    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    // Relasi ke User (Dosen Pembimbing Kampus / Academic Advisor)
    public function academicAdvisor()
    {
        return $this->belongsTo(User::class, 'academic_advisor_id');
    }

    public function dosen()
    {
        return $this->belongsTo(User::class, 'academic_advisor_id');
    }

    // Relasi ke User (Pembimbing / Mentor - Legacy)
    public function pembimbing()
    {
        return $this->belongsTo(User::class, 'pembimbing_id');
    }

    // Tambahkan relasi Logbooks
    public function logbooks()
    {
        return $this->hasMany(Logbook::class, 'placement_id');
    }

    public function finalreport()
    {
        return $this->hasOne(FinalReport::class);
    }

    public function evaluation()
    {
        return $this->hasOne(Evaluation::class);
    }

    public function unit()
    {
        return $this->hasOneThrough(Unit::class, Application::class, 'id', 'id', 'application_id', 'unit_id');
    }

    // Accessor untuk mendapatkan AgencyProfile dari unit penempatan
    public function getAgencyProfileAttribute()
    {
        return $this->application?->unit?->agencyProfile ?? AgencyProfile::first();
    }

    /**
     * Evaluasi dan perbarui status kelulusan magang (COMPLETED) secara otomatis.
     * Syarat: Naskah laporan akhir disetujui (ACC) DAN lembar evaluasi lengkap sesuai skema kampus.
     */
    public function syncCompletionStatus(): bool
    {
        $app = $this->application;
        $finalReport = $this->finalreport;
        $eval = $this->evaluation;

        if (! $app || ! $finalReport || strtolower($finalReport->status ?? '') !== 'approved') {
            return false;
        }

        if ($eval && $eval->is_complete) {
            $rawStatus = $app->status instanceof ApplicationStatus ? $app->status->value : strtolower((string) $app->status);
            if ($rawStatus !== 'completed' && ! in_array($rawStatus, ['resigned', 'rejected'])) {
                $app->update(['status' => ApplicationStatus::COMPLETED]);

                AuditLog::record('AUTO_COMPLETE_INTERNSHIP', 'Application', $app->id, [
                    'student_name' => $app->user?->name,
                    'reason' => 'Laporan akhir disetujui dan nilai evaluasi lengkap.',
                ]);
            }

            return true;
        }

        return false;
    }
}
