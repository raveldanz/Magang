<?php

use App\Models\Application;
use App\Models\Placement;
use App\Models\StudentProfile;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$email = $argv[1] ?? null;
if (! $email) {
    echo "ERROR: Email required\n";
    exit(1);
}

$user = User::where('email', $email)->first();
if (! $user) {
    echo "ERROR: User not found\n";
    exit(1);
}

$unesa = University::where('code', 'UNESA')->first();
$dpl = User::where('email', 'dosen.unesa@unesa.ac.id')->first();
$mentor = User::where('email', 'mentor.kominfo@surabaya.go.id')->first();
$unit = Unit::first();

$user->update(['university_id' => $unesa?->id, 'university' => $unesa?->name]);
$uniqueSuffix = substr($user->id.time(), -4);

StudentProfile::updateOrCreate(
    ['user_id' => $user->id],
    [
        'nim' => '22051204'.$uniqueSuffix,
        'jurusan' => 'S1 Sistem Informasi',
        'universitas' => 'Universitas Negeri Surabaya',
        'phone' => '081234567890',
        'alamat' => 'Jl. Ketintang Baru No. 10, Surabaya',
    ]
);

$application = Application::create([
    'user_id' => $user->id,
    'unit_id' => $unit->id,
    'start_date' => Carbon::now()->subDays(10)->toDateString(),
    'end_date' => Carbon::now()->addMonths(3)->toDateString(),
    'status' => 'accepted',
    'created_at' => now(),
    'updated_at' => now(),
]);

Placement::create([
    'application_id' => $application->id,
    'pembimbing_id' => $mentor?->id,
    'mentor_id' => $mentor?->id,
    'academic_advisor_id' => $dpl?->id,
]);

echo "SUCCESS\n";
