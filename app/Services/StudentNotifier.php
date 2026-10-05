<?php

namespace App\Services;

use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Notifikasi lonceng untuk mahasiswa saat pembimbing (Mentor Dinas / DPL) menindaklanjuti
 * logbook, laporan akhir, atau nilai — agar mahasiswa tidak perlu mengecek satu per satu.
 * Teks sengaja singkat & sama untuk kedua role; hanya nama pengirimnya yang berbeda.
 */
class StudentNotifier
{
    /**
     * @param  string  $from  'mentor' atau 'lecturer'
     */
    public static function logbookRevision(?User $student, string $from, int $count, ?string $feedback = null): void
    {
        if (! $student || $count < 1) {
            return;
        }

        $message = "{$count} logbook Anda diminta diperbaiki oleh ".self::sender($from).'.';
        if (filled($feedback)) {
            $message .= ' Catatan: "'.Str::limit($feedback, 140).'"';
        }

        SystemNotification::send(
            'Logbook Perlu Diperbaiki',
            $message,
            $student->id,
            null,
            route('student.logbook.index', [], false),
            'Perbaiki Logbook',
            'warning',
            'logbook',
            ''
        );
    }

    public static function finalReportReviewed(?User $student, string $from, string $status, ?string $feedback = null): void
    {
        if (! $student) {
            return;
        }

        $approved = $status === 'approved';
        $message = $approved
            ? 'Laporan akhir Anda telah disetujui oleh '.self::sender($from).'.'
            : 'Laporan akhir Anda diminta direvisi oleh '.self::sender($from).'.';
        if (filled($feedback)) {
            $message .= ' Catatan: "'.Str::limit($feedback, 140).'"';
        }

        SystemNotification::send(
            $approved ? 'Laporan Akhir Disetujui' : 'Laporan Akhir Perlu Revisi',
            $message,
            $student->id,
            null,
            route('student.final_report.index', [], false),
            $approved ? 'Lihat Laporan' : 'Unggah Revisi',
            $approved ? 'success' : 'warning',
            'evaluation',
            ''
        );
    }

    public static function evaluationSubmitted(?User $student, string $from): void
    {
        if (! $student) {
            return;
        }

        SystemNotification::send(
            'Nilai Magang Telah Diisi',
            self::sender($from, true).' telah mengisi nilai evaluasi magang Anda.',
            $student->id,
            null,
            route('student.final_report.index', [], false),
            'Lihat Status',
            'success',
            'evaluation',
            ''
        );
    }

    private static function sender(string $from, bool $capitalize = false): string
    {
        $label = $from === 'lecturer' ? 'dosen pembimbing' : 'mentor dinas';

        return $capitalize ? ucfirst($label) : $label;
    }
}
