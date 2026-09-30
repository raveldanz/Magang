<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatConversation extends Model
{
    public const TYPE_DIRECT = 'direct';
    public const TYPE_GROUP = 'group';
    // Grup Bimbingan otomatis per penempatan (mahasiswa + mentor + DPL), anggotanya dikelola sistem
    public const TYPE_PLACEMENT = 'placement';

    protected $fillable = [
        'type',
        'title',
        'description',
        'direct_key',
        'placement_id',
        'created_by',
        'meta_version',
        'last_message_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'meta_version' => 'integer',
    ];

    public function participants()
    {
        return $this->hasMany(ChatParticipant::class, 'conversation_id');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    public function lastMessage()
    {
        return $this->belongsTo(ChatMessage::class, 'last_message_id');
    }

    public function placement()
    {
        return $this->belongsTo(Placement::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isDirect(): bool
    {
        return $this->type === self::TYPE_DIRECT;
    }

    /**
     * Grup buatan staf maupun Grup Bimbingan.
     */
    public function isGroup(): bool
    {
        return !$this->isDirect();
    }

    /**
     * Kunci unik percakapan 1-on-1, tidak bergantung urutan siapa yang memulai.
     */
    public static function directKeyFor(int $userA, int $userB): string
    {
        return min($userA, $userB) . ':' . max($userA, $userB);
    }
}
