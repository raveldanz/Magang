<?php

namespace App\Services\Chat;

use App\Models\AuditLog;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\SystemFeedback;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Mengirim, menghapus, dan melaporkan pesan chat.
 */
class ChatMessageService
{
    public function __construct(
        private ChatService $chat,
        private ChatAttachmentStorage $storage,
        private ChatNotifier $notifier,
        private ChatChannelService $channels,
    ) {}

    /**
     * @param  UploadedFile[]  $files
     */
    public function send(ChatConversation $conversation, User $sender, ?string $body, array $files = [], ?int $replyToId = null, bool $voiceNote = false): ChatMessage
    {
        $participant = $this->chat->participantOrFail($conversation, $sender);
        $this->assertRecipientActive($conversation, $sender);

        if ($conversation->isChannel()) {
            if (! $this->channels->canPublishAnnouncement($conversation, $sender)) {
                throw new AuthorizationException('Hanya Pengelola Saluran yang dapat mempublikasikan pengumuman di sini.');
            }
        }

        if ($replyToId && ! ChatMessage::where('conversation_id', $conversation->id)->whereKey($replyToId)->exists()) {
            throw ValidationException::withMessages(['reply_to_id' => 'Pesan yang dibalas tidak ditemukan di percakapan ini.']);
        }

        $stored = [];
        try {
            foreach ($files as $file) {
                $stored[] = $this->storage->store($file, $conversation->id, $voiceNote);
            }

            $message = DB::transaction(function () use ($conversation, $participant, $sender, $body, $replyToId, $stored) {
                $message = ChatMessage::create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $sender->id,
                    'type' => ChatMessage::TYPE_TEXT,
                    'body' => $body !== null && trim($body) !== '' ? $body : null,
                    'reply_to_id' => $replyToId,
                ]);
                foreach ($stored as $attachment) {
                    $message->attachments()->create($attachment);
                }

                $conversation->forceFill(['last_message_id' => $message->id, 'last_message_at' => $message->created_at])->save();
                // Pesan sendiri otomatis terbaca oleh pengirim
                $participant->forceFill(['last_read_message_id' => $message->id, 'last_read_at' => now()])->save();

                return $message;
            });
        } catch (\Throwable $e) {
            $this->storage->deletePaths(array_column($stored, 'path'));
            throw $e;
        }

        $this->chat->stopTyping($conversation, $sender);
        $this->notifier->messageSent($message, $conversation, $sender);

        return $message->load(['sender', 'attachments', 'replyTo.sender', 'replyTo.attachments']);
    }

    /**
     * Mengirim komentar audiens di bawah pesan siaran pengumuman resmi.
     */
    public function sendComment(ChatMessage $parent, User $sender, string $body): ChatMessage
    {
        $conversation = $parent->conversation;
        $this->chat->participantOrFail($conversation, $sender);

        if ($parent->isDeleted()) {
            throw ValidationException::withMessages(['body' => 'Tidak dapat memberikan komentar pada pengumuman yang telah dihapus.']);
        }

        $trimmed = trim($body);
        if ($trimmed === '') {
            throw ValidationException::withMessages(['body' => 'Komentar tidak boleh kosong.']);
        }

        $comment = DB::transaction(function () use ($conversation, $parent, $sender, $trimmed) {
            $comment = ChatMessage::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'parent_id' => $parent->id,
                'type' => ChatMessage::TYPE_TEXT,
                'body' => $trimmed,
            ]);

            $parent->increment('comments_count');

            return $comment;
        });

        return $comment->load(['sender', 'parent.sender']);
    }

    /**
     * Hapus pesan untuk semua orang: isi & lampiran dibuang, tersisa penanda "Pesan ini telah dihapus".
     * Pengirim boleh menghapus pesannya; Super Admin boleh menghapus pesan siapa pun (moderasi).
     * $moderation = true dipakai dari tiket laporan (Super Admin tidak harus anggota percakapan).
     */
    public function delete(ChatMessage $message, User $actor, bool $moderation = false): ChatMessage
    {
        $isSender = $message->sender_id !== null && (int) $message->sender_id === (int) $actor->id;

        if (! $isSender && ! $actor->isSuperAdmin()) {
            throw new AuthorizationException('Anda hanya dapat menghapus pesan Anda sendiri.');
        }
        if (! $moderation) {
            $this->chat->participantOrFail($message->conversation, $actor);
        }
        if ($message->isSystem()) {
            throw new AuthorizationException('Pesan sistem tidak dapat dihapus.');
        }
        if ($message->isDeleted()) {
            return $message;
        }

        $paths = $message->attachments()->pluck('path')->all();
        DB::transaction(function () use ($message, $actor) {
            $message->attachments()->delete();
            $message->forceFill(['body' => null, 'deleted_at' => now(), 'deleted_by' => $actor->id])->save();

            if ($message->parent_id) {
                ChatMessage::whereKey($message->parent_id)->decrement('comments_count');
            }
        });
        $this->storage->deletePaths($paths);

        if (! $isSender) {
            AuditLog::record('CHAT_MESSAGE_MODERATED', 'ChatMessage', $message->id, [
                'sender' => $message->sender?->name,
                'conversation_id' => $message->conversation_id,
            ]);
        }

        return $message->fresh(['sender', 'attachments', 'replyTo.sender', 'replyTo.attachments']);
    }

    /**
     * Laporkan pesan tidak pantas → tiket "Laporan Pesan Chat" di modul Feedback Super Admin,
     * berisi salinan pesan (Super Admin tidak perlu membuka percakapan pribadi orang lain).
     */
    public function report(ChatMessage $message, User $reporter, string $reason, ?string $note): SystemFeedback
    {
        $conversation = $message->conversation;
        $this->chat->participantOrFail($conversation, $reporter);

        if ((int) $message->sender_id === (int) $reporter->id) {
            throw ValidationException::withMessages(['reason' => 'Anda tidak dapat melaporkan pesan Anda sendiri.']);
        }
        if ($message->isSystem() || $message->isDeleted()) {
            throw ValidationException::withMessages(['reason' => 'Pesan ini tidak dapat dilaporkan.']);
        }
        if (SystemFeedback::where('user_id', $reporter->id)->where('chat_message_id', $message->id)->exists()) {
            throw ValidationException::withMessages(['reason' => 'Anda sudah melaporkan pesan ini. Tim Super Admin sedang meninjaunya.']);
        }

        $sender = $message->sender;
        $reasonLabel = config("chat.report_reasons.$reason", 'Lainnya');
        $attachments = $message->attachments->pluck('name')->implode(', ');

        $snapshot = implode("\n", array_filter([
            "Pelapor: {$reporter->name} (".ChatPresenter::roleLabel($reporter).')',
            'Pengirim pesan: '.($sender ? "{$sender->name} (".ChatPresenter::roleLabel($sender).") · {$sender->email}" : 'Pengguna dihapus'),
            'Percakapan: '.($conversation->isGroup() ? "Grup \"{$conversation->title}\"" : 'Chat 1-on-1')." (#{$conversation->id})",
            'Waktu pesan: '.$message->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i').' WIB',
            "Alasan: {$reasonLabel}",
            $note ? "Catatan pelapor: {$note}" : null,
            '',
            'Isi pesan:',
            '"'.($message->body ?: '(tanpa teks)').'"',
            $attachments ? "Lampiran: {$attachments}" : null,
        ], fn ($line) => $line !== null));

        $feedback = SystemFeedback::create([
            'user_id' => $reporter->id,
            'sender_name' => $reporter->name,
            'sender_email' => $reporter->email,
            'sender_role' => $reporter->role,
            'target_role' => 'super_admin',
            'category' => SystemFeedback::CATEGORY_CHAT_REPORT,
            'subject' => "Laporan Pesan Chat: {$reasonLabel}",
            'message' => $snapshot,
            'priority' => 'high',
            'status' => 'pending',
            'chat_message_id' => $message->id,
        ]);

        SystemNotification::send(
            title: "Laporan Pesan Chat: {$reasonLabel}",
            message: "Dari {$reporter->name}: pesan ".($sender?->name ? "{$sender->name} " : '').'dilaporkan dan perlu ditinjau.',
            userId: null,
            targetRole: 'super_admin',
            actionUrl: route('admin.feedbacks.show', $feedback->id),
            actionLabel: 'Tinjau Laporan',
            type: 'urgent',
            category: 'feedback',
            icon: '🚩'
        );

        AuditLog::record('CHAT_MESSAGE_REPORTED', 'ChatMessage', $message->id, [
            'reporter' => $reporter->name,
            'reason' => $reason,
            'feedback_id' => $feedback->id,
        ]);

        return $feedback;
    }

    private function assertRecipientActive(ChatConversation $conversation, User $sender): void
    {
        if (! $conversation->isDirect()) {
            return;
        }

        $other = ChatParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', '!=', $sender->id)
            ->with('user')
            ->first();

        if ($other?->user?->isInactive()) {
            throw ValidationException::withMessages(['body' => 'Akun penerima sudah dinonaktifkan, pesan tidak dapat dikirim.']);
        }
    }
}
