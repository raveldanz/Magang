<?php

namespace Tests\Feature\Chat;

use App\Models\ChatAttachment;
use App\Models\ChatMessage;
use App\Services\Chat\ChatAttachmentStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ChatAttachmentTest extends ChatTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(ChatAttachmentStorage::DISK);
    }

    public function test_photo_is_stored_privately_and_served_only_to_participants(): void
    {
        $conversationId = $this->startConversation($this->studentA, $this->mentorX);

        $this->sendMessage($this->studentA, $conversationId, ['files' => [UploadedFile::fake()->image('kegiatan hari ini.jpg', 640, 480)]])
            ->assertCreated()
            ->assertJsonPath('message.attachments.0.kind', 'image')
            ->assertJsonPath('message.attachments.0.name', 'kegiatan hari ini.jpg');

        $attachment = ChatAttachment::firstOrFail();
        Storage::disk(ChatAttachmentStorage::DISK)->assertExists($attachment->path);
        $this->assertStringStartsWith("chat/{$conversationId}/", $attachment->path);
        $this->assertFalse(Storage::disk('public')->exists($attachment->path), 'Lampiran tidak boleh di disk publik.');

        $inline = $this->actingAs($this->mentorX)->get(route('chat.attachment', $attachment->id))->assertOk();
        $this->assertStringStartsWith('inline', $inline->headers->get('Content-Disposition'));
        $this->assertSame('nosniff', $inline->headers->get('X-Content-Type-Options'));

        $download = $this->actingAs($this->mentorX)->get(route('chat.attachment', ['attachment' => $attachment->id, 'download' => 1]))->assertOk();
        $this->assertStringStartsWith('attachment', $download->headers->get('Content-Disposition'));
        $this->assertStringContainsString('kegiatan hari ini.jpg', $download->headers->get('Content-Disposition'));

        $this->actingAs($this->adminY)->get(route('chat.attachment', $attachment->id))->assertForbidden();
    }

    public function test_multiple_files_with_caption_are_accepted(): void
    {
        $conversationId = $this->startConversation($this->studentA, $this->dosenA);

        $this->sendMessage($this->studentA, $conversationId, [
            'body' => 'Draf laporan akhir dan dokumentasi, Bu.',
            'files' => [
                UploadedFile::fake()->create('Laporan Akhir.pdf', 120, 'application/pdf'),
                UploadedFile::fake()->image('dokumentasi.png', 300, 200),
            ],
        ])->assertCreated()
            ->assertJsonCount(2, 'message.attachments')
            ->assertJsonPath('message.attachments.0.kind', 'document')
            ->assertJsonPath('message.attachments.1.kind', 'image')
            ->assertJsonPath('message.body', 'Draf laporan akhir dan dokumentasi, Bu.');

        $this->assertSame('Draf laporan akhir dan dokumentasi, Bu.', $this->conversationItem($this->dosenA, $conversationId)['last_message']['preview']);

        $this->actingAs($this->dosenA)->getJson(route('chat.api.conversations.media', $conversationId))
            ->assertOk()
            ->assertJsonCount(2, 'media');
    }

    public function test_voice_note_is_stored_as_audio(): void
    {
        $conversationId = $this->startConversation($this->studentA, $this->mentorX);

        $this->sendMessage($this->studentA, $conversationId, [
            'voice' => 1,
            'files' => [UploadedFile::fake()->create('rekaman.webm', 40, 'audio/webm')],
        ])->assertCreated()
            ->assertJsonPath('message.attachments.0.kind', 'audio')
            ->assertJsonPath('message.attachments.0.name', 'Pesan suara.webm');

        $this->assertSame('🎤 Pesan suara', $this->conversationItem($this->mentorX, $conversationId)['last_message']['preview']);
    }

    public function test_more_than_five_files_is_rejected(): void
    {
        $conversationId = $this->startConversation($this->studentA, $this->mentorX);
        $files = array_map(fn ($i) => UploadedFile::fake()->create("dok{$i}.pdf", 5, 'application/pdf'), range(1, 6));

        $this->sendMessage($this->studentA, $conversationId, ['files' => $files])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('files');
    }

    public function test_dangerous_or_unsupported_files_are_rejected(): void
    {
        $conversationId = $this->startConversation($this->studentA, $this->mentorX);

        foreach ([
            UploadedFile::fake()->create('logo.svg', 5, 'image/svg+xml'),
            UploadedFile::fake()->create('shell.php', 5, 'application/x-php'),
            UploadedFile::fake()->create('halaman.html', 5, 'text/html'),
            UploadedFile::fake()->create('program.exe', 5, 'application/x-msdownload'),
        ] as $file) {
            $this->sendMessage($this->studentA, $conversationId, ['files' => [$file]])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('files.0');
        }

        $this->assertSame(0, ChatMessage::where('type', ChatMessage::TYPE_TEXT)->count());
    }

    public function test_file_over_effective_limit_is_rejected(): void
    {
        $conversationId = $this->startConversation($this->studentA, $this->mentorX);
        $tooBig = UploadedFile::fake()->create('rekaman.pdf', ChatAttachmentStorage::maxUploadKb() + 50, 'application/pdf');

        $this->sendMessage($this->studentA, $conversationId, ['files' => [$tooBig]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('files.0');
    }

    public function test_deleting_message_removes_its_files(): void
    {
        $conversationId = $this->startConversation($this->studentA, $this->mentorX);
        $messageId = $this->sendMessage($this->studentA, $conversationId, ['files' => [UploadedFile::fake()->image('salah kirim.jpg')]])
            ->json('message.id');
        $attachment = ChatAttachment::firstOrFail();

        $this->actingAs($this->studentA)->deleteJson(route('chat.api.messages.destroy', $messageId))
            ->assertOk()
            ->assertJsonPath('message.deleted', true)
            ->assertJsonPath('message.attachments', []);

        Storage::disk(ChatAttachmentStorage::DISK)->assertMissing($attachment->path);
        $this->assertSame(0, ChatAttachment::count());
        $this->actingAs($this->mentorX)->get(route('chat.attachment', $attachment->id))->assertNotFound();
    }
}
