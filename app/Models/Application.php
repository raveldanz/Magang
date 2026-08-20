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
     * Statuses: REJECTED | PENDING | COMPLETED | ACTIVE | ACCEPTED
     */
    public function getLifecycleStatusAttribute(): string
    {
        $status = strtoupper($this->status ?? 'PENDING');

        // 1. REJECTED
        if ($status === 'REJECTED') {
            return 'REJECTED';
        }

        // 2. PENDING / VERIFIED
        if (in_array($status, ['PENDING', 'VERIFIED'])) {
            return 'PENDING';
        }

        $today = Carbon::now()->toDateString();
        $placement = $this->placement;
        $hasApprovedReport = $placement && $placement->finalreport && in_array(strtolower($placement->finalreport->status ?? ''), ['approved', 'disetujui']);
        $hasEvaluation = $placement && $placement->evaluation !== null;
        $isPastEndDate = !empty($this->end_date) && $today > Carbon::parse($this->end_date)->toDateString();

        // 3. COMPLETED
        if ($status === 'COMPLETED' || $hasApprovedReport || ($status === 'ACCEPTED' && $isPastEndDate && ($hasEvaluation || $hasApprovedReport))) {
            return 'COMPLETED';
        }

        // 4. ACTIVE
        $hasAdvisor = $placement && !empty($placement->academic_advisor_id);
        $hasMentor = $placement && (!empty($placement->mentor_id) || !empty($placement->pembimbing_id));
        $isWithinDate = !empty($this->start_date) && !empty($this->end_date)
            && $today >= Carbon::parse($this->start_date)->toDateString()
            && $today <= Carbon::parse($this->end_date)->toDateString();

        if ($status === 'ACCEPTED' && $hasAdvisor && $hasMentor && $isWithinDate) {
            return 'ACTIVE';
        }

        // 5. ACCEPTED (Awaiting start date or DPL selection)
        if ($status === 'ACCEPTED') {
            return 'ACCEPTED';
        }

        return $status;
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