<?php

/**
 * Health Check & Integrity Auditor for Sistem Informasi Manajemen Magang
 * Run via: php scripts/health_check.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

echo "=======================================================" . PHP_EOL;
echo "   SYSTEM HEALTH & FINISHING INTEGRITY CHECK" . PHP_EOL;
echo "=======================================================" . PHP_EOL;

$errors = 0;
$warnings = 0;

// 1. Check Database Connection
try {
    DB::connection()->getPdo();
    echo "[OK] Database Connection: Connected (" . config('database.default') . ")" . PHP_EOL;
} catch (\Exception $e) {
    echo "[FAIL] Database Connection: " . $e->getMessage() . PHP_EOL;
    $errors++;
}

// 2. Check Storage Link
$storageLink = public_path('storage');
if (file_exists($storageLink)) {
    echo "[OK] Storage Symlink: Active (" . $storageLink . ")" . PHP_EOL;
} else {
    echo "[WARN] Storage Symlink: Missing. Run 'php artisan storage:link'" . PHP_EOL;
    $warnings++;
}

// 3. Check App Key
if (config('app.key')) {
    echo "[OK] APP_KEY: Configured" . PHP_EOL;
} else {
    echo "[FAIL] APP_KEY: Missing! Run 'php artisan key:generate'" . PHP_EOL;
    $errors++;
}

// 4. Check Key Tables & Data Count
$tables = ['users', 'agency_profiles', 'universities', 'units', 'student_profiles', 'applications', 'placements', 'logbooks', 'final_reports', 'evaluations', 'certificates'];
echo PHP_EOL . "--- Table Records Overview ---" . PHP_EOL;
foreach ($tables as $t) {
    if (Schema::hasTable($t)) {
        $count = DB::table($t)->count();
        echo "  - Table '{$t}': {$count} rows" . PHP_EOL;
    } else {
        echo "  - Table '{$t}': [MISSING TABLE!]" . PHP_EOL;
        $errors++;
    }
}

// 5. Check User Roles Summary
if (Schema::hasTable('users')) {
    echo PHP_EOL . "--- User Accounts by Role ---" . PHP_EOL;
    $roles = User::select('role', DB::raw('count(*) as total'))->groupBy('role')->pluck('total', 'role');
    foreach ($roles as $role => $count) {
        echo "  - Role '{$role}': {$count} users" . PHP_EOL;
    }
}

// 6. Scan Blade Files for Debug code (dd, dump)
echo PHP_EOL . "--- Scan Blade Views for Remaining Debug Statements ---" . PHP_EOL;
$viewsDir = resource_path('views');
$bladeFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));
$debugFound = 0;
foreach ($bladeFiles as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        if (preg_match('/@(dd|dump|env\(\'local\'\))/i', $content, $matches)) {
            $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
            echo "  [WARN] Found '{$matches[0]}' in: {$relativePath}" . PHP_EOL;
            $debugFound++;
        }
    }
}
if ($debugFound === 0) {
    echo "  [OK] No debugging directives (@dd/@dump) found in Blade views." . PHP_EOL;
} else {
    $warnings += $debugFound;
}

echo PHP_EOL . "=======================================================" . PHP_EOL;
echo "   SUMMARY: {$errors} Error(s), {$warnings} Warning(s)" . PHP_EOL;
echo "=======================================================" . PHP_EOL;
