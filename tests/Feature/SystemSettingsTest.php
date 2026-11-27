<?php

namespace Tests\Feature;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_updates_registration_and_letter_settings(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $this->get('/admin/pengaturan-sistem')->assertOk()->assertSee('Narahubung surat balasan');
        $this->put('/admin/pengaturan-sistem', ['group_limit' => 15, 'letter_contact' => 'Bu Rina'])->assertSessionHasNoErrors();
        $this->assertSame(15, Unit::groupLimit());
        $this->assertDatabaseHas('registration_settings', ['letter_contact' => 'Bu Rina']);
    }

    public function test_admin_instansi_tidak_dapat_mengubah_pengaturan_sistem(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'agency_profile_id' => \App\Models\AgencyProfile::create(['agency_name' => 'Dinas Uji'])->id]));
        $this->put('/admin/pengaturan-sistem', ['group_limit' => 1, 'letter_contact' => 'Nama'])->assertForbidden();
    }
}
