<?php

namespace App\Enums;

use App\Enums\Concerns\DisplaysStatusCode;

/**
 * Status akun pengguna (users.status). INACTIVE memblokir login (EnsureAccountIsActive).
 */
enum AccountStatus: string
{
    use DisplaysStatusCode;

    case ACTIVE = 'active';
    case ON_LEAVE = 'on_leave';
    case INACTIVE = 'inactive';

    public function description(): string
    {
        return match ($this) {
            self::ACTIVE => 'Akun Aktif',
            self::ON_LEAVE => 'Cuti, Tidak Menerima Bimbingan',
            self::INACTIVE => 'Nonaktif, Akses Login Dicabut',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-emerald-50 text-emerald-700 border-emerald-300',
            self::ON_LEAVE => 'bg-amber-50 text-amber-700 border-amber-300',
            self::INACTIVE => 'bg-slate-100 text-slate-700 border-slate-300',
        };
    }

    public function dotColor(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-emerald-500',
            self::ON_LEAVE => 'bg-amber-500',
            self::INACTIVE => 'bg-slate-400',
        };
    }
}
