<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
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
        'advisor_notes',
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

    /**
     * Scope penempatan yang menjadi wewenang verifikasi lapangan seorang mentor:
     *  - mahasiswa bimbingan langsung (mentor_id / pembimbing_id), dan
     *  - FALLBACK: mahasiswa ACTIVE di unit yang ia pimpin (Kepala Unit) yang belum memiliki mentor teknis.
     * Selalu dibatasi ke instansi mentor bila mentor terikat ke instansi tertentu.
     */
    public function scopeFieldReviewableBy($query, User $user)
    {
        $query->where(function ($q) use ($user) {
            $q->where(function ($own) use ($user) {
                $own->where('mentor_id', $user->id)
                    ->orWhere('pembimbing_id', $user->id);
            })->orWhere(function ($fallback) use ($user) {
                $fallback->whereNull('mentor_id')
                    ->whereNull('pembimbing_id')
                    ->whereHas('application', function ($aq) use ($user) {
                        $aq->where('status', ApplicationStatus::ACTIVE->value)
                            ->whereHas('unit', fn ($uq) => $uq->where('head_user_id', $user->id));
                    });
            });
        });

        if ($user->agency_profile_id !== null) {
            $query->whereHas('application.unit', fn ($uq) => $uq->where('agency_profile_id', $user->agency_profile_id));
        }

        return $query;
    }

    /**
     * Alasan form penilaian (Mentor maupun DPL) dikunci, atau null bila boleh diisi/diubah.
     *  - Belum berjalan: nilai baru bisa diisi saat magang aktif (atau tanggal mulai sudah lewat
     *    tetapi status belum sempat disinkronkan scheduler).
     *  - Sudah selesai (COMPLETED): sertifikat sudah dapat diunduh mahasiswa, jadi nilai dikunci
     *    agar isi sertifikat tidak berbeda dengan data di sistem.
     */
    public function evaluationLockReason(): ?string
    {
        $app = $this->application;
        if (! $app) {
            return 'Data pengajuan magang tidak ditemukan.';
        }

        $status = $app->status instanceof ApplicationStatus ? $app->status : ApplicationStatus::tryFrom(strtolower((string) $app->status));

        if ($status === ApplicationStatus::COMPLETED) {
            return 'Nilai sudah dikunci karena magang telah dinyatakan selesai dan sertifikat sudah dapat diunduh mahasiswa. Hubungi Admin Dinas bila perlu koreksi nilai.';
        }

        if ($status === ApplicationStatus::ACTIVE) {
            return null;
        }

        if ($status === ApplicationStatus::ACCEPTED && $app->start_date && Carbon::parse($app->start_date)->startOfDay()->lte(now())) {
            return null;
        }

        return 'Penilaian baru dapat diisi setelah magang mahasiswa berjalan (status Aktif).';
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

    public function academicConsultations()
    {
        return $this->hasMany(AcademicConsultation::class, 'placement_id')->orderBy('consultation_date', 'desc');
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
     * Cek apakah mahasiswa telah mengisi minimal satu catatan logbook magang
     */
    public function getHasFilledLogbookAttribute(): bool
    {
        if (isset($this->attributes['logbooks_count'])) {
            return (int) $this->attributes['logbooks_count'] > 0;
        }

        if ($this->relationLoaded('logbooks')) {
            return $this->logbooks->isNotEmpty();
        }

        return $this->logbooks()->exists();
    }

    /**
     * Evaluasi dan perbarui status kelulusan magang (COMPLETED) secara otomatis.
     * Syarat: Naskah laporan akhir disetujui (ACC), lembar evaluasi lengkap sesuai skema kampus,
     * DAN mahasiswa wajib telah mengisi logbook aktivitas magang.
     */
    public function syncCompletionStatus(): bool
    {
        $app = $this->application;
        $finalReport = $this->finalreport;
        $eval = $this->evaluation;

        if (! $app || ! $finalReport || strtolower($finalReport->status ?? '') !== 'approved') {
            return false;
        }

        // Mahasiswa yang tidak mengisi logbook tidak bisa lulus magang
        if (! $this->has_filled_logbook) {
            return false;
        }

        if ($eval && $eval->is_complete) {
            $rawStatus = $app->status instanceof ApplicationStatus ? $app->status->value : strtolower((string) $app->status);
            if ($rawStatus !== 'completed' && ! in_array($rawStatus, ['resigned', 'rejected'])) {
                $app->update(['status' => ApplicationStatus::COMPLETED]);

                AuditLog::record('AUTO_COMPLETE_INTERNSHIP', 'Application', $app->id, [
                    'student_name' => $app->user?->name,
                    'reason' => 'Laporan akhir disetujui, nilai evaluasi lengkap, dan logbook terisi.',
                ]);
            }

            return true;
        }

        return false;
    }

    /**
     * Pengecekan kelulusan magang:
     * 1. Status wajib ACTIVE.
     * 2. Tanggal sekarang sudah melewati atau sama dengan tanggal selesai magang (now() >= end_date).
     * 3. Syarat administratif (Nilai Mentor Lapangan, Laporan Akhir, Logbook) lengkap.
     */
    public function canBeCompleted(): bool
    {
        return $this->application ? $this->application->canBeCompleted() : false;
    }

    public function isReadyForCompletion(): bool
    {
        return $this->canBeCompleted();
    }
}
