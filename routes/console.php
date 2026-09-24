<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sinkronisasi harian status magang mahasiswa (ACCEPTED -> ACTIVE saat tanggal mulai magang tiba)
Schedule::command('app:sync-internship-status')->daily();
