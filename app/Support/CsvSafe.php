<?php

namespace App\Support;

/**
 * Menetralkan sel CSV yang berasal dari input pengguna (nama, NIM, jurusan, nama kelompok)
 * agar tidak dieksekusi sebagai rumus oleh Excel/LibreOffice (CSV formula injection).
 */
class CsvSafe
{
    /**
     * @param  array<int, mixed>  $row
     * @return array<int, mixed>
     */
    public static function row(array $row): array
    {
        return array_map([self::class, 'cell'], $row);
    }

    public static function cell(mixed $value): mixed
    {
        if (! is_string($value) || $value === '' || $value === '-' || is_numeric($value)) {
            return $value;
        }

        // Sel yang diawali =, +, -, @, tab, atau CR diberi awalan tanda kutip tunggal
        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}
