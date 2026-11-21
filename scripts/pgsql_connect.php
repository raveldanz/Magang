<?php

/**
 * Menyambungkan SIP-MAGANG ke server PostgreSQL yang sudah terpasang (mis. PC kantor).
 * Dipanggil oleh SAMBUNGKAN_POSTGRESQL.bat; nilai koneksi dibaca dari variabel lingkungan:
 *   SIP_PG_HOST, SIP_PG_PORT, SIP_PG_USER, SIP_PG_PASSWORD, SIP_PG_DATABASE, SIP_PG_SYNC (Y/T)
 *
 * Langkah: cek ekstensi -> uji login -> buat database bila belum ada -> tulis .env (lama disalin ke
 * .env.sqlite.backup) -> migrasi + data demo bila database kosong -> (opsional) ambil daftar sekolah nasional.
 * Kata sandi tidak pernah dicetak ke layar.
 */
$base = dirname(__DIR__);
$php = PHP_BINARY;
$say = fn (string $label, string $msg) => print str_pad("[$label]", 10).$msg.PHP_EOL;
$fail = function (string $msg, array $tips = []) use ($say): never {
    $say('GAGAL', $msg);
    foreach ($tips as $tip) {
        echo '          - '.$tip.PHP_EOL;
    }
    exit(1);
};
$artisan = function (string $args) use ($php, $base): array {
    exec('"'.$php.'" "'.$base.DIRECTORY_SEPARATOR.'artisan" '.$args.' 2>&1', $out, $code);

    return [$code, implode(PHP_EOL, $out)];
};

$host = trim((string) getenv('SIP_PG_HOST')) ?: '127.0.0.1';
$port = trim((string) getenv('SIP_PG_PORT')) ?: '5432';
$user = trim((string) getenv('SIP_PG_USER')) ?: 'postgres';
$pass = (string) getenv('SIP_PG_PASSWORD');
$dbName = strtolower(trim((string) getenv('SIP_PG_DATABASE')) ?: 'sip_magang');
$syncSchools = strtoupper(trim((string) getenv('SIP_PG_SYNC'))) === 'Y';

// 1. Validasi isian -----------------------------------------------------------------------------
if (! extension_loaded('pdo_pgsql')) {
    $fail('Ekstensi PHP pdo_pgsql belum aktif.', ['Pakai PHP bawaan zip (folder php\\), atau aktifkan extension=pdo_pgsql dan extension=pgsql di '.(php_ini_loaded_file() ?: 'php.ini').'.']);
}
if (! preg_match('/^[A-Za-z0-9.\-]+$/', $host)) {
    $fail('Alamat server tidak valid: '.$host);
}
if (! ctype_digit($port) || (int) $port < 1 || (int) $port > 65535) {
    $fail('Port tidak valid: '.$port);
}
if (! preg_match('/^[a-z_][a-z0-9_]{0,62}$/', $dbName)) {
    $fail('Nama database hanya boleh huruf kecil, angka, dan garis bawah (contoh: sip_magang).');
}
if (! preg_match('/^[A-Za-z0-9_.\-]+$/', $user)) {
    $fail('Nama pengguna PostgreSQL tidak valid: '.$user);
}
if (str_contains($pass, "'") || str_contains($pass, "\n")) {
    $fail('Kata sandi berisi tanda petik tunggal (\') yang tidak didukung berkas .env.', ['Buat pengguna PostgreSQL khusus aplikasi dengan kata sandi tanpa tanda petik, lalu ulangi.']);
}

