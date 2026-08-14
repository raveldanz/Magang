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
        $mhs1 = User::firstOrCreate(
            ['email' => 'mahasiswa@gmail.com'],
            [
                'name' => 'Raveldo Andyka',
                'password' => Hash::make('mahasiswa123'),
                'role' => 'mahasiswa',
            ]
        );
        \App\Models\StudentProfile::firstOrCreate(
            ['user_id' => $mhs1->id],
            [
                'nim' => '22081010001',
                'universitas' => 'UPN Veteran Jawa Timur',
                'jurusan' => 'Informatika',
                'phone' => '081234567890',
            ]
        );

        $mhs2 = User::firstOrCreate(
            ['email' => 'AdamGanteng@gmail.com'],
            [
                'name' => 'Dimas Adam',
                'password' => Hash::make('josjis'),
                'role' => 'mahasiswa',
            ]
        );
        \App\Models\StudentProfile::firstOrCreate(
            ['user_id' => $mhs2->id],
            [
                'nim' => '22081010002',
                'universitas' => 'Universitas Airlangga',
                'jurusan' => 'Sistem Informasi',
                'phone' => '089876543210',
            ]
        );



        // 4. Seed Unit Instansi (Dengan Variasi Kuota & Deskripsi)
        $unitAptika = Unit::firstOrCreate(
            ['name' => 'Bidang Aptika & E-Government'], 
            ['description' => 'Pengembangan aplikasi dan tata kelola e-government', 'quota' => 5]
        );
        $unitIKP = Unit::firstOrCreate(
            ['name' => 'Bidang Informasi & Komunikasi Publik'], 
            ['description' => 'Manajemen kehumasan dan saluran informasi publik', 'quota' => 3]
        );
        $unitSekretariat = Unit::firstOrCreate(
            ['name' => 'Sekretariat & Keuangan'], 
            ['description' => 'Pengelolaan administrasi dan keuangan instansi', 'quota' => 2]
        );
        $unitJaringan = Unit::firstOrCreate(
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

        // 5. Seed Data Pengajuan Magang Dummy (PENDING, VERIFIED, ACCEPTED, REJECTED)
        // Pengajuan 1: Raveldo Andyka (PENDING)
        \App\Models\Application::firstOrCreate(
            ['user_id' => $mhs1->id, 'unit_id' => $unitAptika->id],
            [
                'start_date' => '2026-09-01',
                'end_date' => '2026-12-01',
                'status' => 'pending',
            ]
        );

        // Pengajuan 2: Dimas Adam (ACCEPTED)
        \App\Models\Application::firstOrCreate(
            ['user_id' => $mhs2->id, 'unit_id' => $unitIKP->id],
            [
                'start_date' => '2026-09-01',
                'end_date' => '2026-11-30',
                'status' => 'accepted',
                'letter_number' => '500/102/APTIKA/2026',
                'letter_date' => '2026-08-14',
            ]
        );

        // Pengajuan 3: Budi Santoso (VERIFIED)
        $mhs3 = User::firstOrCreate(
            ['email' => 'budisantoso@gmail.com'],
            ['name' => 'Budi Santoso', 'password' => Hash::make('mahasiswa123'), 'role' => 'mahasiswa']
        );
        \App\Models\StudentProfile::firstOrCreate(
            ['user_id' => $mhs3->id],
            ['nim' => '22081010003', 'universitas' => 'Universitas Brawijaya', 'jurusan' => 'Teknik Informatika', 'phone' => '085678901234']
        );
        \App\Models\Application::firstOrCreate(
            ['user_id' => $mhs3->id, 'unit_id' => $unitSekretariat->id],
            [
                'start_date' => '2026-09-15',
                'end_date' => '2026-12-15',
                'status' => 'verified',
            ]
        );

        // Pengajuan 4: Siti Nurhaliza (REJECTED)
        $mhs4 = User::firstOrCreate(
            ['email' => 'sitinurhaliza@gmail.com'],
            ['name' => 'Siti Nurhaliza', 'password' => Hash::make('mahasiswa123'), 'role' => 'mahasiswa']
        );
        \App\Models\StudentProfile::firstOrCreate(
            ['user_id' => $mhs4->id],
            ['nim' => '22081010004', 'universitas' => 'ITS Surabaya', 'jurusan' => 'Sistem Informasi', 'phone' => '087654321098']
        );
        \App\Models\Application::firstOrCreate(
            ['user_id' => $mhs4->id, 'unit_id' => $unitJaringan->id],
            [
                'start_date' => '2026-09-01',
                'end_date' => '2026-11-30',
                'status' => 'rejected',
                'rejection_note' => 'Dokumen Transkrip Nilai belum terlampir dengan jelas. Silakan ajukan ulang.',
            ]
        );
    }
}