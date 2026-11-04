<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateTheme extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'year' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Dapatkan tema aktif (berdasarkan tahun magang atau tema aktif global).
     */
    public static function getActive(?int $year = null): self
    {
        if ($year) {
            $theme = self::where('year', $year)->first();
            if ($theme) {
                return $theme;
            }
        }

        $active = self::where('is_active', true)->orderByDesc('year')->first();
        if ($active) {
            return $active;
        }

        $latest = self::latest('id')->first();
        if ($latest) {
            return $latest;
        }

        // Fallback default jika tabel kosong
        return new self([
            'year' => 2026,
            'theme_name' => 'Tema Resmi Dispusip 2026 — Cyan & Orange',
            'primary_color' => '#00A3C4',
            'secondary_color' => '#F36F21',
            'accent_color' => '#D97706',
            'number_format_pattern' => '400.14.5.4/{NUMBER}/436.7.17/{YEAR}',
            'signee_name' => 'Dra. NOVELIJA, SH',
            'signee_position' => 'Sekretaris',
            'signee_rank' => 'PEMBINA TK I',
            'signee_nip' => '197111141992012001',
            'mandate_text' => 'a.n. Kepala Dinas Perpustakaan dan Kearsipan Kota Surabaya',
            'city' => 'Surabaya',
            'is_active' => true,
        ]);
    }

    /**
     * Helper untuk mengubah kecerahan warna hex (shade / tint).
     * $percent < 0 untuk menggelapkan (shade), $percent > 0 untuk menerangkan (tint).
     */
    public static function adjustBrightness(string $hex, int $percent): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (strlen($hex) !== 6) {
            return '#000000';
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        if ($percent < 0) {
            $factor = 1 + ($percent / 100);
            $r = (int) max(0, min(255, round($r * $factor)));
            $g = (int) max(0, min(255, round($g * $factor)));
            $b = (int) max(0, min(255, round($b * $factor)));
        } else {
            $factor = $percent / 100;
            $r = (int) max(0, min(255, round($r + (255 - $r) * $factor)));
            $g = (int) max(0, min(255, round($g + (255 - $g) * $factor)));
            $b = (int) max(0, min(255, round($b + (255 - $b) * $factor)));
        }

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    /**
     * Menghasilkan palet nuansa lengkap untuk warna primer tema.
     */
    public function getPrimaryPalette(): array
    {
        $base = $this->primary_color ?: '#00A3C4';
        return [
            'darkest' => self::adjustBrightness($base, -55),
            'darker'  => self::adjustBrightness($base, -35),
            'dark'    => self::adjustBrightness($base, -18),
            'base'    => $base,
            'medium'  => self::adjustBrightness($base, 25),
            'light'   => self::adjustBrightness($base, 50),
            'lighter' => self::adjustBrightness($base, 72),
            'pale'    => self::adjustBrightness($base, 88),
        ];
    }

    /**
     * Menghasilkan palet nuansa lengkap untuk warna sekunder tema.
     */
    public function getSecondaryPalette(): array
    {
        $base = $this->secondary_color ?: '#F36F21';
        return [
            'darkest' => self::adjustBrightness($base, -55),
            'darker'  => self::adjustBrightness($base, -35),
            'dark'    => self::adjustBrightness($base, -18),
            'base'    => $base,
            'medium'  => self::adjustBrightness($base, 25),
            'light'   => self::adjustBrightness($base, 50),
            'lighter' => self::adjustBrightness($base, 72),
            'pale'    => self::adjustBrightness($base, 88),
        ];
    }

    /**
     * Format nomor sertifikat dinas berdasarkan pola konfigurasi.
     */
    public function formatNumber($sequenceNumber, ?int $targetYear = null): string
    {
        $yr = $targetYear ?: ($this->year ?: date('Y'));
        $pattern = $this->number_format_pattern ?: '400.14.5.4/{NUMBER}/436.7.17/{YEAR}';

        return str_replace(['{NUMBER}', '{YEAR}'], [(string) $sequenceNumber, (string) $yr], $pattern);
    }
}

