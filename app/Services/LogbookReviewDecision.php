<?php

namespace App\Services;

use App\Models\Logbook;
use Illuminate\Support\Collection;

/**
 * Aturan keputusan pemeriksaan logbook mingguan, dipakai Mentor Lapangan dan Dosen Pembimbing.
 *
 *  - status "approved"  : semua hari disetujui,
 *  - status "rejected"  : semua hari dikembalikan untuk diperbaiki,
 *  - status "per_day"   : hari di $reviseIds dikembalikan, sisanya disetujui.
 *
 * Catatan pemeriksa ditujukan ke hari yang dikembalikan bila ada; bila tidak ada yang
 * dikembalikan, catatan itu menjadi catatan persetujuan. Hari yang disetujui setelah
 * sebelumnya dikembalikan (lalu diperbaiki mahasiswa) tidak lagi membawa catatan revisi lama.
 */
class LogbookReviewDecision
{
    /**
     * @param  Collection<int, Logbook>  $logbooks
     * @param  list<int>  $reviseIds
     * @return array<int, string> logbook id => 'approved' | 'rejected'
     */
    public static function resolve(Collection $logbooks, string $status, array $reviseIds = []): array
    {
        $decisions = [];
        foreach ($logbooks as $logbook) {
            $decisions[$logbook->id] = match ($status) {
                'rejected' => 'rejected',
                'per_day' => in_array((int) $logbook->id, $reviseIds, true) ? 'rejected' : 'approved',
                default => 'approved',
            };
        }

        return $decisions;
    }

    /**
     * Catatan yang disimpan untuk satu hari setelah diputuskan.
     *
     * @param  bool  $noteTargetsRejected  true bila ada hari yang dikembalikan pada kiriman ini
     */
    public static function feedbackFor(?string $oldStatus, ?string $oldFeedback, string $newStatus, ?string $note, ?bool $noteTargetsRejected = null): ?string
    {
        $oldStatus = strtolower((string) $oldStatus);

        if ($newStatus === 'rejected') {
            return $note ?? $oldFeedback;
        }

        // Disetujui: catatan baru dipakai hanya jika ditujukan untuk persetujuan.
        if ($note !== null && $noteTargetsRejected !== true) {
            return $note;
        }

        // Hari yang belum pernah disetujui hanya bisa membawa catatan revisi lama (mahasiswa sudah
        // memperbaikinya dan statusnya kembali menunggu). Catatan itu jangan ditampilkan bersama
        // status Disetujui. Catatan pada hari yang memang sudah disetujui tetap dipertahankan.
        return $oldStatus === 'approved' ? $oldFeedback : null;
    }

    public static function summaryMessage(int $approved, int $rejected): string
    {
        return match (true) {
            $approved > 0 && $rejected > 0 => "Tersimpan: {$approved} hari disetujui dan {$rejected} hari dikembalikan ke mahasiswa untuk diperbaiki.",
            $rejected > 0 => "Tersimpan: {$rejected} hari dikembalikan ke mahasiswa untuk diperbaiki.",
            default => "Tersimpan: {$approved} hari kegiatan disetujui.",
        };
    }
}
