<?php

namespace Database\Seeders;

use App\Services\MasterUniversityService;
use Illuminate\Database\Seeder;

/**
 * Isi daftar sekolah menengah (SMA/SMK/MA) dari database/data/master_schools.json
 * agar pilihan "Sekolah" di profil peserta magang langsung terisi.
 */
class MasterSchoolsSeeder extends Seeder
{
    public function run(MasterUniversityService $service): void
    {
        $result = $service->syncSchools();
        $this->command?->info("Master sekolah: {$result['created']} baru, {$result['updated']} diperbarui.");
    }
}
