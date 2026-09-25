<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgencyProfile extends Model
{
    protected $guarded = ['id'];

    /**
     * URL logo instansi yang benar-benar ada.
     * Logo bisa tersimpan di public/ (mis. "images/logos/diskominfo.png") atau di disk public
     * (storage/app/public, diakses lewat /storage/...). Fallback ke logo default.
     */
    public function getLogoUrlAttribute(): string
    {
        $logo = ltrim((string) ($this->attributes['logo'] ?? ''), '/');

        if ($logo !== '') {
            if (is_file(public_path($logo))) {
                return asset($logo);
            }
            if (is_file(public_path('storage/' . $logo)) || is_file(storage_path('app/public/' . $logo))) {
                return asset('storage/' . $logo);
            }
        }

        return asset('images/default-agency.svg');
    }

    public function units()
    {
        return $this->hasMany(Unit::class, 'agency_profile_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'agency_profile_id');
    }

    public function agencyAdmin()
    {
        return $this->hasOne(User::class, 'agency_profile_id')->where('role', 'admin');
    }

    public function admins()
    {
        return $this->hasMany(User::class, 'agency_profile_id')->where('role', 'admin');
    }

public function getRemainingQuotaAttribute()
{
    // Hitung total kapasitas seluruh unit kerja
    $totalQuota = $this->units->sum('quota');

    // Hitung total mahasiswa yang diterima/aktif di bawah dinas ini
    // Mengambil dari relasi unit -> applications yang berstatus accepted/completed
    $filledQuota = \App\Models\Application::whereHas('unit', function ($query) {
        $query->where('agency_profile_id', $this->id);
    })->whereIn('status', ['accepted', 'completed'])->count();

    return max(0, $totalQuota - $filledQuota);
}

}

