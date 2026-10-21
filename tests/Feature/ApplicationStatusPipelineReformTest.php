<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Logbook;
use App\Models\Placement;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicationStatusPipelineReformTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $supervisor;
    private User $superAdmin;
    private User $student;
    private User $mentor;
    private User $dosen;
    private AgencyProfile $agency;
    private Unit $unit;
    private University $univ;

    protected function setUp(): void
    {
        parent::setUp();

        $this->univ = University::create([
            'name' => 'Universitas Airlangga',
            'code' => 'UNAIR',
            'evaluation_scheme' => 'dual_evaluation',
            'require_dpl' => true,
        ]);

        $this->agency = AgencyProfile::create([
            'agency_name' => 'Dinas Perpustakaan dan Kearsipan Kota Surabaya',
            'quota' => 20,
        ]);

        $this->unit = Unit::create([
            'agency_profile_id' => $this->agency->id,
            'name' => 'Bidang Layanan Kearsipan',
            'quota' => 5,
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'agency_profile_id' => $this->agency->id,
        ]);

        $this->supervisor = User::factory()->create([
            'role' => 'supervisor',
            'agency_profile_id' => $this->agency->id,
        ]);

        $this->superAdmin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $this->mentor = User::factory()->create([
            'role' => 'mentor',
            'agency_profile_id' => $this->agency->id,
            'unit_id' => $this->unit->id,
        ]);

        $this->dosen = User::factory()->create([
            'role' => 'dosen',
            'university_id' => $this->univ->id,
        ]);

        $this->student = User::factory()->create([
            'role' => 'mahasiswa',
            'university_id' => $this->univ->id,
        ]);
        $this->student->studentProfile()->create([
            'nim' => '042111535001',
            'universitas' => $this->univ->name,
            'jurusan' => 'Sistem Informasi',
            'phone' => '081298765432',
        ]);
    }

    public function test_enum_has_no_verified_case(): void
    {
        $cases = array_map(fn ($c) => $c->value, ApplicationStatus::cases());
        $this->assertNotContains('verified', $cases);
        $this->assertContains('pending', $cases);
        $this->assertContains('waiting_approval', $cases);
        $this->assertContains('accepted', $cases);
        $this->assertContains('active', $cases);
        $this->assertContains('completed', $cases);
        $this->assertContains('rejected', $cases);
        $this->assertContains('resigned', $cases);
    }

    public function test_auto_activate_if_started_method_activates_on_or_past_start_date(): void
    {
        $app = Application::create([
            'user_id' => $this->student->id,
            'unit_id' => $this->unit->id,
            'status' => ApplicationStatus::ACCEPTED,
            'start_date' => Carbon::today()->subDay()->toDateString(),
            'end_date' => Carbon::today()->addMonths(2)->toDateString(),
        ]);

        $placement = Placement::create([
            'application_id' => $app->id,
            'mentor_id' => $this->mentor->id,
            'academic_advisor_id' => $this->dosen->id,
        ]);

        $this->assertTrue($app->autoActivateIfStarted());
        $this->assertSame(ApplicationStatus::ACTIVE, $app->fresh()->status);
        $this->assertSame(ApplicationStatus::ACTIVE, $placement->fresh()->status);

        // Memanggil ulang tidak mengubah status lagi
        $this->assertFalse($app->autoActivateIfStarted());
    }

    public function test_auto_activates_when_student_submits_first_logbook(): void
    {
        Storage::fake('public');

        $app = Application::create([
            'user_id' => $this->student->id,
            'unit_id' => $this->unit->id,
            'status' => ApplicationStatus::ACCEPTED,
            'start_date' => Carbon::today()->addDays(5)->toDateString(),
            'end_date' => Carbon::today()->addMonths(3)->toDateString(),
        ]);

        $placement = Placement::create([
            'application_id' => $app->id,
            'mentor_id' => $this->mentor->id,
            'academic_advisor_id' => $this->dosen->id,
        ]);

        $response = $this->actingAs($this->student)->post(route('student.logbook.store'), [
            'date' => Carbon::today()->toDateString(),
            'time_in' => '08:00',
            'time_out' => '16:00',
            'activity' => 'Melakukan digitalisasi arsip dokumen dinas',
            'tools_used' => 'Scanner, Komputer Arsip',
            'documentation' => UploadedFile::fake()->image('bukti.jpg'),
        ]);

        $response->assertRedirect(route('student.logbook.index'));
        $this->assertSame(ApplicationStatus::ACTIVE, $app->fresh()->status);
        $this->assertDatabaseHas('logbooks', [
            'placement_id' => $placement->id,
            'activity' => 'Melakukan digitalisasi arsip dokumen dinas',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'AUTO_ACTIVATE_ON_FIRST_LOGBOOK',
            'target_id' => $app->id,
        ]);
    }

    public function test_admin_staff_cannot_directly_accept_waiting_approval_application(): void
    {
        $app = Application::create([
            'user_id' => $this->student->id,
            'unit_id' => $this->unit->id,
            'status' => ApplicationStatus::WAITING_APPROVAL,
            'start_date' => Carbon::today()->addDays(10)->toDateString(),
            'end_date' => Carbon::today()->addMonths(3)->toDateString(),
        ]);

        // Staf Admin mencoba langsung menerima (ACC)
        $response = $this->actingAs($this->admin)->put(route('admin.applications.updateStatus', $app->id), [
            'status' => 'accepted',
            'letter_number' => '500/ACC/2026',
            'letter_date' => Carbon::today()->toDateString(),
        ]);

        $response->assertSessionHas('error');
        $this->assertSame(ApplicationStatus::WAITING_APPROVAL, $app->fresh()->status);

        // Supervisor berhak melakukan ACC
        $responseSupervisor = $this->actingAs($this->supervisor)->put(route('admin.applications.updateStatus', $app->id), [
            'status' => 'accepted',
            'letter_number' => '500/ACC-OK/2026',
            'letter_date' => Carbon::today()->toDateString(),
        ]);

        $responseSupervisor->assertSessionHas('success');
        $this->assertSame(ApplicationStatus::ACCEPTED, $app->fresh()->status);
    }

    public function test_admin_staff_approving_pending_is_routed_to_waiting_approval(): void
    {
        $app = Application::create([
            'user_id' => $this->student->id,
            'unit_id' => $this->unit->id,
            'status' => ApplicationStatus::PENDING,
            'start_date' => Carbon::today()->addDays(5)->toDateString(),
            'end_date' => Carbon::today()->addMonths(3)->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.applications.updateStatus', $app->id), [
            'status' => 'accepted',
            'mentor_id' => $this->mentor->id,
            'academic_advisor_id' => $this->dosen->id,
            'letter_number' => '500/PLOT/2026',
            'letter_date' => Carbon::today()->toDateString(),
        ]);

        $response->assertSessionHas('success');
        // Alur Double ACC: otomatis berstatus waiting_approval
        $this->assertSame(ApplicationStatus::WAITING_APPROVAL, $app->fresh()->status);
    }

    public function test_updating_status_and_mentor_preserves_placement_and_logbooks(): void
    {
        $app = Application::create([
            'user_id' => $this->student->id,
            'unit_id' => $this->unit->id,
            'status' => ApplicationStatus::ACTIVE,
            'start_date' => Carbon::today()->subWeeks(2)->toDateString(),
            'end_date' => Carbon::today()->addMonths(2)->toDateString(),
        ]);

        $placement = Placement::create([
            'application_id' => $app->id,
            'mentor_id' => $this->mentor->id,
            'academic_advisor_id' => $this->dosen->id,
            'start_date' => $app->start_date,
            'end_date' => $app->end_date,
        ]);

        $logbook = Logbook::create([
            'placement_id' => $placement->id,
            'date' => Carbon::today()->subDays(5)->toDateString(),
            'activity' => 'Aktivitas minggu pertama',
            'status' => 'approved',
        ]);

        $newMentor = User::factory()->create([
            'role' => 'mentor',
            'agency_profile_id' => $this->agency->id,
            'unit_id' => $this->unit->id,
        ]);

        // Admin memperbarui mentor dan tanggal mulai via form utama
        $response = $this->actingAs($this->admin)->put(route('admin.applications.updateStatus', $app->id), [
            'status' => 'active',
            'mentor_id' => $newMentor->id,
            'start_date' => Carbon::today()->subWeeks(3)->toDateString(),
            'end_date' => Carbon::today()->addMonths(3)->toDateString(),
        ]);

        $response->assertSessionHas('success');

        // Pastikan placement ID tidak berubah (tidak di-delete lalu create ulang)
        $freshPlacement = $app->fresh()->placement;
        $this->assertSame($placement->id, $freshPlacement->id);
        $this->assertSame($newMentor->id, $freshPlacement->mentor_id);

        // Pastikan relasi logbook tidak ter-reset
        $this->assertDatabaseHas('logbooks', [
            'id' => $logbook->id,
            'placement_id' => $placement->id,
            'activity' => 'Aktivitas minggu pertama',
            'status' => 'approved',
        ]);
    }
}
