<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatConversation extends Model
{
    public const TYPE_DIRECT = 'direct';

    public const TYPE_GROUP = 'group';

    // Grup Bimbingan otomatis per penempatan (mahasiswa + mentor + DPL), anggotanya dikelola sistem
    public const TYPE_PLACEMENT = 'placement';

    // Saluran Siaran Pengumuman Resmi tingkat Dinas Kota dan Universitas
    public const TYPE_CHANNEL = 'channel';

    public const SCOPE_GOVERNMENT = 'government';

    public const SCOPE_UNIVERSITY = 'university';

    public const SCOPE_MENTOR_GUIDANCE = 'mentor_guidance';

    public const SCOPE_DPL_GUIDANCE = 'dpl_guidance';

    protected $fillable = [
        'type',
        'scope_type',
        'university_id',
        'agency_profile_id',
        'image_url',
        'is_broadcast_only',
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
        'is_broadcast_only' => 'boolean',
    ];

    public function participants()
    {
        return $this->hasMany(ChatParticipant::class, 'conversation_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'chat_participants', 'conversation_id', 'user_id');
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

    public function university()
    {
        return $this->belongsTo(University::class, 'university_id');
    }

    public function agencyProfile()
    {
        return $this->belongsTo(AgencyProfile::class, 'agency_profile_id');
    }

    public function isDirect(): bool
    {
        return $this->type === self::TYPE_DIRECT;
    }

    public function isChannel(): bool
    {
        return $this->type === self::TYPE_CHANNEL;
    }

    public function isMentorGuidance(): bool
    {
        return $this->scope_type === self::SCOPE_MENTOR_GUIDANCE;
    }

    public function isDplGuidance(): bool
    {
        return $this->scope_type === self::SCOPE_DPL_GUIDANCE;
    }

    public function isGuidanceGroup(): bool
    {
        return in_array($this->scope_type, [self::SCOPE_MENTOR_GUIDANCE, self::SCOPE_DPL_GUIDANCE], true)
            || $this->type === self::TYPE_PLACEMENT;
    }

    /**
     * Grup buatan staf maupun Grup Bimbingan (bukan 1-on-1 dan bukan channel).
     */
    public function isGroup(): bool
    {
        return ! $this->isDirect() && ! $this->isChannel();
    }

    /**
     * Kunci unik percakapan 1-on-1, tidak bergantung urutan siapa yang memulai.
     */
    public static function directKeyFor(int $userA, int $userB): string
    {
        return min($userA, $userB).':'.max($userA, $userB);
    }
}
