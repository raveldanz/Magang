<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\AgencyProfile;
use App\Models\Unit;
use App\Models\University;
use App\Models\StudentProfile;
use App\Models\Application;
use App\Models\Placement;
use App\Models\Logbook;
use App\Models\FinalReport;
use App\Models\Evaluation;
use App\Models\AuditLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DemoE2ESeeder extends Seeder
{
    public function run(): void
    {
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
            ]
        );

        $its = University::updateOrCreate(
            ['code' => 'ITS'],
            [
                'name' => 'Institut Teknologi Sepuluh Nopember',
                'logo' => 'images/logos/its.png',
                'address' => 'Kampus ITS Sukolilo, Surabaya 60111',
                'phone' => '(031) 5994251',
                'email' => 'humas@its.ac.id',
                'pic_name' => 'Prof. Dr. Ir. Mochamad Ashari, M.Eng.',
                'pic_nip' => '19651012 199103 1 003',
                'pic_position' => 'Rektor Institut Teknologi Sepuluh Nopember',
            ]
        );

        $unair = University::updateOrCreate(
            ['code' => 'UNAIR'],
            [
                'name' => 'Universitas Airlangga',
                'logo' => 'images/logos/unair.png',
                'address' => 'Jl. Airlangga No. 4-6, Surabaya 60115',
                'phone' => '(031) 5914042',
                'email' => 'humas@unair.ac.id',
                'pic_name' => 'Prof. Dr. Moh. Nasih, SE., MT., Ak.',
                'pic_nip' => '19650806 199203 1 002',
                'pic_position' => 'Rektor Universitas Airlangga',
            ]
        );

        $upn = University::updateOrCreate(
            ['code' => 'UPN'],
            [
                'name' => 'UPN Veteran Jawa Timur',
                'logo' => 'images/logos/upnjatim.png',
                'address' => 'Jl. Rungkut Madya No. 1, Gunung Anyar, Surabaya 60294',
                'phone' => '(031) 8706369',
                'email' => 'humas@upnjatim.ac.id',
                'pic_name' => 'Prof. Dr. Ir. Akhmad Fauzi, MMT., IPU.',
                'pic_nip' => '19651123 199003 1 002',
                'pic_position' => 'Rektor UPN Veteran Jawa Timur',
            ]
        );

        // =========================================================================
        // 2. MASTER INSTANSI DINAS PEMKOT SURABAYA
        // =========================================================================
        $kominfo = AgencyProfile::updateOrCreate(
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

        $dispusip = AgencyProfile::updateOrCreate(
            ['id' => 2],
            [
                'government_name' => 'Pemerintah Kota Surabaya',
                'agency_name' => 'Dinas Perpustakaan Dan Kearsipan',
                'address' => 'Jl. Rungkut Asri Tengah No. 5-7, Rungkut Kidul, Surabaya 60293',
                'phone' => '(031) 8704207',
                'email' => 'dispusip@surabaya.go.id',
                'website' => 'https://dispusip.surabaya.go.id',
                'signee_name' => 'Ir. Mia Santi Dewi, M.Si',
                'signee_nip' => '19680812 199403 2 007',
                'signee_position' => 'Kepala Dinas Perpustakaan dan Kearsipan',
                'city' => 'Surabaya',
            ]
        );

        // =========================================================================
        // 3. MASTER UNIT KERJA DINAS KOMINFO
        // =========================================================================
        $unitCsirt = Unit::updateOrCreate(
            ['agency_profile_id' => $kominfo->id, 'name' => 'Bidang Keamanan Informasi & Persandian (CSIRT Surabaya)'],
            [
                'description' => 'Pusat tanggap insiden siber (CSIRT), implementasi TTE BSrE, security hardening, dan audit keamanan sistem informasi',
                'quota' => 10,
            ]
        );

        $unitEgov = Unit::updateOrCreate(
            ['agency_profile_id' => $kominfo->id, 'name' => 'Bidang Layanan Informatika & E-Government'],
            [
                'description' => 'Pengembangan arsitektur SPBE, integrasi aplikasi layanan publik Pemkot Surabaya, dan portal WargaKu',
                'quota' => 15,
            ]
        );

        $unitPikp = Unit::updateOrCreate(
            ['agency_profile_id' => $kominfo->id, 'name' => 'Bidang Pengelolaan Informasi & Komunikasi Publik'],
            [
                'description' => 'Pengelolaan media komunikasi resmi, saluran pengaduan masyarakat, kehumasan, dan keterbukaan informasi publik (PPID)',
                'quota' => 10,
            ]
        );

        // =========================================================================
        // 4. SEED AKUN DEMO RESMI LENGKAP (6 ROLE)
        // =========================================================================

        // [ROLE 1] SUPER ADMIN UTAMA
        $superAdmin1 = User::updateOrCreate(
            ['email' => 'admin@surabaya.go.id'],
            [
                'name' => 'Super Administrator Pemkot Surabaya',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'agency_profile_id' => null,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $superAdmin2 = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrator Utama',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'agency_profile_id' => null,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // [ROLE 2A] ADMIN DINAS DISKOMINFO
        $adminKominfo = User::updateOrCreate(
            ['email' => 'admin.kominfo@surabaya.go.id'],
            [
                'name' => 'Admin Dinas Kominfo Surabaya',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'agency_profile_id' => $kominfo->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // [ROLE 2B] MENTOR LAPANGAN DINAS KOMINFO
        $mentorKominfo = User::updateOrCreate(
            ['email' => 'mentor.kominfo@surabaya.go.id'],
            [
                'name' => 'Ir. Siti Aminah, M.Kom (Mentor CSIRT)',
                'password' => Hash::make('password'),
                'role' => 'mentor',
                'agency_profile_id' => $kominfo->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // [ROLE 5] ADMIN UNIVERSITAS (UNESA & UNITOMO)
        $adminUnesa = User::updateOrCreate(
            ['email' => 'admin@unesa.ac.id'],
            [
                'name' => 'Portal Kampus Universitas Negeri Surabaya',
                'password' => Hash::make('password'),
                'role' => 'universitas',
                'university_id' => $unesa->id,
                'university' => $unesa->name,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $adminUnitomo = User::updateOrCreate(
            ['email' => 'admin@unitomo.ac.id'],
            [
                'name' => 'Portal Kampus Universitas Dr. Soetomo',
                'password' => Hash::make('password'),
                'role' => 'universitas',
                'university_id' => $unitomo->id,
                'university' => $unitomo->name,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // [ROLE 3] DOSEN PEMBIMBING LAPANGAN (DPL)
        $dplUnesa = User::updateOrCreate(
            ['email' => 'dosen.unesa@unesa.ac.id'],
            [
                'name' => 'Dr. Erina Nur Azizah, S.Kom., M.Cs (DPL UNESA)',
                'password' => Hash::make('password'),
                'role' => 'dosen',
                'university_id' => $unesa->id,
                'university' => $unesa->name,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $dplUnitomo = User::updateOrCreate(
            ['email' => 'dosen.unitomo@unitomo.ac.id'],
            [
                'name' => 'Dr. Ir. Bambang Supriyadi, M.Kom (DPL UNITOMO)',
                'password' => Hash::make('password'),
                'role' => 'dosen',
                'university_id' => $unitomo->id,
                'university' => $unitomo->name,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // =========================================================================
        // 5. SEED 3 SKENARIO MAHASISWA (COMPLETED, ACTIVE, SUBMITTED)
        // =========================================================================

        // -------------------------------------------------------------------------
        // SKENARIO 1: MAHASISWA LULUS / SELESAI MAGANG (COMPLETED)
        // -------------------------------------------------------------------------
        $mhsLulus = User::updateOrCreate(
            ['email' => 'mahasiswa.lulus@unesa.ac.id'],
            [
                'name' => 'Rizky Pratama (Lulus Magang)',
                'password' => Hash::make('password'),
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
                'emergency_contact_name' => 'Bpk. Pratama (Ayah)',
                'emergency_contact_phone' => '081234567000',
            ]
        );

        $appLulus = Application::updateOrCreate(
            ['user_id' => $mhsLulus->id],
            [
                'unit_id' => $unitCsirt->id,
                'start_date' => Carbon::now()->subMonths(3)->format('Y-m-d'),
                'end_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'proposal_letter_path' => 'documents/applications/sample_surat_pengantar.pdf',
                'cv_path' => 'documents/applications/sample_cv.pdf',
                'transcript_path' => 'documents/applications/sample_transkrip.pdf',
                'id_card_path' => 'documents/applications/sample_ktm.pdf',
                'status' => 'completed',
                'letter_number' => '500.12.2/091/436.7.14/2026',
                'letter_date' => Carbon::now()->subMonths(3)->subDays(3)->format('Y-m-d'),
            ]
        );

        $placementLulus = Placement::updateOrCreate(
            ['application_id' => $appLulus->id],
            [
                'mentor_id' => $mentorKominfo->id,
                'pembimbing_id' => $mentorKominfo->id,
                'academic_advisor_id' => $dplUnesa->id,
            ]
        );

        // Seed 6 Logbooks
        $logbookActivities = [
            ['hari' => 80, 'text' => 'Orientasi kedinasan, pengenalan arsitektur jaringan CSIRT Pemkot Surabaya, dan pembagian tugas monitoring.', 'fb' => 'Orientasi terlaksana dengan baik, silakan pahami SOP penanganan insiden siber.'],
            ['hari' => 65, 'text' => 'Melakukan pemindaian kerentanan (vulnerability assessment) pada web server portal publik menggunakan OWASP ZAP.', 'fb' => 'Analisis kerentanan sangat detail, lanjutkan mitigasi celah keamanan.'],
            ['hari' => 50, 'text' => 'Pemasangan Web Application Firewall (WAF) dan konfigurasi rule pembatasan akses DDOS.', 'fb' => 'Pekerjaan sesuai dengan standar kepatuhan BSrE.'],
            ['hari' => 35, 'text' => 'Implementasi modul enkripsi data kependudukan dan integrasi Tanda Tangan Elektronik (TTE).', 'fb' => 'Integrasi API berjalan lancar tanpa kendala.'],
            ['hari' => 20, 'text' => 'Pengujian penetrasi berkala dan simulasi penanganan insiden defacement website dinas.', 'fb' => 'Simulasi insiden berhasil diatasi dalam waktu di bawah 15 menit.'],
            ['hari' => 7, 'text' => 'Penyusunan draft laporan akhir magang dan penyerahan dokumentasi source code sistem ke tim dinas.', 'fb' => 'Naskah laporan dan source code telah diterima lengkap. Terima kasih atas dedikasi luar biasa.'],
        ];

        foreach ($logbookActivities as $idx => $lb) {
            Logbook::updateOrCreate(
                ['placement_id' => $placementLulus->id, 'date' => Carbon::now()->subDays($lb['hari'])->format('Y-m-d')],
                [
                    'activity' => $lb['text'],
                    'status' => 'approved',
                    'feedback' => $lb['fb'],
                    'lecturer_status' => 'approved',
                    'lecturer_feedback' => 'Logbook diverifikasi dan selaras dengan capaian pembelajaran magang MBKM.',
                    'lecturer_verified_at' => Carbon::now()->subDays($lb['hari'] - 1),
                ]
            );
        }

        // Laporan Akhir Disetujui
        FinalReport::updateOrCreate(
            ['placement_id' => $placementLulus->id],
            [
                'title' => 'Implementasi Sistem Deteksi Intrusi dan Security Hardening pada Server Layanan Publik Pemkot Surabaya',
                'repository_url' => 'https://github.com/surabaya-csirt/security-hardening-mbkm',
                'file_path' => 'final_reports/sample_laporan_akhir.pdf',
                'final_report_path' => 'final_reports/sample_laporan_akhir.pdf',
                'status' => 'approved',
                'feedback' => 'Naskah laporan akhir ilmiah sangat komprehensif, analisis tajam, dan telah memenuhi standar kelulusan MBKM tanpa revisi.',
            ]
        );

        // Evaluasi Nilai Lengkap (Dinas 40% + DPL 60% = 93.80 / A)
        Evaluation::updateOrCreate(
            ['placement_id' => $placementLulus->id],
            [
                'nilai_disiplin' => 92,
                'nilai_kinerja' => 94,
                'nilai_laporan' => 90, // Nilai Dinas (40%) = 92.00
                'score_mastery' => 96,
                'score_report' => 94,
                'score_attitude' => 95, // Nilai DPL (60%) = 95.00
                'nilai_akademik' => 95,
                'nilai_dosen' => 95.00,
                'final_score' => 93.80,
                'grade' => 'A',
                'catatan' => 'Mahasiswa menunjukkan kedisiplinan dan kapabilitas teknis yang sangat membanggakan selama bertugas di CSIRT Surabaya.',
                'feedback_dosen' => 'Penulisan laporan ilmiah sangat terstruktur, metodologi tepat, dan kontribusi nyata di dinas sangat luar biasa.',
                'catatan_dosen' => 'Penulisan laporan ilmiah sangat terstruktur, metodologi tepat, dan kontribusi nyata di dinas sangat luar biasa.',
            ]
        );

        // -------------------------------------------------------------------------
        // SKENARIO 2: MAHASISWA AKTIF BERJALAN DI LAPANGAN (ACTIVE)
        // -------------------------------------------------------------------------
        $mhsAktif = User::updateOrCreate(
            ['email' => 'mahasiswa.aktif@unesa.ac.id'],
            [
                'name' => 'Aditya Nugraha (Aktif Magang)',
                'password' => Hash::make('password'),
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
                'faculty' => 'Fakultas Teknik',
                'fakultas' => 'Fakultas Teknik',
                'jurusan' => 'Sistem Informasi',
                'major' => 'Sistem Informasi',
                'semester' => '5',
                'phone' => '081234567002',
                'alamat' => 'Jl. Lidah Kulon No. 45, Surabaya',
                'address' => 'Jl. Lidah Kulon No. 45, Surabaya',
                'emergency_contact_name' => 'Ibu Nugraha (Ibu)',
                'emergency_contact_phone' => '081234567003',
            ]
        );

        $appAktif = Application::updateOrCreate(
            ['user_id' => $mhsAktif->id],
            [
                'unit_id' => $unitEgov->id,
                'start_date' => Carbon::now()->subDays(25)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(65)->format('Y-m-d'),
                'proposal_letter_path' => 'documents/applications/sample_surat_pengantar.pdf',
                'cv_path' => 'documents/applications/sample_cv.pdf',
                'transcript_path' => 'documents/applications/sample_transkrip.pdf',
                'id_card_path' => 'documents/applications/sample_ktm.pdf',
                'status' => 'accepted',
                'letter_number' => '500.12.2/092/436.7.14/2026',
                'letter_date' => Carbon::now()->subDays(28)->format('Y-m-d'),
            ]
        );

        $placementAktif = Placement::updateOrCreate(
            ['application_id' => $appAktif->id],
            [
                'mentor_id' => $mentorKominfo->id,
                'pembimbing_id' => $mentorKominfo->id,
                'academic_advisor_id' => $dplUnesa->id,
            ]
        );

        // Seed 3 Logbooks Aktif
        Logbook::updateOrCreate(
            ['placement_id' => $placementAktif->id, 'date' => Carbon::now()->subDays(20)->format('Y-m-d')],
            [
                'activity' => 'Mempelajari API backend layanan WargaKu dan arsitektur microservices Pemkot.',
                'status' => 'approved',
                'feedback' => 'Bagus, lanjutkan dengan integrasi modul pengaduan masyarakat.',
            ]
        );
        Logbook::updateOrCreate(
            ['placement_id' => $placementAktif->id, 'date' => Carbon::now()->subDays(10)->format('Y-m-d')],
            [
                'activity' => 'Mengembangkan antarmuka filter laporan pengaduan berbasis lokasi geospasial kelurahan.',
                'status' => 'approved',
                'feedback' => 'Desain responsif dan loading time optimal.',
            ]
        );
        Logbook::updateOrCreate(
            ['placement_id' => $placementAktif->id, 'date' => Carbon::now()->subDays(2)->format('Y-m-d')],
            [
                'activity' => 'Melakukan pengujian usability testing bersama tim helpdesk Diskominfo.',
                'status' => 'pending',
            ]
        );

        // -------------------------------------------------------------------------
        // SKENARIO 3: MAHASISWA BARU DAFTAR (SUBMITTED / PENDING DINAS)
        // -------------------------------------------------------------------------
        $mhsBaru = User::updateOrCreate(
            ['email' => 'mahasiswa.baru@unitomo.ac.id'],
            [
                'name' => 'Dimas Setiawan (Baru Submit)',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'university_id' => $unitomo->id,
                'university' => $unitomo->name,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        StudentProfile::updateOrCreate(
            ['user_id' => $mhsBaru->id],
            [
                'nim' => '22081010003',
                'universitas' => $unitomo->name,
                'university_id' => $unitomo->id,
                'faculty' => 'Fakultas Ilmu Komputer',
                'fakultas' => 'Fakultas Ilmu Komputer',
                'jurusan' => 'Informatika',
                'major' => 'Informatika',
                'semester' => '5',
                'phone' => '081234567004',
                'alamat' => 'Jl. Manyar Sabrangan No. 77, Surabaya',
                'address' => 'Jl. Manyar Sabrangan No. 77, Surabaya',
                'emergency_contact_name' => 'Bpk. Setiawan (Ayah)',
                'emergency_contact_phone' => '081234567005',
            ]
        );

        Application::updateOrCreate(
            ['user_id' => $mhsBaru->id],
            [
                'unit_id' => $unitCsirt->id,
                'start_date' => Carbon::now()->addDays(14)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(3)->addDays(14)->format('Y-m-d'),
                'proposal_letter_path' => 'documents/applications/sample_surat_pengantar.pdf',
                'cv_path' => 'documents/applications/sample_cv.pdf',
                'transcript_path' => 'documents/applications/sample_transkrip.pdf',
                'id_card_path' => 'documents/applications/sample_ktm.pdf',
                'status' => 'pending',
            ]
        );

        AuditLog::record('DEMO_SEEDER_RUN', 'System', 1, [
            'scenario' => 'Full E2E Demo Suite Loaded',
            'students' => 3,
            'super_admin' => 'admin@surabaya.go.id',
            'admin_kominfo' => 'admin.kominfo@surabaya.go.id',
            'mentor_kominfo' => 'mentor.kominfo@surabaya.go.id',
            'admin_unesa' => 'admin@unesa.ac.id',
            'dosen_unesa' => 'dosen.unesa@unesa.ac.id',
        ]);
    }
}
