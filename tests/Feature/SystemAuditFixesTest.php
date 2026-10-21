<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Logbook;
use App\Models\Placement;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemAuditFixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_delete_pending_logbook_but_not_approved_logbook()
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $unit = Unit::create(['name' => 'IT Dept', 'quota' => 5]);
        $app = Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'status' => 'active',
        ]);
        $placement = Placement::create(['application_id' => $app->id]);

        $pendingLog = Logbook::create([
            'placement_id' => $placement->id,
            'date' => now()->toDateString(),
            'activity' => 'Aktivitas hari ini di IT Dept',
            'status' => 'pending',
        ]);

        $approvedLog = Logbook::create([
            'placement_id' => $placement->id,
            'date' => now()->subDay()->toDateString(),
            'activity' => 'Aktivitas kemarin di IT Dept',
            'status' => 'approved',
        ]);

        // Student can delete pending logbook
        $response = $this->actingAs($student)->delete(route('student.logbook.destroy', $pendingLog->id));
        $response->assertRedirect(route('student.logbook.index'));
        $this->assertDatabaseMissing('logbooks', ['id' => $pendingLog->id]);

        // Student cannot delete approved logbook
        $responseApproved = $this->actingAs($student)->delete(route('student.logbook.destroy', $approvedLog->id));
        $responseApproved->assertRedirect(route('student.logbook.index'));
        $this->assertDatabaseHas('logbooks', ['id' => $approvedLog->id]);
    }

    public function test_active_intern_can_select_advisor_without_404()
    {
        $univ = University::create(['name' => 'Universitas Negeri Surabaya', 'code' => 'UNESA']);
        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'university_id' => $univ->id,
        ]);
        $dosen = User::factory()->create([
            'role' => 'dosen',
            'university_id' => $univ->id,
        ]);

        $unit = Unit::create(['name' => 'Dinas Kominfo Divisi IT', 'quota' => 5]);
        $app = Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'status' => 'active',
        ]);
        $placement = Placement::create(['application_id' => $app->id]);

        $response = $this->actingAs($student)->post(route('student.select_advisor'), [
            'academic_advisor_id' => $dosen->id,
        ]);

        $response->assertRedirect(route('dashboard'));
        $placement->refresh();
        $this->assertEquals($dosen->id, $placement->academic_advisor_id);
    }

    public function test_unit_and_agency_remaining_quota_counts_active_and_frees_completed()
    {
        $agency = AgencyProfile::create(['agency_name' => 'Dinas Kominfo']);
        $unit = Unit::create([
            'agency_profile_id' => $agency->id,
            'name' => 'Divisi Jaringan',
            'quota' => 2,
        ]);

        $student1 = User::factory()->create(['role' => 'mahasiswa']);
        $student2 = User::factory()->create(['role' => 'mahasiswa']);

        // Student 1: active (currently occupying slot)
        Application::create([
            'user_id' => $student1->id,
            'unit_id' => $unit->id,
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => 'active',
        ]);

        // Student 2: completed (graduated, freed up slot)
        Application::create([
            'user_id' => $student2->id,
            'unit_id' => $unit->id,
            'start_date' => now()->subMonths(4)->toDateString(),
            'end_date' => now()->subMonth()->toDateString(),
            'status' => 'completed',
        ]);

        // Unit quota = 2. Active = 1. Remaining should be 1.
        $this->assertEquals(1, $unit->fresh()->remaining_quota);
        $this->assertEquals(1, $agency->fresh()->remaining_quota);
    }

    public function test_student_can_open_final_report_page_right_after_applying()
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $unit = Unit::create(['name' => 'Divisi Kominfo', 'quota' => 5]);

        // Student just applied (status = pending, no placement yet)
        Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($student)->get(route('student.final_report.index'));
        $response->assertStatus(200);
        $response->assertSee('Pengajuan Magang Sedang Diverifikasi');
    }

    public function test_mentor_index_can_filter_by_bidang_unit_id_and_search()
    {
        $agency = AgencyProfile::create(['agency_name' => 'Dinas Perpustakaan dan Kearsipan Kota Surabaya']);
        $unitA = Unit::create(['agency_profile_id' => $agency->id, 'name' => 'Bidang Sekretariat', 'quota' => 10]);
        $unitB = Unit::create(['agency_profile_id' => $agency->id, 'name' => 'Bidang Layanan Kearsipan', 'quota' => 10]);

        $admin = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $agency->id]);

        $mentorA = User::factory()->create([
            'name' => 'Budi Mentor Perpus',
            'email' => 'budi.mentor@dispusip.go.id',
            'role' => 'mentor',
            'agency_profile_id' => $agency->id,
            'unit_id' => $unitA->id,
        ]);

        $mentorB = User::factory()->create([
            'name' => 'Siti Mentor Arsip',
            'email' => 'siti.mentor@dispusip.go.id',
            'role' => 'mentor',
            'agency_profile_id' => $agency->id,
            'unit_id' => $unitB->id,
        ]);

        // Filter by unitA
        $responseA = $this->actingAs($admin)->get(route('admin.mentors.index', ['unit_id' => $unitA->id]));
        $responseA->assertOk();
        $responseA->assertSee($mentorA->name);
        $responseA->assertDontSee($mentorB->name);

        // Filter by search
        $responseSearch = $this->actingAs($admin)->get(route('admin.mentors.index', ['search' => 'Siti']));
        $responseSearch->assertOk();
        $responseSearch->assertSee($mentorB->name);
        $responseSearch->assertDontSee($mentorA->name);
    }

    public function test_feedback_store_falls_back_to_primary_agency_when_target_role_is_admin_dinas()
    {
        $agency = AgencyProfile::first() ?? AgencyProfile::create(['agency_name' => 'Dinas Perpustakaan dan Kearsipan Kota Surabaya']);
        $student = User::factory()->create(['role' => 'mahasiswa']);

        $response = $this->actingAs($student)->post(route('feedbacks.store'), [
            'category' => 'saran_fitur',
            'subject' => 'Saran Layanan Preservasi',
            'message' => 'Mohon ditambah fasilitas digitalisasi arsip kuno.',
            'target_role' => 'admin_dinas',
            // target_agency_id is intentionally omitted
        ]);

        $response->assertRedirect(route('feedbacks.my'));
        $this->assertDatabaseHas('system_feedbacks', [
            'subject' => 'Saran Layanan Preservasi',
            'target_role' => 'admin_dinas',
            'target_agency_id' => $agency->id,
        ]);
    }
}
