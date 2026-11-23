<?php

namespace Tests\Feature;

use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUniversityQuickImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_dapat_mengimpor_sekolah_cepat_dari_direktori_ke_mitra(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
            'agency_profile_id' => null,
        ]);

        $res = $this->actingAs($admin)->post(route('admin.universities.quick_import'), [
            'name' => 'SMK NEGERI 10 SURABAYA',
            'npsn' => '20532204',
            'form' => 'SMK',
            'city' => 'Kota Surabaya',
            'province' => 'Jawa Timur',
            'institution_type' => University::TYPE_SCHOOL,
        ]);

        $res->assertRedirect();
        $this->assertDatabaseHas('universities', [
            'name' => 'SMK NEGERI 10 SURABAYA',
            'code' => '20532204',
            'institution_type' => University::TYPE_SCHOOL,
            'is_verified' => true,
        ]);
    }

    public function test_pencarian_admin_menampilkan_rekomendasi_direktori_nasional(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
            'agency_profile_id' => null,
        ]);

        $res = $this->actingAs($admin)->get(route('admin.universities.index', ['search' => 'smkn 10']));
        $res->assertOk();
        $res->assertSee('Ditemukan di Direktori Nasional');
        $res->assertSee('SMK NEGERI 10 SURABAYA');
    }
}
