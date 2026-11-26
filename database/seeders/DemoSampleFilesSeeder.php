<?php

namespace Database\Seeders;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Berkas contoh buatan untuk data demo, agar pemasangan dari repositori tidak punya gambar atau dokumen yang hilang.
 * Seluruhnya dibuat oleh kode (tanpa berkas atau data orang asli) dan tidak menimpa berkas yang sudah ada.
 */
class DemoSampleFilesSeeder extends Seeder
{
    private const PDFS = [
        'documents/applications/5AdFAcelgeprCcoR82Brj5GF2QzWJvqIMPwbrfc2.pdf' => 'Dokumen Persyaratan',
        'documents/applications/sample_proposal.pdf' => 'Surat Pengantar / Proposal',
        'documents/applications/sample_cv.pdf' => 'Daftar Riwayat Hidup',
        'documents/applications/sample_transcript.pdf' => 'Transkrip Nilai',
        'documents/applications/sample_ktp.pdf' => 'Kartu Identitas',
        'documents/applications/sample_proposal_kelompok.pdf' => 'Proposal Kelompok',
        'final_reports/sample_laporan_akhir.pdf' => 'Laporan Akhir',
        'projects/manual_book_kearsipan.pdf' => 'Buku Panduan Proyek',
        'projects/laporan_luaran_inovasi.pdf' => 'Laporan Luaran Inovasi',
    ];

    private const IMAGES = [
        'anak_membaca_rak_melingkar.jpg', 'ruang_baca_modern_interior.jpg', 'meja_layanan_sirkulasi.jpg',
        'koleksi_buku_rak_perpustakaan.jpg', 'mahasiswi_membaca_koleksi.jpg', 'sample_preservasi.jpg',
        'dokumentasi_balai_desa_1.jpg', 'dokumentasi_sosialisasi_2.jpg', 'community_doc_desa_1.png',
        'community_doc_desa_2.png', 'project_sample_cover.png', 'screenshot_1_jalur.png', 'screenshot_2_kampus.png',
        'demo_dashboard.png', 'demo_arsip_list.png', 'demo_verifikasi.png',
    ];

    public function run(): void
    {
        $disk = Storage::disk('public');

        foreach (self::PDFS as $path => $title) {
            if (! $disk->exists($path)) {
                $disk->put($path, self::pdf($title));
            }
        }

        foreach (self::IMAGES as $index => $name) {
            if (! $disk->exists('projects/'.$name)) {
                $disk->put('projects/'.$name, $this->image($name, $index));
            }
        }
    }

    public static function pdf(string $title): string
    {
        return Pdf::loadHTML('<h1>BERKAS CONTOH</h1><h2>'.e($title).'</h2>'
            .'<p>Dokumen buatan untuk keperluan demo SIP-MAGANG. Tidak memuat data orang asli.</p>')->output();
    }

    /** Gambar pengganti sederhana: warna berbeda per berkas dan keterangan namanya. */
    private function image(string $name, int $index): string
    {
        $palette = [[37, 99, 235], [13, 148, 136], [124, 58, 237], [217, 119, 6], [225, 29, 72], [71, 85, 105]];
        [$r, $g, $b] = $palette[$index % count($palette)];

        $canvas = imagecreatetruecolor(960, 600);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, $r, $g, $b));
        $soft = imagecolorallocate($canvas, (int) ($r * 0.8), (int) ($g * 0.8), (int) ($b * 0.8));
        imagefilledrectangle($canvas, 60, 60, 900, 540, $soft);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagestring($canvas, 5, 400, 270, 'GAMBAR CONTOH', $white);
        $label = ucwords(str_replace('_', ' ', pathinfo($name, PATHINFO_FILENAME)));
        imagestring($canvas, 4, (int) (480 - strlen($label) * 4), 305, $label, $white);

        ob_start();
        str_ends_with($name, '.png') ? imagepng($canvas) : imagejpeg($canvas, null, 82);

        return (string) ob_get_clean();
    }
}
