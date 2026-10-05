<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\ApplicationDocument;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Paket perbaikan 2:
 *  - Fallback verifikasi logbook oleh Kepala Unit / Admin Dinas (Mentor\LogbookController)
 *  - Dokumen pengajuan di disk privat + route unduh terotorisasi + batas ukuran unggahan
 *  - Certificate Gate (evaluasi mentor lengkap + laporan ACC + status COMPLETED)
 */
class Phase2SupervisionStorageCertificateTest extends TestCase
{
    use RefreshDatabase;

    private AgencyProfile $agency;

    private University $univ;

    private Unit $unit;

    private User $adminA;

    private User $unitHead;

    private User $otherMentor;

    private User $assignedMentor;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = AgencyProfile::create(['agency_name' => 'Dinas A']);
        $this->univ = University::create(['name' => 'Universitas Uji', 'code' => 'UU']);
        $this->adminA = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agency->id]);
        $this->unitHead = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $this->agency->id]);
        $this->otherMentor = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $this->agency->id]);
        $this->assignedMentor = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $this->agency->id]);
        $this->unit = Unit::create(['agency_profile_id' => $this->agency->id, 'name' => 'Bidang Aptika', 'quota' => 5, 'head_user_id' => $this->unitHead->id]);
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

        return Placement::create(array_merge(['application_id' => $application->id], $data));
    }

    private function logbook(Placement $placement): Logbook
    {
        return Logbook::create(['placement_id' => $placement->id, 'date' => now()->toDateString(), 'activity' => 'Kegiatan hari pertama', 'status' => 'pending']);
    }

    // ---------------------------------------------------------------
    // BAGIAN 1: Fallback logbook (Kepala Unit) & re-assign
    // ---------------------------------------------------------------

    public function test_unit_head_can_review_logbook_while_no_mentor_assigned(): void
    {
        $log = $this->logbook($this->placement());

        $this->actingAs($this->unitHead)->get(route('mentor.logbooks.index'))->assertOk()->assertSee('Kepala Unit');
        $this->actingAs($this->unitHead)->get(route('mentor.logbooks.show', $log->id))->assertOk();
        $this->actingAs($this->unitHead)
            ->put(route('mentor.logbooks.updateStatus', $log->id), ['status' => 'approved', 'feedback' => 'Baik'])
            ->assertRedirect();
        $this->assertSame('approved', $log->fresh()->status);

        // Mentor lain di instansi yang sama (bukan kepala unit) tidak boleh
        $this->actingAs($this->otherMentor)->get(route('mentor.logbooks.show', $log->id))->assertForbidden();
        $this->actingAs($this->otherMentor)
            ->put(route('mentor.logbooks.updateStatus', $log->id), ['status' => 'rejected'])
            ->assertForbidden();
    }

    public function test_fallback_closes_after_mentor_assigned_and_history_is_kept(): void
    {
        $placement = $this->placement();
        $log = $this->logbook($placement);
        $this->actingAs($this->unitHead)->put(route('mentor.logbooks.updateStatus', $log->id), ['status' => 'approved', 'feedback' => 'OK awal']);

        $this->actingAs($this->adminA)
            ->patch(route('admin.applications.assignment', $placement->application_id), ['mentor_id' => $this->assignedMentor->id])
            ->assertSessionHasNoErrors();

        $log->refresh();
        $this->assertSame('approved', $log->status);
        $this->assertSame('OK awal', $log->feedback);

        $log2 = $this->logbook($placement);
        $this->actingAs($this->unitHead)
            ->put(route('mentor.logbooks.updateStatus', $log2->id), ['status' => 'approved'])
            ->assertForbidden();
        $this->actingAs($this->assignedMentor)
            ->put(route('mentor.logbooks.updateStatus', $log2->id), ['status' => 'approved'])
            ->assertRedirect();
        $this->assertSame('approved', $log2->fresh()->status);
    }

    public function test_unit_head_must_be_mentor_of_same_agency(): void
    {
        $foreignAgency = AgencyProfile::create(['agency_name' => 'Dinas B']);
        $foreignMentor = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $foreignAgency->id]);

        $this->actingAs($this->adminA)
            ->put(route('admin.units.update', $this->unit->id), ['name' => 'Bidang Aptika', 'quota' => 5, 'head_user_id' => $foreignMentor->id])
            ->assertSessionHasErrors('head_user_id');

        $this->actingAs($this->adminA)
            ->put(route('admin.units.update', $this->unit->id), ['name' => 'Bidang Aptika', 'quota' => 5, 'head_user_id' => $this->otherMentor->id])
            ->assertSessionHasNoErrors();
        $this->assertSame($this->otherMentor->id, (int) $this->unit->fresh()->head_user_id);
    }

    // ---------------------------------------------------------------
    // BAGIAN 2: Dokumen privat & batas ukuran
    // ---------------------------------------------------------------

    public function test_application_document_download_is_private_and_authorized(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $this->actingAs($this->student)->post(route('student.application.store'), [
            'unit_id' => $this->unit->id,
            'start_date' => Carbon::now()->addDays(5)->toDateString(),
            'end_date' => Carbon::now()->addDays(60)->toDateString(),
            'surat_pengantar' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
            'transkrip' => UploadedFile::fake()->create('transkrip.pdf', 100, 'application/pdf'),
            'id_card' => UploadedFile::fake()->create('ktm.pdf', 100, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $doc = ApplicationDocument::first();
        $this->assertStringStartsWith('documents/applications/', $doc->file_path);
        Storage::disk('local')->assertExists($doc->file_path);
        Storage::disk('public')->assertMissing($doc->file_path);

        $url = route('documents.application.download', $doc->id);
        $univAdmin = User::factory()->create(['role' => 'universitas', 'university_id' => $this->univ->id]);
        $otherUnivAdmin = User::factory()->create(['role' => 'universitas', 'university_id' => University::create(['name' => 'Lain'])->id]);
        $dosen = User::factory()->create(['role' => 'dosen', 'university_id' => $this->univ->id]);

        Auth::logout();
        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs($this->student)->get($url)->assertOk()->assertDownload();
        $this->actingAs($this->adminA)->get($url)->assertOk();
        $this->actingAs($univAdmin)->get($url)->assertOk();
        $this->actingAs($otherUnivAdmin)->get($url)->assertForbidden();
        $this->actingAs($this->assignedMentor)->get($url)->assertForbidden();
        $this->actingAs($dosen)->get($url)->assertForbidden();
    }

    public function test_application_documents_over_2mb_are_rejected(): void
    {
        Storage::fake('local');

        $this->actingAs($this->student)->post(route('student.application.store'), [
            'unit_id' => $this->unit->id,
            'start_date' => Carbon::now()->addDays(5)->toDateString(),
            'end_date' => Carbon::now()->addDays(60)->toDateString(),
            'surat_pengantar' => UploadedFile::fake()->create('surat.pdf', 2049, 'application/pdf'),
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
            'transkrip' => UploadedFile::fake()->create('transkrip.pdf', 100, 'application/pdf'),
            'id_card' => UploadedFile::fake()->create('ktm.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors('surat_pengantar');

        $this->assertSame(0, Application::count());
    }

    public function test_logbook_attachment_limit_is_2mb(): void
    {
        Storage::fake('public');
        $this->placement('active', ['mentor_id' => $this->assignedMentor->id]);

        $this->actingAs($this->student)->post(route('student.logbook.store'), [
            'date' => now()->toDateString(),
            'activity' => 'Mengerjakan modul pelaporan.',
            'attachment' => UploadedFile::fake()->create('bukti.pdf', 2100, 'application/pdf'),
        ])->assertSessionHasErrors('attachment');

        $this->actingAs($this->student)->post(route('student.logbook.store'), [
            'date' => now()->toDateString(),
            'activity' => 'Mengerjakan modul pelaporan.',
            'attachment' => UploadedFile::fake()->create('bukti.pdf', 1500, 'application/pdf'),
        ])->assertSessionHasNoErrors();
    }

    public function test_final_report_must_be_pdf_max_5mb(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $this->placement('active', ['mentor_id' => $this->assignedMentor->id, 'pembimbing_id' => $this->assignedMentor->id]);

        $this->actingAs($this->student)->post(route('student.final_report.store'), [
            'file_laporan' => UploadedFile::fake()->create('laporan.pdf', 5200, 'application/pdf'),
        ])->assertSessionHasErrors('file_laporan');

        $this->actingAs($this->student)->post(route('student.final_report.store'), [
            'file_laporan' => UploadedFile::fake()->create('laporan.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        ])->assertSessionHasErrors('file_laporan');

        $this->actingAs($this->student)->post(route('student.final_report.store'), [
            'file_laporan' => UploadedFile::fake()->create('laporan.pdf', 4800, 'application/pdf'),
        ])->assertSessionHasNoErrors();
    }

    // ---------------------------------------------------------------
    // BAGIAN 3: Certificate Gate
    // ---------------------------------------------------------------

    public function test_certificate_is_locked_until_all_requirements_met(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $placement = $this->placement('completed', ['mentor_id' => $this->assignedMentor->id]);
        $appId = $placement->application_id;

        // Status COMPLETED saja tidak cukup
        $this->actingAs($this->student)->get(route('student.certificate.show', $appId))
            ->assertForbidden()
            ->assertSee('Sertifikat belum dapat diunduh karena nilai evaluasi dinas atau laporan akhir belum disetujui')
            ->assertSee('Mentor Lapangan')
            ->assertSee('Laporan akhir magang belum disetujui');
        $this->actingAs($this->adminA)->get(route('admin.certificates.generate', $placement->id))->assertForbidden();

        FinalReport::create(['placement_id' => $placement->id, 'file_path' => 'x.pdf', 'status' => 'approved']);
        Evaluation::create(['placement_id' => $placement->id, 'nilai_disiplin' => 90, 'nilai_kinerja' => 85]); // nilai laporan belum

        $this->actingAs($this->student)->get(route('student.certificate.download', $placement->id))->assertForbidden();

        Evaluation::where('placement_id', $placement->id)->update(['nilai_laporan' => 88, 'nilai_akademik' => 90]);

        $this->actingAs($this->student)->get(route('student.certificate.download', $placement->id))->assertOk();
        $this->actingAs($this->adminA)->get(route('admin.certificates.generate', $placement->id))->assertOk();
        $this->actingAs($superAdmin)->get(route('admin.certificates.index'))->assertOk();
    }

    public function test_certificate_locked_when_status_not_completed(): void
    {
        $placement = $this->placement('active', ['mentor_id' => $this->assignedMentor->id]);
        FinalReport::create(['placement_id' => $placement->id, 'file_path' => 'x.pdf', 'status' => 'approved']);
        Evaluation::create(['placement_id' => $placement->id, 'nilai_disiplin' => 90, 'nilai_kinerja' => 85, 'nilai_laporan' => 88]);

        $application = Application::find($placement->application_id);
        $this->assertSame(['Status magang belum dinyatakan lulus / selesai.'], $application->certificateBlockers());

        $this->actingAs($this->student)->get(route('student.certificate.show', $application->id))
            ->assertForbidden()
            ->assertSee('Status magang belum dinyatakan lulus');
    }
}
