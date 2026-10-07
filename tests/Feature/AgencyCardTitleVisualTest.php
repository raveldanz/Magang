<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgencyCardTitleVisualTest extends TestCase
{
    use RefreshDatabase;

    public function test_agency_card_renders_with_line_clamp_3_and_correct_height(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        AgencyProfile::create(['agency_name' => 'Badan Kepegawaian dan Pengembangan Sumber Daya Manusia']);
        
        $response = $this->actingAs($admin)->get(route('admin.agencies.index'));
        
        $response->assertStatus(200);
        $response->assertSee('line-clamp-3');
        $response->assertSee('h-[4.25rem]');
    }

    public function test_university_card_renders_with_line_clamp_3_and_correct_height(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        \App\Models\University::create(['name' => 'Universitas Pembangunan Nasional Veteran Jawa Timur']);
        
        $response = $this->actingAs($admin)->get(route('admin.universities.index'));
        
        $response->assertStatus(200);
        $response->assertSee('line-clamp-3');
        $response->assertSee('h-[4.25rem]');
    }
}
