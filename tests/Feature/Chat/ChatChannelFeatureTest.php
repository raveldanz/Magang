<?php

namespace Tests\Feature\Chat;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\Chat\ChatChannelService;

class ChatChannelFeatureTest extends ChatTestCase
{
    protected ChatChannelService $channelService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->channelService = app(ChatChannelService::class);
    }

    public function test_official_channels_are_automatically_created_with_uniform_naming(): void
    {
        $this->channelService->ensureOfficialChannels();

        $channels = ChatConversation::where('type', ChatConversation::TYPE_CHANNEL)->get();
        $this->assertNotEmpty($channels);

        $pemkot = ChatConversation::where('type', ChatConversation::TYPE_CHANNEL)
            ->where('scope_type', ChatConversation::SCOPE_GOVERNMENT)
            ->whereNull('agency_profile_id')
            ->first();

        $this->assertNotNull($pemkot);
        $this->assertEquals('Pemerintah Kota Surabaya', $pemkot->title);
        $this->assertFalse(str_contains($pemkot->title, '📢'), 'Title must not contain decorative emojis');
        $this->assertTrue($pemkot->is_broadcast_only);

        $unesa = ChatConversation::where('type', ChatConversation::TYPE_CHANNEL)
            ->where('scope_type', ChatConversation::SCOPE_UNIVERSITY)
            ->where('university_id', $this->studentA->university_id)
            ->first();

        $this->assertNotNull($unesa);
        $this->assertEquals('Universitas Negeri Surabaya', $unesa->title);
        $this->assertFalse(str_contains($unesa->title, '📢'), 'Title must not contain decorative emojis');
    }

    public function test_users_are_automatically_joined_to_their_respective_channels_on_chat_visit(): void
    {
        // When studentA visits /chat
        $response = $this->actingAs($this->studentA)->get(route('chat.index'));
        $response->assertOk();

        // Student A should be joined to Pemkot and UNESA channels
        $joinedChannelTitles = ChatConversation::whereHas('members', fn ($q) => $q->where('users.id', $this->studentA->id))
            ->where('type', ChatConversation::TYPE_CHANNEL)
            ->pluck('title')
            ->toArray();

        $this->assertContains('Pemerintah Kota Surabaya', $joinedChannelTitles);
        $this->assertContains('Universitas Negeri Surabaya', $joinedChannelTitles);
        $this->assertNotContains('Institut Teknologi Sepuluh Nopember', $joinedChannelTitles);
    }

    public function test_only_authorized_authorities_can_publish_to_channel_main_feed(): void
    {
        $this->channelService->ensureChannelsForUser($this->studentA);
        $this->channelService->ensureChannelsForUser($this->univAdminA);

        $unesaChannel = ChatConversation::where('type', ChatConversation::TYPE_CHANNEL)
            ->where('scope_type', ChatConversation::SCOPE_UNIVERSITY)
            ->where('university_id', $this->studentA->university_id)
            ->firstOrFail();

        $pemkotChannel = ChatConversation::where('type', ChatConversation::TYPE_CHANNEL)
            ->where('scope_type', ChatConversation::SCOPE_GOVERNMENT)
            ->whereNull('agency_profile_id')
            ->firstOrFail();

        // 1. Non-admin (Student A) attempts to publish announcement -> 403 Forbidden
        $this->actingAs($this->studentA)
            ->postJson(route('chat.api.messages.store', ['conversation' => $unesaChannel->id]), [
                'body' => 'Pengumuman palsu dari mahasiswa',
            ])
            ->assertForbidden();

        // 2. Non-admin (Dosen A) attempts to publish announcement -> 403 Forbidden
        $this->actingAs($this->dosenA)
            ->postJson(route('chat.api.messages.store', ['conversation' => $unesaChannel->id]), [
                'body' => 'Pengumuman dari dosen',
            ])
            ->assertForbidden();

        // 3. Admin UNESA publishing to UNESA channel -> Success 201 Created
        $response = $this->actingAs($this->univAdminA)
            ->postJson(route('chat.api.messages.store', ['conversation' => $unesaChannel->id]), [
                'body' => 'Pengumuman Resmi: Jadwal Bimbingan Magang Semester Gasal telah dibuka.',
            ]);

        $this->assertSame(201, $response->status(), 'Univ Admin failed: '.json_encode($response->json()));
        $response->assertJsonPath('message.body', 'Pengumuman Resmi: Jadwal Bimbingan Magang Semester Gasal telah dibuka.');

        // 4. Super Admin publishing to Pemkot channel -> Success 201 Created
        $this->channelService->ensureChannelsForUser($this->superAdmin);
        $responsePemkot = $this->actingAs($this->superAdmin)
            ->postJson(route('chat.api.messages.store', ['conversation' => $pemkotChannel->id]), [
                'body' => 'Pengumuman Resmi Pemkot: Pendaftaran Magang Kota Surabaya resmi dibuka.',
            ]);

        $responsePemkot->assertCreated()
            ->assertJsonPath('message.body', 'Pengumuman Resmi Pemkot: Pendaftaran Magang Kota Surabaya resmi dibuka.');

        // 5. Admin UNESA attempting to publish to ITS channel -> 403 Forbidden
        $itsChannel = ChatConversation::where('type', ChatConversation::TYPE_CHANNEL)
            ->where('university_id', $this->studentB->university_id)
            ->firstOrFail();

        $this->actingAs($this->univAdminA)
            ->postJson(route('chat.api.messages.store', ['conversation' => $itsChannel->id]), [
                'body' => 'Pengumuman nyasar',
            ])
            ->assertForbidden();
    }

    public function test_channel_members_can_comment_and_increment_comments_count(): void
    {
        $this->channelService->ensureChannelsForUser($this->studentA);
        $this->channelService->ensureChannelsForUser($this->univAdminA);

        $unesaChannel = ChatConversation::where('type', ChatConversation::TYPE_CHANNEL)
            ->where('university_id', $this->studentA->university_id)
            ->firstOrFail();

        // 1. Admin UNESA publishes top-level announcement
        $broadcastRes = $this->actingAs($this->univAdminA)
            ->postJson(route('chat.api.messages.store', ['conversation' => $unesaChannel->id]), [
                'body' => 'Sosialisasi Berkas Laporan Akhir Magang.',
            ])
            ->assertCreated();

        $broadcastId = $broadcastRes->json('message.id');
        $this->assertNotNull($broadcastId);

        $broadcast = ChatMessage::findOrFail($broadcastId);
        $this->assertEquals(0, $broadcast->comments_count);

        // 2. Student A comments on the announcement
        $commentRes1 = $this->actingAs($this->studentA)
            ->postJson(route('chat.api.messages.comments.store', ['message' => $broadcastId]), [
                'body' => 'Izin bertanya Pak, apakah format laporan menggunakan template 2026?',
            ]);

        $commentRes1->assertCreated()
            ->assertJsonPath('comment.body', 'Izin bertanya Pak, apakah format laporan menggunakan template 2026?')
            ->assertJsonPath('comment.parent_id', $broadcastId);

        $broadcast->refresh();
        $this->assertEquals(1, $broadcast->comments_count);

        // 3. Dosen A also comments on the announcement
        $this->channelService->ensureChannelsForUser($this->dosenA);

        $commentRes2 = $this->actingAs($this->dosenA)
            ->postJson(route('chat.api.messages.comments.store', ['message' => $broadcastId]), [
                'body' => 'Format laporan sudah disesuaikan dengan panduan prodi.',
            ]);

        $commentRes2->assertCreated();

        $broadcast->refresh();
        $this->assertEquals(2, $broadcast->comments_count);

        // 4. Fetch comments list via GET /chat/api/messages/{message}/comments
        $listRes = $this->actingAs($this->studentA)
            ->getJson(route('chat.api.messages.comments.index', ['message' => $broadcastId]));

        $listRes->assertOk();
        $comments = $listRes->json('comments');
        $this->assertCount(2, $comments);
        $this->assertEquals('Izin bertanya Pak, apakah format laporan menggunakan template 2026?', $comments[0]['body']);
        $this->assertEquals('Format laporan sudah disesuaikan dengan panduan prodi.', $comments[1]['body']);
    }

    public function test_comments_do_not_leak_into_main_channel_timeline(): void
    {
        $this->channelService->ensureChannelsForUser($this->studentA);
        $this->channelService->ensureChannelsForUser($this->univAdminA);

        $unesaChannel = ChatConversation::where('type', ChatConversation::TYPE_CHANNEL)
            ->where('university_id', $this->studentA->university_id)
            ->firstOrFail();

        // 1. Post announcement
        $announcement = $this->actingAs($this->univAdminA)
            ->postJson(route('chat.api.messages.store', ['conversation' => $unesaChannel->id]), [
                'body' => 'Pengumuman Penting Kampus.',
            ])
            ->json('message');

        // 2. Post 3 comments under the announcement
        for ($i = 1; $i <= 3; $i++) {
            $this->actingAs($this->studentA)
                ->postJson(route('chat.api.messages.comments.store', ['message' => $announcement['id']]), [
                    'body' => "Komentar nomor $i",
                ])
                ->assertCreated();
        }

        // 3. Main timeline fetch: GET /chat/api/conversations/{channel}/messages
        $timelineRes = $this->actingAs($this->studentA)
            ->getJson(route('chat.api.messages.index', ['conversation' => $unesaChannel->id]));

        $timelineRes->assertOk();
        $messages = $timelineRes->json('messages');

        // Only the top-level announcement should be present in the main timeline
        $this->assertCount(1, $messages);
        $this->assertEquals($announcement['id'], $messages[0]['id']);
        $this->assertEquals(3, $messages[0]['comments_count']);
    }

    public function test_deleting_comment_decrements_parent_comments_count(): void
    {
        $this->channelService->ensureChannelsForUser($this->studentA);
        $this->channelService->ensureChannelsForUser($this->univAdminA);

        $unesaChannel = ChatConversation::where('type', ChatConversation::TYPE_CHANNEL)
            ->where('university_id', $this->studentA->university_id)
            ->firstOrFail();

        $announcementRes = $this->actingAs($this->univAdminA)
            ->postJson(route('chat.api.messages.store', ['conversation' => $unesaChannel->id]), [
                'body' => 'Pengumuman untuk diuji penghapusan komentar.',
            ]);

        $announcementId = $announcementRes->json('message.id');

        $commentRes = $this->actingAs($this->studentA)
            ->postJson(route('chat.api.messages.comments.store', ['message' => $announcementId]), [
                'body' => 'Komentar yang akan dihapus.',
            ]);

        $commentId = $commentRes->json('comment.id');

        $announcement = ChatMessage::findOrFail($announcementId);
        $this->assertEquals(1, $announcement->comments_count);

        // Delete the comment
        $this->actingAs($this->studentA)
            ->deleteJson(route('chat.api.messages.destroy', ['message' => $commentId]))
            ->assertOk();

        $announcement->refresh();
        $this->assertEquals(0, $announcement->comments_count);
    }

    public function test_direct_and_group_chats_continue_to_function_normally_without_regression(): void
    {
        // 1. Direct chat between studentA and dosenA
        $convId = $this->startConversation($this->studentA, $this->dosenA);

        $response = $this->actingAs($this->studentA)
            ->postJson(route('chat.api.messages.store', ['conversation' => $convId]), [
                'body' => 'Selamat pagi Bapak Dosen, saya ingin konsultasi.',
            ]);

        $response->assertCreated()
            ->assertJsonPath('message.body', 'Selamat pagi Bapak Dosen, saya ingin konsultasi.');

        // 2. Group chat
        $groupRes = $this->actingAs($this->adminX)
            ->postJson(route('chat.api.groups.store'), [
                'title' => 'Grup Koordinasi Magang IT',
                'description' => 'Koordinasi intern',
                'member_ids' => [$this->mentorX->id, $this->studentA->id],
            ]);

        $groupRes->assertCreated();
        $groupId = $groupRes->json('conversation.id');

        $groupMsgRes = $this->actingAs($this->studentA)
            ->postJson(route('chat.api.messages.store', ['conversation' => $groupId]), [
                'body' => 'Halo tim IT, siap bertugas.',
            ]);

        $groupMsgRes->assertCreated()
            ->assertJsonPath('message.body', 'Halo tim IT, siap bertugas.');
    }
}
