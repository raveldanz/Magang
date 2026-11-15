<?php

/**
 * Uji asap per akun: masuk sebagai tiap akun demo lalu membuka semua halaman GET yang
 * tidak butuh parameter, ditambah halaman detail milik akun itu. Hanya membaca (GET),
 * tidak mengirim form. Dipakai untuk QC cepat sebelum demo.
 *
 *   php scripts/qc_smoke.php                     semua akun bawaan di bawah
 *   php scripts/qc_smoke.php email@contoh.id ... akun tertentu saja
 *   php scripts/qc_smoke.php --verbose           tampilkan juga halaman yang lolos
 *
 * Memakai database yang ditunjuk .env (atau variabel lingkungan DB_CONNECTION/DB_DATABASE).
 * Kode keluar 1 bila ada halaman yang gagal (HTTP 500 atau exception).
 */

use App\Models\Application;
use App\Models\Placement;
use App\Models\User;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$args = array_slice($argv, 1);
$verbose = in_array('--verbose', $args, true);
$emails = array_values(array_filter($args, fn ($a) => ! str_starts_with($a, '--')));

if ($emails === []) {
    $emails = [
        'admin@surabaya.go.id', 'admin.dispusip@surabaya.go.id', 'admin.sekretariat@surabaya.go.id',
        'supervisor@dispusip.surabaya.go.id', 'mentor.dispusip@surabaya.go.id', 'mentor.arsip@dispusip.surabaya.go.id',
        'dosen.unesa@unesa.ac.id', 'dosen.pens@pens.ac.id', 'admin@unesa.ac.id',
        'baru.daftar@unesa.ac.id', 'siap.ajukan@unesa.ac.id', 'fajar.revisi@unesa.ac.id', 'gilang.ditolak@unesa.ac.id',
        'mahasiswa.waiting@unesa.ac.id', 'dani.accepted@unesa.ac.id', 'mahasiswa.aktif@unesa.ac.id',
        'logbook.revisi@unesa.ac.id', 'laporan.menunggu@unesa.ac.id', 'laporan.revisi@unesa.ac.id',
        'siap.dinilai@unesa.ac.id', 'ganda.aktif@pens.ac.id', 'ganda.lulus@pens.ac.id', 'rina.mundur@unesa.ac.id',
        'mahasiswa.lulus@unesa.ac.id', 'haikal.ketua.lulus@unitomo.ac.id', 'hana.sertifikat@unesa.ac.id',
        'fauzan.ketua.aktif@unesa.ac.id', 'andi.ketua.pending@unesa.ac.id',
    ];
}

// Awalan URL yang relevan per peran; halaman umum (dashboard, profil, chat, notifikasi) selalu diuji.
$prefixes = [
    'super_admin' => ['admin', 'supervisor'],
    'admin' => ['admin'],
    'supervisor' => ['supervisor'],
    'mentor' => ['mentor', 'pembimbing'],
    'pembimbing' => ['mentor', 'pembimbing'],
    'dosen' => ['lecturer'],
    'academic_advisor' => ['lecturer'],
    'universitas' => ['university'],
    'mahasiswa' => ['student'],
];
$common = ['dashboard', 'profile', 'chat', 'notifications', 'feedbacks', 'proyek-dinas'];
$skip = ['logout', 'export', 'download', 'batch-print', 'impersonat', 'api/', 'sanctum', 'storage/', 'up', 'verify-email', 'confirm-password', 'backup'];

$getRoutes = [];
foreach (Route::getRoutes() as $route) {
    if (! in_array('GET', $route->methods(), true)) {
        continue;
    }
    $getRoutes[] = $route->uri();
}

