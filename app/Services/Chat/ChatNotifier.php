<?php

namespace App\Services\Chat;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\SystemNotification;
use App\Models\User;

/**
 * Menghubungkan chat ke lonceng Pemberitahuan Sistem (system_notifications).
 * Satu baris per (penerima, percakapan) yang diperbarui setiap pesan baru — bukan satu baris
 * per pesan — agar lonceng tidak banjir. created_at ikut diperbarui supaya entri naik ke atas
 * dan titik merah (NotificationService::hasUnreadDot) muncul lagi. Percakapan yang dibisukan
 * tidak menghasilkan notifikasi.
 */
class ChatNotifier
{
    public const CATEGORY = 'chat';

    public function messageSent(ChatMessage $message, ChatConversation $conversation, User $sender): void
    {
        try {
            $recipients = ChatParticipant::where('conversation_id', $conversation->id)
                ->where('user_id', '!=', $sender->id)
                ->whereNull('muted_at')
                ->get(['user_id', 'last_read_message_id']);

            if ($recipients->isEmpty()) {
                return;
            }

            $url = self::conversationUrl($conversation);
            $preview = $message->preview(120);

            if ($conversation->isDirect()) {
                $recipient = $recipients->first();
                $unread = ChatMessage::where('conversation_id', $conversation->id)
                    ->where('id', '>', (int) $recipient->last_read_message_id)
                    ->where('type', ChatMessage::TYPE_TEXT)
                    ->whereNull('deleted_at')
                    ->where('sender_id', '!=', $recipient->user_id)
                    ->count();
                $title = $unread > 1 ? "{$unread} pesan baru dari {$sender->name}" : "Pesan baru dari {$sender->name}";
            } else {
                $title = "Pesan baru di {$conversation->title}";
                $preview = ChatPresenter::shortName($sender->name) . ': ' . $preview;
            }

            $this->upsert($recipients->pluck('user_id')->map(fn ($id) => (int) $id), $url, $title, $preview);
        } catch (\Throwable $e) {
            // Gagal membuat notifikasi tidak boleh membatalkan pesan yang sudah terkirim
            report($e);
        }
    }

    public function conversationRead(ChatConversation $conversation, User $reader): void
    {
        SystemNotification::where('user_id', $reader->id)
            ->where('category', self::CATEGORY)
            ->where('action_url', self::conversationUrl($conversation))
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Hapus entri lonceng milik anggota yang keluar/dikeluarkan dari grup.
     */
    public function forget(ChatConversation $conversation, array $userIds): void
    {
        if ($userIds) {
            SystemNotification::whereIn('user_id', $userIds)
                ->where('category', self::CATEGORY)
                ->where('action_url', self::conversationUrl($conversation))
                ->delete();
        }
    }

    /**
     * URL relatif, supaya entri tetap cocok walau host (localhost / 127.0.0.1 / domain) berbeda.
     */
    public static function conversationUrl(ChatConversation $conversation): string
    {
        return route('chat.show', $conversation->id, false);
    }

    /**
     * Perbarui entri yang sudah ada dan buat yang belum ada — 3 query berapa pun jumlah anggota grup.
     */
    private function upsert($userIds, string $url, string $title, string $message): void
    {
        $now = now();
        $values = [
            'type' => 'info',
            'icon' => '💬',
            'title' => $title,
            'message' => $message,
            'action_label' => 'Balas Pesan',
            'read_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $existing = SystemNotification::where('category', self::CATEGORY)
            ->where('action_url', $url)
            ->whereIn('user_id', $userIds)
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id);

        if ($existing->isNotEmpty()) {
            SystemNotification::where('category', self::CATEGORY)
                ->where('action_url', $url)
                ->whereIn('user_id', $existing)
                ->update($values);
        }

        $missing = $userIds->diff($existing)->values();
        if ($missing->isNotEmpty()) {
            SystemNotification::insert($missing->map(fn ($id) => $values + [
                'user_id' => $id,
                'target_role' => null,
                'category' => self::CATEGORY,
                'action_url' => $url,
            ])->all());
        }
    }
}
