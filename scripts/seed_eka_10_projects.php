<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Placement;
use App\Models\InternshipProject;

$eka = User::where('email', 'eka.aktif@unesa.ac.id')->first();
if (!$eka) {
    echo "User eka.aktif@unesa.ac.id not found!\n";
    exit(1);
}

$placement = Placement::where('user_id', $eka->id)->first();
if (!$placement) {
    $appRecord = \App\Models\Application::where('user_id', $eka->id)->first();
    if ($appRecord) {
        $placement = Placement::where('application_id', $appRecord->id)->first();
    }
}

if (!$placement) {
    echo "Placement for Eka not found!\n";
    exit(1);
}

echo "Seeding 10 diverse projects with categories for Eka Putri Lestari (Placement ID: {$placement->id})...\n";

// Hapus proyek lama untuk placement ini agar bersih dan konsisten 10 proyek
InternshipProject::where('placement_id', $placement->id)->delete();

$projectsData = [
    [
        'title' => 'Sistem Informasi Manajemen Magang Berbasis Web (SIM-Magang)',
        'category' => 'teknologi',
        'description' => 'Pengembangan platform digital terpadu untuk efisiensi pendaftaran, verifikasi, logbook harian, dan pelaporan akhir magang mahasiswa di lingkungan dinas.',
        'photo_path' => 'projects/project_sample_cover.png',
        'demo_url' => 'https://dispusip.surabaya.go.id',
        'repository_url' => 'https://github.com/surabaya-dev/sim-magang-dispusip',
        'source_code_path' => 'projects/sim_magang_v1.0_source.zip',
        'source_code_name' => 'SIM_Magang_Dispusip_v1.0_SourceCode.zip',
        'source_code_size' => '18.4 MB',
        'video_url' => 'https://youtu.be/jlKU3exv4Oc?si=8Kg2u9vv8zj0peED',
        'community_benefit' => 'Sistem ini mempermudah warga dan mahasiswa dalam mengakses layanan permohonan magang di desa/kelurahan secara transparan tanpa antre, serta telah diserahterimakan ke pengelola dinas.',
        'screenshots' => [
            'projects/project_sample_cover.png',
            'projects/screenshot_1_jalur.png',
            'projects/screenshot_2_kampus.png'
        ],
        'community_photos' => [
            'projects/community_doc_desa_1.png',
            'projects/community_doc_desa_2.png'
        ],
        'documents' => [
            ['name' => 'Buku Panduan Teknis Sistem SIM-Magang.pdf', 'path' => 'projects/manual_book_kearsipan.pdf', 'size' => '2.45 MB'],
            ['name' => 'Laporan Luaran Inovasi & Pengabdian.pdf', 'path' => 'projects/laporan_luaran_inovasi.pdf', 'size' => '1.18 MB']
        ]
    ],
    [
        'title' => 'Buku Cerita Bergambar: Petualangan Si Kancil di Perpustakaan Kota',
        'category' => 'buku',
        'description' => 'Penerbitan buku cerita fisik edukatif 32 halaman berwarna dengan ilustrasi tematik untuk menumbuhkan minat baca anak-anak PAUD dan SD di Surabaya.',
        'photo_path' => 'projects/community_doc_desa_1.png',
        'demo_url' => 'https://anyflip.com/buku-kancil-dispusip-preview',
        'repository_url' => 'https://drive.google.com/drive/folders/buku-cerita-anak-dispusip',
        'source_code_path' => 'projects/sim_magang_v1.0_source.zip',
        'source_code_name' => 'Master_Desain_Buku_Cetak_Resolusi_Tinggi.zip',
        'source_code_size' => '42.1 MB',
        'video_url' => 'https://youtu.be/jlKU3exv4Oc',
        'community_benefit' => 'Telah dicetak 100 eksemplar dan didistribusikan ke 15 Taman Bacaan Masyarakat (TBM) kelurahan se-Kota Surabaya untuk mendampingi program literasi anak usia dini.',
        'screenshots' => [
            'projects/community_doc_desa_1.png',
            'projects/screenshot_2_kampus.png'
        ],
        'community_photos' => [
            'projects/community_doc_desa_2.png'
        ],
        'documents' => [
            ['name' => 'Naskah_Lengkap_Buku_Cerita_Anak.pdf', 'path' => 'projects/manual_book_kearsipan.pdf', 'size' => '3.8 MB']
        ]
    ],
    [
        'title' => 'Revitalisasi & Desain Mural Edukasi Pojok Baca Balai RW 04 Tambaksari',
        'category' => 'desain',
        'description' => 'Penataan interior ruang ramah anak dan pembuatan mural tematik seluas 12 meter persegi guna menciptakan ruang literasi yang nyaman dan menarik.',
        'photo_path' => 'projects/community_doc_desa_2.png',
        'demo_url' => 'https://behance.net/gallery/pojok-baca-ramah-anak-surabaya',
        'repository_url' => 'https://drive.google.com/drive/folders/desain-arsitektur-dan-mural',
        'source_code_path' => null,
        'source_code_name' => null,
        'source_code_size' => null,
        'video_url' => null,
        'community_benefit' => 'Meningkatkan frekuensi kunjungan anak-anak dan warga setempat ke pojok baca hingga 250% per minggu serta menjadi percontohan TBM berbasis RW di kelurahan.',
        'screenshots' => [
            'projects/community_doc_desa_2.png',
            'projects/community_doc_desa_1.png'
        ],
        'community_photos' => [
            'projects/community_doc_desa_1.png'
        ],
        'documents' => [
            ['name' => 'Dokumentasi_Blueprint_Desain_Interior.pdf', 'path' => 'projects/laporan_luaran_inovasi.pdf', 'size' => '5.2 MB']
        ]
    ],
    [
        'title' => 'Preservasi Fisik & Enkapsulasi Naskah Kuno Babad Surabaya Abad XIX',
        'category' => 'preservasi',
        'description' => 'Restorasi fisik kertas rapuh, deasidifikasi kimiawi, dan enkapsulasi polyester film pada lembaran manuskrip kuno bersejarah koleksi depo arsip daerah.',
        'photo_path' => 'projects/screenshot_1_jalur.png',
        'demo_url' => null,
        'repository_url' => 'https://drive.google.com/drive/folders/laporan-laboratorium-preservasi',
        'source_code_path' => 'projects/sim_magang_v1.0_source.zip',
        'source_code_name' => 'Berkas_Hasil_Pemindaian_Preservasi_300DPI.zip',
        'source_code_size' => '28.6 MB',
        'video_url' => 'https://youtu.be/jlKU3exv4Oc',
        'community_benefit' => 'Menyelamatkan bukti autentik sejarah peradaban kota dari ancaman kerapuhan fisik dan oksidasi, memperpanjang usia fisik arsip hingga 50 tahun.',
        'screenshots' => [
            'projects/screenshot_1_jalur.png',
            'projects/screenshot_2_kampus.png'
        ],
        'community_photos' => [
            'projects/community_doc_desa_1.png'
        ],
        'documents' => [
            ['name' => 'Laporan_Laboratorium_Konservasi_Arsip.pdf', 'path' => 'projects/manual_book_kearsipan.pdf', 'size' => '1.9 MB']
        ]
    ],
    [
        'title' => 'Seri Video Dokumenter & Podcast: Suara Dari Balik Arsip Sejarah Kota',
        'category' => 'media',
        'description' => 'Produksi 6 episode video naratif dan siniar literasi berdurasi 15 menit yang mengulas kisah pahlawan dan peristiwa penting Kota Pahlawan.',
        'photo_path' => 'projects/screenshot_2_kampus.png',
        'demo_url' => 'https://spotify.com/show/suara-arsip-surabaya',
        'repository_url' => 'https://youtube.com/playlist?list=edukasi-arsip-dispusip',
        'source_code_path' => null,
        'source_code_name' => null,
        'source_code_size' => null,
        'video_url' => 'https://youtu.be/jlKU3exv4Oc',
        'community_benefit' => 'Ditonton lebih dari 4.000 warga di media sosial dinas dan dijadikan materi penunjang pembelajaran sejarah lokal bagi siswa sekolah di Surabaya.',
        'screenshots' => [
            'projects/screenshot_2_kampus.png'
        ],
        'community_photos' => [
            'projects/community_doc_desa_2.png'
        ],
        'documents' => [
            ['name' => 'Naskah_Rundown_Podcast_Sejarah.pdf', 'path' => 'projects/laporan_luaran_inovasi.pdf', 'size' => '850 KB']
        ]
    ],
    [
        'title' => 'Pedoman Standar Operasional Prosedur (SOP) Tata Kelola Arsip Kelurahan',
        'category' => 'tata_kelola',
        'description' => 'Penyusunan naskah panduan praktis dan formulir kartu kendali penataan arsip dinamis aktif dan inaktif bagi aparat sekretariat kelurahan.',
        'photo_path' => 'projects/project_sample_cover.png',
        'demo_url' => null,
        'repository_url' => 'https://drive.google.com/drive/folders/pedoman-sop-arsip-kelurahan',
        'source_code_path' => null,
        'source_code_name' => null,
        'source_code_size' => null,
        'video_url' => null,
        'community_benefit' => 'Membantu staf administrasi kelurahan menata lebih dari 1.200 berkas layanan warga sehingga waktu temu balik arsip berkurang dari 40 menit menjadi 3 menit.',
        'screenshots' => [
            'projects/project_sample_cover.png'
        ],
        'community_photos' => [
            'projects/community_doc_desa_1.png'
        ],
        'documents' => [
            ['name' => 'Naskah_Final_SOP_Arsip_Kelurahan.pdf', 'path' => 'projects/manual_book_kearsipan.pdf', 'size' => '2.1 MB']
        ]
    ],
    [
        'title' => 'Kriya Kertas Daur Ulang & Penjilidan Tradisional Buku Cerita',
        'category' => 'desain',
        'description' => 'Pengolahan limbah kertas kantor dinas menjadi media kertas seni ramah lingkungan bertekstur alami dan kerajinan sampul buku jahit tangan coptic.',
        'photo_path' => 'projects/community_doc_desa_1.png',
        'demo_url' => 'https://instagram.com/kriya.kertas.dispusip',
        'repository_url' => 'https://drive.google.com/drive/folders/workshop-kriya-lingkungan',
        'source_code_path' => 'projects/sim_magang_v1.0_source.zip',
        'source_code_name' => 'Modul_Workshop_Pelatihan_Kriya_Lengkap.zip',
        'source_code_size' => '12.3 MB',
        'video_url' => 'https://youtu.be/jlKU3exv4Oc',
        'community_benefit' => 'Memberdayakan kelompok ibu-ibu PKK dengan keterampilan kerajinan tangan bernilai ekonomi dan mengolah 80 kg limbah kertas kantor.',
        'screenshots' => [
            'projects/community_doc_desa_1.png'
        ],
        'community_photos' => [
            'projects/community_doc_desa_2.png'
        ],
        'documents' => [
            ['name' => 'Modul_Pelatihan_Daur_Ulang_Kertas.pdf', 'path' => 'projects/laporan_luaran_inovasi.pdf', 'size' => '1.5 MB']
        ]
    ],
    [
        'title' => 'Katalog & Kurasi Pameran Foto Transformasi Tata Kota Surabaya 1945-2025',
        'category' => 'media',
        'description' => 'Penelitian arsip, kuratorial 50 foto sejarah tata ruang kota, dan penulisan lembar narasi informatif untuk pameran publik di Gedung Balai Budaya.',
        'photo_path' => 'projects/community_doc_desa_2.png',
        'demo_url' => 'https://dispusip.surabaya.go.id/pameran-foto-sejarah-surabaya',
        'repository_url' => 'https://drive.google.com/drive/folders/kurasi-koleksi-foto-sejarah',
        'source_code_path' => null,
        'source_code_name' => null,
        'source_code_size' => null,
        'video_url' => null,
        'community_benefit' => 'Memberikan literasi sejarah visual kota bagi lebih dari 2.500 pengunjung pameran umum dan pelajar selama pekan Hari Jadi Kota Surabaya.',
        'screenshots' => [
            'projects/community_doc_desa_2.png'
        ],
        'community_photos' => [
            'projects/community_doc_desa_1.png'
        ],
        'documents' => [
            ['name' => 'Katalog_Resmi_Pameran_Foto_Sejarah.pdf', 'path' => 'projects/manual_book_kearsipan.pdf', 'size' => '4.7 MB']
        ]
    ],
    [
        'title' => 'Transkripsi Buku Audio & Huruf Braille Cerita Rakyat Nusantara',
        'category' => 'buku',
        'description' => 'Alih wahana 10 judul cerita rakyat klasik Jawa Timur ke dalam naskah timbul cetak Braille dan rekaman suara narasi dramatis berkualitas studio.',
        'photo_path' => 'projects/screenshot_1_jalur.png',
        'demo_url' => null,
        'repository_url' => 'https://drive.google.com/drive/folders/koleksi-audiobook-dan-braille',
        'source_code_path' => 'projects/sim_magang_v1.0_source.zip',
        'source_code_name' => 'Paket_Audiobook_MP3_Cerita_Rakyat.zip',
        'source_code_size' => '35.8 MB',
        'video_url' => 'https://youtu.be/jlKU3exv4Oc',
        'community_benefit' => 'Membuka kemudahan akses literasi mandiri bagi lebih dari 45 siswa tunanetra di SLB Yayasan Pendidikan Anak Buta (YPAB) Surabaya.',
        'screenshots' => [
            'projects/screenshot_1_jalur.png',
            'projects/screenshot_2_kampus.png'
        ],
        'community_photos' => [
            'projects/community_doc_desa_1.png'
        ],
        'documents' => [
            ['name' => 'Laporan_Alih_Bentuk_Braille.pdf', 'path' => 'projects/laporan_luaran_inovasi.pdf', 'size' => '1.2 MB']
        ]
    ],
    [
        'title' => 'Formulasi Biopestisida Minyak Atsiri Sereh Wangi Pengendali Kutu Buku',
        'category' => 'preservasi',
        'description' => 'Eksperimen formulasi larutan organik bahan nabati sereh wangi dan cengkeh sebagai bahan proteksi depo arsip yang aman bagi kesehatan.',
        'photo_path' => 'projects/screenshot_2_kampus.png',
        'demo_url' => null,
        'repository_url' => 'https://drive.google.com/drive/folders/riset-biopestisida-arsip',
        'source_code_path' => null,
        'source_code_name' => null,
        'source_code_size' => null,
        'video_url' => null,
        'community_benefit' => 'Menurunkan risiko paparan residu kimia pestisida sintetis berbahaya bagi petugas arsip dan menjaga sterilisasi 5 ruang depo penyimpanan naskah.',
        'screenshots' => [
            'projects/screenshot_2_kampus.png'
        ],
        'community_photos' => [
            'projects/community_doc_desa_2.png'
        ],
        'documents' => [
            ['name' => 'Hasil_Uji_Laboratorium_Biopestisida.pdf', 'path' => 'projects/manual_book_kearsipan.pdf', 'size' => '1.8 MB']
        ]
    ]
];

foreach ($projectsData as $idx => $pData) {
    $p = InternshipProject::create(array_merge($pData, [
        'placement_id' => $placement->id
    ]));
    echo "Created [" . ($idx + 1) . "] ID: {$p->id} - {$p->title} (Kategori: {$p->category_label})\n";
}

echo "Successfully seeded 10 categorized projects for Eka Putri Lestari!\n";
