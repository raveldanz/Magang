<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Satu kali proses sinkron direktori institusi (dari tombol admin atau jadwal). */
class InstitutionSyncRun extends Model
{
    /** Proses aktif yang tidak bergerak selama ini dianggap terhenti. */
    public const STALL_MINUTES = 15;

    public const ACTIVE_STATUSES = ['queued', 'running'];

    protected $guarded = ['id'];

    protected $casts = [
        'forms' => 'array',
        'failed_steps' => 'array',
        'finished_at' => 'datetime',
    ];

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    public function isStalled(): bool
    {
        return $this->isActive() && $this->updated_at->lt(now()->subMinutes(self::STALL_MINUTES));
    }
}
