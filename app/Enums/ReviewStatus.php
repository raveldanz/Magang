<?php

namespace App\Enums;

enum ReviewStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case REVISION = 'revision';

    /**
     * Label representasi resmi Bahasa Indonesia untuk antarmuka pengguna
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Review',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
            self::REVISION => 'Perlu Revisi',
        };
    }

    /**
     * Skema pewarnaan badge Tailwind CSS
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::PENDING => 'bg-amber-50 text-amber-700 border-amber-200',
            self::APPROVED => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::REJECTED => 'bg-rose-50 text-rose-700 border-rose-200',
            self::REVISION => 'bg-orange-50 text-orange-700 border-orange-200',
        };
    }

    /**
     * Kembalikan seluruh nilai raw string enum
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
