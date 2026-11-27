<?php

/**
 * Siapkan database untuk PASANG.bat dan PERBARUI.bat.
 *
 *   php scripts/setup_database.php            -> migrasi; data demo diisi HANYA bila database masih kosong
 *   php scripts/setup_database.php --update   -> migrasi saja, tidak pernah mengisi data demo
 *
 * Skrip ini tidak pernah menghapus tabel atau data.
 */

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\ConsoleOutput;

$base = dirname(__DIR__);
require $base.'/vendor/autoload.php';
$app = require $base.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$output = new ConsoleOutput;
$run = function (string $command, array $options = []) use ($output): void {
    if (Artisan::call($command, $options, $output) !== 0) {
        fwrite(STDERR, "[GAGAL] php artisan {$command}".PHP_EOL);
        exit(1);
    }
};

try {
    if (config('database.default') === 'sqlite') {
        $file = (string) config('database.connections.sqlite.database');
        if ($file !== ':memory:' && ! is_file($file)) {
            touch($file);
        }
    }

    $empty = Schema::getTables() === [];
} catch (Throwable $e) {
    fwrite(STDERR, '[GAGAL] Database belum bisa diakses. Periksa pengaturan DB_* di berkas .env.'.PHP_EOL.$e->getMessage().PHP_EOL);
    exit(1);
}

$seed = $empty && ! in_array('--update', $argv, true);
$run('migrate', ['--force' => true] + ($seed ? ['--seed' => true] : []));
echo $seed
    ? 'Database baru: data demo diisi (kata sandi akun demo: password).'.PHP_EOL
    : 'Skema database sudah terbaru; data yang ada tidak diubah.'.PHP_EOL;

$run('institutions:import-snapshot');
