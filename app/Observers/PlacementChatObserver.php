<?php

namespace App\Observers;

use App\Models\Placement;
use App\Services\Chat\ChatGroupService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

/**
 * Menyinkronkan Grup Bimbingan setiap penempatan disimpan (mentor/DPL ditetapkan atau diganti).
 * Berjalan SETELAH transaksi selesai, dan kegagalannya hanya dicatat ke log, sehingga proses
 * inti (terima pengajuan, pilih DPL, dll.) tidak pernah ikut gagal karena chat.
 */
class PlacementChatObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private ChatGroupService $groups) {}

    public function saved(Placement $placement): void
    {
        try {
            $this->groups->syncPlacementGroup($placement);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function deleted(Placement $placement): void
    {
        try {
            $this->groups->syncPlacementGroup($placement);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
