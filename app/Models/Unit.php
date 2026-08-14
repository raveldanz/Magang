<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $guarded = ['id'];

    // Relasi: Unit milik satu AgencyProfile
    public function agencyProfile()
    {
        return $this->belongsTo(AgencyProfile::class, 'agency_profile_id');
    }

    // Relasi: Satu Unit memiliki banyak pengajuan magang
    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    // Accessor untuk menghitung sisa kuota otomatis: $unit->remaining_quota
    public function getRemainingQuotaAttribute()
    {
        $acceptedCount = $this->applications()->where('status', 'accepted')->count();
        return max(0, $this->quota - $acceptedCount);
    }
}
