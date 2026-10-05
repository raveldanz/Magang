<?php

namespace Tests\Feature\Chat;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\SystemNotification;

class ChatGroupTest extends ChatTestCase
{
    private function createGroup($creator, array $members, string $title = 'Koordinasi Magang Kominfo'): int
    {
        return $this->actingAs($creator)
            ->postJson(route('chat.api.groups.store'), ['title' => $title, 'member_ids' => array_map(fn ($u) => $u->id, $members)])
            ->assertCreated()
            ->json('conversation.id');
    }

    public function test_staff_can_create_group_from_their_contacts(): void
    {
        $id = $this->createGroup($this->adminX, [$this->mentorX, $this->mentorX2, $this->studentA]);

        $conversation = ChatConversation::findOrFail($id);
        $this->assertSame('group', $conversation->type);
        $this->assertSame(4, ChatParticipant::where('conversation_id', $id)->count());
        $this->assertTrue(ChatParticipant::where('conversation_id', $id)->where('user_id', $this->adminX->id)->firstOrFail()->isAdmin());

        $system = ChatMessage::where('conversation_id', $id)->where('type', 'system')->firstOrFail();
        $this->assertStringContainsString('membuat grup', $system->body);

        // Grup langsung muncul di daftar anggota walau belum ada pesan teks
        $item = $this->conversationItem($this->studentA, $id);
        $this->assertSame('Koordinasi Magang Kominfo', $item['title']);
        $this->assertSame('group', $item['type']);
        $this->assertSame(0, $item['unread']);
    }

    public function test_students_cannot_create_groups(): void
    {
        $this->actingAs($this->studentA)
            ->postJson(route('chat.api.groups.store'), ['title' => 'Grup Mahasiswa', 'member_ids' => [$this->mentorX->id]])
            ->assertForbidden();
    }

