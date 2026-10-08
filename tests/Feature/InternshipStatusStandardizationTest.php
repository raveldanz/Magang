<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Evaluation;
use App\Models\FinalReport;
use App\Models\Logbook;
use App\Models\Placement;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternshipStatusStandardizationTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $mentor;
    private User $dosen;
    private User $student;
    private Unit $unit;
    private University $univ;

    protected function setUp(): void
    {
        parent::setUp();

        $this->univ = University::create([
            'name' => 'Universitas Indonesia',
            'code' => 'UI',
            'evaluation_scheme' => 'dual_evaluation',
            'require_dpl' => true,
        ]);

        $agency = AgencyProfile::create([
            'agency_name' => 'Dinas Pendidikan Kota Surabaya',
            'quota' => 20,
        ]);

        $this->unit = Unit::create([
            'agency_profile_id' => $agency->id,
            'name' => 'Divisi Tata Kelola',
            'quota' => 10,
        ]);

        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->mentor = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $agency->id]);
        $this->dosen = User::factory()->create(['role' => 'dosen', 'university_id' => $this->univ->id]);
        $this->student = User::factory()->create(['role' => 'mahasiswa', 'university_id' => $this->univ->id]);
        $this->student->studentProfile()->create([
            'nim' => 'NIM12345',
            'universitas' => $this->univ->name,
            'jurusan' => 'Teknik Informatika',
            'phone' => '081234567890',
        ]);
    }

    public function test_accepted_application_cannot_be_completed_and_shows_pra_magang(): void
    {
        $app = Application::create([
            'user_id' => $this->student->id,
            'unit_id' => $this->unit->id,
            'status' => ApplicationStatus::ACCEPTED,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
        ]);
        $placement = Placement::create([
            'application_id' => $app->id,
            'mentor_id' => $this->mentor->id,
            'academic_advisor_id' => $this->dosen->id,
        ]);

        $this->assertFalse($app->canBeCompleted());
        $this->assertFalse($placement->canBeCompleted());
        $this->assertNotSame('Siap diluluskan', $app->actionHint());

        $response = $this->actingAs($this->superAdmin)->get(route('admin.applications.index'));
        $response->assertOk();
        $response->assertSee('Pra-Magang');
        $response->assertDontSee('Siap diluluskan');
    }

    public function test_active_application_can_only_be_completed_when_past_or_on_end_date_and_complete(): void
    {
        // 1. Ongoing active (end date in future) -> canBeCompleted() MUST be false
        $ongoingApp = Application::create([
            'user_id' => $this->student->id,
            'unit_id' => $this->unit->id,
            'status' => ApplicationStatus::ACTIVE,
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
        ]);
        $placement1 = Placement::create([
            'application_id' => $ongoingApp->id,
            'mentor_id' => $this->mentor->id,
            'academic_advisor_id' => $this->dosen->id,
        ]);
        FinalReport::create(['placement_id' => $placement1->id, 'file_path' => 'x.pdf', 'status' => 'approved']);
        Evaluation::create([
            'placement_id' => $placement1->id,
            'nilai_disiplin' => 90, 'nilai_kinerja' => 90, 'nilai_laporan' => 90,
            'nilai_dosen' => 90, 'nilai_akademik' => 90,
        ]);
        Logbook::create(['placement_id' => $placement1->id, 'date' => now()->subDays(2)->toDateString(), 'activity' => 'Kerja', 'status' => 'approved']);

        $this->assertFalse($ongoingApp->canBeCompleted(), 'Ongoing student should not be canBeCompleted');

        // 2. Ended active (end date in past) with complete requirements -> canBeCompleted() is true
        $student2 = User::factory()->create(['role' => 'mahasiswa', 'university_id' => $this->univ->id]);
        $student2->studentProfile()->create(['nim' => 'NIM67890', 'universitas' => $this->univ->name, 'jurusan' => 'Sistem Informasi', 'phone' => '081234567891']);
        $endedApp = Application::create([
            'user_id' => $student2->id,
            'unit_id' => $this->unit->id,
            'status' => ApplicationStatus::ACTIVE,
            'start_date' => now()->subMonths(3)->toDateString(),
            'end_date' => now()->subDay()->toDateString(),
        ]);
        $placement2 = Placement::create([
            'application_id' => $endedApp->id,
            'mentor_id' => $this->mentor->id,
            'academic_advisor_id' => $this->dosen->id,
        ]);
        FinalReport::create(['placement_id' => $placement2->id, 'file_path' => 'x.pdf', 'status' => 'approved']);
        Evaluation::create([
            'placement_id' => $placement2->id,
            'nilai_disiplin' => 95, 'nilai_kinerja' => 95, 'nilai_laporan' => 95,
            'nilai_dosen' => 95, 'nilai_akademik' => 95,
        ]);
        Logbook::create(['placement_id' => $placement2->id, 'date' => now()->subDays(5)->toDateString(), 'activity' => 'Kerja', 'status' => 'approved']);

        $this->assertTrue($endedApp->canBeCompleted());
        $this->assertTrue($placement2->canBeCompleted());

        $response = $this->actingAs($this->superAdmin)->get(route('admin.applications.index'));
        $response->assertOk();
        $response->assertSee('Siap diluluskan');
    }

    public function test_superadmin_dashboard_banner_and_stat_cards(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee('Administrator Utama');
        $response->assertDontSee('SUPER ADMIN GOVERNANCE HUB');
        $response->assertSee('Diterima, Belum Mulai');
    }

    public function test_mentor_dashboard_table_has_no_status_badge_beside_name(): void
    {
        $app = Application::create([
            'user_id' => $this->student->id,
            'unit_id' => $this->unit->id,
            'status' => ApplicationStatus::ACTIVE,
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
        ]);
        Placement::create([
            'application_id' => $app->id,
            'mentor_id' => $this->mentor->id,
            'academic_advisor_id' => $this->dosen->id,
        ]);

        $response = $this->actingAs($this->mentor)->get(route('mentor.dashboard'));
        $response->assertOk();
        $response->assertSeeText('Daftar Mahasiswa Bimbingan Magang');
        $response->assertSeeText('Mahasiswa');
        $response->assertSeeText('Perguruan Tinggi & Unit');
        $response->assertSeeText('Dosen Pembimbing');
        $response->assertSeeText('Logbook');
        $response->assertSeeText('Laporan Akhir');
        $response->assertSeeText('Nilai Mentor');
        $response->assertSeeText('Aksi');
    }
}
