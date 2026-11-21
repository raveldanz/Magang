<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Placement;
use App\Models\StudentProfile;
use App\Models\SystemFeedback;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use App\Services\UniversityHubService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostgresCompatibilityRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_feedback_search_is_case_insensitive_on_all_drivers(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        SystemFeedback::create([
            'user_id' => $superAdmin->id,
            'sender_name' => 'Budi Santoso',
            'sender_email' => 'Budi.Santoso@example.test',
            'sender_role' => 'mahasiswa',
            'target_role' => 'super_admin',
            'category' => 'error_bug',
            'subject' => 'Kendala Upload Sertifikat PDF',
            'message' => 'Halaman Gagal Memuat Berkas Lampiran',
            'status' => 'pending',
        ]);

        foreach (['kendala upload', 'KENDALA UPLOAD', 'Budi Santoso', 'budi santoso', 'lampiran'] as $query) {
            $this->actingAs($superAdmin)
                ->get(route('admin.feedbacks.index', ['search' => $query]))
                ->assertOk()
                ->assertSee('Kendala Upload Sertifikat PDF');
        }
    }

    public function test_university_dashboard_lecturer_and_hub_search_are_case_insensitive(): void
    {
        $agency = AgencyProfile::create([
            'agency_name' => 'Dinas Perpustakaan dan Kearsipan',
            'acronym' => 'Dispusip',
        ]);
        $unit = Unit::create([
            'agency_profile_id' => $agency->id,
            'name' => 'Bidang Pembinaan dan Pengelolaan Perpustakaan',
            'quota' => 10,
        ]);
        $university = University::create([
            'name' => 'Universitas Negeri Surabaya',
            'acronym' => 'UNESA',
            'is_verified' => true,
            'status' => 'active',
            'institution_type' => University::TYPE_COLLEGE,
        ]);

        $univAdmin = User::factory()->create([
            'role' => 'universitas',
            'status' => 'active',
            'university_id' => $university->id,
        ]);
        $lecturer = User::factory()->create([
            'name' => 'Dr. Andi Wijaya, M.Kom.',
            'email' => 'Andi.Wijaya@unesa.ac.id',
            'role' => 'dosen',
            'status' => 'active',
            'university_id' => $university->id,
        ]);
        $student = User::factory()->create([
            'name' => 'Siti Rahmawati',
            'email' => 'Siti.Rahmawati@unesa.ac.id',
            'role' => 'mahasiswa',
            'status' => 'active',
            'university_id' => $university->id,
        ]);
        StudentProfile::create([
            'user_id' => $student->id,
            'university_id' => $university->id,
            'universitas' => $university->name,
            'nim' => '22051204099',
            'jurusan' => 'Teknik Informatika',
            'phone' => '081234567890',
        ]);
        Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->addDays(50)->toDateString(),
            'status' => ApplicationStatus::ACTIVE,
        ]);

        // 1. University\DashboardController search (lowercase, uppercase, mixed)
        foreach (['siti', 'SITI', 'informatika', 'INFORMATIKA', 'Teknik Informatika'] as $term) {
            $this->actingAs($univAdmin)
                ->get(route('university.dashboard', ['search' => $term]))
                ->assertOk()
                ->assertSee('Siti Rahmawati');
        }

        // 2. University\LecturerController search
        foreach (['andi', 'ANDI', 'Wijaya', 'wijaya'] as $term) {
            $this->actingAs($univAdmin)
                ->get(route('university.lecturers.index', ['search' => $term]))
                ->assertOk()
                ->assertSee($lecturer->name);
        }

        // 3. UniversityHubService search
        $hub = app(UniversityHubService::class);
        foreach (['siti', 'SITI', 'informatika', 'INFORMATIKA'] as $term) {
            [, $students] = $hub->build($university, '', $term, null);
            $this->assertTrue(
                $students->contains(fn (User $u) => $u->id === $student->id),
                "UniversityHubService harus menemukan mahasiswa untuk kata kunci '{$term}'."
            );
        }
    }

    public function test_mentor_decimal_standard_evaluation_scores_save_safely_on_postgresql(): void
    {
        $agency = AgencyProfile::first();
        if ($agency) {
            $agency->update([
                'agency_name' => 'dinas perpustakaan dan kearsipan',
                'acronym' => 'Dispusip',
            ]);
        } else {
            $agency = AgencyProfile::create([
                'agency_name' => 'dinas perpustakaan dan kearsipan',
                'acronym' => 'Dispusip',
            ]);
        }
        $this->assertSame($agency->id, AgencyProfile::certificateIssuerId());

        $unit = Unit::create([
            'agency_profile_id' => $agency->id,
            'name' => 'Bidang Sekretariat',
            'quota' => 10,
        ]);
        $mentor = User::factory()->create([
            'role' => 'mentor',
            'status' => 'active',
            'agency_profile_id' => $agency->id,
            'unit_id' => $unit->id,
        ]);
        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'status' => 'active',
        ]);
        $application = Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'start_date' => now()->subDays(15)->toDateString(),
            'end_date' => now()->addDays(45)->toDateString(),
            'status' => ApplicationStatus::ACTIVE,
        ]);
        $placement = Placement::create([
            'application_id' => $application->id,
            'mentor_id' => $mentor->id,
            'pembimbing_id' => $mentor->id,
        ]);

        $this->actingAs($mentor)
            ->post(route('mentor.evaluations.store', $placement->id), [
                'nilai_disiplin' => '88.5',
                'nilai_kinerja' => '90.25',
                'nilai_laporan' => '86.75',
                'catatan' => 'Evaluasi nilai desimal aman di PostgreSQL.',
            ])
            ->assertRedirect();

        $eval = $placement->fresh()->evaluation;
        $this->assertNotNull($eval);
        $this->assertSame(89, (int) $eval->nilai_disiplin);
        $this->assertSame(90, (int) $eval->nilai_kinerja);
        $this->assertSame(87, (int) $eval->nilai_laporan);
        $this->assertEquals(88.5, $eval->mentor_aspects[0]['score']);
    }
}
