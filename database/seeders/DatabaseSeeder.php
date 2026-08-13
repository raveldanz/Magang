<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Unit;
use App\Models\AgencyProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Seed Profil Instansi Default
        AgencyProfile::firstOrCreate(
            ['id' => 1],
            [
                'government_name' => 'Pemerintah Kota Surabaya',
                'agency_name' => 'Dinas Komunikasi Dan Informatika',
                'address' => 'Jl. Jimerto No. 25-27, Ketabang, Genteng, Kota Surabaya, Jawa Timur 60272',
                'phone' => '(031) 5312144',
                'email' => 'diskominfo@surabaya.go.id',
                'website' => 'https://diskominfo.surabaya.go.id',
                'signee_name' => 'Drs. H. M. NASER, M.Si',
                'signee_nip' => '19700101 199503 1 002',
                'signee_position' => 'Kepala Dinas Komunikasi dan Informatika',
                'city' => 'Surabaya',
            ]
        );
        // 1. Seed User Admin
        User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrator Utama',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]
        );

        // 2. Seed User Pembimbing Lapangan
        User::firstOrCreate(
            ['email' => 'retnomumpuni.if@upnjatim.ac.id'],
            [
                'name' => 'Retno Mumpuni, S.Kom., M.Sc',
                'password' => Hash::make('pembimbing123'),
                'role' => 'pembimbing',
            ]
        );

        // 3. Seed User Mahasiswa (Contoh)
        User::firstOrCreate(
            ['email' => 'mahasiswa@gmail.com'],
            [
                'name' => 'Raveldo Andyka',
                'password' => Hash::make('mahasiswa123'),
                'role' => 'mahasiswa',
            ]
        );

        User::firstOrCreate(
            ['email' => 'AdamGanteng@gmail.com'],
            [
                'name' => 'Dimas Adam',
                'password' => Hash::make('josjis'),
                'role' => 'mahasiswa',
            ]
        );



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