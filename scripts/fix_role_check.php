<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Drop existing check constraint on users role if present in PostgreSQL
    \DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check;');
    echo "Constraint users_role_check dropped successfully.\n";
} catch (\Exception $e) {
    echo "Error dropping constraint: " . $e->getMessage() . "\n";
}
