<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

if (!\Schema::hasColumn('users', 'university')) {
    \Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
        $table->string('university')->nullable();
    });
    echo "Added column 'university' to 'users' table.\n";
} else {
    echo "Column 'university' already exists.\n";
}
