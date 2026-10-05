<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\DB::table('migrations')
    ->where('migration', '2026_08_12_010842_create_personal_access_tokens_table')
    ->update(['migration' => '2026_08_11_082602_create_personal_access_tokens_table']);

echo "Updated migration table entry successfully." . PHP_EOL;
