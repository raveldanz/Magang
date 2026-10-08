<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Evaluation;
use App\Models\FinalReport;
use App\Models\Logbook;
use App\Models\Placement;
use App\Models\SystemNotification;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncInternshipStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $student;
    private User $mentor;
    private User $dosen;
    private Unit $unit;
    private University $univ;

    protected function setUp(): void
    {
        parent::setUp();

        $this->univ = University::create([
            'name' => 'Institut Teknologi Sepuluh Nopember',
            'code' => 'ITS',
            'evaluation_scheme' => 'dual_evaluation',
            'require_dpl' => true,
        ]);

        $agency = AgencyProfile::create([
            'agency_name' => 'Dinas Komunikasi dan Informatika',
            'quota' => 25,
        ]);

        $this->unit = Unit::create([
            'agency_profile_id' => $agency->id,
            'name' => 'Bidang Aplikasi Informatika',
            'quota' => 10,
        ]);

        $this->mentor = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $agency->id]);
        $this->dosen = User::factory()->create(['role' => 'dosen', 'university_id' => $this->univ->id]);
        $this->student = User::factory()->create(['role' => 'mahasiswa', 'university_id' => $this->univ->id]);
        $this->student->studentProfile()->create([
            'nim' => '5025211001',
            'universitas' => $this->univ->name,
            'jurusan' => 'Informatika',
            'phone' => '081234567890',
        ]);
    }

    public function test_auto_activates_accepted_applications_when_start_date_arrives(): void
    {
        $app = Application::create([
            'user_id' => $this->student->id,
            'unit_id' => $this->unit->id,
            'status' => ApplicationStatus::ACCEPTED,
            'start_date' => Carbon::today()->toDateString(),
            'end_date' => Carbon::today()->addMonths(3)->toDateString(),
        ]);

        $this->artisan('app:sync-internship-status')
            ->expectsOutputToContain('Ditemukan 1 mahasiswa yang siap bertransisi ke status ACTIVE.')
            ->assertSuccessful();

        $this->assertSame(ApplicationStatus::ACTIVE, $app->fresh()->status);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'AUTO_ACTIVATE_INTERNSHIP',
            'target_id' => $app->id,
        ]);
    }

    public function test_leaves_future_accepted_applications_as_accepted(): void
    {
        $app = Application::create([
            'user_id' => $this->student->id,
            'unit_id' => $this->unit->id,
            'status' => ApplicationStatus::ACCEPTED,
            'start_date' => Carbon::today()->addDays(3)->toDateString(),
            'end_date' => Carbon::today()->addMonths(3)->toDateString(),
        ]);

        $this->artisan('app:sync-internship-status')
            ->expectsOutputToContain('Tidak ada mahasiswa berstatus ACCEPTED yang siap diaktifkan hari ini.')
            ->assertSuccessful();

        $this->assertSame(ApplicationStatus::ACCEPTED, $app->fresh()->status);
    }

    public function test_auto_completes_ended_active_applications_when_all_requirements_complete(): void
    {
        $app = Application::create([
            'user_id' => $this->student->id,
            'unit_id' => $this->unit->id,
            'status' => ApplicationStatus::ACTIVE,
            'start_date' => Carbon::today()->subMonths(3)->toDateString(),
            'end_date' => Carbon::today()->subDay()->toDateString(),
        ]);

        $placement = Placement::create([
            'application_id' => $app->id,
            'mentor_id' => $this->mentor->id,
            'academic_advisor_id' => $this->dosen->id,
        ]);

        FinalReport::create([
            'placement_id' => $placement->id,
            'file_path' => 'reports/test.pdf',
            'status' => 'approved',
        ]);

        Evaluation::create([
            'placement_id' => $placement->id,
            'nilai_disiplin' => 90,
            'nilai_kinerja' => 90,
            'nilai_laporan' => 90,
            'nilai_dosen' => 90,
            'nilai_akademik' => 90,
        ]);

        Logbook::create([
            'placement_id' => $placement->id,
            'date' => Carbon::today()->subDays(5)->toDateString(),
            'activity' => 'Mengerjakan sistem integrasi API',
            'status' => 'approved',
        ]);

        $this->artisan('app:sync-internship-status')
            ->expectsOutputToContain('COMPLETED')
            ->assertSuccessful();

        $this->assertSame(ApplicationStatus::COMPLETED, $app->fresh()->status);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'AUTO_COMPLETE_INTERNSHIP',
            'target_id' => $app->id,
        ]);
    }

    public function test_sends_warning_and_keeps_active_when_requirements_incomplete(): void
    {
        $app = Application::create([
            'user_id' => $this->student->id,
            'unit_id' => $this->unit->id,
            'status' => ApplicationStatus::ACTIVE,
            'start_date' => Carbon::today()->subMonths(3)->toDateString(),
            'end_date' => Carbon::today()->subDay()->toDateString(),
        ]);

        Placement::create([
            'application_id' => $app->id,
            'mentor_id' => $this->mentor->id,
            'academic_advisor_id' => $this->dosen->id,
        ]);

        $this->artisan('app:sync-internship-status')
            ->expectsOutputToContain('Menunggu Evaluasi Mentor/Kelulusan')
            ->assertSuccessful();

        // Status remains ACTIVE
        $this->assertSame(ApplicationStatus::ACTIVE, $app->fresh()->status);

        // Reminder notification created for student
        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $this->student->id,
            'title' => 'Masa Magang Telah Berakhir',
        ]);

        // Reminder notification created for mentor
        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $this->mentor->id,
            'title' => 'Mahasiswa Melewati Masa Magang',
        ]);
    }
}
