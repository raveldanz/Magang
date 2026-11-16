<?php

/**
 * Uji beban sederhana untuk server demo: meniru banyak peserta yang login lalu membuka halaman
 * dan dicek chat-nya secara bersamaan. Jalankan dari laptop lain di WiFi yang sama (atau dari
 * laptop server) SETELAH start_demo.bat berjalan.
 *
 *   php scripts/demo_loadtest.php --url=http://192.168.1.10:8000 --users=20 --seconds=60
 *
 * Opsi:
 *   --url       Alamat demo (bawaan http://127.0.0.1:8000)
 *   --users     Jumlah pengguna serentak (bawaan 20)
 *   --seconds   Lama uji per pengguna setelah login (bawaan 60)
 *   --think     Jeda rata-rata antar klik dalam milidetik (bawaan 1500, makin kecil makin berat)
 *   --password  Kata sandi akun demo (bawaan "password")
 *
 * Skrip hanya membaca halaman (GET) setelah login; tidak membuat atau mengubah data magang.
 * Login memang mencatat sesi dan "terakhir dilihat", sama seperti pengguna sungguhan.
 */
$options = getopt('', ['url::', 'users::', 'seconds::', 'think::', 'password::', 'child::', 'out::']);
$baseUrl = rtrim($options['url'] ?? 'http://127.0.0.1:8000', '/');
$users = max(1, (int) ($options['users'] ?? 20));
$seconds = max(5, (int) ($options['seconds'] ?? 60));
$think = max(0, (int) ($options['think'] ?? 1500));
$password = (string) ($options['password'] ?? 'password');

/** Akun demo dari DemoE2ESeeder: [email, halaman-halaman yang biasa dibuka peran itu] */
const ACCOUNTS = [
    ['mahasiswa.aktif@unesa.ac.id', ['/dashboard', '/student/logbook', '/student/profile', '/chat', '/notifications']],
    ['mentor.dispusip@surabaya.go.id', ['/mentor/dashboard', '/mentor/logbooks', '/chat', '/notifications']],
    ['dosen.unesa@unesa.ac.id', ['/lecturer/dashboard', '/lecturer/monitoring', '/chat', '/notifications']],
    ['admin.dispusip@surabaya.go.id', ['/admin/dashboard', '/admin/applications', '/admin/users', '/chat']],
    ['supervisor@dispusip.surabaya.go.id', ['/supervisor/dashboard', '/supervisor/applications', '/chat']],
    ['admin@unesa.ac.id', ['/university/dashboard', '/university/lecturers', '/chat']],
];

if (! function_exists('curl_init')) {
    fwrite(STDERR, "Ekstensi curl PHP tidak aktif.\n");
    exit(1);
}

// ---------------------------------------------------------------------------------------------
// Mode anak: satu pengguna virtual
// ---------------------------------------------------------------------------------------------
if (isset($options['child'])) {
    $index = (int) $options['child'];
    [$email, $pages] = ACCOUNTS[$index % count(ACCOUNTS)];
    $jar = tempnam(sys_get_temp_dir(), 'lt');
    $out = fopen($options['out'], 'ab');

    // Seperti browser: setelah gerbang mengarahkan ke salah satu server aplikasi, klik berikutnya
    // tetap ke server itu (alamat halaman ikut berpindah port).
    $origin = $baseUrl;
    $request = function (string $method, string $path, array $form = [], bool $json = false) use (&$origin, $jar, $out): array {
        $ch = curl_init($origin.$path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_COOKIEJAR => $jar,
            CURLOPT_COOKIEFILE => $jar,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER => $json ? ['Accept: application/json', 'X-Requested-With: XMLHttpRequest'] : ['Accept: text/html'],
        ]);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
        }
        $start = microtime(true);
        $body = (string) curl_exec($ch);
        $ms = (microtime(true) - $start) * 1000;
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_errno($ch) ? curl_error($ch) : null;
        $effective = parse_url((string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
        if (! $error && isset($effective['scheme'], $effective['host'])) {
            $origin = $effective['scheme'].'://'.$effective['host'].(isset($effective['port']) ? ':'.$effective['port'] : '');
        }
        curl_close($ch);
        fwrite($out, json_encode(['path' => preg_replace('/\d+/', '{id}', $path), 'ms' => round($ms, 1), 'status' => $status, 'error' => $error])."\n");

        return [$status, $body];
    };

    [, $loginPage] = $request('GET', '/login');
    if (! preg_match('/name="_token"\s+value="([^"]+)"/', $loginPage, $m)) {
        fwrite($out, json_encode(['path' => 'LOGIN', 'ms' => 0, 'status' => 0, 'error' => 'token CSRF tidak ditemukan'])."\n");
        exit(1);
    }
    [$status, $afterLogin] = $request('POST', '/login', ['_token' => $m[1], 'email' => $email, 'password' => $password]);
    if (str_contains($afterLogin, 'name="password"') && str_contains($afterLogin, 'name="email"')) {
        fwrite($out, json_encode(['path' => 'LOGIN', 'ms' => 0, 'status' => $status, 'error' => "login gagal untuk {$email}"])."\n");
        exit(1);
    }

    $until = microtime(true) + $seconds;
    $nextSummary = 0;
    while (microtime(true) < $until) {
        // Badge chat di setiap halaman di-poll berkala (config/chat.php → summary_visible)
        if (microtime(true) >= $nextSummary) {
            $request('GET', '/chat/api/summary', [], true);
            $nextSummary = microtime(true) + 15;
        }
        $page = $pages[array_rand($pages)];
        $request('GET', $page);
        if ($page === '/chat') {
            // Halaman chat mengambil daftar percakapan lalu mem-poll pesan baru tiap ±3 detik
            $request('GET', '/chat/api/conversations', [], true);
        }
        usleep((int) (($think * (0.5 + mt_rand() / mt_getrandmax())) * 1000));
    }
    @unlink($jar);
    exit(0);
}

