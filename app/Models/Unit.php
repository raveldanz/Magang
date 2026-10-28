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

    // Relasi: Kepala / Koordinator Unit (akun mentor instansi, opsional)
    public function head()
    {
        return $this->belongsTo(User::class, 'head_user_id');
    }

    public function headUser()
    {
        return $this->belongsTo(User::class, 'head_user_id');
    }

    // Relasi: Anggota Tim Kerja / Mentor yang bertugas di Bidang ini
    public function mentors()
    {
        return $this->hasMany(User::class, 'unit_id')->where('role', 'mentor');
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
        if (array_key_exists('occupied_count_db', $this->attributes)) {
            return (int) $this->attributes['occupied_count_db'];
        }
        if (array_key_exists('accepted_count', $this->attributes)) {
            return (int) $this->attributes['accepted_count'];
        }
        if (! $this->relationLoaded('applications')) {
            return $this->applications()->occupyingQuota()->count();
        }

        return $this->applications->filter(fn ($app) => $app->occupiesQuota())->count();
    }

    // Accessor alias untuk accepted_count agar seragam dengan occupied_count
    public function getAcceptedCountAttribute(): int
    {
        return $this->occupied_count;
    }

    const MAX_GROUP_MEMBERS = 100;

    protected $casts = [
        'quota' => 'integer',
    ];

    // Accessor untuk menghitung sisa kuota dinamis: $unit->remaining_quota
    public function getRemainingQuotaAttribute()
    {
        return max(0, $this->quota - $this->occupied_count);
    }

    /**
     * Menghitung batas maksimal anggota kelompok yang efektif berlaku untuk bidang ini.
     * Menggunakan standar aman 100 orang dan dibatasi oleh sisa kuota yang masih tersedia.
     */
    public function getEffectiveMaxGroupMembersAttribute(): int
    {
        return min(self::MAX_GROUP_MEMBERS, $this->remaining_quota);
    }
}
