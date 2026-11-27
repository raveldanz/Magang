<?php

namespace App\Console\Commands;

use App\Services\Institutions\SchoolSnapshotService;
use Illuminate\Console\Command;

class ImportSchoolSnapshot extends Command
{
    protected $signature = 'institutions:import-snapshot {--path= : Lokasi file data awal}';

    protected $description = 'Isi data sekolah dari file bawaan proyek (aman diulang, tidak menimpa data yang ada)';

    public function handle(SchoolSnapshotService $snapshot): int
    {
        $path = $this->option('path') ?: SchoolSnapshotService::defaultPath();
        if (! is_file($path)) {
            $this->warn("File data awal sekolah tidak ada ({$path}); dilewati.");

            return Command::SUCCESS;
        }

        try {
            $result = $snapshot->import($path);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return Command::FAILURE;
        }

        $this->info("Data awal sekolah: {$result['created']} baru, {$result['filled']} dilengkapi, {$result['skipped']} sudah ada.");

        return Command::SUCCESS;
    }
}
