<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternshipProject extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_public' => 'boolean',
        'screenshots' => 'array',
        'documents' => 'array',
        'community_photos' => 'array',
    ];

    protected $appends = [
        'photo_url',
        'youtube_embed_url',
        'screenshot_urls',
        'community_photo_urls',
        'source_code_url',
    ];

    public function placement()
    {
        return $this->belongsTo(Placement::class);
    }

    public function likes()
    {
        return $this->hasMany(ProjectLike::class, 'internship_project_id');
    }

    public function comments()
    {
        return $this->hasMany(ProjectComment::class, 'internship_project_id');
    }

    /**
     * Cek apakah proyek sudah disukai oleh pengguna login atau pengunjung tamu
     */
    public function isLikedBy(?User $user, ?string $guestId = null): bool
    {
        if ($user) {
            return $this->likes()->where('user_id', $user->id)->exists();
        }

        if ($guestId) {
            return $this->likes()->where('guest_identifier', $guestId)->exists();
        }

        return false;
    }

    /**
     * URL Berkas Arsip / Source Code Proyek (ZIP)
     */
    public function getSourceCodeUrlAttribute(): ?string
    {
        return $this->normalizeMediaUrl($this->source_code_path);
    }

    /**
     * URL Foto Sampul Utama
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->normalizeMediaUrl($this->photo_path);
    }

    /**
     * Daftar URL Gambar Multi-Screenshot
     */
    public function getScreenshotUrlsAttribute(): array
    {
        if (empty($this->screenshots) || ! is_array($this->screenshots)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($item) {
            $path = is_array($item) ? ($item['path'] ?? null) : $item;
            return $this->normalizeMediaUrl($path);
        }, $this->screenshots)));
    }

    /**
     * Daftar URL Foto Dokumentasi Manfaat Masyarakat
     */
    public function getCommunityPhotoUrlsAttribute(): array
    {
        if (empty($this->community_photos) || ! is_array($this->community_photos)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($item) {
            $path = is_array($item) ? ($item['path'] ?? null) : $item;
            return $this->normalizeMediaUrl($path);
        }, $this->community_photos)));
    }

    /**
     * Ekstraksi Embed Player URL untuk YouTube
     */
    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        if (! $this->video_url) {
            return null;
        }

        $url = trim($this->video_url);

        // Pola YouTube (youtu.be/ID atau youtube.com/watch?v=ID atau youtube.com/embed/ID)
        if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/', $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }

        return null;
    }

    /**
     * Cek apakah URL video merupakan tautan Google Drive
     */
    public function getIsGoogleDriveVideoAttribute(): bool
    {
        if (! $this->video_url) {
            return false;
        }

        return str_contains($this->video_url, 'drive.google.com');
    }

    /**
     * Normalisasi URL Aset / Storage
     */
    public function normalizeMediaUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'images/') || str_starts_with($path, 'assets/')) {
            return asset($path);
        }

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        return asset('storage/' . $path);
    }

    public const CATEGORIES = [
        'teknologi' => [
            'label' => 'Teknologi & Perangkat Lunak',
            'badge' => 'bg-blue-50 text-blue-700 border-blue-200',
        ],
        'buku' => [
            'label' => 'Buku & Karya Tulis Literasi',
            'badge' => 'bg-amber-50 text-amber-800 border-amber-200',
        ],
        'desain' => [
            'label' => 'Desain Grafis & Penataan Ruang',
            'badge' => 'bg-purple-50 text-purple-700 border-purple-200',
        ],
        'preservasi' => [
            'label' => 'Preservasi & Konservasi Kearsipan',
            'badge' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        ],
        'media' => [
            'label' => 'Media Kreatif & Audio-Visual',
            'badge' => 'bg-rose-50 text-rose-700 border-rose-200',
        ],
        'tata_kelola' => [
            'label' => 'Tata Kelola & SOP Layanan',
            'badge' => 'bg-slate-100 text-slate-800 border-slate-300',
        ],
        'lainnya' => [
            'label' => 'Karya Inovasi Lainnya',
            'badge' => 'bg-teal-50 text-teal-800 border-teal-200',
        ],
    ];

    public function getCategoryLabelAttribute(): string
    {
        $key = strtolower(trim((string) $this->category));
        return self::CATEGORIES[$key]['label'] ?? ($this->category ?: 'Inovasi Umum');
    }

    public function getCategoryBadgeClassAttribute(): string
    {
        $key = strtolower(trim((string) $this->category));
        return self::CATEGORIES[$key]['badge'] ?? 'bg-slate-100 text-slate-700 border-slate-200';
    }
}
