<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

if (! Schema::hasColumn('users', 'university')) {
    Schema::table('users', function (Blueprint $table) {
        $table->string('university')->nullable();
    });
    echo "Added column 'university' to 'users' table.\n";
} else {
    echo "Column 'university' already exists.\n";
}
