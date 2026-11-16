<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Temuan QC peran Super Admin dan halaman publik (16 Nov 2026):
 *  - hak Super Admin pada profil dinas ditentukan dari alamat email yang ditulis di kode,
 *  - halaman galat 405 masih bawaan berbahasa Inggris.
 */
class SuperAdminPublicQcFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_tertentu_tidak_lagi_memberi_hak_super_admin_pada_profil_dinas(): void
    {
        $own = AgencyProfile::create(['agency_name' => 'Dinas Sendiri']);
        $other = AgencyProfile::create(['agency_name' => 'Dinas Lain']);

        // Admin Dinas biasa yang kebetulan memakai email lama tetap dibatasi ke instansinya.
        $agencyAdmin = User::factory()->create(['role' => 'admin', 'email' => 'admin@gmail.com', 'agency_profile_id' => $own->id]);

        $this->actingAs($agencyAdmin)->get(route('admin.agency_profile.edit', ['agency_id' => $other->id]))->assertForbidden();
        $this->actingAs($agencyAdmin)->get(route('admin.agency_profile.edit'))->assertOk()->assertSee('Dinas Sendiri');
        $this->actingAs($agencyAdmin)->post(route('admin.agency_profile.update'), [
            'agency_id' => $other->id,
            'government_name' => 'Pemerintah Kota Uji',
            'agency_name' => 'Dinas Lain Dibajak',
            'signee_name' => 'Nama',
            'signee_position' => 'Jabatan',
            'city' => 'Surabaya',
        ]);
        $this->assertSame('Dinas Lain', $other->fresh()->agency_name);
    }

    public function test_super_admin_tetap_bisa_membuka_profil_dinas_mana_pun(): void
    {
        $other = AgencyProfile::create(['agency_name' => 'Dinas Lain']);
        $superAdmin = User::factory()->create(['role' => 'super_admin', 'email' => 'kepala.sistem@uji.test']);

        $this->actingAs($superAdmin)->get(route('admin.agency_profile.edit', ['agency_id' => $other->id]))
            ->assertOk()
            ->assertSee('Dinas Lain');
    }

    public function test_halaman_galat_405_berbahasa_indonesia(): void
    {
        $this->put('/login')
            ->assertStatus(405)
            ->assertSee('Tindakan Tidak Diizinkan')
            ->assertDontSee('Method Not Allowed');
    }
}
