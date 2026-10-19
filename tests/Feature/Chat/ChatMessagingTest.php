<?php

namespace Tests\Feature\Chat;

use App\Models\ChatConversation;
use App\Models\ChatParticipant;
use App\Models\SystemNotification;
use Illuminate\Support\Facades\Schema;

class ChatMessagingTest extends ChatTestCase
{
    public function test_starting_a_chat_is_idempotent_per_pair(): void
    {
        $first = $this->startConversation($this->studentA, $this->mentorX);
        $second = $this->startConversation($this->mentorX, $this->studentA);

        $this->assertSame($first, $second);
        $this->assertSame(1, ChatConversation::where('type', 'direct')->count());
        $this->assertSame(2, ChatParticipant::where('conversation_id', $first)->count());
    }

    public function test_cannot_start_chat_outside_contact_rules(): void
    {
        $this->actingAs($this->studentA)
            ->postJson(route('chat.start'), ['user_id' => $this->adminY->id])
            ->assertForbidden();

        $this->actingAs($this->studentA)
            ->postJson(route('chat.start'), ['user_id' => $this->studentA->id])
            ->assertUnprocessable();

        $this->assertSame(0, ChatConversation::where('type', 'direct')->count());
    }

    public function test_message_flow_updates_unread_counts_and_read_receipts(): void
    {
        $conversationId = $this->startConversation($this->studentA, $this->mentorX);

        $this->sendMessage($this->studentA, $conversationId, ['body' => "Selamat pagi Pak, logbook hari ini sudah saya isi.\nMohon dicek."])
            ->assertCreated()
            ->assertJsonPath('message.is_mine', true);

        $this->actingAs($this->mentorX)->getJson(route('chat.api.summary'))
            ->assertOk()
            ->assertJsonPath('unread_total', 1);

        $messages = $this->actingAs($this->mentorX)
            ->getJson(route('chat.api.messages.index', $conversationId).'?detail=1')
            ->assertOk()
            ->assertJsonPath('messages.0.is_mine', false)
            ->assertJsonPath('conversation.contact.name', $this->studentA->name)
            ->json('messages');

        $this->actingAs($this->mentorX)
            ->postJson(route('chat.api.messages.read', $conversationId), ['message_id' => $messages[0]['id']])
            ->assertOk();

        $this->actingAs($this->mentorX)->getJson(route('chat.api.summary'))->assertJsonPath('unread_total', 0);

        // Mahasiswa melihat pesannya sudah dibaca (✓✓)
        $this->actingAs($this->studentA)
            ->getJson(route('chat.api.messages.index', $conversationId))
            ->assertJsonPath('state.read_state.'.$this->mentorX->id, $messages[0]['id']);

        $this->assertTrue($this->conversationItem($this->studentA, $conversationId)['last_message']['is_mine']);
    }

    public function test_polling_after_id_returns_only_new_messages(): void
    {
        $conversationId = $this->startConversation($this->studentA, $this->dosenA);
        $firstId = $this->sendMessage($this->studentA, $conversationId, ['body' => 'Pesan pertama'])->json('message.id');
        $this->sendMessage($this->dosenA, $conversationId, ['body' => 'Balasan dosen'])->assertCreated();

        $this->actingAs($this->studentA)
            ->getJson(route('chat.api.messages.index', $conversationId).'?after='.$firstId)
            ->assertJsonCount(1, 'messages')
            ->assertJsonPath('messages.0.body', 'Balasan dosen');

        $summary = $this->actingAs($this->studentA)
            ->getJson(route('chat.api.summary').'?after='.$firstId)
            ->assertJsonPath('unread_total', 1)
            ->json();
        $this->assertSame('Balasan dosen', $summary['messages'][0]['preview']);
        $this->assertSame($this->dosenA->name, $summary['messages'][0]['sender']['name']);
    }

    public function test_non_participant_cannot_read_or_write_conversation(): void
    {
        $conversationId = $this->startConversation($this->studentA, $this->mentorX);

        $this->actingAs($this->adminY)->getJson(route('chat.api.messages.index', $conversationId))->assertForbidden();
        $this->sendMessage($this->adminY, $conversationId, ['body' => 'Menyusup'])->assertForbidden();
        $this->actingAs($this->adminY)->get(route('chat.show', $conversationId))->assertForbidden();
        // Super Admin pun tidak bisa membaca percakapan orang lain
        $this->actingAs($this->superAdmin)->getJson(route('chat.api.messages.index', $conversationId))->assertForbidden();
    }

