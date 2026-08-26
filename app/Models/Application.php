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

        // 1. REJECTED
        if ($rawStatus === 'rejected') {
            return 'REJECTED';
        }

        $placement = $this->placement;
        $hasApprovedReport = $placement && $placement->finalreport && in_array(strtolower($placement->finalreport->status ?? ''), ['approved', 'disetujui']);
        $eval = $placement?->evaluation;
        $hasCompleteEval = $eval && (($eval->nilai_pembimbing > 0 && $eval->nilai_dosen_calculated > 0) || $eval->nilai_akhir > 0);

        // 2. COMPLETED
        if ($rawStatus === 'completed' || ($rawStatus === 'accepted' && $hasApprovedReport && $hasCompleteEval)) {
            return 'COMPLETED';
        }

        $today = Carbon::now()->toDateString();

        // 3. ACTIVE / ACCEPTED
        if ($rawStatus === 'accepted') {
            $startDate = !empty($this->start_date) ? Carbon::parse($this->start_date)->toDateString() : null;
            if ($startDate && $today >= $startDate) {
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
}