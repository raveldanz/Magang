<?php

/**
 * Pemeriksaan & penyiapan sebelum demo lewat WiFi lokal. Dipanggil oleh start_demo.bat.
 *
 *   php scripts/demo_preflight.php            -> siapkan semuanya, tulis storage/framework/demo_server.txt
 *   php scripts/demo_preflight.php --check    -> hanya memeriksa, tidak mengubah apa pun
 *   php scripts/demo_preflight.php --pgsql    -> alihkan .env ke PostgreSQL (dipakai start_demo_kantor.bat);
 *                                                .env lama disalin ke .env.backup, nilai DB_* yang sudah ada dipertahankan
 *
 * Aman dijalankan berulang kali. Tidak pernah menghapus database yang sudah berisi data.
 */
$base = dirname(__DIR__);
chdir($base);
$checkOnly = in_array('--check', $argv, true);
$php = PHP_BINARY;
$errors = [];
$warnings = [];

$say = fn (string $label, string $msg) => print str_pad("[$label]", 12).$msg.PHP_EOL;
$artisan = function (string $args) use ($php, $base): array {
    exec('"'.$php.'" "'.$base.DIRECTORY_SEPARATOR.'artisan" '.$args.' 2>&1', $out, $code);

    return [$code, implode(PHP_EOL, $out)];
};

// 1. PHP & ekstensi -----------------------------------------------------------------------
if (version_compare(PHP_VERSION, '8.3.0', '<')) {
    $errors[] = 'PHP '.PHP_VERSION.' terlalu lama. Aplikasi butuh PHP 8.3 atau lebih baru.';
}
$missing = array_values(array_filter(
    ['pdo_sqlite', 'sqlite3', 'mbstring', 'openssl', 'fileinfo', 'gd', 'dom', 'xml', 'ctype', 'tokenizer', 'curl', 'zip'],
    fn ($ext) => ! extension_loaded($ext)
));
if ($missing) {
    $errors[] = 'Ekstensi PHP belum aktif: '.implode(', ', $missing).'. Aktifkan di '.(php_ini_loaded_file() ?: 'php.ini').' (hapus tanda ; di depan extension=...).';
}
$say('PHP', PHP_VERSION.' ('.$php.')');

// 2. Berkas wajib -------------------------------------------------------------------------
if (! is_file($base.'/vendor/autoload.php')) {
    $errors[] = 'Folder vendor/ tidak ada. Jalankan "composer install" atau pakai zip buatan scripts/make_demo_zip.ps1.';
}
if (! is_file($base.'/public/build/manifest.json')) {
    $errors[] = 'Aset tampilan (public/build) belum ada. Jalankan "npm install" lalu "npm run build".';
}
if ($errors) {
    goto finish;
}

