<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\ApplicationStatus;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\Certificate;
use App\Models\Evaluation;
use App\Models\FinalReport;
use App\Models\Logbook;
use App\Models\Placement;
use App\Models\StudentProfile;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use App\Services\MasterUniversityService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Melengkapi akun demo untuk skenario yang belum dicakup seeder lain, supaya setiap keadaan
 * di sistem bisa diperagakan tanpa menyiapkan data dulu. Daftar akunnya ada di PANDUAN_DEMO.md.
 *
 * Aman dijalankan berulang (updateOrCreate) di SQLite maupun PostgreSQL, dan bisa dijalankan sendiri:
 *     php artisan db:seed --class=DemoScenarioSeeder
 * Menjalankannya ulang mengembalikan akun-akun ini ke keadaan awal skenario.
 */
class DemoScenarioSeeder extends Seeder
{
    private const SAMPLE_PDF = 'documents/applications/5AdFAcelgeprCcoR82Brj5GF2QzWJvqIMPwbrfc2.pdf';

    private string $password;

    public function run(): void
    {
        if (! app()->environment(DatabaseSeeder::DEMO_ENVIRONMENTS)) {
            $this->command?->error('DemoScenarioSeeder dilewati: seeder ini membuat akun demo dengan kata sandi bawaan.');

            return;
        }

        $this->password = Hash::make(User::DEFAULT_PASSWORD);

        $dispusip = AgencyProfile::where('agency_name', 'Dinas Perpustakaan dan Kearsipan')->first() ?? AgencyProfile::first();
        $mentor = User::where('email', 'mentor.dispusip@surabaya.go.id')->first();
        $unesa = University::where('code', 'UNESA')->first();
        $dosenUnesa = User::where('email', 'dosen.unesa@unesa.ac.id')->first();

        if (! $dispusip || ! $mentor || ! $unesa) {
            $this->command?->error('DemoScenarioSeeder membutuhkan data dasar. Jalankan `php artisan db:seed` lebih dulu.');

            return;
        }

        $unit = Unit::find($mentor->unit_id) ?? Unit::where('agency_profile_id', $dispusip->id)->orderBy('id')->first();

        // Kampus dengan penilaian ganda (40% mentor + 60% dosen) agar alur nilai dosen bisa diperagakan.
        // Kampus lain di data demo memakai 100% nilai mentor.
        $pens = University::updateOrCreate(
            ['code' => 'PENS'],
            [
                'name' => 'Politeknik Elektronika Negeri Surabaya',
                'evaluation_scheme' => 'dual_evaluation',
                'weight_mentor' => 40,
                'weight_lecturer' => 60,
                'require_dpl' => true,
            ]
        );
        $dosenPens = User::updateOrCreate(
            ['email' => 'dosen.pens@pens.ac.id'],
            ['name' => 'Dr. Rahmat Hidayat, S.T., M.T.', 'password' => $this->password, 'role' => 'dosen', 'university_id' => $pens->id, 'university' => $pens->name, 'status' => 'active', 'email_verified_at' => now()]
        );

        // ---------------------------------------------------------------------------------------
        // 1. Baru mendaftar: belum mengisi profil, belum mengajukan.
        // ---------------------------------------------------------------------------------------
        $baru = User::updateOrCreate(
            ['email' => 'baru.daftar@unesa.ac.id'],
            ['name' => 'Nadia Safitri', 'password' => $this->password, 'role' => 'mahasiswa', 'university_id' => $unesa->id, 'university' => $unesa->name, 'status' => 'active', 'email_verified_at' => now()]
        );
        StudentProfile::where('user_id', $baru->id)->delete();
        Application::where('user_id', $baru->id)->delete();

        // ---------------------------------------------------------------------------------------
        // 2. Profil dan berkas lengkap, siap mengajukan.
        // ---------------------------------------------------------------------------------------
        $siap = $this->student('siap.ajukan@unesa.ac.id', 'Rizky Maulana', '22081010101', $unesa, 'S1 Sistem Informasi', withDocuments: true);
        Application::where('user_id', $siap->id)->delete();

        // ---------------------------------------------------------------------------------------
        // 3. Mengundurkan diri di tengah magang.
        // ---------------------------------------------------------------------------------------
        $mundur = $this->student('rina.mundur@unesa.ac.id', 'Rina Kusuma', '22081010102', $unesa, 'S1 Ilmu Perpustakaan');
        $appMundur = $this->application($mundur, $unit, ApplicationStatus::RESIGNED, Carbon::now()->subDays(40), Carbon::now()->addDays(50), [
            'rejection_reason' => 'Mengundurkan diri karena jadwal kuliah pengganti bertabrakan dengan jam magang.',
        ]);
        $plMundur = $this->placement($appMundur, $mentor, $dosenUnesa);
        $this->logbook($plMundur, 30, 'Orientasi unit kerja dan pengenalan sistem katalog perpustakaan.', 'approved', 'Baik.');

        // ---------------------------------------------------------------------------------------
        // 4. Aktif dengan logbook berbagai status (disetujui, menunggu, ditolak dengan catatan).
        // ---------------------------------------------------------------------------------------
        $lb = $this->student('logbook.revisi@unesa.ac.id', 'Yoga Pratama', '22081010103', $unesa, 'S1 Teknik Informatika');
        $appLb = $this->application($lb, $unit, ApplicationStatus::ACTIVE, Carbon::now()->subDays(20), Carbon::now()->addDays(70));
        $plLb = $this->placement($appLb, $mentor, $dosenUnesa);
        Logbook::where('placement_id', $plLb->id)->delete();
        $this->logbook($plLb, 8, 'Menyusun daftar koleksi buku anak yang perlu dilabel ulang.', 'approved', 'Rapi dan lengkap.');
        $this->logbook($plLb, 6, 'Membantu layanan sirkulasi di meja peminjaman.', 'rejected', 'Mohon tuliskan jumlah pemustaka yang dilayani dan kendala yang ditemui.');
        $this->logbook($plLb, 4, 'Kegiatan harian.', 'rejected', 'Uraian terlalu singkat. Jelaskan apa yang dikerjakan.');
        $this->logbook($plLb, 2, 'Memasukkan 40 judul buku baru ke katalog daring.', 'pending');
        $this->logbook($plLb, 1, 'Menata rak koleksi referensi lantai 2.', 'pending');

        // ---------------------------------------------------------------------------------------
        // 5. Laporan akhir sudah diunggah, menunggu tinjauan.
        // ---------------------------------------------------------------------------------------
        $lapTunggu = $this->student('laporan.menunggu@unesa.ac.id', 'Salsa Maharani', '22081010104', $unesa, 'S1 Ilmu Perpustakaan');
        $appLapTunggu = $this->application($lapTunggu, $unit, ApplicationStatus::ACTIVE, Carbon::now()->subDays(80), Carbon::now()->addDays(7));
        $plLapTunggu = $this->placement($appLapTunggu, $mentor, $dosenUnesa);
        $this->logbook($plLapTunggu, 12, 'Menyusun panduan layanan pojok baca digital.', 'approved', 'Bagus.');
        $this->finalReport($plLapTunggu, 'Laporan Akhir: Panduan Layanan Pojok Baca Digital', 'pending');
        Evaluation::where('placement_id', $plLapTunggu->id)->delete();

        // ---------------------------------------------------------------------------------------
        // 6. Laporan akhir dikembalikan untuk direvisi.
        // ---------------------------------------------------------------------------------------
        $lapRevisi = $this->student('laporan.revisi@unesa.ac.id', 'Tegar Nugroho', '22081010105', $unesa, 'S1 Sistem Informasi');
        $appLapRevisi = $this->application($lapRevisi, $unit, ApplicationStatus::ACTIVE, Carbon::now()->subDays(80), Carbon::now()->addDays(7));
        $plLapRevisi = $this->placement($appLapRevisi, $mentor, $dosenUnesa);
        $this->logbook($plLapRevisi, 12, 'Merekap statistik kunjungan perpustakaan triwulan.', 'approved', 'Baik.');
        $this->finalReport($plLapRevisi, 'Laporan Akhir: Statistik Kunjungan Perpustakaan', 'revision', 'Bab 4 belum memuat analisis data. Lengkapi lalu unggah ulang.');
        Evaluation::where('placement_id', $plLapRevisi->id)->delete();

        // ---------------------------------------------------------------------------------------
        // 7. Masa magang sudah lewat, laporan disetujui, tinggal dinilai mentor.
        //    Begitu mentor menyimpan nilai, status otomatis menjadi selesai.
        // ---------------------------------------------------------------------------------------
        $siapNilai = $this->student('siap.dinilai@unesa.ac.id', 'Vina Anggraini', '22081010106', $unesa, 'S1 Ilmu Perpustakaan');
        $appSiapNilai = $this->application($siapNilai, $unit, ApplicationStatus::ACTIVE, Carbon::now()->subDays(92), Carbon::now()->subDays(2));
        $plSiapNilai = $this->placement($appSiapNilai, $mentor, $dosenUnesa);
        $this->logbook($plSiapNilai, 15, 'Mendigitalkan 120 lembar arsip foto kegiatan dinas.', 'approved', 'Sangat baik.');
        $this->finalReport($plSiapNilai, 'Laporan Akhir: Digitalisasi Arsip Foto Kegiatan Dinas', 'approved', 'Laporan lengkap.');
        Evaluation::where('placement_id', $plSiapNilai->id)->delete();
        Certificate::where('placement_id', $plSiapNilai->id)->delete();

        // ---------------------------------------------------------------------------------------
        // 8. Penilaian ganda: mentor sudah menilai, menunggu nilai dosen.
        // ---------------------------------------------------------------------------------------
        $ganda = $this->student('ganda.aktif@pens.ac.id', 'Farhan Akbar', '3122600011', $pens, 'D4 Teknik Informatika');
        $appGanda = $this->application($ganda, $unit, ApplicationStatus::ACTIVE, Carbon::now()->subDays(92), Carbon::now()->subDays(2));
        $plGanda = $this->placement($appGanda, $mentor, $dosenPens);
        $this->logbook($plGanda, 15, 'Membangun purwarupa pencarian koleksi berbasis web.', 'approved', 'Sangat baik.');
        $this->finalReport($plGanda, 'Laporan Akhir: Purwarupa Pencarian Koleksi Berbasis Web', 'approved', 'Laporan lengkap.');
        Certificate::where('placement_id', $plGanda->id)->delete();
        $aspekMentor = [
            ['name' => $pens->getDinasComponent(1), 'score' => 88],
            ['name' => $pens->getDinasComponent(2), 'score' => 90],
            ['name' => $pens->getDinasComponent(3), 'score' => 86],
        ];
        Evaluation::updateOrCreate(
            ['placement_id' => $plGanda->id],
            [
                'nilai_disiplin' => 88, 'nilai_kinerja' => 90, 'nilai_laporan' => 86,
                'mentor_aspects' => $aspekMentor, 'custom_components' => ['mentor' => $aspekMentor],
                'catatan' => 'Farhan cepat belajar dan teliti.',
                'score_mastery' => 0, 'score_report' => 0, 'score_attitude' => 0,
                'nilai_dosen' => 0, 'nilai_akademik' => 0, 'dosen_aspects' => null,
                'feedback_dosen' => null, 'final_score' => 0, 'grade' => null,
            ]
        );

        // ---------------------------------------------------------------------------------------
        // 9. Penilaian ganda sudah lengkap dan sertifikat sudah disahkan.
        // ---------------------------------------------------------------------------------------
        $gandaLulus = $this->student('ganda.lulus@pens.ac.id', 'Laras Wulandari', '3122600012', $pens, 'D4 Teknik Informatika');
        $appGandaLulus = $this->application($gandaLulus, $unit, ApplicationStatus::COMPLETED, Carbon::now()->subDays(120), Carbon::now()->subDays(20));
        $plGandaLulus = $this->placement($appGandaLulus, $mentor, $dosenPens, 'SERT/201/DISPUSIP-SBY/'.Carbon::now()->subDays(20)->format('Y'));
        $this->logbook($plGandaLulus, 40, 'Menyusun modul pelatihan literasi digital untuk pustakawan.', 'approved', 'Sangat baik.');
        $this->finalReport($plGandaLulus, 'Laporan Akhir: Modul Pelatihan Literasi Digital Pustakawan', 'approved', 'Laporan lengkap.');
        $aspekDosen = [
            ['name' => $pens->getDplComponent(1), 'score' => 85],
            ['name' => $pens->getDplComponent(2), 'score' => 88],
            ['name' => $pens->getDplComponent(3), 'score' => 90],
        ];
        Evaluation::updateOrCreate(
            ['placement_id' => $plGandaLulus->id],
            [
                'nilai_disiplin' => 90, 'nilai_kinerja' => 92, 'nilai_laporan' => 88,
                'mentor_aspects' => [
                    ['name' => $pens->getDinasComponent(1), 'score' => 90],
                    ['name' => $pens->getDinasComponent(2), 'score' => 92],
                    ['name' => $pens->getDinasComponent(3), 'score' => 88],
                ],
                'catatan' => 'Laras mandiri dan komunikatif.',
                'score_mastery' => 85, 'score_report' => 88, 'score_attitude' => 90,
                'nilai_dosen' => 87.67, 'dosen_aspects' => $aspekDosen,
                'feedback_dosen' => 'Analisis laporan tajam dan relevan dengan capaian pembelajaran.',
                // 40% x 90,00 + 60% x 87,67
                'final_score' => 88.6, 'grade' => 'A',
            ]
        );
        $supervisor = User::where('role', 'supervisor')->orderBy('id')->first();
        Certificate::updateOrCreate(
            ['placement_id' => $plGandaLulus->id],
            [
                'certificate_number' => $plGandaLulus->certificate_number,
                'file_path' => 'certificates/'.str_replace('/', '_', $plGandaLulus->certificate_number).'.pdf',
                'issued_at' => Carbon::now()->subDays(15)->toDateString(),
                'approval_status' => 'approved',
                'approved_by' => $supervisor?->id,
                'approved_at' => Carbon::now()->subDays(14),
                'notes' => null,
            ]
        );

        // ---------------------------------------------------------------------------------------
        // 10. Akun dinonaktifkan admin: tidak bisa masuk.
        // ---------------------------------------------------------------------------------------
        $nonaktif = $this->student('akun.nonaktif@unesa.ac.id', 'Bayu Setiawan', '22081010107', $unesa, 'S1 Teknik Informatika');
        $nonaktif->forceFill(['status' => 'inactive'])->save();

        // ---------------------------------------------------------------------------------------
        // 11. Akun demo yang sudah lulus tetapi belum punya logbook sama sekali (mis. kelompok
        //     haikal.ketua.lulus): tanpa logbook, sertifikatnya tidak bisa diterbitkan saat demo.
        // ---------------------------------------------------------------------------------------
        $this->backfillLogbooksForCompletedPlacements();

        // ---------------------------------------------------------------------------------------
        // 12. Pengajuan demo tanpa dokumen: halaman "periksa berkas" Admin Dinas akan kosong saat demo.
        //     Juga lengkapi berkas profil akun yang diperagakan mengajukan ulang.
        // ---------------------------------------------------------------------------------------
        $this->backfillApplicationDocuments();

        // ---------------------------------------------------------------------------------------
        // 13. Peserta dari sekolah (SMK): admin sekolah, guru pembimbing (disetujui & menunggu
        //     persetujuan), siswa aktif magang, dan siswa lulus dengan sertifikat sah.
        // ---------------------------------------------------------------------------------------
        $this->schoolScenarios($unit, $mentor, $supervisor);

        $this->command?->info('DemoScenarioSeeder: skenario demo tambahan (termasuk peserta sekolah) siap dipakai (kata sandi bawaan).');
    }

