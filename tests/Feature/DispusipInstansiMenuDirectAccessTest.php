<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DispusipInstansiMenuDirectAccessTest extends TestCase
{
    use RefreshDatabase;

    private function getOrCreateDispusip(): AgencyProfile
    {
        $dispusip = AgencyProfile::where('agency_name', 'like', '%Perpustakaan%')
            ->orWhere('acronym', 'like', '%Dispusip%')
            ->first();

        if (! $dispusip) {
            $dispusip = AgencyProfile::create([
                'agency_name' => 'Dinas Perpustakaan dan Kearsipan',
                'acronym' => 'Dispusip',
                'email' => 'admin.dispusip@surabaya.go.id',
                'address' => 'Jl. Tunjungan No. 1-3 (Gedung Siola) Lantai 4, Surabaya 60275',
            ]);
        }

        return $dispusip;
    }

    public function test_superadmin_accessing_agencies_index_redirects_directly_to_dispusip_show(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'agency_profile_id' => null]);
        $dispusip = $this->getOrCreateDispusip();

        $response = $this->actingAs($admin)->get(route('admin.agencies.index'));

        // Harus langsung dialihkan ke show Dispusip tanpa menampilkan halaman kartu
        $response->assertRedirect(route('admin.agencies.show', $dispusip->id));

        // Mengikuti redirect menghasilkan halaman pusat kendali lengkap Dispusip (200 OK)
        $followResponse = $this->actingAs($admin)->get(route('admin.agencies.show', $dispusip->id));
        $followResponse->assertStatus(200);
        $followResponse->assertSee('Pusat Kendali Instansi');
        $followResponse->assertSee('Dinas Perpustakaan dan Kearsipan');
        $followResponse->assertSee('Personil');
        $followResponse->assertSee('Divisi');
    }

    public function test_navigation_renders_direct_link_to_dispusip_show(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'agency_profile_id' => null]);
        $dispusip = $this->getOrCreateDispusip();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        // Link instansi di navigasi dan dashboard langsung mengarah ke show Dispusip
        $response->assertSee(route('admin.agencies.show', $dispusip->id));
    }

    public function test_impersonating_agency_admin_and_leaving_returns_to_agency_show_page(): void
    {
        $superAdmin = User::factory()->create(['role' => 'admin', 'agency_profile_id' => null]);
        $dispusip = $this->getOrCreateDispusip();
        $dinasAdmin = User::factory()->create([
            'role' => 'admin',
            'agency_profile_id' => $dispusip->id,
            'email' => 'admin.dispusip@surabaya.go.id',
        ]);

        // 1. Super Admin login as dinas admin dari halaman show Dispusip
        $agencyShowUrl = route('admin.agencies.show', $dispusip->id);
        $response = $this->actingAs($superAdmin)->post(route('admin.impersonate', $dinasAdmin->id), [
            'return_to' => $agencyShowUrl,
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertEquals($dinasAdmin->id, auth()->id());

        // 2. Saat klik tombol kembali (leave impersonation), harus kembali ke show Dispusip, BUKAN ke /admin/users
        $leaveResponse = $this->post(route('admin.impersonate.leave'));
        $leaveResponse->assertRedirect($agencyShowUrl);
        $this->assertEquals($superAdmin->id, auth()->id());
    }
}