    public function test_group_members_must_be_contacts_of_creator(): void
    {
        $this->actingAs($this->mentorX)
            ->postJson(route('chat.api.groups.store'), ['title' => 'Grup Campuran', 'member_ids' => [$this->studentA->id, $this->adminY->id]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('member_ids');

        $this->assertSame(0, ChatConversation::where('title', 'Grup Campuran')->count());
    }

    public function test_group_message_reaches_all_members_with_one_bell_entry_each(): void
    {
        $id = $this->createGroup($this->adminX, [$this->mentorX, $this->studentA]);
        $this->sendMessage($this->adminX, $id, ['body' => 'Rapat koordinasi besok pukul 09.00'])->assertCreated();

        foreach ([$this->mentorX, $this->studentA] as $member) {
            $this->actingAs($member)->getJson(route('chat.api.summary'))->assertJsonPath('unread_total', 1);
        }

        $notifications = SystemNotification::where('category', 'chat')->where('action_url', '/chat/'.$id)->get();
        $this->assertCount(2, $notifications);
        $this->assertSame('Pesan baru di Koordinasi Magang Kominfo', $notifications[0]->title);
        $this->assertStringStartsWith('Admin: Rapat koordinasi', $notifications[0]->message);
    }

    public function test_group_read_state_counts_readers(): void
    {
        $id = $this->createGroup($this->adminX, [$this->mentorX, $this->studentA]);
        $messageId = $this->sendMessage($this->adminX, $id, ['body' => 'Mohon konfirmasi kehadiran'])->json('message.id');

        $this->actingAs($this->studentA)->postJson(route('chat.api.messages.read', $id), ['message_id' => $messageId])->assertOk();

        $state = $this->actingAs($this->adminX)->getJson(route('chat.api.messages.index', $id))->json('state.read_state');
        $this->assertSame($messageId, $state[$this->studentA->id]);
        $this->assertLessThan($messageId, $state[$this->mentorX->id]);
    }

    public function test_admin_manages_members_and_title(): void
    {
        $id = $this->createGroup($this->adminX, [$this->mentorX]);

        $this->actingAs($this->adminX)->postJson(route('chat.api.groups.members.store', $id), ['user_ids' => [$this->studentA->id, $this->dosenA->id]])
            ->assertOk()
            ->assertJsonPath('conversation.member_count', 4);

        $this->actingAs($this->adminX)->patchJson(route('chat.api.groups.update', $id), ['title' => 'Magang Batch Oktober'])
            ->assertOk()
            ->assertJsonPath('conversation.title', 'Magang Batch Oktober');

        $this->actingAs($this->adminX)->deleteJson(route('chat.api.groups.members.destroy', [$id, $this->dosenA->id]))
            ->assertOk()
            ->assertJsonPath('conversation.member_count', 3);

        $this->actingAs($this->dosenA)->getJson(route('chat.api.messages.index', $id))->assertForbidden();
        $this->assertGreaterThanOrEqual(3, ChatConversation::findOrFail($id)->meta_version);

        $events = ChatMessage::where('conversation_id', $id)->where('type', 'system')->pluck('body')->implode(' | ');
        $this->assertStringContainsString('menambahkan', $events);
        $this->assertStringContainsString('mengubah nama grup', $events);
        $this->assertStringContainsString('mengeluarkan', $events);
    }

    public function test_non_admin_member_cannot_manage_group(): void
    {
        $id = $this->createGroup($this->adminX, [$this->mentorX, $this->studentA]);

        $this->actingAs($this->mentorX)->patchJson(route('chat.api.groups.update', $id), ['title' => 'Diubah Mentor'])->assertForbidden();
        $this->actingAs($this->mentorX)->deleteJson(route('chat.api.groups.members.destroy', [$id, $this->studentA->id]))->assertForbidden();
        $this->actingAs($this->mentorX)->postJson(route('chat.api.groups.members.store', $id), ['user_ids' => [$this->mentorX2->id]])->assertForbidden();
    }

    public function test_last_admin_leaving_promotes_oldest_member(): void
    {
        // mentorX bergabung lebih dulu daripada studentA
        $id = $this->createGroup($this->adminX, [$this->mentorX]);
        $this->actingAs($this->adminX)->postJson(route('chat.api.groups.members.store', $id), ['user_ids' => [$this->studentA->id]])->assertOk();

        $this->actingAs($this->adminX)->postJson(route('chat.api.groups.leave', $id))->assertOk();

        $this->assertNull(ChatParticipant::where('conversation_id', $id)->where('user_id', $this->adminX->id)->first());
        $this->assertTrue(ChatParticipant::where('conversation_id', $id)->where('user_id', $this->mentorX->id)->firstOrFail()->isAdmin());
        $this->actingAs($this->adminX)->getJson(route('chat.api.messages.index', $id))->assertForbidden();
    }

    public function test_muted_group_is_excluded_from_badge_and_bell(): void
    {
        $id = $this->createGroup($this->adminX, [$this->mentorX, $this->studentA]);
        $this->actingAs($this->studentA)->postJson(route('chat.api.conversations.settings', $id), ['muted' => true, 'pinned' => true])
            ->assertOk()
            ->assertJson(['muted' => true, 'pinned' => true]);

        $this->sendMessage($this->adminX, $id, ['body' => 'Pengumuman umum'])->assertCreated();

        $this->actingAs($this->studentA)->getJson(route('chat.api.summary'))->assertJsonPath('unread_total', 0);
        $this->assertSame(0, SystemNotification::where('category', 'chat')->where('user_id', $this->studentA->id)->count());
        // Jumlah belum dibaca tetap terlihat (abu-abu) di daftar
        $this->actingAs($this->studentA)->getJson(route('chat.api.conversations'))
            ->assertJsonPath('conversations.0.unread', 1)
            ->assertJsonPath('conversations.0.muted', true)
            ->assertJsonPath('conversations.0.pinned', true);
    }
}
