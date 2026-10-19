<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Evaluation;
use App\Models\Placement;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupervisionSecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    private function createSecurityScenario()
    {
        $univA = University::create([
            'name' => 'Universitas Indonesia',
            'code' => 'UI',
            'evaluation_scheme' => 'dual_evaluation',
            'weight_mentor' => 40,
            'weight_lecturer' => 60,
        ]);

        $univB = University::create([
            'name' => 'Universitas Airlangga',
            'code' => 'UNAIR',
            'evaluation_scheme' => 'dual_evaluation',
            'weight_mentor' => 50,
            'weight_lecturer' => 50,
        ]);

        $agencyA = AgencyProfile::create(['agency_name' => 'Dinas Komunikasi dan Informatika']);
        $unitA = Unit::create(['agency_profile_id' => $agencyA->id, 'name' => 'Bidang Aplikasi Siber', 'quota' => 10]);

        $agencyB = AgencyProfile::create(['agency_name' => 'Dinas Kependudukan dan Pencatatan Sipil']);
        $unitB = Unit::create(['agency_profile_id' => $agencyB->id, 'name' => 'Bidang Administrasi Kependudukan', 'quota' => 10]);

        $mentorA = User::factory()->create([
            'role' => 'mentor',
            'name' => 'Mentor A Kominfo',
            'agency_profile_id' => $agencyA->id,
        ]);

        $mentorB = User::factory()->create([
            'role' => 'mentor',
            'name' => 'Mentor B Dukcapil',
            'agency_profile_id' => $agencyB->id,
        ]);

        $lecturerA = User::factory()->create([
            'role' => 'dosen',
            'name' => 'Dosen A (UI)',
            'university_id' => $univA->id,
            'university' => $univA->name,
        ]);

        $lecturerB = User::factory()->create([
            'role' => 'dosen',
            'name' => 'Dosen B (UNAIR)',
            'university_id' => $univB->id,
            'university' => $univB->name,
        ]);

        // Student A: ditempatkan di Unit A, Mentor A, Dosen A
        $studentA = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Mahasiswa A']);
        $studentA->studentProfile()->create(['nim' => '22081010001', 'universitas' => $univA->name, 'jurusan' => 'Informatika', 'phone' => '08123456781']);
        $appA = Application::create([
            'user_id' => $studentA->id,
            'unit_id' => $unitA->id,
            'status' => 'active',
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
        ]);
        $placementA = Placement::create([
            'application_id' => $appA->id,
            'mentor_id' => $mentorA->id,
            'academic_advisor_id' => $lecturerA->id,
        ]);

        // Student B: ditempatkan di Unit B, Mentor B, Dosen B
        $studentB = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Mahasiswa B']);
        $studentB->studentProfile()->create(['nim' => '22081010002', 'universitas' => $univB->name, 'jurusan' => 'Sistem Informasi', 'phone' => '08123456782']);
        $appB = Application::create([
            'user_id' => $studentB->id,
            'unit_id' => $unitB->id,
            'status' => 'active',
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
        ]);
        $placementB = Placement::create([
            'application_id' => $appB->id,
            'mentor_id' => $mentorB->id,
            'academic_advisor_id' => $lecturerB->id,
        ]);

        return compact(
            'univA', 'univB',
            'agencyA', 'agencyB',
            'mentorA', 'mentorB',
            'lecturerA', 'lecturerB',
            'studentA', 'studentB',
            'placementA', 'placementB'
        );
    }

    public function test_mentor_cannot_access_or_evaluate_unassigned_student_from_another_agency(): void
    {
        $data = $this->createSecurityScenario();

        // Mentor A mencoba mengakses detail Mahasiswa B
        $responseShow = $this->actingAs($data['mentorA'])->get(route('mentor.students.show', $data['placementB']->id));
        $this->assertTrue(in_array($responseShow->status(), [403, 404]), 'Mentor A should not access student B');

        // Mentor A mencoba mengakses formulir penilaian Mahasiswa B
        $responseEvalForm = $this->actingAs($data['mentorA'])->get(route('mentor.evaluations.create', $data['placementB']->id));
        $this->assertTrue(in_array($responseEvalForm->status(), [403, 404]), 'Mentor A should not open eval form for student B');

        // Mentor A mencoba mengirim nilai ke Mahasiswa B
        $responseStore = $this->actingAs($data['mentorA'])->post(route('mentor.evaluations.store', $data['placementB']->id), [
            'nilai_disiplin' => 95,
            'nilai_kinerja' => 95,
            'nilai_laporan' => 95,
        ]);
        $this->assertTrue(in_array($responseStore->status(), [403, 404]), 'Mentor A should not store evaluation for student B');
    }

    public function test_lecturer_cannot_access_or_evaluate_unassigned_student_from_another_university(): void
    {
        $data = $this->createSecurityScenario();

        // Dosen A mencoba mengakses detail Mahasiswa B
        $responseShow = $this->actingAs($data['lecturerA'])->get(route('lecturer.students.show', $data['placementB']->id));
        $this->assertEquals(403, $responseShow->status(), 'Lecturer A should receive 403 on student B');

        // Dosen A mencoba mengakses form evaluasi Mahasiswa B
        $responseEvalForm = $this->actingAs($data['lecturerA'])->get(route('lecturer.evaluations.create', $data['placementB']->id));
        $this->assertEquals(403, $responseEvalForm->status(), 'Lecturer A should receive 403 on student B eval form');

        // Dosen A mencoba menyimpan nilai untuk Mahasiswa B
        $responseStore = $this->actingAs($data['lecturerA'])->post(route('lecturer.evaluations.store', $data['placementB']->id), [
            'score_mastery' => 90,
            'score_report' => 90,
            'score_attitude' => 90,
        ]);
        $this->assertEquals(403, $responseStore->status(), 'Lecturer A should receive 403 on storing eval for student B');
    }

    public function test_role_boundary_isolation_between_mentor_lecturer_and_student(): void
    {
        $data = $this->createSecurityScenario();

        // Mentor dilarang masuk ke portal Dosen
        $this->actingAs($data['mentorA'])->get(route('lecturer.dashboard'))->assertForbidden();

        // Dosen dilarang masuk ke portal Mentor
        $this->actingAs($data['lecturerA'])->get(route('mentor.dashboard'))->assertForbidden();

        // Mahasiswa dilarang masuk ke portal Mentor maupun Dosen
        $this->actingAs($data['studentA'])->get(route('mentor.dashboard'))->assertForbidden();
        $this->actingAs($data['studentA'])->get(route('lecturer.dashboard'))->assertForbidden();
    }

    public function test_score_updates_do_not_interfere_between_mentor_and_lecturer(): void
    {
        $data = $this->createSecurityScenario();

        // 1. Mentor A memberi nilai untuk Mahasiswa A
        $this->actingAs($data['mentorA'])->post(route('mentor.evaluations.store', $data['placementA']->id), [
            'nilai_disiplin' => 85,
            'nilai_kinerja' => 90,
            'nilai_laporan' => 95,
            'catatan' => 'Kerja mentor memuaskan',
        ])->assertRedirect();

        $evalAfterMentor = Evaluation::where('placement_id', $data['placementA']->id)->first();
        $this->assertNotNull($evalAfterMentor);
        $this->assertEquals(90.0, (float) $evalAfterMentor->nilai_pembimbing);
        $this->assertEquals('Kerja mentor memuaskan', $evalAfterMentor->catatan);

        // 2. Dosen A memberi nilai untuk Mahasiswa A
        $this->actingAs($data['lecturerA'])->post(route('lecturer.evaluations.store', $data['placementA']->id), [
            'score_mastery' => 80,
            'score_report' => 85,
            'score_attitude' => 90,
            'feedback_dosen' => 'Analisis akademik cukup tajam',
        ])->assertRedirect();

        $evalAfterDosen = Evaluation::where('placement_id', $data['placementA']->id)->first();
        // Nilai mentor tetap utuh!
        $this->assertEquals(85, $evalAfterDosen->nilai_disiplin);
        $this->assertEquals(90, $evalAfterDosen->nilai_kinerja);
        $this->assertEquals(95, $evalAfterDosen->nilai_laporan);
        $this->assertEquals('Kerja mentor memuaskan', $evalAfterDosen->catatan);

        // Nilai dosen tersimpan dengan benar
        $this->assertEquals(85.0, (float) $evalAfterDosen->nilai_dosen_calculated);
        $this->assertEquals('Analisis akademik cukup tajam', $evalAfterDosen->feedback_dosen);

        // Nilai akhir dihitung adaptif (Bobot UI: 40% Mentor (90) + 60% Dosen (85) = 36 + 51 = 87)
        $this->assertEquals(87.0, (float) $evalAfterDosen->final_score);
        $this->assertEquals('A', $evalAfterDosen->grade);
    }

    public function test_score_validation_bounds_reject_out_of_range_values(): void
    {
        $data = $this->createSecurityScenario();

        // Mentor mengirim nilai di luar batas (< 0 atau > 100)
        $this->actingAs($data['mentorA'])->post(route('mentor.evaluations.store', $data['placementA']->id), [
            'nilai_disiplin' => -10,
            'nilai_kinerja' => 150,
            'nilai_laporan' => 'bukan_angka',
        ])->assertSessionHasErrors(['nilai_disiplin', 'nilai_kinerja', 'nilai_laporan']);

        // Dosen mengirim nilai di luar batas
        $this->actingAs($data['lecturerA'])->post(route('lecturer.evaluations.store', $data['placementA']->id), [
            'score_mastery' => -5,
            'score_report' => 105,
            'score_attitude' => 'invalid',
        ])->assertSessionHasErrors(['score_mastery', 'score_report', 'score_attitude']);
    }
}
