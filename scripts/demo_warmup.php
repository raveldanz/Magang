<?php

/**
 * Memanaskan server aplikasi demo sebelum peserta datang. Dipanggil scripts/demo_serve.bat.
 *
 *   php scripts/demo_warmup.php 8001,8002,8003
 *
 * Server yang baru menyala harus memuat & mengompilasi ratusan berkas PHP pada permintaan pertama.
 * Bila 20 peserta login tepat setelah start_demo.bat dijalankan, setiap server "dingin" sekaligus dan
 * login bisa memakan 10-20 detik. Skrip ini membuka halaman umum, login dengan akun demo, lalu membuka
 * dasbor & chat di setiap server (bersamaan), sehingga OPcache sudah terisi sebelum alamat dibagikan.
 * Kegagalan login (mis. akun demo diubah) diabaikan: halaman umum tetap memanaskan sebagian besar kode.
 */
$ports = array_values(array_filter(array_map('intval', explode(',', (string) ($argv[1] ?? '')))));
if (! $ports || ! function_exists('curl_multi_init')) {
    exit(0);
}

$jars = [];
foreach ($ports as $port) {
    $jars[$port] = tempnam(sys_get_temp_dir(), 'warm');
}

/** Satu langkah untuk semua server bersamaan; mengembalikan isi halaman per port. */
$step = function (string $method, string $path, ?callable $form = null) use ($ports, $jars): array {
    $multi = curl_multi_init();
    $handles = [];
    foreach ($ports as $port) {
        $ch = curl_init("http://127.0.0.1:{$port}{$path}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_COOKIEJAR => $jars[$port],
            CURLOPT_COOKIEFILE => $jars[$port],
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        if ($method === 'POST' && $form) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form($port)));
        }
        curl_multi_add_handle($multi, $ch);
        $handles[$port] = $ch;
    }
    do {
        $status = curl_multi_exec($multi, $running);
        if ($running) {
            curl_multi_select($multi, 1.0);
        }
    } while ($running && $status === CURLM_OK);

    $bodies = [];
    foreach ($handles as $port => $ch) {
        $bodies[$port] = (string) curl_multi_getcontent($ch);
        curl_multi_remove_handle($multi, $ch);
        curl_close($ch);
    }
    curl_multi_close($multi);

    return $bodies;
};

// Tunggu semua server menerima koneksi (maks. 30 detik)
$deadline = microtime(true) + 30;
foreach ($ports as $port) {
    while (microtime(true) < $deadline) {
        $socket = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.5);
        if ($socket) {
            fclose($socket);
            break;
        }
        usleep(300_000);
    }
}

$start = microtime(true);
$step('GET', '/');
$loginPages = $step('GET', '/login');
$tokens = [];
foreach ($loginPages as $port => $html) {
    $tokens[$port] = preg_match('/name="_token"\s+value="([^"]+)"/', $html, $m) ? $m[1] : '';
}
$step('POST', '/login', fn (int $port) => ['_token' => $tokens[$port], 'email' => 'mahasiswa.aktif@unesa.ac.id', 'password' => 'password']);
foreach (['/dashboard', '/chat', '/student/logbook', '/notifications'] as $path) {
    $step('GET', $path);
}

foreach ($jars as $jar) {
    @unlink($jar);
}
printf(" %d server aplikasi sudah dipanaskan (%.1f detik).\n", count($ports), microtime(true) - $start);
