<?php

namespace App\Enums;

use App\Enums\Concerns\DisplaysStatusCode;

/**
 * Status tiket feedback / aduan sistem (system_feedbacks.status).
 */
enum FeedbackStatus: string
{
    use DisplaysStatusCode;

    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';

    public function description(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Ditinjau',
            self::IN_PROGRESS => 'Sedang Ditangani',
            self::RESOLVED => 'Selesai Ditangani',
            self::CLOSED => 'Ditutup',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::PENDING => 'bg-amber-50 text-amber-700 border-amber-300',
            self::IN_PROGRESS => 'bg-blue-50 text-blue-700 border-blue-300',
            self::RESOLVED => 'bg-emerald-50 text-emerald-700 border-emerald-300',
            self::CLOSED => 'bg-slate-100 text-slate-700 border-slate-300',
        };
    }

    public function dotColor(): string
    {
        return match ($this) {
            self::PENDING => 'bg-amber-500',
            self::IN_PROGRESS => 'bg-blue-500',
            self::RESOLVED => 'bg-emerald-500',
            self::CLOSED => 'bg-slate-400',
        };
    }
}
