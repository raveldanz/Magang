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

class EvaluationPolicyAndComponentsTest extends TestCase
{
    use RefreshDatabase;

    private AgencyProfile $agency;
    private Unit $unit;
    private User $admin;
    private User $mentor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = AgencyProfile::create(['agency_name' => 'Dinas Kominfo']);
        $this->admin = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agency->id]);
        $this->mentor = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $this->agency->id]);
        $this->unit = Unit::create([
            'agency_profile_id' => $this->agency->id,
            'name' => 'Aptika',
            'quota' => 5,
            'head_user_id' => $this->mentor->id,
        ]);
    }

    public function test_university_defaults_to_100_percent_dinas(): void
    {
        $univ = University::create([
            'name' => 'Universitas Indonesia Hebat',
            'code' => 'UIH',
        ]);

        $this->assertSame('mentor_only', $univ->evaluation_scheme);
        $this->assertSame(100, (int) $univ->weight_mentor);
        $this->assertSame(0, (int) $univ->weight_lecturer);
        $this->assertFalse((bool) $univ->require_dpl);
        $this->assertFalse($univ->requiresAdvisor());

        // Default components
        $dinasComponents = $univ->getDinasComponents();
        $this->assertCount(3, $dinasComponents);
        $this->assertSame('Disiplin, Kehadiran, & Ketaatan Tata Tertib Kedinasan', $dinasComponents[1]);
        $this->assertSame('Kinerja Teknis, Kualitas Output Proyek, & Tanggung Jawab Kerja', $dinasComponents[2]);
        $this->assertSame('Inisiatif, Komunikasi Lapangan, & Penyusunan Laporan Dinas', $dinasComponents[3]);

        $dplComponents = $univ->getDplComponents();
        $this->assertCount(3, $dplComponents);
        $this->assertSame('Penguasaan Materi, Teori Ilmiah, & Solusi Teknis Magang', $dplComponents[1]);
        $this->assertSame('Kualitas, Sistematika Penulisan, & Ketajaman Analisis Laporan Akhir', $dplComponents[2]);
        $this->assertSame('Sikap, Komunikasi, & Keaktifan Konsultasi Bimbingan', $dplComponents[3]);
    }

    public function test_custom_components_stored_and_retrieved_correctly(): void
    {
        $customDinas = [
            'Kedisiplinan Kerja Praktik',
            'Kualitas Hasil Proyek',
            'Laporan dan Presentasi Dinas',
        ];
        $customDpl = [
            'Dasar Teori dan Metodologi',
            'Sistematika Laporan Ilmiah',
            'Etika dan Bimbingan Akademik',
        ];

        $univ = University::create([
            'name' => 'Institut Teknologi Kustom',
            'code' => 'ITK',
            'dinas_components' => $customDinas,
            'dpl_components' => $customDpl,
        ]);

        $this->assertSame($customDinas, array_values($univ->getDinasComponents()));
        $this->assertSame($customDpl, array_values($univ->getDplComponents()));

        $this->assertSame('Kedisiplinan Kerja Praktik', $univ->getDinasComponent(1));
        $this->assertSame('Kualitas Hasil Proyek', $univ->getDinasComponent(2));
        $this->assertSame('Laporan dan Presentasi Dinas', $univ->getDinasComponent(3));

        $this->assertSame('Dasar Teori dan Metodologi', $univ->getDplComponent(1));
        $this->assertSame('Sistematika Laporan Ilmiah', $univ->getDplComponent(2));
        $this->assertSame('Etika dan Bimbingan Akademik', $univ->getDplComponent(3));
    }

    public function test_university_profile_can_update_policy_and_custom_components(): void
    {
        $univ = University::create([
            'name' => 'Universitas Pembaharuan',
            'code' => 'UPB',
        ]);
        $univAdmin = User::factory()->create([
            'role' => 'universitas',
            'university_id' => $univ->id,
        ]);

        $response = $this->actingAs($univAdmin)->put(route('university.profile.update'), [
            'name' => 'Universitas Pembaharuan Maju',
            'code' => 'UPB',
            'evaluation_scheme' => 'dual_evaluation',
            'weight_mentor' => 60,
            'weight_lecturer' => 40,
            'require_dpl' => 1,
            'dinas_components' => [
                'Komponen Dinas 1',
                'Komponen Dinas 2',
                'Komponen Dinas 3',
            ],
            'dpl_components' => [
                'Komponen DPL 1',
                'Komponen DPL 2',
                'Komponen DPL 3',
            ],
        ]);

        $response->assertRedirect();
        $univ->refresh();

        $this->assertSame('dual_evaluation', $univ->evaluation_scheme);
        $this->assertSame(60, (int) $univ->weight_mentor);
        $this->assertSame(40, (int) $univ->weight_lecturer);
        $this->assertTrue((bool) $univ->require_dpl);
        $this->assertSame('Komponen Dinas 1', $univ->getDinasComponent(1));
        $this->assertSame('Komponen DPL 1', $univ->getDplComponent(1));
    }

    public function test_university_profile_validates_dual_evaluation_sum_must_be_100(): void
    {
        $univ = University::create(['name' => 'Univ Validasi', 'code' => 'UV']);
        $univAdmin = User::factory()->create([
            'role' => 'universitas',
            'university_id' => $univ->id,
        ]);

        $response = $this->actingAs($univAdmin)->put(route('university.profile.update'), [
            'name' => 'Univ Validasi',
            'code' => 'UV',
            'evaluation_scheme' => 'dual_evaluation',
            'weight_mentor' => 70,
            'weight_lecturer' => 40, // 70 + 40 = 110 != 100
        ]);

        $response->assertSessionHasErrors('weight_mentor');
    }

    public function test_mentor_only_100_percent_scheme_evaluation_and_certificate(): void
    {
        $univ = University::create([
            'name' => 'Universitas Mandiri',
            'code' => 'UM',
            'evaluation_scheme' => 'mentor_only',
            'weight_mentor' => 100,
            'weight_lecturer' => 0,
            'require_dpl' => false,
        ]);

        $student = User::factory()->create(['role' => 'mahasiswa', 'university_id' => $univ->id]);
        $student->studentProfile()->create(['nim' => '123456', 'universitas' => $univ->name, 'jurusan' => 'TI', 'phone' => '08123456789']);

        $application = Application::create([
            'user_id' => $student->id,
            'unit_id' => $this->unit->id,
            'status' => 'completed',
            'start_date' => '2026-01-01',
            'end_date' => '2026-03-31',
        ]);
        $placement = Placement::create([
            'application_id' => $application->id,
            'mentor_id' => $this->mentor->id,
            'certificate_hash' => 'hash_test_mentor_only_12345678901234',
        ]);
        FinalReport::create(['placement_id' => $placement->id, 'file_path' => 'report.pdf', 'status' => 'approved']);
        Logbook::create(['placement_id' => $placement->id, 'date' => '2026-02-01', 'activity' => 'Aktivitas magang', 'status' => 'approved']);

        // Nilai dinas lengkap, DPL 0
        $evaluation = Evaluation::create([
            'placement_id' => $placement->id,
            'nilai_disiplin' => 90,
            'nilai_kinerja' => 80,
            'nilai_laporan' => 100, // Rata-rata dinas = 90
            'nilai_akademik' => 0,
            'score_mastery' => 0,
            'score_report' => 0,
            'score_attitude' => 0,
        ]);

        $this->assertTrue($evaluation->is_complete);
        $this->assertEquals(90.0, (float) $evaluation->nilai_akhir);

        $application->refresh();
        $this->assertTrue($application->has_complete_evaluation);
        $this->assertEmpty($application->certificateBlockers());

        // Test render sertifikat HTML
        $response = $this->actingAs($this->admin)->get(route('admin.certificates.generate', $placement->id));
        $response->assertOk();
        $content = $response->getContent();

        // Section A (Dinas) ada
        $this->assertStringContainsString('Penilaian Pembimbing Lapangan Dinas', $content);
        $this->assertStringContainsString('Disiplin, Kehadiran', $content);

        // Section B (DPL) dan subtotal DPL TIDAK ADA
        $this->assertStringNotContainsString('Penilaian Akademik Dosen Pembimbing Lapangan', $content);
        $this->assertStringNotContainsString('Subtotal Nilai DPL', $content);
    }

    public function test_lecturer_only_100_percent_scheme_evaluation_and_certificate(): void
    {
        $univ = University::create([
            'name' => 'Universitas Akademik Penuh',
            'code' => 'UAP',
            'evaluation_scheme' => 'lecturer_only',
            'weight_mentor' => 0,
            'weight_lecturer' => 100,
            'require_dpl' => true,
        ]);

        $student = User::factory()->create(['role' => 'mahasiswa', 'university_id' => $univ->id]);
        $student->studentProfile()->create(['nim' => '654321', 'universitas' => $univ->name, 'jurusan' => 'SI', 'phone' => '08123456789']);

        $application = Application::create([
            'user_id' => $student->id,
            'unit_id' => $this->unit->id,
            'status' => 'completed',
            'start_date' => '2026-01-01',
            'end_date' => '2026-03-31',
        ]);
        $placement = Placement::create([
            'application_id' => $application->id,
            'mentor_id' => $this->mentor->id,
            'certificate_hash' => 'hash_test_lecturer_only_123456789012',
        ]);
        FinalReport::create(['placement_id' => $placement->id, 'file_path' => 'report.pdf', 'status' => 'approved']);
        Logbook::create(['placement_id' => $placement->id, 'date' => '2026-02-01', 'activity' => 'Aktivitas magang', 'status' => 'approved']);

        // Nilai DPL lengkap, dinas 0
        $evaluation = Evaluation::create([
            'placement_id' => $placement->id,
            'nilai_disiplin' => 0,
            'nilai_kinerja' => 0,
            'nilai_laporan' => 0,
            'score_mastery' => 85,
            'score_report' => 90,
            'score_attitude' => 95, // Rata-rata DPL = 90
            'nilai_akademik' => 90,
        ]);

        $this->assertTrue($evaluation->is_complete);
        $this->assertEquals(90.0, (float) $evaluation->nilai_akhir);

        $application->refresh();
        $this->assertTrue($application->has_complete_evaluation);
        $this->assertEmpty($application->certificateBlockers());

        // Test render sertifikat HTML
        $response = $this->actingAs($this->admin)->get(route('admin.certificates.generate', $placement->id));
        $response->assertOk();
        $content = $response->getContent();

        // Section Dinas TIDAK ADA
        $this->assertStringNotContainsString('Penilaian Pembimbing Lapangan Dinas', $content);
        $this->assertStringNotContainsString('Subtotal Nilai Dinas', $content);

        // Section DPL ada
        $this->assertStringContainsString('Penilaian Akademik Dosen Pembimbing Lapangan', $content);
        $this->assertStringContainsString('Penguasaan Materi, Teori Ilmiah', $content);
    }

    public function test_dual_evaluation_scheme_requires_both_and_calculates_weighted_score(): void
    {
        $univ = University::create([
            'name' => 'Universitas Dua Pihak',
            'code' => 'UDP',
            'evaluation_scheme' => 'dual_evaluation',
            'weight_mentor' => 60,
            'weight_lecturer' => 40,
            'require_dpl' => true,
        ]);

        $student = User::factory()->create(['role' => 'mahasiswa', 'university_id' => $univ->id]);
        $student->studentProfile()->create(['nim' => '789101', 'universitas' => $univ->name, 'jurusan' => 'IF', 'phone' => '08123456789']);

        $application = Application::create([
            'user_id' => $student->id,
            'unit_id' => $this->unit->id,
            'status' => 'completed',
            'start_date' => '2026-01-01',
            'end_date' => '2026-03-31',
        ]);
        $placement = Placement::create([
            'application_id' => $application->id,
            'mentor_id' => $this->mentor->id,
            'certificate_hash' => 'hash_test_dual_eval_123456789012345',
        ]);
        FinalReport::create(['placement_id' => $placement->id, 'file_path' => 'report.pdf', 'status' => 'approved']);
        Logbook::create(['placement_id' => $placement->id, 'date' => '2026-02-01', 'activity' => 'Aktivitas magang', 'status' => 'approved']);

        // Hanya isi dinas: belum lengkap
        $evaluation = Evaluation::create([
            'placement_id' => $placement->id,
            'nilai_disiplin' => 80,
            'nilai_kinerja' => 80,
            'nilai_laporan' => 80, // Avg dinas = 80
            'score_mastery' => 0,
            'score_report' => 0,
            'score_attitude' => 0,
            'nilai_akademik' => 0,
        ]);

        $this->assertFalse($evaluation->is_complete);
        $application->refresh();
        $this->assertFalse($application->has_complete_evaluation);
        $this->assertContains('Nilai evaluasi akademik dari Dosen Pembimbing Lapangan (kampus) belum lengkap.', $application->certificateBlockers());

        // Lengkapi nilai DPL
        $evaluation->update([
            'score_mastery' => 90,
            'score_report' => 90,
            'score_attitude' => 90,
            'nilai_akademik' => 90,
        ]);

        // Nilai akhir: (80 * 0.6) + (90 * 0.4) = 48 + 36 = 84.0
        $this->assertTrue($evaluation->is_complete);
        $this->assertEquals(84.0, (float) $evaluation->nilai_akhir);

        $application->refresh();
        $this->assertTrue($application->has_complete_evaluation);
        $this->assertEmpty($application->certificateBlockers());

        // Test render sertifikat HTML: keduanya harus muncul
        $response = $this->actingAs($this->admin)->get(route('admin.certificates.generate', $placement->id));
        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringContainsString('Penilaian Pembimbing Lapangan Dinas', $content);
        $this->assertStringContainsString('Subtotal Nilai Dinas (Rata-rata):', $content);
        $this->assertStringContainsString('60%', $content);
        $this->assertStringContainsString('Penilaian Akademik Dosen Pembimbing Lapangan', $content);
        $this->assertStringContainsString('Subtotal Nilai DPL (Rata-rata):', $content);
        $this->assertStringContainsString('40%', $content);
    }
}
