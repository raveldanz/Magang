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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LogbookCompletionRequirementTest extends TestCase
{
    use RefreshDatabase;

    private University $university;
    private AgencyProfile $agency;
    private Unit $unit;
    private User $superAdmin;
    private User $mentor;
    private User $dosen;
    private User $student;
    private Application $application;
    private Placement $placement;

    protected function setUp(): void
    {
        parent::setUp();

        $this->university = University::create([
            'name' => 'Universitas Airlangga',
            'code' => 'UNAIR',
            'evaluation_scheme' => 'dual_evaluation',
            'require_dpl' => true,
        ]);

        $this->agency = AgencyProfile::create([
            'agency_name' => 'Dinas Komunikasi dan Informatika Surabaya',
            'quota' => 10,
        ]);

        $this->unit = Unit::create([
            'agency_profile_id' => $this->agency->id,
            'name' => 'Divisi Aplikasi Informatika',
            'quota' => 5,
        ]);

        $this->superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'superadmin.logbook@test.local',
        ]);

        $this->mentor = User::factory()->create([
            'role' => 'mentor',
            'agency_profile_id' => $this->agency->id,
        ]);

        $this->dosen = User::factory()->create([
            'role' => 'dosen',
            'university_id' => $this->university->id,
        ]);

        $this->student = User::factory()->create([
            'role' => 'mahasiswa',
            'university_id' => $this->university->id,
            'email' => 'mhs.logbook@test.local',
        ]);

        $this->student->studentProfile()->create([
            'nim' => '123456789',
            'universitas' => $this->university->name,
            'jurusan' => 'Sistem Informasi',
            'phone' => '081234567890',
        ]);

        $this->application = Application::create([
            'user_id' => $this->student->id,
            'unit_id' => $this->unit->id,
            'status' => ApplicationStatus::ACTIVE,
            'start_date' => Carbon::now()->subMonths(2)->toDateString(),
            'end_date' => Carbon::now()->addMonth()->toDateString(),
        ]);

        $this->placement = Placement::create([
            'application_id' => $this->application->id,
            'mentor_id' => $this->mentor->id,
            'academic_advisor_id' => $this->dosen->id,
        ]);

        FinalReport::create([
            'placement_id' => $this->placement->id,
            'title' => 'Laporan Akhir Magang Kominfo',
            'file_path' => 'documents/final_reports/test_report.pdf',
            'status' => 'approved',
        ]);

        Evaluation::create([
            'placement_id' => $this->placement->id,
            'nilai_pembimbing' => 92,
            'nilai_disiplin' => 90,
            'nilai_kinerja' => 95,
            'nilai_laporan' => 91,
            'nilai_dosen' => 90,
            'nilai_akademik' => 90,
            'nilai_akhir' => 91,
            'grade' => 'A',
            'feedback' => 'Sangat berdedikasi',
        ]);
    }

    /**
     * 1. Mahasiswa yang belum mengisi logbook: can_complete bernilai FALSE
     */
    public function test_student_without_logbook_cannot_complete_internship(): void
    {
        $app = $this->application->fresh();

        $this->assertTrue($app->has_approved_report);
        $this->assertTrue($app->has_complete_evaluation);
        $this->assertFalse($app->has_filled_logbook);
        $this->assertFalse($app->can_complete);
    }

    /**
     * 2. syncCompletionStatus menolak menyelesaikan magang jika logbook kosong
     */
    public function test_sync_completion_status_fails_if_logbook_empty(): void
    {
        $this->assertFalse($this->placement->syncCompletionStatus());

        $this->application->refresh();
        $this->assertEquals(ApplicationStatus::ACTIVE, $this->application->status);
    }

    /**
     * 3. Admin diblokir jika mencoba meluluskan mahasiswa yang belum mengisi logbook
     */
    public function test_admin_cannot_mark_application_completed_if_logbook_empty(): void
    {
        $response = $this->actingAs($this->superAdmin)->put(
            route('admin.applications.updateStatus', $this->application->id),
            [
                'status' => 'completed',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Logbook aktivitas magang belum pernah diisi', session('error'));

        $this->application->refresh();
        $this->assertNotEquals(ApplicationStatus::COMPLETED, $this->application->status);
    }

    /**
     * 4. Mahasiswa tanpa logbook diblokir dari akses E-Sertifikat
     */
    public function test_student_without_logbook_cannot_access_certificate(): void
    {
        $response = $this->actingAs($this->student)->get(
            route('student.certificate.show', $this->placement->id)
        );

        // Status masih ACTIVE (karena belum lulus), menghasilkan 403
        $response->assertStatus(403);
    }

    /**
     * 5. Setelah mahasiswa mengisi logbook, kelulusan berhasil diproses
     */
    public function test_student_can_graduate_after_filling_logbook(): void
    {
        // Mahasiswa mengisi logbook kegiatan harian
        $logbook = Logbook::create([
            'placement_id' => $this->placement->id,
            'date' => Carbon::now()->subDays(5)->toDateString(),
            'activity' => 'Melakukan perancangan modul database dan backend Laravel',
            'status' => 'approved',
        ]);

        $app = $this->application->fresh();
        $this->assertTrue($app->has_filled_logbook);
        $this->assertTrue($app->can_complete);

        // syncCompletionStatus sekarang berhasil
        $this->assertTrue($this->placement->syncCompletionStatus());

        $app->refresh();
        $this->assertEquals(ApplicationStatus::COMPLETED, $app->status);

        // Mahasiswa yang sudah lulus dapat mengakses sertifikat
        $response = $this->actingAs($this->student)->get(
            route('student.certificate.show', $this->placement->id)
        );
        $response->assertOk();
    }

    /**
     * 6. Admin dapat meluluskan mahasiswa secara manual setelah logbook terisi
     */
    public function test_admin_can_mark_completed_after_logbook_filled(): void
    {
        Logbook::create([
            'placement_id' => $this->placement->id,
            'date' => Carbon::now()->subDays(2)->toDateString(),
            'activity' => 'Implementasi fitur verifikasi dan testing aplikasi',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->superAdmin)->put(
            route('admin.applications.updateStatus', $this->application->id),
            [
                'status' => 'completed',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->application->refresh();
        $this->assertEquals(ApplicationStatus::COMPLETED, $this->application->status);
    }
}
