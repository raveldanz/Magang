<?php

namespace Database\Seeders;

use App\Models\AgencyProfile;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SurabayaAgenciesSeeder extends Seeder
{
    /**
     * Seeder Master Data Resmi Instansi Dinas Pemerintah Kota Surabaya.
     * Dikhususkan untuk Dinas Perpustakaan dan Kearsipan beserta unit kerjanya.
     */
    public function run(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('agency_profiles', 'id'), COALESCE((SELECT MAX(id) FROM agency_profiles), 1))");
            DB::statement("SELECT setval(pg_get_serial_sequence('units', 'id'), COALESCE((SELECT MAX(id) FROM units), 1))");
            DB::statement("SELECT setval(pg_get_serial_sequence('users', 'id'), COALESCE((SELECT MAX(id) FROM users), 1))");
        }

        $agencies = [
            // =====================================================================
            // DINAS DAERAH KOTA SURABAYA (DINAS PERPUSTAKAAN DAN KEARSIPAN)
            // =====================================================================
            [
                'match_keyword' => 'Perpustakaan',
                'agency_name' => 'Dinas Perpustakaan dan Kearsipan',
                'address' => 'Jl. Rungkut Asri Tengah No. 5-7, Rungkut Kidul, Kec. Rungkut, Kota Surabaya, Jawa Timur 60293',
                'phone' => '(031) 8707682',
                'email' => 'dispusip@surabaya.go.id',
                'website' => 'https://dispusip.surabaya.go.id',
                'logo' => 'images/logos/dispusip.png',
                'signee_name' => 'Mia Santi Dewi, S.H., M.Si.',
                'signee_nip' => '19720315 199803 2 004',
                'signee_position' => 'Kepala Dinas Perpustakaan dan Kearsipan',
                'admin_email' => 'admin.dispusip@surabaya.go.id',
                'admin_name' => 'Admin Dispusip Surabaya',
                'units' => [
                    ['name' => 'Bidang Pelayanan & Otomasi Perpustakaan Digital', 'quota' => 10, 'description' => 'Pengelolaan katalog digital, automasi perpustakaan, dan kurasi literasi digital.'],
                    ['name' => 'Bidang Preservasi & Pengelolaan Arsip Statis Elektronik', 'quota' => 8, 'description' => 'Digitalisasi naskah kuno, restorasi arsip bersejarah, dan kearsipan elektronik.'],
                    ['name' => 'Bidang Pembinaan & Pengembangan Minat Baca', 'quota' => 8, 'description' => 'Edukasi literasi masyarakat, mobil perpustakaan keliling, dan kemitraan literasi publik.'],
                ],
            ],
        ];

        $defaultPassword = Hash::make('password');

        foreach ($agencies as $item) {
            $adminEmail = $item['admin_email'];
            $adminName = $item['admin_name'];
            $units = $item['units'] ?? [];
            $keyword = $item['match_keyword'] ?? null;

            unset($item['admin_email'], $item['admin_name'], $item['units'], $item['match_keyword']);

            $item['government_name'] = $item['government_name'] ?? 'Pemerintah Kota Surabaya';
            $item['city'] = $item['city'] ?? 'Surabaya';

            $acronymMap = [
                'Perpustakaan' => 'Dispusip',
            ];
            if ($keyword && isset($acronymMap[$keyword])) {
                $item['acronym'] = $acronymMap[$keyword];
            }

            // Sinkronisasi: Cari instansi yang sudah ada berdasarkan keyword nama atau nama persis
            $query = AgencyProfile::whereRaw('LOWER(agency_name) = ?', [strtolower($item['agency_name'])]);
            if ($keyword) {
                $query->orWhere('agency_name', 'like', '%'.$keyword.'%');
            }
            $agency = $query->first();

            if ($agency) {
                $agency->update($item);
            } else {
                $agency = AgencyProfile::create($item);
            }

            // 1. Pastikan Akun Admin Dinas Resmi Terdaftar
            $existingAdmin = User::where('agency_profile_id', $agency->id)
                ->where('role', 'admin')
                ->first();

            if (! $existingAdmin) {
                User::firstOrCreate(
                    ['email' => $adminEmail],
                    [
                        'name' => $adminName,
                        'password' => $defaultPassword,
                        'role' => 'admin',
                        'agency_profile_id' => $agency->id,
                        'status' => 'active',
                        'email_verified_at' => now(),
                    ]
                );
            } else {
                // Pertahankan akun yang ada dan pastikan statusnya aktif
                $existingAdmin->update([
                    'status' => 'active',
                    'agency_profile_id' => $agency->id,
                ]);
            }

            // 2. Pastikan Unit / Bidang Kerja Magang Tersedia
            foreach ($units as $unitData) {
                Unit::firstOrCreate(
                    [
                        'agency_profile_id' => $agency->id,
                        'name' => $unitData['name'],
                    ],
                    [
                        'description' => $unitData['description'] ?? null,
                        'quota' => $unitData['quota'] ?? 10,
                    ]
                );
            }
        }
    }
}
