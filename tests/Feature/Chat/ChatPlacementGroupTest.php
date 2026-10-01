<?php

namespace Tests\Feature\Chat;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\Placement;

class ChatPlacementGroupTest extends ChatTestCase
{
    private function placementGroup(): ChatConversation
    {
        return ChatConversation::where('type', 'placement')->firstOrFail();
    }

    private function memberIds(ChatConversation $conversation): array
    {
        return ChatParticipant::where('conversation_id', $conversation->id)->orderBy('user_id')->pluck('user_id')->map(fn ($id) => (int) $id)->all();
    }

    public function test_placement_with_supervisors_gets_guidance_group_automatically(): void
    {
        $group = $this->placementGroup();

        $expected = [$this->studentA->id, $this->mentorX->id, $this->dosenA->id];
        sort($expected);
        $this->assertSame($expected, $this->memberIds($group));
        $this->assertSame("Bimbingan · {$this->studentA->name}", $group->title);

        $events = ChatMessage::where('conversation_id', $group->id)->pluck('body')->implode(' | ');
        $this->assertStringContainsString('dibuat otomatis', $events);
        $this->assertStringContainsString("{$this->mentorX->name} ditambahkan sebagai Mentor Lapangan", $events);
        $this->assertStringContainsString("{$this->dosenA->name} ditambahkan sebagai Dosen Pembimbing (DPL)", $events);
    }

    public function test_changing_mentor_updates_group_membership(): void
    {
        $placement = Placement::firstOrFail();
        $placement->update(['mentor_id' => $this->mentorX2->id]);

        $group = $this->placementGroup();
        $this->assertContains($this->mentorX2->id, $this->memberIds($group));
        $this->assertNotContains($this->mentorX->id, $this->memberIds($group));

        $events = ChatMessage::where('conversation_id', $group->id)->pluck('body')->implode(' | ');
        $this->assertStringContainsString("{$this->mentorX->name} tidak lagi menjadi pembimbing", $events);

        $this->actingAs($this->mentorX)->getJson(route('chat.api.messages.index', $group->id))->assertForbidden();
    }

    public function test_guidance_group_is_managed_by_system(): void
    {
        $group = $this->placementGroup();

        $this->actingAs($this->studentA)->postJson(route('chat.api.groups.leave', $group->id))->assertUnprocessable();
        $this->actingAs($this->mentorX)->patchJson(route('chat.api.groups.update', $group->id), ['title' => 'Ganti Nama'])->assertForbidden();

        $this->sendMessage($this->mentorX, $group->id, ['body' => 'Tolong unggah logbook minggu ini ya.'])->assertCreated();
        $this->actingAs($this->dosenA)->getJson(route('chat.api.summary'))->assertJsonPath('unread_total', 1);
    }

    public function test_missing_groups_are_backfilled_and_placement_link_redirects(): void
    {
        ChatConversation::query()->delete();

        $this->actingAs($this->mentorX)->get(route('chat.index'))->assertOk();
        $group = $this->placementGroup();

        $placement = Placement::firstOrFail();
        $this->actingAs($this->studentA)->get(route('chat.placement', $placement->id))->assertRedirect(route('chat.show', $group->id));
        $this->actingAs($this->adminY)->get(route('chat.placement', $placement->id))->assertForbidden();
    }

    public function test_sync_command_creates_groups(): void
    {
        ChatConversation::query()->delete();

        $this->artisan('chat:sync-placement-groups')->assertSuccessful();
        $this->assertSame(1, ChatConversation::where('type', 'placement')->count());
    }
}
