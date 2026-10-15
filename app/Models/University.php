<?php

namespace App\Models;

use App\Services\UniversityResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class University extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'acronym',
        'code',
        'is_verified',
        'status',
        'address',
        'phone',
        'email',
        'pic_name',
        'pic_nip',
        'pic_position',
        'logo',
        'evaluation_scheme',
        'weight_mentor',
        'weight_lecturer',
        'require_dpl',
        'dinas_components',
        'dpl_components',
    ];

    protected $attributes = [
        'evaluation_scheme' => 'mentor_only',
        'weight_mentor' => 100,
        'weight_lecturer' => 0,
        'require_dpl' => false,
    ];

    protected $casts = [
        'weight_mentor' => 'integer',
        'weight_lecturer' => 'integer',
        'require_dpl' => 'boolean',
        'is_verified' => 'boolean',
        'dinas_components' => 'array',
        'dpl_components' => 'array',
    ];

    /** Default komponen penilaian dinas (1-3) */
    public const DEFAULT_DINAS_COMPONENTS = [
        1 => 'Disiplin, Kehadiran, & Ketaatan Tata Tertib Kedinasan',
        2 => 'Kinerja Teknis, Kualitas Output Proyek, & Tanggung Jawab Kerja',
        3 => 'Inisiatif, Komunikasi Lapangan, & Penyusunan Laporan Dinas',
    ];

    /** Default komponen penilaian DPL kampus (1-3) */
    public const DEFAULT_DPL_COMPONENTS = [
        1 => 'Penguasaan Materi, Teori Ilmiah, & Solusi Teknis Magang',
        2 => 'Kualitas, Sistematika Penulisan, & Ketajaman Analisis Laporan Akhir',
        3 => 'Sikap, Komunikasi, & Keaktifan Konsultasi Bimbingan',
    ];

    /**
     * Mengambil array komponen penilaian dinas dengan fallback ke default.
     * Mendukung jumlah komponen dinamis (bisa diedit, ditambah, atau dihapus).
     * @return array<int, string>
     */
    public function getDinasComponents(): array
    {
        $saved = $this->dinas_components;
        if (! is_array($saved) || empty($saved)) {
            return self::DEFAULT_DINAS_COMPONENTS;
        }

        $clean = [];
        $i = 1;
        foreach ($saved as $k => $v) {
            $name = is_array($v) ? ($v['name'] ?? '') : (string) $v;
            $name = trim($name);
            if ($name !== '') {
                $clean[$i++] = $name;
            }
        }

        return ! empty($clean) ? $clean : self::DEFAULT_DINAS_COMPONENTS;
    }

    /**
     * Mengambil array komponen penilaian DPL dengan fallback ke default.
     * Mendukung jumlah komponen dinamis (bisa diedit, ditambah, atau dihapus).
     * @return array<int, string>
     */
    public function getDplComponents(): array
    {
        $saved = $this->dpl_components;
        if (! is_array($saved) || empty($saved)) {
            return self::DEFAULT_DPL_COMPONENTS;
        }

        $clean = [];
        $i = 1;
        foreach ($saved as $k => $v) {
            $name = is_array($v) ? ($v['name'] ?? '') : (string) $v;
            $name = trim($name);
            if ($name !== '') {
                $clean[$i++] = $name;
            }
        }

        return ! empty($clean) ? $clean : self::DEFAULT_DPL_COMPONENTS;
    }

    /**
     * Label komponen dinas per indeks (1, 2, atau 3).
     */
    public function getDinasComponent(int $index): string
    {
        return $this->getDinasComponents()[$index] ?? (self::DEFAULT_DINAS_COMPONENTS[$index] ?? '');
    }

    /**
     * Label komponen DPL kampus per indeks (1, 2, atau 3).
     */
    public function getDplComponent(int $index): string
    {
        return $this->getDplComponents()[$index] ?? (self::DEFAULT_DPL_COMPONENTS[$index] ?? '');
    }

    /** Label status verifikasi kampus (satu sumber teks untuk badge & pesan). */
    public const LABEL_VERIFIED = 'Terverifikasi';

    public const LABEL_PENDING_VERIFICATION = 'Menunggu Verifikasi';

    public function getVerificationLabelAttribute(): string
    {
        return ($this->is_verified ?? true) ? self::LABEL_VERIFIED : self::LABEL_PENDING_VERIFICATION;
    }

    /**
     * Menentukan apakah perguruan tinggi mewajibkan penetapan Dosen Pembimbing (DPL).
     * Jika skema evaluasi 'mentor_only' atau bobot DPL 0%, DPL tidak diperlukan.
     */
    public function requiresAdvisor(): bool
    {
        if ($this->evaluation_scheme === 'mentor_only' || (int) ($this->weight_lecturer ?? 0) === 0) {
            return false;
        }

        return (bool) ($this->require_dpl ?? false);
    }

    protected static function booted(): void
    {
        // Slug otomatis dari nama kampus (dibuat ulang bila nama berubah)
        static::saving(function (University $university) {
            if ($university->isDirty('name') || empty($university->slug)) {
                $university->slug = Str::slug((string) $university->name) ?: null;
            }
        });
    }

    /**
     * Normalisasi nama kampus input bebas: rapikan spasi ganda & spasi tepi.
     */
    public static function normalizeName(?string $name): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', (string) $name));
    }

    /**
     * Cari kampus berdasarkan nama (tidak peka huruf besar/kecil) atau buat entri baru
     * berstatus "Menunggu Verifikasi" (is_verified = false) untuk kampus yang belum terdaftar.
     * Dipakai alur onboarding mahasiswa (opsi "Perguruan Tinggi Lainnya").
     */
    public static function findOrCreateUnverified(string $name): self
    {
        $name = static::normalizeName($name);

        // Nama / akronim / kode yang sama persis (abaikan huruf besar-kecil & tanda baca) → pakai kampus yang ada
        $existing = app(UniversityResolver::class)->findExact($name)
            ?? static::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->orderBy('id')->first();
        if ($existing) {
            return $existing;
        }

        return static::firstOrCreate(['name' => $name], ['is_verified' => false, 'status' => 'active']);
    }

    /**
     * Kata kunci pencarian (nama, inti nama tanpa kata generik, akronim, kode, slug) dipisah '|'.
     * Dipakai combobox agar "unair", "airlangga", atau salah ketik ringan tetap menemukan kampus.
     */
    public function getSearchKeywordsAttribute(): string
    {
        return implode('|', UniversityResolver::keywordsFor($this));
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('is_verified', true);
    }

    public function scopePendingVerification(Builder $query): Builder
    {
        return $query->where('is_verified', false);
    }

    /**
     * URL logo universitas yang benar-benar ada dengan fallback default resmi.
     */
    public function getLogoUrlAttribute(): string
    {
        $logo = ltrim((string) ($this->attributes['logo'] ?? ''), '/');

        if ($logo !== '') {
            if (is_file(public_path($logo))) {
                return asset($logo);
            }
            if (is_file(public_path('storage/'.$logo)) || is_file(storage_path('app/public/'.$logo))) {
                return asset('storage/'.$logo);
            }
        }

        return asset('images/default-university.svg');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function dosens()
    {
        return $this->hasMany(User::class)->whereIn('role', ['dosen', 'academic_advisor']);
    }

    public function students()
    {
        return $this->hasMany(User::class)->where('role', 'mahasiswa');
    }

    public function universityAdmin()
    {
        return $this->hasOne(User::class)->where('role', 'universitas');
    }
}
