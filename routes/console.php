<?php

use App\Console\Commands\SyncExternalInstitutions;
use App\Jobs\QueueHeartbeat;
use App\Services\SystemHealth;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sinkronisasi harian status magang mahasiswa (ACCEPTED -> ACTIVE saat tanggal mulai tiba,
// serta penutupan / pengingat magang yang melewati tanggal selesai). Waktu mengikuti APP_TIMEZONE (WIB).
Schedule::command('app:sync-internship-status')->dailyAt('00:05')->withoutOverlapping();

// Master sekolah mengikuti direktori nasional (NPSN): sekolah baru otomatis muncul tiap pekan, data manual tidak disentuh.
// Diperiksa tiap jam dan baru berjalan bila sinkron penuh terakhir sudah >= 7 hari (atau belum pernah), sehingga
// laptop demo yang mati pada jam jadwal tetap menyusul begitu penjadwal hidup lagi. Gagal (tanpa internet) = dicoba jam berikutnya.
Schedule::command('institutions:sync-external')->hourlyAt(30)
    ->when(fn () => SyncExternalInstitutions::isDue())
    ->withoutOverlapping(120)->runInBackground();

// Heartbeat tiap 5 menit: menandai scheduler hidup & mengirim job kecil untuk memastikan queue worker hidup.
// Statusnya tampil di dasbor Super Admin dan lewat `php artisan app:health`.
Schedule::call(function () {
    SystemHealth::recordSchedulerHeartbeat();
    QueueHeartbeat::dispatch();
})->everyFiveMinutes()->name('system-heartbeat')->withoutOverlapping();
