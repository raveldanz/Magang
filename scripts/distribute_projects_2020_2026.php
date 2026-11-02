<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Placement;
use App\Models\InternshipProject;
use Carbon\Carbon;

echo "--- Mendistribusikan Proyek Inovasi Magang Tahun 2020 - 2026 ---\n";

$placement = Placement::first();
if (!$placement) {
    echo "Tidak ada placement ditemukan!\n";
    exit(1);
}

// Data proyek tematik Dispusip Surabaya per tahun (2020 s/d 2026)
$yearlyProjects = [
    [
        'year' => 2020,
        'title' => 'Digitalisasi Naskah Kuno dan Foto Bersejarah Era Kolonial Surabaya',
        'category' => 'arsip',
        'description' => 'Alih media 1.200 lembar naskah kuno beraksara Jawa dan foto dokumentasi Kota Surabaya masa pra-kemerdekaan ke format digital resolusi tinggi.',
        'photo_path' => 'projects/project_sample_cover.png',
        'demo_url' => 'https://dispusip.surabaya.go.id',
        'community_benefit' => 'Menyelamatkan bukti fisik sejarah kota dari kerapuhan dan memfasilitasi riset akademik sejarah bagi 15 perguruan tinggi di Jawa Timur.',
        'likes_count' => 12,
        'comments_count' => 4,
        'is_public' => true,
    ],
    [
        'year' => 2021,
        'title' => 'Portal Layanan Pinjam Buku Mandiri Digital Masa Pandemi',
        'category' => 'teknologi',
        'description' => 'Pengembangan modul booking dan peminjaman buku perpustakaan secara mandiri dengan integrasi notifikasi pesan instan.',
        'photo_path' => 'projects/screenshot_1_jalur.png',
        'demo_url' => 'https://dispusip.surabaya.go.id',
        'community_benefit' => 'Memastikan akses literasi warga kota Surabaya tetap berjalan aman dengan sistem antrean tanpa tatap muka selama masa pembatasan sosial.',
        'likes_count' => 19,
        'comments_count' => 6,
        'is_public' => true,
    ],
    [
        'year' => 2022,
        'title' => 'Standardisasi Penataan Naskah Kuno dan Preservasi Kuratif Daun Lontar',
        'category' => 'preservasi',
        'description' => 'Pembersihan mikrobiologi dan enkapsulasi lembar naskah daun lontar peninggalan peradaban pesisir Jawa Timur di depo steril.',
        'photo_path' => 'projects/screenshot_2_kampus.png',
        'demo_url' => null,
        'community_benefit' => 'Memperpanjang usia simpan fisik artefak literatur nusantara hingga lebih dari 100 tahun ke depan untuk generasi penerus bangsa.',
        'likes_count' => 15,
        'comments_count' => 3,
        'is_public' => true,
    ],
    [
        'year' => 2023,
        'title' => 'Buku Cerita Bergambar: Petualangan Si Kancil di Perpustakaan Kota',
        'category' => 'buku',
        'description' => 'Penerbitan buku cerita fisik edukatif berwarna 32 halaman untuk menumbuhkan minat baca anak-anak PAUD dan SD di Surabaya.',
        'photo_path' => 'projects/community_doc_desa_1.png',
        'demo_url' => 'https://anyflip.com/buku-kancil-dispusip-preview',
        'community_benefit' => 'Telah dicetak dan didistribusikan ke 15 Taman Bacaan Masyarakat (TBM) kelurahan se-Kota Surabaya untuk mendampingi program literasi.',
        'likes_count' => 28,
        'comments_count' => 9,
        'is_public' => true,
    ],
    [
        'year' => 2024,
        'title' => 'Revitalisasi & Desain Mural Edukasi Pojok Baca Balai RW 04 Tambaksari',
        'category' => 'desain',
        'description' => 'Penataan interior ruang ramah anak dan pembuatan mural tematik seluas 12 meter persegi guna menciptakan ruang literasi yang nyaman.',
        'photo_path' => 'projects/community_doc_desa_2.png',
        'demo_url' => 'https://behance.net/gallery/pojok-baca-ramah-anak-surabaya',
        'community_benefit' => 'Meningkatkan frekuensi kunjungan anak-anak dan warga setempat ke pojok baca hingga 250% per minggu di balai RW.',
        'likes_count' => 22,
        'comments_count' => 5,
        'is_public' => true,
    ],
    [
        'year' => 2025,
        'title' => 'Sistem Inventaris Preservasi Arsip Berbasis Barcode QR Cepat',
        'category' => 'teknologi',
        'description' => 'Sistem penandaan dan pelacakan kotak arsip berbasis QR Code yang memangkas waktu pencarian berkas dari 40 menit menjadi 3 menit.',
        'photo_path' => 'projects/screenshot_1_jalur.png',
        'demo_url' => 'https://dispusip.surabaya.go.id',
        'community_benefit' => 'Meningkatkan indeks kepuasan masyarakat terhadap kecepatan pencarian arsip perizinan dan pertanahan di Kota Surabaya.',
        'likes_count' => 34,
        'comments_count' => 11,
        'is_public' => true,
    ],
    [
        'year' => 2026,
        'title' => 'Sistem Informasi Manajemen Magang Berbasis Web (SIM-Magang)',
        'category' => 'teknologi',
        'description' => 'Pengembangan platform digital terpadu untuk efisiensi pendaftaran, verifikasi, logbook harian, dan pelaporan akhir magang mahasiswa.',
        'photo_path' => 'projects/project_sample_cover.png',
        'demo_url' => 'https://dispusip.surabaya.go.id',
        'community_benefit' => 'Mempermudah ribuan mahasiswa dan perguruan tinggi dalam mengakses peluang magang dinas secara transparan tanpa berkas fisik.',
        'likes_count' => 47,
        'comments_count' => 14,
        'is_public' => true,
    ],
];

foreach ($yearlyProjects as $data) {
    $year = $data['year'];
    $createdAt = Carbon::createFromDate($year, 6, 15)->setTime(10, 30, 0);

    $proj = InternshipProject::updateOrCreate(
        ['title' => $data['title']],
        [
            'placement_id' => $placement->id,
            'category' => $data['category'],
            'description' => $data['description'],
            'photo_path' => $data['photo_path'],
            'demo_url' => $data['demo_url'],
            'community_benefit' => $data['community_benefit'],
            'likes_count' => $data['likes_count'],
            'comments_count' => $data['comments_count'],
            'is_public' => $data['is_public'],
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]
    );

    // Update timestamp secara langsung jika Eloquent overwrite
    \DB::table('internship_projects')->where('id', $proj->id)->update([
        'created_at' => $createdAt->toDateTimeString(),
        'updated_at' => $createdAt->toDateTimeString(),
    ]);

    echo "Tahun {$year}: Berhasil mengatur proyek '{$data['title']}' (ID: {$proj->id})\n";
}

echo "--- Sukses Mengatur Proyek Tahun 2020 s/d 2026 ---\n";
