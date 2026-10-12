<?php

namespace App\Jobs;

use App\Services\SystemHealth;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

/**
 * Job kecil yang dikirim scheduler tiap 5 menit. Bila queue worker berjalan, job ini dieksekusi dan
 * mencatat waktu terakhir — dipakai SystemHealth untuk mendeteksi worker yang mati
 * (notifikasi web push bergantung pada queue).
 */
class QueueHeartbeat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Cache::forever(SystemHealth::QUEUE_KEY, now()->toIso8601String());
    }
}
