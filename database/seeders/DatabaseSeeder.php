<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed User Admin
        User::create([
            'name' => 'Administrator Utama',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // 2. Seed User Pembimbing Lapangan
        User::create([
            'name' => 'Retno Mumpuni, S.Kom., M.Sc',
            'email' => 'retnomumpuni.if@upnjatim.ac.id',
            'password' => Hash::make('pembimbing123'),
            'role' => 'pembimbing',
        ]);

        // 3. Seed User Mahasiswa (Contoh)
        User::create([
            'name' => 'Raveldo Andyka',
            'email' => 'mahasiswa@gmail.com',
            'password' => Hash::make('mahasiswa123'),
            'role' => 'mahasiswa',

            'name' => 'Dimas Adam',
            'email' => 'AdamGanteng@gmail.com',
            'password' => Hash::make('josjis'),
            'role' => 'mahasiswa',
        ]);



        // 4. Seed Unit Instansi Awal
        Unit::create(['name' => 'Bidang Aptika & E-Government', 'quota' => 5]);
        Unit::create(['name' => 'Bidang Informasi & Komunikasi Publik', 'quota' => 3]);
        Unit::create(['name' => 'Sekretariat & Keuangan', 'quota' => 2]);
    }
}