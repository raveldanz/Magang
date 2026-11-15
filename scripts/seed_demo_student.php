<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use App\Models\StudentProfile;
use App\Models\University;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

$univ = University::first();

$user = User::firstOrCreate([
    'email' => 'calon.magang@unesa.ac.id',
], [
    'name' => 'Dimas Arya Pratama',
    'password' => bcrypt('password'),
    'role' => 'mahasiswa',
    'university_id' => $univ?->id,
    'university' => 'Universitas Negeri Surabaya',
]);

StudentProfile::firstOrCreate([
    'user_id' => $user->id,
], [
    'nim' => '22051204099',
    'universitas' => 'Universitas Negeri Surabaya',
    'jurusan' => 'Teknik Informatika',
    'phone' => '081234567899',
]);

echo 'User ready: '.$user->email."\n";
