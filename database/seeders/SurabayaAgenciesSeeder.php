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
     * Seeder Master Data Resmi Dinas Perpustakaan dan Kearsipan (Dispusip) Kota Surabaya (Single-Agency).
     * Terintegrasi dengan 4 Bidang resmi, akun Admin Dispusip, Atasan (Supervisor), dan Tim Kerja Pembimbing (Mentor).
     */
    public function run(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('agency_profiles', 'id'), COALESCE((SELECT MAX(id) FROM agency_profiles), 1))");
            DB::statement("SELECT setval(pg_get_serial_sequence('units', 'id'), COALESCE((SELECT MAX(id) FROM units), 1))");
            DB::statement("SELECT setval(pg_get_serial_sequence('users', 'id'), COALESCE((SELECT MAX(id) FROM users), 1))");
        }

        $defaultPassword = Hash::make('password');

        // 1. Master Profil Instansi Tunggal: Dinas Perpustakaan dan Kearsipan (Dispusip) Kota Surabaya
        $dispusipData = [
            'government_name' => 'Pemerintah Kota Surabaya',
            'agency_name' => 'Dinas Perpustakaan dan Kearsipan',
            'acronym' => 'Dispusip',
            'address' => 'Jl. Rungkut Asri Tengah No. 5-7, Rungkut Kidul, Kec. Rungkut, Kota Surabaya, Jawa Timur 60293',
            'phone' => '(031) 8707682',
            'email' => 'dispusip@surabaya.go.id',
            'website' => 'https://dispusip.surabaya.go.id',
            'logo' => 'images/logos/dispusip.png',
            'signee_name' => 'Mia Santi Dewi, S.H., M.Si.',
            'signee_nip' => '19720315 199803 2 004',
            'signee_position' => 'Kepala Dinas Perpustakaan dan Kearsipan',
            'city' => 'Surabaya',
        ];

        $dispusip = AgencyProfile::where('agency_name', 'like', '%Perpustakaan%')->first()
            ?? AgencyProfile::first();

        if ($dispusip) {
            $dispusip->update($dispusipData);
        } else {
            $dispusip = AgencyProfile::create($dispusipData);
        }

        // 2. Standarisasi 4 Bidang Resmi Dispusip Kota Surabaya
        $officialUnits = [
            [
                'name' => 'Bidang Sekretariat',
                'quota' => 10,
                'description' => 'Pengelolaan administrasi kepegawaian, keuangan, perlengkapan, tata usaha, serta koordinasi umum dinas.',
            ],
            [
                'name' => 'Bidang Pembinaan dan Pengelolaan Perpustakaan',
                'quota' => 15,
                'description' => 'Pengembangan sistem perpustakaan digital, kurasi koleksi literasi, layanan otomasi perpustakaan, dan pembinaan TBM.',
            ],
            [
                'name' => 'Bidang Pembinaan dan Layanan Kearsipan',
                'quota' => 10,
                'description' => 'Layanan peminjaman dan konsultasi arsip dinamis, pembinaan tata kelola arsip perangkat daerah, dan edukasi kearsipan.',
            ],
            [
                'name' => 'Bidang Pengelolaan, Perlindungan, dan Penyelamatan Kearsipan',
                'quota' => 10,
                'description' => 'Preservasi arsip statis, digitalisasi naskah kuno bersejarah, alih media dokumen berharga, dan restorasi arsip fisik.',
            ],
        ];

        $createdUnits = [];
        foreach ($officialUnits as $uData) {
            $createdUnits[$uData['name']] = Unit::updateOrCreate(
                [
                    'agency_profile_id' => $dispusip->id,
                    'name' => $uData['name'],
                ],
                [
                    'quota' => $uData['quota'],
                    'description' => $uData['description'],
                ]
            );
        }

        // Hapus SEMUA profil dinas selain Dispusip
        AgencyProfile::where('id', '!=', $dispusip->id)->delete();

        // Hapus unit di luar 4 Bidang Resmi Dispusip
        Unit::whereNotIn('name', array_keys($createdUnits))->delete();

        // 3. Akun Super Admin Dispusip (Pengelola IT Sistem)
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Super Admin Dispusip',
                'password' => Hash::make('admin123'),
                'role' => 'super_admin',
                'agency_profile_id' => $dispusip->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // 4. Akun Admin Pengelola Magang Dispusip (Admin Dinas Keseluruhan / Seluruh Bidang)
        User::updateOrCreate(
            ['email' => 'admin.dispusip@surabaya.go.id'],
            [
                'name' => 'Admin Dispusip Surabaya',
                'password' => $defaultPassword,
                'role' => 'admin',
                'agency_profile_id' => $dispusip->id,
                'unit_id' => null,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // 4.1. Akun Admin Khusus Per Bidang (Fokus Bidang Masing-masing)
        $bidangAdminSeedData = [
            [
                'email' => 'admin.sekretariat@surabaya.go.id',
                'name' => 'Admin Bidang Sekretariat',
                'unit' => 'Bidang Sekretariat',
            ],
            [
                'email' => 'admin.perpustakaan@surabaya.go.id',
                'name' => 'Admin Bidang Pembinaan & Pengelolaan Perpustakaan',
                'unit' => 'Bidang Pembinaan dan Pengelolaan Perpustakaan',
            ],
            [
                'email' => 'admin.layananarsip@surabaya.go.id',
                'name' => 'Admin Bidang Pembinaan & Layanan Kearsipan',
                'unit' => 'Bidang Pembinaan dan Layanan Kearsipan',
            ],
            [
                'email' => 'admin.penyelamatanarsip@surabaya.go.id',
                'name' => 'Admin Bidang Pengelolaan, Perlindungan & Penyelamatan Kearsipan',
                'unit' => 'Bidang Pengelolaan, Perlindungan, dan Penyelamatan Kearsipan',
            ],
        ];

        foreach ($bidangAdminSeedData as $adminData) {
            $unitModel = $createdUnits[$adminData['unit']] ?? null;
            if ($unitModel) {
                User::updateOrCreate(
                    ['email' => $adminData['email']],
                    [
                        'name' => $adminData['name'],
                        'password' => $defaultPassword,
                        'role' => 'admin',
                        'agency_profile_id' => $dispusip->id,
                        'unit_id' => $unitModel->id,
                        'status' => 'active',
                        'email_verified_at' => now(),
                    ]
                );
            }
        }

        // 5. Akun Atasan / Supervisor (Tim Kerja Umum dan Kepegawaian Dispusip) — Double ACC
        User::updateOrCreate(
            ['email' => 'atasan.dispusip@surabaya.go.id'],
            [
                'name' => 'Bambang Supriyadi, S.Sos., M.AP',
                'password' => $defaultPassword,
                'role' => 'supervisor',
                'agency_profile_id' => $dispusip->id,
                'unit_id' => $createdUnits['Bidang Sekretariat']->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'supervisor@dispusip.surabaya.go.id'],
            [
                'name' => 'Tim Kerja Umum dan Kepegawaian',
                'password' => $defaultPassword,
                'role' => 'supervisor',
                'agency_profile_id' => $dispusip->id,
                'unit_id' => $createdUnits['Bidang Sekretariat']->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // 6. Akun Tim Kerja Pembimbing Lapangan (Mentor) terikat ke 4 Bidang Resmi Dispusip
        $mentorSeedData = [
            [
                'email' => 'mentor.sekretariat@dispusip.surabaya.go.id',
                'name' => 'Siti Nurhaliza, S.AP',
                'unit' => 'Bidang Sekretariat',
            ],
            [
                'email' => 'mentor.dispusip1@surabaya.go.id',
                'name' => 'Budi Santoso, S.ST., M.MT',
                'unit' => 'Bidang Pembinaan dan Pengelolaan Perpustakaan',
            ],
            [
                'email' => 'mentor.layanan@dispusip.surabaya.go.id',
                'name' => 'Dewi Lestari, S.Sos., M.A',
                'unit' => 'Bidang Pembinaan dan Layanan Kearsipan',
            ],
            [
                'email' => 'mentor.preservasi@dispusip.surabaya.go.id',
                'name' => 'Agus Pramono, S.Hum., M.P',
                'unit' => 'Bidang Pengelolaan, Perlindungan, dan Penyelamatan Kearsipan',
            ],
        ];

        foreach ($mentorSeedData as $m) {
            $unitObj = $createdUnits[$m['unit']] ?? null;
            $mentorUser = User::updateOrCreate(
                ['email' => $m['email']],
                [
                    'name' => $m['name'],
                    'password' => $defaultPassword,
                    'role' => 'mentor',
                    'agency_profile_id' => $dispusip->id,
                    'unit_id' => $unitObj?->id,
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            // Jika kepala bidang belum ada, jadikan salah satu mentor sebagai kepala bidang
            if ($unitObj && ! $unitObj->head_user_id) {
                $unitObj->update(['head_user_id' => $mentorUser->id]);
            }
        }
    }
}
