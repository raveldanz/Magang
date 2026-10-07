<?php

namespace Tests\Feature;

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
use Tests\TestCase;

class LecturerInnovationsTest extends TestCase
{
    use RefreshDatabase;

    private User $dosen;
    private User $student;
    private Placement $placement;
    private Application $application;

    protected function setUp(): void
    {
        parent::setUp();

        $univ = University::create(['name' => 'Universitas Negeri Surabaya', 'code' => 'UNESA']);
        $agency = AgencyProfile::create(['agency_name' => 'Dinas Komunikasi dan Informatika']);
        $unit = Unit::create(['agency_profile_id' => $agency->id, 'name' => 'Bidang Aplikasi Informatika', 'quota' => 5]);

        $this->dosen = User::factory()->create([
            'role' => 'dosen',
            'university_id' => $univ->id,
            'name' => 'Dr. Budi Santoso, M.Kom.',
        ]);

        $this->student = User::factory()->create([
            'role' => 'mahasiswa',
            'university_id' => $univ->id,
            'name' => 'Ahmad Fajar',
        ]);
        $this->student->studentProfile()->create([
            'nim' => '22081010199',
            'universitas' => $univ->name,
            'jurusan' => 'Teknik Informatika',
            'phone' => '081234567890',
        ]);

        $this->application = Application::create([
            'user_id' => $this->student->id,
            'unit_id' => $unit->id,
            'status' => 'active',
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
        ]);

        $this->placement = Placement::create([
            'application_id' => $this->application->id,
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'academic_advisor_id' => $this->dosen->id,
        ]);
    }

    public function test_dpl_can_access_dashboard_with_smart_action_alerts(): void
    {
        // 1 unreviewed logbook
        Logbook::create([
            'placement_id' => $this->placement->id,
            'date' => now()->subDays(2)->toDateString(),
            'activity' => 'Mengerjakan modul autentikasi',
            'status' => 'pending',
            'lecturer_status' => 'pending',
        ]);

        // 1 pending report
        FinalReport::create([
            'placement_id' => $this->placement->id,
            'file_path' => 'reports/test.pdf',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->dosen)->get(route('lecturer.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Smart Action Center');
        $response->assertSee('Naskah Laporan Akhir');
        $response->assertSee('Logbook Harian');
    }

    public function test_dpl_can_view_dual_status_badges_on_dashboard(): void
    {
        Evaluation::create([
            'placement_id' => $this->placement->id,
            'nilai_disiplin' => 88,
            'nilai_kinerja' => 88,
            'nilai_laporan' => 88,
            'nilai_dosen' => 92.0,
            'score_mastery' => 92.0,
            'score_report' => 92.0,
            'score_attitude' => 92.0,
            'feedback_dosen' => 'Sangat memuaskan.',
        ]);

        $response = $this->actingAs($this->dosen)->get(route('lecturer.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('88.0');
        $response->assertSee('92.0');
        $response->assertSee('BAP');
    }

    public function test_dpl_can_view_official_grade_sheet_bap(): void
    {
        Evaluation::create([
            'placement_id' => $this->placement->id,
            'nilai_disiplin' => 85,
            'nilai_kinerja' => 85,
            'nilai_laporan' => 85,
            'nilai_dosen' => 90.0,
            'score_mastery' => 90.0,
            'score_report' => 90.0,
            'score_attitude' => 90.0,
            'feedback_dosen' => 'Kinerja luar biasa.',
        ]);

        $response = $this->actingAs($this->dosen)->get(route('lecturer.students.grade_sheet', $this->placement->id));
        $response->assertStatus(200);
        $response->assertSee('BERITA ACARA', false);
        $response->assertSee('DOSEN PEMBIMBING LAPANGAN', false);
        $response->assertSee('Ahmad Fajar');
        $response->assertSee('22081010199');
        $response->assertSee('Dr. Budi Santoso, M.Kom.');
        $response->assertSee('Cetak / Simpan PDF', false);
    }

    public function test_unauthorized_dpl_cannot_view_other_dpl_grade_sheet(): void
    {
        $otherDosen = User::factory()->create([
            'role' => 'dosen',
            'name' => 'Dosen Lain, M.T.',
        ]);

        $response = $this->actingAs($otherDosen)->get(route('lecturer.students.grade_sheet', $this->placement->id));
        $response->assertStatus(403);
    }

    public function test_dpl_can_add_and_delete_academic_consultation_session(): void
    {
        // Add consultation
        $postResponse = $this->actingAs($this->dosen)->post(
            route('lecturer.consultations.store', $this->placement->id),
            [
                'consultation_date' => now()->toDateString(),
                'stage' => 'bimbingan_laporan',
                'topic' => 'Revisi Laporan Akhir BAB II',
                'notes' => 'Revisi BAB II Metodologi Penelitian dan penambahan referensi jurnal.',
            ]
        );

        $postResponse->assertRedirect();
        $this->assertDatabaseHas('academic_consultations', [
            'placement_id' => $this->placement->id,
            'academic_advisor_id' => $this->dosen->id,
            'stage' => 'bimbingan_laporan',
            'topic' => 'Revisi Laporan Akhir BAB II',
        ]);

        $consultation = $this->placement->academicConsultations()->first();
        $this->assertNotNull($consultation);

        // Delete consultation
        $delResponse = $this->actingAs($this->dosen)->delete(
            route('lecturer.consultations.destroy', $consultation->id)
        );

        $delResponse->assertRedirect();
        $this->assertDatabaseMissing('academic_consultations', [
            'id' => $consultation->id,
        ]);
    }

    public function test_dpl_can_export_monitoring_to_csv(): void
    {
        $response = $this->actingAs($this->dosen)->get(route('lecturer.monitoring.export'));
        
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));
        $this->assertTrue(str_contains($response->headers->get('content-disposition'), 'Rekap_Bimbingan_DPL_'));
        
        // Check content
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('Nama Mahasiswa', $content);
        $this->assertStringContainsString('NIM', $content);
        $this->assertStringContainsString('Ahmad Fajar', $content);
        $this->assertStringContainsString('22081010199', $content);
    }
}
