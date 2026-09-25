<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Placement;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Regresi keamanan QR verifikasi publik (surat balasan & sertifikat) dan rate limit API login.
 */
class VerificationQrTest extends TestCase
{
    use RefreshDatabase;

    private function makePlacement(string $status = 'completed'): Placement
    {
        $agency = AgencyProfile::create(['agency_name' => 'Dinas Uji']);
        $unit = Unit::create(['agency_profile_id' => $agency->id, 'name' => 'Unit Uji', 'quota' => 5]);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $student->studentProfile()->create(['nim' => '123', 'universitas' => 'Unesa', 'jurusan' => 'TI', 'phone' => '08']);

        $application = Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'status' => $status,
            'start_date' => Carbon::now()->subDays(60)->toDateString(),
            'end_date' => Carbon::now()->subDay()->toDateString(),
            'letter_token' => 'tokensurat1234567890abcdefghijklm',
        ]);

        return Placement::create([
            'application_id' => $application->id,
            'certificate_hash' => 'hashsertifikat1234567890abcdefgh',
        ]);
    }

    public function test_verification_works_with_random_token(): void
    {
        $placement = $this->makePlacement();

        $this->get(route('verify.letter', 'tokensurat1234567890abcdefghijklm'))->assertOk();
        $this->get(route('verify.certificate', 'hashsertifikat1234567890abcdefgh'))->assertOk();
    }

    public function test_numeric_id_is_rejected_by_default(): void
    {
        $placement = $this->makePlacement();

        $this->get('/verify-letter/' . $placement->application_id)->assertNotFound();
        $this->get('/verify-certificate/' . $placement->id)->assertNotFound();
        $this->get('/verify-certificate/' . $placement->application_id)->assertNotFound();
    }

    public function test_numeric_id_can_be_enabled_for_legacy_documents(): void
    {
        config(['app.verify_numeric_fallback' => true]);
        $placement = $this->makePlacement();

        $this->get('/verify-letter/' . $placement->application_id)->assertOk();
        $this->get('/verify-certificate/' . $placement->id)->assertOk();
    }

    public function test_tokens_are_generated_when_missing(): void
    {
        $placement = $this->makePlacement();
        $placement->update(['certificate_hash' => null]);
        $placement->application->update(['letter_token' => null]);

        $hash = $placement->fresh()->ensureCertificateHash();
        $token = $placement->application->fresh()->ensureLetterToken();

        $this->assertSame(32, strlen($hash));
        $this->assertSame(32, strlen($token));
        $this->assertSame($hash, $placement->fresh()->certificate_hash);
        $this->assertSame($token, $placement->application->fresh()->letter_token);
        $this->assertFalse(ctype_digit($hash));
    }

    public function test_many_students_on_same_network_can_login_to_api(): void
    {
        // 50 akun berbeda login dari 1 IP (mis. Wi-Fi kampus) tidak boleh kena 429
        $users = User::factory()->count(50)->create(['password' => 'password']);

        foreach ($users as $u) {
            $this->postJson('/api/login', ['email' => $u->email, 'password' => 'password'])->assertOk();
        }
    }

    public function test_concurrent_token_generation_keeps_first_token(): void
    {
        $placement = $this->makePlacement();
        $placement->update(['certificate_hash' => null]);

        // Dua request membaca data yang sama (hash kosong) sebelum salah satunya menyimpan
        $a = Placement::find($placement->id);
        $b = Placement::find($placement->id);

        $hashA = $a->ensureCertificateHash();
        $hashB = $b->ensureCertificateHash();

        $this->assertSame($hashA, $hashB);
        $this->assertSame($hashA, $placement->fresh()->certificate_hash);
    }

    public function test_verification_page_handles_many_scans(): void
    {
        $this->makePlacement();

        for ($i = 0; $i < 100; $i++) {
            $this->get(route('verify.certificate', 'hashsertifikat1234567890abcdefgh'))->assertOk();
        }
    }

    public function test_api_login_is_rate_limited(): void
    {
        User::factory()->create(['email' => 'target@example.com']);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', ['email' => 'target@example.com', 'password' => 'salah'])->assertStatus(401);
        }

        $this->postJson('/api/login', ['email' => 'target@example.com', 'password' => 'salah'])->assertStatus(429);
    }
}
