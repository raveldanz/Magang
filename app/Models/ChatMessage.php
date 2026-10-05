<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ChatMessage extends Model
{
    public const TYPE_TEXT = 'text';

    // Pesan sistem: "Budi ditambahkan sebagai Mentor Lapangan", "Ani keluar dari grup", dst.
    public const TYPE_SYSTEM = 'system';

    public const DELETED_LABEL = 'Pesan ini telah dihapus';

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'parent_id',
        'comments_count',
        'type',
        'body',
        'reply_to_id',
        'meta',
        'deleted_at',
        'deleted_by',
    ];

    protected $casts = [
        'meta' => 'array',
        'deleted_at' => 'datetime',
        'comments_count' => 'integer',
    ];

    public function conversation()
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function attachments()
    {
        return $this->hasMany(ChatAttachment::class, 'message_id');
    }

    public function replyTo()
    {
        return $this->belongsTo(self::class, 'reply_to_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function comments()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function isComment(): bool
    {
        return $this->parent_id !== null;
    }

    public function isBroadcast(): bool
    {
        return $this->parent_id === null;
    }

    public function isDeleted(): bool
    {
        return $this->deleted_at !== null;
    }

    public function isSystem(): bool
    {
        return $this->type === self::TYPE_SYSTEM;
    }

    /**
     * Teks ringkas untuk daftar percakapan, notifikasi, dan kutipan balasan.
     */
    public function preview(int $limit = 80): string
    {
        if ($this->isDeleted()) {
            return '🚫 '.self::DELETED_LABEL;
        }

        $body = trim((string) $this->body);
        if ($body !== '') {
            return Str::limit(preg_replace('/\s+/', ' ', $body), $limit);
        }

        $attachments = $this->attachments;
        $first = $attachments->first();
        if (! $first) {
            return '';
        }

        $label = match ($first->kind) {
            ChatAttachment::KIND_IMAGE => '📷 Foto',
            ChatAttachment::KIND_VIDEO => '🎬 Video',
            ChatAttachment::KIND_AUDIO => '🎤 Pesan suara',
            default => '📎 '.$first->name,
        };

        return $attachments->count() > 1 ? $label.' +'.($attachments->count() - 1).' lampiran' : $label;
    }
}
