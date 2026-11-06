<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Environment yang boleh berisi akun demo ber-password tetap (lihat CLAUDE.md "Demo accounts"). */
    public const DEMO_ENVIRONMENTS = ['local', 'testing', 'development'];

    /**
     * Seed the application's database focusing 100% on Dinas Perpustakaan dan Kearsipan (Dispusip).
     *
     * Seluruh seeder di bawah membuat akun dengan password yang tertulis di repositori
     * (mis. super admin admin@gmail.com / admin123). Karena itu seeder ini hanya berjalan di
     * environment demo. Di production/staging buat Super Admin pertama dengan
     * `php artisan app:create-super-admin {email}`.
     */
    public function run(): void
    {
        if (! app()->environment(self::DEMO_ENVIRONMENTS)) {
            $this->command?->error('DatabaseSeeder dilewati: seeder ini membuat akun demo dengan password yang diketahui umum.');
            $this->command?->line('Gunakan `php artisan app:create-super-admin {email}` untuk membuat Super Admin pertama.');

            return;
        }

        $this->call([
            SurabayaAndTopUniversitiesSeeder::class,
            SurabayaAgenciesSeeder::class,
            DemoE2ESeeder::class,
            AdminWorkflowSeeder::class,
            SupervisorDemoSeeder::class,
            InternshipGroupDemoSeeder::class,
            YearlyProjectsFiveYearsSeeder::class,
        ]);
    }
}