// 2. Uji login ke database pemeliharaan "postgres" -----------------------------------------------
$connect = function (string $database) use ($host, $port, $user, $pass): PDO {
    return new PDO("pgsql:host={$host};port={$port};dbname={$database};connect_timeout=8", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
};
try {
    $admin = $connect('postgres');
} catch (PDOException $e) {
    $m = $e->getMessage();
    if (stripos($m, 'password authentication failed') !== false || stripos($m, 'no password supplied') !== false) {
        $fail('Login ditolak: nama pengguna atau kata sandi PostgreSQL salah.', ['Kata sandi ini yang diisi saat memasang PostgreSQL (pengguna bawaan: postgres).']);
    }
    if (stripos($m, 'does not exist') !== false && stripos($m, 'database') !== false) {
        $admin = null; // server tanpa database "postgres": langsung coba database tujuan
    } else {
        $fail('Tidak bisa terhubung ke PostgreSQL di '.$host.':'.$port.'.', [
            'Pastikan layanan PostgreSQL menyala (services.msc -> postgresql-x64-NN -> Start).',
            'Periksa port (bawaan 5432) di pgAdmin atau postgresql.conf.',
            'Bila server di komputer lain: izinkan alamat laptop ini di pg_hba.conf dan buka port di firewall server.',
            'Pesan teknis: '.preg_replace('/\s+/', ' ', $m),
        ]);
    }
}
$version = $admin ? (string) $admin->query('SHOW server_version')->fetchColumn() : '';
$say('OK', 'Terhubung ke PostgreSQL '.($version ?: '').' di '.$host.':'.$port.' sebagai '.$user.'.');
if ($version !== '' && version_compare($version, '12', '<')) {
    $fail('PostgreSQL '.$version.' terlalu lama. Gunakan PostgreSQL 12 atau lebih baru (disarankan 16).');
}

// 3. Database tujuan --------------------------------------------------------------------------
$exists = false;
if ($admin) {
    $stmt = $admin->prepare('SELECT 1 FROM pg_database WHERE datname = ?');
    $stmt->execute([$dbName]);
    $exists = (bool) $stmt->fetchColumn();
    if (! $exists) {
        try {
            $admin->exec('CREATE DATABASE "'.$dbName.'" ENCODING \'UTF8\' TEMPLATE template0');
            $say('OK', 'Database "'.$dbName.'" dibuat.');
        } catch (PDOException $e) {
            $fail('Database "'.$dbName.'" belum ada dan tidak bisa dibuat (pengguna '.$user.' tidak punya izin CREATEDB).', [
                'Buat database itu lewat pgAdmin (klik kanan Databases -> Create -> Database), lalu ulangi.',
            ]);
        }
    }
}
try {
    $pdo = $connect($dbName);
} catch (PDOException $e) {
    $fail('Tidak bisa membuka database "'.$dbName.'": '.preg_replace('/\s+/', ' ', $e->getMessage()));
}
$hasUsers = (bool) $pdo->query("SELECT to_regclass('public.users') IS NOT NULL")->fetchColumn()
    && (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0;
$pdo = null;
$admin = null;
if ($hasUsers) {
    $say('INFO', 'Database "'.$dbName.'" sudah berisi data: data itu dipakai apa adanya (tidak ditimpa).');
}

// 4. Tulis .env -----------------------------------------------------------------------------
$envPath = $base.'/.env';
$envText = is_file($envPath) ? (string) file_get_contents($envPath) : (string) file_get_contents($base.'/.env.example');
$wasPgsql = (bool) preg_match('/^DB_CONNECTION=pgsql\s*$/m', $envText);
$set = function (string $key, string $value) use (&$envText): void {
    $line = $key.'='.$value;
    $pattern = '/^#?\s*'.preg_quote($key, '/').'=.*$/m';
    $envText = preg_match($pattern, $envText)
        ? preg_replace_callback($pattern, fn () => $line, $envText, 1)
        : rtrim($envText).PHP_EOL.$line.PHP_EOL;
};
$set('DB_CONNECTION', 'pgsql');
$set('DB_HOST', $host);
$set('DB_PORT', $port);
$set('DB_DATABASE', $dbName);
$set('DB_USERNAME', $user);
// Tanda petik tunggal: isi diambil apa adanya oleh Laravel (tanpa interpolasi ${...} atau komentar #).
$set('DB_PASSWORD', $pass === '' ? '' : (preg_match('/^[A-Za-z0-9_.@%+=:,\-]+$/', $pass) ? $pass : "'".$pass."'"));
if (is_file($envPath) && ! $wasPgsql && ! is_file($base.'/.env.sqlite.backup')) {
    copy($envPath, $base.'/.env.sqlite.backup');
    $say('ENV', 'Pengaturan lama (SQLite) disalin ke .env.sqlite.backup.');
}
file_put_contents($envPath, $envText);
foreach (glob($base.'/bootstrap/cache/*.php') ?: [] as $cached) {
    @unlink($cached);
}
$say('ENV', '.env kini memakai PostgreSQL ('.$dbName.'@'.$host.':'.$port.').');

// 5. Tabel + data demo ---------------------------------------------------------------------------
[$code, $out] = $artisan($hasUsers ? 'migrate --force' : 'migrate --force --seed');
if ($code !== 0) {
    $fail('Migrasi database gagal:'.PHP_EOL.$out);
}
$say('DB', $hasUsers ? 'Struktur tabel diperbarui.' : 'Tabel dibuat dan data demo diisi (akun demo, kata sandi: password).');
[$code] = $artisan('universities:sync-master --only-schools');
$say('SEKOLAH', $code === 0 ? 'Daftar sekolah bawaan siap.' : 'Daftar sekolah bawaan gagal diimpor (bisa diulang lewat 1_MULAI_DEMO.bat).');

// 6. Direktori sekolah nasional (butuh internet) -------------------------------------------------
if ($syncSchools) {
    $say('SEKOLAH', 'Mengambil daftar SMA/SMK/SLB se-Indonesia dari internet (3-5 menit, jangan tutup jendela)...');
    [$code, $out] = $artisan('institutions:sync-external');
    $lines = array_values(array_filter(explode(PHP_EOL, $out)));
    $say($code === 0 ? 'OK' : 'PERHATIAN', end($lines) ?: ($code === 0 ? 'Selesai.' : 'Gagal.'));
    if ($code !== 0) {
        echo '          Tidak apa-apa: penjadwal mencoba lagi otomatis tiap jam selama demo berjalan dan ada internet.'.PHP_EOL;
    }
} else {
    $say('SEKOLAH', 'Daftar sekolah nasional akan diambil otomatis oleh penjadwal saat demo berjalan dan ada internet.');
}

echo PHP_EOL;
$say('SELESAI', 'SIP-MAGANG tersambung ke PostgreSQL. Klik dua kali 1_MULAI_DEMO.bat untuk memulai demo.');
exit(0);
