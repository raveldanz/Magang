<?php

/**
 * Account Quick Reference Sheet for Testing & Revisions
 * Run via: php scripts/dev_credentials.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "==========================================================================" . PHP_EOL;
echo "          DAFTAR AKUN TESTING SISTEM INFORMASI MAGANG" . PHP_EOL;
echo "==========================================================================" . PHP_EOL;

$users = User::orderBy('role')->orderBy('email')->get();

$grouped = $users->groupBy('role');

foreach ($grouped as $role => $roleUsers) {
    echo PHP_EOL . "=== ROLE: " . strtoupper($role) . " (" . $roleUsers->count() . " akun) ===" . PHP_EOL;
    foreach ($roleUsers as $u) {
        $agency = $u->agencyProfile ? " [Instansi: {$u->agencyProfile->agency_name}]" : "";
        $univ = $u->university ? " [Kampus: {$u->university}]" : "";
        $passHint = ($u->email === 'admin@gmail.com') ? 'admin123' : 'password';
        echo sprintf(" - %-38s | Pass: %-10s | %s%s%s", $u->email, $passHint, $u->name, $agency, $univ) . PHP_EOL;
    }
}

echo PHP_EOL . "==========================================================================" . PHP_EOL;
echo " Catatan: Sebagian besar password default adalah: 'password' (kecuali admin@gmail.com = admin123)" . PHP_EOL;
echo "==========================================================================" . PHP_EOL;
