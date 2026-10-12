<?php

namespace App\Enums;

/**
 * Daftar jenis status yang dirender <x-status-badge type="..."> dan <x-status-legend type="...">.
 */
final class StatusType
{
    private const MAP = [
        'application' => ApplicationStatus::class,
        'review' => ReviewStatus::class,
        'account' => AccountStatus::class,
        'feedback' => FeedbackStatus::class,
    ];

    /** @return class-string<\BackedEnum> */
    public static function enumFor(string $type): string
    {
        return self::MAP[$type] ?? throw new \InvalidArgumentException("Jenis status tidak dikenal: {$type}");
    }
}
