<?php

namespace Tests\Feature;

use App\Models\StudentProfile;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Revisi 16 Nov 2026: profil peserta magang punya pilihan Sekolah (SMK/SMA/MA) selain Perguruan Tinggi.
 */
class SchoolParticipantProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->student = User::factory()->create(['role' => 'mahasiswa']);
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'name' => 'Siswa Contoh',
            'nim' => '0061234567',
            'education_level' => 'sekolah',
            'university_id' => 'other',
            'custom_university_name' => 'SMK Negeri 5 Surabaya',
            'jurusan' => 'Otomatisasi Tata Kelola Perkantoran',
            'semester' => 11,
            'phone' => '081234567890',
        ], $override);
    }

    public function test_profile_page_offers_school_option(): void
    {
        $this->actingAs($this->student)->get(route('student.profile.edit'))
            ->assertOk()
            ->assertSee('Profil Peserta Magang')
            ->assertSee('value="sekolah"', false)
            ->assertSee('Siswa PKL SMK/SMA/MA')
            ->assertSee('Asal Kampus / Sekolah');
    }

    public function test_school_student_saves_profile_with_class_and_new_school(): void
    {
        $this->actingAs($this->student)
            ->post(route('student.profile.update'), $this->payload())
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $school = University::where('name', 'SMK Negeri 5 Surabaya')->firstOrFail();
        $this->assertSame(University::TYPE_SCHOOL, $school->institution_type);
        $this->assertFalse((bool) $school->is_verified);

        $profile = StudentProfile::where('user_id', $this->student->id)->firstOrFail();
        $this->assertTrue($profile->isSchoolStudent());
        $this->assertSame('NIS', $profile->identityNumberLabel());
        $this->assertSame('Kelas 11', $profile->periodLabel());
        $this->assertNull($profile->faculty);

        // Muat ulang pengguna: relasi profil pada objek uji masih versi sebelum disimpan
        $this->actingAs($this->student->fresh())->get(route('student.profile.edit'))
            ->assertOk()
            ->assertSee('Kelas 11')
            ->assertSee('Siswa PKL');
    }

    public function test_school_class_must_be_between_10_and_13(): void
    {
        $this->actingAs($this->student)
            ->post(route('student.profile.update'), $this->payload(['semester' => 5]))
            ->assertSessionHasErrors(['semester' => 'Kelas harus antara 10 sampai 13.']);
    }

    public function test_level_must_match_the_chosen_institution(): void
    {
        $school = University::create(['name' => 'SMA Negeri 2 Surabaya', 'is_verified' => true, 'institution_type' => University::TYPE_SCHOOL]);

        $this->actingAs($this->student)
            ->post(route('student.profile.update'), $this->payload([
                'education_level' => 'perguruan_tinggi',
                'university_id' => $school->id,
                'custom_university_name' => null,
                'semester' => 5,
            ]))
            ->assertSessionHasErrors('university_id');

        $this->actingAs($this->student)
            ->post(route('student.profile.update'), $this->payload([
                'university_id' => $school->id,
                'custom_university_name' => null,
            ]))
            ->assertSessionHasNoErrors();
        $this->assertSame($school->id, (int) $this->student->fresh()->university_id);
    }

    public function test_college_student_flow_is_unchanged(): void
    {
        $campus = University::create(['name' => 'Universitas Negeri Surabaya', 'code' => 'UNESA', 'acronym' => 'UNESA', 'is_verified' => true]);

        $this->actingAs($this->student)
            ->post(route('student.profile.update'), [
                'name' => 'Mahasiswa Contoh',
                'nim' => '22081010001',
                'university_id' => $campus->id,
                'faculty' => 'Teknik',
                'jurusan' => 'Teknik Informatika',
                'semester' => 5,
                'phone' => '081234567890',
            ])
            ->assertSessionHasNoErrors();

        $profile = StudentProfile::where('user_id', $this->student->id)->firstOrFail();
        $this->assertFalse($profile->isSchoolStudent());
        $this->assertSame('Semester 5', $profile->periodLabel());
        $this->assertSame('NIM', $profile->identityNumberLabel());
    }

    public function test_institution_type_is_guessed_from_name(): void
    {
        $this->assertSame(University::TYPE_SCHOOL, University::guessInstitutionType('SMKN 1 Surabaya'));
        $this->assertSame(University::TYPE_SCHOOL, University::guessInstitutionType('SMA Negeri 5 Surabaya'));
        $this->assertSame(University::TYPE_SCHOOL, University::guessInstitutionType('MAN 2 Kota Surabaya'));
        $this->assertSame(University::TYPE_COLLEGE, University::guessInstitutionType('Universitas Airlangga'));
        $this->assertSame(University::TYPE_COLLEGE, University::guessInstitutionType('Politeknik Elektronika Negeri Surabaya'));
    }

    public function test_student_role_label_uses_new_term(): void
    {
        $this->assertSame('Peserta Magang', $this->student->roleLabel());
    }
}
