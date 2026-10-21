<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\Evaluation;
use App\Models\FinalReport;
use App\Models\InternshipGroup;
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

class InternshipGroupDemoSeeder extends Seeder
{
    /**
     * Seeder Akun Kelompok Magang Lengkap dengan Berbagai Status:
     * 1. PENDING (Sedang diajukan ke Admin Dinas)
     * 2. WAITING_APPROVAL (Menunggu Double ACC dari Atasan/Supervisor)
     * 3. ACTIVE (Sedang aktif magang, bimbingan mentor & DPL, isi logbook)
     * 4. COMPLETED (Lulus magang, nilai evaluasi lengkap, laporan disetujui, sertifikat terbit)
     * 5. REJECTED (Ditolak dinas dengan catatan alasan resmi)
     */
    public function run(): void
    {
        $password = Hash::make('password');

        // 1. Instansi Target Dispusip Surabaya
        $dispusip = AgencyProfile::where('agency_name', 'like', '%Perpustakaan%')->first()
            ?? AgencyProfile::first();

        // 2. 4 Bidang Resmi Dispusip
        $unitPerpus = Unit::where('agency_profile_id', $dispusip->id)
            ->where('name', 'like', '%Perpustakaan%')->first()
            ?? Unit::firstOrCreate(
                ['agency_profile_id' => $dispusip->id, 'name' => 'Bidang Pembinaan dan Pengelolaan Perpustakaan'],
                ['quota' => 15]
            );

        $unitPreservasi = Unit::where('agency_profile_id', $dispusip->id)
            ->where('name', 'like', '%Penyelamatan%')->first()
            ?? Unit::firstOrCreate(
                ['agency_profile_id' => $dispusip->id, 'name' => 'Bidang Pengelolaan, Perlindungan, dan Penyelamatan Kearsipan'],
                ['quota' => 10]
            );

        $unitLayananArsip = Unit::where('agency_profile_id', $dispusip->id)
            ->where('name', 'like', '%Layanan Kearsipan%')->first()
            ?? Unit::firstOrCreate(
                ['agency_profile_id' => $dispusip->id, 'name' => 'Bidang Pembinaan dan Layanan Kearsipan'],
                ['quota' => 10]
            );

        $unitSekretariat = Unit::where('agency_profile_id', $dispusip->id)
            ->where('name', 'like', '%Sekretariat%')->first()
            ?? Unit::firstOrCreate(
                ['agency_profile_id' => $dispusip->id, 'name' => 'Bidang Sekretariat'],
                ['quota' => 10]
            );

        // 3. Perguruan Tinggi Mitra
        $unesa = University::firstOrCreate(
            ['code' => 'UNESA'],
            [
                'name' => 'Universitas Negeri Surabaya',
                'logo' => 'images/logos/unesa.png',
                'evaluation_scheme' => 'mentor_and_lecturer',
                'weight_mentor' => 50,
                'weight_lecturer' => 50,
                'require_dpl' => true,
            ]
        );

        $unair = University::firstOrCreate(
            ['code' => 'UNAIR'],
            [
                'name' => 'Universitas Airlangga',
                'logo' => 'images/logos/unair.png',
                'evaluation_scheme' => 'mentor_and_lecturer',
                'weight_mentor' => 60,
                'weight_lecturer' => 40,
                'require_dpl' => true,
            ]
        );

        $unitomo = University::firstOrCreate(
            ['code' => 'UNITOMO'],
            [
                'name' => 'Universitas Dr. Soetomo',
                'logo' => 'images/logos/unitomo.png',
                'evaluation_scheme' => 'mentor_and_lecturer',
                'weight_mentor' => 60,
                'weight_lecturer' => 40,
                'require_dpl' => true,
            ]
        );

        // 4. Mentor Dispusip Terkait
        $mentorPerpus = User::where('email', 'mentor.perpus@dispusip.surabaya.go.id')->first()
            ?? User::where('email', 'mentor.dispusip@surabaya.go.id')->first()
            ?? User::where('role', 'mentor')->first();

        $mentorArsip = User::where('email', 'mentor.arsip@dispusip.surabaya.go.id')->first()
            ?? User::where('role', 'mentor')->first();

        $mentorSekretariat = User::where('email', 'mentor.sekretariat@dispusip.surabaya.go.id')->first()
            ?? User::where('role', 'mentor')->first();

        // 5. Dosen Pembimbing Lapangan (DPL)
        $dplUnesa = User::firstOrCreate(
            ['email' => 'dosen.unesa@unesa.ac.id'],
            [
                'name' => 'Dr. Erina Nur Azizah, S.Kom., M.Cs',
                'password' => $password,
                'role' => 'dosen',
                'university_id' => $unesa->id,
                'university' => $unesa->name,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $dplUnitomo = User::firstOrCreate(
            ['email' => 'dosen.unitomo@unitomo.ac.id'],
            [
                'name' => 'Drs. Hendro Wibowo, M.Si.',
                'password' => $password,
                'role' => 'dosen',
                'university_id' => $unitomo->id,
                'university' => $unitomo->name,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $samplePdf = 'documents/applications/sample_proposal_kelompok.pdf';

        // =========================================================================
        // KELOMPOK 1: STATUS PENDING (Menunggu Verifikasi Staf Admin Dinas)
        // =========================================================================
        $userK1Ketua = User::updateOrCreate(
            ['email' => 'andi.ketua.pending@unesa.ac.id'],
            [
                'name' => 'Andi Pratama Wijaya',
                'password' => $password,
                'role' => 'mahasiswa',
                'university_id' => $unesa->id,
                'university' => $unesa->name,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        StudentProfile::updateOrCreate(
            ['user_id' => $userK1Ketua->id],
            [
                'nim' => '22051204011',
                'universitas' => $unesa->name,
                'jurusan' => 'S1 Sistem Informasi',
                'phone' => '081234500011',
            ]
        );

        $userK1Anggota = User::updateOrCreate(
            ['email' => 'bela.anggota.pending@unesa.ac.id'],
            [
                'name' => 'Bela Safitri',
                'password' => $password,
                'role' => 'mahasiswa',
                'university_id' => $unesa->id,
                'university' => $unesa->name,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        StudentProfile::updateOrCreate(
            ['user_id' => $userK1Anggota->id],
            [
                'nim' => '22051204012',
                'universitas' => $unesa->name,
                'jurusan' => 'S1 Sistem Informasi',
                'phone' => '081234500012',
            ]
        );

        $group1 = InternshipGroup::updateOrCreate(
            ['code' => 'GRP-PERPUS-01'],
            [
                'name' => 'Tim Riset Otomasi Perpustakaan',
                'leader_user_id' => $userK1Ketua->id,
                'unit_id' => $unitPerpus->id,
                'start_date' => Carbon::now()->addDays(14)->toDateString(),
                'end_date' => Carbon::now()->addDays(104)->toDateString(),
                'status' => 'pending',
                'proposal_letter_path' => $samplePdf,
            ]
        );

        $appK1Ketua = Application::updateOrCreate(
            ['user_id' => $userK1Ketua->id],
            [
                'unit_id' => $unitPerpus->id,
                'start_date' => $group1->start_date,
                'end_date' => $group1->end_date,
                'status' => ApplicationStatus::PENDING->value,
                'group_id' => $group1->id,
                'role_in_group' => 'leader',
                'letter_token' => 'token_grp_perpus_01_ketua',
            ]
        );

        $appK1Anggota = Application::updateOrCreate(
            ['user_id' => $userK1Anggota->id],
            [
                'unit_id' => $unitPerpus->id,
                'start_date' => $group1->start_date,
                'end_date' => $group1->end_date,
                'status' => ApplicationStatus::PENDING->value,
                'group_id' => $group1->id,
                'role_in_group' => 'member',
                'letter_token' => 'token_grp_perpus_01_anggota',
            ]
        );

        // =========================================================================
        // KELOMPOK 2: STATUS WAITING_APPROVAL (Menunggu Double ACC Atasan / Supervisor)
        // =========================================================================
        $userK2Ketua = User::updateOrCreate(
            ['email' => 'dimas.ketua.acc@unair.ac.id'],
            [
                'name' => 'Dimas Surya Atmaja',
                'password' => $password,
                'role' => 'mahasiswa',
                'university_id' => $unair->id,
                'university' => $unair->name,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        StudentProfile::updateOrCreate(
            ['user_id' => $userK2Ketua->id],
            [
                'nim' => '072011533011',
                'universitas' => $unair->name,
                'jurusan' => 'S1 Ilmu Informasi dan Perpustakaan',
                'phone' => '081234500021',
            ]
        );

        $userK2Anggota = User::updateOrCreate(
            ['email' => 'intan.anggota.acc@unair.ac.id'],
            [
                'name' => 'Intan Permatasari',
                'password' => $password,
                'role' => 'mahasiswa',
                'university_id' => $unair->id,
                'university' => $unair->name,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        StudentProfile::updateOrCreate(
            ['user_id' => $userK2Anggota->id],
            [
                'nim' => '072011533012',
                'universitas' => $unair->name,
                'jurusan' => 'S1 Ilmu Informasi dan Perpustakaan',
                'phone' => '081234500022',
            ]
        );

        $group2 = InternshipGroup::updateOrCreate(
            ['code' => 'GRP-ARSIP-02'],
            [
                'name' => 'Tim Digitalisasi Naskah Kuno',
                'leader_user_id' => $userK2Ketua->id,
                'unit_id' => $unitPreservasi->id,
                'start_date' => Carbon::now()->addDays(7)->toDateString(),
                'end_date' => Carbon::now()->addDays(97)->toDateString(),
                'status' => 'waiting_approval',
                'proposal_letter_path' => $samplePdf,
                'letter_number' => '500.12.1/021/436.7.14/2026',
                'letter_date' => Carbon::now()->toDateString(),
                'letter_token' => 'token_grp_arsip_02_group',
            ]
        );

        $appK2Ketua = Application::updateOrCreate(
            ['user_id' => $userK2Ketua->id],
            [
                'unit_id' => $unitPreservasi->id,
                'start_date' => $group2->start_date,
                'end_date' => $group2->end_date,
                'status' => ApplicationStatus::WAITING_APPROVAL->value,
                'group_id' => $group2->id,
                'role_in_group' => 'leader',
                'letter_number' => $group2->letter_number,
                'letter_date' => $group2->letter_date,
                'letter_token' => 'token_grp_arsip_02_ketua',
            ]
        );
        Placement::updateOrCreate(
            ['application_id' => $appK2Ketua->id],
            [
                'mentor_id' => $mentorArsip?->id,
                'pembimbing_id' => $mentorArsip?->id,
            ]
        );

        $appK2Anggota = Application::updateOrCreate(
            ['user_id' => $userK2Anggota->id],
            [
                'unit_id' => $unitPreservasi->id,
                'start_date' => $group2->start_date,
                'end_date' => $group2->end_date,
                'status' => ApplicationStatus::WAITING_APPROVAL->value,
                'group_id' => $group2->id,
                'role_in_group' => 'member',
                'letter_number' => $group2->letter_number,
                'letter_date' => $group2->letter_date,
                'letter_token' => 'token_grp_arsip_02_anggota',
            ]
        );
        Placement::updateOrCreate(
            ['application_id' => $appK2Anggota->id],
            [
                'mentor_id' => $mentorArsip?->id,
                'pembimbing_id' => $mentorArsip?->id,
            ]
        );

        // =========================================================================
        // KELOMPOK 3: STATUS ACTIVE (Sedang Aktif Menjalani Magang)
        // =========================================================================
        $userK3Ketua = User::updateOrCreate(
            ['email' => 'fauzan.ketua.aktif@unesa.ac.id'],
            [
                'name' => 'Fauzan Al Ghifari',
                'password' => $password,
                'role' => 'mahasiswa',
                'university_id' => $unesa->id,
                'university' => $unesa->name,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        StudentProfile::updateOrCreate(
            ['user_id' => $userK3Ketua->id],
            [
                'nim' => '22051204021',
                'universitas' => $unesa->name,
                'jurusan' => 'S1 Teknik Informatika',
                'phone' => '081234500031',
            ]
        );

        $userK3Anggota = User::updateOrCreate(
            ['email' => 'gina.anggota.aktif@unesa.ac.id'],
            [
                'name' => 'Gina Maharani',
                'password' => $password,
                'role' => 'mahasiswa',
                'university_id' => $unesa->id,
                'university' => $unesa->name,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        StudentProfile::updateOrCreate(
            ['user_id' => $userK3Anggota->id],
            [
                'nim' => '22051204022',
                'universitas' => $unesa->name,
                'jurusan' => 'S1 Sistem Informasi',
                'phone' => '081234500032',
            ]
        );

        $group3 = InternshipGroup::updateOrCreate(
            ['code' => 'GRP-LAYANAN-03'],
            [
                'name' => 'Tim Layanan Informasi Kearsipan Terpadu',
                'leader_user_id' => $userK3Ketua->id,
                'unit_id' => $unitLayananArsip->id,
                'start_date' => Carbon::now()->subDays(30)->toDateString(),
                'end_date' => Carbon::now()->addDays(60)->toDateString(),
                'status' => 'accepted',
                'proposal_letter_path' => $samplePdf,
                'letter_number' => '500.12.1/088/436.7.14/2026',
                'letter_date' => Carbon::now()->subDays(35)->toDateString(),
                'letter_token' => 'token_grp_layanan_03_active_2026',
            ]
        );

        $appK3Ketua = Application::updateOrCreate(
            ['user_id' => $userK3Ketua->id],
            [
                'unit_id' => $unitLayananArsip->id,
                'start_date' => $group3->start_date,
                'end_date' => $group3->end_date,
                'status' => ApplicationStatus::ACTIVE->value,
                'group_id' => $group3->id,
                'role_in_group' => 'leader',
                'letter_number' => $group3->letter_number,
                'letter_date' => $group3->letter_date,
                'letter_token' => 'token_grp_layanan_03_ketua',
            ]
        );
        $plcK3Ketua = Placement::updateOrCreate(
            ['application_id' => $appK3Ketua->id],
            [
                'mentor_id' => $mentorArsip?->id,
                'pembimbing_id' => $mentorArsip?->id,
                'academic_advisor_id' => $dplUnesa->id,
                'certificate_number' => 'SERT/088-A/PEMKOT-SBY/2026',
                'certificate_hash' => Str::random(32),
            ]
        );

        $appK3Anggota = Application::updateOrCreate(
            ['user_id' => $userK3Anggota->id],
            [
                'unit_id' => $unitLayananArsip->id,
                'start_date' => $group3->start_date,
                'end_date' => $group3->end_date,
                'status' => ApplicationStatus::ACTIVE->value,
                'group_id' => $group3->id,
                'role_in_group' => 'member',
                'letter_number' => $group3->letter_number,
                'letter_date' => $group3->letter_date,
                'letter_token' => 'token_grp_layanan_03_anggota',
            ]
        );
        $plcK3Anggota = Placement::updateOrCreate(
            ['application_id' => $appK3Anggota->id],
            [
                'mentor_id' => $mentorArsip?->id,
                'pembimbing_id' => $mentorArsip?->id,
                'academic_advisor_id' => $dplUnesa->id,
                'certificate_number' => 'SERT/088-B/PEMKOT-SBY/2026',
                'certificate_hash' => Str::random(32),
            ]
        );

        // Tambah contoh logbook untuk kelompok aktif
        Logbook::updateOrCreate(
            ['placement_id' => $plcK3Ketua->id, 'date' => Carbon::now()->subDays(2)->toDateString()],
            [
                'activity' => 'Melakukan pemetaan alur peminjaman arsip dinamis pada sistem layanan dinas.',
                'status' => 'approved',
                'feedback' => 'Pekerjaan rapi dan sistematis. Lanjutkan ke penyusunan modul katalog.',
            ]
        );
        Logbook::updateOrCreate(
            ['placement_id' => $plcK3Anggota->id, 'date' => Carbon::now()->subDays(2)->toDateString()],
            [
                'activity' => 'Input metadata arsip dinamis perangkat daerah ke database kearsipan terpadu.',
                'status' => 'approved',
                'feedback' => 'Akurasi input data baik.',
            ]
        );

        // =========================================================================
        // KELOMPOK 4: STATUS COMPLETED (Lulus Magang & Terbit E-Sertifikat)
        // =========================================================================
        $userK4Ketua = User::updateOrCreate(
            ['email' => 'haikal.ketua.lulus@unitomo.ac.id'],
            [
                'name' => 'Haikal Fadhilah',
                'password' => $password,
                'role' => 'mahasiswa',
                'university_id' => $unitomo->id,
                'university' => $unitomo->name,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        StudentProfile::updateOrCreate(
            ['user_id' => $userK4Ketua->id],
            [
                'nim' => '2021110051',
                'universitas' => $unitomo->name,
                'jurusan' => 'S1 Administrasi Publik',
                'phone' => '081234500041',
            ]
        );

        $userK4Anggota = User::updateOrCreate(
            ['email' => 'isabella.anggota.lulus@unitomo.ac.id'],
            [
                'name' => 'Isabella Putri',
                'password' => $password,
                'role' => 'mahasiswa',
                'university_id' => $unitomo->id,
                'university' => $unitomo->name,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        StudentProfile::updateOrCreate(
            ['user_id' => $userK4Anggota->id],
            [
                'nim' => '2021110052',
                'universitas' => $unitomo->name,
                'jurusan' => 'S1 Manajemen',
                'phone' => '081234500042',
            ]
        );

        $group4 = InternshipGroup::updateOrCreate(
            ['code' => 'GRP-SEKRET-04'],
            [
                'name' => 'Tim Transformasi Administrasi Sekretariat',
                'leader_user_id' => $userK4Ketua->id,
                'unit_id' => $unitSekretariat->id,
                'start_date' => Carbon::now()->subDays(100)->toDateString(),
                'end_date' => Carbon::now()->subDays(10)->toDateString(),
                'status' => 'accepted',
                'proposal_letter_path' => $samplePdf,
                'letter_number' => '500.12.1/042/436.7.14/2026',
                'letter_date' => Carbon::now()->subDays(105)->toDateString(),
                'letter_token' => 'token_grp_sekret_04_completed_2026',
            ]
        );

        $appK4Ketua = Application::updateOrCreate(
            ['user_id' => $userK4Ketua->id],
            [
                'unit_id' => $unitSekretariat->id,
                'start_date' => $group4->start_date,
                'end_date' => $group4->end_date,
                'status' => ApplicationStatus::COMPLETED->value,
                'group_id' => $group4->id,
                'role_in_group' => 'leader',
                'letter_number' => $group4->letter_number,
                'letter_date' => $group4->letter_date,
                'letter_token' => 'token_grp_sekret_04_ketua',
            ]
        );
        $plcK4Ketua = Placement::updateOrCreate(
            ['application_id' => $appK4Ketua->id],
            [
                'mentor_id' => $mentorSekretariat?->id,
                'pembimbing_id' => $mentorSekretariat?->id,
                'academic_advisor_id' => $dplUnitomo->id,
                'certificate_number' => 'SERT/042-A/PEMKOT-SBY/2026',
                'certificate_hash' => Str::random(32),
            ]
        );
        Evaluation::updateOrCreate(
            ['placement_id' => $plcK4Ketua->id],
            [
                'nilai_disiplin' => 95,
                'nilai_kinerja' => 94,
                'nilai_laporan' => 93,
                'nilai_akademik' => 90,
                'catatan' => 'Mahasiswa menunjukkan kepemimpinan tim yang luar biasa dan dedikasi tinggi selama magang.',
                'catatan_dosen' => 'Pencapaian kompetensi sangat baik dan relevan dengan kurikulum program studi.',
            ]
        );
        FinalReport::updateOrCreate(
            ['placement_id' => $plcK4Ketua->id],
            [
                'title' => 'Laporan Akhir: Optimalisasi Tata Kelola Administrasi Digital di Bidang Sekretariat Dispusip Surabaya',
                'file_path' => $samplePdf,
                'status' => 'approved',
                'feedback' => 'Laporan lengkap, format A4 sesuai panduan akademik dinas.',
            ]
        );

        $appK4Anggota = Application::updateOrCreate(
            ['user_id' => $userK4Anggota->id],
            [
                'unit_id' => $unitSekretariat->id,
                'start_date' => $group4->start_date,
                'end_date' => $group4->end_date,
                'status' => ApplicationStatus::COMPLETED->value,
                'group_id' => $group4->id,
                'role_in_group' => 'member',
                'letter_number' => $group4->letter_number,
                'letter_date' => $group4->letter_date,
                'letter_token' => 'token_grp_sekret_04_anggota',
            ]
        );
        $plcK4Anggota = Placement::updateOrCreate(
            ['application_id' => $appK4Anggota->id],
            [
                'mentor_id' => $mentorSekretariat?->id,
                'pembimbing_id' => $mentorSekretariat?->id,
                'academic_advisor_id' => $dplUnitomo->id,
                'certificate_number' => 'SERT/042-B/PEMKOT-SBY/2026',
                'certificate_hash' => Str::random(32),
            ]
        );
        Evaluation::updateOrCreate(
            ['placement_id' => $plcK4Anggota->id],
            [
                'nilai_disiplin' => 92,
                'nilai_kinerja' => 92,
                'nilai_laporan' => 92,
                'nilai_akademik' => 88,
                'catatan' => 'Kerja tim solid, teliti dalam pengarsipan berkas internal kedinasan.',
                'catatan_dosen' => 'Penyusunan laporan sistematis dan memiliki dasar teori yang kuat.',
            ]
        );
        FinalReport::updateOrCreate(
            ['placement_id' => $plcK4Anggota->id],
            [
                'title' => 'Laporan Akhir: Manajemen Berkas Kepegawaian dan Pengarsipan Digital Dispusip Surabaya',
                'file_path' => $samplePdf,
                'status' => 'approved',
                'feedback' => 'Laporan telah divalidasi dan disetujui penuh.',
            ]
        );

        // =========================================================================
        // KELOMPOK 5: STATUS REJECTED (Pengajuan Ditolak Dinas)
        // =========================================================================
        $userK5Ketua = User::updateOrCreate(
            ['email' => 'joko.ketua.tolak@unesa.ac.id'],
            [
                'name' => 'Joko Susilo',
                'password' => $password,
                'role' => 'mahasiswa',
                'university_id' => $unesa->id,
                'university' => $unesa->name,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        StudentProfile::updateOrCreate(
            ['user_id' => $userK5Ketua->id],
            [
                'nim' => '22051204091',
                'universitas' => $unesa->name,
                'jurusan' => 'S1 Pendidikan Bahasa',
                'phone' => '081234500051',
            ]
        );

        $userK5Anggota = User::updateOrCreate(
            ['email' => 'karina.anggota.tolak@unesa.ac.id'],
            [
                'name' => 'Karina Damayanti',
                'password' => $password,
                'role' => 'mahasiswa',
                'university_id' => $unesa->id,
                'university' => $unesa->name,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        StudentProfile::updateOrCreate(
            ['user_id' => $userK5Anggota->id],
            [
                'nim' => '22051204092',
                'universitas' => $unesa->name,
                'jurusan' => 'S1 Ilmu Komunikasi',
                'phone' => '081234500052',
            ]
        );

        $alasanPenolakan = 'Kuota unit kerja bidang perpustakaan telah terisi penuh untuk periode yang diajukan. Silakan sesuaikan tanggal magang pada periode berikutnya atau ajukan ke bidang kearsipan.';

        $group5 = InternshipGroup::updateOrCreate(
            ['code' => 'GRP-TOLAK-05'],
            [
                'name' => 'Tim Pengembangan Literasi Komunitas',
                'leader_user_id' => $userK5Ketua->id,
                'unit_id' => $unitPerpus->id,
                'start_date' => Carbon::now()->addDays(30)->toDateString(),
                'end_date' => Carbon::now()->addDays(120)->toDateString(),
                'status' => 'rejected',
                'rejection_reason' => $alasanPenolakan,
                'proposal_letter_path' => $samplePdf,
            ]
        );

        $appK5Ketua = Application::updateOrCreate(
            ['user_id' => $userK5Ketua->id],
            [
                'unit_id' => $unitPerpus->id,
                'start_date' => $group5->start_date,
                'end_date' => $group5->end_date,
                'status' => ApplicationStatus::REJECTED->value,
                'group_id' => $group5->id,
                'role_in_group' => 'leader',
                'rejection_note' => $alasanPenolakan,
                'rejection_reason' => $alasanPenolakan,
                'letter_token' => 'token_grp_tolak_05_ketua',
            ]
        );

        $appK5Anggota = Application::updateOrCreate(
            ['user_id' => $userK5Anggota->id],
            [
                'unit_id' => $unitPerpus->id,
                'start_date' => $group5->start_date,
                'end_date' => $group5->end_date,
                'status' => ApplicationStatus::REJECTED->value,
                'group_id' => $group5->id,
                'role_in_group' => 'member',
                'rejection_note' => $alasanPenolakan,
                'rejection_reason' => $alasanPenolakan,
                'letter_token' => 'token_grp_tolak_05_anggota',
            ]
        );
    }
}
