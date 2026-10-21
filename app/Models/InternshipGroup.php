<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class InternshipGroup extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'letter_date' => 'date',
    ];

    /**
     * Generate kode unik kelompok yang mudah dibaca (misal: GRP-7K9A2P).
     */
    public static function generateUniqueCode(): string
    {
        do {
            $code = 'GRP-' . strtoupper(Str::random(6));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    /**
     * Memastikan kelompok memiliki token acak untuk QR verifikasi surat balasan bersama.
     */
    public function ensureLetterToken(): string
    {
        if (empty($this->letter_token)) {
            static::whereKey($this->getKey())
                ->where(fn ($q) => $q->whereNull('letter_token')->orWhere('letter_token', ''))
                ->update(['letter_token' => Str::random(32)]);

            $this->letter_token = static::whereKey($this->getKey())->value('letter_token');
            $this->syncOriginalAttribute('letter_token');
        }

        return $this->letter_token;
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_user_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'group_id');
    }

    public function leaderApplication(): HasOne
    {
        return $this->hasOne(Application::class, 'group_id')->where('role_in_group', 'leader');
    }

    public function getMemberCountAttribute(): int
    {
        return $this->applications()->count();
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isWaitingApproval(): bool
    {
        return $this->status === 'waiting_approval';
    }

    public function isAccepted(): bool
    {
        return in_array($this->status, ['accepted', 'active', 'completed']);
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
