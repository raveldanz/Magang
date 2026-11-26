<?php

namespace Tests\Feature;

use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** Pencarian saat mengetik hanya membaca database: API hanya dipakai sinkron. */
class InstitutionSearchLocalOnlyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        config([
            'services.school_directory.enabled' => true,
            'services.school_directory.base_url' => 'https://dir.test',
            'services.college_directory.enabled' => true,
            'services.college_directory.base_url' => 'https://dir.test',
        ]);
        Http::fake();
    }

    public function test_pencarian_login_tidak_memanggil_api(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);

        $this->actingAs($user)->getJson(route('institutions.search', ['q' => 'tidak ada sama sekali']))
            ->assertOk()
            ->assertJsonPath('data', []);

        Http::assertNothingSent();
    }

    public function test_master_search_publik_tidak_memanggil_api(): void
    {
        $this->getJson(route('universities.master-search', ['q' => 'tidak ada sama sekali']))->assertOk();

        Http::assertNothingSent();
    }

    public function test_hasil_sekolah_menampilkan_kota(): void
    {
        University::create(['name' => 'SMKN 1 UJI KOTA', 'code' => '90000001', 'address' => 'Jl. A, Surabaya, Jawa Timur', 'institution_type' => University::TYPE_SCHOOL, 'is_verified' => true, 'status' => 'active']);
        $user = User::factory()->create(['role' => 'mahasiswa']);

        $this->actingAs($user)->getJson(route('institutions.search', ['q' => 'uji kota']))
            ->assertOk()
            ->assertJsonPath('data.0.meta', '90000001 · Surabaya');
    }

    public function test_saran_pembimbing_hanya_menampilkan_sekolah_terverifikasi_dan_mengenali_singkatan(): void
    {
        foreach ([true, false] as $verified) {
            University::create(['name' => 'SMKN 1 MADIUN', 'code' => $verified ? '90000001' : '90000002', 'institution_type' => University::TYPE_SCHOOL, 'is_verified' => $verified, 'status' => 'active']);
        }
        $this->getJson('/register/institutions?q=SMK%20Negeri%201%20Madiun')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'SMKN 1 MADIUN');
    }

    public function test_route_resolve_sudah_tidak_ada(): void
    {
        $this->assertFalse(Route::has('institutions.resolve'));
    }
}