$fetch = function (User $user, string $uri) use ($kernel) {
    Auth::guard('web')->setUser($user);
    $request = Request::create('/'.ltrim($uri, '/'), 'GET');
    try {
        $response = $kernel->handle($request);
        $status = $response->getStatusCode();
        $note = '';
        if ($status >= 500) {
            $ex = $response->exception ?? null;
            $note = $ex ? get_class($ex).': '.substr($ex->getMessage(), 0, 200).' @ '.basename($ex->getFile()).':'.$ex->getLine() : '';
        } elseif ($status >= 300 && $status < 400) {
            $note = '-> '.parse_url((string) $response->headers->get('Location'), PHP_URL_PATH);
        }
        $kernel->terminate($request, $response);
    } catch (Throwable $e) {
        $status = 599;
        $note = get_class($e).': '.substr($e->getMessage(), 0, 200).' @ '.basename($e->getFile()).':'.$e->getLine();
    }
    Auth::guard('web')->forgetUser();

    return [$status, $note];
};

$totalFail = 0;
$totalPages = 0;

foreach ($emails as $email) {
    $user = User::where('email', $email)->first();
    if (! $user) {
        echo "[LEWATI] {$email}: akun tidak ada di database ini\n";

        continue;
    }

    $allowed = array_merge($prefixes[$user->role] ?? [], $common);
    $uris = [];
    foreach ($getRoutes as $uri) {
        if (str_contains($uri, '{')) {
            continue;
        }
        $first = explode('/', $uri)[0];
        if (! in_array($first, $allowed, true)) {
            continue;
        }
        foreach ($skip as $s) {
            if (str_contains($uri, $s)) {
                continue 2;
            }
        }
        $uris[] = $uri;
    }

    // Halaman detail yang butuh ID, diambil dari data milik akun ini.
    if ($user->role === 'mahasiswa') {
        $application = Application::where('user_id', $user->id)->latest()->first();
        if ($application?->placement) {
            $uris[] = 'student/certificate/'.$application->placement->id;
        }
    } elseif (in_array($user->role, ['mentor', 'pembimbing'], true)) {
        foreach (Placement::where('mentor_id', $user->id)->orWhere('pembimbing_id', $user->id)->limit(6)->pluck('id') as $id) {
            $uris[] = "mentor/students/{$id}";
            $uris[] = "mentor/students/{$id}/evaluation";
        }
    } elseif (in_array($user->role, ['dosen', 'academic_advisor'], true)) {
        foreach (Placement::where('academic_advisor_id', $user->id)->limit(6)->pluck('id') as $id) {
            $uris[] = "lecturer/students/{$id}";
            $uris[] = "lecturer/students/{$id}/evaluation";
        }
    } elseif (in_array($user->role, ['admin', 'super_admin', 'supervisor'], true)) {
        $base = $user->role === 'supervisor' ? 'supervisor' : 'admin';
        foreach (Application::latest('id')->limit(4)->pluck('id') as $id) {
            $uris[] = "{$base}/applications/{$id}";
        }
        foreach (Placement::whereHas('certificate')->limit(3)->pluck('id') as $id) {
            $uris[] = "{$base}/certificates/{$id}/preview";
        }
    }

    $fail = [];
    $counts = [];
    foreach (array_unique($uris) as $uri) {
        [$status, $note] = $fetch($user, $uri);
        $counts[$status] = ($counts[$status] ?? 0) + 1;
        $totalPages++;
        if ($status >= 500) {
            $fail[] = "    GAGAL {$status} /{$uri}  {$note}";
        } elseif ($verbose) {
            echo "    {$status} /{$uri} {$note}\n";
        }
    }
    ksort($counts);
    $summary = implode(' ', array_map(fn ($s, $c) => "{$s}x{$c}", array_keys($counts), $counts));
    echo ($fail ? '[GAGAL] ' : '[OK]    ').str_pad($email, 42).' '.str_pad($user->role, 12).' '.$summary."\n";
    foreach ($fail as $line) {
        echo $line."\n";
    }
    $totalFail += count($fail);
}

echo "\nTotal halaman dibuka: {$totalPages}, gagal: {$totalFail}\n";
exit($totalFail > 0 ? 1 : 0);
