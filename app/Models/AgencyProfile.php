<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgencyProfile extends Model
{
    protected $guarded = ['id'];

    /**
     * Alias atribut name ke agency_name agar seragam dengan antarmuka komponen.
     */
    public function getNameAttribute(): string
    {
        return (string) ($this->attributes['agency_name'] ?? '');
    }

    /**
     * URL logo instansi yang benar-benar ada.
     * Logo bisa tersimpan di public/ (mis. "images/logos/diskominfo.png") atau di disk public
     * (storage/app/public, diakses lewat /storage/...). Fallback ke logo default.
     */
    public function getLogoUrlAttribute(): string
    {
        $raw = (string) ($this->attributes['logo'] ?? '');
        if (trim($raw) === '') {
            return asset('images/logos/surabaya.png');
        }

        if (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://')) {
            return $raw;
        }

        $logo = ltrim($raw, '/');

        if (str_starts_with($logo, 'storage/')) {
            $sub = substr($logo, 8);
            if (is_file(public_path($logo)) || is_file(storage_path('app/public/'.$sub))) {
                return asset($logo);
            }
        }

        if (is_file(public_path($logo))) {
            return asset($logo);
        }

        if (is_file(public_path('storage/'.$logo)) || is_file(storage_path('app/public/'.$logo))) {
            return asset('storage/'.$logo);
        }

        if (is_file(public_path('images/logos/'.$logo))) {
            return asset('images/logos/'.$logo);
        }

        return is_file(public_path('images/logos/surabaya.png'))
            ? asset('images/logos/surabaya.png')
            : asset('images/default-agency.svg');
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

        // Hitung total mahasiswa yang diterima/aktif di bawah dinas ini saat ini
        $today = date('Y-m-d');
        $filledQuota = Application::whereHas('unit', function ($query) {
            $query->where('agency_profile_id', $this->id);
        })->whereIn('status', ['accepted', 'active'])
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $today);
            })->count();

        return max(0, $totalQuota - $filledQuota);
    }
}
