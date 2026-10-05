<?php

namespace Tests\Feature;

use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * Penjaga standar status: gagal bila ada view yang menulis nama status sendiri.
 *
 * Nama & warna status hanya boleh berasal dari enum (Enum::label()/description()) dan dirender
 * lewat <x-status-badge> / <x-status-legend>. Dulu satu status bisa punya 5–6 nama berbeda
 * tergantung halaman (lihat LRN-038). Test ini memindai SELURUH resources/views, sehingga view
 * baru ikut terjaga tanpa harus didaftarkan.
 *
 * Bila sebuah kata memang bukan status (mis. teks tombol), tambahkan komentar
 * {{-- status-guard:ignore --}} pada baris yang sama.
 */
class StatusViewGuardTest extends TestCase
{
    private const CODES = 'PENDING|VERIFIED|ACCEPTED|ACTIVE|COMPLETED|REJECTED|RESIGNED|APPROVED|REVISION|IN PROGRESS|RESOLVED|CLOSED';

    private const LEGACY_NAMES = 'Aktif|Nonaktif|Non-Aktif|Cuti|Sedang Magang|Magang Aktif|Lulus|Lulus Magang|Diterima|Ditolak'
        .'|Disetujui|Menunggu Review|Menunggu Verifikasi|Lolos Berkas|Terverifikasi|Selesai Magang|Mengundurkan Diri'
        .'|Revisi|Pending|Approved|Rejected';

    private const STATUS_VALUES = 'pending|verified|accepted|active|completed|rejected|resigned|approved|revision|in_progress|resolved|closed|inactive';

    public function test_views_do_not_hardcode_status_names(): void
    {
        $rules = [
            'nama status ditulis langsung sebagai isi elemen' => '/>\s*(?:'.self::CODES.'|'.self::LEGACY_NAMES.')\s*</',
            'status dijadikan huruf kapital sendiri' => '/strtoupper\(\s*\$[\w>-]*(?:status|lecturer_status)\b/',
            'peta nilai status ke label buatan sendiri' => "/'(?:".self::STATUS_VALUES.")'\s*=>\s*'[A-Z][^']*'/",
        ];

        $violations = [];
        $finder = Finder::create()->files()->in(resource_path('views'))->name('*.blade.php')->notPath('vendor');

        foreach ($finder as $file) {
            foreach (preg_split('/\R/', $file->getContents()) as $index => $line) {
                if (str_contains($line, 'status-guard:ignore')) {
                    continue;
                }
                foreach ($rules as $rule => $pattern) {
                    if (preg_match($pattern, $line)) {
                        $violations[] = sprintf('%s:%d [%s] %s', $file->getRelativePathname(), $index + 1, $rule, trim(mb_substr($line, 0, 120)));
                    }
                }
            }
        }

        $this->assertSame([], $violations, "Nama status harus dari enum lewat <x-status-badge> / Enum::label():\n".implode("\n", $violations));
    }

    public function test_guard_catches_known_bad_patterns(): void
    {
        // Memastikan pola penjaga benar-benar menangkap bentuk kesalahan lama
        $samples = [
            '<span>ACTIVE</span>',
            '<span class="x">Sedang Magang</span>',
            '{{ strtoupper($log->status) }}',
            "'active' => 'AKTIF',",
            '<option value="inactive">Non-Aktif</option>',
        ];
        $patterns = [
            '/>\s*(?:'.self::CODES.'|'.self::LEGACY_NAMES.')\s*</',
            '/strtoupper\(\s*\$[\w>-]*(?:status|lecturer_status)\b/',
            "/'(?:".self::STATUS_VALUES.")'\s*=>\s*'[A-Z][^']*'/",
        ];

        foreach ($samples as $sample) {
            $caught = array_filter($patterns, fn ($p) => preg_match($p, $sample));
            $this->assertNotEmpty($caught, "Penjaga tidak menangkap: {$sample}");
        }
        $this->assertSame(0, preg_match($patterns[0], '<span>{{ $case->label() }}</span>'));
    }
}
