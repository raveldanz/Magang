<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\FinalReport;
use App\Models\Placement;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\UniversityHubService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemAuditComprehensiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_university_hub_stats_remain_macro_when_table_is_filtered()
    {
        $univ = University::create([
            'name' => 'Universitas Indonesia',
            'code' => 'UI',
            'require_dpl' => true,
        ]);

        $unit = Unit::create(['name' => 'Divisi Siber', 'quota' => 10]);

        // Student 1: active
        $student1 = User::factory()->create([
            'role' => 'mahasiswa',
            'university_id' => $univ->id,
            'name' => 'Mahasiswa Aktif',
        ]);
        $app1 = Application::create([
            'user_id' => $student1->id,
            'unit_id' => $unit->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(2)->toDateString(),
            'status' => 'active',
        ]);
        Placement::create(['application_id' => $app1->id]);

        // Student 2: completed
        $student2 = User::factory()->create([
            'role' => 'mahasiswa',
            'university_id' => $univ->id,
            'name' => 'Mahasiswa Selesai',
        ]);
        $app2 = Application::create([
            'user_id' => $student2->id,
            'unit_id' => $unit->id,
            'start_date' => now()->subMonths(3)->toDateString(),
            'end_date' => now()->subMonth()->toDateString(),
            'status' => 'completed',
        ]);
        Placement::create(['application_id' => $app2->id]);

        // Hub service without filter
        [$dosens, $studentsAll, $statsAll] = app(UniversityHubService::class)->build($univ);
        $this->assertEquals(2, $statsAll['total_students']);
        $this->assertEquals(1, $statsAll['active_interns']);
        $this->assertEquals(1, $statsAll['completed_interns']);
        $this->assertCount(2, $studentsAll);

        // Hub service with filter 'completed'
        [$dosens, $studentsFiltered, $statsFiltered] = app(UniversityHubService::class)->build($univ, 'completed');
        // Filtered students should contain only student2
        $this->assertCount(1, $studentsFiltered);
        $this->assertEquals($student2->id, $studentsFiltered->first()->id);

        // BUT macro stats should STILL reflect full university statistics!
        $this->assertEquals(2, $statsFiltered['total_students']);
        $this->assertEquals(1, $statsFiltered['active_interns']);
        $this->assertEquals(1, $statsFiltered['completed_interns']);
    }

    public function test_student_dashboard_renders_without_warnings_when_no_application_exists()
    {
        $univ = University::create(['name' => 'Universitas Airlangga', 'code' => 'UNAIR']);
        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'university_id' => $univ->id,
        ]);

        $response = $this->actingAs($student)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Registrasi Akun');
        $response->assertSee('Selamat Datang, '.$student->name);
    }

    public function test_notification_service_skips_urgent_dpl_prompt_for_mentor_only_university()
    {
        $univ = University::create([
            'name' => 'Politeknik Elektronika Negeri Surabaya',
            'code' => 'PENS',
            'evaluation_scheme' => 'mentor_only',
            'require_dpl' => false,
        ]);

        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'university_id' => $univ->id,
        ]);

        $unit = Unit::create(['name' => 'Divisi Jaringan', 'quota' => 5]);
        $app = Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'status' => 'active',
        ]);
        // Placement without academic advisor
        Placement::create(['application_id' => $app->id]);

        $service = app(NotificationService::class);
        $notifications = $service->getNotificationsForUser($student);

        // Student should NOT receive the urgent 'student_need_dpl' notification
        $ids = collect($notifications)->pluck('id')->all();
        $this->assertNotContains('student_need_dpl', $ids);
        $this->assertContains('student_ready', $ids);
    }

    public function test_unit_occupied_count_accessor_handles_preloaded_accepted_count()
    {
        $unit = new Unit(['quota' => 10]);
        $unit->setRawAttributes([
            'id' => 999,
            'name' => 'Testing Unit',
            'quota' => 10,
            'accepted_count' => 3,
        ]);

        $this->assertEquals(3, $unit->occupied_count);
        $this->assertEquals(3, $unit->accepted_count);
        $this->assertEquals(7, $unit->remaining_quota);
    }

    public function test_mentor_can_approve_final_report_via_type_safe_authorization()
    {
        $mentor = User::factory()->create(['role' => 'mentor']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $unit = Unit::create(['name' => 'Divisi Kominfo', 'quota' => 5]);

        $app = Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'status' => 'active',
        ]);

        $placement = Placement::create([
            'application_id' => $app->id,
            'mentor_id' => $mentor->id,
        ]);

        $report = FinalReport::create([
            'placement_id' => $placement->id,
            'file_path' => 'reports/test.pdf',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($mentor)->patch(route('mentor.final_report.updateStatus', $report->id), [
            'status' => 'approved',
            'feedback' => 'Laporan sangat memuaskan dan memenuhi standar.',
        ]);

        $response->assertRedirect();
        $report->refresh();
        $this->assertEquals('approved', $report->status);
    }
}
