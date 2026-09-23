<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Application extends Model
{
    protected $guarded = ['id'];

    protected $appends = [
        'lifecycle_status',
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
     * Computed dynamic lifecycle status
     * Statuses: REJECTED | SUBMITTED | ACCEPTED | ACTIVE | COMPLETED | DRAFT
     */
    public function getLifecycleStatusAttribute(): string
    {
        $rawStatus = strtolower($this->status ?? 'draft');

        if ($rawStatus === 'rejected' || $rawStatus === 'canceled') {
            return 'REJECTED';
        }
        
        if ($rawStatus === 'resigned') {
            return 'RESIGNED';
        }

        $placement = $this->placement;
        $hasApprovedReport = $placement && $placement->finalreport && in_array(strtolower($placement->finalreport->status ?? ''), ['approved', 'disetujui']);
        $eval = $placement?->evaluation;
        $hasCompleteEval = $eval && $eval->is_complete;

        // 2. COMPLETED
        if ($rawStatus === 'completed' || ($rawStatus === 'accepted' && $hasApprovedReport && $hasCompleteEval)) {
            return 'COMPLETED';
        }

        $today = Carbon::now()->toDateString();

        // 3. ACTIVE / ACCEPTED
        if ($rawStatus === 'accepted') {
            $startDate = !empty($this->start_date) ? Carbon::parse($this->start_date)->toDateString() : null;
            if (!$startDate || $today >= $startDate) {
                return 'ACTIVE';
            }
            return 'ACCEPTED';
        }

        // 4. SUBMITTED / PENDING / VERIFIED
        if (in_array($rawStatus, ['submitted', 'pending', 'verified'])) {
            return 'SUBMITTED';
        }

        // 5. DRAFT
        return 'DRAFT';
    }

    public function getIsActiveInternshipAttribute(): bool
    {
        return $this->lifecycle_status === 'ACTIVE';
    }

    public function getIsEligibleForLogbookAttribute(): bool
    {
        return $this->is_active_internship;
    }

    public function getHasApprovedReportAttribute(): bool
    {
        $placement = $this->placement;
        return (bool)($placement && $placement->finalreport && in_array(strtolower($placement->finalreport->status ?? ''), ['approved', 'disetujui']));
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
        if (strtolower($this->status) === 'completed') {
            return true;
        }

        if (strtolower($this->status) !== 'accepted') {
            return false;
        }

        return $this->has_approved_report && $this->has_complete_evaluation;
    }
}