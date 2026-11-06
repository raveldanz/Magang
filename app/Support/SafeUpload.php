<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

/**
 * Ekstensi berkas unggahan diambil dari ISI berkas (deteksi MIME), bukan dari nama berkas
 * kiriman klien. Aturan validasi `image`/`mimes` hanya memeriksa isi, sehingga memakai
 * getClientOriginalExtension() memungkinkan PNG bernama "x.html" tersimpan sebagai .html
 * di folder publik (stored XSS pada origin aplikasi).
 */
class SafeUpload
{
    /**
     * @param  array<int, string>  $allowed  ekstensi yang boleh dipakai (huruf kecil)
     */
    public static function extension(UploadedFile $file, array $allowed, string $fallback = 'bin'): string
    {
        $ext = strtolower((string) $file->guessExtension());

        if ($ext === 'jpeg') {
            $ext = 'jpg';
        }

        $allowed = array_map('strtolower', $allowed);
        if (in_array('jpeg', $allowed, true)) {
            $allowed[] = 'jpg';
        }

        return in_array($ext, $allowed, true) ? $ext : $fallback;
    }
}
