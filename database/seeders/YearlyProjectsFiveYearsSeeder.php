<?php

namespace Database\Seeders;

use App\Models\InternshipProject;
use App\Models\InternshipProjectMember;
use App\Models\Placement;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class YearlyProjectsFiveYearsSeeder extends Seeder
{
    /**
     * Jalankan penyemaian ragam proyek inovasi mahasiswa yang terdistribusi ke 5 tahun (2022 - 2026).
     * Menggunakan foto nyata perpustakaan dan kearsipan Dispusip Surabaya.
     */
    public function run(): void
    {
        echo "Menyemai data proyek inovasi 5 tahun (2022 - 2026) dengan foto nyata...\n";

        // Pastikan symlink/file media siap di disk
        $this->ensureProjectMediaFiles();

        // Ambil daftar penempatan (placement) yang ada
        $placements = Placement::with(['application.user.studentProfile'])->get();
        if ($placements->isEmpty()) {
            echo "Tidak ada placement ditemukan. Lewati seeder proyek.\n";

            return;
        }

        // Hapus proyek lama agar bersih dan terstruktur rapi
        InternshipProjectMember::query()->delete();
        InternshipProject::query()->delete();

        $projects = [
            // ==================== TAHUN 2022 ====================
            [
                'year' => 2022,
                'month' => 4,
                'day' => 18,
                'title' => 'Buku Antologi Cerita Rakyat & Legenda Surabaya untuk Pembaca Anak Usia Dini',
                'category' => 'buku',
                'custom_category' => null,
                'project_type' => 'group',
                'photo_path' => 'projects/anak_membaca_rak_melingkar.jpg',
                'description' => "Penyusunan dan penerbitan buku antologi cerita bergambar yang mengangkat kearifan lokal Kota Surabaya seperti kisah Sawunggaling, asal-usul Kali Jagir, dan Jembatan Merah.\n\nBuku ini disusun dengan ilustrasi ramah anak dan bahasa narasi komunikatif untuk menumbuhkan minat membaca generasi dini di taman bacaan masyarakat.",
                'community_benefit' => 'Telah dicetak 150 eksemplar fisik dan didistribusikan ke 15 Taman Bacaan Masyarakat (TBM) kelurahan se-Kota Surabaya, digunakan sebagai materi rutin program dongeng mingguan.',
                'demo_url' => 'https://anyflip.com/buku-antologi-cerita-surabaya',
                'repository_url' => 'https://drive.google.com/drive/folders/antologi-literasi-surabaya',
                'video_url' => 'https://youtu.be/jlKU3exv4Oc',
                'screenshots' => [
                    'projects/ruang_baca_modern_interior.jpg',
                    'projects/community_doc_desa_1.png',
                ],
                'community_photos' => [
                    'projects/anak_membaca_rak_melingkar.jpg',
                    'projects/community_doc_desa_1.png',
                ],
                'documents' => [
                    ['name' => 'Naskah_Lengkap_Buku_Antologi_Cerita_Surabaya.pdf', 'path' => 'projects/laporan_luaran_inovasi.pdf', 'size' => '4.2 MB'],
                ],
                'likes_count' => 24,
                'comments_count' => 7,
                'is_public' => true,
                'members' => [
                    ['role' => 'Ketua Tim & Penulis Naskah', 'is_leader' => true],
                    ['role' => 'Ilustrator Karakter & Layout', 'is_leader' => false],
                    ['role' => 'Riset Cerita Lokal & Editor', 'is_leader' => false],
                ],
            ],
            [
                'year' => 2022,
                'month' => 9,
                'day' => 25,
                'title' => 'Preservasi Fisik & Enkapsulasi Naskah Kuno Peninggalan Abad 19',
                'category' => 'preservasi',
                'custom_category' => null,
                'project_type' => 'individual',
                'photo_path' => 'projects/ruang_baca_modern_interior.jpg',
                'description' => "Kegiatan konservasi kuratif terhadap naskah beraksara Jawa dan kertas Eropa abad ke-19 yang mengalami asam tinggi dan kerapuhan biologis.\n\nMetode meliputi netralisasi keasaman (*deacidification*), perbaikan serat kertas dengan tisu Jepang (*Japanese tissue*), serta enkapsulasi poliester bebas asam guna melindungi fisik asli arsip.",
                'community_benefit' => 'Berhasil menyelamatkan 45 naskah manuskrip bersejarah dari ancaman kerusakan permanen, sehingga dapat diteliti dengan aman oleh akademisi dan sejarawan nusantara.',
                'demo_url' => null,
                'repository_url' => 'https://drive.google.com/drive/folders/dokumentasi-preservasi-naskah',
                'video_url' => null,
                'screenshots' => [
                    'projects/meja_layanan_sirkulasi.jpg',
                ],
                'community_photos' => [
                    'projects/ruang_baca_modern_interior.jpg',
                ],
                'documents' => [
                    ['name' => 'Laporan_Teknis_Preservasi_Enkapsulasi.pdf', 'path' => 'projects/manual_book_kearsipan.pdf', 'size' => '2.1 MB'],
                ],
                'likes_count' => 19,
                'comments_count' => 5,
                'is_public' => true,
                'members' => [
                    ['role' => 'Restorator Naskah Fisik & Konservasi', 'is_leader' => true],
                ],
            ],
            [
                'year' => 2022,
                'month' => 6,
                'day' => 14,
                'title' => 'Program Pojok Dongeng Interaktif & Panggung Boneka Literasi Akhir Pekan',
                'category' => 'lainnya',
                'custom_category' => 'Literasi Anak',
                'project_type' => 'group',
                'photo_path' => 'projects/community_doc_desa_2.png',
                'description' => "Penyelenggaraan agenda dongeng berkala bagi siswa TK dan SD dengan media panggung boneka tangan bertema budi pekerti dan sejarah kepahlawanan Kota Surabaya.\n\nProgram dilaksanakan kolaboratif di ruang baca anak serta balai baca kecamatan.",
                'community_benefit' => 'Menghadirkan lebih dari 400 anak usia dini dalam 12 sesi dongeng interaktif sepanjang semester dan meningkatkan peminjaman buku cerita anak.',
                'demo_url' => null,
                'repository_url' => 'https://drive.google.com/drive/folders/pojok-dongeng-dispusip',
                'video_url' => 'https://youtu.be/jlKU3exv4Oc',
                'screenshots' => ['projects/anak_membaca_rak_melingkar.jpg', 'projects/community_doc_desa_2.png'],
                'community_photos' => ['projects/community_doc_desa_2.png'],
                'documents' => [
                    ['name' => 'Modul_Skenario_Dongeng_Anak_Surabaya.pdf', 'path' => 'projects/laporan_luaran_inovasi.pdf', 'size' => '2.2 MB'],
                ],
                'likes_count' => 22,
                'comments_count' => 6,
                'is_public' => true,
                'members' => [
                    ['role' => 'Koordinator & Pendongeng Utama', 'is_leader' => true],
                    ['role' => 'Kreator Properti & Boneka Edukasi', 'is_leader' => false],
                ],
            ],
            [
                'year' => 2022,
                'month' => 7,
                'day' => 21,
                'title' => 'Pemetaan Indeks Minat Baca Pelajar SD/SMP Kecamatan Rungkut & Gubeng',
                'category' => 'tata_kelola',
                'custom_category' => null,
                'project_type' => 'individual',
                'photo_path' => 'projects/meja_layanan_sirkulasi.jpg',
                'description' => 'Survei lapangan dan pengolahan statistik tingkat kunjungan perpustakaan sekolah serta preferensi bahan bacaan pelajar guna merumuskan prioritas pengadaan buku tahunan dinas.',
                'community_benefit' => 'Menyediakan basis data akurat bagi bidang pembinaan perpustakaan dalam mengalokasikan bantuan buku ke 24 sekolah sasaran.',
                'demo_url' => null,
                'repository_url' => 'https://drive.google.com/drive/folders/pemetaan-indeks-literasi-2022',
                'video_url' => null,
                'screenshots' => ['projects/demo_dashboard.png'],
                'community_photos' => ['projects/community_doc_desa_1.png'],
                'documents' => [
                    ['name' => 'Laporan_Survei_Minat_Baca_2022.pdf', 'path' => 'projects/laporan_luaran_inovasi.pdf', 'size' => '3.0 MB'],
                ],
                'likes_count' => 17,
                'comments_count' => 4,
                'is_public' => true,
                'members' => [
                    ['role' => 'Analis Data & Riset Literasi Daerah', 'is_leader' => true],
                ],
            ],
            [
                'year' => 2022,
                'month' => 10,
                'day' => 12,
                'title' => 'Modul Infografis Panduan Penyelamatan Dokumen Pasca-Banjir Tingkat RT/RW',
                'category' => 'media',
                'custom_category' => null,
                'project_type' => 'group',
                'photo_path' => 'projects/community_doc_desa_1.png',
                'description' => 'Perancangan poster infografis dan panduan darurat pengeringan dokumen keluarga yang terendam air agar tulisan dan cap legal dokumen kependudukan tetap terbaca sebelum dialihmediakan.',
                'community_benefit' => 'Didistribusikan dalam format cetak tahan air dan PDF ke posko kelurahan siaga bencana.',
                'demo_url' => 'https://behance.net/gallery/infografis-selamatkan-arsip',
                'repository_url' => 'https://drive.google.com/drive/folders/infografis-arsip-bencana',
                'video_url' => null,
                'screenshots' => ['projects/community_doc_desa_1.png', 'projects/ruang_baca_modern_interior.jpg'],
                'community_photos' => ['projects/community_doc_desa_1.png'],
                'documents' => [
                    ['name' => 'Poster_Penyelamatan_Arsip_Darurat.pdf', 'path' => 'projects/manual_book_kearsipan.pdf', 'size' => '4.1 MB'],
                ],
                'likes_count' => 28,
                'comments_count' => 8,
                'is_public' => true,
                'members' => [
                    ['role' => 'Desainer Komunikasi Visual', 'is_leader' => true],
                    ['role' => 'Penyusun Materi Teknis Kearsipan', 'is_leader' => false],
                ],
            ],
            [
                'year' => 2022,
                'month' => 11,
                'day' => 19,
                'title' => 'Digitalisasi Katalog Kartu Koleksi Lama (Card-to-MARC Converter) Perpustakaan',
                'category' => 'teknologi',
                'custom_category' => null,
                'project_type' => 'group',
                'photo_path' => 'projects/demo_dashboard.png',
                'description' => 'Konversi ribuan kartu katalog fisik peninggalan era 1980-an menjadi rekaman metadata terstruktur berstandar INLISLite/MARC21 sehingga koleksi lawas dapat dicari daring oleh pemustaka.',
                'community_benefit' => 'Membuka kembali akses informasi terhadap lebih dari 2.500 judul referensi sejarah Jawa Timur yang sebelumnya sulit ditemukan.',
                'demo_url' => 'https://dispusip.surabaya.go.id',
                'repository_url' => 'https://github.com/surabaya-dev/card-to-marc-dispusip',
                'video_url' => null,
                'screenshots' => ['projects/demo_dashboard.png', 'projects/meja_layanan_sirkulasi.jpg'],
                'community_photos' => ['projects/ruang_baca_modern_interior.jpg'],
                'documents' => [
                    ['name' => 'Dokumentasi_Skema_Konversi_MARC21.pdf', 'path' => 'projects/laporan_luaran_inovasi.pdf', 'size' => '2.7 MB'],
                ],
                'likes_count' => 33,
                'comments_count' => 9,
                'is_public' => true,
                'members' => [
                    ['role' => 'Lead Developer & Parser Metadata', 'is_leader' => true],
                    ['role' => 'Verifikator Bibliografi Koleksi Lama', 'is_leader' => false],
                ],
            ],

            // ==================== TAHUN 2023 ====================
            [
                'year' => 2023,
                'month' => 3,
                'day' => 14,
                'title' => 'Revitalisasi & Desain Interior Pojok Baca Kreatif Balai Pertemuan Warga',
                'category' => 'media',
                'custom_category' => null,
                'project_type' => 'group',
                'photo_path' => 'projects/community_doc_desa_2.png',
                'description' => "Penataan ulang estetika ruang pojok baca di Balai RW binaan dinas menjadi ruang baca santai (*lounge style*) ramah anak dan pemuda.\n\nDilengkapi dengan zona sofa diskusi, pencahayaan ergonomis, pembuatan mural tematik literasi, dan katalogisasi rak buku berbasis warna.",
                'community_benefit' => 'Meningkatkan frekuensi kunjungan warga ke balai baca hingga 300% per minggu dan menjadi percontohan pojok baca terpadu bagi kelurahan lain di Surabaya.',
                'demo_url' => 'https://behance.net/gallery/pojok-baca-dispusip-surabaya',
                'repository_url' => 'https://drive.google.com/drive/folders/arsitektur-interior-pojok-baca',
                'video_url' => 'https://youtu.be/jlKU3exv4Oc',
                'screenshots' => [
                    'projects/anak_membaca_rak_melingkar.jpg',
                    'projects/community_doc_desa_2.png',
                ],
                'community_photos' => [
                    'projects/ruang_baca_modern_interior.jpg',
                    'projects/community_doc_desa_2.png',
                ],
                'documents' => [
                    ['name' => 'Konsep_Desain_Interior_Pojok_Baca.pdf', 'path' => 'projects/laporan_luaran_inovasi.pdf', 'size' => '5.6 MB'],
                ],
                'likes_count' => 31,
                'comments_count' => 11,
                'is_public' => true,
                'members' => [
                    ['role' => 'Desainer Interior & Ruang', 'is_leader' => true],
                    ['role' => 'Pembuat Mural & Grafis Edukasi', 'is_leader' => false],
                    ['role' => 'Dokumentasi & Penata Display Buku', 'is_leader' => false],
                ],
            ],
            [
                'year' => 2023,
                'month' => 8,
                'day' => 20,
                'title' => 'Standardisasi SOP Layanan Sirkulasi & Resepsionis Ramah Pemustaka Terpadu',
                'category' => 'tata_kelola',
                'custom_category' => null,
                'project_type' => 'group',
                'photo_path' => 'projects/meja_layanan_sirkulasi.jpg',
                'description' => "Penyusunan standar operasional prosedur (SOP) komprehensif untuk petugas meja layanan sirkulasi dan resepsionis perpustakaan daerah.\n\nFokus pada pelayanan prima 3S (Senyum, Salam, Sapa), alur peminjaman cepat kilat mandiri, dan penanganan pemustaka prioritas seperti lansia dan penyandang disabilitas.",
                'community_benefit' => 'Memangkas waktu tunggu layanan sirkulasi dari rata-rata 15 menit menjadi 3 menit per pemustaka, meningkatkan indeks kepuasan masyarakat (IKM) dinas ke angka 94,8%.',
                'demo_url' => null,
                'repository_url' => 'https://drive.google.com/drive/folders/sop-layanan-sirkulasi-dispusip',
                'video_url' => null,
                'screenshots' => [
                    'projects/meja_layanan_sirkulasi.jpg',
                ],
                'community_photos' => [
                    'projects/meja_layanan_sirkulasi.jpg',
                ],
                'documents' => [
                    ['name' => 'Buku_Pedoman_SOP_Layanan_Sirkulasi.pdf', 'path' => 'projects/manual_book_kearsipan.pdf', 'size' => '1.9 MB'],
                ],
                'likes_count' => 18,
                'comments_count' => 4,
                'is_public' => true,
                'members' => [
                    ['role' => 'Analis Kebijakan & Alur Layanan', 'is_leader' => true],
                    ['role' => 'Penyusun SOP Teknis & Notulis', 'is_leader' => false],
                ],
            ],
            [
                'year' => 2023,
                'month' => 4,
                'day' => 16,
                'title' => 'Klasifikasi Ulang Dewey Decimal (DDC) Koleksi Ilmu Terapan & Vokasi',
                'category' => 'tata_kelola',
                'custom_category' => null,
                'project_type' => 'group',
                'photo_path' => 'projects/anak_membaca_rak_melingkar.jpg',
                'description' => 'Penyelarasan nomor panggil (call number) kelas 600 DDC untuk ribuan buku keterampilan kerja, pertanian perkotaan, dan tata boga agar selaras dengan pembaruan standar katalogisasi nasional.',
                'community_benefit' => 'Memudahkan pelajar SMK dan peserta kursus kerja menemukan buku referensi kejuruan secara mandiri di rak terbuka.',
                'demo_url' => null,
                'repository_url' => 'https://drive.google.com/drive/folders/klasifikasi-ddc-vokasi',
                'video_url' => null,
                'screenshots' => ['projects/ruang_baca_modern_interior.jpg'],
                'community_photos' => ['projects/meja_layanan_sirkulasi.jpg'],
                'documents' => [
                    ['name' => 'Daftar_Inventaris_Nomor_Panggil_DDC_600.pdf', 'path' => 'projects/manual_book_kearsipan.pdf', 'size' => '2.4 MB'],
                ],
                'likes_count' => 20,
                'comments_count' => 5,
                'is_public' => true,
                'members' => [
                    ['role' => 'Kataloger Klasifikasi DDC', 'is_leader' => true],
                    ['role' => 'Penata Koleksi & Pelabelan Rak', 'is_leader' => false],
                ],
            ],
            [
                'year' => 2023,
                'month' => 5,
                'day' => 22,
                'title' => 'Katalog Foto Sejarah Arsitektur Kawasan Kota Lama Surabaya (Jembatan Merah)',
                'category' => 'buku',
                'custom_category' => null,
                'project_type' => 'group',
                'photo_path' => 'projects/community_doc_desa_1.png',
                'description' => 'Kurasi arsip foto bangunan cagar budaya kawasan Jembatan Merah dan Kalimas yang dilengkapi narasi tahun pendirian, arsitek historis, dan perubahan fungsi gedung hingga masa kini.',
                'community_benefit' => 'Menjadi bahan rujukan resmi tur edukasi sejarah pelajar serta pegiat pelestarian cagar budaya Kota Surabaya.',
                'demo_url' => 'https://anyflip.com/katalog-kota-lama-surabaya',
                'repository_url' => 'https://drive.google.com/drive/folders/arsip-foto-kota-lama',
                'video_url' => null,
                'screenshots' => ['projects/community_doc_desa_1.png', 'projects/ruang_baca_modern_interior.jpg'],
                'community_photos' => ['projects/community_doc_desa_1.png'],
                'documents' => [
                    ['name' => 'Katalog_Arsitektur_Kota_Lama_Surabaya.pdf', 'path' => 'projects/laporan_luaran_inovasi.pdf', 'size' => '6.1 MB'],
                ],
                'likes_count' => 39,
                'comments_count' => 13,
                'is_public' => true,
                'members' => [
                    ['role' => 'Kurator Arsip Foto & Sejarah', 'is_leader' => true],
                    ['role' => 'Desainer Tata Letak Buku Katalog', 'is_leader' => false],
                ],
            ],
            [
                'year' => 2023,
                'month' => 7,
                'day' => 11,
                'title' => 'Konservasi & Penjilidan Ulang Surat Kabar Langka Periode 1950–1975',
                'category' => 'preservasi',
                'custom_category' => null,
                'project_type' => 'individual',
                'photo_path' => 'projects/ruang_baca_modern_interior.jpg',
                'description' => 'Pembuatan boks pelindung bebas asam (acid-free archival box) dan perbaikan tulang punggung jilidan koleksi koran lokal Surabaya pasca-kemerdekaan yang mengalami kerapuhan lipatan.',
                'community_benefit' => 'Memperpanjang usia simpan koleksi surat kabar bersejarah hingga puluhan tahun dan menjaga keutuhan teks berita lokal.',
                'demo_url' => null,
                'repository_url' => 'https://drive.google.com/drive/folders/konservasi-koran-1950',
                'video_url' => null,
                'screenshots' => ['projects/ruang_baca_modern_interior.jpg'],
                'community_photos' => ['projects/ruang_baca_modern_interior.jpg'],
                'documents' => [
                    ['name' => 'Laporan_Konservasi_Terbitan_Berkala_Kuno.pdf', 'path' => 'projects/manual_book_kearsipan.pdf', 'size' => '2.9 MB'],
                ],
                'likes_count' => 25,
                'comments_count' => 6,
                'is_public' => true,
                'members' => [
                    ['role' => 'Teknisi Preservasi & Jilid Arsip', 'is_leader' => true],
                ],
            ],
            [
                'year' => 2023,
                'month' => 11,
                'day' => 9,
                'title' => 'Aplikasi Antrean Digital & Reservasi Meja Konsultasi Arsip Layanan Publik',
                'category' => 'teknologi',
                'custom_category' => null,
                'project_type' => 'group',
                'photo_path' => 'projects/demo_arsip_list.png',
                'description' => 'Sistem reservasi jadwal konsultasi penelusuran arsip pertanahan dan kependudukan berbasis web ringan yang menampilkan estimasi waktu pelayanan petugas.',
                'community_benefit' => 'Mengurangi penumpukan warga di ruang layanan kearsipan Gedung Siola sekaligus memberikan kepastian jadwal pelayanan.',
                'demo_url' => 'https://dispusip.surabaya.go.id',
                'repository_url' => 'https://github.com/surabaya-dev/antrean-arsip-dispusip',
                'video_url' => 'https://youtu.be/jlKU3exv4Oc',
                'screenshots' => ['projects/demo_arsip_list.png', 'projects/screenshot_1_jalur.png'],
                'community_photos' => ['projects/meja_layanan_sirkulasi.jpg'],
                'documents' => [
                    ['name' => 'Manual_Penggunaan_Antrean_Arsip.pdf', 'path' => 'projects/laporan_luaran_inovasi.pdf', 'size' => '1.7 MB'],
                ],
                'likes_count' => 34,
                'comments_count' => 10,
                'is_public' => true,
                'members' => [
                    ['role' => 'Backend Developer & API Antrean', 'is_leader' => true],
                    ['role' => 'UI/UX Antarmuka Kiosk Layanan', 'is_leader' => false],
                ],
            ],

            // ==================== TAHUN 2024 ====================
            [
                'year' => 2024,
                'month' => 2,
                'day' => 11,
                'title' => 'Katalog Terbuka OPAC & Reservasi Buku Bersejarah Berbasis Web Mobile',
                'category' => 'teknologi',
                'custom_category' => null,
                'project_type' => 'group',
                'photo_path' => 'projects/demo_dashboard.png',
                'description' => "Pengembangan portal pencarian bibliografi terintegrasi katalog umum (*Online Public Access Catalog*) yang dioptimalkan untuk perangkat seluler.\n\nSistem mendukung pelacakan rak presisi dengan denah visual interaktif dan fitur pesan pengingat pengembalian otomatis.",
                'community_benefit' => 'Mempermudah ribuan mahasiswa dan akademisi menemukan koleksi langka tanpa harus mencari manual di lorong rak buku.',
                'demo_url' => 'https://dispusip.surabaya.go.id',
                'repository_url' => 'https://github.com/surabaya-dev/opac-mobile-surabaya',
                'video_url' => 'https://youtu.be/jlKU3exv4Oc',
                'screenshots' => [
                    'projects/screenshot_1_jalur.png',
                    'projects/demo_dashboard.png',
                    'projects/screenshot_2_kampus.png',
                ],
                'community_photos' => [
                    'projects/meja_layanan_sirkulasi.jpg',
                ],
                'documents' => [
                    ['name' => 'Dokumentasi_API_dan_Arsitektur_OPAC.pdf', 'path' => 'projects/laporan_luaran_inovasi.pdf', 'size' => '3.4 MB'],
                ],
                'likes_count' => 45,
                'comments_count' => 14,
                'is_public' => true,
                'members' => [
                    ['role' => 'Koordinator & Backend Developer', 'is_leader' => true],
                    ['role' => 'Frontend & UI/UX Designer', 'is_leader' => false],
                    ['role' => 'Database & REST API Engineer', 'is_leader' => false],
                    ['role' => 'Quality Assurance & Tester', 'is_leader' => false],
                ],
            ],
            [
                'year' => 2024,
                'month' => 10,
                'day' => 5,
                'title' => 'Buku Panduan Edukasi Sadar Arsip Keluarga untuk Warga Surabaya',
                'category' => 'buku',
                'custom_category' => null,
                'project_type' => 'individual',
                'photo_path' => 'projects/ruang_baca_modern_interior.jpg',
                'description' => 'Penyusunan modul buku saku praktis bagi masyarakat mengenai cara menyimpan, merawat, dan memilah dokumen penting keluarga seperti sertifikat tanah, akta kelahiran, dan ijazah agar terhindar dari kerusakan akibat banjir atau kebakaran.',
                'community_benefit' => 'Telah didiseminasikan kepada kader PKK di 10 kelurahan rawan genangan air di Surabaya untuk membangun kesadaran tertib arsip mandiri.',
                'demo_url' => 'https://anyflip.com/buku-sadar-arsip-keluarga',
                'repository_url' => 'https://drive.google.com/drive/folders/modul-sadar-arsip',
                'video_url' => null,
                'screenshots' => [
                    'projects/anak_membaca_rak_melingkar.jpg',
                ],
                'community_photos' => [
                    'projects/ruang_baca_modern_interior.jpg',
                ],
                'documents' => [
                    ['name' => 'Buku_Saku_Sadar_Arsip_Keluarga.pdf', 'path' => 'projects/manual_book_kearsipan.pdf', 'size' => '2.8 MB'],
                ],
                'likes_count' => 27,
                'comments_count' => 6,
                'is_public' => true,
                'members' => [
                    ['role' => 'Penulis & Edukator Kearsipan Publik', 'is_leader' => true],
                ],
            ],
            [
                'year' => 2024,
                'month' => 4,
                'day' => 18,
                'title' => 'Pedoman Penataan Koleksi Terbitan Berkala, Majalah, dan Jurnal Ilmiah',
                'category' => 'tata_kelola',
                'custom_category' => null,
                'project_type' => 'individual',
                'photo_path' => 'projects/meja_layanan_sirkulasi.jpg',
                'description' => 'Penyusunan standar pencatatan edisi berurutan (kardex digital), penjilidan volume tahunan, dan tata letak rak majalah populer serta jurnal riset di perpustakaan daerah.',
                'community_benefit' => 'Memastikan tidak ada nomor terbitan berseri yang tercecer dan mempermudah mahasiswa menemukan edisi jurnal lama.',
                'demo_url' => null,
                'repository_url' => 'https://drive.google.com/drive/folders/pedoman-terbitan-berkala',
                'video_url' => null,
                'screenshots' => ['projects/meja_layanan_sirkulasi.jpg'],
                'community_photos' => ['projects/ruang_baca_modern_interior.jpg'],
                'documents' => [
                    ['name' => 'Panduan_Pengelolaan_Koleksi_Serial.pdf', 'path' => 'projects/manual_book_kearsipan.pdf', 'size' => '2.0 MB'],
                ],
                'likes_count' => 21,
                'comments_count' => 4,
                'is_public' => true,
                'members' => [
                    ['role' => 'Pengelola Koleksi Terbitan Berseri', 'is_leader' => true],
                ],
            ],
            [
                'year' => 2024,
                'month' => 6,
                'day' => 24,
                'title' => "Kampanye Video Pendek 'Ayo Membaca di Taman Kota' untuk Pelajar Surabaya",
                'category' => 'media',
                'custom_category' => null,
                'project_type' => 'group',
                'photo_path' => 'projects/community_doc_desa_2.png',
                'description' => 'Produksi 8 konten reels edukatif pendek yang memperkenalkan fasilitas perpustakaan taman (Taman Bungkul, Taman Flora, Balai Pemuda) dengan konsep bercerita yang dekat dengan generasi muda.',
                'community_benefit' => 'Meningkatkan kunjungan pemustaka usia pelajar hingga 45% pada jam sore dan akhir pekan selama masa kampanye.',
                'demo_url' => 'https://youtube.com/playlist?list=dispusip-ayo-membaca',
                'repository_url' => 'https://drive.google.com/drive/folders/kampanye-literasi-taman',
                'video_url' => 'https://youtu.be/jlKU3exv4Oc',
                'screenshots' => ['projects/community_doc_desa_2.png', 'projects/anak_membaca_rak_melingkar.jpg'],
                'community_photos' => ['projects/community_doc_desa_2.png'],
                'documents' => [
                    ['name' => 'Brief_Kreatif_Konten_Literasi_2024.pdf', 'path' => 'projects/laporan_luaran_inovasi.pdf', 'size' => '3.3 MB'],
                ],
                'likes_count' => 41,
                'comments_count' => 11,
                'is_public' => true,
                'members' => [
                    ['role' => 'Content Creator & Videografer', 'is_leader' => true],
                    ['role' => 'Scriptwriter & Talent Edukasi', 'is_leader' => false],
                ],
            ],
            [
                'year' => 2024,
                'month' => 8,
                'day' => 15,
                'title' => 'Audit Fisik & Pelabelan Barcode Boks Arsip Inaktif Sekretariat Daerah',
                'category' => 'preservasi',
                'custom_category' => null,
                'project_type' => 'group',
                'photo_path' => 'projects/community_doc_desa_1.png',
                'description' => 'Inventarisasi fisik boks arsip inaktif masa simpan 5–10 tahun, pemindaian daftar berkas, serta pencetakan label kode batang unik per boks untuk percepatan pelacakan lokasi rak depo.',
                'community_benefit' => 'Mempercepat penemuan fisik berkas surat keputusan dan kontrak dinas dari 1 jam menjadi di bawah 5 menit.',
                'demo_url' => null,
                'repository_url' => 'https://drive.google.com/drive/folders/audit-boks-arsip-2024',
                'video_url' => null,
                'screenshots' => ['projects/demo_arsip_list.png', 'projects/community_doc_desa_1.png'],
                'community_photos' => ['projects/community_doc_desa_1.png'],
                'documents' => [
                    ['name' => 'Berita_Acara_Penataan_Boks_Arsip_2024.pdf', 'path' => 'projects/manual_book_kearsipan.pdf', 'size' => '3.7 MB'],
                ],
                'likes_count' => 29,
                'comments_count' => 7,
                'is_public' => true,
                'members' => [
                    ['role' => 'Koordinator Inventarisasi Boks', 'is_leader' => true],
                    ['role' => 'Verifikator Daftar Pertelaan Arsip', 'is_leader' => false],
                ],
            ],
            [
                'year' => 2024,
                'month' => 11,
                'day' => 20,
                'title' => 'Sistem Monitoring Peminjaman Mobil Perpustakaan Keliling Berbasis Jadwal Rute',
                'category' => 'teknologi',
                'custom_category' => null,
                'project_type' => 'group',
                'photo_path' => 'projects/screenshot_1_jalur.png',
                'description' => 'Modul penjadwalan rute armada Perpustakaan Keliling (Pusling) ke sekolah pinggiran dan rusunawa Surabaya, mencakup laporan jumlah pengunjung harian dan rotasi peti buku.',
                'community_benefit' => 'Menjamin pemerataan kunjungan armada literasi ke seluruh 31 kecamatan tanpa tumpang tindih jadwal titik layanan.',
                'demo_url' => 'https://dispusip.surabaya.go.id',
                'repository_url' => 'https://github.com/surabaya-dev/monitoring-pusling-dispusip',
                'video_url' => null,
                'screenshots' => ['projects/screenshot_1_jalur.png', 'projects/demo_dashboard.png'],
                'community_photos' => ['projects/community_doc_desa_2.png'],
                'documents' => [
                    ['name' => 'Panduan_Operasional_Jadwal_Pusling.pdf', 'path' => 'projects/laporan_luaran_inovasi.pdf', 'size' => '2.6 MB'],
                ],
                'likes_count' => 37,
                'comments_count' => 10,
                'is_public' => true,
                'members' => [
                    ['role' => 'Fullstack Web Developer', 'is_leader' => true],
                    ['role' => 'Desainer Alur Jadwal & Peta Titik', 'is_leader' => false],
                ],
            ],

            // ==================== TAHUN 2025 ====================
            [
                'year' => 2025,
                'month' => 3,
                'day' => 19,
                'title' => 'Alih Media Digital & Scanning Resolusi Tinggi Peta Tata Kota Surabaya 1920-1945',
                'category' => 'preservasi',
                'custom_category' => null,
                'project_type' => 'group',
                'photo_path' => 'projects/demo_arsip_list.png',
                'description' => "Proyek digitalisasi lembaran peta historis berukuran besar (*oversized cartography*) periode Gemeente Soerabaia hingga pendudukan Jepang.\n\nMenggunakan scanner format lebar khusus tanpa panas (*cold lamp*) dan pembubuhan metadata berbasis standar kearsipan internasional ISAD(G).",
                'community_benefit' => 'Menghasilkan 120 berkas peta geospasial resolusi tinggi yang dapat diakses oleh dinas tata kota, arsitek cagar budaya, dan peneliti sejarah tata kota.',
                'demo_url' => 'https://dispusip.surabaya.go.id',
                'repository_url' => 'https://drive.google.com/drive/folders/arsip-peta-surabaya-highres',
                'video_url' => 'https://youtu.be/jlKU3exv4Oc',
                'screenshots' => [
                    'projects/meja_layanan_sirkulasi.jpg',
                    'projects/demo_arsip_list.png',
                ],
                'community_photos' => [
                    'projects/ruang_baca_modern_interior.jpg',
                ],
                'documents' => [
                    ['name' => 'Katalog_Metadata_Peta_Kuno_Surabaya.pdf', 'path' => 'projects/laporan_luaran_inovasi.pdf', 'size' => '4.8 MB'],
                ],
                'likes_count' => 38,
                'comments_count' => 12,
                'is_public' => true,
                'members' => [
                    ['role' => 'Kurator Naskah & Metadata ISAD(G)', 'is_leader' => true],
                    ['role' => 'Operator Alih Media Format Besar', 'is_leader' => false],
                    ['role' => 'Restorator Fisik Peta Lipat', 'is_leader' => false],
                ],
            ],
            [
                'year' => 2025,
                'month' => 7,
                'day' => 12,
                'title' => "Serial Video Dokumenter 'Jejak Pustaka & Kearsipan Nusantara Kota Pahlawan'",
                'category' => 'media',
                'custom_category' => null,
                'project_type' => 'group',
                'photo_path' => 'projects/community_doc_desa_1.png',
                'description' => "Produksi 5 episode video dokumenter berkualitas sinematik yang mengisahkan perjalanan arsip perjuangan kemerdekaan di Surabaya dan keunikan layanan perpustakaan modern di Gedung Siola.\n\nDilengkapi sulih suara (*voice over*) dwi-bahasa dan teks terjemahan (*subtitle*).",
                'community_benefit' => 'Ditayangkan di layar informasi publik balai kota dan media sosial dinas, berhasil menjaring 15.000 lebih penonton dari kalangan pelajar dan mahasiswa.',
                'demo_url' => 'https://youtube.com/playlist?list=dispusip-jejak-pustaka',
                'repository_url' => 'https://drive.google.com/drive/folders/master-video-jejak-pustaka',
                'video_url' => 'https://youtu.be/jlKU3exv4Oc',
                'screenshots' => [
                    'projects/anak_membaca_rak_melingkar.jpg',
                    'projects/community_doc_desa_1.png',
                ],
                'community_photos' => [
                    'projects/ruang_baca_modern_interior.jpg',
                ],
                'documents' => [
                    ['name' => 'Storyboard_dan_Naskah_Video_Dokumenter.pdf', 'path' => 'projects/manual_book_kearsipan.pdf', 'size' => '3.1 MB'],
                ],
                'likes_count' => 52,
                'comments_count' => 19,
                'is_public' => true,
                'members' => [
                    ['role' => 'Videografer & Sutradara', 'is_leader' => true],
                    ['role' => 'Editor Video & Motion Graphic', 'is_leader' => false],
                    ['role' => 'Voice Over & Riset Materi Sejarah', 'is_leader' => false],
                ],
            ],
            [
                'year' => 2025,
                'month' => 11,
                'day' => 28,
                'title' => 'Buku Pedoman Tata Kelola Arsip Dinamis & Inaktif Kantor Kelurahan',
                'category' => 'tata_kelola',
                'custom_category' => null,
                'project_type' => 'individual',
                'photo_path' => 'projects/meja_layanan_sirkulasi.jpg',
                'description' => 'Penyusunan petunjuk teknis pengelolaan berkas inaktif pada instansi pemerintahan tingkat kelurahan se-Surabaya, mencakup jadwal retensi arsip (JRA), tata cara pemindahan berkas, dan prosedur pemusnahan arsip aman.',
                'community_benefit' => 'Membantu 31 kecamatan dan 154 kelurahan menertibkan gudang arsip dan mempercepat penemuan kembali warkat kependudukan warga.',
                'demo_url' => null,
                'repository_url' => 'https://drive.google.com/drive/folders/pedoman-tata-kelola-arsip-kelurahan',
                'video_url' => null,
                'screenshots' => [
                    'projects/ruang_baca_modern_interior.jpg',
                ],
                'community_photos' => [
                    'projects/meja_layanan_sirkulasi.jpg',
                ],
                'documents' => [
                    ['name' => 'Buku_Pedoman_Teknis_Pengelolaan_Arsip_Inaktif.pdf', 'path' => 'projects/manual_book_kearsipan.pdf', 'size' => '2.5 MB'],
                ],
                'likes_count' => 23,
                'comments_count' => 3,
                'is_public' => true,
                'members' => [
                    ['role' => 'Analis Tata Kelola Kearsipan Daerah', 'is_leader' => true],
                ],
            ],
            [
                'year' => 2025,
                'month' => 1,
                'day' => 22,
                'title' => 'Evaluasi Ergonomi & Tata Cahaya Ruang Baca Anak Gedung Siola & Rungkut',
                'category' => 'lainnya',
                'custom_category' => 'Ergonomi Ruang Baca',
                'project_type' => 'individual',
                'photo_path' => 'projects/anak_membaca_rak_melingkar.jpg',
                'description' => 'Pengukuran intensitas pencahayaan lux, tinggi kursi baca ergonomis anak, dan jalur sirkulasi ramah kursi roda di ruang layanan publik perpustakaan kota.',
                'community_benefit' => 'Menghasilkan rekomendasi penataan ulang meja baca yang lebih nyaman bagi kesehatan mata anak dan aksesibilitas difabel.',
                'demo_url' => null,
                'repository_url' => 'https://drive.google.com/drive/folders/audit-ergonomi-ruang-baca',
                'video_url' => null,
                'screenshots' => ['projects/anak_membaca_rak_melingkar.jpg', 'projects/ruang_baca_modern_interior.jpg'],
                'community_photos' => ['projects/anak_membaca_rak_melingkar.jpg'],
                'documents' => [
                    ['name' => 'Rekomendasi_Ergonomi_Ruang_Baca_2025.pdf', 'path' => 'projects/laporan_luaran_inovasi.pdf', 'size' => '3.1 MB'],
                ],
                'likes_count' => 26,
                'comments_count' => 7,
                'is_public' => true,
                'members' => [
                    ['role' => 'Peneliti Ergonomi & Tata Ruang Publik', 'is_leader' => true],
                ],
            ],
            [
                'year' => 2025,
                'month' => 5,
                'day' => 16,
                'title' => 'Pengembangan Pojok Literasi Kewirausahaan UMKM & Koperasi Kelurahan',
                'category' => 'buku',
                'custom_category' => null,
                'project_type' => 'group',
                'photo_path' => 'projects/community_doc_desa_2.png',
                'description' => 'Kurasi paket koleksi buku panduan usaha mikro, pembukuan sederhana, dan pemasaran digital yang ditempatkan khusus di rak tematik UMKM bersama pelatihan singkat warga.',
                'community_benefit' => 'Membantu lebih dari 120 pelaku usaha rumahan mengakses referensi kemasan produk dan legalitas NIB secara gratis.',
                'demo_url' => 'https://anyflip.com/pojok-literasi-umkm-surabaya',
                'repository_url' => 'https://drive.google.com/drive/folders/pojok-literasi-umkm',
                'video_url' => null,
                'screenshots' => ['projects/community_doc_desa_2.png', 'projects/community_doc_desa_1.png'],
                'community_photos' => ['projects/community_doc_desa_2.png'],
                'documents' => [
                    ['name' => 'Panduan_Pojok_Literasi_UMKM.pdf', 'path' => 'projects/manual_book_kearsipan.pdf', 'size' => '2.9 MB'],
                ],
                'likes_count' => 35,
                'comments_count' => 11,
                'is_public' => true,
                'members' => [
                    ['role' => 'Koordinator Kurasi Koleksi UMKM', 'is_leader' => true],
                    ['role' => 'Fasilitator Pelatihan Warga', 'is_leader' => false],
                ],
            ],
            [
                'year' => 2025,
                'month' => 9,
                'day' => 10,
                'title' => 'Portal Informasi Direktori Kampus & Mitra Pembudayaan Kegemaran Membaca',
                'category' => 'teknologi',
                'custom_category' => null,
                'project_type' => 'group',
                'photo_path' => 'projects/screenshot_2_kampus.png',
                'description' => 'Pembangunan basis data jejaring kemitraan perguruan tinggi, SMK, dan komunitas taman baca yang terhubung untuk kolaborasi kegiatan KKN Tematik Literasi dan pengabdian masyarakat.',
                'community_benefit' => 'Mempercepat koordinasi program Duta Baca dan penempatan relawan literasi kampus ke kelurahan sasaran.',
                'demo_url' => 'https://dispusip.surabaya.go.id',
                'repository_url' => 'https://github.com/surabaya-dev/direktori-mitra-literasi',
                'video_url' => 'https://youtu.be/jlKU3exv4Oc',
                'screenshots' => ['projects/screenshot_2_kampus.png', 'projects/demo_dashboard.png'],
                'community_photos' => ['projects/community_doc_desa_1.png'],
                'documents' => [
                    ['name' => 'Arsitektur_Direktori_Mitra_Literasi.pdf', 'path' => 'projects/laporan_luaran_inovasi.pdf', 'size' => '2.4 MB'],
                ],
                'likes_count' => 43,
                'comments_count' => 14,
                'is_public' => true,
                'members' => [
                    ['role' => 'System Analyst & Web Developer', 'is_leader' => true],
                    ['role' => 'Pengelola Integrasi API Direktori', 'is_leader' => false],
                ],
            ],

            // ==================== TAHUN 2026 ====================
            [
                'year' => 2026,
                'month' => 1,
                'day' => 15,
                'title' => 'Sistem Informasi Manajemen Magang Berbasis Web (SIM-Magang)',
                'category' => 'teknologi',
                'custom_category' => null,
                'project_type' => 'group',
                'photo_path' => 'projects/project_sample_cover.png',
                'description' => 'Pengembangan platform terpadu untuk efisiensi pendaftaran peserta magang, verifikasi penempatan, pencatatan logbook harian, penilaian mentor dinas dan pembimbing, dan etalase terbuka proyek inovasi kedinasan.',
                'community_benefit' => 'Mengeliminasi penggunaan berkas fisik proposal kertas, memfasilitasi ratusan peserta magang dari kampus dan sekolah mitra, dan mempublikasikan karya nyata peserta magang kepada masyarakat.',
                'demo_url' => 'https://dispusip.surabaya.go.id',
                'repository_url' => 'https://github.com/surabaya-dev/sim-magang-dispusip',
                'video_url' => 'https://youtu.be/jlKU3exv4Oc',
                'screenshots' => [
                    'projects/project_sample_cover.png',
                    'projects/screenshot_1_jalur.png',
                    'projects/screenshot_2_kampus.png',
                    'projects/demo_dashboard.png',
                ],
                'community_photos' => [
                    'projects/community_doc_desa_1.png',
                    'projects/community_doc_desa_2.png',
                ],
                'documents' => [
                    ['name' => 'Buku_Panduan_Pengguna_SIM_Magang.pdf', 'path' => 'projects/manual_book_kearsipan.pdf', 'size' => '3.8 MB'],
                    ['name' => 'Laporan_Arsitektur_Sistem.pdf', 'path' => 'projects/laporan_luaran_inovasi.pdf', 'size' => '2.1 MB'],
                ],
                'likes_count' => 64,
                'comments_count' => 21,
                'is_public' => true,
                'members' => [
                    ['role' => 'Koordinator Tim & Fullstack Engineer', 'is_leader' => true],
                    ['role' => 'Frontend & Blade Templating', 'is_leader' => false],
                    ['role' => 'Backend API & Database Architect', 'is_leader' => false],
                    ['role' => 'Quality Assurance & Red-Team Testing', 'is_leader' => false],
                ],
            ],
            [
                'year' => 2026,
                'month' => 3,
                'day' => 22,
                'title' => 'Program Klinik Baca & Pojok Audio Literasi Inklusif Ramah Disabilitas Netra',
                'category' => 'lainnya',
                'custom_category' => 'Klinik Literasi Inklusif',
                'project_type' => 'group',
                'photo_path' => 'projects/anak_membaca_rak_melingkar.jpg',
                'description' => 'Inisiatif penyediaan layanan audio book naratif lokal, buku berhuruf Braille, dan bimbingan membaca intensif bagi anak-anak dan pemustaka penyandang disabilitas sensorik netra di Kota Surabaya.',
                'community_benefit' => 'Memberikan hak akses literasi yang setara bagi 60 pemustaka disabilitas dan telah memproduksi 25 buku suara berbahasa Indonesia.',
                'demo_url' => 'https://anyflip.com/klinik-literasi-inklusif-dispusip',
                'repository_url' => 'https://drive.google.com/drive/folders/klinik-literasi-inklusif',
                'video_url' => null,
                'screenshots' => [
                    'projects/ruang_baca_modern_interior.jpg',
                    'projects/ruang_baca_modern_interior.jpg',
                ],
                'community_photos' => [
                    'projects/anak_membaca_rak_melingkar.jpg',
                ],
                'documents' => [
                    ['name' => 'Panduan_Pendampingan_Literasi_Inklusif.pdf', 'path' => 'projects/laporan_luaran_inovasi.pdf', 'size' => '1.8 MB'],
                ],
                'likes_count' => 47,
                'comments_count' => 15,
                'is_public' => true,
                'members' => [
                    ['role' => 'Inisiator Program & Pendamping Khusus', 'is_leader' => true],
                    ['role' => 'Produksi Rekaman Audio Book', 'is_leader' => false],
                    ['role' => 'Fasilitator Pemustaka & Humas Komunitas', 'is_leader' => false],
                ],
            ],
            [
                'year' => 2026,
                'month' => 5,
                'day' => 8,
                'title' => 'Perancangan Wayfinding Signage & Identitas Visual Depo Kearsipan Terpadu',
                'category' => 'media',
                'custom_category' => null,
                'project_type' => 'individual',
                'photo_path' => 'projects/ruang_baca_modern_interior.jpg',
                'description' => 'Perancangan sistem penunjuk arah fisik (*wayfinding signage*), peta direktori gedung, dan rambu keselamatan terpadu untuk depo penyimpanan arsip dinas kearsipan dan perpustakaan Surabaya.',
                'community_benefit' => 'Mempermudah navigasi pengunjung publik menuju ruang baca umum, ruang konsultasi naskah, dan zona pelayanan kearsipan secara efisien dan estetis.',
                'demo_url' => 'https://behance.net/gallery/wayfinding-signage-dispusip',
                'repository_url' => 'https://drive.google.com/drive/folders/desain-signage-kearsipan',
                'video_url' => null,
                'screenshots' => [
                    'projects/meja_layanan_sirkulasi.jpg',
                ],
                'community_photos' => [
                    'projects/ruang_baca_modern_interior.jpg',
                ],
                'documents' => [
                    ['name' => 'Manual_Standardisasi_Signage_Dinas.pdf', 'path' => 'projects/manual_book_kearsipan.pdf', 'size' => '5.2 MB'],
                ],
                'likes_count' => 36,
                'comments_count' => 8,
                'is_public' => true,
                'members' => [
                    ['role' => 'Desainer Grafis Lingkungan & Signage', 'is_leader' => true],
                ],
            ],
            [
                'year' => 2026,
                'month' => 1,
                'day' => 20,
                'title' => 'Standardisasi Metadata Arsip Elektronik (e-Archive) Terintegrasi Unit OPD',
                'category' => 'tata_kelola',
                'custom_category' => null,
                'project_type' => 'group',
                'photo_path' => 'projects/demo_arsip_list.png',
                'description' => 'Penyusunan skema penamaan berkas, klasifikasi kode arsip dinamis (JRA), dan template impor metadata elektronik untuk mempercepat serah terima arsip antar-OPD Pemkot Surabaya.',
                'community_benefit' => 'Menyeragamkan struktur berkas arsip digital di puluhan perangkat daerah sehingga siap diaudit ANRI dan mudah dicari ulang.',
                'demo_url' => null,
                'repository_url' => 'https://drive.google.com/drive/folders/standardisasi-metadata-2026',
                'video_url' => null,
                'screenshots' => ['projects/demo_arsip_list.png', 'projects/screenshot_1_jalur.png'],
                'community_photos' => ['projects/meja_layanan_sirkulasi.jpg'],
                'documents' => [
                    ['name' => 'Skema_Metadata_Arsip_Elektronik_2026.pdf', 'path' => 'projects/manual_book_kearsipan.pdf', 'size' => '3.2 MB'],
                ],
                'likes_count' => 31,
                'comments_count' => 9,
                'is_public' => true,
                'members' => [
                    ['role' => 'Arsiparis Ahli & Perumus Metadata', 'is_leader' => true],
                    ['role' => 'Validasi Klasifikasi Surat Dinas', 'is_leader' => false],
                ],
            ],
            [
                'year' => 2026,
                'month' => 2,
                'day' => 18,
                'title' => 'Restorasi Manuskrip Peta Pelabuhan Kalimas & Dokumentasi Foto Historis',
                'category' => 'preservasi',
                'custom_category' => null,
                'project_type' => 'group',
                'photo_path' => 'projects/community_doc_desa_1.png',
                'description' => 'Pembersihan kering, perbaikan robekan kertas kalkir menggunakan lem pati gandum (wheat starch paste), dan digitalisasi cadangan peta maritim kawasan Pelabuhan Kalimas awal abad 20.',
                'community_benefit' => 'Menyelamatkan 18 lembar cetak biru bersejarah agar aman disimpan dalam laci peta datar (map cabinet) bebas asam.',
                'demo_url' => null,
                'repository_url' => 'https://drive.google.com/drive/folders/restorasi-peta-kalimas-2026',
                'video_url' => 'https://youtu.be/jlKU3exv4Oc',
                'screenshots' => ['projects/ruang_baca_modern_interior.jpg', 'projects/community_doc_desa_1.png'],
                'community_photos' => ['projects/community_doc_desa_1.png'],
                'documents' => [
                    ['name' => 'Laporan_Konservasi_Peta_Kalimas.pdf', 'path' => 'projects/laporan_luaran_inovasi.pdf', 'size' => '4.5 MB'],
                ],
                'likes_count' => 44,
                'comments_count' => 12,
                'is_public' => true,
                'members' => [
                    ['role' => 'Restorator Utama Peta Historis', 'is_leader' => true],
                    ['role' => 'Operator Scanning Resolusi Tinggi', 'is_leader' => false],
                ],
            ],
            [
                'year' => 2026,
                'month' => 4,
                'day' => 14,
                'title' => 'Dashboard Analitik Sirkulasi & Rekomendasi Koleksi Berbasis Data Pemustaka',
                'category' => 'teknologi',
                'custom_category' => null,
                'project_type' => 'group',
                'photo_path' => 'projects/demo_dashboard.png',
                'description' => 'Visualisasi tren peminjaman buku harian, jam sibuk layanan perpustakaan, serta analisis topik koleksi paling diminati untuk mendukung pengadaan buku berbasis data.',
                'community_benefit' => 'Membantu pustakawan dan pimpinan dinas membaca kebutuhan literasi warga per wilayah secara cepat dan akurat.',
                'demo_url' => 'https://dispusip.surabaya.go.id',
                'repository_url' => 'https://github.com/surabaya-dev/analitik-sirkulasi-dispusip',
                'video_url' => 'https://youtu.be/jlKU3exv4Oc',
                'screenshots' => ['projects/demo_dashboard.png', 'projects/screenshot_2_kampus.png'],
                'community_photos' => ['projects/meja_layanan_sirkulasi.jpg'],
                'documents' => [
                    ['name' => 'Dokumentasi_Dashboard_Analitik_Sirkulasi.pdf', 'path' => 'projects/laporan_luaran_inovasi.pdf', 'size' => '2.9 MB'],
                ],
                'likes_count' => 58,
                'comments_count' => 16,
                'is_public' => true,
                'members' => [
                    ['role' => 'Data Analyst & Fullstack Developer', 'is_leader' => true],
                    ['role' => 'UI/UX Visualisasi Grafik Kinerja', 'is_leader' => false],
                ],
            ],
        ];

        $placementCount = $placements->count();
        $pIndex = 0;

        foreach ($projects as $item) {
            $createdDate = Carbon::create($item['year'], $item['month'], $item['day'], 10, 30, 0);

            // Tentukan placement penanggung jawab proyek
            $primaryPlacement = $placements[$pIndex % $placementCount];
            $pIndex++;

            $proj = InternshipProject::create([
                'placement_id' => $primaryPlacement->id,
                'title' => $item['title'],
                'category' => $item['category'],
                'custom_category' => $item['custom_category'],
                'photo_path' => $item['photo_path'],
                'description' => $item['description'],
                'community_benefit' => $item['community_benefit'],
                'demo_url' => $item['demo_url'],
                'repository_url' => $item['repository_url'],
                'video_url' => $item['video_url'],
                'source_code_path' => 'projects/sim_magang_v1.0_source.zip',
                'source_code_name' => 'Paket_Berkas_Sumber_Inovasi.zip',
                'source_code_size' => '24.6 MB',
                'screenshots' => $item['screenshots'],
                'community_photos' => $item['community_photos'],
                'documents' => $item['documents'],
                'likes_count' => $item['likes_count'],
                'comments_count' => $item['comments_count'],
                'is_public' => $item['is_public'],
                'created_at' => $createdDate,
                'updated_at' => $createdDate,
            ]);

            // Buat relasi anggota tim jika ada
            if (! empty($item['members'])) {
                foreach ($item['members'] as $mIdx => $mConfig) {
                    if ($mIdx === 0) {
                        // Leader adalah pemilik placement proyek
                        $memberPlacement = $primaryPlacement;
                    } else {
                        // Anggota lain dari placement rekan
                        $memberPlacement = $placements[($pIndex + $mIdx) % $placementCount];
                    }

                    InternshipProjectMember::create([
                        'internship_project_id' => $proj->id,
                        'placement_id' => $memberPlacement->id,
                        'role_in_project' => $mConfig['role'],
                        'is_leader' => $mConfig['is_leader'],
                        'created_at' => $createdDate,
                        'updated_at' => $createdDate,
                    ]);
                }
            }

            echo "[+] Proyek ID {$proj->id} ({$item['year']}): {$proj->title} [{$proj->category_label}]\n";
        }

        echo "Berhasil menyemai 30 proyek inovasi ke dalam 5 tahun (2022 - 2026, 6 karya per tahun)!\n";
    }

    /**
     * Memastikan berkas aset media dan lampiran tersedia di disk fisik
     */
    protected function ensureProjectMediaFiles(): void
    {
        $storageProjectsDir = storage_path('app/public/projects/');
        $sourceAssetsDir = public_path('images/projects/');
        $publicProjectsDir = public_path('storage/projects/');

        if (! File::isDirectory($storageProjectsDir)) {
            File::makeDirectory($storageProjectsDir, 0755, true);
        }

        // Salin gambar resmi bawaan dari public/images/projects ke storage/app/public/projects
        // agar seluruh thumbnail kartu & galeri tangkapan layar tidak pernah mengalami 404.
        if (File::isDirectory($sourceAssetsDir)) {
            foreach (File::files($sourceAssetsDir) as $assetFile) {
                $dest = $storageProjectsDir.$assetFile->getFilename();
                if (! File::exists($dest)) {
                    @copy($assetFile->getPathname(), $dest);
                }
            }
        }

        // Hanya tulis ke public/storage/projects bila public/storage memang sudah ada sebagai direktori nyata
        $shouldCopyToPublicDir = File::isDirectory(public_path('storage')) && ! is_link(public_path('storage'));
        if ($shouldCopyToPublicDir && ! File::isDirectory($publicProjectsDir)) {
            File::makeDirectory($publicProjectsDir, 0755, true);
        }

        // Dummy PDF jika belum ada
        foreach (['manual_book_kearsipan.pdf', 'laporan_luaran_inovasi.pdf'] as $pdfName) {
            $dest = $storageProjectsDir.$pdfName;
            if (! File::exists($dest)) {
                File::put($dest, "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj 2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj 3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R/Resources<<>>>>endobj\nxref\n0 4\n0000000000 65535 f\n0000000010 00000 n\n0000000053 00000 n\n0000000102 00000 n\ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n185\n%%EOF");
            }
            if ($shouldCopyToPublicDir && ! File::exists($publicProjectsDir.$pdfName)) {
                @copy($dest, $publicProjectsDir.$pdfName);
            }
        }

        // Dummy ZIP jika belum ada
        $zipDest = $storageProjectsDir.'sim_magang_v1.0_source.zip';
        if (! File::exists($zipDest)) {
            if (class_exists(\ZipArchive::class)) {
                $zip = new \ZipArchive;
                if ($zip->open($zipDest, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                    $zip->addFromString('README.md', "# SIM Magang Dispusip Surabaya\n\nBerkas arsip kode & karya luaran inovasi peserta magang.");
                    $zip->close();
                }
            } else {
                // Fallback byte ZIP kosong resmi (PK standard) jika ekstensi ext-zip PHP tidak aktif
                File::put($zipDest, "PK\x05\x06\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00");
            }
        }
        if ($shouldCopyToPublicDir && ! File::exists($publicProjectsDir.'sim_magang_v1.0_source.zip')) {
            @copy($zipDest, $publicProjectsDir.'sim_magang_v1.0_source.zip');
        }
    }
}
