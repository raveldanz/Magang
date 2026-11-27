<?php

namespace Tests\Feature;

use App\Models\University;
use App\Models\User;
use App\Services\MasterUniversityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectorySchoolRulesTest extends TestCase
{
    use RefreshDatabase;

    private function upsert(string $npsn, string $name): array
    {
        return app(MasterUniversityService::class)->upsertDirectorySchool(['npsn' => $npsn, 'name' => $name]);
    }

    private function school(string $name, ?string $code, string $address): University
    {
        return University::create(['name' => $name, 'code' => $code, 'address' => $address, 'institution_type' => University::TYPE_SCHOOL, 'is_verified' => true, 'status' => 'active']);
    }

    private function profilePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Budi Siswa', 'nim' => '123456', 'education_level' => 'sekolah', 'university_id' => 'other',
            'custom_university_name' => 'SMA PGRI 1', 'jurusan' => 'IPA', 'semester' => '11', 'phone' => '08123456789',
        ], $overrides);
    }

    public function test_dua_sekolah_bernama_sama_beda_npsn_tersimpan_keduanya(): void
    {
        $this->upsert('90000001', 'SMA PGRI 1');
        $second = $this->upsert('90000002', 'SMA PGRI 1');

        $this->assertTrue($second['created']);
        $this->assertSame(2, University::count());
    }

    public function test_nama_sama_tanpa_kode_tidak_membuktikan_identitas(): void
    {
        $manual = University::create(['name' => 'SMK Manual', 'code' => null, 'institution_type' => University::TYPE_SCHOOL, 'is_verified' => false, 'status' => 'active']);

        $result = $this->upsert('90000003', 'SMK Manual');

        $this->assertSame(2, University::count());
        $this->assertNull($manual->fresh()->code);
        $this->assertFalse($manual->fresh()->is_verified);
        $this->assertNull($result['matched_by']);
        $this->assertTrue($result['created']);
    }

    public function test_hasil_upsert_menyebut_cara_cocok_dan_kolom_yang_dilengkapi(): void
    {
        $this->assertNull($this->upsert('90000004', 'SMA BARU')['matched_by']);
        $again = $this->upsert('90000004', 'SMA BARU');

        $this->assertSame('npsn', $again['matched_by']);
        $this->assertFalse($again['filled']);
    }

    public function test_quick_import_tanpa_npsn_tidak_membuat_kode_acak_atau_data_bawaan(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin', 'agency_profile_id' => null]);

        $this->actingAs($admin)->post(route('admin.universities.quick_import'), [
            'name' => 'SMA CONTOH', 'institution_type' => University::TYPE_SCHOOL,
        ])->assertRedirect();

        $school = University::where('name', 'SMA CONTOH')->firstOrFail();
        $this->assertNull($school->code);
        $this->assertNull($school->address);
    }

    public function test_input_manual_yang_cocok_beberapa_sekolah_meminta_memilih(): void
    {
        $this->school('SMA PGRI 1', '90000001', 'Jl. A, Surabaya, Jawa Timur');
        $this->school('SMA PGRI 1', '90000002', 'Jl. B, Madiun, Jawa Timur');
        $student = User::factory()->create(['role' => 'mahasiswa']);

        $response = $this->actingAs($student)->post(route('student.profile.update'), $this->profilePayload());

        $response->assertSessionHasErrors('custom_university_name');
        $names = collect(session('university_suggestions'))->pluck('name');
        $this->assertEqualsCanonicalizing(['SMA PGRI 1 · Surabaya', 'SMA PGRI 1 · Madiun'], $names->all());
        $this->assertSame(2, University::count());
    }

    public function test_sekolah_berbeda_bernama_sama_bisa_didaftarkan_dengan_konfirmasi(): void
    {
        $this->school('SMA PGRI 1', '90000001', 'Jl. A, Surabaya, Jawa Timur');
        $this->school('SMA PGRI 1', '90000002', 'Jl. B, Madiun, Jawa Timur');
        $student = User::factory()->create(['role' => 'mahasiswa']);

        $this->actingAs($student)
            ->post(route('student.profile.update'), $this->profilePayload(['confirm_new_university' => '1']))
            ->assertSessionHasNoErrors();

        $new = University::findOrFail($student->fresh()->university_id);
        $this->assertSame(3, University::count());
        $this->assertFalse($new->is_verified);
        $this->assertSame(University::TYPE_SCHOOL, $new->institution_type);
    }
}
