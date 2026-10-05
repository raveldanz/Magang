<?php

namespace App\Console\Commands;

use App\Services\SystemHealth;
use Illuminate\Console\Command;

class SystemHealthCheck extends Command
{
    protected $signature = 'app:health';

    protected $description = 'Periksa apakah scheduler & queue worker berjalan (heartbeat)';

    public function handle(SystemHealth $health): int
    {
        $status = $health->status();

        $this->table(['Komponen', 'Terakhir aktif'], [
            ['Scheduler', $status['scheduler_last']?->toDateTimeString() ?? 'belum pernah'],
            ['Queue worker', $status['queue_last']?->toDateTimeString() ?? 'belum pernah'],
            ['Job tertunda', $status['pending_jobs']],
            ['Job gagal', $status['failed_jobs']],
        ]);

        foreach ($status['issues'] as $issue) {
            $this->warn('• '.$issue);
        }

        if ($status['issues'] === []) {
            $this->info('Semua proses latar belakang berjalan normal.');
        }

        return $status['issues'] === [] ? self::SUCCESS : self::FAILURE;
    }
}
