<?php

namespace Tests\Feature;

use App\Models\University;
use App\Services\MasterUniversityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExternalInstitutionSyncTest extends TestCase
{
    use RefreshDatabase;

    private function row(string $npsn, string $name, string $form = 'SMK'): array
    {
        return [
            'npsn' => $npsn, 'sekolah' => $name, 'bentuk' => $form, 'status' => 'N',
            'alamat_jalan' => 'Jl. Contoh 1', 'kabupaten_kota' => 'Kota Surabaya', 'propinsi' => 'Prov. Jawa Timur',
        ];
    }

    public function test_sync_menambah_sekolah_baru_dan_memperbarui_tanpa_menghapus(): void
    {
        config(['services.school_directory.base_url' => 'https://dir.test', 'services.school_directory.enabled' => true]);
        University::create(['name' => 'Kampus Manual', 'code' => 'KM1', 'institution_type' => University::TYPE_COLLEGE, 'is_verified' => true, 'status' => 'active']);

        Http::fake([
            'dir.test/sekolah/SMK*' => Http::response(['status' => 'success', 'total_data' => 2, 'dataSekolah' => [
                $this->row('90000001', 'SMKN 99 CONTOH'), $this->row('90000002', 'SMKS TEST BARU'),
            ]]),
            'dir.test/*' => Http::response(['status' => 'success', 'total_data' => 0, 'dataSekolah' => []]),
        ]);

        $this->artisan('institutions:sync-external')->assertSuccessful();
        $this->assertSame(2, University::where('institution_type', University::TYPE_SCHOOL)->count());
        $this->assertDatabaseHas('universities', ['code' => '90000001', 'name' => 'SMKN 99 CONTOH']);

        // Sinkron ulang tidak menggandakan dan tidak menghapus data manual.
        $this->artisan('institutions:sync-external')->assertSuccessful();
        $this->assertSame(2, University::where('institution_type', University::TYPE_SCHOOL)->count());
        $this->assertDatabaseHas('universities', ['code' => 'KM1']);
        $this->assertNotNull(Cache::get('institutions:last-external-sync'));
    }

    public function test_sync_gagal_api_tidak_merusak_data_lokal(): void
    {
        config(['services.school_directory.base_url' => 'https://dir.test', 'services.school_directory.enabled' => true]);
        University::create(['name' => 'SMA Lama', 'code' => '11111111', 'institution_type' => University::TYPE_SCHOOL, 'is_verified' => true, 'status' => 'active']);
        Http::fake(['dir.test/*' => Http::response('error', 500)]);

        $this->artisan('institutions:sync-external', ['--form' => ['SMK']])->assertFailed();
        $this->assertDatabaseHas('universities', ['code' => '11111111']);
    }

    public function test_pencarian_menambahkan_sekolah_dari_api_lalu_tersimpan_saat_dipilih(): void
    {
        config(['services.school_directory.base_url' => 'https://dir.test', 'services.school_directory.enabled' => true]);
        Cache::flush();
        Http::fake(['dir.test/sekolah/s*' => Http::response(['status' => 'success', 'dataSekolah' => [
            $this->row('90000077', 'SMKN 77 UJI PENCARIAN'),
        ]])]);

        $service = app(MasterUniversityService::class);
        $hit = $service->search('uji pencarian', 10)->firstWhere('npsn', '90000077');
        $this->assertNotNull($hit);
        $this->assertFalse($hit['is_in_db']);
        $this->assertDatabaseMissing('universities', ['code' => '90000077']);

        $service->resolveToUniversity($hit);
        $this->assertDatabaseHas('universities', ['code' => '90000077']);
    }

    public function test_pencarian_tetap_jalan_saat_api_mati(): void
    {
        config(['services.school_directory.base_url' => 'https://dir.test', 'services.school_directory.enabled' => true]);
        Cache::flush();
        Http::fake(['dir.test/*' => Http::response('x', 503)]);
        University::create(['name' => 'Universitas Uji Lokal', 'code' => 'UUL', 'institution_type' => University::TYPE_COLLEGE, 'is_verified' => true, 'status' => 'active']);

        $names = app(MasterUniversityService::class)->search('Uji Lokal', 10)->pluck('name');
        $this->assertTrue($names->contains('Universitas Uji Lokal'));
    }

    public function test_endpoint_pencarian_async_butuh_login_dan_mengembalikan_id_nyata(): void
    {
        config(['services.school_directory.enabled' => false]);
        $univ = University::create(['name' => 'Universitas Async Uji', 'code' => 'UAU', 'institution_type' => University::TYPE_COLLEGE, 'is_verified' => true, 'status' => 'active']);

        $this->getJson(route('institutions.search', ['q' => 'async']))->assertRedirect(); // tamu dialihkan ke halaman login

        $user = \App\Models\User::factory()->create(['role' => 'mahasiswa']);
        $this->actingAs($user)->getJson(route('institutions.search', ['q' => 'async']))
            ->assertOk()
            ->assertJsonPath('data.0.id', $univ->id)
            ->assertJsonPath('data.0.name', 'Universitas Async Uji');
    }

    public function test_endpoint_menyimpan_sekolah_hasil_api_agar_punya_id(): void
    {
        config(['services.school_directory.base_url' => 'https://dir.test', 'services.school_directory.enabled' => true]);
        Cache::flush();
        Http::fake(['dir.test/sekolah/s*' => Http::response(['status' => 'success', 'dataSekolah' => [$this->row('90000123', 'SMKN 123 ASYNC')]])]);
        $admin = \App\Models\User::factory()->create(['role' => 'super_admin']);

        $res = $this->actingAs($admin)->getJson(route('institutions.search', ['q' => 'smkn 123 async']))->assertOk();
        $this->assertSame('SMKN 123 ASYNC', $res->json('data.0.name'));
        $this->assertDatabaseHas('universities', ['code' => '90000123']);
    }

    public function test_listable_tidak_memuat_sekolah_sinkron_yang_belum_dipakai(): void
    {
        University::create(['name' => 'SMA Tak Terpakai', 'code' => '22222222', 'institution_type' => University::TYPE_SCHOOL, 'is_verified' => true, 'status' => 'active']);
        $used = University::create(['name' => 'SMA Terpakai', 'code' => '33333333', 'institution_type' => University::TYPE_SCHOOL, 'is_verified' => true, 'status' => 'active']);
        \App\Models\User::factory()->create(['role' => 'mahasiswa', 'university_id' => $used->id]);
        University::create(['name' => 'Kampus Daftar', 'code' => 'KD1', 'institution_type' => University::TYPE_COLLEGE, 'is_verified' => true, 'status' => 'active']);

        $names = University::listable()->pluck('name');
        $this->assertTrue($names->contains('SMA Terpakai'));
        $this->assertTrue($names->contains('Kampus Daftar'));
        $this->assertFalse($names->contains('SMA Tak Terpakai'));
    }}
