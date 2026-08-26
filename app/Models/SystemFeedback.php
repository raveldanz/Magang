<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemFeedback extends Model
{
    use HasFactory;

    protected $table = 'system_feedbacks';

    protected $fillable = [
        'user_id',
        'sender_name',
        'sender_email',
        'sender_role',
        'target_role',
        'target_agency_id',
        'target_university_id',
        'category',
        'subject',
        'message',
        'attachment',
        'priority',
        'status',
        'admin_response',
        'responded_by',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    public function targetAgency()
    {
        return $this->belongsTo(AgencyProfile::class, 'target_agency_id');
    }

    public function targetUniversity()
    {
        return $this->belongsTo(University::class, 'target_university_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForSuperAdmin($query)
    {
        return $query->where(function ($q) {
            $q->where('target_role', 'super_admin')
              ->orWhereNull('target_role');
        });
    }

    public function scopeForAgency($query, $agencyId)
    {
        return $query->where(function ($q) use ($agencyId) {
            $q->where('target_role', 'admin_dinas')
              ->where('target_agency_id', $agencyId);
        });
    }

    public function scopeForUniversity($query, $universityId)
    {
        return $query->where(function ($q) use ($universityId) {
            $q->where('target_role', 'universitas')
              ->where('target_university_id', $universityId);
        });
    }
}
