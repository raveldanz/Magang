<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Lampiran pesan chat, disimpan di disk private 'local' dan hanya diunduh lewat route chat.attachment.
 */
class ChatAttachment extends Model
{
    public const KIND_IMAGE = 'image';
    public const KIND_VIDEO = 'video';
    public const KIND_AUDIO = 'audio';
    public const KIND_DOCUMENT = 'document';

    protected $fillable = [
        'message_id',
        'path',
        'name',
        'mime',
        'size',
        'kind',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function message()
    {
        return $this->belongsTo(ChatMessage::class, 'message_id');
    }

    /**
     * Jenis yang aman ditampilkan langsung di browser (selain itu selalu diunduh).
     */
    public function isPreviewable(): bool
    {
        return in_array($this->kind, [self::KIND_IMAGE, self::KIND_VIDEO, self::KIND_AUDIO], true)
            || $this->mime === 'application/pdf';
    }
}
