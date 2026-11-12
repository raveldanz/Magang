<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$emails = [
    'admin@gmail.com',
    'admin@surabaya.go.id',
    'admin.dispusip@surabaya.go.id',
    'kepegawaian@dispusip.surabaya.go.id',
    'supervisor@dispusip.surabaya.go.id',
    'mentor.dispusip@surabaya.go.id',
    'admin@unesa.ac.id',
    'dosen.unesa@unesa.ac.id',
    'mahasiswa.aktif@unesa.ac.id'
];

echo "Checking Users:\n";
foreach ($emails as $email) {
    $user = User::where('email', $email)->first();
    if (!$user) {
        echo "[-] $email NOT FOUND\n";
    } else {
        $checkPass = Hash::check('password', $user->password) ? 'OK' : 'FAIL';
        $checkAdmin123 = Hash::check('admin123', $user->password) ? 'admin123' : '';
        echo "[+] ID: {$user->id} | {$user->name} | Email: {$user->email} | Role: {$user->role} | Pass 'password': $checkPass | Other: $checkAdmin123\n";
    }
}
