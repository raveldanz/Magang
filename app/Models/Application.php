<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Application extends Model
{
    protected $guarded = ['id'];

    /**
     * Token acak untuk QR verifikasi surat balasan (/verify-letter/{token}).
     * Dibuat otomatis bila masih kosong, agar QR tidak pernah berisi ID angka yang mudah ditebak.
     */
    public function ensureLetterToken(): string
    {
        if (empty($this->letter_token)) {
            // Atomic: hanya mengisi jika masih kosong, supaya dua cetak bersamaan
            // tidak saling menimpa token (QR yang sudah tercetak tetap valid).
            static::whereKey($this->getKey())
                ->where(fn ($q) => $q->whereNull('letter_token')->orWhere('letter_token', ''))
                ->update(['letter_token' => Str::random(32)]);

            $this->letter_token = static::whereKey($this->getKey())->value('letter_token');
            $this->syncOriginalAttribute('letter_token');
        }

        return $this->letter_token;
    }

    protected $casts = [
        'status' => ApplicationStatus::class,
    ];

    protected $appends = [
        'is_active_internship',
        'is_eligible_for_logbook',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function studentProfile()
    {
        return $this->hasOneThrough(StudentProfile::class, User::class, 'id', 'user_id', 'user_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function documents()
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function placement()
    {
        return $this->hasOne(Placement::class);
    }

    public function getAgencyProfileAttribute()
    {
        return $this->unit?->agencyProfile ?? AgencyProfile::first();
    }

    public function getRejectionReasonAttribute()
    {
        return $this->attributes['rejection_reason'] ?? $this->attributes['rejection_note'] ?? null;
    }

    /**
     * Backward-compatible accessor untuk lifecycle status
     * Menghilangkan virtual calculation di RAM dan merefleksikan status fisik database secara type-safe
     */
    public function getLifecycleStatusAttribute(): string
    {
        if ($this->status instanceof ApplicationStatus) {
            return strtoupper($this->status->value);
        }

        return strtoupper((string) ($this->status ?? 'PENDING'));
    }

    public function getIsActiveInternshipAttribute(): bool
    {
        return $this->status === ApplicationStatus::ACTIVE || $this->status === 'active';
    }

    public function getIsEligibleForLogbookAttribute(): bool
    {
        return $this->is_active_internship;
    }

    public function getHasApprovedReportAttribute(): bool
    {
        $placement = $this->placement;

        return (bool) ($placement && $placement->finalreport && strtolower($placement->finalreport->status ?? '') === 'approved');
    }

    public function getHasCompleteEvaluationAttribute(): bool
    {
        $eval = $this->placement?->evaluation;
        if (! $eval) {
            return false;
        }

        if ((float) ($eval->final_score ?? 0) > 0) {
            return true;
        }

        $hasMentor = ($eval->nilai_disiplin > 0 && $eval->nilai_kinerja > 0 && $eval->nilai_laporan > 0);
        $hasDosen = ($eval->nilai_dosen > 0 || $eval->nilai_akademik > 0 || ($eval->score_mastery > 0 && $eval->score_report > 0 && $eval->score_attitude > 0));

        $univ = $eval->getUniversity();
        $scheme = $univ->evaluation_scheme ?? 'dual_evaluation';
        if ($scheme === 'mentor_only') {
            return $hasMentor;
        }

        return ($hasMentor && $hasDosen) || $eval->is_complete;
    }

    /**
     * Magang berstatus ACTIVE yang tanggal selesainya sudah lewat namun belum dinyatakan lulus
     * (laporan akhir / nilai belum lengkap). Status tetap ACTIVE agar mahasiswa masih bisa melengkapi.
     */
    public function isPastEndDate(): bool
    {
        return $this->statusValue() === ApplicationStatus::ACTIVE->value
            && $this->end_date
            && \Illuminate\Support\Carbon::parse($this->end_date)->endOfDay()->isPast();
    }

    /** Jumlah hari sejak tanggal selesai magang (0 bila belum lewat). */
    public function daysPastEndDate(): int
    {
        if (! $this->isPastEndDate()) {
            return 0;
        }

        return (int) \Illuminate\Support\Carbon::parse($this->end_date)->startOfDay()->diffInDays(\Illuminate\Support\Carbon::today());
    }

    /**
     * Certificate Gate: daftar syarat penerbitan E-Sertifikat yang BELUM terpenuhi (kosong = boleh terbit).
     *  1. Evaluasi kinerja Mentor Lapangan lengkap (nilai disiplin, kinerja, laporan terisi).
     *  2. Laporan akhir mahasiswa disetujui (status approved).
     *  3. Mahasiswa telah mengisi logbook aktivitas magang.
     *  4. Status magang sudah dinyatakan lulus (COMPLETED).
     *
     * @return array<int, string>
     */
    public function certificateBlockers(): array
    {
        $placement = $this->placement;
        $eval = $placement?->evaluation;
        $blockers = [];

        $mentorEvalComplete = $eval
            && (float) ($eval->nilai_disiplin ?? 0) > 0
            && (float) ($eval->nilai_kinerja ?? 0) > 0
            && (float) ($eval->nilai_laporan ?? 0) > 0;

        if (! $mentorEvalComplete) {
            $blockers[] = 'Nilai evaluasi kinerja dari Mentor Lapangan (dinas) belum lengkap.';
        }

        if (! $this->has_approved_report) {
            $blockers[] = 'Laporan akhir magang belum disetujui (ACC).';
        }

        if (! $this->has_filled_logbook) {
            $blockers[] = 'Logbook aktivitas magang belum pernah diisi.';
        }

        if ($this->statusValue() !== ApplicationStatus::COMPLETED->value) {
            $blockers[] = 'Status magang belum dinyatakan lulus / selesai.';
        }

        return $blockers;
    }

    public function isCertificateEligible(): bool
    {
        return $this->certificateBlockers() === [];
    }

    public function getHasFilledLogbookAttribute(): bool
    {
        $placement = $this->placement;
        if (! $placement) {
            return false;
        }

        if (isset($placement->attributes['logbooks_count'])) {
            return (int) $placement->attributes['logbooks_count'] > 0;
        }

        if ($placement->relationLoaded('logbooks')) {
            return $placement->logbooks->isNotEmpty();
        }

        return $placement->logbooks()->exists();
    }

    /**
     * Memvalidasi apakah pengajuan memenuhi syarat kelulusan:
     * 1. Status wajib ACTIVE.
     * 2. Tanggal sekarang sudah melewati atau sama dengan tanggal selesai magang (now() >= end_date).
     * 3. Syarat administratif (Nilai Mentor Lapangan, Laporan Akhir ACC, & Logbook) terpenuhi.
     */
    public function canBeCompleted(): bool
    {
        $rawStatus = $this->statusValue();

        if ($rawStatus !== ApplicationStatus::ACTIVE->value) {
            return false;
        }

        if ($this->end_date && Carbon::parse($this->end_date)->startOfDay()->isFuture()) {
            return false;
        }

        return $this->has_approved_report && $this->has_complete_evaluation && $this->has_filled_logbook;
    }

    public function isReadyForCompletion(): bool
    {
        return $this->canBeCompleted();
    }

    public function getCanCompleteAttribute(): bool
    {
        $rawStatus = $this->statusValue();

        if ($rawStatus === ApplicationStatus::COMPLETED->value) {
            return true;
        }

        if ($rawStatus !== ApplicationStatus::ACTIVE->value) {
            return false;
        }

        return $this->has_approved_report && $this->has_complete_evaluation && $this->has_filled_logbook;
    }

    /** Status pengajuan yang menempati kuota divisi */
    public const QUOTA_STATUSES = ['accepted', 'active'];

    /**
     * Pengajuan yang sedang menempati kuota divisi: diterima/aktif dan masa magangnya belum berakhir.
     */
    public function scopeOccupyingQuota($query)
    {
        $today = now()->toDateString();

        return $query->whereIn('status', self::QUOTA_STATUSES)
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $today));
    }

    /**
     * Versi in-memory dari scopeOccupyingQuota (untuk koleksi yang sudah dimuat).
     */
    public function occupiesQuota(): bool
    {
        return in_array($this->statusValue(), self::QUOTA_STATUSES, true)
            && (empty($this->end_date) || substr((string) $this->end_date, 0, 10) >= now()->toDateString());
    }

    /**
     * Status mentah (string), apa pun bentuk simpanannya (enum atau string lama).
     */
    public function statusValue(): string
    {
        return $this->status instanceof ApplicationStatus
            ? $this->status->value
            : strtolower((string) ($this->status ?? ApplicationStatus::PENDING->value));
    }

    // ------------------------------------------------------------------
    // STANDAR PRIORITAS TINDAKAN ADMIN (dipakai halaman Pengajuan, pusat kendali dinas & kampus)
    //   1 Perlu verifikasi / keputusan (pending, verified)
    //   2 Siap diluluskan (diterima/aktif + laporan disetujui + nilai lengkap)
    //   3 Pembimbing belum ditetapkan (mentor, atau DPL bila kampus mewajibkan)
    //   4 Magang aktif   5 Diterima (menunggu mulai)   6 Selesai
    //   7 Lainnya / belum mengajukan   8 Ditolak / mengundurkan diri
    // Tingkat 1–3 = perlu tindakan: diurutkan antrean (paling lama menunggu di atas);
    // tingkat lain: terbaru di atas. Versi SQL (actionPrioritySql) wajib identik dengan
    // actionPriority() — dijaga oleh tests/Feature/ActionPriorityDisplayTest.
    // ------------------------------------------------------------------

    /** Batas atas tingkat prioritas yang dianggap "perlu tindakan" */
    public const ACTION_THRESHOLD = 3;

    /**
     * Prioritas tindakan admin (angka kecil = lebih mendesak).
     *
     * @param  bool|null  $requireAdvisor  DPL wajib? null = ikuti kebijakan kampus mahasiswa (universities.require_dpl).
     */
    public function actionPriority(?bool $requireAdvisor = null): int
    {
        $status = $this->statusValue();
        $ongoing = in_array($status, ['accepted', 'active'], true);

        return match (true) {
            in_array($status, ['pending', 'verified'], true) => 1,
            $status === 'active' && $this->can_complete => 2,
            $ongoing && $this->missingSupervisor($requireAdvisor ?? $this->requiresAdvisor()) !== null => 3,
            $status === 'active' => 4,
            $status === 'accepted' => 5,
            $status === 'completed' => 6,
            in_array($status, ['rejected', 'resigned'], true) => 8,
            default => 7,
        };
    }

    public function needsAction(?bool $requireAdvisor = null): bool
    {
        return $this->actionPriority($requireAdvisor) <= self::ACTION_THRESHOLD;
    }

    /**
     * Keterangan tindakan yang TIDAK terbaca dari badge status (null jika tidak ada).
     * Tingkat 1 (PENDING/VERIFIED) sengaja tanpa keterangan: badge-nya sudah menyatakan tindakannya.
     */
    public function actionHint(?bool $requireAdvisor = null): ?string
    {
        $requireAdvisor ??= $this->requiresAdvisor();

        return match ($this->actionPriority($requireAdvisor)) {
            2 => 'Siap diluluskan',
            3 => $this->missingSupervisor($requireAdvisor) === 'mentor' ? 'Mentor belum ditetapkan' : 'Dosen pembimbing belum ditetapkan',
            default => null,
        };
    }

    /**
     * Kunci urut standar untuk koleksi di memori (padanan scopeOrderByActionPriority):
     * prioritas naik; tingkat perlu tindakan = terlama dulu, tingkat lain = terbaru dulu.
     */
    public static function actionSortKey(int $priority, $createdAt, int $id = 0): array
    {
        $ts = $createdAt ? Carbon::parse($createdAt)->getTimestamp() : 0;

        return [$priority, $priority <= self::ACTION_THRESHOLD ? $ts : -$ts, -$id];
    }

    /**
     * DPL wajib menurut kebijakan kampus mahasiswa (default wajib bila kampus tak diketahui).
     * Kampus di-resolve lewat users.university_id — sama dengan versi SQL.
     */
    public function requiresAdvisor(): bool
    {
        $univ = $this->user?->universityRelation;

        return $univ ? $univ->requiresAdvisor() : true;
    }

    /**
     * Pembimbing yang belum ditetapkan pada penempatan: 'mentor', 'dosen', atau null.
     */
    private function missingSupervisor(bool $requireAdvisor): ?string
    {
        $placement = $this->placement;

        if (! $placement || (! $placement->mentor_id && ! $placement->pembimbing_id)) {
            return 'mentor';
        }

        if ($requireAdvisor && ! $placement->academic_advisor_id) {
            return 'dosen';
        }

        return null;
    }

    /**
     * Ekspresi SQL tingkat prioritas (PostgreSQL & SQLite), padanan actionPriority().
     * Kelengkapan nilai mengikuti Application::has_complete_evaluation / Evaluation::is_complete.
     */
    public static function actionPrioritySql(): string
    {
        $univField = fn (string $field) => "(SELECT un.{$field} FROM users u JOIN universities un ON un.id = u.university_id WHERE u.id = applications.user_id)";
        $mentorOnly = "COALESCE({$univField('evaluation_scheme')}, 'dual_evaluation') = 'mentor_only'";
        $requireDpl = "COALESCE({$univField('require_dpl')}, TRUE) = TRUE";

        $mentorSum = 'COALESCE(ev.nilai_disiplin, 0) + COALESCE(ev.nilai_kinerja, 0) + COALESCE(ev.nilai_laporan, 0)';
        $mentorAll = 'COALESCE(ev.nilai_disiplin, 0) > 0 AND COALESCE(ev.nilai_kinerja, 0) > 0 AND COALESCE(ev.nilai_laporan, 0) > 0';
        $dosenAny = '(COALESCE(ev.nilai_dosen, 0) > 0 OR COALESCE(ev.nilai_akademik, 0) > 0'
            .' OR COALESCE(ev.score_mastery, 0) + COALESCE(ev.score_report, 0) + COALESCE(ev.score_attitude, 0) > 0)';

        $evaluationComplete = "EXISTS (SELECT 1 FROM placements p JOIN evaluations ev ON ev.placement_id = p.id
            WHERE p.application_id = applications.id AND (
                COALESCE(ev.final_score, 0) > 0
                OR ({$mentorOnly} AND {$mentorAll})
                OR (NOT ({$mentorOnly}) AND {$mentorSum} > 0 AND {$dosenAny})
            ))";
        $reportApproved = "EXISTS (SELECT 1 FROM placements p JOIN final_reports fr ON fr.placement_id = p.id
            WHERE p.application_id = applications.id AND LOWER(fr.status) = 'approved')";
        $logbookFilled = 'EXISTS (SELECT 1 FROM placements p JOIN logbooks lb ON lb.placement_id = p.id
            WHERE p.application_id = applications.id)';
        $mentorMissing = 'NOT EXISTS (SELECT 1 FROM placements p WHERE p.application_id = applications.id
            AND (p.mentor_id IS NOT NULL OR p.pembimbing_id IS NOT NULL))';
        $dosenMissing = 'EXISTS (SELECT 1 FROM placements p WHERE p.application_id = applications.id AND p.academic_advisor_id IS NULL)';

        return "(CASE
            WHEN applications.status IN ('pending', 'verified') THEN 1
            WHEN applications.status = 'active' AND {$reportApproved} AND {$evaluationComplete} AND {$logbookFilled} THEN 2
            WHEN applications.status IN ('accepted', 'active') AND ({$mentorMissing} OR ({$dosenMissing} AND {$requireDpl})) THEN 3
            WHEN applications.status = 'active' THEN 4
            WHEN applications.status = 'accepted' THEN 5
            WHEN applications.status = 'completed' THEN 6
            WHEN applications.status IN ('rejected', 'resigned') THEN 8
            ELSE 7
        END)";
    }

    /**
     * Urutan standar daftar pengajuan: prioritas tindakan, lalu antrean (tingkat 1–3 terlama dulu,
     * tingkat lain terbaru dulu).
     */
    public function scopeOrderByActionPriority($query)
    {
        $tier = self::actionPrioritySql();

        return $query->orderByRaw("{$tier} ASC")
            ->orderByRaw("CASE WHEN {$tier} <= ".self::ACTION_THRESHOLD.' THEN applications.created_at END ASC')
            ->orderBy('applications.created_at', 'desc')
            ->orderBy('applications.id', 'desc');
    }

    /**
     * Hanya pengajuan yang butuh tindakan admin (tingkat 1–3). Pemakaian: ->requiringAction()
     */
    public function scopeRequiringAction($query)
    {
        return $query->whereRaw(self::actionPrioritySql().' <= '.self::ACTION_THRESHOLD);
    }
}
