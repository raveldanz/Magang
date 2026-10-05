<?php

namespace App\Observers;

use App\Models\Application;
use App\Services\Chat\ChatGroupService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

/**
 * Menyinkronkan grup bimbingan mentor dan DPL saat status aplikasi mahasiswa berubah
 * (mis. accepted, active, atau keluar/resigned/rejected).
 */
class ApplicationChatObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private ChatGroupService $groups) {}

    public function saved(Application $application): void
    {
        try {
            if ($application->user) {
                $this->groups->syncStudentSupervisionGroups($application->user);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
