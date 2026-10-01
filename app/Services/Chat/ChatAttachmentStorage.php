<?php

namespace App\Services\Chat;

use App\Models\ChatAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Penyimpanan lampiran chat di disk private 'local' (storage/app/private/chat/...).
 * Nama berkas fisik dibuat server (UUID); jenis file dicek dari isinya, bukan dari nama unggahan.
 */
class ChatAttachmentStorage
{
    public const DISK = 'local';

    /**
     * @return array{path: string, name: string, mime: string, size: int, kind: string}
     */
    public function store(UploadedFile $file, int $conversationId, bool $voiceNote = false): array
    {
        $allowed = self::allowedExtensions();
        $clientExtension = strtolower($file->getClientOriginalExtension());
        // Pakai ekstensi asli bila diizinkan (mis. "mp3", bukan tebakan PHP "mpga")
        $extension = in_array($clientExtension, $allowed, true) ? $clientExtension : strtolower((string) $file->guessExtension());
        $mime = $file->getMimeType() ?: 'application/octet-stream';

        $directory = 'chat/' . $conversationId . '/' . now()->format('Y-m');
        $path = $file->storeAs($directory, Str::uuid() . '.' . $extension, self::DISK);

        return [
            'path' => $path,
            'name' => $voiceNote ? 'Pesan suara.' . $extension : $this->cleanName($file->getClientOriginalName(), $extension),
            'mime' => $mime,
            'size' => (int) $file->getSize(),
            'kind' => self::kindFor($extension, $mime, $voiceNote),
        ];
    }

    public function absolutePath(ChatAttachment $attachment): ?string
    {
        if (empty($attachment->path) || str_contains($attachment->path, '..')) {
            return null;
        }

        $path = Storage::disk(self::DISK)->path($attachment->path);

        return is_file($path) ? $path : null;
    }

    public function deletePaths(iterable $paths): void
    {
        foreach ($paths as $path) {
            if ($path && !str_contains($path, '..')) {
                Storage::disk(self::DISK)->delete($path);
            }
        }
    }

    /**
     * Voice note hasil rekaman browser berformat WebM/MP4 yang oleh PHP sering dikenali sebagai video,
     * jadi penanda voice note dari browser menentukan tampilannya sebagai pemutar audio.
     */
    public static function kindFor(string $extension, string $mime, bool $voiceNote = false): string
    {
        if ($voiceNote || str_starts_with($mime, 'audio/') || in_array($extension, config('chat.allowed_extensions.audio', []), true)) {
            return ChatAttachment::KIND_AUDIO;
        }

        foreach ([ChatAttachment::KIND_IMAGE, ChatAttachment::KIND_VIDEO] as $kind) {
            if (in_array($extension, config("chat.allowed_extensions.$kind", []), true)) {
                return $kind;
            }
        }

        return ChatAttachment::KIND_DOCUMENT;
    }

    public static function allowedExtensions(): array
    {
        return array_values(array_unique(array_merge(...array_values(config('chat.allowed_extensions', [])))));
    }

    /**
     * Batas efektif ukuran satu file (KB): tidak boleh melebihi batas php.ini, agar pengguna
     * mendapat pesan yang jelas alih-alih unggahan yang gagal diam-diam.
     */
    public static function maxUploadKb(): int
    {
        return max(1, min(array_filter([
            (int) config('chat.max_upload_kb', 10240),
            self::iniToKb(ini_get('upload_max_filesize')),
            self::maxTotalKb(),
        ], fn ($v) => $v > 0)));
    }

    /**
     * Batas total seluruh lampiran dalam satu pesan (post_max_size dikurangi ruang untuk teks).
     */
    public static function maxTotalKb(): int
    {
        $post = self::iniToKb(ini_get('post_max_size'));

        return $post > 0 ? max(1, $post - 64) : 0;
    }

    private static function iniToKb(string|false $value): int
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '0' || $value === '-1') {
            return 0; // tanpa batas
        }

        $number = (float) $value;

        return (int) match (strtolower(substr($value, -1))) {
            'g' => $number * 1024 * 1024,
            'm' => $number * 1024,
            'k' => $number,
            default => $number / 1024,
        };
    }

    private function cleanName(string $original, string $extension): string
    {
        $base = pathinfo($original, PATHINFO_FILENAME);
        $base = trim(preg_replace('/[^\pL\pN\s._()-]+/u', '', $base)) ?: 'lampiran';

        return Str::limit($base, 120, '') . '.' . $extension;
    }
}
