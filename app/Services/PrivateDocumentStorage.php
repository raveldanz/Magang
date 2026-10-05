<?php

namespace App\Services;

use App\Http\Controllers\DocumentController;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Penyimpanan berkas sensitif mahasiswa di disk privat 'local' (root: storage/app/private).
 * Berkas TIDAK bisa diakses lewat URL /storage/... — hanya lewat route terotorisasi.
 */
class PrivateDocumentStorage
{
    public const DISK = 'local';

    /** Folder dokumen pengajuan di dalam disk privat → storage/app/private/documents/applications */
    public const APPLICATION_DIR = 'documents/applications';

    /** Folder privat lain (lampiran logbook, foto profil, lampiran tiket masukan) */
    public const LOGBOOK_DIR = 'documents/logbooks';

    public const PROFILE_PHOTO_DIR = 'profile-photos';

    public const FEEDBACK_DIR = 'attachments/feedbacks';

    /**
     * Simpan berkas apa pun ke disk privat pada folder tertentu.
     */
    public function store(?UploadedFile $file, string $directory): ?string
    {
        if (! $file) {
            return null;
        }

        $path = Storage::disk(self::DISK)->putFile($directory, $file);

        return $path === false ? null : $path;
    }

    /**
     * Hapus satu berkas dari disk privat maupun lokasi publik lama (data sebelum migrasi ke privat).
     */
    public function delete(?string $path): void
    {
        if (empty($path)) {
            return;
        }
        foreach ([self::DISK, 'public'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        }
    }

    /**
     * Response berkas (inline untuk PDF/gambar) dari disk privat, dengan fallback lokasi publik lama.
     * 404 bila berkas tidak ditemukan.
     */
    public static function response(?string $path, string $downloadName, bool $forceDownload = false)
    {
        $location = self::locate($path);
        $realPath = $location
            ? Storage::disk($location['disk'])->path($location['path'])
            : DocumentController::resolveStoredFile($path);

        abort_unless($realPath && is_file($realPath), 404, 'Berkas tidak ditemukan.');

        $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION) ?: 'bin');
        $inline = ! $forceDownload && in_array($ext, ['pdf', 'png', 'jpg', 'jpeg', 'webp'], true);
        $downloadName = preg_replace('/[^A-Za-z0-9._-]+/', '_', $downloadName) ?: 'berkas';
        if (! str_ends_with(strtolower($downloadName), '.'.$ext)) {
            $downloadName .= '.'.$ext;
        }

        return response()->file($realPath, [
            'Content-Type' => mime_content_type($realPath) ?: 'application/octet-stream',
            'Content-Disposition' => ($inline ? 'inline' : 'attachment')."; filename=\"{$downloadName}\"",
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function storeApplicationDocument(?UploadedFile $file): ?string
    {
        if (! $file) {
            return null;
        }

        $path = Storage::disk(self::DISK)->putFile(self::APPLICATION_DIR, $file);

        return $path === false ? null : $path;
    }

    /**
     * Hapus berkas yang sudah terlanjur diunggah (mis. pengajuan dibatalkan karena duplikat).
     *
     * @param  array<int, ?string>  $paths
     */
    public function discard(array $paths): void
    {
        $paths = array_values(array_filter($paths));
        if ($paths) {
            Storage::disk(self::DISK)->delete($paths);
        }
    }

    /**
     * Cari lokasi berkas: disk privat dulu, lalu disk public lama (kompatibilitas data lama).
     *
     * @return array{disk: string, path: string}|null
     */
    public static function locate(?string $relativePath): ?array
    {
        if (empty($relativePath) || str_contains($relativePath, '..')) {
            return null;
        }

        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');

        foreach ([self::DISK, 'public'] as $disk) {
            if (Storage::disk($disk)->exists($relativePath)) {
                return ['disk' => $disk, 'path' => $relativePath];
            }
        }

        return null;
    }
}
