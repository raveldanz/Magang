<?php

namespace Tests\Feature;

use App\Console\Commands\SyncExternalInstitutions;
use App\Models\University;
use App\Models\User;
use App\Services\Directory\SchoolDirectoryClient;
use App\Services\MasterUniversityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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

    public function test_npsn_terverifikasi_mengoreksi_nama_lama_provider(): void
    {
        $client = app(SchoolDirectoryClient::class);
        $row = $client->normalizeRow($this->row('20529953', 'SMA PGRI SUMENEP', 'SMA'));
        $this->assertSame('SMA CHUNG CHUNG CHRISTIAN SCHOOL SURABAYA', $row['name']);
    }

    public function test_sync_menambah_sekolah_baru_dan_memperbarui_tanpa_menghapus(): void
    {
        config(['services.school_directory.base_url' => 'https://dir.test', 'services.school_directory.enabled' => true]);
        Storage::fake('local');
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
        $this->assertNotNull(SyncExternalInstitutions::lastFullSync());
        $this->assertFalse(SyncExternalInstitutions::isDue());
    }

    public function test_sinkron_uji_coba_tidak_dicatat_dan_jadwal_menyusul_setelah_tujuh_hari(): void
    {
        config(['services.school_directory.base_url' => 'https://dir.test', 'services.school_directory.enabled' => true]);
        Storage::fake('local');
        Http::fake(['dir.test/*' => Http::response(['status' => 'success', 'total_data' => 1, 'dataSekolah' => [$this->row('90000401', 'SMKN 401 UJI')]])]);

        // Belum pernah sinkron penuh: jatuh tempo.
        $this->assertTrue(SyncExternalInstitutions::isDue());

        // Uji coba sebagian (--max-pages / --form) tidak boleh menunda sinkron penuh.
        $this->artisan('institutions:sync-external', ['--max-pages' => 1])->assertSuccessful();
        $this->artisan('institutions:sync-external', ['--form' => ['SMK']])->assertSuccessful();
        $this->assertNull(SyncExternalInstitutions::lastFullSync());
        $this->assertTrue(SyncExternalInstitutions::isDue());

        $this->artisan('institutions:sync-external')->assertSuccessful();
        $this->assertFalse(SyncExternalInstitutions::isDue());

        $this->travel(8)->days();
        $this->assertTrue(SyncExternalInstitutions::isDue());
    }

    public function test_sync_gagal_api_tidak_merusak_data_lokal(): void
    {
        config(['services.school_directory.base_url' => 'https://dir.test', 'services.school_directory.enabled' => true]);
        University::create(['name' => 'SMA Lama', 'code' => '11111111', 'institution_type' => University::TYPE_SCHOOL, 'is_verified' => true, 'status' => 'active']);
        Http::fake(['dir.test/*' => Http::response('error', 500)]);

        $this->artisan('institutions:sync-external', ['--form' => ['SMK']])->assertFailed();
        $this->assertDatabaseHas('universities', ['code' => '11111111']);
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

        $user = User::factory()->create(['role' => 'mahasiswa']);
        $this->actingAs($user)->getJson(route('institutions.search', ['q' => 'async']))
            ->assertOk()
            ->assertJsonPath('data.0.id', $univ->id)
            ->assertJsonPath('data.0.name', 'Universitas Async Uji');
    }

    public function test_upsert_direktori_tidak_menimpa_keputusan_moderasi_admin(): void
    {
        // Sekolah yang sengaja dinonaktifkan/diturunkan admin tidak boleh hidup lagi oleh sinkron API.
        $ditolak = University::create(['name' => 'SMKN 50 DITOLAK', 'code' => '90000050', 'institution_type' => University::TYPE_SCHOOL, 'is_verified' => false, 'status' => 'inactive']);
        // Kampus manual bernama sama tanpa NPSN: tidak diubah tipe/verifikasinya, hanya diisi NPSN.
        $manual = University::create(['name' => 'SMK Nama Sama', 'code' => null, 'institution_type' => University::TYPE_COLLEGE, 'is_verified' => false, 'status' => 'pending']);

        $svc = app(MasterUniversityService::class);
        $svc->upsertDirectorySchool(['npsn' => '90000050', 'name' => 'SMKN 50 DITOLAK', 'address' => 'Jl. Baru']);
        $svc->upsertDirectorySchool(['npsn' => '90000051', 'name' => 'SMK Nama Sama']);

        $ditolak->refresh();
        $manual->refresh();
        $this->assertFalse($ditolak->is_verified);
        $this->assertSame('inactive', $ditolak->status);
        $this->assertSame('Jl. Baru', $ditolak->address); // kolom kosong boleh dilengkapi
        $this->assertNull($manual->code);
        $this->assertDatabaseHas('universities', ['code' => '90000051', 'is_verified' => true]);
        $this->assertFalse($manual->is_verified);
        $this->assertSame('pending', $manual->status);
        $this->assertSame(University::TYPE_COLLEGE, $manual->institution_type);
    }

    public function test_listable_tidak_memuat_sekolah_sinkron_yang_belum_dipakai(): void
    {
        University::create(['name' => 'SMA Tak Terpakai', 'code' => '22222222', 'institution_type' => University::TYPE_SCHOOL, 'is_verified' => true, 'status' => 'active']);
        $used = University::create(['name' => 'SMA Terpakai', 'code' => '33333333', 'institution_type' => University::TYPE_SCHOOL, 'is_verified' => true, 'status' => 'active']);
        User::factory()->create(['role' => 'mahasiswa', 'university_id' => $used->id]);
        University::create(['name' => 'Kampus Daftar', 'code' => 'KD1', 'institution_type' => University::TYPE_COLLEGE, 'is_verified' => true, 'status' => 'active']);

        $names = University::listable()->pluck('name');
        $this->assertTrue($names->contains('SMA Terpakai'));
        $this->assertTrue($names->contains('Kampus Daftar'));
        $this->assertFalse($names->contains('SMA Tak Terpakai'));
    }
}
