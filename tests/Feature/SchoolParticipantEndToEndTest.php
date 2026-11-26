<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Certificate;
use App\Models\FinalReport;
use App\Models\Logbook;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use App\Services\MasterUniversityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Alur demo lengkap untuk peserta dari SEKOLAH (SMK), lewat rute aplikasi sungguhan:
 * guru daftar sendiri → disetujui Admin Sekolah → siswa daftar & lengkapi profil sekolah →
 * ajukan magang → verifikasi Admin Dinas → ACC Atasan (Double ACC) → aktif → pilih pembimbing →
 * logbook → laporan akhir → nilai mentor & pembimbing → sertifikat → pengesahan → verifikasi QR.
 * Di setiap dokumen peserta tampil sebagai siswa: NIS, Kelas, Sekolah, tanpa MBKM.
 */
class SchoolParticipantEndToEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_participant_full_flow(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        app(MasterUniversityService::class)->syncSchools();
        $school = University::where('name', 'SMK NEGERI 7 SURABAYA')->firstOrFail();
        // Kebijakan sekolah: nilai gabungan 40% mentor dinas + 60% guru pembimbing
        $school->update(['evaluation_scheme' => 'dual_evaluation', 'weight_mentor' => 40, 'weight_lecturer' => 60, 'require_dpl' => true]);

        $agency = AgencyProfile::create(['agency_name' => 'Dinas Perpustakaan dan Kearsipan']);
        $unit = Unit::create(['agency_profile_id' => $agency->id, 'name' => 'Bidang Kearsipan', 'quota' => 10]);
        $adminDinas = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $agency->id]);
        $supervisor = User::factory()->create(['role' => 'supervisor', 'agency_profile_id' => $agency->id]);
        $mentor = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $agency->id, 'unit_id' => $unit->id]);
        $schoolAdmin = User::factory()->create(['role' => 'universitas', 'university_id' => $school->id, 'university' => $school->name]);

        // ── 1. Guru daftar sendiri dari halaman Daftar Akun (sekolah dikenali dari nama singkat) ──
        $this->get('/register')->assertOk();
        $this->getJson(route('register.institutions', ['q' => 'SMK NEGERI 7 SURABAYA']))->assertOk()->assertJsonFragment(['id' => $school->id]);
        $this->post('/register', [
            'register_as' => 'advisor',
            'name' => 'Sri Wahyuni, S.Pd.',
            'email' => 'guru@smkn7.test',
            'university' => 'smkn 7 surabaya',
            'password' => 'RahasiaGuru123',
            'password_confirmation' => 'RahasiaGuru123',
        ])->assertRedirect(route('dashboard'));
        auth()->logout();
        $teacher = User::where('email', 'guru@smkn7.test')->firstOrFail();
        $this->assertSame($school->id, (int) $teacher->university_id);
        $this->assertTrue($teacher->isPendingApproval());

        // ── 2. Admin Sekolah menyetujui guru ──
        $this->actingAs($schoolAdmin)->post(route('university.lecturers.approve', $teacher->id))->assertSessionHas('success');
        $this->assertFalse($teacher->fresh()->isPendingApproval());

        // ── 3. Siswa daftar dan melengkapi profil sebagai peserta sekolah ──
        auth()->logout();
        $this->post('/register', [
            'name' => 'Putri Ayu Lestari',
            'email' => 'putri@siswa.test',
            'password' => 'RahasiaSiswa123',
            'password_confirmation' => 'RahasiaSiswa123',
        ])->assertRedirect(route('student.profile.edit'));
        $student = User::where('email', 'putri@siswa.test')->firstOrFail();
        $this->actingAs($student)->post(route('student.profile.update'), [
            'name' => 'Putri Ayu Lestari',
            'nim' => '0071234561',
            'education_level' => 'sekolah',
            'university_id' => $school->id,
            'jurusan' => 'Otomatisasi Tata Kelola Perkantoran',
            'semester' => 11,
            'phone' => '081234567890',
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
            'id_card' => UploadedFile::fake()->create('kartu-pelajar.pdf', 100, 'application/pdf'),
        ])->assertSessionHasNoErrors();
        $student = $student->fresh();
        $this->assertTrue($student->studentProfile->isSchoolStudent());

        // ── 4. Siswa mengajukan magang ──
        $this->actingAs($student)->post(route('student.application.store'), [
            'unit_id' => $unit->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(60)->toDateString(),
            'surat_pengantar' => UploadedFile::fake()->create('surat-pengantar.pdf', 100, 'application/pdf'),
            'proposal_kampus' => UploadedFile::fake()->create('proposal.pdf', 100, 'application/pdf'),
        ])->assertSessionHasNoErrors();
        $application = Application::where('user_id', $student->id)->firstOrFail();
        $this->assertSame('pending', $application->statusValue());

        // ── 5. Admin Dinas memverifikasi dan menetapkan mentor → menunggu ACC Atasan ──
        $this->actingAs($adminDinas)->get(route('admin.applications.show', $application->id))
            ->assertOk()->assertSee('Asal Kampus/Sekolah')->assertSee('SMK NEGERI 7 SURABAYA');
        $this->actingAs($adminDinas)->put(route('admin.applications.updateStatus', $application->id), [
            'status' => 'accepted',
            'mentor_id' => $mentor->id,
            'letter_number' => '500.12.1/PKL-01/2026',
            'letter_date' => now()->toDateString(),
        ])->assertSessionHasNoErrors();
        $this->assertSame('waiting_approval', $application->fresh()->statusValue());

        // ── 6. Atasan (Supervisor) memberi ACC kedua → magang langsung aktif (tanggal mulai hari ini) ──
        $this->actingAs($supervisor)->post(route('supervisor.applications.approve', $application->id))->assertSessionHasNoErrors();
        $application = $application->fresh();
        $this->assertSame('active', $application->statusValue());
        $placement = $application->placement;
        $this->assertSame($mentor->id, (int) $placement->mentor_id);

        // Surat penerimaan untuk siswa memakai istilah sekolah
        $letter = $this->actingAs($student)->get(route('student.application.letter', $application->id))->assertOk()->getContent();
        $this->assertStringContainsString('dari Sekolah Saudara', $letter);
        $this->assertStringContainsString('Kelas 11', $letter);
        $this->assertStringNotContainsString('Universitas Saudara', $letter);

        // ── 7. Siswa memilih guru pembimbing ──
        $this->actingAs($student)->post(route('student.select_advisor'), ['academic_advisor_id' => $teacher->id])->assertSessionHas('success');
        $placement = $placement->fresh();
        $this->assertSame($teacher->id, (int) $placement->academic_advisor_id);

        // ── 8. Logbook harian, diperiksa mentor dan pembimbing ──
        $this->actingAs($student)->post(route('student.logbook.store'), [
            'date' => now()->toDateString(),
            'activity' => 'Menata arsip surat masuk dan mencatat nomor agenda.',
        ])->assertSessionHasNoErrors();
        $logbook = Logbook::where('placement_id', $placement->id)->firstOrFail();
        $this->actingAs($mentor)->post(route('mentor.logbooks.bulk_review'), [
            'logbook_ids' => [$logbook->id],
            'status' => 'approved',
        ])->assertSessionHas('success');
        $this->actingAs($teacher)->post(route('lecturer.logbooks.bulk_approve'), [
            'logbook_ids' => [$logbook->id],
            'status' => 'per_day',
        ])->assertSessionHas('success');
        $this->assertSame('approved', $logbook->fresh()->status);

        // Halaman mentor & pembimbing menampilkan siswa dengan label sekolah
        $this->actingAs($mentor)->get(route('mentor.students.show', $placement->id))
            ->assertOk()->assertSee('Kampus/Sekolah')->assertDontSee('Dosen Pembimbing');
        $this->actingAs($teacher)->get(route('lecturer.dashboard'))->assertOk()->assertSee('PUTRI AYU LESTARI');

        // ── 9. Laporan akhir diunggah lalu disetujui mentor ──
        $this->actingAs($student)->post(route('student.final_report.store'), [
            'title' => 'Laporan PKL: Penataan Arsip Surat Masuk',
            'file_laporan' => UploadedFile::fake()->create('laporan.pdf', 300, 'application/pdf'),
        ])->assertSessionHasNoErrors();
        $report = FinalReport::where('placement_id', $placement->id)->firstOrFail();
        $this->actingAs($mentor)->put(route('mentor.final_report.updateStatus', $report->id), ['status' => 'approved'])->assertSessionHas('success');

        // ── 10. Nilai mentor (40%) dan guru pembimbing (60%) → magang selesai ──
        $this->actingAs($mentor)->post(route('mentor.evaluations.store', $placement->id), [
            'aspects' => [
                ['name' => 'Disiplin & Kehadiran', 'score' => 90],
                ['name' => 'Kinerja Teknis', 'score' => 88],
                ['name' => 'Inisiatif & Komunikasi', 'score' => 86],
            ],
            'catatan' => 'Teliti dan disiplin.',
        ])->assertSessionHasNoErrors();
        $this->actingAs($teacher)->post(route('lecturer.evaluations.store', $placement->id), [
            'aspects' => [
                ['name' => 'Penguasaan Kompetensi Keahlian', 'score' => 85],
                ['name' => 'Kualitas Laporan PKL', 'score' => 87],
                ['name' => 'Sikap & Etika Kerja', 'score' => 90],
            ],
            'feedback_dosen' => 'Kompetensi kearsipan sudah baik.',
        ])->assertSessionHasNoErrors();
        // Aturan sistem: magang baru selesai setelah tanggal akhir lewat (dijalankan penjadwal harian)
        $this->assertSame('active', $application->fresh()->statusValue());
        $this->travel(61)->days();
        $this->artisan('app:sync-internship-status')->assertSuccessful();
        $application = $application->fresh();
        $this->assertSame('completed', $application->statusValue());

        // ── 11. Admin Dinas menerbitkan sertifikat, Atasan mengesahkan ──
        $certificatePage = $this->actingAs($adminDinas)->get(route('admin.certificates.generate', $placement->id))->assertOk()->getContent();
        $this->assertStringContainsString('Sekolah Asal', $certificatePage);
        $certificate = Certificate::where('placement_id', $placement->id)->firstOrFail();
        $this->assertSame('waiting_approval', $certificate->approval_status);
        $this->actingAs($supervisor)->post(route('supervisor.certificates.approve', $certificate->id))->assertSessionHasNoErrors();
        $this->assertSame('approved', $certificate->fresh()->approval_status);

        // ── 12. Verifikasi QR publik (tanpa login) ──
        auth()->logout();
        $hash = $placement->fresh()->certificate_hash;
        $this->assertNotEmpty($hash);
        $verify = $this->get('/verify-certificate/'.$hash)->assertOk()->getContent();
        $this->assertStringContainsString('NIS', $verify);
        $this->assertStringContainsString('Sekolah', $verify);
        $this->assertStringNotContainsString('Perguruan Tinggi', $verify);

        $token = $application->letter_token;
        if ($token) {
            $this->get('/verify-letter/'.$token)->assertOk()->assertSee('Sekolah / Jurusan');
        }
    }
}
