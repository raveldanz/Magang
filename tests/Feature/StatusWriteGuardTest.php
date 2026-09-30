<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Models\SystemFeedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * Kolom users.status & system_feedbacks.status berjenis teks biasa tanpa CHECK constraint.
 * Penjaga simpan di AppServiceProvider menolak nilai di luar enum (mis. "on_leave", "submitted").
 */
class StatusWriteGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_account_status_is_rejected_on_save(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $this->expectException(InvalidArgumentException::class);
        $user->update(['status' => 'on_leave']);
    }

    public function test_valid_saves_are_not_blocked(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        // Status tidak berubah: penyimpanan kolom lain harus tetap tersimpan
        $user->update(['name' => 'Nama Baru']);
        $this->assertSame('Nama Baru', $user->fresh()->name);

        $user->update(['status' => AccountStatus::INACTIVE]);
        $this->assertSame('inactive', $user->fresh()->status);

        $user->update(['status' => 'active']);
        $this->assertSame('active', $user->fresh()->status);
    }

    public function test_feedback_status_is_guarded(): void
    {
        $feedback = SystemFeedback::create([
            'sender_name' => 'Uji', 'sender_email' => 'uji@example.test', 'subject' => 'Tanya',
            'message' => 'Isi', 'status' => 'pending',
        ]);
        $feedback->update(['status' => 'in_progress']);
        $this->assertSame('in_progress', $feedback->fresh()->status);

        $this->expectException(InvalidArgumentException::class);
        $feedback->update(['status' => 'submitted']);
    }
}
