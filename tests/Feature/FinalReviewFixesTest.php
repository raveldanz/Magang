<?php

namespace Tests\Feature;

use App\Models\InstitutionChangeProposal;
use App\Models\University;
use App\Models\User;
use App\Services\Institutions\SchoolSnapshotService;
use App\Services\UniversityResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Perbaikan dari pemeriksaan akhir branch sinkron institusi. */
class FinalReviewFixesTest extends TestCase
{
    use RefreshDatabase;

    private function school(string $name, ?string $code, ?string $address, array $extra = []): University
    {
        return University::create($extra + ['name' => $name, 'code' => $code, 'address' => $address, 'institution_type' => University::TYPE_SCHOOL, 'is_verified' => true, 'status' => 'active']);
    }

    private function twins(): array
    {
        return [
            $this->school('SMA PGRI 1', '90000001', 'Jl. A, Surabaya, Jawa Timur'),
            $this->school('SMA PGRI 1', '90000002', 'Jl. B, Madiun, Jawa Timur'),
        ];
    }

    private function register(string $institution)
    {
        return $this->post(route('register'), [
            'name' => 'GURU UJI', 'email' => 'guru.uji@demo.sch.id', 'password' => 'Rahasia-Uji-123', 'password_confirmation' => 'Rahasia-Uji-123',
            'register_as' => 'advisor', 'university' => $institution,
        ]);
    }

    public function test_beda_tanda_baca_tetap_dianggap_nama_kembar_di_profil(): void
    {
        $this->twins();
        $student = User::factory()->create(['role' => 'mahasiswa']);

        $this->actingAs($student)->post(route('student.profile.update'), [
            'name' => 'Budi', 'nim' => '123456', 'education_level' => 'sekolah', 'university_id' => 'other',
            'custom_university_name' => 'SMA-PGRI 1.', 'jurusan' => 'IPA', 'semester' => '11', 'phone' => '08123456789',
        ])->assertSessionHasErrors('custom_university_name');

        $this->assertCount(2, session('university_suggestions'));
    }

    public function test_daftar_pembimbing_tidak_memilih_sembarang_sekolah_kembar(): void
    {
        $this->twins();

        $this->register('SMA PGRI 1')->assertSessionHasErrors('university');

        $this->assertDatabaseMissing('users', ['email' => 'guru.uji@demo.sch.id']);
        $this->assertStringContainsString('SMA PGRI 1 · Madiun', session('errors')->first('university'));
        $this->assertSame(2, University::count());
    }

    public function test_daftar_pembimbing_dengan_kota_memilih_sekolah_yang_tepat(): void
    {
        [, $madiun] = $this->twins();

        $this->register('SMA PGRI 1 · Madiun')->assertSessionHasNoErrors();

        $this->assertSame($madiun->id, (int) User::where('email', 'guru.uji@demo.sch.id')->value('university_id'));
        $this->assertSame(2, University::count());
    }

    public function test_daftar_pembimbing_dengan_kota_lain_tidak_buntu(): void
    {
        $this->twins();

        $this->register('SMA PGRI 1 · Kediri')->assertSessionHasNoErrors();

        $new = University::findOrFail(User::where('email', 'guru.uji@demo.sch.id')->value('university_id'));
        $this->assertFalse($new->is_verified);
        $this->assertSame('SMA PGRI 1 · Kediri', $new->name);
    }

    public function test_saran_tidak_memindai_dengan_kata_umum_dan_tetap_menemukan(): void
    {
        $target = $this->school('SMA AL HIKMAH SURABAYA', '90000003', null);
        $patterns = [];
        DB::listen(function ($query) use (&$patterns) {
            array_push($patterns, ...array_filter($query->bindings, 'is_string'));
        });

        $found = app(UniversityResolver::class)->suggest('SMA Negeri Al Hikmah Surabaya', 3)->pluck('university.id');

        $this->assertTrue($found->contains($target->id));
        foreach (['%sma%', '%nege%', '%al%'] as $broad) {
            $this->assertNotContains($broad, $patterns);
        }
    }

    public function test_saran_tetap_jalan_bila_hanya_berisi_kata_umum(): void
    {
        $this->school('SMA 5', '90000004', null);

        $this->assertNotEmpty(app(UniversityResolver::class)->suggest('SMA 5', 3));
    }

    public function test_impor_tidak_menebak_npsn_untuk_nama_yang_kembar(): void
    {
        $manual = $this->school('SMAS MUHAMMADIYAH 1', null, null);
        $path = storage_path('framework/testing/kembar-'.uniqid().'.json.gz');
        @mkdir(dirname($path), 0777, true);
        file_put_contents($path, gzencode(json_encode(['generated_at' => '2026-11-25T10:00:00+07:00', 'rows' => [
            ['npsn' => '90000005', 'name' => 'SMAS MUHAMMADIYAH 1', 'address' => 'Jl. A, Padang, Sumatera Barat'],
            ['npsn' => '90000006', 'name' => 'SMAS MUHAMMADIYAH 1', 'address' => 'Jl. B, Surabaya, Jawa Timur'],
        ]])));

        $result = app(SchoolSnapshotService::class)->import($path);
        @unlink($path);

        $this->assertSame(['created' => 2, 'filled' => 0, 'skipped' => 0], $result);
        $this->assertNull($manual->fresh()->code);
    }

    public function test_halaman_usul_menampilkan_nilai_sekarang_yang_hidup_dan_nama_dicentang_satu_per_satu(): void
    {
        $school = $this->school('SMK LAMA', '90000007', 'Jl. Awal, Surabaya, Jawa Timur');
        InstitutionChangeProposal::create(['university_id' => $school->id, 'kind' => 'name', 'current_value' => 'SMK LAMA', 'proposed_value' => 'SMK API', 'fingerprint' => sha1('n'), 'status' => 'pending']);
        InstitutionChangeProposal::create(['university_id' => $school->id, 'kind' => 'address', 'current_value' => 'Jl. Awal, Surabaya, Jawa Timur', 'proposed_value' => 'Jl. API', 'fingerprint' => sha1('a'), 'status' => 'pending']);
        $school->update(['name' => 'SMK KOREKSI ADMIN', 'address' => 'Jl. Koreksi Admin']);
        $admin = User::factory()->create(['role' => 'super_admin', 'agency_profile_id' => null]);

        $response = $this->actingAs($admin)->get(route('admin.universities.sync.proposals'))->assertOk();

        $response->assertSeeInOrder(['Sekarang', 'SMK KOREKSI ADMIN', 'SMK API']);
        $response->assertSeeInOrder(['Sekarang', 'Jl. Koreksi Admin', 'Jl. API']);
        $response->assertSee('kecuali perubahan nama');
        $this->assertSame(1, substr_count($response->getContent(), 'data-manual="1"'));
    }
}
