<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

echo "Checking & Fixing 'placements' table schema..." . PHP_EOL;

Schema::table('placements', function (Blueprint $table) {
    if (!Schema::hasColumn('placements', 'mentor_id')) {
        $table->foreignId('mentor_id')->nullable()->constrained('users')->onDelete('set null');
        echo "  - Added 'mentor_id' column." . PHP_EOL;
    }
    if (!Schema::hasColumn('placements', 'academic_advisor_id')) {
        $table->foreignId('academic_advisor_id')->nullable()->constrained('users')->onDelete('set null');
        echo "  - Added 'academic_advisor_id' column." . PHP_EOL;
    }
    if (!Schema::hasColumn('placements', 'status')) {
        $table->string('status')->nullable()->default('active');
        echo "  - Added 'status' column." . PHP_EOL;
    }
});

echo "Done fixing 'placements' schema!" . PHP_EOL;
