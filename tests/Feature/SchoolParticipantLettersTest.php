<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Placement;
use App\Models\StudentProfile;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Surat untuk peserta dari sekolah (SMK/SMA/MA) memakai istilah sekolah: Kepala Sekolah, PKL,
 * NIS, jurusan & kelas; bukan "Universitas Saudara", "Dekan", atau "MBKM".
 */
class SchoolParticipantLettersTest extends TestCase
{
    use RefreshDatabase;

    private function scenario(bool $school): array
    {
        $agency = AgencyProfile::create(['agency_name' => 'Dinas Perpustakaan dan Kearsipan']);
        $unit = Unit::create(['agency_profile_id' => $agency->id, 'name' => 'Bidang Arsip', 'quota' => 10]);
        $institution = University::create($school
            ? ['name' => 'SMK NEGERI 7 SURABAYA', 'code' => '20532200', 'acronym' => 'SMKN 7 SURABAYA', 'is_verified' => true, 'institution_type' => University::TYPE_SCHOOL]
            : ['name' => 'Universitas Negeri Surabaya', 'code' => 'UNESA', 'acronym' => 'UNESA', 'is_verified' => true]);
        $student = User::factory()->create(['role' => 'mahasiswa', 'university_id' => $institution->id, 'university' => $institution->name]);
        StudentProfile::create([
            'user_id' => $student->id,
            'nim' => $school ? '0061234567' : '22081010001',
            'universitas' => $institution->name,
            'university_id' => $institution->id,
            'jurusan' => $school ? 'Otomatisasi Tata Kelola Perkantoran' : 'Teknik Informatika',
            'fakultas' => $school ? null : 'Teknik',
            'semester' => $school ? 11 : 5,
            'education_level' => $school ? University::TYPE_SCHOOL : University::TYPE_COLLEGE,
            'phone' => '0812',
        ]);
        $application = Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'status' => 'accepted',
            'start_date' => Carbon::now()->addDays(3)->toDateString(),
            'end_date' => Carbon::now()->addDays(60)->toDateString(),
        ]);
        Placement::create(['application_id' => $application->id]);

        return [$student, $application, $institution];
    }

    public function test_acceptance_letter_for_school_student_uses_school_wording(): void
    {
        [$student, $application] = $this->scenario(true);

        $html = $this->actingAs($student)->get(route('student.application.letter', $application->id))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Yth. Kepala', $html);
        $this->assertStringContainsString('dari Sekolah Saudara', $html);
        $this->assertStringContainsString('Praktik Kerja Lapangan (PKL)', $html);
        $this->assertStringContainsString('>NIS<', $html);
        $this->assertStringContainsString('Kelas 11', $html);
        $this->assertStringNotContainsString('Universitas Saudara', $html);
        $this->assertStringNotContainsString('Yth. Dekan', $html);
        $this->assertStringNotContainsString('>Fakultas<', $html);
    }

    public function test_acceptance_letter_for_college_student_is_unchanged(): void
    {
        [$student, $application] = $this->scenario(false);

        $html = $this->actingAs($student)->get(route('student.application.letter', $application->id))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Yth. Dekan Teknik', $html);
        $this->assertStringContainsString('dari Universitas Saudara', $html);
        $this->assertStringContainsString('>NIM<', $html);
        $this->assertStringContainsString('Semester 5', $html);
    }

    public function test_school_cover_letter_has_no_mbkm_or_campus_wording(): void
    {
        [, $application, $school] = $this->scenario(true);
        $schoolAdmin = User::factory()->create(['role' => 'universitas', 'university_id' => $school->id]);

        $html = $this->actingAs($schoolAdmin)->get(route('university.students.letter', $application->id))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Praktik Kerja Lapangan (PKL)', $html);
        $this->assertStringContainsString('Kepala <strong>SMK NEGERI 7 SURABAYA</strong>', $html);
        $this->assertStringContainsString('Jurusan / Kelas', $html);
        $this->assertStringNotContainsString('Merdeka Belajar Kampus Merdeka', $html);
        $this->assertStringNotContainsString('Fakultas / Program Studi', $html);
    }

    public function test_letter_verification_page_labels_school_identity(): void
    {
        [, $application] = $this->scenario(true);
        $application->update(['letter_token' => 'tokensekolah123']);

        $this->get('/verify-letter/tokensekolah123')
            ->assertOk()
            ->assertSee('Sekolah / Jurusan')
            ->assertSee('NIS');
    }
}
