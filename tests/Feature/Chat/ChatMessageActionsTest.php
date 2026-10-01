<?php

namespace Tests\Feature\Chat;

use App\Models\AuditLog;
use App\Models\ChatMessage;
use App\Models\SystemFeedback;

class ChatMessageActionsTest extends ChatTestCase
{
    public function test_reply_quotes_original_message(): void
    {
        $id = $this->startConversation($this->studentA, $this->mentorX);
        $originalId = $this->sendMessage($this->mentorX, $id, ['body' => 'Laporan mingguan dikumpulkan Jumat'])->json('message.id');

        $this->sendMessage($this->studentA, $id, ['body' => 'Siap, Pak', 'reply_to_id' => $originalId])
            ->assertCreated()
            ->assertJsonPath('message.reply_to.id', $originalId)
            ->assertJsonPath('message.reply_to.preview', 'Laporan mingguan dikumpulkan Jumat')
            ->assertJsonPath('message.reply_to.sender_name', $this->mentorX->name);
    }

    public function test_cannot_reply_to_message_from_another_conversation(): void
    {
        $first = $this->startConversation($this->studentA, $this->mentorX);
        $foreignId = $this->sendMessage($this->mentorX, $first, ['body' => 'Pesan lain'])->json('message.id');
        $second = $this->startConversation($this->studentA, $this->dosenA);

        $this->sendMessage($this->studentA, $second, ['body' => 'Balas', 'reply_to_id' => $foreignId])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('reply_to_id');
    }

    public function test_sender_deletes_message_for_everyone_and_poll_reports_change(): void
    {
        $id = $this->startConversation($this->studentA, $this->mentorX);
        $messageId = $this->sendMessage($this->studentA, $id, ['body' => 'Salah kirim'])->json('message.id');
        $since = now()->subSecond()->toIso8601String();

        $this->actingAs($this->mentorX)->deleteJson(route('chat.api.messages.destroy', $messageId))->assertForbidden();
        $this->actingAs($this->studentA)->deleteJson(route('chat.api.messages.destroy', $messageId))
            ->assertOk()
            ->assertJsonPath('message.deleted', true)
            ->assertJsonPath('message.body', null);

        $this->assertNull(ChatMessage::findOrFail($messageId)->body);

        $this->actingAs($this->mentorX)
            ->getJson(route('chat.api.messages.index', $id) . '?after=' . $messageId . '&since=' . urlencode($since))
            ->assertJsonPath('changed.0.id', $messageId)
            ->assertJsonPath('changed.0.deleted', true);

        // Pesan terhapus tidak lagi dihitung belum dibaca
        $this->actingAs($this->mentorX)->getJson(route('chat.api.summary'))->assertJsonPath('unread_total', 0);
    }

    public function test_report_creates_super_admin_ticket_and_moderation_deletes_message(): void
    {
        $id = $this->startConversation($this->studentA, $this->mentorX);
        $messageId = $this->sendMessage($this->mentorX, $id, ['body' => 'Isi pesan yang tidak pantas'])->json('message.id');

        $this->actingAs($this->studentA)
            ->postJson(route('chat.api.messages.report', $messageId), ['reason' => 'tidak_pantas', 'note' => 'Mohon ditindaklanjuti'])
            ->assertOk();

        $ticket = SystemFeedback::firstOrFail();
        $this->assertSame('laporan_chat', $ticket->category);
        $this->assertSame($messageId, (int) $ticket->chat_message_id);
        $this->assertStringContainsString('Isi pesan yang tidak pantas', $ticket->message);
        $this->assertStringContainsString('Mohon ditindaklanjuti', $ticket->message);

        // Tidak boleh melaporkan dua kali / melaporkan pesan sendiri
        $this->actingAs($this->studentA)->postJson(route('chat.api.messages.report', $messageId), ['reason' => 'spam'])->assertUnprocessable();
        $this->actingAs($this->mentorX)->postJson(route('chat.api.messages.report', $messageId), ['reason' => 'spam'])->assertUnprocessable();

        // Tiket menampilkan panel moderasi untuk Super Admin
        $this->actingAs($this->superAdmin)->get(route('admin.feedbacks.show', $ticket->id))
            ->assertOk()
            ->assertSee('Moderasi Pesan Chat')
            ->assertSee(route('chat.moderation.destroy', $messageId), false);

        $this->actingAs($this->adminX)->delete(route('chat.moderation.destroy', $messageId))->assertForbidden();
        $this->actingAs($this->superAdmin)->delete(route('chat.moderation.destroy', $messageId))->assertRedirect();

        $this->assertNotNull(ChatMessage::findOrFail($messageId)->deleted_at);
        $this->assertTrue(AuditLog::where('action', 'CHAT_MESSAGE_MODERATED')->exists());
    }

    public function test_typing_indicator_is_visible_to_other_participant(): void
    {
        $id = $this->startConversation($this->studentA, $this->mentorX);

        $this->actingAs($this->studentA)->postJson(route('chat.api.messages.typing', $id))->assertOk();

        $this->actingAs($this->mentorX)->getJson(route('chat.api.messages.index', $id))
            ->assertJsonPath('state.typing', [$this->studentA->name]);
        $this->actingAs($this->studentA)->getJson(route('chat.api.messages.index', $id))
            ->assertJsonPath('state.typing', []);

        // Mengirim pesan menghentikan indikator mengetik
        $this->sendMessage($this->studentA, $id, ['body' => 'Halo Pak'])->assertCreated();
        $this->actingAs($this->mentorX)->getJson(route('chat.api.messages.index', $id))->assertJsonPath('state.typing', []);
    }

    public function test_presence_is_tracked_except_during_impersonation(): void
    {
        $id = $this->startConversation($this->studentA, $this->mentorX);

        $this->actingAs($this->mentorX)->withSession(['impersonator_id' => $this->superAdmin->id])->getJson(route('chat.api.summary'))->assertOk();
        $this->assertNull($this->mentorX->fresh()->last_seen_at);

        $this->flushSession();
        $this->actingAs($this->mentorX)->getJson(route('chat.api.summary'))->assertOk();
        $this->assertNotNull($this->mentorX->fresh()->last_seen_at);

        $this->actingAs($this->studentA)->getJson(route('chat.api.messages.index', $id))
            ->assertJsonPath('state.presence.online', true);
    }

    public function test_impersonation_blocks_every_write_action(): void
    {
        $id = $this->startConversation($this->studentA, $this->mentorX);
        $messageId = $this->sendMessage($this->mentorX, $id, ['body' => 'Pesan mentor'])->json('message.id');
        $session = ['impersonator_id' => $this->superAdmin->id];

        $this->actingAs($this->adminX)->withSession($session)
            ->postJson(route('chat.api.groups.store'), ['title' => 'Grup Palsu', 'member_ids' => [$this->mentorX->id]])
            ->assertForbidden();
        $this->actingAs($this->studentA)->withSession($session)
            ->postJson(route('chat.api.messages.report', $messageId), ['reason' => 'spam'])
            ->assertForbidden();
        $this->actingAs($this->mentorX)->withSession($session)
            ->deleteJson(route('chat.api.messages.destroy', $messageId))
            ->assertForbidden();
        $this->actingAs($this->studentA)->withSession($session)
            ->postJson(route('chat.api.conversations.settings', $id), ['muted' => true])
            ->assertForbidden();

        $this->assertNull(ChatMessage::findOrFail($messageId)->deleted_at);
    }
}
