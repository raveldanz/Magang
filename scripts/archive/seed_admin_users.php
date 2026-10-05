<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\AgencyProfile;
use App\Models\University;
use Illuminate\Support\Facades\Hash;

echo "Seeding Admin accounts..." . PHP_EOL;

$defaultPassword = Hash::make('password');
$admin123 = Hash::make('admin123');

// 1. Super Admin Utama
$adminUtama = User::updateOrCreate(
    ['email' => 'admin@gmail.com'],
    [
        'name' => 'Administrator Utama',
        'password' => $admin123,
        'role' => 'admin',
        'status' => 'active',
        'email_verified_at' => now(),
    ]
);
echo "Created/Updated: admin@gmail.com | password: admin123" . PHP_EOL;

$superAdminGov = User::updateOrCreate(
    ['email' => 'admin@surabaya.go.id'],
    [
        'name' => 'Super Administrator Pemkot Surabaya',
        'password' => $defaultPassword,
        'role' => 'admin',
        'status' => 'active',
        'email_verified_at' => now(),
    ]
);
echo "Created/Updated: admin@surabaya.go.id | password: password" . PHP_EOL;

// 2. Instansi Pemkot Surabaya
$kominfo = AgencyProfile::firstOrCreate(
    ['id' => 1],
    [
        'government_name' => 'Pemerintah Kota Surabaya',
        'agency_name' => 'Dinas Komunikasi Dan Informatika',
        'address' => 'Jl. Jimerto No. 25-27, Ketabang, Genteng, Kota Surabaya',
        'email' => 'diskominfo@surabaya.go.id',
    ]
);

$dispusip = AgencyProfile::firstOrCreate(
    ['id' => 2],
    [
        'government_name' => 'Pemerintah Kota Surabaya',
        'agency_name' => 'Dinas Perpustakaan Dan Kearsipan',
        'address' => 'Jl. Rungkut Asri Tengah No. 5-7, Surabaya',
        'email' => 'dispusip@surabaya.go.id',
    ]
);

$dukcapil = AgencyProfile::firstOrCreate(
    ['id' => 3],
    [
        'government_name' => 'Pemerintah Kota Surabaya',
        'agency_name' => 'Dinas Kependudukan Dan Pencatatan Sipil',
        'address' => 'Jl. Manyar Kertoarjo No. 1, Surabaya',
        'email' => 'dispendukcapil@surabaya.go.id',
    ]
);

// 3. Admin Instansi Dinas
User::updateOrCreate(
    ['email' => 'admin.kominfo@surabaya.go.id'],
    [
        'name' => 'Admin Dinas Kominfo Surabaya',
        'password' => $defaultPassword,
        'role' => 'admin',
        'status' => 'active',
        'agency_profile_id' => $kominfo->id,
        'email_verified_at' => now(),
    ]
);
echo "Created/Updated: admin.kominfo@surabaya.go.id | password: password" . PHP_EOL;

User::updateOrCreate(
    ['email' => 'admin.diskominfo@surabaya.go.id'],
    [
        'name' => 'Admin Diskominfo Surabaya',
        'password' => $defaultPassword,
        'role' => 'admin',
        'status' => 'active',
        'agency_profile_id' => $kominfo->id,
        'email_verified_at' => now(),
    ]
);
echo "Created/Updated: admin.diskominfo@surabaya.go.id | password: password" . PHP_EOL;

User::updateOrCreate(
    ['email' => 'admin.dispusip@surabaya.go.id'],
    [
        'name' => 'Admin Dispusip Surabaya',
        'password' => $defaultPassword,
        'role' => 'admin',
        'status' => 'active',
        'agency_profile_id' => $dispusip->id,
        'email_verified_at' => now(),
    ]
);
echo "Created/Updated: admin.dispusip@surabaya.go.id | password: password" . PHP_EOL;

User::updateOrCreate(
    ['email' => 'admin.dispendukcapil@surabaya.go.id'],
    [
        'name' => 'Admin Dispendukcapil Surabaya',
        'password' => $defaultPassword,
        'role' => 'admin',
        'status' => 'active',
        'agency_profile_id' => $dukcapil->id,
        'email_verified_at' => now(),
    ]
);
echo "Created/Updated: admin.dispendukcapil@surabaya.go.id | password: password" . PHP_EOL;

// 4. Admin Universitas
$unesa = University::firstOrCreate(['code' => 'UNESA'], ['name' => 'Universitas Negeri Surabaya']);
$unitomo = University::firstOrCreate(['code' => 'UNITOMO'], ['name' => 'Universitas Dr. Soetomo']);

User::updateOrCreate(
    ['email' => 'admin@unesa.ac.id'],
    [
        'name' => 'Portal Kampus Universitas Negeri Surabaya',
        'password' => $defaultPassword,
        'role' => 'universitas',
        'status' => 'active',
        'university_id' => $unesa->id,
        'university' => $unesa->name,
        'email_verified_at' => now(),
    ]
);
echo "Created/Updated: admin@unesa.ac.id | password: password" . PHP_EOL;

User::updateOrCreate(
    ['email' => 'admin@unitomo.ac.id'],
    [
        'name' => 'Portal Kampus Universitas Dr. Soetomo',
        'password' => $defaultPassword,
        'role' => 'universitas',
        'status' => 'active',
        'university_id' => $unitomo->id,
        'university' => $unitomo->name,
        'email_verified_at' => now(),
    ]
);
echo "Created/Updated: admin@unitomo.ac.id | password: password" . PHP_EOL;

echo "Seeding completed successfully!" . PHP_EOL;
