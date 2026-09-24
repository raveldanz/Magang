<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Application extends Model
{
    protected $guarded = ['id'];

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
        return strtoupper((string)($this->status ?? 'PENDING'));
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
        return (bool)($placement && $placement->finalreport && strtolower($placement->finalreport->status ?? '') === 'approved');
    }

    public function getHasCompleteEvaluationAttribute(): bool
    {
        $eval = $this->placement?->evaluation;
        if (!$eval) {
            return false;
        }

        if ((float)($eval->final_score ?? 0) > 0) {
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

    public function getCanCompleteAttribute(): bool
    {
        $rawStatus = $this->status instanceof ApplicationStatus ? $this->status->value : strtolower((string)$this->status);

        if ($rawStatus === 'completed') {
            return true;
        }

        if (!in_array($rawStatus, ['accepted', 'active'])) {
            return false;
        }

        return $this->has_approved_report && $this->has_complete_evaluation;
    }
}