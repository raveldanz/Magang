<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\Certificate;
use App\Models\Evaluation;
use App\Models\FinalReport;
use App\Models\InternshipProject;
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

class SupervisorDemoSeeder extends Seeder
{
    /**
     * Seeder Akun Dummy dan Dataset Lengkap untuk Pengujian Fitur Supervisor (Atasan Dispusip).
     * Mencakup seluruh status alur kerja:
     * - Double ACC Tahap 1 (Persetujuan Pengajuan: waiting_approval, accepted, active, revision, rejected)
     * - Double ACC Tahap 2 (Pengesahan Sertifikat: waiting_approval, approved, revision)
     * - Monitoring Bidang & Kepegawaian (distribusi kuota & mahasiswa di 4 Bidang Resmi Dispusip)
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('password');

        // 1. Instansi Target: Dinas Perpustakaan dan Kearsipan Kota Surabaya
        $dispusip = AgencyProfile::where('agency_name', 'like', '%Perpustakaan%')->first()
            ?? AgencyProfile::firstOrCreate(
                ['agency_name' => 'Dinas Perpustakaan dan Kearsipan'],
                [
                    'government_name' => 'Pemerintah Kota Surabaya',
                    'address' => 'Jl. Rungkut Asri Tengah No. 5-7, Rungkut Kidul, Kec. Rungkut, Kota Surabaya, Jawa Timur 60293',
                    'phone' => '(031) 8707682',
                    'email' => 'dispusip@surabaya.go.id',
                    'website' => 'https://dispusip.surabaya.go.id',
                    'signee_name' => 'Ir. Mia Santi Dewi, M.Si',
                    'signee_nip' => '19680812 199403 2 007',
                    'signee_position' => 'Kepala Dinas Perpustakaan dan Kearsipan',
                    'city' => 'Surabaya',
                ]
            );

        // 2. 4 Bidang Resmi Dispusip
        $unitSekretariat = Unit::updateOrCreate(
            ['agency_profile_id' => $dispusip->id, 'name' => 'Bidang Sekretariat'],
            ['description' => 'Pengelolaan administrasi umum, kepegawaian, keuangan, perlengkapan, dan tata kelola internal kedinasan.', 'quota' => 10]
        );
        $unitPerpustakaan = Unit::updateOrCreate(
            ['agency_profile_id' => $dispusip->id, 'name' => 'Bidang Pembinaan dan Pengelolaan Perpustakaan'],
            ['description' => 'Pengembangan koleksi, otomasi perpustakaan digital, layanan pemustaka, dan pembinaan minat baca publik.', 'quota' => 15]
        );
        $unitLayananArsip = Unit::updateOrCreate(
            ['agency_profile_id' => $dispusip->id, 'name' => 'Bidang Pembinaan dan Layanan Kearsipan'],
            ['description' => 'Pengelolaan arsip dinamis, bimbingan kearsipan perangkat daerah, dan layanan keterbukaan informasi kearsipan.', 'quota' => 10]
        );
        $unitPreservasi = Unit::updateOrCreate(
            ['agency_profile_id' => $dispusip->id, 'name' => 'Bidang Pengelolaan, Perlindungan, dan Penyelamatan Kearsipan'],
            ['description' => 'Preservasi, digitalisasi arsip bersejarah, restorasi naskah kuno, dan penyelamatan memori kolektif daerah.', 'quota' => 10]
        );

        // 3. Akun Supervisor Utama (Untuk Login Pengujian)
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

        // 4. Akun Mentor Tim Kerja (Masing-masing Bidang)
        $mentorPerpus = User::updateOrCreate(
            ['email' => 'mentor.perpus@dispusip.surabaya.go.id'],
            [
                'name' => 'Budi Santoso, S.ST., M.MT',
                'password' => $defaultPassword,
                'role' => 'mentor',
                'agency_profile_id' => $dispusip->id,
                'unit_id' => $unitPerpustakaan->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $mentorSekretariat = User::updateOrCreate(
            ['email' => 'mentor.sekretariat@dispusip.surabaya.go.id'],
            [
                'name' => 'Dra. Endang Sri Lestari, M.Si',
                'password' => $defaultPassword,
                'role' => 'mentor',
                'agency_profile_id' => $dispusip->id,
                'unit_id' => $unitSekretariat->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $mentorArsip = User::updateOrCreate(
            ['email' => 'mentor.arsip@dispusip.surabaya.go.id'],
            [
                'name' => 'Ahmad Fauzi, S.Kom.',
                'password' => $defaultPassword,
                'role' => 'mentor',
                'agency_profile_id' => $dispusip->id,
                'unit_id' => $unitLayananArsip->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $mentorPreservasi = User::updateOrCreate(
            ['email' => 'mentor.preservasi@dispusip.surabaya.go.id'],
            [
                'name' => 'Siti Aminah, A.Md.',
                'password' => $defaultPassword,
                'role' => 'mentor',
                'agency_profile_id' => $dispusip->id,
                'unit_id' => $unitPreservasi->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // 5. Perguruan Tinggi Mitra
        $unesa = University::firstOrCreate(
            ['code' => 'UNESA'],
            [
                'name' => 'Universitas Negeri Surabaya',
                'logo' => 'images/logos/unesa.png',
                'evaluation_scheme' => 'mentor_only',
                'weight_mentor' => 100,
                'weight_lecturer' => 0,
            ]
        );
        $unitomo = University::firstOrCreate(
            ['code' => 'UNITOMO'],
            [
                'name' => 'Universitas Dr. Soetomo',
                'logo' => 'images/logos/unitomo.png',
                'evaluation_scheme' => 'mentor_only',
                'weight_mentor' => 100,
                'weight_lecturer' => 0,
            ]
        );
        $unair = University::firstOrCreate(
            ['code' => 'UNAIR'],
            [
                'name' => 'Universitas Airlangga',
                'logo' => 'images/logos/unair.png',
                'evaluation_scheme' => 'mentor_only',
                'weight_mentor' => 100,
                'weight_lecturer' => 0,
            ]
        );

        // File sample dokumen PDF
        $samplePdf = 'documents/applications/5AdFAcelgeprCcoR82Brj5GF2QzWJvqIMPwbrfc2.pdf';

        // =========================================================================
        // FITUR 1: PERSETUJUAN PENGAJUAN MAGANG (DOUBLE ACC TAHAP 1)
        // =========================================================================

        // [STATUS WAITING_APPROVAL #1] Mahasiswa Anisa Rahmawati -> Siap di-ACC Atasan
        $userAnisa = User::updateOrCreate(
            ['email' => 'anisa.approval@unesa.ac.id'],
            ['name' => 'Anisa Rahmawati', 'password' => $defaultPassword, 'role' => 'mahasiswa', 'university_id' => $unesa->id, 'university' => $unesa->name, 'status' => 'active', 'email_verified_at' => now()]
        );
        StudentProfile::updateOrCreate(
            ['user_id' => $userAnisa->id],
            ['nim' => '22081010011', 'universitas' => $unesa->name, 'university_id' => $unesa->id, 'jurusan' => 'S1 Ilmu Perpustakaan', 'major' => 'S1 Ilmu Perpustakaan', 'semester' => '6', 'phone' => '081234560011', 'alamat' => 'Jl. Ketintang Madya No. 12, Surabaya', 'address' => 'Jl. Ketintang Madya No. 12, Surabaya']
        );
        $appAnisa = Application::updateOrCreate(
            ['user_id' => $userAnisa->id],
            [
                'unit_id' => $unitPerpustakaan->id,
                'start_date' => Carbon::now()->addDays(5)->toDateString(),
                'end_date' => Carbon::now()->addDays(95)->toDateString(),
                'status' => ApplicationStatus::WAITING_APPROVAL->value,
            ]
        );
        Placement::updateOrCreate(
            ['application_id' => $appAnisa->id],
            ['mentor_id' => $mentorPerpus->id, 'pembimbing_id' => $mentorPerpus->id]
        );
        ApplicationDocument::updateOrCreate(
            ['application_id' => $appAnisa->id, 'document_type' => 'Surat Pengantar Kampus'],
            ['file_path' => $samplePdf]
        );
        ApplicationDocument::updateOrCreate(
            ['application_id' => $appAnisa->id, 'document_type' => 'Proposal Magang Mahasiswa'],
            ['file_path' => $samplePdf]
        );
        ApplicationDocument::updateOrCreate(
            ['application_id' => $appAnisa->id, 'document_type' => 'Curriculum Vitae (CV)'],
            ['file_path' => $samplePdf]
        );

        // [STATUS WAITING_APPROVAL #2] Mahasiswa Bagas Pratama -> Untuk diuji tombol Kembalikan ke Admin (Revisi)
        $userBagas = User::updateOrCreate(
            ['email' => 'bagas.approval@unitomo.ac.id'],
            ['name' => 'Bagas Pratama', 'password' => $defaultPassword, 'role' => 'mahasiswa', 'university_id' => $unitomo->id, 'university' => $unitomo->name, 'status' => 'active', 'email_verified_at' => now()]
        );
        StudentProfile::updateOrCreate(
            ['user_id' => $userBagas->id],
            ['nim' => '22081010012', 'universitas' => $unitomo->name, 'university_id' => $unitomo->id, 'jurusan' => 'S1 Teknik Informatika', 'major' => 'S1 Teknik Informatika', 'semester' => '6', 'phone' => '081234560012', 'alamat' => 'Jl. Semolowaru No. 45, Surabaya', 'address' => 'Jl. Semolowaru No. 45, Surabaya']
        );
        $appBagas = Application::updateOrCreate(
            ['user_id' => $userBagas->id],
            [
                'unit_id' => $unitSekretariat->id,
                'start_date' => Carbon::now()->addDays(10)->toDateString(),
                'end_date' => Carbon::now()->addDays(100)->toDateString(),
                'status' => ApplicationStatus::WAITING_APPROVAL->value,
            ]
        );
        Placement::updateOrCreate(
            ['application_id' => $appBagas->id],
            ['mentor_id' => $mentorSekretariat->id, 'pembimbing_id' => $mentorSekretariat->id]
        );
        ApplicationDocument::updateOrCreate(
            ['application_id' => $appBagas->id, 'document_type' => 'Surat Pengantar Kampus'],
            ['file_path' => $samplePdf]
        );
        ApplicationDocument::updateOrCreate(
            ['application_id' => $appBagas->id, 'document_type' => 'Proposal Magang Mahasiswa'],
            ['file_path' => $samplePdf]
        );

        // [STATUS WAITING_APPROVAL #3] Mahasiswa Citra Kirana -> Siap di-ACC
        $userCitra = User::updateOrCreate(
            ['email' => 'citra.approval@unair.ac.id'],
            ['name' => 'Citra Kirana', 'password' => $defaultPassword, 'role' => 'mahasiswa', 'university_id' => $unair->id, 'university' => $unair->name, 'status' => 'active', 'email_verified_at' => now()]
        );
        StudentProfile::updateOrCreate(
            ['user_id' => $userCitra->id],
            ['nim' => '22081010013', 'universitas' => $unair->name, 'university_id' => $unair->id, 'jurusan' => 'D4 Kearsipan Digital', 'major' => 'D4 Kearsipan Digital', 'semester' => '7', 'phone' => '081234560013', 'alamat' => 'Jl. Dharmawangsa Dalam No. 18, Surabaya', 'address' => 'Jl. Dharmawangsa Dalam No. 18, Surabaya']
        );
        $appCitra = Application::updateOrCreate(
            ['user_id' => $userCitra->id],
            [
                'unit_id' => $unitPreservasi->id,
                'start_date' => Carbon::now()->addDays(14)->toDateString(),
                'end_date' => Carbon::now()->addDays(104)->toDateString(),
                'status' => ApplicationStatus::WAITING_APPROVAL->value,
            ]
        );
        Placement::updateOrCreate(
            ['application_id' => $appCitra->id],
            ['mentor_id' => $mentorPreservasi->id, 'pembimbing_id' => $mentorPreservasi->id]
        );
        ApplicationDocument::updateOrCreate(
            ['application_id' => $appCitra->id, 'document_type' => 'Surat Permohonan Magang'],
            ['file_path' => $samplePdf]
        );

        // [STATUS ACCEPTED] Mahasiswa Dani Ramadhan -> Sudah disahkan Atasan (Belum mulai)
        $userDani = User::updateOrCreate(
            ['email' => 'dani.accepted@unesa.ac.id'],
            ['name' => 'Dani Ramadhan', 'password' => $defaultPassword, 'role' => 'mahasiswa', 'university_id' => $unesa->id, 'university' => $unesa->name, 'status' => 'active', 'email_verified_at' => now()]
        );
        StudentProfile::updateOrCreate(
            ['user_id' => $userDani->id],
            ['nim' => '22081010014', 'universitas' => $unesa->name, 'university_id' => $unesa->id, 'jurusan' => 'S1 Ilmu Komunikasi', 'major' => 'S1 Ilmu Komunikasi', 'semester' => '6', 'phone' => '081234560014', 'alamat' => 'Jl. Gayungan No. 3, Surabaya', 'address' => 'Jl. Gayungan No. 3, Surabaya']
        );
        $appDani = Application::updateOrCreate(
            ['user_id' => $userDani->id],
            [
                'unit_id' => $unitLayananArsip->id,
                'start_date' => Carbon::now()->addDays(10)->toDateString(),
                'end_date' => Carbon::now()->addDays(100)->toDateString(),
                'status' => ApplicationStatus::ACCEPTED->value,
                'letter_number' => '500.12.1/045/436.7.14/2026',
                'letter_date' => Carbon::now()->subDays(1)->toDateString(),
                'letter_token' => Str::random(32),
            ]
        );
        Placement::updateOrCreate(
            ['application_id' => $appDani->id],
            ['mentor_id' => $mentorArsip->id, 'pembimbing_id' => $mentorArsip->id]
        );

        // [STATUS ACTIVE] Mahasiswa Eka Putri Lestari -> Sedang Aktif Bertugas di Bidang
        $userEka = User::updateOrCreate(
            ['email' => 'eka.aktif@unesa.ac.id'],
            ['name' => 'Eka Putri Lestari', 'password' => $defaultPassword, 'role' => 'mahasiswa', 'university_id' => $unesa->id, 'university' => $unesa->name, 'status' => 'active', 'email_verified_at' => now()]
        );
        StudentProfile::updateOrCreate(
            ['user_id' => $userEka->id],
            ['nim' => '22081010015', 'universitas' => $unesa->name, 'university_id' => $unesa->id, 'jurusan' => 'S1 Sistem Informasi', 'major' => 'S1 Sistem Informasi', 'semester' => '6', 'phone' => '081234560015', 'alamat' => 'Jl. Lidah Kulon No. 22, Surabaya', 'address' => 'Jl. Lidah Kulon No. 22, Surabaya']
        );
        $appEka = Application::updateOrCreate(
            ['user_id' => $userEka->id],
            [
                'unit_id' => $unitPerpustakaan->id,
                'start_date' => Carbon::now()->subDays(20)->toDateString(),
                'end_date' => Carbon::now()->addDays(70)->toDateString(),
                'status' => ApplicationStatus::ACTIVE->value,
                'letter_number' => '500.12.1/040/436.7.14/2026',
                'letter_date' => Carbon::now()->subDays(25)->toDateString(),
                'letter_token' => Str::random(32),
            ]
        );
        $placementEka = Placement::updateOrCreate(
            ['application_id' => $appEka->id],
            ['mentor_id' => $mentorPerpus->id, 'pembimbing_id' => $mentorPerpus->id]
        );
        Logbook::updateOrCreate(
            ['placement_id' => $placementEka->id, 'date' => Carbon::now()->subDays(1)->toDateString()],
            ['activity' => 'Melakukan entri metadata buku digital pada modul otomasi perpustakaan Inlislite Dispusip Surabaya.', 'status' => 'approved', 'feedback' => 'Pekerjaan rapi dan teliti.']
        );
        InternshipProject::updateOrCreate(
            ['placement_id' => $placementEka->id, 'title' => 'Katalog Digital Koleksi Anak dan Remaja'],
            [
                'description' => 'Perancangan antarmuka pencarian buku anak interaktif pada perpustakaan umum.',
                'community_benefit' => 'Mempermudah anak-anak dan orang tua dalam memilih bacaan yang mendidik dan sesuai umur.',
                'photo_path' => 'images/logos/dispusip.png',
                'demo_url' => 'https://dispusip.surabaya.go.id',
            ]
        );

        // [STATUS REVISION] Mahasiswa Fajar Nugraha -> Dikembalikan Atasan ke Admin untuk Penyesuaian
        $userFajar = User::updateOrCreate(
            ['email' => 'fajar.revisi@unesa.ac.id'],
            ['name' => 'Fajar Nugraha', 'password' => $defaultPassword, 'role' => 'mahasiswa', 'university_id' => $unesa->id, 'university' => $unesa->name, 'status' => 'active', 'email_verified_at' => now()]
        );
        StudentProfile::updateOrCreate(
            ['user_id' => $userFajar->id],
            ['nim' => '22081010016', 'universitas' => $unesa->name, 'university_id' => $unesa->id, 'jurusan' => 'S1 Ilmu Perpustakaan', 'major' => 'S1 Ilmu Perpustakaan', 'semester' => '6', 'phone' => '081234560016', 'alamat' => 'Jl. Manukan Wetan No. 9, Surabaya', 'address' => 'Jl. Manukan Wetan No. 9, Surabaya']
        );
        $appFajar = Application::updateOrCreate(
            ['user_id' => $userFajar->id],
            [
                'unit_id' => $unitPerpustakaan->id,
                'start_date' => Carbon::now()->addDays(15)->toDateString(),
                'end_date' => Carbon::now()->addDays(105)->toDateString(),
                'status' => ApplicationStatus::PENDING->value,
                'rejection_note' => 'Catatan Revisi Atasan: Kuota mentor Budi Santoso sedang padat. Mohon koordinasikan penugasan mentor pendamping lainnya di Bidang Perpustakaan.',
                'rejection_reason' => 'Catatan Revisi Atasan: Kuota mentor Budi Santoso sedang padat. Mohon koordinasikan penugasan mentor pendamping lainnya di Bidang Perpustakaan.',
            ]
        );
        Placement::updateOrCreate(
            ['application_id' => $appFajar->id],
            ['mentor_id' => $mentorPerpus->id, 'pembimbing_id' => $mentorPerpus->id]
        );

        // [STATUS REJECTED] Mahasiswa Gilang Ramadhan -> Ditolak
        $userGilang = User::updateOrCreate(
            ['email' => 'gilang.ditolak@unesa.ac.id'],
            ['name' => 'Gilang Ramadhan', 'password' => $defaultPassword, 'role' => 'mahasiswa', 'university_id' => $unesa->id, 'university' => $unesa->name, 'status' => 'active', 'email_verified_at' => now()]
        );
        StudentProfile::updateOrCreate(
            ['user_id' => $userGilang->id],
            ['nim' => '22081010017', 'universitas' => $unesa->name, 'university_id' => $unesa->id, 'jurusan' => 'S1 Sosiologi', 'major' => 'S1 Sosiologi', 'semester' => '7', 'phone' => '081234560017', 'alamat' => 'Jl. Karang Menjangan No. 5, Surabaya', 'address' => 'Jl. Karang Menjangan No. 5, Surabaya']
        );
        Application::updateOrCreate(
            ['user_id' => $userGilang->id],
            [
                'unit_id' => $unitSekretariat->id,
                'start_date' => Carbon::now()->addDays(20)->toDateString(),
                'end_date' => Carbon::now()->addDays(110)->toDateString(),
                'status' => ApplicationStatus::REJECTED->value,
                'rejection_reason' => 'Alokasi formasi magang untuk jurusan bersangkutan pada periode ini belum dibuka.',
                'rejection_note' => 'Alokasi formasi magang untuk jurusan bersangkutan pada periode ini belum dibuka.',
            ]
        );

        // =========================================================================
        // FITUR 2: PENGESAHAN SERTIFIKAT KELULUSAN (DOUBLE ACC TAHAP 2)
        // =========================================================================

        // [SERTIFIKAT WAITING_APPROVAL #1] Mahasiswa Hana Pertiwi -> Siap di-ACC oleh Atasan
        $userHana = User::updateOrCreate(
            ['email' => 'hana.sertifikat@unesa.ac.id'],
            ['name' => 'Hana Pertiwi', 'password' => $defaultPassword, 'role' => 'mahasiswa', 'university_id' => $unesa->id, 'university' => $unesa->name, 'status' => 'active', 'email_verified_at' => now()]
        );
        StudentProfile::updateOrCreate(
            ['user_id' => $userHana->id],
            ['nim' => '22081010018', 'universitas' => $unesa->name, 'university_id' => $unesa->id, 'jurusan' => 'S1 Ilmu Perpustakaan', 'major' => 'S1 Ilmu Perpustakaan', 'semester' => '7', 'phone' => '081234560018', 'alamat' => 'Jl. Gunung Anyar Timur No. 34, Surabaya', 'address' => 'Jl. Gunung Anyar Timur No. 34, Surabaya']
        );
        $appHana = Application::updateOrCreate(
            ['user_id' => $userHana->id],
            [
                'unit_id' => $unitPerpustakaan->id,
                'start_date' => Carbon::now()->subMonths(3)->toDateString(),
                'end_date' => Carbon::now()->subDays(2)->toDateString(),
                'status' => ApplicationStatus::COMPLETED->value,
                'letter_number' => '500.12.1/012/436.7.14/2026',
                'letter_date' => Carbon::now()->subMonths(3)->toDateString(),
                'letter_token' => Str::random(32),
            ]
        );
        $placementHana = Placement::updateOrCreate(
            ['application_id' => $appHana->id],
            [
                'mentor_id' => $mentorPerpus->id,
                'pembimbing_id' => $mentorPerpus->id,
                'certificate_number' => 'SERT/101/DISPUSIP-SBY/2026',
                'certificate_hash' => Str::random(32),
            ]
        );
        Logbook::updateOrCreate(
            ['placement_id' => $placementHana->id, 'date' => Carbon::now()->subDays(10)->toDateString()],
            ['activity' => 'Menyelesaikan inventarisasi koleksi referensi dan uji coba katalog online.', 'status' => 'approved', 'feedback' => 'Sangat memuaskan.']
        );
        FinalReport::updateOrCreate(
            ['placement_id' => $placementHana->id],
            [
                'file_path' => $samplePdf,
                'final_report_path' => $samplePdf,
                'title' => 'Laporan Akhir: Optimalisasi Akses Temu Balik Informasi Perpustakaan Digital Dispusip',
                'status' => 'approved',
                'feedback' => 'Laporan komprehensif dan memenuhi standar kedinasan.',
            ]
        );
        InternshipProject::updateOrCreate(
            ['placement_id' => $placementHana->id, 'title' => 'Portal Layanan Pojok Baca Digital Dispusip'],
            [
                'description' => 'Aplikasi web pencarian dan reservasi buku fisik serta e-book di seluruh taman bacaan Kota Surabaya.',
                'community_benefit' => 'Meningkatkan literasi warga dan memangkas waktu pencarian koleksi bacaan dari 20 menit menjadi 3 menit.',
                'photo_path' => 'images/logos/dispusip.png',
                'demo_url' => 'https://dispusip.surabaya.go.id/pojok-baca',
            ]
        );
        InternshipProject::updateOrCreate(
            ['placement_id' => $placementHana->id, 'title' => 'Program Revitalisasi Pojok Baca Keliling'],
            [
                'description' => 'Dokumentasi program pendampingan membaca anak-anak di 5 titik taman kota Surabaya.',
                'community_benefit' => 'Menjangkau lebih dari 300 anak sekolah dasar untuk aktif membaca buku setiap akhir pekan.',
                'photo_path' => 'images/logos/surabaya.png',
            ]
        );
        Evaluation::updateOrCreate(
            ['placement_id' => $placementHana->id],
            [
                'nilai_disiplin' => 95,
                'nilai_kinerja' => 94,
                'nilai_laporan' => 94,
                'final_score' => 94.33,
                'grade' => 'A',
                'catatan' => 'Hana memiliki dedikasi tinggi, pemahaman otomasi perpustakaan yang unggul, dan etika kerja ASN yang sangat baik.',
                'custom_components' => [
                    'mentor' => [
                        ['name' => 'Disiplin, Kehadiran, & Ketaatan Tata Tertib Kedinasan', 'score' => 95],
                        ['name' => 'Kinerja Teknis & Inovasi Katalog Perpustakaan Digital', 'score' => 94],
                        ['name' => 'Penyusunan Dokumentasi Laporan Kearsipan & Perpustakaan', 'score' => 94],
                    ],
                ],
                'mentor_aspects' => [
                    ['name' => 'Disiplin, Kehadiran, & Ketaatan Tata Tertib Kedinasan', 'score' => 95],
                    ['name' => 'Kinerja Teknis & Inovasi Katalog Perpustakaan Digital', 'score' => 94],
                    ['name' => 'Penyusunan Dokumentasi Laporan Kearsipan & Perpustakaan', 'score' => 94],
                ],
            ]
        );
        Certificate::updateOrCreate(
            ['placement_id' => $placementHana->id],
            [
                'certificate_number' => 'SERT/101/DISPUSIP-SBY/2026',
                'file_path' => 'certificates/SERT_101_DISPUSIP-SBY_2026.pdf',
                'issued_at' => Carbon::now()->subDays(1)->toDateString(),
                'approval_status' => 'waiting_approval',
                'approved_by' => null,
                'approved_at' => null,
                'notes' => null,
            ]
        );

        // [SERTIFIKAT WAITING_APPROVAL #2] Mahasiswa Irfan Hakim -> Untuk diuji tombol Kembalikan untuk Revisi
        $userIrfan = User::updateOrCreate(
            ['email' => 'irfan.sertifikat@unitomo.ac.id'],
            ['name' => 'Irfan Hakim', 'password' => $defaultPassword, 'role' => 'mahasiswa', 'university_id' => $unitomo->id, 'university' => $unitomo->name, 'status' => 'active', 'email_verified_at' => now()]
        );
        StudentProfile::updateOrCreate(
            ['user_id' => $userIrfan->id],
            ['nim' => '22081010019', 'universitas' => $unitomo->name, 'university_id' => $unitomo->id, 'jurusan' => 'S1 Teknik Informatika', 'major' => 'S1 Teknik Informatika', 'semester' => '8', 'phone' => '081234560019', 'alamat' => 'Jl. Manyar Sabrangan No. 8, Surabaya', 'address' => 'Jl. Manyar Sabrangan No. 8, Surabaya']
        );
        $appIrfan = Application::updateOrCreate(
            ['user_id' => $userIrfan->id],
            [
                'unit_id' => $unitSekretariat->id,
                'start_date' => Carbon::now()->subMonths(3)->toDateString(),
                'end_date' => Carbon::now()->subDays(3)->toDateString(),
                'status' => ApplicationStatus::COMPLETED->value,
                'letter_number' => '500.12.1/014/436.7.14/2026',
                'letter_date' => Carbon::now()->subMonths(3)->toDateString(),
                'letter_token' => Str::random(32),
            ]
        );
        $placementIrfan = Placement::updateOrCreate(
            ['application_id' => $appIrfan->id],
            [
                'mentor_id' => $mentorSekretariat->id,
                'pembimbing_id' => $mentorSekretariat->id,
                'certificate_number' => 'SERT/102/DISPUSIP-SBY/2026',
                'certificate_hash' => Str::random(32),
            ]
        );
        Logbook::updateOrCreate(
            ['placement_id' => $placementIrfan->id, 'date' => Carbon::now()->subDays(12)->toDateString()],
            ['activity' => 'Pemeliharaan perangkat keras dan backup basis data kepegawaian internal Dispusip.', 'status' => 'approved', 'feedback' => 'Bagus dan tuntas.']
        );
        FinalReport::updateOrCreate(
            ['placement_id' => $placementIrfan->id],
            [
                'file_path' => $samplePdf,
                'final_report_path' => $samplePdf,
                'title' => 'Laporan Akhir: Manajemen Infrastruktur Komputer dan Jaringan Bidang Sekretariat Dispusip',
                'status' => 'approved',
                'feedback' => 'Laporan telah divalidasi pembimbing lapangan.',
            ]
        );
        InternshipProject::updateOrCreate(
            ['placement_id' => $placementIrfan->id, 'title' => 'Sistem Monitoring Jaringan dan Infrastruktur IT Dispusip'],
            [
                'description' => 'Dashboard pemantauan ketersediaan server dan akses jaringan lokal antar-ruang kerja kedinasan.',
                'community_benefit' => 'Menjamin kelancaran operasional layanan publik dan meminimalisir downtime sistem administrasi kearsipan.',
                'photo_path' => 'images/logos/dispusip.png',
            ]
        );
        Evaluation::updateOrCreate(
            ['placement_id' => $placementIrfan->id],
            [
                'nilai_disiplin' => 88,
                'nilai_kinerja' => 90,
                'nilai_laporan' => 88,
                'final_score' => 88.67,
                'grade' => 'A',
                'catatan' => 'Irfan terampil dalam troubleshooting sistem teknis kedinasan.',
                'custom_components' => [
                    'mentor' => [
                        ['name' => 'Disiplin, Kehadiran, & Ketaatan Tata Tertib Kedinasan', 'score' => 88],
                        ['name' => 'Kinerja Teknis & Inovasi Katalog Perpustakaan Digital', 'score' => 90],
                        ['name' => 'Penyusunan Dokumentasi Laporan Kearsipan & Perpustakaan', 'score' => 88],
                    ],
                ],
                'mentor_aspects' => [
                    ['name' => 'Disiplin, Kehadiran, & Ketaatan Tata Tertib Kedinasan', 'score' => 88],
                    ['name' => 'Kinerja Teknis & Inovasi Katalog Perpustakaan Digital', 'score' => 90],
                    ['name' => 'Penyusunan Dokumentasi Laporan Kearsipan & Perpustakaan', 'score' => 88],
                ],
            ]
        );
        Certificate::updateOrCreate(
            ['placement_id' => $placementIrfan->id],
            [
                'certificate_number' => 'SERT/102/DISPUSIP-SBY/2026',
                'file_path' => 'certificates/SERT_102_DISPUSIP-SBY_2026.pdf',
                'issued_at' => Carbon::now()->subDays(2)->toDateString(),
                'approval_status' => 'waiting_approval',
                'approved_by' => null,
                'approved_at' => null,
                'notes' => null,
            ]
        );

        // [SERTIFIKAT APPROVED] Mahasiswa Joko Prasetyo -> Sudah Disahkan Atasan
        $userJoko = User::updateOrCreate(
            ['email' => 'joko.lulus@unesa.ac.id'],
            ['name' => 'Joko Prasetyo', 'password' => $defaultPassword, 'role' => 'mahasiswa', 'university_id' => $unesa->id, 'university' => $unesa->name, 'status' => 'active', 'email_verified_at' => now()]
        );
        StudentProfile::updateOrCreate(
            ['user_id' => $userJoko->id],
            ['nim' => '22081010020', 'universitas' => $unesa->name, 'university_id' => $unesa->id, 'jurusan' => 'S1 Ilmu Perpustakaan', 'major' => 'S1 Ilmu Perpustakaan', 'semester' => '8', 'phone' => '081234560020', 'alamat' => 'Jl. Wonokromo No. 10, Surabaya', 'address' => 'Jl. Wonokromo No. 10, Surabaya']
        );
        $appJoko = Application::updateOrCreate(
            ['user_id' => $userJoko->id],
            [
                'unit_id' => $unitPerpustakaan->id,
                'start_date' => Carbon::now()->subMonths(4)->toDateString(),
                'end_date' => Carbon::now()->subDays(15)->toDateString(),
                'status' => ApplicationStatus::COMPLETED->value,
                'letter_number' => '500.12.1/008/436.7.14/2026',
                'letter_date' => Carbon::now()->subMonths(4)->toDateString(),
                'letter_token' => Str::random(32),
            ]
        );
        $placementJoko = Placement::updateOrCreate(
            ['application_id' => $appJoko->id],
            [
                'mentor_id' => $mentorPerpus->id,
                'pembimbing_id' => $mentorPerpus->id,
                'certificate_number' => 'SERT/099/DISPUSIP-SBY/2026',
                'certificate_hash' => Str::random(32),
            ]
        );
        Logbook::updateOrCreate(
            ['placement_id' => $placementJoko->id, 'date' => Carbon::now()->subDays(20)->toDateString()],
            ['activity' => 'Finalisasi serah terima pekerjaan preservasi arsip digital.', 'status' => 'approved', 'feedback' => 'Tuntas.']
        );
        FinalReport::updateOrCreate(
            ['placement_id' => $placementJoko->id],
            ['file_path' => $samplePdf, 'final_report_path' => $samplePdf, 'title' => 'Laporan Akhir: Tata Kelola Perpustakaan Terpadu', 'status' => 'approved', 'feedback' => 'Sangat baik.']
        );
        InternshipProject::updateOrCreate(
            ['placement_id' => $placementJoko->id, 'title' => 'Alih Media dan Digitalisasi Naskah Kuno Surabaya'],
            [
                'description' => 'Preservasi naskah kuno era kolonial dan prasasti lokal Surabaya ke dalam format digital beresolusi tinggi.',
                'community_benefit' => 'Menjaga warisan budaya Kota Surabaya agar dapat diakses oleh peneliti sejarah dan generasi penerus tanpa merusak fisik naskah asli.',
                'photo_path' => 'images/logos/surabaya.png',
            ]
        );
        Evaluation::updateOrCreate(
            ['placement_id' => $placementJoko->id],
            [
                'nilai_disiplin' => 96,
                'nilai_kinerja' => 97,
                'nilai_laporan' => 96,
                'final_score' => 96.33,
                'grade' => 'A',
                'catatan' => 'Kinerja luar biasa, siap terjun ke dunia profesional kearsipan.',
                'custom_components' => [
                    'mentor' => [
                        ['name' => 'Disiplin, Kehadiran, & Ketaatan Tata Tertib Kedinasan', 'score' => 96],
                        ['name' => 'Kinerja Teknis & Inovasi Katalog Perpustakaan Digital', 'score' => 97],
                        ['name' => 'Penyusunan Dokumentasi Laporan Kearsipan & Perpustakaan', 'score' => 96],
                    ],
                ],
                'mentor_aspects' => [
                    ['name' => 'Disiplin, Kehadiran, & Ketaatan Tata Tertib Kedinasan', 'score' => 96],
                    ['name' => 'Kinerja Teknis & Inovasi Katalog Perpustakaan Digital', 'score' => 97],
                    ['name' => 'Penyusunan Dokumentasi Laporan Kearsipan & Perpustakaan', 'score' => 96],
                ],
            ]
        );
        Certificate::updateOrCreate(
            ['placement_id' => $placementJoko->id],
            [
                'certificate_number' => 'SERT/099/DISPUSIP-SBY/2026',
                'file_path' => 'certificates/SERT_099_DISPUSIP-SBY_2026.pdf',
                'issued_at' => Carbon::now()->subDays(10)->toDateString(),
                'approval_status' => 'approved',
                'approved_by' => $supervisorDispusip->id,
                'approved_at' => Carbon::now()->subDays(8),
                'notes' => null,
            ]
        );

        // [SERTIFIKAT REVISION] Mahasiswa Kartika Sari -> Dikembalikan Atasan untuk Koreksi Nilai
        $userKartika = User::updateOrCreate(
            ['email' => 'kartika.revisi@unesa.ac.id'],
            ['name' => 'Kartika Sari', 'password' => $defaultPassword, 'role' => 'mahasiswa', 'university_id' => $unesa->id, 'university' => $unesa->name, 'status' => 'active', 'email_verified_at' => now()]
        );
        StudentProfile::updateOrCreate(
            ['user_id' => $userKartika->id],
            ['nim' => '22081010021', 'universitas' => $unesa->name, 'university_id' => $unesa->id, 'jurusan' => 'S1 Kearsipan', 'major' => 'S1 Kearsipan', 'semester' => '8', 'phone' => '081234560021', 'alamat' => 'Jl. Karah Agung No. 14, Surabaya', 'address' => 'Jl. Karah Agung No. 14, Surabaya']
        );
        $appKartika = Application::updateOrCreate(
            ['user_id' => $userKartika->id],
            [
                'unit_id' => $unitPreservasi->id,
                'start_date' => Carbon::now()->subMonths(3)->toDateString(),
                'end_date' => Carbon::now()->subDays(5)->toDateString(),
                'status' => ApplicationStatus::COMPLETED->value,
                'letter_number' => '500.12.1/019/436.7.14/2026',
                'letter_date' => Carbon::now()->subMonths(3)->toDateString(),
                'letter_token' => Str::random(32),
            ]
        );
        $placementKartika = Placement::updateOrCreate(
            ['application_id' => $appKartika->id],
            [
                'mentor_id' => $mentorPreservasi->id,
                'pembimbing_id' => $mentorPreservasi->id,
                'certificate_number' => 'SERT/103/DISPUSIP-SBY/2026',
                'certificate_hash' => Str::random(32),
            ]
        );
        Logbook::updateOrCreate(
            ['placement_id' => $placementKartika->id, 'date' => Carbon::now()->subDays(15)->toDateString()],
            ['activity' => 'Melakukan perekaman data restorasi naskah kearsipan.', 'status' => 'approved', 'feedback' => 'Baik.']
        );
        FinalReport::updateOrCreate(
            ['placement_id' => $placementKartika->id],
            ['file_path' => $samplePdf, 'final_report_path' => $samplePdf, 'title' => 'Laporan Akhir: Preservasi Naskah Kearsipan', 'status' => 'approved', 'feedback' => 'Laporan lengkap.']
        );
        Evaluation::updateOrCreate(
            ['placement_id' => $placementKartika->id],
            [
                'nilai_disiplin' => 80,
                'nilai_kinerja' => 85,
                'nilai_laporan' => 84,
                'final_score' => 83.0,
                'grade' => 'B+',
                'catatan' => 'Perlu ditingkatkan kedisiplinan pencatatan harian.',
                'custom_components' => [
                    'mentor' => [
                        ['name' => 'Disiplin, Kehadiran, & Ketaatan Tata Tertib Kedinasan', 'score' => 80],
                        ['name' => 'Kinerja Teknis & Inovasi Katalog Perpustakaan Digital', 'score' => 85],
                        ['name' => 'Penyusunan Dokumentasi Laporan Kearsipan & Perpustakaan', 'score' => 84],
                    ],
                ],
                'mentor_aspects' => [
                    ['name' => 'Disiplin, Kehadiran, & Ketaatan Tata Tertib Kedinasan', 'score' => 80],
                    ['name' => 'Kinerja Teknis & Inovasi Katalog Perpustakaan Digital', 'score' => 85],
                    ['name' => 'Penyusunan Dokumentasi Laporan Kearsipan & Perpustakaan', 'score' => 84],
                ],
            ]
        );
        Certificate::updateOrCreate(
            ['placement_id' => $placementKartika->id],
            [
                'certificate_number' => 'SERT/103/DISPUSIP-SBY/2026',
                'file_path' => 'certificates/SERT_103_DISPUSIP-SBY_2026.pdf',
                'issued_at' => Carbon::now()->subDays(4)->toDateString(),
                'approval_status' => 'revision',
                'approved_by' => null,
                'approved_at' => null,
                'notes' => 'Catatan Atasan: Mohon diverifikasi kembali skor kehadiran pada lembar presensi sebelum pengesahan sertifikat.',
            ]
        );
    }
}
