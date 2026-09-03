<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = $argv[1] ?? null;
if (!$email) {
    echo "ERROR: Email required\n";
    exit(1);
}

$user = \App\Models\User::where('email', $email)->first();
if (!$user) {
    echo "ERROR: User not found\n";
    exit(1);
}

$unesa = \App\Models\University::where('code', 'UNESA')->first();
$dpl = \App\Models\User::where('email', 'dosen.unesa@unesa.ac.id')->first();
$mentor = \App\Models\User::where('email', 'mentor.kominfo@surabaya.go.id')->first();
$unit = \App\Models\Unit::first();

$user->update(['university_id' => $unesa?->id, 'university' => $unesa?->name]);
$uniqueSuffix = substr($user->id . time(), -4);

\App\Models\StudentProfile::updateOrCreate(
    ['user_id' => $user->id],
    [
        'nim' => '22051204' . $uniqueSuffix,
        'jurusan' => 'S1 Sistem Informasi',
        'universitas' => 'Universitas Negeri Surabaya',
        'phone' => '081234567890',
        'alamat' => 'Jl. Ketintang Baru No. 10, Surabaya'
    ]
);

$application = \App\Models\Application::create([
    'user_id' => $user->id,
    'unit_id' => $unit->id,
    'start_date' => \Carbon\Carbon::now()->subDays(10)->toDateString(),
    'end_date' => \Carbon\Carbon::now()->addMonths(3)->toDateString(),
    'status' => 'accepted',
    'created_at' => now(),
    'updated_at' => now()
]);

\App\Models\Placement::create([
    'application_id' => $application->id,
    'pembimbing_id' => $mentor?->id,
    'mentor_id' => $mentor?->id,
    'academic_advisor_id' => $dpl?->id
]);

echo "SUCCESS\n";
