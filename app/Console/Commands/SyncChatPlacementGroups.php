<?php

namespace App\Console\Commands;

use App\Models\Placement;
use App\Services\Chat\ChatGroupService;
use Illuminate\Console\Command;

/**
 * Membuat/menyamakan Grup Bimbingan untuk semua penempatan (mis. data lama sebelum fitur chat).
 * Tanpa command ini pun grup dibuat otomatis saat pengguna membuka halaman chat.
 */
class SyncChatPlacementGroups extends Command
{
    protected $signature = 'chat:sync-placement-groups';

    protected $description = 'Buat atau sinkronkan Grup Bimbingan chat untuk seluruh penempatan magang';

    public function handle(ChatGroupService $groups): int
    {
        $synced = 0;
        $failed = 0;

        Placement::query()->orderBy('id')->chunkById(100, function ($placements) use ($groups, &$synced, &$failed) {
            foreach ($placements as $placement) {
                try {
                    if ($groups->syncPlacementGroup($placement)) {
                        $synced++;
                    }
                } catch (\Throwable $e) {
                    $failed++;
                    report($e);
                }
            }
        });

        $this->info("Grup Bimbingan tersinkron: {$synced}" . ($failed ? " (gagal: {$failed}, lihat log)" : ''));

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
