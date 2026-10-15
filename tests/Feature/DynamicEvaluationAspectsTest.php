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

class DynamicEvaluationAspectsTest extends TestCase
{
    use RefreshDatabase;

    private function createPlacementScenario(): array
    {
        $univ = University::create([
            'name' => 'Institut Teknologi Sepuluh Nopember',
            'code' => 'ITS',
            'evaluation_scheme' => 'dual_evaluation',
            'weight_mentor' => 40,
            'weight_lecturer' => 60,
        ]);

        $agency = AgencyProfile::create(['agency_name' => 'Dinas Komunikasi dan Informatika Surabaya']);
        $unit = Unit::create(['agency_profile_id' => $agency->id, 'name' => 'Bidang Aplikasi dan Tata Kelola', 'quota' => 10]);

        $mentor = User::factory()->create([
            'role' => 'mentor',
            'name' => 'Budi Mentor',
            'agency_profile_id' => $agency->id,
        ]);

        $lecturer = User::factory()->create([
            'role' => 'dosen',
            'name' => 'Dr. Rahmat DPL',
            'university_id' => $univ->id,
            'university' => $univ->name,
        ]);

        $student = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Siti Mahasiswa']);
        $student->studentProfile()->create([
            'nim' => '5025211001',
            'universitas' => $univ->name,
            'jurusan' => 'Teknik Informatika',
            'phone' => '081234567890',
        ]);

        $application = Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'status' => 'active',
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
        ]);

        $placement = Placement::create([
            'application_id' => $application->id,
            'mentor_id' => $mentor->id,
            'academic_advisor_id' => $lecturer->id,
            'certificate_hash' => 'hashdynamicaspectstest1234567890ab',
        ]);

        return compact('univ', 'agency', 'unit', 'mentor', 'lecturer', 'student', 'application', 'placement');
    }

    public function test_lecturer_can_submit_custom_manual_aspects(): void
    {
        $data = $this->createPlacementScenario();
        $placement = $data['placement'];
        $lecturer = $data['lecturer'];

        $customAspects = [
            ['name' => 'Capaian Pembelajaran (CPL) Teori & Algoritma', 'score' => 90],
            ['name' => 'Kualitas Analisis & Naskah Laporan Ilmiah', 'score' => 85],
            ['name' => 'Sikap, Komunikasi & Etika Akademik', 'score' => 95],
            ['name' => 'Ujian Sidang & Presentasi Akhir Magang', 'score' => 90],
        ];

        $response = $this->actingAs($lecturer)->post(route('lecturer.evaluations.store', $placement->id), [
            'aspects' => $customAspects,
            'feedback_dosen' => 'Mahasiswa menunjukkan pemahaman teori dan analisis yang sangat baik.',
        ]);

        $response->assertRedirect(route('lecturer.students.show', $placement->id));
        $response->assertSessionHas('success');

        $eval = Evaluation::where('placement_id', $placement->id)->first();
        $this->assertNotNull($eval);

        // Nilai Dosen = rata-rata 4 aspek: (90 + 85 + 95 + 90) / 4 = 90.0
        $this->assertEquals(90.0, $eval->nilai_dosen_calculated);
        $this->assertCount(4, $eval->dosen_aspects);
        $this->assertSame('Capaian Pembelajaran (CPL) Teori & Algoritma', $eval->dosen_aspects[0]['name']);
        $this->assertEquals(90.0, $eval->dosen_aspects[0]['score']);

        // Kompatibilitas mundur
        $this->assertEquals(90.0, $eval->score_mastery);
        $this->assertEquals(85.0, $eval->score_report);
        $this->assertEquals(95.0, $eval->score_attitude);
        $this->assertEquals(90, $eval->nilai_akademik);
    }

    public function test_mentor_can_submit_custom_manual_aspects(): void
    {
        $data = $this->createPlacementScenario();
        $placement = $data['placement'];
        $mentor = $data['mentor'];

        $customAspects = [
            ['name' => 'Kedisiplinan & Presensi Harian', 'score' => 95],
            ['name' => 'Kinerja Teknis Pemrograman & Bug Fixing', 'score' => 90],
            ['name' => 'Kerjasama Tim & Inisiatif Solusi', 'score' => 85],
            ['name' => 'Kelengkapan Dokumentasi API & Laporan Kerja', 'score' => 90],
        ];

        $response = $this->actingAs($mentor)->post(route('mentor.evaluations.store', $placement->id), [
            'aspects' => $customAspects,
            'catatan' => 'Kinerja mahasiswa di dinas sangat memuaskan dan disiplin tinggi.',
        ]);

        $response->assertRedirect(route('mentor.students.show', $placement->id));
        $response->assertSessionHas('success');

        $eval = Evaluation::where('placement_id', $placement->id)->first();
        $this->assertNotNull($eval);

        // Nilai Pembimbing = rata-rata 4 aspek: (95 + 90 + 85 + 90) / 4 = 90.0
        $this->assertEquals(90.0, $eval->nilai_pembimbing);
        $this->assertCount(4, $eval->mentor_aspects);
        $this->assertSame('Kedisiplinan & Presensi Harian', $eval->mentor_aspects[0]['name']);
        $this->assertEquals(95.0, $eval->mentor_aspects[0]['score']);

        // Kompatibilitas mundur
        $this->assertEquals(95, $eval->nilai_disiplin);
        $this->assertEquals(90, $eval->nilai_kinerja);
        $this->assertEquals(85, $eval->nilai_laporan);
    }

    public function test_grade_sheet_displays_all_custom_aspects(): void
    {
        $data = $this->createPlacementScenario();
        $placement = $data['placement'];
        $lecturer = $data['lecturer'];

        Evaluation::create([
            'placement_id' => $placement->id,
            'nilai_disiplin' => 90,
            'nilai_kinerja' => 90,
            'nilai_laporan' => 90,
            'dosen_aspects' => [
                ['name' => 'CPL Penguasaan Teori', 'score' => 88],
                ['name' => 'CPL Desain Sistem Informasi', 'score' => 92],
                ['name' => 'Naskah Laporan Ilmiah', 'score' => 90],
            ],
            'mentor_aspects' => [
                ['name' => 'Presensi Kedinasan', 'score' => 95],
                ['name' => 'Kerjasama Tim Lapangan', 'score' => 85],
            ],
            'nilai_dosen' => 90.0,
            'nilai_akademik' => 90,
            'final_score' => 90.0,
            'grade' => 'A',
        ]);

        $response = $this->actingAs($lecturer)->get(route('lecturer.students.grade_sheet', $placement->id));
        $response->assertOk();
        $response->assertSee('CPL Penguasaan Teori');
        $response->assertSee('CPL Desain Sistem Informasi');
        $response->assertSee('Naskah Laporan Ilmiah');
        $response->assertSee('Presensi Kedinasan');
    }

    public function test_internship_certificate_displays_all_custom_aspects_on_transcript(): void
    {
        $data = $this->createPlacementScenario();
        $placement = $data['placement'];
        $application = $data['application'];

        $application->update(['status' => 'completed']);
        \App\Models\FinalReport::create([
            'placement_id' => $placement->id,
            'file_path' => 'laporan_magang_final.pdf',
            'status' => 'approved',
        ]);
        \App\Models\Logbook::create([
            'placement_id' => $placement->id,
            'date' => now()->toDateString(),
            'activity' => 'Penyusunan modul dan implementasi sistem',
            'status' => 'approved',
        ]);

        Evaluation::create([
            'placement_id' => $placement->id,
            'nilai_disiplin' => 90,
            'nilai_kinerja' => 90,
            'nilai_laporan' => 90,
            'dosen_aspects' => [
                ['name' => 'CPL Penguasaan Teori', 'score' => 88],
                ['name' => 'CPL Desain Sistem Informasi', 'score' => 92],
                ['name' => 'Naskah Laporan Ilmiah', 'score' => 90],
            ],
            'mentor_aspects' => [
                ['name' => 'Presensi Kedinasan', 'score' => 95],
                ['name' => 'Kerjasama Tim Lapangan', 'score' => 85],
            ],
            'nilai_dosen' => 90.0,
            'nilai_akademik' => 90,
            'final_score' => 90.0,
            'grade' => 'A',
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.certificates.show', $placement->id));
        $response->assertOk();
        $response->assertSee('Lampiran Transkrip Nilai Magang');
        $response->assertSee('CPL Penguasaan Teori');
        $response->assertSee('CPL Desain Sistem Informasi');
        $response->assertSee('Naskah Laporan Ilmiah');
        $response->assertSee('Presensi Kedinasan');
        $response->assertSee('Kerjasama Tim Lapangan');
    }
}

