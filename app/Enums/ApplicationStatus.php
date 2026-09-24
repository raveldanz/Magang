<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case PENDING = 'pending';
    case VERIFIED = 'verified';
    case ACCEPTED = 'accepted';
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';
    case RESIGNED = 'resigned';

    /**
     * Label representasi resmi Bahasa Indonesia untuk antarmuka pengguna
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Verifikasi',
            self::VERIFIED => 'Terverifikasi Berkas',
            self::ACCEPTED => 'Diterima Magang',
            self::ACTIVE => 'Magang Aktif',
            self::COMPLETED => 'Selesai Magang',
            self::REJECTED => 'Ditolak',
            self::RESIGNED => 'Mengundurkan Diri',
        };
    }

    /**
     * Skema pewarnaan badge seragam Tailwind CSS di seluruh dashboard
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::PENDING => 'bg-amber-50 text-amber-700 border-amber-300',
            self::VERIFIED => 'bg-sky-50 text-sky-700 border-sky-300',
            self::ACCEPTED => 'bg-indigo-50 text-indigo-700 border-indigo-300',
            self::ACTIVE => 'bg-emerald-50 text-emerald-700 border-emerald-300',
            self::COMPLETED => 'bg-blue-50 text-blue-700 border-blue-300',
            self::REJECTED => 'bg-rose-50 text-rose-700 border-rose-300',
            self::RESIGNED => 'bg-slate-100 text-slate-700 border-slate-300',
        };
    }

    /**
     * Titik indikator warna (dot indicator)
     */
    public function dotColor(): string
    {
        return match ($this) {
            self::PENDING => 'bg-amber-500',
            self::VERIFIED => 'bg-sky-500',
            self::ACCEPTED => 'bg-indigo-500',
            self::ACTIVE => 'bg-emerald-500',
            self::COMPLETED => 'bg-blue-600',
            self::REJECTED => 'bg-rose-500',
            self::RESIGNED => 'bg-slate-500',
        };
    }

    /**
     * True jika mahasiswa sedang dalam periode aktif magang di lapangan
     */
    public function isOngoing(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * True jika mahasiswa memiliki izin akses pengisian logbook
     */
    public function canLogbook(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Kembalikan seluruh nilai raw string enum
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
