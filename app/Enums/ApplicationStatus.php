<?php

namespace App\Enums;

use App\Enums\Concerns\DisplaysStatusCode;

enum ApplicationStatus: string
{
    use DisplaysStatusCode;

    case DRAFT = 'draft';
    case PENDING = 'pending';
    case WAITING_APPROVAL = 'waiting_approval';
    case ACCEPTED = 'accepted';
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';
    case RESIGNED = 'resigned';

    /**
     * Keterangan Bahasa Indonesia untuk kode status. Tampil sebagai baris kedua di area
     * yang lapang (kartu/panel) dan sebagai tooltip pada badge ringkas di tabel.
     */
    public function description(?string $role = null): string
    {
        $role = $role ?? auth()->user()?->role;
        if ($this === self::WAITING_APPROVAL && $role === 'supervisor') {
            return 'Menunggu ACC';
        }

        return match ($this) {
            self::DRAFT => 'Draf Kelompok (Belum Dikirim)',
            self::PENDING => 'Menunggu Verifikasi Berkas',
            self::WAITING_APPROVAL => 'Setujui, Serahkan ke Atasan',
            self::ACCEPTED => 'Diterima, Belum Mulai Magang',
            self::ACTIVE => 'Aktif Magang',
            self::COMPLETED => 'Selesai Magang',
            self::REJECTED => 'Pengajuan Ditolak',
            self::RESIGNED => 'Mengundurkan Diri',
        };
    }

    /**
     * Deskripsi khusus untuk peran Supervisor / Atasan
     */
    public function descriptionForSupervisor(): string
    {
        return $this === self::WAITING_APPROVAL ? 'Menunggu ACC' : $this->description();
    }

    /**
     * Skema pewarnaan badge seragam Tailwind CSS di seluruh dashboard
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-slate-100 text-slate-700 border-slate-300',
            self::PENDING => 'bg-amber-50 text-amber-700 border-amber-300',
            self::WAITING_APPROVAL => 'bg-purple-50 text-purple-700 border-purple-200',
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
            self::DRAFT => 'bg-slate-400',
            self::PENDING => 'bg-amber-500',
            self::WAITING_APPROVAL => 'bg-purple-600',
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
        return $this === self::ACTIVE || $this === self::ACCEPTED;
    }
}
