<?php

namespace Tests\Feature;

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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Temuan QC peran Mahasiswa (16 Nov 2026):
 *  - tanggal pengajuan/logbook yang tidak masuk akal diterima,
 *  - catatan penolakan mentor tidak tampil di daftar logbook,
 *  - laporan berstatus revisi bisa dikirim ulang tanpa berkas baru,
 *  - mengganti dokumen profil menghapus berkas yang masih dipakai pengajuan lama,
 *  - dashboard menawarkan unduh sertifikat padahal sertifikat belum diterbitkan.
 */
class StudentQcFlowTest extends TestCase
{
    use RefreshDatabase;

    private AgencyProfile $agency;

    private University $univ;

    private Unit $unit;

    private User $mentor;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');

        $this->agency = AgencyProfile::create(['agency_name' => 'Dinas A']);
        $this->univ = University::create(['name' => 'Universitas Uji', 'code' => 'UU']);
        $this->mentor = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $this->agency->id]);
        $this->unit = Unit::create(['agency_profile_id' => $this->agency->id, 'name' => 'Bidang Aptika', 'quota' => 5]);
        $this->student = User::factory()->create(['role' => 'mahasiswa', 'university_id' => $this->univ->id]);
        StudentProfile::create(['user_id' => $this->student->id, 'nim' => '1', 'universitas' => 'Universitas Uji', 'university_id' => $this->univ->id, 'jurusan' => 'TI', 'phone' => '08']);
    }

    private function placement(string $status = 'active', array $data = []): Placement
    {
        $application = Application::create([
            'user_id' => $this->student->id,
            'unit_id' => $this->unit->id,
            'status' => $status,
            'start_date' => Carbon::now()->subDays(10)->toDateString(),
            'end_date' => Carbon::now()->addDays(30)->toDateString(),
        ]);

        return Placement::create(array_merge(['application_id' => $application->id, 'mentor_id' => $this->mentor->id], $data));
    }

    private function applicationPayload(string $start, string $end): array
    {
        return [
            'unit_id' => $this->unit->id,
            'start_date' => $start,
            'end_date' => $end,
            'surat_pengantar' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
            'transkrip' => UploadedFile::fake()->create('transkrip.pdf', 100, 'application/pdf'),
            'id_card' => UploadedFile::fake()->create('ktm.pdf', 100, 'application/pdf'),
        ];
    }

    public function test_pengajuan_dengan_tanggal_mulai_di_masa_lalu_ditolak(): void
    {
        $this->actingAs($this->student)
            ->post(route('student.application.store'), $this->applicationPayload('2020-01-01', '2020-03-01'))
            ->assertSessionHasErrors(['start_date' => 'Tanggal mulai magang tidak boleh sebelum hari ini.']);

        $this->assertSame(0, Application::count());
    }

    public function test_pengajuan_dengan_periode_lebih_dari_satu_tahun_ditolak(): void
    {
        $start = Carbon::now()->addDays(5);

        $this->actingAs($this->student)
            ->post(route('student.application.store'), $this->applicationPayload($start->toDateString(), $start->copy()->addYears(36)->toDateString()))
            ->assertSessionHasErrors(['end_date' => 'Periode magang paling lama 1 tahun sejak tanggal mulai.']);

        $this->assertSame(0, Application::count());

        $this->actingAs($this->student)
            ->post(route('student.application.store'), $this->applicationPayload($start->toDateString(), $start->copy()->addMonths(3)->toDateString()))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Application::count());
    }

    public function test_kelompok_dengan_tanggal_mulai_di_masa_lalu_ditolak(): void
    {
        $this->student->studentProfile->update(['cv_path' => 'a.pdf', 'transcript_path' => 'b.pdf', 'id_card_path' => 'c.pdf']);

        $this->actingAs($this->student)
            ->post(route('student.group.create'), ['unit_id' => $this->unit->id, 'start_date' => '2019-01-01', 'end_date' => '2019-03-01', 'name' => 'Tim Lampau'])
            ->assertSessionHasErrors('start_date');

        $this->assertSame(0, Application::count());
    }

    public function test_logbook_menolak_tanggal_masa_depan_dan_sebelum_magang(): void
    {
        $placement = $this->placement();

        $this->actingAs($this->student)
            ->post(route('student.logbook.store'), ['date' => Carbon::now()->addYears(4)->toDateString(), 'activity' => 'Kegiatan dengan tanggal masa depan.'])
            ->assertSessionHasErrors(['date' => 'Tanggal kegiatan tidak boleh melewati hari ini.']);

        $this->actingAs($this->student)
            ->post(route('student.logbook.store'), ['date' => '1990-01-01', 'activity' => 'Kegiatan dengan tanggal sangat lampau.'])
            ->assertSessionHasErrors(['date' => 'Tanggal kegiatan tidak boleh sebelum periode magang Anda dimulai.']);

        $this->assertSame(0, Logbook::count());

        $this->actingAs($this->student)
            ->post(route('student.logbook.store'), ['date' => Carbon::now()->subDays(2)->toDateString(), 'activity' => 'Kegiatan dua hari yang lalu.'])
            ->assertSessionHasNoErrors();

        $log = Logbook::where('placement_id', $placement->id)->firstOrFail();

        $this->actingAs($this->student)
            ->put(route('student.logbook.update', $log->id), ['date' => Carbon::now()->addYear()->toDateString(), 'activity' => 'Mengubah tanggal ke masa depan.'])
            ->assertSessionHasErrors('date');
    }

    public function test_catatan_penolakan_mentor_tampil_di_daftar_logbook_mahasiswa(): void
    {
        $placement = $this->placement();
        Logbook::create([
            'placement_id' => $placement->id,
            'date' => Carbon::now()->subDay()->toDateString(),
            'activity' => 'Kegiatan harian.',
            'status' => 'rejected',
            'feedback' => 'Uraian terlalu singkat, jelaskan pekerjaannya.',
        ]);

        $this->actingAs($this->student)->get(route('student.logbook.index'))
            ->assertOk()
            ->assertSee('Catatan Mentor:')
            ->assertSee('Uraian terlalu singkat, jelaskan pekerjaannya.');
    }

    public function test_laporan_berstatus_revisi_wajib_berkas_baru(): void
    {
        $placement = $this->placement('active', ['academic_advisor_id' => User::factory()->create(['role' => 'dosen', 'university_id' => $this->univ->id])->id]);
        $report = FinalReport::create([
            'placement_id' => $placement->id,
            'title' => 'Judul Lama',
            'file_path' => 'final_reports/lama.pdf',
            'final_report_path' => 'final_reports/lama.pdf',
            'status' => 'revision',
            'feedback' => 'Bab 4 belum lengkap.',
        ]);

        $this->actingAs($this->student)
            ->post(route('student.final_report.store'), ['title' => 'Judul Lama'])
            ->assertSessionHasErrors('file_laporan');

        $report->refresh();
        $this->assertSame('revision', $report->status);
        $this->assertSame('Bab 4 belum lengkap.', $report->feedback);

        $this->actingAs($this->student)
            ->post(route('student.final_report.store'), ['file_laporan' => UploadedFile::fake()->create('revisi.pdf', 200, 'application/pdf')])
            ->assertSessionHasNoErrors();

        $report->refresh();
        $this->assertSame('pending', $report->status);
        $this->assertNull($report->feedback);
        $this->assertSame('Judul Lama', $report->title, 'Judul lama tidak boleh tertimpa judul bawaan saat kolom judul dikosongkan.');
    }

    public function test_mengganti_dokumen_profil_tidak_menghapus_berkas_yang_masih_dipakai_pengajuan(): void
    {
        $oldCv = 'documents/student-profiles/cv-lama.pdf';
        $oldTranscript = 'documents/student-profiles/transkrip-lama.pdf';
        Storage::disk('local')->put($oldCv, 'cv lama');
        Storage::disk('local')->put($oldTranscript, 'transkrip lama');
        $this->student->studentProfile->update(['cv_path' => $oldCv, 'transcript_path' => $oldTranscript]);

        // Pengajuan yang sudah aktif tetap menunjuk CV lama; transkrip lama tidak dirujuk siapa pun.
        $placement = $this->placement('active');
        $placement->application->update(['cv_path' => $oldCv]);

        $this->actingAs($this->student)->post(route('student.profile.update'), [
            'name' => $this->student->name,
            'nim' => '1',
            'university_id' => $this->univ->id,
            'jurusan' => 'TI',
            'phone' => '08123456789',
            'cv' => UploadedFile::fake()->create('cv-baru.pdf', 100, 'application/pdf'),
            'transkrip' => UploadedFile::fake()->create('transkrip-baru.pdf', 100, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $profile = $this->student->studentProfile->fresh();
        $this->assertNotSame($oldCv, $profile->cv_path);
        Storage::disk('local')->assertExists($profile->cv_path);
        Storage::disk('local')->assertExists($oldCv);
        Storage::disk('local')->assertMissing($oldTranscript);
        $this->assertSame($oldCv, $placement->application->fresh()->cv_path);
    }

    public function test_dashboard_tidak_menawarkan_unduh_sertifikat_sebelum_diterbitkan(): void
    {
        $placement = $this->placement('completed');
        Logbook::create(['placement_id' => $placement->id, 'date' => Carbon::now()->subDays(3)->toDateString(), 'activity' => 'Kegiatan harian magang.', 'status' => 'approved']);
        FinalReport::create(['placement_id' => $placement->id, 'title' => 'Laporan', 'file_path' => 'final_reports/a.pdf', 'final_report_path' => 'final_reports/a.pdf', 'status' => 'approved']);
        Evaluation::create([
            'placement_id' => $placement->id,
            'nilai_disiplin' => 85,
            'nilai_kinerja' => 85,
            'nilai_laporan' => 85,
            'final_score' => 85,
            'grade' => 'A',
        ]);

        $this->assertSame([], $placement->application->fresh()->certificateBlockers());

        $this->actingAs($this->student)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Magang Anda telah selesai.')
            ->assertSee('Sertifikat magang belum diterbitkan oleh Admin Dinas.')
            ->assertDontSee('E-Sertifikat resmi siap diunduh.');

        Certificate::create([
            'placement_id' => $placement->id,
            'certificate_number' => 'SERT/900/UJI/2026',
            'file_path' => 'certificates/uji.pdf',
            'issued_at' => Carbon::now()->toDateString(),
            'approval_status' => 'approved',
        ]);

        $this->actingAs($this->student)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('E-Sertifikat resmi siap diunduh.');
    }
}
