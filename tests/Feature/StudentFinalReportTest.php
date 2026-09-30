<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Placement;
use App\Models\StudentProfile;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentFinalReportTest extends TestCase
{
    use RefreshDatabase;

    private function createStudentWithPlacement(): array
    {
        $univ = University::create([
            'name' => 'Universitas Dr. Soetomo',
            'code' => 'UNITOMO',
            'address' => 'Surabaya',
        ]);

        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'name' => 'Bagas Saputra',
            'email' => 'bagas.saputra@mhs.unitomo.ac.id',
        ]);

        StudentProfile::create([
            'user_id' => $student->id,
            'nim' => '2201001',
            'universitas' => 'Universitas Dr. Soetomo',
            'jurusan' => 'Teknik Informatika',
            'phone' => '081234567890',
        ]);

        $dpl = User::factory()->create([
            'role' => 'lecturer',
            'name' => 'Dr. Bambang',
            'email' => 'bambang@unitomo.ac.id',
            'university_id' => $univ->id,
        ]);

        $agency = AgencyProfile::create([
            'name' => 'Dinas Komunikasi dan Informatika Surabaya',
            'address' => 'Jl. Jimerto',
        ]);

        $unit = \App\Models\Unit::create([
            'agency_profile_id' => $agency->id,
            'name' => 'Bidang Aplikasi dan Tata Kelola Informatika',
        ]);

        $application = Application::create([
            'user_id' => $student->id,
            'agency_profile_id' => $agency->id,
            'unit_id' => $unit->id,
            'status' => ApplicationStatus::ACCEPTED,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(2),
        ]);

        $placement = Placement::create([
            'application_id' => $application->id,
            'agency_profile_id' => $agency->id,
            'academic_advisor_id' => $dpl->id,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(2),
        ]);

        return [$student, $application, $placement];
    }

    public function test_accepted_student_with_dpl_can_access_final_report_form()
    {
        [$student] = $this->createStudentWithPlacement();

        $response = $this->actingAs($student)->get(route('student.final_report.index'));

        $response->assertStatus(200);
        $response->assertSee('Formulir Pengunggahan Laporan Akhir');
        $response->assertSee('file_laporan');
        $response->assertSee('reportFileBox');
    }

    public function test_accepted_student_can_upload_final_report()
    {
        Storage::fake('public');

        [$student] = $this->createStudentWithPlacement();

        $file = UploadedFile::fake()->create('Laporan_Akhir_Bagas.pdf', 500, 'application/pdf');

        $response = $this->actingAs($student)->post(route('student.final_report.store'), [
            'title' => 'Laporan Akhir Bagas Saputra - PKL Kominfo',
            'file_laporan' => $file,
            'repository_url' => 'https://github.com/bagas/magang-kominfo',
        ]);

        $response->assertRedirect(route('student.final_report.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('final_reports', [
            'title' => 'Laporan Akhir Bagas Saputra - PKL Kominfo',
            'repository_url' => 'https://github.com/bagas/magang-kominfo',
        ]);
    }
}
