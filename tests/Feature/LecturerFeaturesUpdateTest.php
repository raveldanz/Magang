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

class LecturerFeaturesUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function createLecturerScenario()
    {
        $univ = University::create([
            'name' => 'Universitas Indonesia',
            'code' => 'UI',
            'evaluation_scheme' => 'dual_evaluation',
            'weight_mentor' => 40,
            'weight_lecturer' => 60,
        ]);

        $lecturer = User::factory()->create([
            'role' => 'dosen',
            'name' => 'Dr. Budi Santoso, M.Kom',
            'email' => 'dosen.budi@ui.ac.id',
            'university_id' => $univ->id,
            'university' => $univ->name,
        ]);

        $otherLecturer = User::factory()->create([
            'role' => 'dosen',
            'name' => 'Dr. Siti Rahma, M.T',
            'email' => 'dosen.siti@ui.ac.id',
            'university_id' => $univ->id,
            'university' => $univ->name,
        ]);

        $agency = AgencyProfile::create(['agency_name' => 'Dinas Komunikasi dan Informatika']);
        $unit = Unit::create(['agency_profile_id' => $agency->id, 'name' => 'Aplikasi & Tata Kelola e-Gov', 'quota' => 10]);

        $mentor = User::factory()->create([
            'role' => 'mentor',
            'name' => 'Ahmad Mentor, S.Kom',
            'agency_profile_id' => $agency->id,
        ]);

        // Student 1: Aktif Magang (status 'active')
        $student1 = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Bima Arya']);
        $student1->studentProfile()->create([
            'nim' => '22081010001',
            'universitas' => $univ->name,
            'jurusan' => 'Teknik Informatika',
            'phone' => '08123456789',
        ]);
        $app1 = Application::create([
            'user_id' => $student1->id,
            'unit_id' => $unit->id,
            'status' => 'active',
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
        ]);
        $placement1 = Placement::create([
            'application_id' => $app1->id,
            'mentor_id' => $mentor->id,
            'academic_advisor_id' => $lecturer->id,
        ]);

        // Add logbook for Student 1
        $logbook1 = Logbook::create([
            'placement_id' => $placement1->id,
            'date' => now()->subDays(2)->toDateString(),
            'activity' => 'Melakukan testing REST API layanan e-Gov',
            'status' => 'approved',
            'feedback' => 'Bagus dan rapi',
            'lecturer_status' => 'pending',
        ]);

        // Add evaluation for Student 1
        Evaluation::create([
            'placement_id' => $placement1->id,
            'nilai_disiplin' => 90,
            'nilai_kinerja' => 90,
            'nilai_laporan' => 90,
            'score_mastery' => 88,
            'score_report' => 90,
            'score_attitude' => 92,
            'nilai_dosen' => 90,
            'final_score' => 90,
            'grade' => 'A',
            'feedback_dosen' => 'Mahasiswa sangat proaktif dan memiliki kemampuan analisis yang tinggi.',
        ]);

        // Add FinalReport for Student 1
        FinalReport::create([
            'placement_id' => $placement1->id,
            'file_path' => 'final_reports/default.pdf',
            'status' => 'approved',
            'feedback' => 'Naskah laporan akhir telah disetujui (ACC)',
        ]);

        // Student 2: Calon Peserta diterima tapi belum 'active' (status 'accepted')
        $student2 = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Citra Dewi']);
        $student2->studentProfile()->create([
            'nim' => '22081010002',
            'universitas' => $univ->name,
            'jurusan' => 'Sistem Informasi',
            'phone' => '08123456788',
        ]);
        $app2 = Application::create([
            'user_id' => $student2->id,
            'unit_id' => $unit->id,
            'status' => 'accepted',
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
        ]);
        $placement2 = Placement::create([
            'application_id' => $app2->id,
            'mentor_id' => $mentor->id,
            'academic_advisor_id' => $lecturer->id,
        ]);

        // Student 3: Mahasiswa Mengundurkan Diri (status 'resigned')
        $student3 = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Doni Resigned']);
        $student3->studentProfile()->create([
            'nim' => '22081010003',
            'universitas' => $univ->name,
            'jurusan' => 'Informatika',
            'phone' => '08123456787',
        ]);
        $app3 = Application::create([
            'user_id' => $student3->id,
            'unit_id' => $unit->id,
            'status' => 'resigned',
            'start_date' => now()->subMonths(3)->toDateString(),
            'end_date' => now()->toDateString(),
        ]);
        $placement3 = Placement::create([
            'application_id' => $app3->id,
            'academic_advisor_id' => $lecturer->id,
        ]);

        return compact('univ', 'lecturer', 'otherLecturer', 'agency', 'unit', 'mentor', 'student1', 'placement1', 'logbook1', 'student2', 'placement2', 'student3', 'placement3');
    }

    public function test_lecturer_dashboard_excludes_resigned_students()
    {
        $data = $this->createLecturerScenario();

        $response = $this->actingAs($data['lecturer'])->get(route('lecturer.dashboard'));

        $response->assertOk()
            ->assertSeeText('Bima Arya')
            ->assertSeeText('Citra Dewi')
            ->assertDontSeeText('Doni Resigned');
    }

    public function test_lecturer_monitoring_active_tab_includes_both_active_and_accepted()
    {
        $data = $this->createLecturerScenario();

        // Mengakses tab 'active' (default)
        $response = $this->actingAs($data['lecturer'])->get(route('lecturer.monitoring.index', ['tab' => 'active']));

        $response->assertOk()
            ->assertSeeText('Bima Arya')
            ->assertSeeText('Citra Dewi') // Status 'accepted' sekarang ikut tampil di tab bimbingan aktif
            ->assertDontSeeText('Doni Resigned');
    }

    public function test_lecturer_dashboard_report_status_filter_options_clean_without_optgroup()
    {
        $data = $this->createLecturerScenario();

        $response = $this->actingAs($data['lecturer'])->get(route('lecturer.dashboard'));

        $response->assertOk()
            ->assertSeeText('Semua Status Laporan')
            ->assertSeeText('Menunggu Review (Pending)')
            ->assertSeeText('Perlu Revisi')
            ->assertSeeText('Disetujui (Approved)')
            ->assertSeeText('Belum Unggah Laporan')
            ->assertDontSee('<optgroup');
    }

    public function test_lecturer_can_view_student_detail_and_open_report_in_new_tab()
    {
        $data = $this->createLecturerScenario();

        $response = $this->actingAs($data['lecturer'])->get(route('lecturer.students.show', $data['placement1']->id));

        $response->assertOk()
            ->assertSeeText('Bima Arya')
            ->assertSeeText('Buka Naskah Laporan (Tab Baru ↗)')
            ->assertSee('target="_blank"', false)
            ->assertDontSeeText('Cetak Lembar Nilai / Berita Acara')
            ->assertDontSee('<iframe', false)
            ->assertSeeText('Melakukan testing REST API layanan e-Gov')
            ->assertSeeText('Verifikasi DPL');
    }

    public function test_lecturer_can_verify_logbook_inline_with_feedback()
    {
        $data = $this->createLecturerScenario();

        $response = $this->actingAs($data['lecturer'])->put(route('lecturer.logbooks.updateStatus', $data['logbook1']->id), [
            'status' => 'approved',
            'feedback' => 'Logbook diverifikasi dan disetujui oleh DPL.',
        ]);

        $response->assertRedirect();
        $this->assertEquals('approved', $data['logbook1']->fresh()->lecturer_status);
        $this->assertEquals('Logbook diverifikasi dan disetujui oleh DPL.', $data['logbook1']->fresh()->lecturer_feedback);
        $this->assertNotNull($data['logbook1']->fresh()->lecturer_verified_at);
    }
}
