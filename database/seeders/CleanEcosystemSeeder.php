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
use App\Models\SystemFeedback;
use App\Models\SystemNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CleanEcosystemSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info("Memulai pembersihan akun sampah & duplikat...");

            // 1. DAFTAR EMAIL SAMPAH, JUNK, DAN DUPLIKAT
            $emailsToDelete = [
                // Junk / Unrealistic / Joke dummy accounts
                'bimoli@gmail.com',
                'evan@gmail.com',
                'budi@gmail.com',
                'testuniv@testuniv.ac.id',
                'sunarto@gmail.com',
                'universi@universi.ac.id',
                'eva@gmail.com',
                'dinkes@gmail.com',
                // Legacy desynchronized accounts
                'mhs.draft@unitomo.ac.id',
                'mhs.pending1@unesa.ac.id',
                'mhs.pending2@unair.ac.id',
                'mhs.accepted1@unitomo.ac.id',
                'mhs.accepted2@its.ac.id',
                'mhs.aktif1@unitomo.ac.id',
                'mhs.aktif2@unesa.ac.id',
                'mhs.aktif3@upnjatim.ac.id',
                'mhs.lulus1@unitomo.ac.id',
                'mhs.lulus2@unair.ac.id',
                'mahasiswa@gmail.com',
                'dimas.adam@mhs.unair.ac.id',
                'siti.nurhaliza@mhs.unesa.ac.id',
                'ahmad.fauzi@mhs.unitomo.ac.id',
                'nabila.putri@mhs.unesa.ac.id',
                'rizky.ramadhan@mhs.unitomo.ac.id',
                'anisa.rahma@mhs.unesa.ac.id',
                'fajar.dwi@mhs.its.ac.id',
                'dewi.anggraini@mhs.unair.ac.id',
                'bagus.tri@mhs.upnjatim.ac.id',
                'clara.salsabila@mhs.its.ac.id',
                'hafidz.m@mhs.unair.ac.id',
                'putri.maharani@mhs.upnjatim.ac.id',
                'mahasiswa.lulus@unesa.ac.id',
                'mahasiswa.aktif@unesa.ac.id',
                'mahasiswa.baru@unitomo.ac.id',
            ];

            // Dapatkan user target berdasarkan daftar email dan pola regex Hermes dimas.e2e.%
            $targetUsers = User::whereIn('email', $emailsToDelete)
                ->orWhere('email', 'like', 'dimas.e2e.%')
                ->get();

            $deletedUserCount = 0;
            foreach ($targetUsers as $u) {
                // Hapus aplikasi beserta relasinya
                foreach ($u->applications as $app) {
                    if ($app->placement) {
                        Evaluation::where('placement_id', $app->placement->id)->delete();
                        FinalReport::where('placement_id', $app->placement->id)->delete();
                        Logbook::where('placement_id', $app->placement->id)->delete();
                        $app->placement->delete();
                    }
                    $app->delete();
                }

                // Hapus penempatan dimana user bertindak sebagai mentor atau dosen
                Placement::where('mentor_id', $u->id)
                    ->orWhere('academic_advisor_id', $u->id)
                    ->orWhere('pembimbing_id', $u->id)
                    ->update(['mentor_id' => null, 'academic_advisor_id' => null, 'pembimbing_id' => null]);

                // Hapus profil mahasiswa
                StudentProfile::where('user_id', $u->id)->delete();

                // Hapus audit log & feedback
                AuditLog::where('user_id', $u->id)->delete();
                SystemFeedback::where('user_id', $u->id)->orWhere('responded_by', $u->id)->delete();
                SystemNotification::where('user_id', $u->id)->delete();

                $u->delete();
                $deletedUserCount++;
            }
            $this->command->info("Selesai membersihkan {$deletedUserCount} user sampah dan seluruh dependensinya.");

            // Bersihkan universitas palsu jika ada (Univ ID 7 dan 9)
            University::whereIn('id', [7, 9])->orWhere('name', 'like', '%Test%')->delete();

            // =========================================================================
            // 2. MASTER INSTANSI RESMI & UNIT KERJA
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

            $dukcapil = AgencyProfile::updateOrCreate(
                ['id' => 3],
                [
                    'government_name' => 'Pemerintah Kota Surabaya',
                    'agency_name' => 'Dinas Kependudukan Dan Pencatatan Sipil',
                    'address' => 'Jl. Manyar Kertoarjo No. 1, Manyar Sabrangan, Kec. Mulyorejo, Surabaya 60116',
                    'phone' => '(031) 5913222',
                    'email' => 'dispendukcapil@surabaya.go.id',
                    'website' => 'https://dispendukcapil.surabaya.go.id',
                    'signee_name' => 'Eddy Christijanto, Drs., M.Si',
                    'signee_nip' => '19670615 199303 1 005',
                    'signee_position' => 'Kepala Dinas Kependudukan dan Pencatatan Sipil',
                    'city' => 'Surabaya',
                ]
            );

            $dinkes = AgencyProfile::updateOrCreate(
                ['id' => 7],
                [
                    'government_name' => 'Pemerintah Kota Surabaya',
                    'agency_name' => 'Dinas Kesehatan',
                    'address' => 'Jl. Jemursari No. 197, Sidosermo, Kec. Wonocolo, Surabaya 60239',
                    'phone' => '(031) 8439473',
                    'email' => 'dinkes@surabaya.go.id',
                    'website' => 'https://dinkes.surabaya.go.id',
                    'signee_name' => 'Nanik Sukristina, S.KM., M.Kes.',
                    'signee_nip' => '19730514 199703 2 004',
                    'signee_position' => 'Kepala Dinas Kesehatan Kota Surabaya',
                    'city' => 'Surabaya',
                ]
            );

            // Unit Kerja
            $unitCsirt = Unit::updateOrCreate(
                ['agency_profile_id' => $kominfo->id, 'name' => 'Bidang Keamanan Informasi & Persandian (CSIRT Surabaya)'],
                ['description' => 'Penanganan insiden siber, pentest, dan sertifikasi TTE', 'quota' => 10]
            );
            $unitEgov = Unit::updateOrCreate(
                ['agency_profile_id' => $kominfo->id, 'name' => 'Bidang Layanan Informatika & E-Government'],
                ['description' => 'Pengembangan portal WargaKu, SPBE, dan integrasi API', 'quota' => 15]
            );
            $unitPikp = Unit::updateOrCreate(
                ['agency_profile_id' => $kominfo->id, 'name' => 'Bidang Pengelolaan Informasi & Komunikasi Publik'],
                ['description' => 'Pengelolaan media resmi, humas, dan portal PPID', 'quota' => 10]
            );

            $unitArsip = Unit::updateOrCreate(
                ['agency_profile_id' => $dispusip->id, 'name' => 'Bidang Preservasi & Pengelolaan Arsip Statis Elektronik'],
                ['description' => 'Digitalisasi naskah kuno dan arsip statis Pemkot Surabaya', 'quota' => 8]
            );
            $unitPerpus = Unit::updateOrCreate(
                ['agency_profile_id' => $dispusip->id, 'name' => 'Bidang Pelayanan & Otomasi Perpustakaan Digital'],
                ['description' => 'Pengembangan katalog digital e-Library Surabaya dan otomasi sirkulasi', 'quota' => 14]
            );

            $unitPiak = Unit::updateOrCreate(
                ['agency_profile_id' => $dukcapil->id, 'name' => 'Bidang Pengelolaan Informasi Administrasi Kependudukan (PIAK)'],
                ['description' => 'Sistem database kependudukan SIAK terpusat dan analitik data kependudukan', 'quota' => 10]
            );

            $unitYankes = Unit::updateOrCreate(
                ['agency_profile_id' => $dinkes->id, 'name' => 'Bidang Pelayanan Kesehatan & Sistem Informasi Puskesmas'],
                ['description' => 'Digitalisasi rekam medis elektronik dan bridging SIMPUS-BPJS', 'quota' => 10]
            );

            // =========================================================================
            // 3. MASTER UNIVERSITAS RESMI DI SURABAYA
            // =========================================================================
            $unesa = University::updateOrCreate(
                ['code' => 'UNESA'],
                ['name' => 'Universitas Negeri Surabaya', 'email' => 'humas@unesa.ac.id', 'logo' => 'images/logos/unesa.png']
            );
            $unitomo = University::updateOrCreate(
                ['code' => 'UNITOMO'],
                ['name' => 'Universitas Dr. Soetomo', 'email' => 'rektorat@unitomo.ac.id', 'logo' => 'images/logos/unitomo.png']
            );
            $its = University::updateOrCreate(
                ['code' => 'ITS'],
                ['name' => 'Institut Teknologi Sepuluh Nopember', 'email' => 'humas@its.ac.id', 'logo' => 'images/logos/its.png']
            );
            $unair = University::updateOrCreate(
                ['code' => 'UNAIR'],
                ['name' => 'Universitas Airlangga', 'email' => 'humas@unair.ac.id', 'logo' => 'images/logos/unair.png']
            );
            $upn = University::updateOrCreate(
                ['code' => 'UPN'],
                ['name' => 'UPN Veteran Jawa Timur', 'email' => 'humas@upnjatim.ac.id', 'logo' => 'images/logos/upnjatim.png']
            );

            // =========================================================================
            // 4. STANDARISASI AKUN UTAMA / MASTER DATA
            // =========================================================================
            $defaultPassword = Hash::make('password');

            // Super Admin
            User::updateOrCreate(
                ['email' => 'admin@surabaya.go.id'],
                ['name' => 'Super Administrator Pemkot Surabaya', 'password' => $defaultPassword, 'role' => 'admin', 'status' => 'active', 'agency_profile_id' => null, 'email_verified_at' => now()]
            );
            User::updateOrCreate(
                ['email' => 'admin@gmail.com'],
                ['name' => 'Administrator Utama', 'password' => Hash::make('admin123'), 'role' => 'admin', 'status' => 'active', 'agency_profile_id' => null, 'email_verified_at' => now()]
            );

            // Admin Dinas
            User::updateOrCreate(
                ['email' => 'admin.kominfo@surabaya.go.id'],
                ['name' => 'Admin Dinas Kominfo Surabaya', 'password' => $defaultPassword, 'role' => 'admin', 'status' => 'active', 'agency_profile_id' => $kominfo->id, 'email_verified_at' => now()]
            );
            User::updateOrCreate(
                ['email' => 'admin.dispusip@surabaya.go.id'],
                ['name' => 'Admin Dispusip Surabaya', 'password' => $defaultPassword, 'role' => 'admin', 'status' => 'active', 'agency_profile_id' => $dispusip->id, 'email_verified_at' => now()]
            );
            User::updateOrCreate(
                ['email' => 'admin.dispendukcapil@surabaya.go.id'],
                ['name' => 'Admin Dispendukcapil Surabaya', 'password' => $defaultPassword, 'role' => 'admin', 'status' => 'active', 'agency_profile_id' => $dukcapil->id, 'email_verified_at' => now()]
            );
            User::updateOrCreate(
                ['email' => 'admin.dinkes@surabaya.go.id'],
                ['name' => 'Admin Dinas Kesehatan Surabaya', 'password' => $defaultPassword, 'role' => 'admin', 'status' => 'active', 'agency_profile_id' => $dinkes->id, 'email_verified_at' => now()]
            );

            // Mentor Lapangan Dinas
            $mentorKominfo = User::updateOrCreate(
                ['email' => 'mentor.kominfo@surabaya.go.id'],
                ['name' => 'Ir. Siti Aminah, M.Kom (Mentor CSIRT)', 'password' => $defaultPassword, 'role' => 'mentor', 'status' => 'active', 'agency_profile_id' => $kominfo->id, 'email_verified_at' => now()]
            );
            $mentorDispusip = User::updateOrCreate(
                ['email' => 'mentor.dispusip1@surabaya.go.id'],
                ['name' => 'Budi Santoso, S.ST., M.MT (Mentor Arsip)', 'password' => $defaultPassword, 'role' => 'mentor', 'status' => 'active', 'agency_profile_id' => $dispusip->id, 'email_verified_at' => now()]
            );
            $mentorDukcapil = User::updateOrCreate(
                ['email' => 'mentor.dukcapil1@surabaya.go.id'],
                ['name' => 'Hendra Wijaya, S.Kom., M.Eng (Mentor PIAK)', 'password' => $defaultPassword, 'role' => 'mentor', 'status' => 'active', 'agency_profile_id' => $dukcapil->id, 'email_verified_at' => now()]
            );

            // Admin Universitas
            User::updateOrCreate(
                ['email' => 'admin@unesa.ac.id'],
                ['name' => 'Portal Kampus Universitas Negeri Surabaya', 'password' => $defaultPassword, 'role' => 'universitas', 'status' => 'active', 'university_id' => $unesa->id, 'university' => $unesa->name, 'email_verified_at' => now()]
            );
            User::updateOrCreate(
                ['email' => 'admin@unitomo.ac.id'],
                ['name' => 'Portal Kampus Universitas Dr. Soetomo', 'password' => $defaultPassword, 'role' => 'universitas', 'status' => 'active', 'university_id' => $unitomo->id, 'university' => $unitomo->name, 'email_verified_at' => now()]
            );

            // Dosen Pembimbing Lapangan (DPL)
            $dplUnesa = User::updateOrCreate(
                ['email' => 'dosen.unesa@unesa.ac.id'],
                ['name' => 'Dr. Erina Nur Azizah, S.Kom., M.Cs (DPL UNESA)', 'password' => $defaultPassword, 'role' => 'dosen', 'status' => 'active', 'university_id' => $unesa->id, 'university' => $unesa->name, 'email_verified_at' => now()]
            );
            $dplUnitomo = User::updateOrCreate(
                ['email' => 'dosen.unitomo@unitomo.ac.id'],
                ['name' => 'Dr. Ir. Bambang Supriyadi, M.Kom (DPL UNITOMO)', 'password' => $defaultPassword, 'role' => 'dosen', 'status' => 'active', 'university_id' => $unitomo->id, 'university' => $unitomo->name, 'email_verified_at' => now()]
            );
            $dplUpn = User::updateOrCreate(
                ['email' => 'dosen.upn@upnjatim.ac.id'],
                ['name' => 'Dr. Eng. Yasin Al-Aqsho, S.Kom., M.Kom (DPL UPN)', 'password' => $defaultPassword, 'role' => 'dosen', 'status' => 'active', 'university_id' => $upn->id, 'university' => $upn->name, 'email_verified_at' => now()]
            );
            $dplUnair = User::updateOrCreate(
                ['email' => 'dosen.unair1@unair.ac.id'],
                ['name' => 'Dr. Rimuljo Hendradi, S.Si., M.Si (DPL UNAIR)', 'password' => $defaultPassword, 'role' => 'dosen', 'status' => 'active', 'university_id' => $unair->id, 'university' => $unair->name, 'email_verified_at' => now()]
            );

            // =========================================================================
            // 5. PENYEDIAAN DATA REALISTIS 7 TAHAP PROGRES MAHASISWA (LIFECYCLE MATRIX)
            // =========================================================================
            $this->command->info("Menyusun 7 akun mahasiswa representasi siklus nyata...");

            // TAHAP 1: BARU DAFTAR (PROFIL LENGKAP, BELUM MELAMAR)
            $mhsStage1 = User::updateOrCreate(
                ['email' => 'dewi.kartika@mhs.unesa.ac.id'],
                ['name' => 'Dewi Kartika Sari', 'password' => $defaultPassword, 'role' => 'mahasiswa', 'status' => 'active', 'university_id' => $unesa->id, 'university' => $unesa->name, 'email_verified_at' => now()]
            );
            StudentProfile::updateOrCreate(
                ['user_id' => $mhsStage1->id],
                [
                    'nim' => '22051204010', 'universitas' => $unesa->name, 'university_id' => $unesa->id,
                    'faculty' => 'Fakultas Teknik', 'fakultas' => 'Fakultas Teknik',
                    'jurusan' => 'Pendidikan Teknologi Informasi', 'major' => 'Pendidikan Teknologi Informasi',
                    'semester' => '6', 'phone' => '081234567111', 'alamat' => 'Jl. Ketintang Madya No. 45, Surabaya',
                    'address' => 'Jl. Ketintang Madya No. 45, Surabaya', 'emergency_contact_name' => 'Kartika (Ibu)', 'emergency_contact_phone' => '081234567110'
                ]
            );

            // TAHAP 2: PENGAJUAN BARU DIAJUKAN (STATUS PENDING - MENUNGGU REVIEW DINAS)
            $mhsStage2 = User::updateOrCreate(
                ['email' => 'farhan.maulana@mhs.unitomo.ac.id'],
                ['name' => 'Farhan Maulana Pratama', 'password' => $defaultPassword, 'role' => 'mahasiswa', 'status' => 'active', 'university_id' => $unitomo->id, 'university' => $unitomo->name, 'email_verified_at' => now()]
            );
            StudentProfile::updateOrCreate(
                ['user_id' => $mhsStage2->id],
                [
                    'nim' => '202241010045', 'universitas' => $unitomo->name, 'university_id' => $unitomo->id,
                    'faculty' => 'Fakultas Ilmu Komputer', 'fakultas' => 'Fakultas Ilmu Komputer',
                    'jurusan' => 'Teknik Informatika', 'major' => 'Teknik Informatika',
                    'semester' => '6', 'phone' => '081234567222', 'alamat' => 'Jl. Nginden Semolo No. 88, Surabaya',
                    'address' => 'Jl. Nginden Semolo No. 88, Surabaya', 'emergency_contact_name' => 'Pratama (Ayah)', 'emergency_contact_phone' => '081234567220'
                ]
            );
            Application::updateOrCreate(
                ['user_id' => $mhsStage2->id],
                [
                    'unit_id' => $unitEgov->id,
                    'start_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
                    'end_date' => Carbon::now()->addMonths(3)->addDays(7)->format('Y-m-d'),
                    'status' => 'pending',
                    'proposal_letter_path' => 'documents/applications/10LBy2J9fDWtTow9L2vqS3k7KnpCzDnkxZ2SJ0O9.pdf',
                    'cv_path' => 'documents/applications/3riCI6vGLiScPQfp94D3uuwrNJoM1rCKxD26G172.pdf',
                    'transcript_path' => 'documents/applications/5AdFAcelgeprCcoR82Brj5GF2QzWJvqIMPwbrfc2.pdf',
                    'id_card_path' => 'documents/applications/5O8TJlnXvXGHsTxOSFfmR8BXpRw98pbziIGdphnG.pdf',
                ]
            );

            // TAHAP 3: PENGAJUAN DITOLAK DINAS (STATUS REJECTED DENGAN CATATAN RESMI)
            $mhsStage3 = User::updateOrCreate(
                ['email' => 'anisa.septiani@mhs.unesa.ac.id'],
                ['name' => 'Anisa Septiani', 'password' => $defaultPassword, 'role' => 'mahasiswa', 'status' => 'active', 'university_id' => $unesa->id, 'university' => $unesa->name, 'email_verified_at' => now()]
            );
            StudentProfile::updateOrCreate(
                ['user_id' => $mhsStage3->id],
                [
                    'nim' => '22051214022', 'universitas' => $unesa->name, 'university_id' => $unesa->id,
                    'faculty' => 'Fakultas Teknik', 'fakultas' => 'Fakultas Teknik',
                    'jurusan' => 'Sistem Informasi', 'major' => 'Sistem Informasi',
                    'semester' => '6', 'phone' => '081234567333', 'alamat' => 'Jl. Lidah Kulon No. 12, Surabaya',
                    'address' => 'Jl. Lidah Kulon No. 12, Surabaya', 'emergency_contact_name' => 'Septiani (Ibu)', 'emergency_contact_phone' => '081234567330'
                ]
            );
            Application::updateOrCreate(
                ['user_id' => $mhsStage3->id],
                [
                    'unit_id' => $unitCsirt->id,
                    'start_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
                    'end_date' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                    'status' => 'rejected',
                    'rejection_note' => 'Kuota pendaftar pada Bidang Keamanan Informasi telah penuh untuk periode ini. Silakan mendaftar pada Bidang Layanan Informatika & E-Government.',
                    'rejection_reason' => 'Kuota pendaftar pada Bidang Keamanan Informasi telah penuh untuk periode ini. Silakan mendaftar pada Bidang Layanan Informatika & E-Government.',
                    'proposal_letter_path' => 'documents/applications/7DinuOZUelTosbtSVhsc4iRPvc2CAFP1DidGmaXR.pdf',
                    'cv_path' => 'documents/applications/bzpkfSZ50DdRaW4WdYjHZTRJSJvBcA7zBsZkzmBc.pdf',
                    'transcript_path' => 'documents/applications/eyCllWCDURIChwCcske5hnygK8aZ9Mbm8sJ0D5rx.pdf',
                    'id_card_path' => 'documents/applications/hoX8PjSzM9HGjFQxBMSsVerhei9W6qevro7VPGER.pdf',
                ]
            );

            // TAHAP 4: PENGAJUAN DITERIMA / PENEMPATAN AKTIF - MENUNGGU PLOTTING DPL OLEH KAMPUS
            $mhsStage4 = User::updateOrCreate(
                ['email' => 'rian.kurniawan@mhs.upnjatim.ac.id'],
                ['name' => 'Rian Kurniawan', 'password' => $defaultPassword, 'role' => 'mahasiswa', 'status' => 'active', 'university_id' => $upn->id, 'university' => $upn->name, 'email_verified_at' => now()]
            );
            StudentProfile::updateOrCreate(
                ['user_id' => $mhsStage4->id],
                [
                    'nim' => '22081010088', 'universitas' => $upn->name, 'university_id' => $upn->id,
                    'faculty' => 'Fakultas Ilmu Komputer', 'fakultas' => 'Fakultas Ilmu Komputer',
                    'jurusan' => 'Informatika', 'major' => 'Informatika',
                    'semester' => '6', 'phone' => '081234567444', 'alamat' => 'Jl. Rungkut Asri Timur No. 20, Surabaya',
                    'address' => 'Jl. Rungkut Asri Timur No. 20, Surabaya', 'emergency_contact_name' => 'Kurniawan (Ayah)', 'emergency_contact_phone' => '081234567440'
                ]
            );
            $appStage4 = Application::updateOrCreate(
                ['user_id' => $mhsStage4->id],
                [
                    'unit_id' => $unitCsirt->id,
                    'start_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                    'end_date' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                    'status' => 'accepted',
                    'letter_number' => '500.12.2/105/436.7.14/2026',
                    'letter_date' => Carbon::now()->subDays(7)->format('Y-m-d'),
                    'proposal_letter_path' => 'documents/applications/idWMezdheCB9ZNZvfZHDPmStogaPz4Hv3ZrVuyyQ.pdf',
                    'cv_path' => 'documents/applications/kolYZSlGrqTuUFc4DbgDnlx5UVExxis5gxLkTeJw.pdf',
                    'transcript_path' => 'documents/applications/mw22HtAZqk4Z0pDnfdfyf7A9LOFCHbezgsjn0Wwn.pdf',
                    'id_card_path' => 'documents/applications/n8T0yWgnD9wqaivWctfdx19qybm94p8pxFwxgBDm.pdf',
                ]
            );
            Placement::updateOrCreate(
                ['application_id' => $appStage4->id],
                [
                    'mentor_id' => $mentorKominfo->id,
                    'pembimbing_id' => $mentorKominfo->id,
                    'academic_advisor_id' => null, // Belum di-assign oleh koordinator kampus
                ]
            );

            // TAHAP 5: MAGANG AKTIF BERJALAN (MENTOR & DPL LENGKAP, LOGBOOK AKTIF)
            $mhsStage5 = User::updateOrCreate(
                ['email' => 'bagas.saputra@mhs.unitomo.ac.id'],
                ['name' => 'Muhammad Bagas Saputra', 'password' => $defaultPassword, 'role' => 'mahasiswa', 'status' => 'active', 'university_id' => $unitomo->id, 'university' => $unitomo->name, 'email_verified_at' => now()]
            );
            StudentProfile::updateOrCreate(
                ['user_id' => $mhsStage5->id],
                [
                    'nim' => '202241010052', 'universitas' => $unitomo->name, 'university_id' => $unitomo->id,
                    'faculty' => 'Fakultas Ilmu Komputer', 'fakultas' => 'Fakultas Ilmu Komputer',
                    'jurusan' => 'Teknik Informatika', 'major' => 'Teknik Informatika',
                    'semester' => '6', 'phone' => '081234567555', 'alamat' => 'Jl. Semolowaru Elok No. 15, Surabaya',
                    'address' => 'Jl. Semolowaru Elok No. 15, Surabaya', 'emergency_contact_name' => 'Saputra (Ayah)', 'emergency_contact_phone' => '081234567550'
                ]
            );
            $appStage5 = Application::updateOrCreate(
                ['user_id' => $mhsStage5->id],
                [
                    'unit_id' => $unitArsip->id,
                    'start_date' => Carbon::now()->subDays(20)->format('Y-m-d'),
                    'end_date' => Carbon::now()->addMonths(2)->format('Y-m-d'),
                    'status' => 'accepted',
                    'letter_number' => '500.12.2/098/436.7.14/2026',
                    'letter_date' => Carbon::now()->subDays(22)->format('Y-m-d'),
                    'proposal_letter_path' => 'documents/applications/NDsOEHUvPKLZjGXGAALShK4VDrIqwxRSzxcMFMnY.pdf',
                    'cv_path' => 'documents/applications/neWT8jI2DKOwJfBtThgDJJfbM35peiUoXu1EPx2K.pdf',
                    'transcript_path' => 'documents/applications/PHZuRXS28YXk4WM9qinArTbrfADkfoS9VgSlm1jU.pdf',
                    'id_card_path' => 'documents/applications/PX7tkV2SI1ScVseBsTJ2VqaT9Y1emzlTHuFVlc2Y.pdf',
                ]
            );
            $plcStage5 = Placement::updateOrCreate(
                ['application_id' => $appStage5->id],
                [
                    'mentor_id' => $mentorDispusip->id,
                    'pembimbing_id' => $mentorDispusip->id,
                    'academic_advisor_id' => $dplUnitomo->id,
                ]
            );
            // Isi 6 logbook aktif
            for ($i = 15; $i >= 2; $i -= 3) {
                Logbook::updateOrCreate(
                    ['placement_id' => $plcStage5->id, 'date' => Carbon::now()->subDays($i)->format('Y-m-d')],
                    [
                        'activity' => "Melakukan kurasi metadata dan digitalisasi naskah arsip daerah volume ke-{$i}.",
                        'status' => ($i > 5 ? 'approved' : 'pending'),
                        'feedback' => ($i > 5 ? 'Pekerjaan rapi dan sesuai indeks arsip dinas.' : null),
                        'lecturer_status' => ($i > 5 ? 'approved' : 'pending'),
                        'lecturer_feedback' => ($i > 5 ? 'Telah selaras dengan silabus magang.' : null),
                    ]
                );
            }

            // TAHAP 6: LAPORAN AKHIR DIAJUKAN (MENUNGGU REVIEW & ACC MENTOR/DPL)
            $mhsStage6 = User::updateOrCreate(
                ['email' => 'nurul.izzah@mhs.unesa.ac.id'],
                ['name' => 'Nurul Izzah Zahirah', 'password' => $defaultPassword, 'role' => 'mahasiswa', 'status' => 'active', 'university_id' => $unesa->id, 'university' => $unesa->name, 'email_verified_at' => now()]
            );
            StudentProfile::updateOrCreate(
                ['user_id' => $mhsStage6->id],
                [
                    'nim' => '22051214035', 'universitas' => $unesa->name, 'university_id' => $unesa->id,
                    'faculty' => 'Fakultas Teknik', 'fakultas' => 'Fakultas Teknik',
                    'jurusan' => 'Sistem Informasi', 'major' => 'Sistem Informasi',
                    'semester' => '6', 'phone' => '081234567666', 'alamat' => 'Jl. Mayjen Sungkono No. 50, Surabaya',
                    'address' => 'Jl. Mayjen Sungkono No. 50, Surabaya', 'emergency_contact_name' => 'Zahirah (Ibu)', 'emergency_contact_phone' => '081234567660'
                ]
            );
            $appStage6 = Application::updateOrCreate(
                ['user_id' => $mhsStage6->id],
                [
                    'unit_id' => $unitEgov->id,
                    'start_date' => Carbon::now()->subMonths(3)->format('Y-m-d'),
                    'end_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
                    'status' => 'accepted',
                    'letter_number' => '500.12.2/076/436.7.14/2026',
                    'letter_date' => Carbon::now()->subMonths(3)->subDays(2)->format('Y-m-d'),
                    'proposal_letter_path' => 'documents/applications/r4GoQag4ukN2ZCWq7qemFmyUfsoT6INgkFEUYBpS.pdf',
                    'cv_path' => 'documents/applications/SS5KXdffW8zRiZ2ova3CjF1cZykHIpLvjqYDTtsh.pdf',
                    'transcript_path' => 'documents/applications/tFqbAeimmOefB8axOsnUpSMZ6hp4QtoJfTbrxdhG.pdf',
                    'id_card_path' => 'documents/applications/TxTpbus97fdGIQthXa3BdmDwPzfqnJaOSQSPjUA2.pdf',
                ]
            );
            $plcStage6 = Placement::updateOrCreate(
                ['application_id' => $appStage6->id],
                [
                    'mentor_id' => $mentorKominfo->id,
                    'pembimbing_id' => $mentorKominfo->id,
                    'academic_advisor_id' => $dplUnesa->id,
                ]
            );
            // Logbook lengkap
            for ($k = 60; $k >= 10; $k -= 10) {
                Logbook::updateOrCreate(
                    ['placement_id' => $plcStage6->id, 'date' => Carbon::now()->subDays($k)->format('Y-m-d')],
                    [
                        'activity' => "Pengembangan dan optimalisasi endpoint API SPBE Surabaya tahap {$k}.",
                        'status' => 'approved',
                        'feedback' => 'Kode bersih dan sesuai arsitektur RESTful.',
                        'lecturer_status' => 'approved',
                        'lecturer_feedback' => 'Bagus, implementasi sesuai dengan standar industri.',
                    ]
                );
            }
            // Final report terunggah status pending
            FinalReport::updateOrCreate(
                ['placement_id' => $plcStage6->id],
                [
                    'title' => 'Rancang Bangun Integrasi Antarmuka Layanan Publik Berbasis Microservices pada Portal WargaKu Surabaya',
                    'repository_url' => 'https://github.com/nurul-izzah/wargaku-microservices-integration',
                    'file_path' => 'final_reports/sample_laporan_akhir.pdf',
                    'final_report_path' => 'final_reports/sample_laporan_akhir.pdf',
                    'status' => 'pending',
                ]
            );

            // TAHAP 7: LULUS MAGANG (EVALUASI DUAL COMPLETED, SERTIFIKAT TERBIT)
            $mhsStage7 = User::updateOrCreate(
                ['email' => 'ahmad.zulfikar@mhs.unair.ac.id'],
                ['name' => 'Ahmad Zulfikar Rahman', 'password' => $defaultPassword, 'role' => 'mahasiswa', 'status' => 'active', 'university_id' => $unair->id, 'university' => $unair->name, 'email_verified_at' => now()]
            );
            StudentProfile::updateOrCreate(
                ['user_id' => $mhsStage7->id],
                [
                    'nim' => '22081010065', 'universitas' => $unair->name, 'university_id' => $unair->id,
                    'faculty' => 'Fakultas Sains dan Teknologi', 'fakultas' => 'Fakultas Sains dan Teknologi',
                    'jurusan' => 'Sistem Informasi', 'major' => 'Sistem Informasi',
                    'semester' => '6', 'phone' => '081234567777', 'alamat' => 'Jl. Dharmawangsa No. 29, Surabaya',
                    'address' => 'Jl. Dharmawangsa No. 29, Surabaya', 'emergency_contact_name' => 'Rahman (Ayah)', 'emergency_contact_phone' => '081234567770'
                ]
            );
            $appStage7 = Application::updateOrCreate(
                ['user_id' => $mhsStage7->id],
                [
                    'unit_id' => $unitPiak->id,
                    'start_date' => Carbon::now()->subMonths(4)->format('Y-m-d'),
                    'end_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
                    'status' => 'completed',
                    'letter_number' => '500.12.2/045/436.7.14/2026',
                    'letter_date' => Carbon::now()->subMonths(4)->subDays(3)->format('Y-m-d'),
                    'proposal_letter_path' => 'documents/applications/uKTkqKsWYJHOg0nf6DYb06PGxphTrB9oSXiV8Ug7.pdf',
                    'cv_path' => 'documents/applications/XkAUXZdq5B898TxSykUtjja9RUYv91ztY1RZB3Oe.pdf',
                    'transcript_path' => 'documents/applications/XRN18GGyIUERTSdQdyNekMVWJBjVPtNeywe9bhcr.pdf',
                    'id_card_path' => 'documents/applications/10LBy2J9fDWtTow9L2vqS3k7KnpCzDnkxZ2SJ0O9.pdf',
                ]
            );
            $plcStage7 = Placement::updateOrCreate(
                ['application_id' => $appStage7->id],
                [
                    'mentor_id' => $mentorDukcapil->id,
                    'pembimbing_id' => $mentorDukcapil->id,
                    'academic_advisor_id' => $dplUnair->id,
                    'certificate_number' => 'SERT/00045/PEMKOT-SBY/2026',
                    'certificate_hash' => hash('sha256', 'SERT/00045/PEMKOT-SBY/2026' . $mhsStage7->id . 'Dukcapil'),
                ]
            );
            // Logbook lengkap
            for ($m = 70; $m >= 10; $m -= 12) {
                Logbook::updateOrCreate(
                    ['placement_id' => $plcStage7->id, 'date' => Carbon::now()->subDays($m)->format('Y-m-d')],
                    [
                        'activity' => "Analisis anomali data NIK ganda dan sinkronisasi SIAK terpusat tahap {$m}.",
                        'status' => 'approved',
                        'feedback' => 'Analisis komprehensif dan akurat.',
                        'lecturer_status' => 'approved',
                        'lecturer_feedback' => 'Sangat relevan dengan mata kuliah data mining.',
                    ]
                );
            }
            FinalReport::updateOrCreate(
                ['placement_id' => $plcStage7->id],
                [
                    'title' => 'Penerapan Algoritma Deduplikasi Data Kependudukan Terdistribusi pada Sistem SIAK Terpusat Kota Surabaya',
                    'repository_url' => 'https://github.com/ahmad-zulfikar/siak-deduplication-analytics',
                    'file_path' => 'final_reports/sample_laporan_akhir.pdf',
                    'final_report_path' => 'final_reports/sample_laporan_akhir.pdf',
                    'status' => 'approved',
                    'feedback' => 'Laporan sangat memuaskan dan telah diuji langsung pada lingkungan staging.',
                ]
            );
            Evaluation::updateOrCreate(
                ['placement_id' => $plcStage7->id],
                [
                    'nilai_disiplin' => 92,
                    'nilai_kinerja' => 94,
                    'nilai_laporan' => 90,
                    'score_mastery' => 95,
                    'score_report' => 94,
                    'score_attitude' => 96,
                    'nilai_akademik' => 95,
                    'nilai_dosen' => 95.00,
                    'final_score' => 93.90,
                    'grade' => 'A',
                    'catatan' => 'Sangat bertanggung jawab dan memiliki dedikasi tinggi selama magang di Dispendukcapil Surabaya.',
                    'catatan_dosen' => 'Karya ilmiah aplikatif, penguasaan metodologi sangat matang.',
                    'feedback_dosen' => 'Karya ilmiah aplikatif, penguasaan metodologi sangat matang.',
                ]
            );

            // =========================================================================
            // 6. SUITE 1: 6 AKUN KHUSUS TESTING DESKTOP (BROWSER TOOL + HERMES)
            // =========================================================================
            $this->command->info("Menyiapkan Suite 1: Akun Testing Desktop (Password: Password123!)...");

            $testPassword = Hash::make('Password123!');

            // 1. Super Admin Desktop
            User::updateOrCreate(
                ['email' => 'superadmin.desktop@surabaya.go.id'],
                ['name' => 'H. Mochamad Syafii, S.Sos., M.AP (Superadmin Desktop)', 'password' => $testPassword, 'role' => 'admin', 'status' => 'active', 'agency_profile_id' => null, 'email_verified_at' => now()]
            );

            // 2. Admin Dinas Diskominfo Desktop
            User::updateOrCreate(
                ['email' => 'admin.kominfo.desktop@surabaya.go.id'],
                ['name' => 'Drs. Hendro Gunawan, M.Si (Admin Kominfo Desktop)', 'password' => $testPassword, 'role' => 'admin', 'status' => 'active', 'agency_profile_id' => $kominfo->id, 'email_verified_at' => now()]
            );

            // 3. Mentor Dinas Diskominfo Desktop
            User::updateOrCreate(
                ['email' => 'mentor.kominfo.desktop@surabaya.go.id'],
                ['name' => 'Danang Wicaksono, S.Kom., M.Cs (Mentor Kominfo Desktop)', 'password' => $testPassword, 'role' => 'mentor', 'status' => 'active', 'agency_profile_id' => $kominfo->id, 'email_verified_at' => now()]
            );

            // 4. Admin Universitas UNESA Desktop
            User::updateOrCreate(
                ['email' => 'admin.unesa.desktop@unesa.ac.id'],
                ['name' => 'Koordinator MBKM UNESA (Admin Kampus Desktop)', 'password' => $testPassword, 'role' => 'universitas', 'status' => 'active', 'university_id' => $unesa->id, 'university' => $unesa->name, 'email_verified_at' => now()]
            );

            // 5. Dosen Pembimbing Lapangan UNESA Desktop
            User::updateOrCreate(
                ['email' => 'dosen.unesa.desktop@unesa.ac.id'],
                ['name' => 'Dr. Rahmad Hidayat, S.Kom., M.Kom (DPL UNESA Desktop)', 'password' => $testPassword, 'role' => 'dosen', 'status' => 'active', 'university_id' => $unesa->id, 'university' => $unesa->name, 'email_verified_at' => now()]
            );

            // 6. Mahasiswa UNESA Desktop (Siap Uji Coba Alur dari Awal sampai Akhir)
            $mhsDesktop = User::updateOrCreate(
                ['email' => 'mhs.unesa.desktop@unesa.ac.id'],
                ['name' => 'Gilang Pratama Yudha', 'password' => $testPassword, 'role' => 'mahasiswa', 'status' => 'active', 'university_id' => $unesa->id, 'university' => $unesa->name, 'email_verified_at' => now()]
            );
            StudentProfile::updateOrCreate(
                ['user_id' => $mhsDesktop->id],
                [
                    'nim' => '22051204099', 'universitas' => $unesa->name, 'university_id' => $unesa->id,
                    'faculty' => 'Fakultas Teknik', 'fakultas' => 'Fakultas Teknik',
                    'jurusan' => 'Teknik Informatika', 'major' => 'Teknik Informatika',
                    'semester' => '6', 'phone' => '081234567901', 'alamat' => 'Jl. Ketintang Madya No. 99, Gayungan, Surabaya',
                    'address' => 'Jl. Ketintang Madya No. 99, Gayungan, Surabaya', 'emergency_contact_name' => 'Yudha (Ayah)', 'emergency_contact_phone' => '081234567900'
                ]
            );

            // =========================================================================
            // 7. SUITE 2: 6 AKUN KHUSUS TESTING MOBILE (BROWSER TOOL + HERMES)
            // =========================================================================
            $this->command->info("Menyiapkan Suite 2: Akun Testing Mobile (Password: Password123!)...");

            // 1. Super Admin Mobile
            User::updateOrCreate(
                ['email' => 'superadmin.mobile@surabaya.go.id'],
                ['name' => 'Dra. Hj. Sri Wahyuni, M.Si (Superadmin Mobile)', 'password' => $testPassword, 'role' => 'admin', 'status' => 'active', 'agency_profile_id' => null, 'email_verified_at' => now()]
            );

            // 2. Admin Dinas Dispusip Mobile
            User::updateOrCreate(
                ['email' => 'admin.dispusip.mobile@surabaya.go.id'],
                ['name' => 'Kurniawati Dewi, S.Sos., M.PSDM (Admin Dispusip Mobile)', 'password' => $testPassword, 'role' => 'admin', 'status' => 'active', 'agency_profile_id' => $dispusip->id, 'email_verified_at' => now()]
            );

            // 3. Mentor Dinas Dispusip Mobile
            User::updateOrCreate(
                ['email' => 'mentor.dispusip.mobile@surabaya.go.id'],
                ['name' => 'Aris Prasetyo, S.ST., M.MT (Mentor Dispusip Mobile)', 'password' => $testPassword, 'role' => 'mentor', 'status' => 'active', 'agency_profile_id' => $dispusip->id, 'email_verified_at' => now()]
            );

            // 4. Admin Universitas UNITOMO Mobile
            User::updateOrCreate(
                ['email' => 'admin.unitomo.mobile@unitomo.ac.id'],
                ['name' => 'Biro Kemahasiswaan UNITOMO (Admin Kampus Mobile)', 'password' => $testPassword, 'role' => 'universitas', 'status' => 'active', 'university_id' => $unitomo->id, 'university' => $unitomo->name, 'email_verified_at' => now()]
            );

            // 5. Dosen Pembimbing Lapangan UNITOMO Mobile
            User::updateOrCreate(
                ['email' => 'dosen.unitomo.mobile@unitomo.ac.id'],
                ['name' => 'Dr. Ir. Wahyudi Hartono, M.Kom (DPL UNITOMO Mobile)', 'password' => $testPassword, 'role' => 'dosen', 'status' => 'active', 'university_id' => $unitomo->id, 'university' => $unitomo->name, 'email_verified_at' => now()]
            );

            // 6. Mahasiswa UNITOMO Mobile (Siap Uji Coba Alur dari Awal sampai Akhir)
            $mhsMobile = User::updateOrCreate(
                ['email' => 'mhs.unitomo.mobile@unitomo.ac.id'],
                ['name' => 'Naufal Rizqullah Ramadhan', 'password' => $testPassword, 'role' => 'mahasiswa', 'status' => 'active', 'university_id' => $unitomo->id, 'university' => $unitomo->name, 'email_verified_at' => now()]
            );
            StudentProfile::updateOrCreate(
                ['user_id' => $mhsMobile->id],
                [
                    'nim' => '202241010099', 'universitas' => $unitomo->name, 'university_id' => $unitomo->id,
                    'faculty' => 'Fakultas Ilmu Komputer', 'fakultas' => 'Fakultas Ilmu Komputer',
                    'jurusan' => 'Teknik Informatika', 'major' => 'Teknik Informatika',
                    'semester' => '6', 'phone' => '081234567902', 'alamat' => 'Jl. Semolowaru No. 99, Sukolilo, Surabaya',
                    'address' => 'Jl. Semolowaru No. 99, Sukolilo, Surabaya', 'emergency_contact_name' => 'Ramadhan (Ayah)', 'emergency_contact_phone' => '081234567903'
                ]
            );

            $this->command->info("Ekosistem pengujian dan pengguna realistis berhasil disiapkan 100%!");
        });
    }
}
