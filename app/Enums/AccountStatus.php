<?php

namespace App\Enums;

use App\Enums\Concerns\DisplaysStatusCode;

/**
 * Status keaktifan akun pengguna (users.status), sama untuk semua role.
 * INACTIVE memblokir login (EnsureAccountIsActive); cuti pembimbing = INACTIVE, diaktifkan kembali oleh admin.
 *
 * Pengecualian standar tampilan: label memakai Bahasa Indonesia (Aktif/Nonaktif), bukan kode,
 * karena status akun bukan status alur kerja dan kata "ACTIVE" bertabrakan dengan status magang ACTIVE.
 */
enum AccountStatus: string
{
    use DisplaysStatusCode;

    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Aktif',
            self::INACTIVE => 'Nonaktif',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ACTIVE => 'Dapat Login',
            self::INACTIVE => 'Akses Login Dicabut',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-emerald-50 text-emerald-700 border-emerald-300',
            self::INACTIVE => 'bg-slate-100 text-slate-700 border-slate-300',
        };
    }

    public function dotColor(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-emerald-500',
            self::INACTIVE => 'bg-slate-400',
        };
    }
}
