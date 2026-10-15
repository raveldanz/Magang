<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database focusing 100% on Dinas Perpustakaan dan Kearsipan (Dispusip).
     */
    public function run(): void
    {
        $this->call([
            SurabayaAndTopUniversitiesSeeder::class,
            SurabayaAgenciesSeeder::class,
            DemoE2ESeeder::class,
            AdminWorkflowSeeder::class,
            SupervisorDemoSeeder::class,
        ]);
    }
}
