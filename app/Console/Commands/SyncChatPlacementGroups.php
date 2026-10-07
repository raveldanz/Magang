<?php

namespace App\Console\Commands;

use App\Models\Placement;
use App\Models\User;
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

        $mentorIds = Placement::whereNotNull('mentor_id')->pluck('mentor_id')
            ->merge(Placement::whereNotNull('pembimbing_id')->pluck('pembimbing_id'))
            ->unique()->filter();

        foreach (User::whereIn('id', $mentorIds)->get() as $mentor) {
            try {
                if ($groups->syncMentorGroup($mentor)) {
                    $synced++;
                }
            } catch (\Throwable $e) {
                $failed++;
                report($e);
            }
        }

        $dplIds = Placement::whereNotNull('academic_advisor_id')->pluck('academic_advisor_id')->unique()->filter();

        foreach (User::whereIn('id', $dplIds)->get() as $dpl) {
            try {
                if ($groups->syncDplGroup($dpl)) {
                    $synced++;
                }
            } catch (\Throwable $e) {
                $failed++;
                report($e);
            }
        }

        $this->info("Grup Bimbingan tersinkron: {$synced}".($failed ? " (gagal: {$failed}, lihat log)" : ''));

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
