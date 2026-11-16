<?php

namespace App\Console\Commands;

use App\Models\University;
use App\Services\MasterUniversityService;
use Illuminate\Console\Command;

class SyncMasterUniversities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'universities:sync-master
        {--skip-schools : Jangan impor master sekolah (SMA/SMK/MA)}
        {--only-schools : Hanya impor master sekolah, kampus tidak disentuh}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi master perguruan tinggi dan sekolah menengah (SMA/SMK/MA) ke database';

    /**
     * Execute the console command.
     */
    public function handle(MasterUniversityService $service): int
    {
        if ($this->option('skip-schools') && $this->option('only-schools')) {
            $this->error('Opsi --skip-schools dan --only-schools tidak boleh dipakai bersamaan.');

            return Command::FAILURE;
        }

        if (! $this->option('only-schools')) {
            $this->info('Menyinkronkan master perguruan tinggi ('.count($service->allMaster()).' kampus)...');
            $colleges = $service->syncColleges();
            $this->line("Kampus: {$colleges['created']} baru, {$colleges['updated']} diperbarui.");
        }

        if (! $this->option('skip-schools')) {
            $this->info('Menyinkronkan master sekolah ('.count($service->allSchools()).' sekolah)...');
            $schools = $service->syncSchools();
            $this->line("Sekolah: {$schools['created']} baru, {$schools['updated']} diperbarui.");
        }

        $this->info('Total institusi di database: '.University::count()
            .' ('.University::where('institution_type', University::TYPE_SCHOOL)->count().' sekolah).');

        return Command::SUCCESS;
    }
}
