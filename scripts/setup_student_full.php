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

$placement = \App\Models\Placement::create([
    'application_id' => $application->id,
    'pembimbing_id' => $mentor?->id,
    'mentor_id' => $mentor?->id,
    'academic_advisor_id' => $dpl?->id
]);

// Seed 6 hari logbook awal (Hari 1 s/d Hari 6)
$days = [
    ['sub' => 6, 'act' => 'Hari 1: Orientasi magang, briefing aturan kerja instansi, perkenalan tim pengembang, dan setup workstation di Bidang CSIRT Diskominfo Surabaya.'],
    ['sub' => 5, 'act' => 'Hari 2: Mempelajari arsitektur jaringan Pemkot Surabaya, konfigurasi perimeter firewall, dan compliance keamanan informasi SPBE.'],
    ['sub' => 4, 'act' => 'Hari 3: Analisis log server dan monitoring anomali traffic mencurigakan pada sistem gateway portal layanan publik WargaKu.'],
    ['sub' => 3, 'act' => 'Hari 4: Hardening konfigurasi web server Nginx, pembaruan sertifikat SSL/TLS, dan evaluasi implementasi header HTTP security.'],
    ['sub' => 2, 'act' => 'Hari 5: Melakukan vulnerability scanning pada modul autentikasi internal menggunakan checklist OWASP Top 10 dan Burp Suite.'],
    ['sub' => 1, 'act' => 'Hari 6: Menyusun rekomendasi mitigasi celah keamanan web, validasi hasil pengujian patch update bersama tim teknis Diskominfo.'],
    ['sub' => 0, 'act' => 'Hari 7: Evaluasi mingguan bersama Mentor Lapangan CSIRT Diskominfo dan Dosen Pembimbing (DPL UNESA), menyelesaikan dokumentasi checklist keamanan sistem dan penyerahan resume 7 hari magang.']
];

foreach ($days as $item) {
    \App\Models\Logbook::create([
        'placement_id' => $placement->id,
        'date' => \Carbon\Carbon::now()->subDays($item['sub'])->toDateString(),
        'activity' => $item['act'],
        'status' => 'approved',
        'lecturer_status' => 'approved',
        'lecturer_feedback' => 'ACC Dosen: Aktivitas relevan dengan kompetensi kurikulum magang.',
        'lecturer_verified_at' => \Carbon\Carbon::now()->subDays($item['sub']),
        'created_at' => \Carbon\Carbon::now()->subDays($item['sub']),
        'updated_at' => \Carbon\Carbon::now()->subDays($item['sub'])
    ]);
}

echo "SUCCESS_6_DAYS\n";
