<?php

namespace Tests\Feature;

use App\Jobs\QueueHeartbeat;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\FinalReport;
use App\Models\Placement;
use App\Models\StudentProfile;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use App\Services\SystemHealth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Paket 4: heartbeat scheduler/queue, agregasi dasbor tanpa N+1, partial view, & QR lokal.
 */
class Phase4OperationalTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_health_detects_missing_scheduler_and_queue(): void
    {
        $health = app(SystemHealth::class);
        $this->assertNotEmpty($health->status()['issues']);

        SystemHealth::recordSchedulerHeartbeat();
        (new QueueHeartbeat)->handle();
        $this->assertSame([], $health->status()['issues']);

        Cache::forever(SystemHealth::QUEUE_KEY, now()->subHour()->toIso8601String());
        $this->assertStringContainsString('Queue worker', implode(' ', $health->status()['issues']));

        $this->artisan('app:health')->assertFailed();
    }

    public function test_super_admin_dashboard_shows_health_warning_only_when_needed(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $agencyAdmin = User::factory()->create(['role' => 'admin', 'agency_profile_id' => AgencyProfile::create(['agency_name' => 'Dinas A'])->id]);

        $this->actingAs($superAdmin)->get(route('admin.dashboard'))->assertOk()->assertSee('Proses latar belakang server perlu diperiksa');
        $this->actingAs($agencyAdmin)->get(route('admin.dashboard'))->assertOk()->assertDontSee('Proses latar belakang server perlu diperiksa');

        SystemHealth::recordSchedulerHeartbeat();
        (new QueueHeartbeat)->handle();
        $this->actingAs($superAdmin)->get(route('admin.dashboard'))->assertOk()->assertDontSee('Proses latar belakang server perlu diperiksa');
    }

    public function test_dashboard_and_mentor_list_use_aggregated_queries(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $univ = University::create(['name' => 'Universitas Uji']);
        $mentorIds = [];

        foreach (range(1, 6) as $i) {
            $agency = AgencyProfile::create(['agency_name' => "Dinas {$i}"]);
            $unit = Unit::create(['agency_profile_id' => $agency->id, 'name' => "Bidang {$i}", 'quota' => 5]);
            $mentor = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $agency->id]);
            $mentorIds[] = $mentor->id;
            $student = User::factory()->create(['role' => 'mahasiswa', 'university_id' => $univ->id]);
            $app = Application::create(['user_id' => $student->id, 'unit_id' => $unit->id, 'status' => 'active', 'start_date' => now()->subDays(5), 'end_date' => now()->addDays(30)]);
            $placement = Placement::create(['application_id' => $app->id, 'mentor_id' => $mentor->id, 'pembimbing_id' => $mentor->id]);
            if ($i % 2 === 0) {
                FinalReport::create(['placement_id' => $placement->id, 'file_path' => 'x.pdf', 'status' => 'approved']);
            }
        }

        DB::enableQueryLog();
        $this->actingAs($superAdmin)->get(route('admin.dashboard'))->assertOk()->assertSee('Universitas Uji');
        $dashboardQueries = count(DB::getQueryLog());
        DB::flushQueryLog();

        $response = $this->actingAs($superAdmin)->get(route('admin.mentors.index'))->assertOk();
        $mentorQueries = count(DB::getQueryLog());

        // Jumlah query tidak lagi bertambah per instansi/kampus/mentor
        $this->assertLessThan(60, $dashboardQueries, "Dasbor menjalankan {$dashboardQueries} query");
        $this->assertLessThan(40, $mentorQueries, "Daftar mentor menjalankan {$mentorQueries} query");

        $mentors = $response->viewData('mentors');
        $this->assertSame(6, $mentors->sum('active_students_count'));
        $this->assertSame(3, $mentors->sum('completed_students_count'));
    }

    public function test_split_pages_render(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $univ = University::create(['name' => 'Universitas Uji', 'code' => 'UU']);
        $student = User::factory()->create(['role' => 'mahasiswa', 'university_id' => $univ->id]);
        StudentProfile::create(['user_id' => $student->id, 'nim' => '1', 'universitas' => $univ->name, 'university_id' => $univ->id, 'jurusan' => 'TI', 'phone' => '08']);

        foreach (['dosen', 'mahasiswa', 'admin', 'policy'] as $tab) {
            $this->actingAs($superAdmin)->get(route('admin.universities.show', ['university' => $univ->id, 'tab' => $tab]))
                ->assertOk()->assertSee('Pusat Kendali Perguruan Tinggi');
        }
        $this->actingAs($superAdmin)->get(route('admin.universities.show', ['university' => $univ->id, 'student_status' => 'no_application', 'student_search' => 'x']))->assertOk();

        $this->actingAs($student)->get(route('dashboard'))->assertOk();
    }

    public function test_no_external_qr_service_is_referenced(): void
    {
        $offenders = [];
        foreach (['app', 'resources/views'] as $dir) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(base_path($dir))) as $file) {
                if ($file->isFile() && str_contains(file_get_contents($file->getPathname()), 'api.qrserver.com')) {
                    $offenders[] = $file->getPathname();
                }
            }
        }

        $this->assertSame([], $offenders);
    }
}
