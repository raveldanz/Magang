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

    // Accessor jumlah kuota terisi: $unit->occupied_count (definisi: Application::scopeOccupyingQuota)
    // Memakai relasi yang sudah di-eager-load bila ada, agar halaman daftar divisi tidak N+1
    public function getOccupiedCountAttribute(): int
    {
        if (!$this->relationLoaded('applications')) {
            return $this->applications()->occupyingQuota()->count();
        }

        return $this->applications->filter(fn ($app) => $app->occupiesQuota())->count();
    }

    // Accessor untuk menghitung sisa kuota dinamis: $unit->remaining_quota
    public function getRemainingQuotaAttribute()
    {
        return max(0, $this->quota - $this->occupied_count);
    }
}
