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
    ];

    protected $casts = [
        'weight_mentor' => 'integer',
        'weight_lecturer' => 'integer',
        'require_dpl' => 'boolean',
        'is_verified' => 'boolean',
    ];

    /** Label status verifikasi kampus (satu sumber teks untuk badge & pesan). */
    public const LABEL_VERIFIED = 'Terverifikasi';

    public const LABEL_PENDING_VERIFICATION = 'Menunggu Verifikasi';

    public function getVerificationLabelAttribute(): string
    {
        return ($this->is_verified ?? true) ? self::LABEL_VERIFIED : self::LABEL_PENDING_VERIFICATION;
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
