<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Placement;
use App\Models\StudentProfile;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleStatusIntegrationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function setupDispusipEcosystem(): array
    {
        $dispusip = AgencyProfile::create([
            'agency_name' => 'Dinas Perpustakaan dan Kearsipan Kota Surabaya',
            'acronym' => 'Dispusip',
            'city' => 'Kota Surabaya',
            'email' => 'dispusip@surabaya.go.id',
        ]);

        $unit = Unit::create([
            'agency_profile_id' => $dispusip->id,
            'name' => 'Bidang Pembinaan dan Pengelolaan Perpustakaan',
            'quota' => 15,
        ]);

        $univ = University::create([
            'name' => 'Universitas Airlangga',
            'code' => 'UNAIR',
        ]);

        $supervisor = User::factory()->create([
            'role' => 'supervisor',
            'agency_profile_id' => $dispusip->id,
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'agency_profile_id' => $dispusip->id,
        ]);

        $mentor = User::factory()->create([
            'role' => 'mentor',
            'agency_profile_id' => $dispusip->id,
            'unit_id' => $unit->id,
        ]);

        $dosen = User::factory()->create([
            'role' => 'dosen',
            'university_id' => $univ->id,
            'university' => $univ->name,
        ]);

        $univUser = User::factory()->create([
            'role' => 'universitas',
            'university_id' => $univ->id,
            'university' => $univ->name,
        ]);

        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'university_id' => $univ->id,
            'university' => $univ->name,
        ]);

        StudentProfile::create([
            'user_id' => $student->id,
            'university_id' => $univ->id,
            'universitas' => $univ->name,
            'nim' => '081911633001',
            'jurusan' => 'Sistem Informasi',
            'phone' => '08123456789',
        ]);

        return compact('dispusip', 'unit', 'univ', 'supervisor', 'admin', 'mentor', 'dosen', 'univUser', 'student');
    }

    public function test_supervisor_approve_future_start_date_transitions_to_accepted_and_creates_placement(): void
    {
        $eco = $this->setupDispusipEcosystem();

        $app = Application::create([
            'user_id' => $eco['student']->id,
            'unit_id' => $eco['unit']->id,
            'status' => ApplicationStatus::WAITING_APPROVAL,
            'start_date' => Carbon::tomorrow()->toDateString(),
            'end_date' => Carbon::tomorrow()->addMonths(3)->toDateString(),
        ]);

        // 1. Atasan meng-ACC pengajuan
        $response = $this->actingAs($eco['supervisor'])->post(route('supervisor.applications.approve', $app->id));
        $response->assertRedirect(route('supervisor.applications.index'));
        $response->assertSessionHas('success');

        $app->refresh();
        $this->assertEquals(ApplicationStatus::ACCEPTED->value, $app->statusValue());
        $this->assertNotNull($app->letter_number);
        $this->assertNotNull($app->placement);

        // 2. Mahasiswa melihat status Diterima di Dashboard
        $responseMhs = $this->actingAs($eco['student'])->get(route('dashboard'));
        $responseMhs->assertOk();
        $responseMhs->assertSee('Diterima');
        $responseMhs->assertSee('Pilih Dosen');

        // 3. Mahasiswa dapat memilih DPL kampus
        $responseDosen = $this->actingAs($eco['student'])->post(route('student.select_advisor'), [
            'academic_advisor_id' => $eco['dosen']->id,
        ]);
        $responseDosen->assertRedirect(route('dashboard'));
        $this->assertEquals($eco['dosen']->id, $app->fresh()->placement->academic_advisor_id);

        // 4. Mentor melihat mahasiswa di kategori Calon Peserta (Upcoming)
        $responseMentor = $this->actingAs($eco['mentor'])->get(route('mentor.dashboard'));
        $responseMentor->assertOk();
    }

    public function test_supervisor_approve_today_or_past_start_date_transitions_directly_to_active(): void
    {
        $eco = $this->setupDispusipEcosystem();

        $app = Application::create([
            'user_id' => $eco['student']->id,
            'unit_id' => $eco['unit']->id,
            'status' => ApplicationStatus::WAITING_APPROVAL,
            'start_date' => Carbon::today()->toDateString(),
            'end_date' => Carbon::today()->addMonths(3)->toDateString(),
        ]);

        $response = $this->actingAs($eco['supervisor'])->post(route('supervisor.applications.approve', $app->id));
        $response->assertRedirect(route('supervisor.applications.index'));

        $app->refresh();
        $this->assertEquals(ApplicationStatus::ACTIVE->value, $app->statusValue());
    }

    public function test_auto_activate_on_the_fly_when_start_date_arrives_on_dashboard(): void
    {
        $eco = $this->setupDispusipEcosystem();

        $app = Application::create([
            'user_id' => $eco['student']->id,
            'unit_id' => $eco['unit']->id,
            'status' => ApplicationStatus::ACCEPTED,
            'start_date' => Carbon::today()->toDateString(), // Hari mulai magang telah tiba
            'end_date' => Carbon::today()->addMonths(3)->toDateString(),
        ]);

        Placement::create([
            'application_id' => $app->id,
            'mentor_id' => $eco['mentor']->id,
            'pembimbing_id' => $eco['mentor']->id,
            'academic_advisor_id' => $eco['dosen']->id,
        ]);

        // Mahasiswa membuka dasbor di hari H
        $response = $this->actingAs($eco['student'])->get(route('dashboard'));
        $response->assertOk();

        // Status otomatis bertransisi ke ACTIVE
        $app->refresh();
        $this->assertEquals(ApplicationStatus::ACTIVE->value, $app->statusValue());
        $response->assertSee('Aktif Magang');
    }

    public function test_supervisor_revise_returns_status_to_pending_with_notes(): void
    {
        $eco = $this->setupDispusipEcosystem();

        $app = Application::create([
            'user_id' => $eco['student']->id,
            'unit_id' => $eco['unit']->id,
            'status' => ApplicationStatus::WAITING_APPROVAL,
            'start_date' => Carbon::tomorrow()->toDateString(),
            'end_date' => Carbon::tomorrow()->addMonths(3)->toDateString(),
        ]);

        $response = $this->actingAs($eco['supervisor'])->post(route('supervisor.applications.revise', $app->id), [
            'notes' => 'Mohon sesuaikan periode magang dengan kalender operasional dinas.',
        ]);

        $response->assertRedirect(route('supervisor.applications.index'));

        $app->refresh();
        $this->assertEquals(ApplicationStatus::PENDING->value, $app->statusValue());
        $this->assertStringContainsString('Catatan Penolakan Atasan', $app->rejection_note);
    }

    public function test_sync_internship_status_artisan_command_activates_accepted_students(): void
    {
        $eco = $this->setupDispusipEcosystem();

        $app = Application::create([
            'user_id' => $eco['student']->id,
            'unit_id' => $eco['unit']->id,
            'status' => ApplicationStatus::ACCEPTED,
            'start_date' => Carbon::yesterday()->toDateString(),
            'end_date' => Carbon::today()->addMonths(3)->toDateString(),
        ]);

        Placement::create([
            'application_id' => $app->id,
            'mentor_id' => $eco['mentor']->id,
        ]);

        $this->artisan('app:sync-internship-status')
            ->assertExitCode(0);

        $app->refresh();
        $this->assertEquals(ApplicationStatus::ACTIVE->value, $app->statusValue());
    }

    public function test_mentor_dashboard_tab_filtering_preserves_overall_stat_counts(): void
    {
        $eco = $this->setupDispusipEcosystem();

        $student2 = User::factory()->create(['role' => 'mahasiswa']);
        $appActive = Application::create([
            'user_id' => $eco['student']->id,
            'unit_id' => $eco['unit']->id,
            'status' => ApplicationStatus::ACTIVE,
            'start_date' => Carbon::yesterday()->toDateString(),
            'end_date' => Carbon::today()->addMonths(3)->toDateString(),
        ]);
        Placement::create(['application_id' => $appActive->id, 'mentor_id' => $eco['mentor']->id]);

        $appUpcoming = Application::create([
            'user_id' => $student2->id,
            'unit_id' => $eco['unit']->id,
            'status' => ApplicationStatus::ACCEPTED,
            'start_date' => Carbon::tomorrow()->toDateString(),
            'end_date' => Carbon::tomorrow()->addMonths(3)->toDateString(),
        ]);
        Placement::create(['application_id' => $appUpcoming->id, 'mentor_id' => $eco['mentor']->id]);

        // Buka tab active
        $response = $this->actingAs($eco['mentor'])->get(route('mentor.dashboard', ['tab' => 'active']));
        $response->assertOk();

        // Pastikan stats total_students tetap 2, active_students = 1, upcoming_students = 1
        $stats = $response->viewData('stats');
        $this->assertEquals(2, $stats['total_students']);
        $this->assertEquals(1, $stats['active_students']);
        $this->assertEquals(1, $stats['upcoming_students']);
    }
}
