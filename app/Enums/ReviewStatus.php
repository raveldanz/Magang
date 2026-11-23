<?php

namespace App\Enums;

use App\Enums\Concerns\DisplaysStatusCode;
use App\Models\User;

enum ReviewStatus: string
{
    use DisplaysStatusCode;

    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case REVISION = 'revision';

    /**
     * Keterangan Bahasa Indonesia (baris kedua di area lapang / tooltip di badge ringkas).
     */
    public function description(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Review',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak, Perlu Diperbaiki',
            self::REVISION => 'Perlu Revisi',
        };
    }

    /**
     * Label status UI Bahasa Indonesia terstandar khusus peran mentor & supervisor.
     */
    public function localizedLabel(?string $role = null): string
    {
        $effectiveRole = $role ?? auth()->user()?->role;
        if (! User::isLocalizedStatusUiRole($effectiveRole)) {
            return $this->label();
        }

        return match ($this) {
            self::PENDING => 'Menunggu Tinjauan',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Perlu Revisi',
            self::REVISION => 'Perlu Revisi',
        };
    }

    /**
     * Skema pewarnaan badge Tailwind CSS (selaras dengan ApplicationStatus)
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::PENDING => 'bg-amber-50 text-amber-800 border-amber-300',
            self::APPROVED => 'bg-emerald-50 text-emerald-800 border-emerald-300',
            self::REJECTED => 'bg-rose-50 text-rose-800 border-rose-300',
            self::REVISION => 'bg-orange-50 text-orange-800 border-orange-300',
        };
    }

    public function dotColor(): string
    {
        return match ($this) {
            self::PENDING => 'bg-amber-500',
            self::APPROVED => 'bg-emerald-500',
            self::REJECTED => 'bg-rose-500',
            self::REVISION => 'bg-orange-500',
        };
    }
}
