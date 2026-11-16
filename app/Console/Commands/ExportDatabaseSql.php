<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ExportDatabaseSql extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:export-sql 
                            {--filename= : Nama berkas output SQL}
                            {--data-only : Hanya ekspor data tabel tanpa DDL create table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ekspor database PostgreSQL ke berkas SQL terstruktur yang siap diimpor ke server produksi';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $driver = DB::getDriverName();
        $this->info("Menyiapkan ekspor database [Driver: {$driver}]...");

        $backupDir = storage_path('app/backups');
        if (! File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = date('Y-m-d_His');
        $customName = $this->option('filename');
        $fileName = $customName ?: "sip_magang_{$driver}_dump_{$timestamp}.sql";
        if (! str_ends_with($fileName, '.sql')) {
            $fileName .= '.sql';
        }
        $outputPath = $backupDir.DIRECTORY_SEPARATOR.$fileName;

        if ($driver === 'pgsql') {
            $host = config('database.connections.pgsql.host', '127.0.0.1');
            $port = config('database.connections.pgsql.port', '5432');
            $database = config('database.connections.pgsql.database');
            $username = config('database.connections.pgsql.username');
            $password = config('database.connections.pgsql.password');

            $dataOnlyFlag = $this->option('data-only') ? '--data-only' : '';

            // Coba eksekusi pg_dump jika tersedia di PATH sistem
            $pgDumpCmd = "pg_dump -h {$host} -p {$port} -U {$username} {$dataOnlyFlag} --no-owner --no-privileges -F p -b -v -f \"{$outputPath}\" {$database}";

            $this->line("Menjalankan pg_dump ke: {$outputPath}");

            // Set PGPASSWORD environment variable untuk perintah
            putenv("PGPASSWORD={$password}");
            $output = [];
            $exitCode = 0;
            exec($pgDumpCmd.' 2>&1', $output, $exitCode);
            putenv('PGPASSWORD');

            if ($exitCode === 0 && File::exists($outputPath) && File::size($outputPath) > 0) {
                $this->info('Ekspor database berhasil via pg_dump!');
                $this->info("Lokasi berkas: {$outputPath} (".round(File::size($outputPath) / 1024, 2).' KB)');
                $this->renderServerImportInstructions($fileName, $outputPath);

                return Command::SUCCESS;
            }

            $this->warn('pg_dump tidak ditemukan atau gagal dijalankan secara otomatis. Menggunakan generator SQL internal Laravel...');
        }

        // Fallback generator SQL mandiri (bekerja baik untuk pgsql maupun sqlite)
        $this->exportViaPhpGenerator($outputPath);

        $this->info('Ekspor database berhasil!');
        $this->info("Lokasi berkas: {$outputPath} (".round(File::size($outputPath) / 1024, 2).' KB)');
        $this->renderServerImportInstructions($fileName, $outputPath);

        return Command::SUCCESS;
    }

    /**
     * Generator SQL mandiri menggunakan koneksi PDO Laravel
     */
    protected function exportViaPhpGenerator(string $outputPath): void
    {
        $handle = fopen($outputPath, 'w');
        if (! $handle) {
            $this->error("Gagal membuka berkas untuk penulisan: {$outputPath}");

            return;
        }

        fwrite($handle, "-- =====================================================\n");
        fwrite($handle, "-- SIP-MAGANG DATABASE EXPORT\n");
        fwrite($handle, '-- Tanggal Ekspor: '.date('Y-m-d H:i:s')."\n");
        fwrite($handle, '-- Driver: '.DB::getDriverName()."\n");
        fwrite($handle, "-- =====================================================\n\n");
        fwrite($handle, "SET statement_timeout = 0;\n");
        fwrite($handle, "SET client_encoding = 'UTF8';\n");
        fwrite($handle, "SET standard_conforming_strings = on;\n\n");

        $tables = Schema::getTableListing();

        // Abaikan tabel sementara/cache
        $ignored = ['sessions', 'cache', 'cache_locks', 'jobs', 'failed_jobs', 'job_batches'];

        foreach ($tables as $table) {
            if (in_array($table, $ignored, true)) {
                continue;
            }

            $count = DB::table($table)->count();
            if ($count === 0) {
                continue;
            }

            fwrite($handle, "-- -----------------------------------------------------\n");
            fwrite($handle, "-- Data untuk tabel: {$table} ({$count} baris)\n");
            fwrite($handle, "-- -----------------------------------------------------\n");

            DB::table($table)->orderBy(DB::raw('1'))->chunk(200, function ($rows) use ($handle, $table) {
                foreach ($rows as $row) {
                    $rowArray = (array) $row;
                    $columns = array_keys($rowArray);
                    $escapedCols = array_map(fn ($c) => '"'.str_replace('"', '""', $c).'"', $columns);

                    $values = [];
                    foreach ($rowArray as $val) {
                        if ($val === null) {
                            $values[] = 'NULL';
                        } elseif (is_bool($val)) {
                            $values[] = $val ? 'TRUE' : 'FALSE';
                        } elseif (is_numeric($val) && ! is_string($val)) {
                            $values[] = $val;
                        } else {
                            $escaped = str_replace("'", "''", (string) $val);
                            $values[] = "'{$escaped}'";
                        }
                    }

                    $colList = implode(', ', $escapedCols);
                    $valList = implode(', ', $values);
                    fwrite($handle, "INSERT INTO \"{$table}\" ({$colList}) VALUES ({$valList}) ON CONFLICT DO NOTHING;\n");
                }
            });

            fwrite($handle, "\n");
        }

        // Update sequence serial ID untuk PostgreSQL
        if (DB::getDriverName() === 'pgsql') {
            fwrite($handle, "-- -----------------------------------------------------\n");
            fwrite($handle, "-- Sinkronisasi Sequence ID PostgreSQL\n");
            fwrite($handle, "-- -----------------------------------------------------\n");
            foreach ($tables as $table) {
                if (in_array($table, $ignored, true)) {
                    continue;
                }
                fwrite($handle, "SELECT setval(pg_get_serial_sequence('\"{$table}\"', 'id'), COALESCE((SELECT MAX(id) FROM \"{$table}\"), 1), true);\n");
            }
        }

        fclose($handle);
    }

    /**
     * Tampilkan panduan cara impor berkas ke server
     */
    protected function renderServerImportInstructions(string $fileName, string $fullPath): void
    {
        $this->newLine();
        $this->line('=========================================================');
        $this->info('PANDUAN EKSPOR / IMPOR DATABASE KE SERVER PRODUKSI:');
        $this->line('=========================================================');
        $this->line('1. Salin berkas backup ini ke server via SCP / SFTP:');
        $this->comment("   scp \"{$fullPath}\" user@server-ip:/var/www/backups/{$fileName}");
        $this->newLine();
        $this->line('2. Impor berkas SQL ke database server produksi:');
        $this->comment("   psql -h 127.0.0.1 -U nama_user_db -d nama_database_prod < /var/www/backups/{$fileName}");
        $this->newLine();
        $this->line('3. Bersihkan cache di server setelah impor selesai:');
        $this->comment('   php artisan optimize:clear && php artisan app:health');
        $this->line('=========================================================');
    }
}