    private function schoolScenarios(Unit $unit, User $mentor, ?User $supervisor): void
    {
        $service = app(MasterUniversityService::class);
        $item = $service->search('SMK NEGERI 7 SURABAYA', 5)->firstWhere('institution_type', University::TYPE_SCHOOL);
        $school = $item
            ? $service->resolveToUniversity($item)
            : University::updateOrCreate(['name' => 'SMK NEGERI 7 SURABAYA'], ['institution_type' => University::TYPE_SCHOOL, 'is_verified' => true, 'status' => 'active']);

        $account = fn (string $email, string $name, string $role, string $status = 'active') => User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => $this->password, 'role' => $role, 'university_id' => $school->id, 'university' => $school->name, 'status' => $status, 'email_verified_at' => now()]
        );

        $account('admin.smkn7@demo.sch.id', 'Admin SMK Negeri 7 Surabaya', 'universitas');
        $guru = $account('guru.smkn7@demo.sch.id', 'Sri Wahyuni, S.Pd.', 'dosen');
        // Guru yang mendaftar sendiri: menunggu persetujuan Admin Sekolah (tombol Setujui di menu Pembimbing)
        $account('guru.menunggu@demo.sch.id', 'Agus Santoso, S.Pd.', 'dosen', AccountStatus::PENDING->value);

        $siswaAktif = $this->schoolStudent('siswa.smk.aktif@demo.sch.id', 'Putri Ayu Lestari', '0071234561', $school, 11);
        $appAktif = $this->application($siswaAktif, $unit, ApplicationStatus::ACTIVE, Carbon::now()->subDays(15), Carbon::now()->addDays(75));
        $plAktif = $this->placement($appAktif, $mentor, $guru);
        Logbook::where('placement_id', $plAktif->id)->delete();
        $this->logbook($plAktif, 5, 'Membantu penataan arsip surat masuk dan mencatat nomor agenda.', 'approved', 'Rapi.');
        $this->logbook($plAktif, 3, 'Memindai 60 lembar arsip inaktif dan memberi nama berkas sesuai pedoman.', 'pending');
        $this->logbook($plAktif, 1, 'Membantu layanan peminjaman buku di ruang baca anak.', 'pending');

        $siswaLulus = $this->schoolStudent('siswa.smk.lulus@demo.sch.id', 'Dimas Ardiansyah', '0071234562', $school, 12);
        $appLulus = $this->application($siswaLulus, $unit, ApplicationStatus::COMPLETED, Carbon::now()->subDays(110), Carbon::now()->subDays(15));
        $plLulus = $this->placement($appLulus, $mentor, $guru, 'SERT/301/DISPUSIP-SBY/'.Carbon::now()->subDays(15)->format('Y'));
        $this->logbook($plLulus, 30, 'Menyusun daftar arsip vital bidang kearsipan.', 'approved', 'Sangat baik.');
        $this->finalReport($plLulus, 'Laporan PKL: Penataan Arsip Vital Bidang Kearsipan', 'approved', 'Laporan lengkap.');
        Evaluation::updateOrCreate(
            ['placement_id' => $plLulus->id],
            [
                'nilai_disiplin' => 90, 'nilai_kinerja' => 88, 'nilai_laporan' => 86,
                'mentor_aspects' => [
                    ['name' => $school->getDinasComponent(1), 'score' => 90],
                    ['name' => $school->getDinasComponent(2), 'score' => 88],
                    ['name' => $school->getDinasComponent(3), 'score' => 86],
                ],
                'catatan' => 'Dimas disiplin dan teliti menata arsip.',
                'score_mastery' => 0, 'score_report' => 0, 'score_attitude' => 0,
                'nilai_dosen' => 0, 'dosen_aspects' => null, 'feedback_dosen' => null,
                'final_score' => 88, 'grade' => 'A',
            ]
        );
        Certificate::updateOrCreate(
            ['placement_id' => $plLulus->id],
            [
                'certificate_number' => $plLulus->certificate_number,
                'file_path' => 'certificates/'.str_replace('/', '_', $plLulus->certificate_number).'.pdf',
                'issued_at' => Carbon::now()->subDays(10)->toDateString(),
                'approval_status' => 'approved',
                'approved_by' => $supervisor?->id,
                'approved_at' => Carbon::now()->subDays(9),
                'notes' => null,
            ]
        );
    }

    /** Siswa SMK: jenjang sekolah, NIS, kelas (10-13), tanpa fakultas, berkas lengkap. */
    private function schoolStudent(string $email, string $name, string $nis, University $school, int $kelas): User
    {
        $user = $this->student($email, $name, $nis, $school, 'Otomatisasi Tata Kelola Perkantoran', withDocuments: true);
        StudentProfile::where('user_id', $user->id)->update([
            'education_level' => University::TYPE_SCHOOL,
            'semester' => (string) $kelas,
            'faculty' => null,
            'fakultas' => null,
            'alamat' => 'Jl. Pawiyatan No. 2, Surabaya',
            'address' => 'Jl. Pawiyatan No. 2, Surabaya',
        ]);

        return $user;
    }

    private function student(string $email, string $name, string $nim, University $univ, string $jurusan, bool $withDocuments = false): User
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => $this->password, 'role' => 'mahasiswa', 'university_id' => $univ->id, 'university' => $univ->name, 'status' => 'active', 'email_verified_at' => now()]
        );

        $phone = '0812'.str_pad((string) (abs(crc32($email)) % 100000000), 8, '0', STR_PAD_LEFT);
        StudentProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'nim' => $nim, 'universitas' => $univ->name, 'university_id' => $univ->id,
                'jurusan' => $jurusan, 'major' => $jurusan, 'semester' => '7', 'phone' => $phone,
                'alamat' => 'Jl. Ketintang No. 10, Surabaya', 'address' => 'Jl. Ketintang No. 10, Surabaya',
                'emergency_contact_name' => 'Orang Tua '.$name, 'emergency_contact_phone' => '081200000000',
                'cv_path' => $withDocuments ? self::SAMPLE_PDF : null,
                'transcript_path' => $withDocuments ? self::SAMPLE_PDF : null,
                'id_card_path' => $withDocuments ? self::SAMPLE_PDF : null,
            ]
        );

        return $user;
    }

    private function application(User $student, Unit $unit, ApplicationStatus $status, Carbon $start, Carbon $end, array $extra = []): Application
    {
        return Application::updateOrCreate(
            ['user_id' => $student->id],
            array_merge([
                'unit_id' => $unit->id,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'status' => $status->value,
                'letter_number' => '500.12.1/2'.str_pad((string) ($student->id % 100), 2, '0', STR_PAD_LEFT).'/436.7.14/'.$start->format('Y'),
                'letter_date' => $start->copy()->subDays(5)->toDateString(),
                'letter_token' => Str::random(32),
                'cv_path' => self::SAMPLE_PDF,
                'transcript_path' => self::SAMPLE_PDF,
                'id_card_path' => self::SAMPLE_PDF,
                'proposal_letter_path' => self::SAMPLE_PDF,
                'rejection_reason' => null,
            ], $extra)
        );
    }

    private function placement(Application $application, User $mentor, ?User $dosen, ?string $certificateNumber = null): Placement
    {
        $placement = Placement::firstOrNew(['application_id' => $application->id]);
        $placement->fill([
            'mentor_id' => $mentor->id,
            'pembimbing_id' => $mentor->id,
            'academic_advisor_id' => $dosen?->id,
            'certificate_number' => $certificateNumber ?? $placement->certificate_number,
            'certificate_hash' => $placement->certificate_hash ?: Str::random(32),
        ])->save();

        return $placement;
    }

    private function backfillApplicationDocuments(): void
    {
        // Akun dengan email pribadi bukan data demo; berkasnya tidak boleh diisi berkas contoh.
        $isDemoAccount = fn (?User $user) => $user && ! preg_match('/@(gmail|yahoo|outlook|hotmail)\./i', (string) $user->email);

        $applications = Application::whereDoesntHave('documents')->with('user')->get();
        foreach ($applications as $application) {
            if (! $isDemoAccount($application->user)) {
                continue;
            }

            $paths = [
                'Surat Pengantar' => $application->proposal_letter_path ?: self::SAMPLE_PDF,
                'CV' => $application->cv_path ?: self::SAMPLE_PDF,
                'Transkrip Nilai' => $application->transcript_path ?: self::SAMPLE_PDF,
                'KTM / Kartu Identitas' => $application->id_card_path ?: self::SAMPLE_PDF,
            ];

            $application->forceFill([
                'proposal_letter_path' => $paths['Surat Pengantar'],
                'cv_path' => $paths['CV'],
                'transcript_path' => $paths['Transkrip Nilai'],
                'id_card_path' => $paths['KTM / Kartu Identitas'],
            ])->saveQuietly();

            foreach ($paths as $type => $path) {
                ApplicationDocument::create(['application_id' => $application->id, 'document_type' => $type, 'file_path' => $path]);
            }
        }

        $reapplyAccounts = User::whereIn('email', ['gilang.ditolak@unesa.ac.id', 'rina.mundur@unesa.ac.id'])->pluck('id');
        StudentProfile::whereIn('user_id', $reapplyAccounts)->get()->each(function (StudentProfile $profile) {
            $profile->forceFill([
                'cv_path' => $profile->cv_path ?: self::SAMPLE_PDF,
                'transcript_path' => $profile->transcript_path ?: self::SAMPLE_PDF,
                'id_card_path' => $profile->id_card_path ?: self::SAMPLE_PDF,
            ])->save();
        });
    }

    private function backfillLogbooksForCompletedPlacements(): void
    {
        $activities = [
            'Orientasi unit kerja dan pengenalan alur layanan.',
            'Membantu pengolahan data dan administrasi harian unit kerja.',
            'Menyusun rangkuman pekerjaan dan serah terima tugas magang.',
        ];

        $placements = Placement::whereHas('application', fn ($q) => $q->where('status', ApplicationStatus::COMPLETED->value))
            ->whereDoesntHave('logbooks')
            ->with('application')
            ->get();

        foreach ($placements as $placement) {
            $start = Carbon::parse($placement->application->start_date ?? Carbon::now()->subDays(30));

            foreach ($activities as $offset => $activity) {
                Logbook::create([
                    'placement_id' => $placement->id,
                    'date' => $start->copy()->addDays($offset + 1)->toDateString(),
                    'activity' => $activity,
                    'status' => 'approved',
                    'lecturer_status' => 'approved',
                    'lecturer_verified_at' => Carbon::now(),
                ]);
            }
        }
    }

    private function logbook(Placement $placement, int $daysAgo, string $activity, string $status, ?string $feedback = null): void
    {
        Logbook::updateOrCreate(
            ['placement_id' => $placement->id, 'date' => Carbon::now()->subDays($daysAgo)->toDateString()],
            ['activity' => $activity, 'status' => $status, 'feedback' => $feedback]
        );
    }

    private function finalReport(Placement $placement, string $title, string $status, ?string $feedback = null): void
    {
        FinalReport::updateOrCreate(
            ['placement_id' => $placement->id],
            ['file_path' => self::SAMPLE_PDF, 'final_report_path' => self::SAMPLE_PDF, 'title' => $title, 'status' => $status, 'feedback' => $feedback]
        );
    }
}
