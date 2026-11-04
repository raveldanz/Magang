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

    public function members()
    {
        return $this->hasMany(InternshipProjectMember::class, 'internship_project_id');
    }

    public function collaborators()
    {
        return $this->belongsToMany(Placement::class, 'internship_project_members')
            ->withPivot(['role_in_project', 'is_leader'])
            ->withTimestamps();
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
            'label' => 'Sistem Digital',
            'badge' => 'bg-blue-100 text-blue-800 border-blue-300',
            'bg' => '#dbeafe',
            'text' => '#1e40af',
            'border' => '#93c5fd',
        ],
        'buku' => [
            'label' => 'Karya Literasi',
            'badge' => 'bg-amber-100 text-amber-900 border-amber-300',
            'bg' => '#fef3c7',
            'text' => '#92400e',
            'border' => '#fcd34d',
        ],
        'media' => [
            'label' => 'Media Kreatif',
            'badge' => 'bg-rose-100 text-rose-800 border-rose-300',
            'bg' => '#ffe4e6',
            'text' => '#9f1239',
            'border' => '#fda4af',
        ],
        'desain' => [
            'label' => 'Media Kreatif',
            'badge' => 'bg-rose-100 text-rose-800 border-rose-300',
            'bg' => '#ffe4e6',
            'text' => '#9f1239',
            'border' => '#fda4af',
        ],
        'preservasi' => [
            'label' => 'Preservasi Kearsipan',
            'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'bg' => '#d1fae5',
            'text' => '#065f46',
            'border' => '#6ee7b7',
        ],
        'tata_kelola' => [
            'label' => 'Tata Kelola Layanan',
            'badge' => 'bg-slate-200 text-slate-800 border-slate-300',
            'bg' => '#e2e8f0',
            'text' => '#1e293b',
            'border' => '#cbd5e1',
        ],
        'lainnya' => [
            'label' => 'Inovasi Lainnya',
            'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'bg' => '#d1fae5',
            'text' => '#065f46',
            'border' => '#6ee7b7',
        ],
    ];

    /**
     * Rekomendasi peran dinamis berdasarkan kategori proyek
     */
    public const ROLE_RECOMMENDATIONS = [
        'teknologi' => ['Koordinator', 'Frontend', 'Backend', 'UI/UX', 'Mobile Dev', 'Database', 'QA'],
        'buku' => ['Penulis Naskah', 'Penyusun Antologi', 'Editor Bahasa', 'Ilustrator', 'Layout', 'Riset Materi'],
        'media' => ['Desainer Grafis', 'Videografer', 'Editor Video', 'Ilustrator', 'Voice Over', 'Penata Display', 'Dokumenter'],
        'desain' => ['Desainer Grafis', 'Videografer', 'Editor Video', 'Ilustrator', 'Voice Over', 'Penata Display', 'Dokumenter'],
        'preservasi' => ['Restorator Fisik', 'Alih Media Digital', 'Kurator Metadata', 'Fumigasi', 'Inventarisasi Arsip'],
        'tata_kelola' => ['Penyusun SOP', 'Analis Alur Layanan', 'Surveyor', 'Dokumenter', 'Notulis'],
        'lainnya' => ['Koordinator', 'Pelaksana Teknis', 'Riset', 'Dokumentasi', 'Publikasi'],
    ];

    /**
     * Kamus kata kunci pintar untuk mencocokkan input kategori lainnya dengan kategori standar
     */
    public const CATEGORY_KEYWORDS = [
        'teknologi' => [
            'it', 'web', 'website', 'aplikasi', 'software', 'coding', 'koding', 'pemrograman', 'program', 'programmer', 
            'developer', 'ui', 'ux', 'ui/ux', 'mobile', 'frontend', 'backend', 'fullstack', 'database', 'basis data', 
            'android', 'ios', 'flutter', 'laravel', 'react', 'vue', 'digital', 'sistem digital', 'sistem informasi', 
            'komputer', 'jaringan', 'iot', 'ai', 'kecerdasan buatan', 'machine learning', 'api', 'server', 'cloud', 
            'otomasi digital', 'bot', 'chatbot', 'dashboard', 'portal web', 'e-library', 'perpustakaan digital', 
            'arsip digital', 'katalog online', 'opac'
        ],
        'buku' => [
            'buku', 'karya literasi', 'literasi', 'buku bacaan', 'novel', 'cerita', 'cerpen', 'cerita anak', 'cerita pendek', 
            'cerita rakyat', 'antologi', 'kamus', 'modul', 'modul ajar', 'ensiklopedia', 'bacaan', 'karya tulis', 
            'karya ilmiah', 'puisi', 'pantun', 'komik', 'penulisan naskah', 'naskah buku', 'penulisan buku', 'resensi', 
            'jurnal', 'artikel', 'buletin', 'majalah', 'sinopsis', 'esai', 'storytelling', 'dongeng', 'mendongeng', 
            'pojok baca', 'minat baca', 'klub buku', 'literasi inklusif', 'braille', 'buku saku', 'panduan membaca'
        ],
        'media' => [
            'media kreatif', 'videografi', 'videografer', 'video', 'edit video', 'editing video', 'video editor', 
            'sinematografi', 'film', 'film pendek', 'dokumenter', 'animasi', 'motion graphic', 'fotografi', 'fotografer', 
            'foto', 'photoshoot', 'pemotretan', 'podcast', 'audio', 'audiobook', 'rekaman suara', 'voice over', 
            'narasi suara', 'multimedia', 'desain grafis', 'grafis', 'desain', 'desainer', 'karya seni', 'seni rupa', 
            'seni visual', 'seni lukis', 'lukisan', 'melukis', 'sketsa', 'kaligrafi', 'lettering', 'tipografi', 
            'mural', 'ilustrasi', 'ilustrator', 'poster', 'infografis', 'flyer', 'brosur', 'leaflet', 'pamflet', 
            'banner', 'spanduk', 'baliho', 'dekorasi visual', 'penata display', 'display pameran', 'maket', 
            'interior ruang', 'tata ruang', 'wayfinding', 'signage', 'penunjuk arah', 'branding', 'logo', 
            'feed instagram', 'konten kreatif', 'media sosial', 'youtube', 'reels', 'tiktok', 'katalog visual'
        ],
        'preservasi' => [
            'preservasi', 'preservasi kearsipan', 'konservasi', 'konservasi arsip', 'arsip sejarah', 'sejarah', 
            'sejarah lokal', 'sejarah surabaya', 'naskah kuno', 'manuskrip', 'naskah bersejarah', 'dokumen sejarah', 
            'presentasi sejarah', 'kajian sejarah', 'telaah sejarah', 'cagar budaya', 'restorasi', 'restorasi naskah', 
            'fumigasi', 'laminasi', 'enkapsulasi', 'alih media arsip', 'scanning arsip', 'digitalisasi arsip', 
            'arsip kuno', 'arsip statis', 'arsip vital', 'khazanah arsip', 'perawatan arsip', 'perawatan fisik', 
            'penjilidan arsip', 'penyelamatan arsip', 'inventarisasi arsip', 'kurasi arsip', 'pameran arsip', 
            'diorama sejarah', 'arsiparis'
        ],
        'tata_kelola' => [
            'tata kelola', 'tata kelola layanan', 'sop', 'standar operasional', 'standar operasional prosedur', 
            'standar pelayanan', 'maklumat pelayanan', 'pedoman kerja', 'pedoman tata kelola', 'alur layanan', 
            'alur kerja', 'kebijakan', 'regulasi', 'aturan', 'prosedur kerja', 'analisis jabatan', 'survei kepuasan', 
            'ikm', 'indeks kepuasan masyarakat', 'kuesioner', 'evaluasi layanan', 'penilaian kinerja', 
            'jadwal retensi arsip', 'jra', 'penyusutan arsip', 'pemusnahan arsip', 'tata naskah dinas', 
            'penataan berkas', 'klasifikasi arsip', 'pengelolaan arsip inaktif', 'arsip dinamis', 
            'administrasi perkantoran', 'manajemen kantor', 'reformasi birokrasi', 'pelayanan publik', 'inovasi layanan'
        ],
    ];

    /**
     * Deteksi kecocokan kategori dari teks bebas input lainnya berbasis pembobotan kata kunci
     */
    public static function detectMatchingCategory(?string $customText): ?string
    {
        if (empty($customText)) {
            return null;
        }

        $text = strtolower(trim($customText));
        $scores = [];

        foreach (self::CATEGORY_KEYWORDS as $catKey => $keywords) {
            $scores[$catKey] = 0;
            foreach ($keywords as $kw) {
                $kw = strtolower(trim($kw));
                if (empty($kw)) continue;

                if (mb_strlen($kw) < 4) {
                    if (preg_match('/(^|[^a-z0-9])' . preg_quote($kw, '/') . '([^a-z0-9]|$)/i', $text)) {
                        $scores[$catKey] += mb_strlen($kw) * 3;
                    }
                } else {
                    if (str_contains($text, $kw)) {
                        $scores[$catKey] += mb_strlen($kw);
                    }
                }
            }
        }

        arsort($scores);
        $topScore = reset($scores);
        $topCat = key($scores);

        return $topScore > 0 ? $topCat : null;
    }

    public function getCategoryLabelAttribute(): string
    {
        $key = strtolower(trim((string) $this->category));
        if ($key === 'lainnya' && ! empty($this->custom_category)) {
            return $this->custom_category;
        }

        return self::CATEGORIES[$key]['label'] ?? ($this->custom_category ?: ($this->category ?: 'Inovasi Lainnya'));
    }

    public function getCategoryBadgeClassAttribute(): string
    {
        $key = strtolower(trim((string) $this->category));
        return self::CATEGORIES[$key]['badge'] ?? 'bg-emerald-100 text-emerald-800 border-emerald-300';
    }

    public function getCategoryBgColorAttribute(): string
    {
        $key = strtolower(trim((string) $this->category));
        return self::CATEGORIES[$key]['bg'] ?? '#d1fae5';
    }

    public function getCategoryTextColorAttribute(): string
    {
        $key = strtolower(trim((string) $this->category));
        return self::CATEGORIES[$key]['text'] ?? '#065f46';
    }

    public function getCategoryBorderColorAttribute(): string
    {
        $key = strtolower(trim((string) $this->category));
        return self::CATEGORIES[$key]['border'] ?? '#6ee7b7';
    }
}
