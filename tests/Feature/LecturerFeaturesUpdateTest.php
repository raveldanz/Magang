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
            ->assertSeeText('Buka Naskah Laporan')
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

    public function test_lecturer_can_one_click_approve_mentor_reviewed_logbooks_for_placement()
    {
        $data = $this->createLecturerScenario();

        // Buat logbook kedua yang juga sudah di-ACC mentor tapi pending di DPL
        $logbook2 = Logbook::create([
            'placement_id' => $data['placement1']->id,
            'date' => now()->subDay()->toDateString(),
            'activity' => 'Deployment aplikasi ke server staging',
            'status' => 'approved',
            'lecturer_status' => 'pending',
        ]);

        // Buat logbook ketiga yang belum di-ACC mentor (status masih pending)
        $logbook3 = Logbook::create([
            'placement_id' => $data['placement1']->id,
            'date' => now()->toDateString(),
            'activity' => 'Menulis dokumentasi teknis API',
            'status' => 'pending',
            'lecturer_status' => 'pending',
        ]);

        $response = $this->actingAs($data['lecturer'])->post(
            route('lecturer.students.logbooks.approve_mentor_reviewed', $data['placement1']->id)
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Logbook 1 dan 2 (yang sudah di-ACC mentor) harus ter-ACC oleh DPL
        $this->assertEquals('approved', $data['logbook1']->fresh()->lecturer_status);
        $this->assertEquals('approved', $logbook2->fresh()->lecturer_status);
        $this->assertNotNull($data['logbook1']->fresh()->lecturer_verified_at);

        // Logbook 3 (yang belum di-ACC mentor) harus tetap pending
        $this->assertEquals('pending', $logbook3->fresh()->lecturer_status);
    }

    public function test_lecturer_can_one_click_approve_all_mentor_reviewed_logbooks_globally()
    {
        $data = $this->createLecturerScenario();

        $response = $this->actingAs($data['lecturer'])->post(
            route('lecturer.logbooks.approve_all_mentor_reviewed')
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals('approved', $data['logbook1']->fresh()->lecturer_status);
    }

    public function test_student_detail_renders_adaptive_stepper_for_default_mentor_only_scheme()
    {
        $data = $this->createLecturerScenario();

        // Update universitas mahasiswa ke skema default 100% mentor dinas
        $univ = University::find($data['student1']->university_id ?? $data['lecturer']->university_id);
        $univ->update([
            'evaluation_scheme' => 'mentor_only',
            'weight_mentor' => 100,
            'weight_lecturer' => 0,
        ]);

        $response = $this->actingAs($data['lecturer'])->get(
            route('lecturer.students.show', $data['placement1']->id)
        );

        $response->assertOk()
            ->assertSeeText('3 Syarat')
            ->assertDontSeeText('4 Syarat')
            ->assertSeeText('100% Mentor Dinas')
            ->assertSeeText('ACC Semua yang Di-ACC Mentor (1)');
    }

    public function test_lecturer_dashboard_displays_refined_action_center_and_balanced_cards()
    {
        $data = $this->createLecturerScenario();

        // Update naskah laporan menjadi pending agar Action Center muncul
        $data['placement1']->finalreport->update([
            'status' => 'pending',
        ]);

        $response = $this->actingAs($data['lecturer'])->get(route('lecturer.dashboard'));

        $response->assertOk()
            ->assertSeeText('Perlu Tindakan Dosen')
            ->assertSeeText('Review Laporan')
            ->assertSeeText('Semua evaluasi tuntas')
            ->assertDontSeeText('bg-rose-600');
    }

    public function test_lecturer_can_filter_logbooks_by_mentor_approved_status()
    {
        $data = $this->createLecturerScenario();

        // Logbook 1: status=approved (mentor), lecturer_status=pending (dpl) -> harus muncul
        // Buat logbook 2: status=pending, lecturer_status=pending -> tidak boleh muncul di filter mentor_approved
        \App\Models\Logbook::create([
            'placement_id' => $data['placement1']->id,
            'date' => now()->subDays(2)->toDateString(),
            'activity' => 'Aktivitas yang belum di-ACC mentor dinas',
            'status' => 'pending',
            'lecturer_status' => 'pending',
        ]);

        $response = $this->actingAs($data['lecturer'])->get(
            route('lecturer.logbooks.index', ['status' => 'mentor_approved'])
        );

        $response->assertOk()
            ->assertSeeText('Melakukan testing REST API layanan e-Gov')
            ->assertDontSeeText('Aktivitas yang belum di-ACC mentor dinas');
    }

    public function test_lecturer_monitoring_displays_student_activity_status()
    {
        $data = $this->createLecturerScenario();

        $response = $this->actingAs($data['lecturer'])->get(route('lecturer.monitoring.index'));

        $response->assertOk()
            ->assertSeeText('2 hari lalu');

        // Buat logbook hari ini
        \App\Models\Logbook::create([
            'placement_id' => $data['placement1']->id,
            'date' => now()->toDateString(),
            'activity' => 'Melakukan testing hari ini',
            'status' => 'pending',
            'lecturer_status' => 'pending',
        ]);

        $responseToday = $this->actingAs($data['lecturer'])->get(route('lecturer.monitoring.index'));
        $responseToday->assertOk()
            ->assertSeeText('Aktif hari ini');
    }
}


