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

    // Accessor untuk menghitung sisa kuota dinamis: $unit->remaining_quota
    // Hanya menghitung mahasiswa berstatus accepted yang rentang magangnya masih aktif saat ini atau ke depan
    public function getRemainingQuotaAttribute()
    {
        $today = date('Y-m-d');
        $occupiedCount = $this->applications()
            ->where('status', 'accepted')
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $today);
            })
            ->count();

        return max(0, $this->quota - $occupiedCount);
    }
}
