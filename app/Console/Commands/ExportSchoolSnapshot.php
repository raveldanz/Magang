<?php

namespace App\Console\Commands;

use App\Services\Institutions\SchoolSnapshotService;
use Illuminate\Console\Command;

class ExportSchoolSnapshot extends Command
{
    protected $signature = 'institutions:export-snapshot {--path= : Lokasi file tujuan}';

    protected $description = 'Buat ulang file data awal sekolah (hanya sekolah hasil sinkron direktori)';

    public function handle(SchoolSnapshotService $snapshot): int
    {
        $path = $this->option('path') ?: SchoolSnapshotService::defaultPath();
        $count = $snapshot->export($path);
        $this->info("{$count} sekolah diekspor ke {$path} (".number_format(filesize($path) / 1024, 0, ',', '.').' KB).');

        return Command::SUCCESS;
    }
}
