<?php

namespace App\Enums;

use App\Enums\Concerns\DisplaysStatusCode;

/**
 * Status keaktifan akun pengguna (users.status), sama untuk semua role.
 * INACTIVE memblokir login (EnsureAccountIsActive); cuti pembimbing = INACTIVE, diaktifkan kembali oleh admin.
 * PENDING dipakai Pembimbing (dosen/guru) yang mendaftar sendiri: boleh login, tetapi belum bisa dipilih
 * atau ditugaskan membimbing sampai disetujui Admin Kampus/Sekolah atau Super Admin.
 *
 * Pengecualian standar tampilan: label memakai Bahasa Indonesia (Aktif/Nonaktif), bukan kode,
 * karena status akun bukan status alur kerja dan kata "ACTIVE" bertabrakan dengan status magang ACTIVE.
 */
enum AccountStatus: string
{
    use DisplaysStatusCode;

    case ACTIVE = 'active';
    case PENDING = 'pending';
    case INACTIVE = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Aktif',
            self::PENDING => 'Menunggu Persetujuan',
            self::INACTIVE => 'Nonaktif',
        };
    }

    public function localizedLabel(?string $role = null): string
    {
        return $this->label();
    }

    public function description(): string
    {
        return match ($this) {
            self::ACTIVE => 'Dapat Login',
            self::PENDING => 'Dapat Login, Belum Bisa Membimbing',
            self::INACTIVE => 'Akses Login Dicabut',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-emerald-50 text-emerald-700 border-emerald-300',
            self::PENDING => 'bg-amber-50 text-amber-800 border-amber-300',
            self::INACTIVE => 'bg-slate-100 text-slate-700 border-slate-300',
        };
    }

    public function dotColor(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-emerald-500',
            self::PENDING => 'bg-amber-500',
            self::INACTIVE => 'bg-slate-400',
        };
    }
}
