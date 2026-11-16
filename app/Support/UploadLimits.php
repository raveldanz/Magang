<?php

namespace App\Support;

/**
 * Batas unggah yang benar-benar berlaku di PHP yang sedang berjalan (php.ini / opsi -d).
 *
 * Dikirim ke browser lewat meta tag di layout agar penjaga unggah (resources/js/app.js) menolak
 * berkas SEBELUM dikirim. Bila total kiriman melebihi post_max_size, PHP membuang seluruh isi
 * form (termasuk biodata yang sudah diketik) dan berkas tetap terunggah penuh lebih dulu,
 * sehingga halaman lama "loading" lalu kembali kosong.
 */
class UploadLimits
{
    /** Batas satu berkas dari PHP (upload_max_filesize), dalam KB. */
    public static function phpFileMaxKb(): int
    {
        return self::toKb((string) ini_get('upload_max_filesize'));
    }

    /** Batas total satu kiriman form dari PHP (post_max_size), dalam KB. 0 = tanpa batas. */
    public static function phpPostMaxKb(): int
    {
        return self::toKb((string) ini_get('post_max_size'));
    }

    /** Ubah notasi php.ini ("2M", "512K", "1G", "8388608") ke KB. */
    public static function toKb(string $value): int
    {
        $value = trim($value);
        if ($value === '' || ! preg_match('/^(\d+(?:\.\d+)?)\s*([KMG]?)/i', $value, $m)) {
            return 0;
        }

        $number = (float) $m[1];

        return (int) floor(match (strtoupper($m[2])) {
            'G' => $number * 1024 * 1024,
            'M' => $number * 1024,
            'K' => $number,
            default => $number / 1024,
        });
    }
}
