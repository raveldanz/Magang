<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\ReviewStatus;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Evaluation;
use App\Models\FinalReport;
use App\Models\Placement;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ApplicationStatusArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_status_enum_values_and_helpers()
    {
        $this->assertEquals('pending', ApplicationStatus::PENDING->value);
        $this->assertEquals('verified', ApplicationStatus::VERIFIED->value);
        $this->assertEquals('accepted', ApplicationStatus::ACCEPTED->value);
        $this->assertEquals('active', ApplicationStatus::ACTIVE->value);
        $this->assertEquals('completed', ApplicationStatus::COMPLETED->value);
        $this->assertEquals('rejected', ApplicationStatus::REJECTED->value);
        $this->assertEquals('resigned', ApplicationStatus::RESIGNED->value);

        $this->assertTrue(ApplicationStatus::ACTIVE->isOngoing());
        $this->assertFalse(ApplicationStatus::PENDING->isOngoing());
        $this->assertTrue(ApplicationStatus::ACTIVE->canLogbook());
        $this->assertFalse(ApplicationStatus::ACCEPTED->canLogbook());

        $this->assertNotEmpty(ApplicationStatus::ACTIVE->label());
        $this->assertNotEmpty(ApplicationStatus::ACTIVE->badgeColor());
    }

    public function test_review_status_enum_values()
    {
        $this->assertEquals('pending', ReviewStatus::PENDING->value);
        $this->assertEquals('approved', ReviewStatus::APPROVED->value);
        $this->assertEquals('rejected', ReviewStatus::REJECTED->value);
        $this->assertEquals('revision', ReviewStatus::REVISION->value);

        $this->assertEquals('Disetujui', ReviewStatus::APPROVED->label());
    }

    protected function createUnit(): Unit
    {
        $agency = AgencyProfile::create([
            'government_name' => 'Pemerintah Kota Surabaya',
            'agency_name' => 'Dinas Komunikasi dan Informatika',
            'email' => 'diskominfo@surabaya.go.id',
            'address' => 'Jl. Jimerto',
        ]);

        return Unit::create([
            'agency_profile_id' => $agency->id,
            'name' => 'Testing Unit',
            'quota' => 5,
        ]);
    }

    public function test_application_casts_status_to_enum()
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $unit = $this->createUnit();

        $app = Application::create([
            'user_id' => $user->id,
            'unit_id' => $unit->id,
            'status' => ApplicationStatus::ACCEPTED,
            'start_date' => Carbon::now()->addDays(5)->toDateString(),
            'end_date' => Carbon::now()->addDays(60)->toDateString(),
        ]);

        $app->refresh();
        $this->assertInstanceOf(ApplicationStatus::class, $app->status);
        $this->assertEquals(ApplicationStatus::ACCEPTED, $app->status);
        $this->assertFalse($app->is_active_internship);
        $this->assertFalse($app->is_eligible_for_logbook);

        // Update to ACTIVE
        $app->update(['status' => ApplicationStatus::ACTIVE]);
        $app->refresh();
        $this->assertTrue($app->is_active_internship);
        $this->assertTrue($app->is_eligible_for_logbook);
    }

    public function test_sync_internship_status_artisan_command()
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $unit = $this->createUnit();

        // Application with start_date <= today and accepted status
        $app = Application::create([
            'user_id' => $user->id,
            'unit_id' => $unit->id,
            'status' => 'accepted',
            'start_date' => Carbon::now()->subDays(2)->toDateString(),
            'end_date' => Carbon::now()->addDays(30)->toDateString(),
        ]);

        $this->artisan('app:sync-internship-status')
            ->assertExitCode(0);

        $app->refresh();
        $this->assertEquals(ApplicationStatus::ACTIVE, $app->status);
    }

    public function test_placement_sync_completion_status()
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $mentor = User::factory()->create(['role' => 'mentor']);
        $unit = $this->createUnit();

        $app = Application::create([
            'user_id' => $user->id,
            'unit_id' => $unit->id,
            'status' => ApplicationStatus::ACTIVE,
            'start_date' => Carbon::now()->subDays(30)->toDateString(),
            'end_date' => Carbon::now()->subDays(1)->toDateString(),
        ]);

        $placement = Placement::create([
            'application_id' => $app->id,
            'mentor_id' => $mentor->id,
        ]);

        $finalReport = FinalReport::create([
            'placement_id' => $placement->id,
            'title' => 'Laporan Akhir Pengujian',
            'file_path' => 'documents/final_reports/test.pdf',
            'status' => 'approved',
        ]);

        $evaluation = Evaluation::create([
            'placement_id' => $placement->id,
            'nilai_pembimbing' => 90,
            'nilai_disiplin' => 90,
            'nilai_kinerja' => 90,
            'nilai_laporan' => 90,
            'nilai_dosen' => 90,
            'nilai_akademik' => 90,
            'nilai_akhir' => 90,
            'grade' => 'A',
            'feedback' => 'Sangat baik',
        ]);

        $synced = $placement->syncCompletionStatus();
        $this->assertTrue($synced);

        $app->refresh();
        $this->assertEquals(ApplicationStatus::COMPLETED, $app->status);
    }

    public function test_student_dashboard_renders_without_type_error_for_all_application_statuses()
    {
        $agency = AgencyProfile::create(['agency_name' => 'Dinas Pendidikan', 'quota' => 10]);
        $unit = Unit::create(['name' => 'IT Support', 'agency_profile_id' => $agency->id, 'quota' => 5]);
        $student = User::factory()->create(['role' => 'mahasiswa', 'email' => 'student.dash@test.com']);

        foreach (ApplicationStatus::cases() as $status) {
            $app = Application::create([
                'user_id' => $student->id,
                'unit_id' => $unit->id,
                'status' => $status,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(3),
            ]);

            $response = $this->actingAs($student)->get(route('dashboard'));
            $response->assertStatus(200);

            $app->delete();
        }
    }

    public function test_admin_application_show_renders_with_seven_status_options_and_no_type_error()
    {
        $admin = User::factory()->create(['role' => 'admin', 'email' => 'admin.verify@test.com']);
        $agency = AgencyProfile::create(['agency_name' => 'Dinas Perhubungan', 'quota' => 10]);
        $unit = Unit::create(['name' => 'Sekretariat', 'agency_profile_id' => $agency->id, 'quota' => 5]);
        $student = User::factory()->create(['role' => 'mahasiswa', 'email' => 'student.verify@test.com']);

        $app = Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'status' => ApplicationStatus::VERIFIED,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonths(3),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.applications.show', $app->id));
        $response->assertStatus(200);
        $response->assertSee('id="status-select"', false);
        $response->assertSee('value="pending"', false);
        $response->assertSee('value="verified"', false);
        $response->assertSee('value="accepted"', false);
        $response->assertSee('value="active"', false);
        $response->assertSee('value="completed"', false);
        $response->assertSee('value="rejected"', false);
        $response->assertSee('value="resigned"', false);
        $response->assertSee('id="acceptance-box"', false);
    }

    public function test_student_application_create_renders_with_enum_history()
    {
        $agency = AgencyProfile::create(['agency_name' => 'Dinas Kominfo', 'quota' => 10]);
        $unit = Unit::create(['name' => 'Aplikasi', 'agency_profile_id' => $agency->id, 'quota' => 5]);
        $student = User::factory()->create(['role' => 'mahasiswa', 'email' => 'student.hist@test.com']);
        \App\Models\StudentProfile::create([
            'user_id' => $student->id,
            'nim' => '1234567890',
            'universitas' => 'Universitas Negeri Surabaya',
            'jurusan' => 'Teknik Informatika',
            'phone' => '081234567890',
        ]);

        Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'status' => ApplicationStatus::REJECTED,
            'start_date' => Carbon::now()->subMonths(6),
            'end_date' => Carbon::now()->subMonths(3),
            'rejection_note' => 'Kuota divisi penuh',
        ]);

        $response = $this->actingAs($student)->get(route('student.application.create'));
        $response->assertStatus(200);
        $response->assertSee('REJECTED');
        $response->assertSee('Kuota divisi penuh');
    }
}
