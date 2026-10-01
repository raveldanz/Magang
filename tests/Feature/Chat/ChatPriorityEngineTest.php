<?php

namespace Tests\Feature\Chat;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\ChatConversation;
use App\Models\Placement;
use App\Services\Chat\ChatPresenter;

class ChatPriorityEngineTest extends ChatTestCase
{
    private ChatPresenter $presenter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->presenter = app(ChatPresenter::class);
    }

    public function test_stage_badge_for_student_in_selection(): void
    {
        // studentB status pending
        $appB = Application::where('user_id', $this->studentB->id)->first();
        $this->assertNotNull($appB);
        $this->assertSame(ApplicationStatus::PENDING, $appB->status);

        $badge = $this->presenter->applicationStageBadge($appB);
        $this->assertSame('urgent', $badge['code']);
        $this->assertSame('Seleksi Masuk', $badge['label']);
        $this->assertSame('urgent', $badge['theme']);
        $this->assertTrue($badge['is_urgent']);
        $this->assertSame(1, $badge['priority_rank']);
    }

    public function test_stage_badge_for_student_approaching_end_date(): void
    {
        // studentA status active, set end_date to 3 days from now
        $appA = Application::where('user_id', $this->studentA->id)->first();
        $this->assertNotNull($appA);
        $appA->update([
            'status' => ApplicationStatus::ACTIVE,
            'end_date' => now()->addDays(3)->toDateString(),
        ]);

        $badge = $this->presenter->applicationStageBadge($appA);
        $this->assertSame('approaching_end', $badge['code']);
        $this->assertSame('H-3 Selesai', $badge['label']);
        $this->assertSame('warning', $badge['theme']);
        $this->assertTrue($badge['is_urgent']);
        $this->assertSame(4, $badge['priority_rank']);
    }

    public function test_stage_badge_for_normal_active_student(): void
    {
        // studentA active with 30 days left
        $appA = Application::where('user_id', $this->studentA->id)->first();
        $appA->update([
            'status' => ApplicationStatus::ACTIVE,
            'end_date' => now()->addDays(30)->toDateString(),
        ]);

        $badge = $this->presenter->applicationStageBadge($appA);
        $this->assertSame('active', $badge['code']);
        $this->assertSame('Aktif Magang', $badge['label']);
        $this->assertSame('success', $badge['theme']);
        $this->assertFalse($badge['is_urgent']);
    }

    public function test_stage_badge_for_completed_student(): void
    {
        $appA = Application::where('user_id', $this->studentA->id)->first();
        $appA->update(['status' => ApplicationStatus::COMPLETED]);

        $badge = $this->presenter->applicationStageBadge($appA);
        $this->assertSame('completed', $badge['code']);
        $this->assertSame('Alumni Magang', $badge['label']);
        $this->assertSame('neutral', $badge['theme']);
        $this->assertFalse($badge['is_urgent']);
    }

    public function test_stage_badge_for_placement_group_and_staff_direct_chat(): void
    {
        $placement = Placement::firstOrFail();
        $placementGroup = ChatConversation::where('placement_id', $placement->id)->first();
        if (!$placementGroup) {
            $placementGroup = ChatConversation::create([
                'type' => ChatConversation::TYPE_PLACEMENT,
                'placement_id' => $placement->id,
                'title' => 'Bimbingan Magang IT',
            ]);
        }

        $groupBadge = $this->presenter->stageBadge($placementGroup, null);
        $this->assertNotNull($groupBadge);
        $this->assertSame('student', $groupBadge['role_group']);

        // Chat 1-on-1 dengan mentor
        $directConv = ChatConversation::create([
            'type' => ChatConversation::TYPE_DIRECT,
            'direct_key' => ChatConversation::directKeyFor($this->studentA->id, $this->mentorX->id),
        ]);
        $mentorBadge = $this->presenter->stageBadge($directConv, $this->mentorX);
        $this->assertNotNull($mentorBadge);
        $this->assertSame('staff', $mentorBadge['code']);
        $this->assertSame('Mentor Lapangan', $mentorBadge['label']);
        $this->assertSame('mentor', $mentorBadge['role_group']);
        $this->assertFalse($mentorBadge['is_urgent']);
    }

    public function test_conversations_api_includes_stage_badge(): void
    {
        $conversationId = $this->startConversation($this->adminY, $this->studentB);
        $this->sendMessage($this->studentB, $conversationId, ['body' => 'Selamat siang admin, mohon info verifikasi berkas']);

        $response = $this->actingAs($this->adminY)
            ->getJson(route('chat.api.conversations'))
            ->assertOk();

        $conversations = $response->json('conversations');
        $this->assertNotEmpty($conversations);

        $target = collect($conversations)->firstWhere('id', $conversationId);
        $this->assertNotNull($target);
        $this->assertArrayHasKey('stage_badge', $target);
        $this->assertSame('urgent', $target['stage_badge']['code']);
        $this->assertSame('Seleksi Masuk', $target['stage_badge']['label']);
        $this->assertTrue($target['stage_badge']['is_urgent']);
    }
}
