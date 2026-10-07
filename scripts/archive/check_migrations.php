<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$migrations = DB::table('migrations')->get();
foreach ($migrations as $m) {
    echo "{$m->id} | {$m->migration} | batch: {$m->batch}".PHP_EOL;
}
