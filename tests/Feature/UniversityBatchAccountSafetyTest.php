<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniversityBatchAccountSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_batch_command_refuses_to_run_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->artisan('universities:process-batch1')->assertExitCode(1);

        $this->assertDatabaseCount('universities', 0);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_new_accounts_are_flagged_to_change_their_default_password(): void
    {
        $this->artisan('universities:process-batch1')->assertExitCode(0);

        $admin = User::where('email', 'admin@unesa.ac.id')->firstOrFail();
        $dosen = User::where('email', 'dedyrahman@unesa.ac.id')->firstOrFail();

        $this->assertTrue($admin->must_change_password);
        $this->assertTrue($dosen->must_change_password);
    }

    public function test_existing_account_with_other_role_or_inactive_status_is_not_escalated(): void
    {
        $student = User::factory()->create([
            'email' => 'admin@unesa.ac.id',
            'role' => 'mahasiswa',
            'status' => 'inactive',
        ]);
        $suspendedDosen = User::factory()->create([
            'email' => 'dedyrahman@unesa.ac.id',
            'role' => 'dosen',
            'status' => 'inactive',
        ]);

        $this->artisan('universities:process-batch1')
            ->expectsOutputToContain('TIDAK diubah')
            ->assertExitCode(0);

        $student->refresh();
        $this->assertSame('mahasiswa', $student->role);
        $this->assertSame('inactive', $student->status);
        $this->assertNull($student->university_id);

        // Akun dengan peran yang sama boleh ditautkan ke kampusnya, tetapi statusnya tidak diaktifkan ulang
        $suspendedDosen->refresh();
        $this->assertSame('dosen', $suspendedDosen->role);
        $this->assertSame('inactive', $suspendedDosen->status);
        $this->assertNotNull($suspendedDosen->university_id);
    }
}
