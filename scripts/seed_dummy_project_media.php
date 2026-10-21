<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\InternshipProject;

$project = InternshipProject::first();
if (!$project) {
    echo "No project found.\n";
    exit(1);
}

// Update first project with dummy video, screenshots, community photos, documents, repo, and source code ZIP
$project->update([
    'title' => 'Sistem Informasi Manajemen Magang Berbasis Web (SIM-Magang)',
    'description' => "Pengembangan platform digital terpadu untuk efisiensi pendaftaran, verifikasi instansi, logbook harian, dan pelaporan akhir magang mahasiswa di lingkungan dinas.\n\nSistem ini dirancang untuk memangkas proses manual dan mempermudah pemantauan kinerja mahasiswa oleh mentor dinas dan dosen pembimbing.",
    'photo_path' => 'projects/project_sample_cover.png',
    'demo_url' => 'https://dispusip.surabaya.go.id',
    'repository_url' => 'https://github.com/surabaya-dev/sim-magang-dispusip',
    'source_code_path' => 'projects/sim_magang_v1.0_source.zip',
    'source_code_name' => 'SIM_Magang_Dispusip_v1.0_SourceCode.zip',
    'source_code_size' => '18.4 MB',
    'video_url' => 'https://youtu.be/jlKU3exv4Oc?si=8Kg2u9vv8zj0peED',
    'community_benefit' => 'Sistem ini mempermudah warga dan mahasiswa dalam mengakses layanan permohonan magang di desa/kelurahan secara transparan tanpa antre, serta telah disosialisasikan dan diserahterimakan langsung kepada pihak mitra pengelola di lokasi pengabdian.',
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
        [
            'name' => 'Buku Panduan Teknis Sistem SIM-Magang.pdf',
            'path' => 'projects/manual_book_kearsipan.pdf',
            'size' => '2.45 MB'
        ],
        [
            'name' => 'Laporan Luaran Inovasi & Pengabdian.pdf',
            'path' => 'projects/laporan_luaran_inovasi.pdf',
            'size' => '1.18 MB'
        ]
    ]
]);

$targetDir = storage_path('app/public/projects/');
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}
$dummyImages = [
    'demo_dashboard.png',
    'demo_arsip_list.png',
    'demo_verifikasi.png',
    'dokumentasi_balai_desa_1.jpg',
    'dokumentasi_sosialisasi_2.jpg'
];
foreach ($dummyImages as $f) {
    if (!file_exists($targetDir . $f)) {
        if (file_exists(public_path('images/bsre-logo.png'))) {
            copy(public_path('images/bsre-logo.png'), $targetDir . $f);
        } else {
            file_put_contents($targetDir . $f, 'sample image content');
        }
    }
}
if (!file_exists($targetDir . 'manual_book_kearsipan.pdf')) {
    file_put_contents($targetDir . 'manual_book_kearsipan.pdf', "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj 2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj 3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R/Resources<<>>>>endobj\nxref\n0 4\n0000000000 65535 f\n0000000010 00000 n\n0000000053 00000 n\n0000000102 00000 n\ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n185\n%%EOF");
}
if (!file_exists($targetDir . 'laporan_luaran_inovasi.pdf')) {
    file_put_contents($targetDir . 'laporan_luaran_inovasi.pdf', "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj 2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj 3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R/Resources<<>>>>endobj\nxref\n0 4\n0000000000 65535 f\n0000000010 00000 n\n0000000053 00000 n\n0000000102 00000 n\ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n185\n%%EOF");
}
if (!file_exists($targetDir . 'sim_magang_v1.0_source.zip')) {
    $zip = new ZipArchive();
    if ($zip->open($targetDir . 'sim_magang_v1.0_source.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
        $zip->addFromString('README.md', "# SIM Magang Dispusip Surabaya\n\nSource code arsip proyek inovasi mahasiswa.");
        $zip->close();
    }
}

echo "Updated project ID: {$project->id} - {$project->title}\n";
echo "Embed URL: {$project->youtube_embed_url}\n";
echo "Placement ID: {$project->placement_id}\n";
echo "Student Email: " . ($project->placement?->application?->user?->email ?? 'none') . "\n";
echo "Dummy files verified in storage.\n";

