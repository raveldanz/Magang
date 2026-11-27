<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/** Data sekolah dari file bawaan proyek, agar pemasangan baru langsung lengkap tanpa menunggu API. */
class SchoolSnapshotSeeder extends Seeder
{
    public function run(): void
    {
        // Puluhan ribu baris hanya memperlambat pengujian otomatis.
        if (app()->runningUnitTests()) {
            return;
        }

        $this->command?->call('institutions:import-snapshot');
    }
}
