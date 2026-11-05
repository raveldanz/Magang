<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Evaluation;
use App\Models\Placement;
use App\Models\StudentProfile;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateTranscriptDensityTest extends TestCase
{
    use RefreshDatabase;

    private function placementFor(string $scheme, int $weightMentor, int $weightLecturer): Placement
    {
        $agency = AgencyProfile::create([
            'government_name' => 'Pemerintah Kota Surabaya',
            'agency_name' => 'Dinas Perpustakaan dan Kearsipan',
            'city' => 'Surabaya',
        ]);
        $unit = Unit::create(['agency_profile_id' => $agency->id, 'name' => 'Bidang Arsip', 'quota' => 5]);
        $univ = University::create([
            'name' => 'Universitas Contoh',
            'evaluation_scheme' => $scheme,
            'weight_mentor' => $weightMentor,
            'weight_lecturer' => $weightLecturer,
        ]);
        $student = User::factory()->create(['name' => 'Siti Contoh', 'role' => 'mahasiswa', 'university_id' => $univ->id]);
        StudentProfile::create([
            'user_id' => $student->id, 'nim' => '99887766', 'phone' => '081200000000',
            'jurusan' => 'Ilmu Perpustakaan', 'universitas' => 'Universitas Contoh', 'university_id' => $univ->id,
        ]);
        $application = Application::create([
            'user_id' => $student->id, 'unit_id' => $unit->id, 'status' => 'completed',
            'start_date' => '2026-08-01', 'end_date' => '2026-10-01',
        ]);
        $placement = Placement::create([
            'application_id' => $application->id,
            'status' => 'active',
            'certificate_number' => '400.14.5.4/1234/436.7.17/2026',
            'certificate_hash' => 'hash-uji-kepadatan-transkrip-0001',
        ]);
        Evaluation::create(['placement_id' => $placement->id, 'nilai_pembimbing' => 90, 'nilai_akhir' => 90, 'grade' => 'A', 'status' => 'submitted']);

        return $placement;
    }

    private function renderCertificate(Placement $placement)
    {
        $admin = User::factory()->create(['role' => 'admin', 'agency_profile_id' => null]);

        return $this->actingAs($admin)->get(route('admin.certificates.show', $placement->id));
    }

    public function test_single_party_transcript_starts_at_normal_density(): void
    {
        $response = $this->renderCertificate($this->placementFor('mentor_only', 100, 0));

        $response->assertStatus(200);
        $response->assertSee('data-density="normal"', false);
        $response->assertSee('data-row="final"', false);
        $response->assertDontSee('Subtotal Nilai DPL');
    }

    public function test_two_party_transcript_starts_denser_and_renders_both_sections(): void
    {
        $placement = $this->placementFor('dual', 60, 40);

        $response = $this->renderCertificate($placement);

        $response->assertStatus(200);
        $response->assertSee('Subtotal Nilai Dinas');
        $response->assertSee('Subtotal Nilai DPL');
        $response->assertDontSee('data-density="normal"', false);
    }

    public function test_layout_script_and_page_label_are_present_on_single_and_batch_print(): void
    {
        $placement = $this->placementFor('mentor_only', 100, 0);

        $single = $this->renderCertificate($placement);
        $single->assertSee('data-tr-page-label', false);
        $single->assertSee('transcript-sheet', false);
        $single->assertSee('layoutTranscript', false);

        \App\Models\Certificate::create([
            'placement_id' => $placement->id,
            'certificate_number' => $placement->certificate_number,
            'file_path' => 'certificates/siti.pdf',
            'approval_status' => 'approved',
            'issued_at' => now()->toDateString(),
        ]);

        $batch = $this->get(route('admin.certificates.batchPrint'));
        $batch->assertStatus(200);
        $batch->assertSee('data-tr-page-label', false);
        $batch->assertSee('layoutTranscript', false);
    }
}