// ---------------------------------------------------------------------------------------------
// Mode utama: jalankan semua pengguna sebagai proses terpisah, lalu ringkas hasilnya
// ---------------------------------------------------------------------------------------------
$resultFile = tempnam(sys_get_temp_dir(), 'ltres');
printf("Uji beban %s: %d pengguna serentak selama %d detik (jeda klik ±%d ms)...\n", $baseUrl, $users, $seconds, $think);

$procs = [];
for ($i = 0; $i < $users; $i++) {
    $cmd = [PHP_BINARY, __FILE__, '--child='.$i, '--out='.$resultFile, '--url='.$baseUrl, '--seconds='.$seconds, '--think='.$think, '--password='.$password];
    $procs[] = proc_open($cmd, [1 => ['file', 'NUL', 'w'], 2 => ['file', 'NUL', 'w']], $pipes);
    usleep(150_000); // login tidak dilakukan tepat bersamaan, seperti peserta sungguhan
}
foreach ($procs as $p) {
    proc_close($p);
}

$rows = array_values(array_filter(array_map(fn ($l) => json_decode($l, true), file($resultFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES))));
@unlink($resultFile);
if (! $rows) {
    fwrite(STDERR, "Tidak ada permintaan yang tercatat. Pastikan server demo berjalan di {$baseUrl}.\n");
    exit(1);
}

$percentile = function (array $values, float $p): float {
    sort($values);

    return $values[(int) min(count($values) - 1, floor($p * (count($values) - 1)))];
};

$failed = array_filter($rows, fn ($r) => $r['error'] || $r['status'] >= 500 || $r['status'] === 0);
$times = array_column($rows, 'ms');
$byPath = [];
foreach ($rows as $r) {
    $byPath[$r['path']][] = $r['ms'];
}
ksort($byPath);

echo PHP_EOL;
printf("%-34s %7s %9s %9s %9s\n", 'Halaman', 'Jumlah', 'Median', 'P95', 'Maks');
foreach ($byPath as $path => $values) {
    printf("%-34s %7d %7.0fms %7.0fms %7.0fms\n", $path, count($values), $percentile($values, 0.5), $percentile($values, 0.95), max($values));
}
echo str_repeat('-', 72).PHP_EOL;
printf("%-34s %7d %7.0fms %7.0fms %7.0fms\n", 'SEMUA', count($rows), $percentile($times, 0.5), $percentile($times, 0.95), max($times));
printf("Permintaan gagal (error/5xx/timeout): %d\n", count($failed));
foreach (array_slice($failed, 0, 5) as $f) {
    printf("  - %s → %s %s\n", $f['path'], $f['status'], $f['error'] ?? '');
}

$p95 = $percentile($times, 0.95);
echo PHP_EOL.match (true) {
    count($failed) > 0 => 'HASIL: ada permintaan gagal; server belum siap untuk jumlah pengguna ini.',
    $p95 <= 1500 => 'HASIL: lancar (95% halaman terbuka dalam 1,5 detik).',
    $p95 <= 3000 => 'HASIL: masih bisa dipakai, tetapi terasa agak lambat saat ramai.',
    default => 'HASIL: lambat saat ramai; kurangi jumlah pengguna serentak atau tambah server (DEMO_WORKERS).',
}.PHP_EOL;
exit(count($failed) > 0 ? 1 : 0);