if (! $checkOnly) {
    // Vite dev server hanya melayani 127.0.0.1: bila berkas ini tertinggal, CSS/JS gagal dimuat di perangkat lain.
    @unlink($base.'/public/hot');

    foreach (['storage/app/public', 'storage/app/private', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs', 'bootstrap/cache'] as $dir) {
        if (! is_dir($base.'/'.$dir)) {
            mkdir($base.'/'.$dir, 0777, true);
        }
    }

    // Cache config/route menyimpan path absolut komputer asal: harus dibuang setelah proyek dipindah.
    foreach (glob($base.'/bootstrap/cache/*.php') ?: [] as $cached) {
        @unlink($cached);
    }
}

// 3. .env ---------------------------------------------------------------------------------
if (! is_file($base.'/.env')) {
    if ($checkOnly) {
        $errors[] = 'Berkas .env tidak ada.';
        goto finish;
    }
    $env = file_get_contents($base.'/.env.example');
    $env = preg_replace('/^APP_NAME=.*$/m', 'APP_NAME="SIM Magang Surabaya"', $env);
    $env = preg_replace('/^SESSION_DRIVER=.*$/m', 'SESSION_DRIVER=file', $env);
    $env = preg_replace('/^CACHE_STORE=.*$/m', 'CACHE_STORE=file', $env);
    file_put_contents($base.'/.env', $env);
    $say('ENV', '.env dibuat dari .env.example (SQLite).');
}
$envText = (string) file_get_contents($base.'/.env');

$envValue = function (string $key) use (&$envText): ?string {
    return preg_match('/^'.preg_quote($key, '/').'=(.*)$/m', $envText, $mm) ? trim(trim($mm[1]), '"\'') : null;
};
$envSet = function (string $key, string $value) use (&$envText): void {
    $line = $key.'='.$value;
    $envText = preg_match('/^'.preg_quote($key, '/').'=/m', $envText)
        ? preg_replace_callback('/^'.preg_quote($key, '/').'=.*$/m', fn () => $line, $envText)
        : rtrim($envText).PHP_EOL.$line.PHP_EOL;
};

$forcePgsql = in_array('--pgsql', $argv, true);
if ($forcePgsql && ! $checkOnly) {
    $before = $envText;
    $wasPgsql = $envValue('DB_CONNECTION') === 'pgsql';

    // Wajib untuk mode kantor
    $envSet('DB_CONNECTION', 'pgsql');
    $envSet('SESSION_LIFETIME', '480');
    $envSet('QUEUE_CONNECTION', 'database');

    // Nilai bawaan hanya dipakai bila belum diisi: kredensial yang sudah disetel tidak ditimpa
    $pgDefaults = ['DB_HOST' => '127.0.0.1', 'DB_PORT' => '5432', 'DB_DATABASE' => 'magang', 'DB_USERNAME' => 'postgres', 'DB_PASSWORD' => 'postgres'];
    foreach ($pgDefaults as $k => $v) {
        $current = $envValue($k);
        $leftoverSqlite = $k === 'DB_DATABASE' && $current !== null && preg_match('/\.sqlite$/i', $current);
        if ($current === null || $leftoverSqlite || (! $wasPgsql && $k !== 'DB_PASSWORD' && $current === '')) {
            $envSet($k, $v);
        }
    }

    if ($envText !== $before) {
        if (! $wasPgsql) {
            copy($base.'/.env', $base.'/.env.backup');
            $say('ENV', '.env sebelumnya disalin ke .env.backup (salin balik untuk kembali ke pengaturan lama).');
        }
        file_put_contents($base.'/.env', $envText);
    }
    $say('ENV', 'Mode PostgreSQL ('.$envValue('DB_DATABASE').'@'.$envValue('DB_HOST').':'.$envValue('DB_PORT').', sesi 8 jam).');
} elseif ($forcePgsql && $envValue('DB_CONNECTION') !== 'pgsql') {
    $warnings[] = '.env belum memakai PostgreSQL. Jalankan tanpa --check agar dialihkan.';
}

if (in_array(strtolower((string) $envValue('APP_DEBUG')), ['true', '1'], true)) {
    $warnings[] = 'APP_DEBUG=true: halaman error menampilkan detail internal ke semua perangkat di WiFi. start_demo.bat dan start_demo_kantor.bat sudah memaksanya mati; peringatan ini hanya berlaku bila server dijalankan dengan cara lain.';
}

if (! preg_match('/^APP_KEY=\S+/m', $envText) && ! $checkOnly) {
    [$code, $out] = $artisan('key:generate --force');
    $code === 0 ? $say('ENV', 'APP_KEY dibuat.') : $errors[] = 'Gagal membuat APP_KEY: '.$out;
}
preg_match('/^DB_CONNECTION=(\S+)/m', $envText, $m);
$driver = $m[1] ?? 'sqlite';
preg_match('/^DB_DATABASE=(\S+)/m', $envText, $m);
$dbPath = $m[1] ?? 'database/database.sqlite';
if ($driver === 'sqlite' && ! preg_match('#^([A-Za-z]:[\\\\/]|/)#', $dbPath)) {
    $dbPath = $base.'/'.$dbPath;
}

// 4. Database -----------------------------------------------------------------------------
$fresh = false;
if ($driver === 'sqlite') {
    if (! is_file($dbPath) || filesize($dbPath) === 0) {
        if ($checkOnly) {
            $errors[] = 'Database SQLite belum ada: '.$dbPath;
            goto finish;
        }
        touch($dbPath);
        $fresh = true;
    }
} elseif ($driver === 'pgsql') {
    if (! extension_loaded('pdo_pgsql')) {
        $errors[] = 'Ekstensi PHP pdo_pgsql belum aktif. Aktifkan extension=pdo_pgsql dan extension=pgsql di '.(php_ini_loaded_file() ?: 'php.ini').'.';
        goto finish;
    }
    $pgHost = $envValue('DB_HOST') ?: '127.0.0.1';
    $pgPort = $envValue('DB_PORT') ?: '5432';
    $pgDb = $envValue('DB_DATABASE') ?: 'magang';
    $pgUser = $envValue('DB_USERNAME') ?: 'postgres';
    $pgPass = $envValue('DB_PASSWORD') ?? '';
    $pgConnect = fn (string $db) => new PDO("pgsql:host={$pgHost};port={$pgPort};dbname={$db}", $pgUser, $pgPass, [PDO::ATTR_TIMEOUT => 3, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // PDO melaporkan gagal sambung sebagai 08006; kode asli server (3D000) hanya ada di teks pesannya
    $dbMissing = fn (Throwable $e) => (bool) preg_match('/3D000|database "[^"]*" does not exist/i', $e->getMessage());

    $pdo = null;
    $pgError = null;
    // Server yang baru dinyalakan butuh beberapa detik sampai menerima koneksi
    for ($attempt = 1; $attempt <= ($checkOnly ? 1 : 8) && ! $pdo; $attempt++) {
        try {
            $pdo = $pgConnect($pgDb);
        } catch (Throwable $e) {
            $pgError = $e;
            if ($dbMissing($e)) {
                break; // database belum ada: ditangani di bawah
            }
            if (preg_match('/28P01|28000|password authentication/i', $e->getMessage())) {
                break; // kata sandi salah: mengulang tidak membantu
            }
            sleep(1);
        }
    }

    if (! $pdo && $pgError && $dbMissing($pgError) && ! $checkOnly) {
        if (! preg_match('/^[A-Za-z0-9_]+$/', $pgDb)) {
            $errors[] = "Nama database \"{$pgDb}\" tidak valid untuk dibuat otomatis. Buat database itu secara manual.";
            goto finish;
        }
        try {
            $pgConnect('postgres')->exec('CREATE DATABASE "'.$pgDb.'"');
            $pdo = $pgConnect($pgDb);
            $say('DB', "Database PostgreSQL \"{$pgDb}\" belum ada, sudah dibuat.");
        } catch (Throwable $e) {
            $pgError = $e;
        }
    }

    if (! $pdo) {
        $reason = $pgError ? $pgError->getMessage() : 'tidak diketahui';
        $hint = preg_match('/28P01|28000|password authentication/i', $reason)
            ? 'Periksa DB_USERNAME/DB_PASSWORD di .env.'
            : "Pastikan PostgreSQL berjalan di {$pgHost}:{$pgPort} dan database \"{$pgDb}\" ada.";
        $errors[] = "Gagal terhubung ke PostgreSQL: {$reason}. {$hint}";
        goto finish;
    }

    $hasUsers = $pdo->query("SELECT to_regclass('public.users')")->fetchColumn() !== null;
    if ($hasUsers) {
        $userCount = (int) $pdo->query('SELECT count(*) FROM users')->fetchColumn();
        $say('DB', "PostgreSQL terhubung (database: {$pgDb}, {$userCount} pengguna terdaftar).");
    } elseif ($checkOnly) {
        $warnings[] = "Database PostgreSQL \"{$pgDb}\" masih kosong: akan dimigrasi dan diisi data demo saat dijalankan tanpa --check.";
    } else {
        $say('DB', "PostgreSQL terhubung, database \"{$pgDb}\" masih kosong.");
        $fresh = true;
    }
    $pdo = null;
} else {
    $warnings[] = "DB_CONNECTION=$driver: pastikan server database itu hidup di komputer ini. Untuk demo paling aman pakai sqlite.";
}
// Mode --check: laporkan migrasi tertunda & daftar sekolah tanpa mengubah database
if ($checkOnly && ! $errors && ! $fresh && ($driver !== 'pgsql' || ! empty($hasUsers))) {
    [$code, $out] = $artisan('migrate:status');
    $pending = preg_match_all('/\bPending\b/', $out);
    if ($code !== 0) {
        $warnings[] = 'Status migrasi tidak bisa dibaca:'.PHP_EOL.trim($out);
    } elseif ($pending > 0) {
        $warnings[] = "{$pending} migrasi database belum dijalankan, sehingga sebagian halaman bisa galat. start_demo.bat menjalankannya otomatis; atau jalankan: php artisan migrate";
    } else {
        $say('DB', 'Skema database sudah terbaru (tidak ada migrasi tertunda).');
        [$code, $out] = $artisan('tinker --execute="echo \'SEKOLAH=\'.App\Models\University::where(\'institution_type\', \'sekolah\')->count();"');
        $schools = preg_match('/SEKOLAH=(\d+)/', $out, $sm) ? (int) $sm[1] : null;
        if ($schools === 0) {
            $warnings[] = 'Daftar sekolah (SMA/SMK/MA) belum diimpor: pilihan "Sekolah" di profil peserta akan kosong. start_demo.bat mengimpornya otomatis; atau jalankan: php artisan universities:sync-master --only-schools';
        } elseif ($schools !== null) {
            $say('SEKOLAH', "{$schools} sekolah (SMA/SMK/MA) siap dipilih di profil peserta.");
        }
    }
}

if (! $checkOnly && ! $errors) {
    [$code, $out] = $artisan('optimize:clear');
    [$code, $out] = $artisan($fresh ? 'migrate --force --seed' : 'migrate --force');
    if ($code !== 0) {
        $errors[] = 'Migrasi database gagal:'.PHP_EOL.$out;
        goto finish;
    }
    $say('DB', $fresh ? 'Database baru dibuat dan diisi data demo.' : 'Skema database sudah terbaru.');

    // Master sekolah SMA/SMK/MA (pilihan "Sekolah" di profil peserta); aman diulang, tidak menggandakan
    [$code, $out] = $artisan('universities:sync-master --only-schools');
    $code === 0
        ? $say('SEKOLAH', 'Daftar sekolah (SMA/SMK/MA) siap dipilih di profil peserta.')
        : $warnings[] = 'Impor daftar sekolah gagal; pilihan sekolah di profil peserta bisa kosong:'.PHP_EOL.$out;

    // 5. Tautan public/storage (junction Windows tidak ikut ter-zip dengan benar) -----------
    $link = $base.'/public/storage';
    $target = realpath($base.'/storage/app/public');
    if (realpath($link) !== $target) {
        if (is_link($link) || is_file($link)) {
            @unlink($link);
        }
        if (file_exists($link) || is_link($link)) {
            // junction / folder hasil ekstrak zip
            exec('cmd /c rmdir "'.str_replace('/', '\\', $link).'" 2>&1', $o, $c);
        }
        if (file_exists($link)) {
            $warnings[] = 'public/storage berupa folder biasa (bukan tautan). Hapus folder itu lalu jalankan ulang agar logo/gambar tampil.';
        } else {
            [$code, $out] = $artisan('storage:link');
            if ($code !== 0 || realpath($link) !== $target) {
                exec('cmd /c mklink /J "'.str_replace('/', '\\', $link).'" "'.$target.'" 2>&1', $o, $c);
            }
            realpath($link) === $target
                ? $say('STORAGE', 'Tautan public/storage dibuat ulang.')
                : $warnings[] = 'Tautan public/storage gagal dibuat: logo/gambar unggahan tidak akan tampil.';
        }
    }

    // 6. Yang biasanya dikerjakan scheduler harian: mahasiswa "Diterima" jadi "Aktif" saat tanggal mulai tiba
    $artisan('app:sync-internship-status');

    // 7. Kompilasi semua tampilan Blade sekarang. Demo menjalankan beberapa server sekaligus; bila mereka
    //    mengompilasi tampilan yang sama bersamaan, Windows menolak menimpa berkas yang sedang dibuka
    //    ("rename ... Access is denied") dan peserta mendapat galat 500.
    [$code, $out] = $artisan('view:cache');
    $code === 0
        ? $say('TAMPILAN', 'Semua tampilan sudah dikompilasi (server tidak perlu mengompilasi saat ramai).')
        : $warnings[] = 'Kompilasi tampilan gagal; halaman pertama bisa lambat atau galat saat ramai:'.PHP_EOL.$out;
}

// 7. Batas unggah (hanya info: start_demo.bat menaikkannya lewat opsi -d) ------------------
// 8. Jaringan -----------------------------------------------------------------------------
$ips = [];
$sock = @stream_socket_client('udp://192.0.2.1:9', $errno, $errstr, 1);
if ($sock) {
    $name = stream_socket_get_name($sock, false);
    fclose($sock);
    if ($name && ($ip = substr($name, 0, strrpos($name, ':'))) && $ip !== '0.0.0.0') {
        $ips[] = $ip;
    }
}
foreach ((array) @gethostbynamel(gethostname()) as $ip) {
    $ips[] = $ip;
}
$ips = array_values(array_unique(array_filter($ips, fn ($ip) => filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
    && ! str_starts_with($ip, '127.') && ! str_starts_with($ip, '169.254.'))));
// Utamakan alamat jaringan privat (WiFi/LAN)
usort($ips, fn ($a, $b) => (int) (! preg_match('/^(192\.168|10|172\.(1[6-9]|2\d|3[01]))\./', $a)) <=> (int) (! preg_match('/^(192\.168|10|172\.(1[6-9]|2\d|3[01]))\./', $b)));

// Server bawaan PHP hanya melayani satu permintaan pada satu waktu. Agar 15-20 peserta tetap lancar,
// demo_serve.bat menjalankan beberapa server aplikasi (port utama + 1 ... + N) dan sebuah gerbang di
// port utama yang membagi pengunjung bergiliran (scripts/demo_gateway.php). Jumlah server mengikuti
// jumlah inti prosesor (4-8), atau DEMO_WORKERS di lingkungan / .env.
$envWorkers = getenv('DEMO_WORKERS') ?: (preg_match('/^DEMO_WORKERS=(\d+)/m', (string) @file_get_contents($base.'/.env'), $wm) ? $wm[1] : null);
$workers = $envWorkers !== null && $envWorkers !== false
    ? max(1, min(16, (int) $envWorkers))
    : max(4, min(8, (int) (getenv('NUMBER_OF_PROCESSORS') ?: 4)));

$portFree = function (int $candidate): bool {
    $probe = @fsockopen('127.0.0.1', $candidate, $e1, $e2, 0.3);
    if ($probe) {
        fclose($probe);

        return false;
    }

    return true;
};

$port = null;
foreach ([8000, 8080, 8100, 8200, 8300] as $candidate) {
    $block = range($candidate, $candidate + $workers);
    if (count(array_filter($block, $portFree)) === count($block)) {
        $port = $candidate;
        break;
    }
}
if ($port === null) {
    $errors[] = "Tidak ada deretan {$workers} port kosong di sekitar 8000/8080/8100/8200/8300. Tutup server lain terlebih dahulu.";
} elseif ($port !== 8000) {
    $warnings[] = 'Port 8000-'.(8000 + $workers)." sebagian sedang dipakai program lain (mungkin server dev yang masih terbuka), demo memakai port $port.";
}

// OPcache menyimpan hasil kompilasi PHP di memori: tanpa ini setiap klik mengompilasi ulang ratusan berkas Laravel.
$opcache = 0; // 0 = tidak tersedia, 1 = perlu dimuat lewat -d zend_extension=opcache, 2 = sudah dimuat php.ini
if (extension_loaded('Zend OPcache')) {
    $opcache = 2;
} else {
    exec('"'.$php.'" -d zend_extension=opcache -r "echo extension_loaded(\'Zend OPcache\') ? \'OPCACHE=1\' : \'OPCACHE=0\';" 2>&1', $oc);
    $opcache = str_contains(implode('', $oc), 'OPCACHE=1') ? 1 : 0;
}
if ($opcache === 0) {
    $warnings[] = 'OPcache PHP tidak tersedia (ext/php_opcache.dll tidak ada): aplikasi tetap jalan, tetapi lebih lambat saat ramai.';
}
if ($port !== null) {
    $say('SERVER', "{$workers} server aplikasi + gerbang pembagi beban".($opcache ? ', OPcache aktif' : '')
        .($workers >= 4 && $opcache ? ' (diuji lancar untuk 20 pengguna serentak).' : '.'));
}
if (! $ips) {
    $warnings[] = 'Laptop ini belum terhubung ke WiFi/LAN. Sambungkan dulu, lalu jalankan ulang agar alamat untuk perangkat lain muncul.';
}

// 9. Windows Firewall ---------------------------------------------------------------------
if (PHP_OS_FAMILY === 'Windows') {
    $ps = '$r = Get-NetFirewallApplicationFilter -Program \''.str_replace("'", "''", $php).'\' -ErrorAction SilentlyContinue | Get-NetFirewallRule | Where-Object { $_.Enabled -eq \'True\' -and $_.Direction -eq \'Inbound\' };'
        .' \'B=\' + @($r | Where-Object { $_.Action -eq \'Block\' }).Count + \' A=\' + @($r | Where-Object { $_.Action -eq \'Allow\' }).Count';
    $fw = (string) @shell_exec('powershell -NoProfile -ExecutionPolicy Bypass -Command "'.$ps.'" 2>NUL');
    if (preg_match('/B=(\d+) A=(\d+)/', $fw, $f)) {
        if ((int) $f[1] > 0) {
            $warnings[] = 'WINDOWS FIREWALL MEMBLOKIR php.exe: perangkat lain TIDAK akan bisa membuka aplikasi. Klik kanan izinkan_firewall.bat > Run as administrator, lalu jalankan ulang.';
        } elseif ((int) $f[2] === 0) {
            $warnings[] = 'Belum ada izin Windows Firewall untuk php.exe. Bila muncul jendela "Windows Security Alert", centang Private DAN Public lalu Allow access; atau jalankan izinkan_firewall.bat sebagai administrator.';
        }
    }
}

finish:
echo PHP_EOL;
foreach ($warnings as $w) {
    $say('PERHATIAN', $w);
}
foreach ($errors as $e) {
    $say('GAGAL', $e);
}
if ($errors) {
    exit(1);
}
if (! $checkOnly) {
    // Dibaca start_demo.bat / demo_serve.bat: IP  PORT_UTAMA  JUMLAH_SERVER  MODE_OPCACHE
    file_put_contents($base.'/storage/framework/demo_server.txt', ($ips[0] ?? '127.0.0.1').' '.$port.' '.$workers.' '.$opcache.PHP_EOL);
}
echo PHP_EOL.'  Di laptop ini      : http://localhost:'.$port.PHP_EOL;
foreach ($ips as $ip) {
    echo '  Dari HP/laptop lain: http://'.$ip.':'.$port.'   (harus di WiFi yang sama)'.PHP_EOL;
}
echo PHP_EOL;
exit(0);
