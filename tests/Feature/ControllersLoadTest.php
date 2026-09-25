<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Memastikan semua controller bisa dimuat (tidak ada fatal error seperti bentrok
 * nama/visibility method dengan base Controller) dan halaman profil dinas terbuka.
 */
class ControllersLoadTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_route_controller_class_can_be_loaded(): void
    {
        $classes = collect(Route::getRoutes()->getRoutes())
            ->map(fn ($route) => $route->getAction('controller'))
            ->filter(fn ($action) => is_string($action))
            ->map(fn ($action) => explode('@', $action)[0])
            ->unique();

        $this->assertNotEmpty($classes);

        foreach ($classes as $class) {
            $this->assertTrue(class_exists($class), "Controller {$class} gagal dimuat");
        }
    }

    public function test_admin_dinas_can_open_agency_profile_page(): void
    {
        $agency = AgencyProfile::create(['agency_name' => 'Dinas Uji']);
        $admin = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $agency->id]);

        $this->actingAs($admin)->get(route('admin.agency_profile.edit'))->assertOk();
    }
}
