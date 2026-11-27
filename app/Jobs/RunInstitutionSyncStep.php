<?php

namespace App\Jobs;

use App\Models\InstitutionSyncRun;
use App\Services\Institutions\InstitutionSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Satu langkah sinkron (satu halaman API) lalu mengantrekan langkah berikutnya, sehingga job lain
 * (misalnya notifikasi) tidak tertahan selama sinkron penuh.
 */
class RunInstitutionSyncStep implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    /** Di bawah retry_after antrean (90 detik) agar job yang masih jalan tidak diambil ulang. */
    public int $timeout = 80;

    public function __construct(public int $runId, public string $step, public int $page) {}

    public function handle(InstitutionSyncService $sync): void
    {
        $run = InstitutionSyncRun::find($this->runId);
        if (! $run?->isActive()) {
            return; // proses sudah selesai, gagal, atau digantikan
        }

        $next = $sync->processStep($run, $this->step, $this->page);
        if ($next !== null) {
            self::dispatch($this->runId, $next['step'], $next['page']);
        }
    }

    public function failed(?\Throwable $e): void
    {
        $run = InstitutionSyncRun::find($this->runId);
        if ($run?->isActive()) {
            app(InstitutionSyncService::class)->fail($run, 'Sinkron berhenti karena galat: '.mb_substr((string) $e?->getMessage(), 0, 200));
        }
    }
}
