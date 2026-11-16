<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SurabayaAgenciesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Kata sandi akun demo admin@gmail.com diambil dari DEMO_ADMIN_PASSWORD (config auth.demo_admin_password).
 */
class DemoAdminPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_demo_admin_password_is_admin123(): void
    {
        $this->assertSame('admin123', config('auth.demo_admin_password'));
    }

    public function test_seeder_uses_configured_demo_admin_password(): void
    {
        config(['auth.demo_admin_password' => 'RahasiaDemo2026']);

        $this->seed(SurabayaAgenciesSeeder::class);

        $admin = User::where('email', 'admin@gmail.com')->firstOrFail();
        $this->assertTrue(Hash::check('RahasiaDemo2026', $admin->password));
        $this->assertFalse(Hash::check('admin123', $admin->password));
        $this->assertFalse((bool) $admin->must_change_password);
    }
}
