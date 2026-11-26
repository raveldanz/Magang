<?php

namespace Tests\Feature;

use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Dengan puluhan ribu sekolah, pilihan tujuan gabung dicari lewat server, tidak ditanam di halaman. */
class AdminMergeTargetSearchTest extends TestCase
{
    use RefreshDatabase;

    private function make(string $name, array $extra = []): University
    {
        return University::create($extra + ['name' => $name, 'institution_type' => University::TYPE_SCHOOL, 'is_verified' => true, 'status' => 'active']);
    }

    public function test_saran_dobel_dibatasi_agar_halaman_tetap_ringan(): void
    {
        foreach (range(1, 12) as $i) {
            $this->make(sprintf('SMK MENUNGGU %02d', $i), ['is_verified' => false]);
        }
        $admin = User::factory()->create(['role' => 'super_admin', 'agency_profile_id' => null]);

        $response = $this->actingAs($admin)->get(route('admin.universities.index'))->assertOk();

        $this->assertCount(8, $response->viewData('mergeSuggestions'));
        $this->assertSame(12, substr_count($response->getContent(), 'name="target_university_id"'));
    }

    public function test_halaman_hanya_memuat_saran_dobel_bukan_seluruh_sekolah(): void
    {
        foreach (range(1, 30) as $i) {
            $this->make(sprintf('SEKOLAH AWAL %02d', $i));
        }
        $this->make('ZZZ SEKOLAH TIDAK TERKAIT');
        $similar = $this->make('SMK PEDALAMAN NUSANTARA');
        $pending = $this->make('SMK PEDALAMAN NUSANTARA JAYA', ['is_verified' => false]);
        $admin = User::factory()->create(['role' => 'super_admin', 'agency_profile_id' => null]);

        $response = $this->actingAs($admin)->get(route('admin.universities.index'))->assertOk();

        $response->assertDontSee('ZZZ SEKOLAH TIDAK TERKAIT');
        $response->assertSee('Kemungkinan dobel');
        $response->assertSee($similar->name);
        $response->assertSee('name="target_university_id"', false);
        $response->assertSee(str_replace('/', '\/', route('institutions.search')), false);

        $this->actingAs($admin)->post(route('admin.universities.merge', $pending->id), ['target_university_id' => $similar->id]);
        $this->assertNull($pending->fresh());
    }
}
