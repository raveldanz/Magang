<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pemeriksaan proses latar belakang yang WAJIB berjalan di server:
 *  - Scheduler (`php artisan schedule:run` tiap menit / `schedule:work`): aktivasi & penutupan magang harian.
 *  - Queue worker (`php artisan queue:work`): pengiriman notifikasi web push.
 */
class SystemHealth
{
    public const SCHEDULER_KEY = 'health:scheduler_heartbeat';

    public const QUEUE_KEY = 'health:queue_heartbeat';

    /** Toleransi keterlambatan heartbeat (menit). Heartbeat dikirim tiap 5 menit. */
    public const STALE_MINUTES = 20;

    public static function recordSchedulerHeartbeat(): void
    {
        Cache::forever(self::SCHEDULER_KEY, now()->toIso8601String());
    }

    /**
     * @return array{scheduler_last: ?Carbon, queue_last: ?Carbon, pending_jobs: int, failed_jobs: int, issues: array<int, string>}
     */
    public function status(): array
    {
        $schedulerLast = $this->timestamp(self::SCHEDULER_KEY);
        $queueLast = $this->timestamp(self::QUEUE_KEY);
        $pending = Schema::hasTable('jobs') ? (int) DB::table('jobs')->count() : 0;
        $failed = Schema::hasTable('failed_jobs') ? (int) DB::table('failed_jobs')->count() : 0;
        $oldestPending = Schema::hasTable('jobs') ? DB::table('jobs')->min('created_at') : null;

        $issues = [];
        if (! $schedulerLast || $schedulerLast->lt(now()->subMinutes(self::STALE_MINUTES))) {
            $issues[] = 'Scheduler tidak berjalan'.($schedulerLast ? ' sejak '.$schedulerLast->diffForHumans() : '')
                .' — aktivasi magang otomatis & pengingat masa magang berakhir tidak diproses. Pasang cron "php artisan schedule:run" tiap menit (atau "php artisan schedule:work" saat development).';
        }
        $queueStuck = $oldestPending && Carbon::createFromTimestamp((int) $oldestPending)->lt(now()->subMinutes(self::STALE_MINUTES));
        if ($schedulerLast && (! $queueLast || $queueLast->lt(now()->subMinutes(self::STALE_MINUTES)) || $queueStuck)) {
            $issues[] = "Queue worker tidak berjalan ({$pending} job tertunda) — notifikasi web push tidak terkirim. Jalankan \"php artisan queue:work\" sebagai proses permanen.";
        }
        if ($failed > 0) {
            $issues[] = "{$failed} job gagal tercatat di tabel failed_jobs. Periksa dengan \"php artisan queue:failed\".";
        }

        return [
            'scheduler_last' => $schedulerLast,
            'queue_last' => $queueLast,
            'pending_jobs' => $pending,
            'failed_jobs' => $failed,
            'issues' => $issues,
        ];
    }

    private function timestamp(string $key): ?Carbon
    {
        $value = Cache::get($key);

        return $value ? Carbon::parse($value) : null;
    }
}
