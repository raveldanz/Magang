<?php

namespace Tests\Feature\Chat;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Placement;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Ekosistem mini dua kampus & dua dinas untuk menguji aturan kontak chat.
 * - studentA (UNESA) magang aktif di Dinas X: mentor = mentorX, DPL = dosenA
 * - studentB (ITS) baru mengajukan ke Dinas Y
 */
abstract class ChatTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $adminX;
    protected User $adminY;
    protected User $mentorX;
    protected User $mentorX2;
    protected User $dosenA;
    protected User $dosenB;
    protected User $univAdminA;
    protected User $univAdminB;
    protected User $studentA;
    protected User $studentB;
    protected User $inactiveStudent;

    protected function setUp(): void
    {
        parent::setUp();

        $univA = University::create(['name' => 'Universitas Negeri Surabaya', 'code' => 'UNESA']);
        $univB = University::create(['name' => 'Institut Teknologi Sepuluh Nopember', 'code' => 'ITS']);
        $agencyX = AgencyProfile::create(['agency_name' => 'Dinas Kominfo']);
        $agencyY = AgencyProfile::create(['agency_name' => 'Dinas Kesehatan']);
        $unitX = Unit::create(['agency_profile_id' => $agencyX->id, 'name' => 'Divisi IT', 'quota' => 5]);
        $unitY = Unit::create(['agency_profile_id' => $agencyY->id, 'name' => 'Divisi Data', 'quota' => 5]);

        $this->superAdmin = User::factory()->create(['name' => 'Super Admin', 'role' => 'super_admin']);
        $this->adminX = User::factory()->create(['name' => 'Admin Kominfo', 'role' => 'admin', 'agency_profile_id' => $agencyX->id]);
        $this->adminY = User::factory()->create(['name' => 'Admin Dinkes', 'role' => 'admin', 'agency_profile_id' => $agencyY->id]);
        $this->mentorX = User::factory()->create(['name' => 'Mentor Budi', 'role' => 'mentor', 'agency_profile_id' => $agencyX->id]);
        $this->mentorX2 = User::factory()->create(['name' => 'Mentor Sari', 'role' => 'pembimbing', 'agency_profile_id' => $agencyX->id]);
        $this->dosenA = User::factory()->create(['name' => 'Dosen Rina', 'role' => 'dosen', 'university_id' => $univA->id]);
        $this->dosenB = User::factory()->create(['name' => 'Dosen Joko', 'role' => 'dosen', 'university_id' => $univB->id]);
        $this->univAdminA = User::factory()->create(['name' => 'Admin UNESA', 'role' => 'universitas', 'university_id' => $univA->id]);
        $this->univAdminB = User::factory()->create(['name' => 'Admin ITS', 'role' => 'universitas', 'university_id' => $univB->id]);
        $this->studentA = User::factory()->create(['name' => 'Andi Mahasiswa', 'role' => 'mahasiswa', 'university_id' => $univA->id]);
        $this->studentB = User::factory()->create(['name' => 'Bella Mahasiswa', 'role' => 'mahasiswa', 'university_id' => $univB->id]);
        $this->inactiveStudent = User::factory()->create(['name' => 'Citra Nonaktif', 'role' => 'mahasiswa', 'university_id' => $univA->id, 'status' => 'inactive']);

        $applicationA = Application::create([
            'user_id' => $this->studentA->id,
            'unit_id' => $unitX->id,
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonths(2)->toDateString(),
            'status' => 'active',
        ]);
        Placement::create([
            'application_id' => $applicationA->id,
            'mentor_id' => $this->mentorX->id,
            'academic_advisor_id' => $this->dosenA->id,
        ]);

        Application::create([
            'user_id' => $this->studentB->id,
            'unit_id' => $unitY->id,
            'start_date' => now()->addMonth()->toDateString(),
            'end_date' => now()->addMonths(4)->toDateString(),
            'status' => 'pending',
        ]);
    }

    protected function allUsers(): array
    {
        return [
            $this->superAdmin, $this->adminX, $this->adminY, $this->mentorX, $this->mentorX2, $this->dosenA,
            $this->dosenB, $this->univAdminA, $this->univAdminB, $this->studentA, $this->studentB,
        ];
    }

    protected function startConversation(User $from, User $to): int
    {
        return $this->actingAs($from)
            ->postJson(route('chat.start'), ['user_id' => $to->id])
            ->assertOk()
            ->json('conversation.id');
    }

    /**
     * Item percakapan dari daftar milik $viewer (urutan daftar bisa sama detiknya, jadi cari berdasarkan id).
     */
    protected function conversationItem(User $viewer, int $conversationId): array
    {
        $item = collect($this->actingAs($viewer)->getJson(route('chat.api.conversations'))->assertOk()->json('conversations'))
            ->firstWhere('id', $conversationId);
        $this->assertNotNull($item, "Percakapan #{$conversationId} tidak ada di daftar {$viewer->name}.");

        return $item;
    }

    protected function sendMessage(User $from, int $conversationId, array $payload)
    {
        return $this->actingAs($from)->post(
            route('chat.api.messages.store', $conversationId),
            $payload,
            ['Accept' => 'application/json']
        );
    }
}
