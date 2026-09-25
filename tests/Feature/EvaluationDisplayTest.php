<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Evaluation;
use App\Models\FinalReport;
use App\Models\Placement;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresi tampilan nilai: kolom final_score / score_* default-nya 0 (bukan null),
 * sehingga pola `$eval->final_score ?? ...` selalu menampilkan 0.
 */
class EvaluationDisplayTest extends TestCase
{
    use RefreshDatabase;

    /** Data seperti seeder lama: hanya nilai dinas + nilai_akademik, tanpa final_score & aspek DPL */
    private function makeLegacyEvaluation(): Placement
    {
        $agency = AgencyProfile::create(['agency_name' => 'Dinas Uji']);
        $unit = Unit::create(['agency_profile_id' => $agency->id, 'name' => 'Unit Uji', 'quota' => 5]);
        $student = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Anisa Uji']);
        $student->studentProfile()->create(['nim' => '22081010014', 'universitas' => 'Unesa', 'jurusan' => 'TI', 'phone' => '08']);

        $application = Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'status' => 'completed',
            'start_date' => '2026-06-23',
            'end_date' => '2026-09-23',
        ]);
        $placement = Placement::create(['application_id' => $application->id, 'certificate_hash' => 'hashujinilai1234567890abcdefghij']);
        FinalReport::create(['placement_id' => $placement->id, 'file_path' => 'x.pdf', 'status' => 'approved']);

        Evaluation::create([
            'placement_id' => $placement->id,
            'nilai_disiplin' => 93,
            'nilai_kinerja' => 93,
            'nilai_laporan' => 90,
            'nilai_akademik' => 97,
        ]);

        return $placement;
    }

    public function test_accessors_compute_final_score_from_legacy_data(): void
    {
        $eval = $this->makeLegacyEvaluation()->evaluation;

        $this->assertEquals(92.0, $eval->nilai_pembimbing);
        $this->assertEquals(97.0, $eval->nilai_dosen_calculated);
        $this->assertEquals(95.0, $eval->nilai_akhir);       // 40% x 92 + 60% x 97
        $this->assertSame('A', $eval->grade_calculated);
        $this->assertEquals(97.0, $eval->dosenAspectScore('score_mastery'));
    }

    public function test_public_verification_page_shows_real_final_score(): void
    {
        $this->makeLegacyEvaluation();

        $this->get(route('verify.certificate', 'hashujinilai1234567890abcdefghij'))
            ->assertOk()
            ->assertSeeText('95 (Predikat: A)')
            ->assertDontSeeText(' 0 (Predikat')
            ->assertDontSeeText('0.00 (Predikat');
    }

    public function test_certificate_shows_dpl_aspect_scores_instead_of_zero(): void
    {
        $placement = $this->makeLegacyEvaluation();
        $admin = User::factory()->create(['role' => 'super_admin']);

        $html = $this->actingAs($admin)
            ->get(route('admin.certificates.show', $placement->id))
            ->assertOk()
            ->getContent();

        $compact = preg_replace('/\s+/', '', $html);

        // 3 aspek DPL + subtotal DPL harus menampilkan 97, bukan 0 / 0.00
        $this->assertGreaterThanOrEqual(4, substr_count($compact, '>97<'));
        $this->assertStringNotContainsString('>0.00<', $compact);
    }

    public function test_aspect_scores_are_used_when_filled(): void
    {
        $eval = $this->makeLegacyEvaluation()->evaluation;
        $eval->update(['score_mastery' => 88, 'score_report' => 90, 'score_attitude' => 92, 'nilai_dosen' => 90]);

        $this->assertEquals(88.0, $eval->fresh()->dosenAspectScore('score_mastery'));
        $this->assertEquals(90.0, $eval->fresh()->nilai_dosen_calculated);
    }
}
