<?php

/**
 * Gerbang server demo (router untuk `php -S`).
 *
 * Server bawaan PHP hanya melayani satu permintaan pada satu waktu, sehingga 15-20 peserta yang
 * membuka aplikasi bersamaan saling menunggu. scripts/demo_serve.bat menjalankan beberapa server
 * aplikasi di port berurutan (alamat utama + 1, + 2, ...) dan gerbang ini di alamat utama.
 * Gerbang hanya mengarahkan setiap pengunjung ke server aplikasi berikutnya secara bergiliran;
 * setelah itu browser langsung berbicara dengan server tersebut.
 *
 * Sesi login tetap berlaku di semua port: cookie browser tidak dibedakan per port dan semua server
 * memakai folder sesi & database yang sama.
 *
 * Daftar port dibaca dari variabel lingkungan DEMO_WORKER_PORTS (mis. "8001,8002,8003").
 */
$ports = array_values(array_filter(array_map('intval', explode(',', (string) getenv('DEMO_WORKER_PORTS')))));
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

/** Server aplikasi yang sedang hidup (yang mati, mis. jendelanya tertutup, dilewati). */
$alive = function (int $port): bool {
    $socket = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.3);
    if (! $socket) {
        return false;
    }
    fclose($socket);

    return true;
};

if ($uri === '/__demo-status') {
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-store');
    foreach ($ports as $port) {
        echo "Server aplikasi port {$port}: ".($alive($port) ? 'hidup' : 'MATI').PHP_EOL;
    }

    return true;
}

// Giliran berikutnya disimpan di berkas kecil agar pembagian tetap merata
$counterFile = __DIR__.'/../storage/framework/cache/demo_gateway.counter';
$next = 0;
$handle = @fopen($counterFile, 'c+');
if ($handle && flock($handle, LOCK_EX)) {
    $next = (int) stream_get_contents($handle);
    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, (string) (($next + 1) % 1000000));
    flock($handle, LOCK_UN);
}
if ($handle) {
    fclose($handle);
}

$target = null;
for ($i = 0, $count = count($ports); $i < $count; $i++) {
    $port = $ports[($next + $i) % $count];
    if ($alive($port)) {
        $target = $port;
        break;
    }
}

if ($target === null) {
    http_response_code(503);
    header('Content-Type: text/html; charset=utf-8');
    header('Retry-After: 5');
    echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        .'<title>Server demo belum siap</title>'
        .'<body style="font-family:sans-serif;max-width:560px;margin:15vh auto;padding:0 16px;line-height:1.6">'
        .'<h1 style="font-size:1.4rem">Server demo belum siap</h1>'
        .'<p>Server aplikasi SIP-MAGANG sedang dinyalakan atau jendelanya tertutup. Tunggu beberapa detik lalu muat ulang halaman ini.</p>'
        .'<p>Bila tetap muncul, jalankan ulang <b>start_demo.bat</b> di laptop server.</p></body>';

    return true;
}

// Nama host yang dipakai pengunjung (IP WiFi atau localhost), tanpa port
$host = preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '127.0.0.1'));
if (! preg_match('/^(\[[0-9a-fA-F:]+\]|[A-Za-z0-9.-]+)$/', $host)) {
    $host = '127.0.0.1';
}

// 307 mempertahankan metode & isi formulir (POST); GET/HEAD cukup 302
http_response_code(in_array($method, ['GET', 'HEAD'], true) ? 302 : 307);
header('Cache-Control: no-store');
header('Location: http://'.$host.':'.$target.$uri);

return true;