    public function test_empty_or_too_long_message_is_rejected(): void
    {
        $conversationId = $this->startConversation($this->studentA, $this->mentorX);

        $this->sendMessage($this->studentA, $conversationId, ['body' => '   '])->assertUnprocessable();
        $this->sendMessage($this->studentA, $conversationId, ['body' => str_repeat('a', config('chat.max_body_length') + 1)])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('body');
    }

    public function test_cannot_message_deactivated_account(): void
    {
        $conversationId = $this->startConversation($this->studentA, $this->mentorX);
        $this->mentorX->update(['status' => 'inactive']);

        $this->sendMessage($this->studentA, $conversationId, ['body' => 'Halo'])->assertUnprocessable();
    }

    public function test_impersonation_is_read_only(): void
    {
        $conversationId = $this->startConversation($this->studentA, $this->mentorX);
        $messageId = $this->sendMessage($this->mentorX, $conversationId, ['body' => 'Besok rapat jam 9'])->json('message.id');

        $impersonating = ['impersonator_id' => $this->superAdmin->id];

        $this->actingAs($this->studentA)->withSession($impersonating)
            ->post(route('chat.api.messages.store', $conversationId), ['body' => 'Dikirim admin'], ['Accept' => 'application/json'])
            ->assertForbidden();

        $this->actingAs($this->studentA)->withSession($impersonating)
            ->postJson(route('chat.api.messages.read', $conversationId), ['message_id' => $messageId])
            ->assertOk();

        $this->assertNull(ChatParticipant::where('conversation_id', $conversationId)->where('user_id', $this->studentA->id)->value('last_read_message_id'));
    }

    public function test_bell_notification_is_one_entry_per_conversation_and_cleared_on_read(): void
    {
        $conversationId = $this->startConversation($this->studentA, $this->mentorX);
        $this->sendMessage($this->studentA, $conversationId, ['body' => 'Pesan satu'])->assertCreated();
        $lastId = $this->sendMessage($this->studentA, $conversationId, ['body' => 'Pesan dua'])->json('message.id');

        $notifications = SystemNotification::where('category', 'chat')->get();
        $this->assertCount(1, $notifications, 'Lonceng harus menggabungkan pesan per percakapan.');
        $this->assertSame($this->mentorX->id, $notifications[0]->user_id);
        $this->assertSame('/chat/'.$conversationId, $notifications[0]->action_url);
        $this->assertSame("2 pesan baru dari {$this->studentA->name}", $notifications[0]->title);
        $this->assertSame('Pesan dua', $notifications[0]->message);
        $this->assertNull($notifications[0]->read_at);

        $this->actingAs($this->mentorX)
            ->postJson(route('chat.api.messages.read', $conversationId), ['message_id' => $lastId])
            ->assertOk();

        $this->assertNotNull($notifications[0]->fresh()->read_at);
    }

    public function test_chat_page_renders_for_every_role(): void
    {
        foreach ($this->allUsers() as $user) {
            $this->actingAs($user)->get(route('chat.index'))
                ->assertOk()
                ->assertSee('Chat Baru')
                ->assertSee('data-chat-summary-url', false);
        }

        $conversationId = $this->startConversation($this->studentA, $this->mentorX);
        $this->actingAs($this->mentorX)->get(route('chat.show', $conversationId))->assertOk();
    }

    public function test_pages_still_render_when_chat_migration_has_not_run(): void
    {
        // Simulasi: kode terbaru ditarik tapi `php artisan migrate` belum dijalankan
        Schema::dropIfExists('chat_attachments');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_participants');
        Schema::dropIfExists('chat_conversations');

        $this->actingAs($this->mentorX)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('data-chat-unread="0"', false);
    }

    public function test_navbar_shows_unread_chat_badge(): void
    {
        $conversationId = $this->startConversation($this->studentA, $this->mentorX);
        $this->sendMessage($this->studentA, $conversationId, ['body' => 'Halo Pak'])->assertCreated();

        $this->actingAs($this->mentorX)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('data-chat-unread="1"', false)
            ->assertSee(route('chat.index'), false);
    }
}
