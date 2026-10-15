<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Certificate;
use App\Models\Evaluation;
use App\Models\FinalReport;
use App\Models\Logbook;
use App\Models\Placement;
use App\Models\StudentProfile;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoE2ESeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('password');

        // =========================================================================
        // 1. MASTER UNIVERSITAS DI SURABAYA
        // =========================================================================
        $unesa = University::updateOrCreate(
            ['code' => 'UNESA'],
            [
                'name' => 'Universitas Negeri Surabaya',
                'logo' => 'images/logos/unesa.png',
                'address' => 'Jl. Lidah Wetan, Kec. Lakarsantri, Surabaya, Jawa Timur 60213',
                'phone' => '(031) 99424930',
                'email' => 'humas@unesa.ac.id',
                'pic_name' => 'Prof. Dr. Nurhasan, M.Kes.',
                'pic_nip' => '19630429 199002 1 001',
                'pic_position' => 'Rektor Universitas Negeri Surabaya',
                'evaluation_scheme' => 'mentor_only',
                'weight_mentor' => 100,
                'weight_lecturer' => 0,
                'require_dpl' => false,
            ]
        );

        $unitomo = University::updateOrCreate(
            ['code' => 'UNITOMO'],
            [
                'name' => 'Universitas Dr. Soetomo',
                'logo' => 'images/logos/unitomo.png',
                'address' => 'Jl. Semolowaru No. 84, Menur Pumpungan, Kec. Sukolilo, Surabaya 60118',
                'phone' => '(031) 5925970',
                'email' => 'rektorat@unitomo.ac.id',
                'pic_name' => 'Prof. Dr. Hj. Siti Marwiyah, S.H., M.H.',
                'pic_nip' => '19680308 199303 2 001',
                'pic_position' => 'Rektor Universitas Dr. Soetomo',
                'evaluation_scheme' => 'mentor_only',
                'weight_mentor' => 100,
                'weight_lecturer' => 0,
                'require_dpl' => false,
            ]
        );

        // =========================================================================
        // 2. MASTER INSTANSI TUNGGAL: DINAS PERPUSTAKAAN DAN KEARSIPAN (DISPUSIP)
        // =========================================================================
        $dispusip = AgencyProfile::updateOrCreate(
            ['agency_name' => 'Dinas Perpustakaan dan Kearsipan'],
            [
                'government_name' => 'Pemerintah Kota Surabaya',
                'acronym' => 'Dispusip',
                'address' => 'Jl. Rungkut Asri Tengah No. 5-7, Rungkut Kidul, Surabaya 60293',
                'phone' => '(031) 8704207',
                'email' => 'dispusip@surabaya.go.id',
                'website' => 'https://dispusip.surabaya.go.id',
                'logo' => 'images/logos/dispusip.png',
                'signee_name' => 'Mia Santi Dewi, S.H., M.Si.',
                'signee_nip' => '19720315 199803 2 004',
                'signee_position' => 'Kepala Dinas Perpustakaan dan Kearsipan',
                'city' => 'Surabaya',
            ]
        );

        // =========================================================================
        // 3. MASTER 4 BIDANG RESMI DISPUSIP KOTA SURABAYA
        // =========================================================================
        $unitSekretariat = Unit::updateOrCreate(
            ['agency_profile_id' => $dispusip->id, 'name' => 'Bidang Sekretariat'],
            [
                'description' => 'Pengelolaan administrasi kepegawaian, keuangan, perlengkapan, tata usaha, serta koordinasi umum dinas.',
                'quota' => 10,
            ]
        );

        $unitPerpustakaan = Unit::updateOrCreate(
            ['agency_profile_id' => $dispusip->id, 'name' => 'Bidang Pembinaan dan Pengelolaan Perpustakaan'],
            [
                'description' => 'Pengembangan sistem perpustakaan digital, kurasi koleksi literasi, layanan otomasi perpustakaan, dan pembinaan TBM.',
                'quota' => 15,
            ]
        );

        $unitLayananArsip = Unit::updateOrCreate(
            ['agency_profile_id' => $dispusip->id, 'name' => 'Bidang Pembinaan dan Layanan Kearsipan'],
            [
                'description' => 'Layanan peminjaman dan konsultasi arsip dinamis, pembinaan tata kelola arsip perangkat daerah, dan edukasi kearsipan.',
                'quota' => 10,
            ]
        );

        $unitPreservasi = Unit::updateOrCreate(
            ['agency_profile_id' => $dispusip->id, 'name' => 'Bidang Pengelolaan, Perlindungan, dan Penyelamatan Kearsipan'],
            [
                'description' => 'Preservasi arsip statis, digitalisasi naskah kuno bersejarah, alih media dokumen berharga, dan restorasi arsip fisik.',
                'quota' => 10,
            ]
        );

        // =========================================================================
        // 4. SEED AKUN DEMO RESMI LENGKAP (SUPER ADMIN, ADMIN, SUPERVISOR, MENTOR, DOSEN, MHS)
        // =========================================================================

        // [ROLE 1] SUPER ADMIN DISPUSIP
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

        User::updateOrCreate(
            ['email' => 'admin@surabaya.go.id'],
            [
                'name' => 'Super Admin Dispusip',
                'password' => $defaultPassword,
                'role' => 'super_admin',
                'agency_profile_id' => $dispusip->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // [ROLE 2] ADMIN DISPUSIP
        $adminDispusip = User::updateOrCreate(
            ['email' => 'admin.dispusip@surabaya.go.id'],
            [
                'name' => 'Admin Dispusip Surabaya',
                'password' => $defaultPassword,
                'role' => 'admin',
                'agency_profile_id' => $dispusip->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // [ROLE 3] SUPERVISOR / ATASAN DISPUSIP (TIM KERJA UMUM & KEPEGAWAIAN) — DOUBLE ACC
        $supervisorDispusip = User::updateOrCreate(
            ['email' => 'supervisor@dispusip.surabaya.go.id'],
            [
                'name' => 'Bambang Supriyadi, S.Sos., M.AP',
                'password' => $defaultPassword,
                'role' => 'supervisor',
                'agency_profile_id' => $dispusip->id,
                'unit_id' => $unitSekretariat->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'atasan.dispusip@surabaya.go.id'],
            [
                'name' => 'Koordinator Tim Kerja Kepegawaian',
                'password' => $defaultPassword,
                'role' => 'supervisor',
                'agency_profile_id' => $dispusip->id,
                'unit_id' => $unitSekretariat->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // [ROLE 4] MENTOR PEMBIMBING LAPANGAN (Terikat ke Bidang Dispusip)
        $mentorPerpus = User::updateOrCreate(
            ['email' => 'mentor.dispusip@surabaya.go.id'],
            [
                'name' => 'Budi Santoso, S.ST., M.MT (Tim Perpustakaan)',
                'password' => $defaultPassword,
                'role' => 'mentor',
                'agency_profile_id' => $dispusip->id,
                'unit_id' => $unitPerpustakaan->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $mentorArsip = User::updateOrCreate(
            ['email' => 'mentor.arsip@dispusip.surabaya.go.id'],
            [
                'name' => 'Dewi Lestari, S.Sos., M.A (Tim Arsip)',
                'password' => $defaultPassword,
                'role' => 'mentor',
                'agency_profile_id' => $dispusip->id,
                'unit_id' => $unitPreservasi->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Tetapkan Kepala Bidang
        $unitPerpustakaan->update(['head_user_id' => $mentorPerpus->id]);
        $unitPreservasi->update(['head_user_id' => $mentorArsip->id]);

        // [ROLE 5] UNIVERSITAS & DOSEN PEMBIMBING (DPL)
        User::updateOrCreate(
            ['email' => 'admin@unesa.ac.id'],
            [
                'name' => 'Portal Kampus Universitas Negeri Surabaya',
                'password' => $defaultPassword,
                'role' => 'universitas',
                'university_id' => $unesa->id,
                'university' => $unesa->name,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $dplUnesa = User::updateOrCreate(
            ['email' => 'dosen.unesa@unesa.ac.id'],
            [
                'name' => 'Dr. Erina Nur Azizah, S.Kom., M.Cs (DPL UNESA)',
                'password' => $defaultPassword,
                'role' => 'dosen',
                'university_id' => $unesa->id,
                'university' => $unesa->name,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // =========================================================================
        // 5. SEED SKENARIO MAHASISWA & DOUBLE ACC WORKFLOW
        // =========================================================================

        // SKENARIO 1: MAHASISWA LULUS / SELESAI MAGANG (COMPLETED) DENGAN SERTIFIKAT DISAHKAN ATASAN
        $mhsLulus = User::updateOrCreate(
            ['email' => 'mahasiswa.lulus@unesa.ac.id'],
            [
                'name' => 'Rizky Pratama (Lulus Magang)',
                'password' => $defaultPassword,
                'role' => 'mahasiswa',
                'university_id' => $unesa->id,
                'university' => $unesa->name,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        StudentProfile::updateOrCreate(
            ['user_id' => $mhsLulus->id],
            [
                'nim' => '22081010001',
                'universitas' => $unesa->name,
                'university_id' => $unesa->id,
                'faculty' => 'Fakultas Teknik',
                'fakultas' => 'Fakultas Teknik',
                'jurusan' => 'Teknik Informatika',
                'major' => 'Teknik Informatika',
                'semester' => '6',
                'phone' => '081234567001',
                'alamat' => 'Jl. Ketintang Baru No. 12, Surabaya',
                'address' => 'Jl. Ketintang Baru No. 12, Surabaya',
            ]
        );

        $appLulus = Application::updateOrCreate(
            ['user_id' => $mhsLulus->id],
            [
                'unit_id' => $unitPerpustakaan->id,
                'start_date' => Carbon::now()->subMonths(3)->format('Y-m-d'),
                'end_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'status' => ApplicationStatus::COMPLETED->value,
                'letter_number' => '500.12.1/001/436.7.14/2026',
                'letter_date' => Carbon::now()->subMonths(3)->format('Y-m-d'),
                'letter_token' => Str::random(32),
            ]
        );

        $placementLulus = Placement::updateOrCreate(
            ['application_id' => $appLulus->id],
            [
                'mentor_id' => $mentorPerpus->id,
                'pembimbing_id' => $mentorPerpus->id,
                'academic_advisor_id' => $dplUnesa->id,
                'certificate_number' => 'SERT/001/DISPUSIP-SBY/2026',
                'certificate_hash' => '2nUkl4agLziObPEKxC3JqH9sn34KayDM',
            ]
        );

        // Evaluasi Nilai 100% Dinas
        $evalLulus = Evaluation::updateOrCreate(
            ['placement_id' => $placementLulus->id],
            [
                'nilai_disiplin' => 95,
                'nilai_kinerja' => 92,
                'nilai_laporan' => 90,
                'final_score' => 92.33,
                'grade' => 'A',
                'catatan' => 'Sangat berdedikasi dalam pengembangan otomasi katalog digital perpustakaan.',
                'custom_components' => [
                    'mentor' => [
                        ['name' => 'Disiplin, Kehadiran, & Ketaatan Tata Tertib Kedinasan', 'score' => 95],
                        ['name' => 'Kinerja Teknis & Inovasi Katalog Perpustakaan Digital', 'score' => 92],
                        ['name' => 'Penyusunan Dokumentasi Laporan Kearsipan & Perpustakaan', 'score' => 90],
                    ],
                ],
            ]
        );

        // Sertifikat Selesai Magang telah disahkan (ACC) oleh Supervisor
        Certificate::updateOrCreate(
            ['placement_id' => $placementLulus->id],
            [
                'certificate_number' => 'SERT/001/DISPUSIP-SBY/2026',
                'file_path' => 'certificates/SERT_001_DISPUSIP-SBY_2026.pdf',
                'issued_at' => Carbon::now()->subDays(2)->toDateString(),
                'approval_status' => 'approved',
                'approved_by' => $supervisorDispusip->id,
                'approved_at' => Carbon::now()->subDays(2),
            ]
        );

        // SKENARIO 2: MAHASISWA AKTIF MAGANG (ACTIVE)
        $mhsAktif = User::updateOrCreate(
            ['email' => 'mahasiswa.aktif@unesa.ac.id'],
            [
                'name' => 'Dimas Wicaksono (Aktif Magang)',
                'password' => $defaultPassword,
                'role' => 'mahasiswa',
                'university_id' => $unesa->id,
                'university' => $unesa->name,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        StudentProfile::updateOrCreate(
            ['user_id' => $mhsAktif->id],
            [
                'nim' => '22081010002',
                'universitas' => $unesa->name,
                'university_id' => $unesa->id,
                'jurusan' => 'Sistem Informasi',
                'major' => 'Sistem Informasi',
                'semester' => '6',
                'phone' => '081234567002',
                'alamat' => 'Jl. Lidah Wetan No. 50, Surabaya',
                'address' => 'Jl. Lidah Wetan No. 50, Surabaya',
            ]
        );

        $appAktif = Application::updateOrCreate(
            ['user_id' => $mhsAktif->id],
            [
                'unit_id' => $unitPreservasi->id,
                'start_date' => Carbon::now()->subDays(20)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(70)->format('Y-m-d'),
                'status' => ApplicationStatus::ACTIVE->value,
                'letter_number' => '500.12.1/002/436.7.14/2026',
                'letter_date' => Carbon::now()->subDays(25)->format('Y-m-d'),
                'letter_token' => Str::random(32),
            ]
        );

        Placement::updateOrCreate(
            ['application_id' => $appAktif->id],
            [
                'mentor_id' => $mentorArsip->id,
                'pembimbing_id' => $mentorArsip->id,
                'academic_advisor_id' => $dplUnesa->id,
                'certificate_number' => 'SERT/002/DISPUSIP-SBY/2026',
                'certificate_hash' => Str::random(32),
            ]
        );

        // SKENARIO 3: MAHASISWA MENUNGGU ACC ATASAN (WAITING_APPROVAL) — UNTUK DEMO SUPERVISOR
        $mhsWaiting = User::updateOrCreate(
            ['email' => 'mahasiswa.waiting@unesa.ac.id'],
            [
                'name' => 'Anisa Rahmawati (Menunggu ACC Atasan)',
                'password' => $defaultPassword,
                'role' => 'mahasiswa',
                'university_id' => $unesa->id,
                'university' => $unesa->name,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        StudentProfile::updateOrCreate(
            ['user_id' => $mhsWaiting->id],
            [
                'nim' => '22081010003',
                'universitas' => $unesa->name,
                'university_id' => $unesa->id,
                'jurusan' => 'Ilmu Informasi dan Perpustakaan',
                'major' => 'Ilmu Informasi dan Perpustakaan',
                'semester' => '5',
                'phone' => '081234567003',
                'alamat' => 'Jl. Rungkut Madya No. 88, Surabaya',
                'address' => 'Jl. Rungkut Madya No. 88, Surabaya',
            ]
        );

        $appWaiting = Application::updateOrCreate(
            ['user_id' => $mhsWaiting->id],
            [
                'unit_id' => $unitPerpustakaan->id,
                'start_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'status' => ApplicationStatus::WAITING_APPROVAL->value,
            ]
        );

        Placement::updateOrCreate(
            ['application_id' => $appWaiting->id],
            [
                'mentor_id' => $mentorPerpus->id,
                'pembimbing_id' => $mentorPerpus->id,
            ]
        );
    }
}
