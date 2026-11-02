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
     * Format nomor sertifikat dinas berdasarkan pola konfigurasi.
     */
    public function formatNumber($sequenceNumber, ?int $targetYear = null): string
    {
        $yr = $targetYear ?: ($this->year ?: date('Y'));
        $pattern = $this->number_format_pattern ?: '400.14.5.4/{NUMBER}/436.7.17/{YEAR}';

        return str_replace(['{NUMBER}', '{YEAR}'], [(string) $sequenceNumber, (string) $yr], $pattern);
    }
}

