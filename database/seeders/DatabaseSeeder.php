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
        ]);

        User::create([
            'name' => 'Dimas Adam',
            'email' => 'AdamGanteng@gmail.com',
            'password' => Hash::make('josjis'),
            'role' => 'mahasiswa',
        ]);



        // 4. Seed Unit Instansi (Dengan Variasi Kuota & Deskripsi)
        Unit::firstOrCreate(
            ['name' => 'Bidang Aptika & E-Government'], 
            ['description' => 'Pengembangan aplikasi dan tata kelola e-government', 'quota' => 5]
        );
        Unit::firstOrCreate(
            ['name' => 'Bidang Informasi & Komunikasi Publik'], 
            ['description' => 'Manajemen kehumasan dan saluran informasi publik', 'quota' => 3]
        );
        Unit::firstOrCreate(
            ['name' => 'Sekretariat & Keuangan'], 
            ['description' => 'Pengelolaan administrasi dan keuangan instansi', 'quota' => 2]
        );
        Unit::firstOrCreate(
            ['name' => 'Bidang Infrastruktur & Jaringan'], 
            ['description' => 'Pemeliharaan jaringan, server, dan infrastruktur IT', 'quota' => 4]
        );
        Unit::firstOrCreate(
            ['name' => 'Bidang Persandian & Keamanan Informasi'], 
            ['description' => 'Proteksi cyber, enkripsi, dan keamanan data', 'quota' => 1]
        );
        Unit::firstOrCreate(
            ['name' => 'Bidang Statistik & Data Analitik'], 
            ['description' => 'Pengolahan data statistik dan integrasi sistem data', 'quota' => 0]
        );
    }
}