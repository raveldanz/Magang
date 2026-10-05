<?php

use App\Models\Application;
use App\Models\Logbook;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';
$a = require __DIR__.'/../bootstrap/app.php';
$a->make(Kernel::class)->bootstrap();

$u = User::where('email', 'mhs.live0528@unesa.ac.id')->first();
$app = Application::where('user_id', $u->id)->first();
try {
    $l = Logbook::create([
        'placement_id' => $app->placement->id,
        'date' => '2026-08-25',
        'activity' => 'Testing logbook creation direct',
        'status' => 'pending',
        'lecturer_status' => 'pending',
    ]);
    echo 'SUCCESS_ID_'.$l->id."\n";
} catch (Throwable $e) {
    echo 'ERROR: '.$e->getMessage()."\n";
}
