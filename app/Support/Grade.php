<?php

namespace App\Support;

class Grade
{
    /**
     * Konversi nilai numerik (0 - 100) ke skala huruf baku:
     * A  >= 85
     * AB >= 75
     * B  >= 65
     * BC >= 55
     * C  >= 40
     * E  < 40
     */
    public static function letter(?float $score): string
    {
        if ($score === null) {
            return '-';
        }

        if ($score >= 85) {
            return 'A';
        }
        if ($score >= 75) {
            return 'AB';
        }
        if ($score >= 65) {
            return 'B';
        }
        if ($score >= 55) {
            return 'BC';
        }
        if ($score >= 40) {
            return 'C';
        }

        return 'E';
    }

    /**
     * Alias untuk letter($score)
     */
    public static function fromScore(?float $score): string
    {
        return self::letter($score);
    }

    /**
     * Label predikat verbal lengkap sesuai huruf mutu.
     */
    public static function label(?float $score): string
    {
        $letter = self::letter($score);

        return match ($letter) {
            'A' => 'A (Sangat Baik / Unggul)',
            'AB' => 'AB (Baik Sekali)',
            'B' => 'B (Baik)',
            'BC' => 'BC (Cukup Baik)',
            'C' => 'C (Cukup)',
            'E' => 'E (Tidak Lulus)',
            default => '-',
        };
    }
}
