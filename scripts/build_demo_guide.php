<?php

/**
 * Membuat PDF "Panduan Persiapan Demo & Testing SIP-MAGANG" (A4) dengan dompdf.
 * Pemakaian: php scripts/build_demo_guide.php  -> docs/Panduan_Persiapan_Demo_SIP-MAGANG.pdf
 * Tidak butuh internet maupun database.
 */
$base = dirname(__DIR__);
require $base.'/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$img = function (string $rel): string {
    $path = dirname(__DIR__).'/public/'.$rel;

    return is_file($path) ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($path)) : '';
};
$e = fn (string $s) => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
$box = '<span class="cb">&#9744;</span>';

/** Tabel sederhana: $head = judul kolom, $rows = baris (sel boleh berisi HTML). */
$table = function (array $head, array $rows, array $widths = [], string $class = '') {
    $h = '<table class="grid '.$class.'"><thead><tr>';
    foreach ($head as $i => $col) {
        $w = isset($widths[$i]) ? ' style="width:'.$widths[$i].'"' : '';
        $h .= "<th{$w}>{$col}</th>";
    }
    $h .= '</tr></thead><tbody>';
    foreach ($rows as $row) {
        $h .= '<tr>';
        foreach ($row as $cell) {
            $h .= "<td>{$cell}</td>";
        }
        $h .= '</tr>';
    }

    return $h.'</tbody></table>';
};
$code = fn (string $s) => '<span class="code">'.htmlspecialchars($s, ENT_QUOTES, 'UTF-8').'</span>';

$tanggal = '16 November 2026';
$logoSby = $img('images/logos/surabaya.png');
$logoDispusip = $img('images/logos/dispusip.png');

ob_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Panduan Persiapan Demo &amp; Testing SIP-MAGANG</title>
<style>
    @page { margin: 22mm 18mm 20mm 18mm; }
    body { font-family: "DejaVu Sans", sans-serif; font-size: 9.6pt; color: #1e293b; line-height: 1.45; }
    h1 { font-size: 22pt; color: #0f2a5c; margin: 0 0 6px 0; line-height: 1.2; }
    h2 { font-size: 14pt; color: #0f2a5c; border-bottom: 2px solid #2563eb; padding-bottom: 4px; margin: 0 0 10px 0; }
    h3 { font-size: 11pt; color: #1d4ed8; margin: 14px 0 6px 0; }
    p { margin: 0 0 7px 0; }
    .page-break { page-break-before: always; }
    .cover { text-align: center; padding-top: 70px; }
    .cover .logos img { height: 70px; margin: 0 14px; }
    .cover .subtitle { font-size: 12pt; color: #334155; margin-top: 4px; }
    .cover .meta { margin-top: 40px; font-size: 10pt; color: #475569; }
    .cover .band { margin: 34px auto 0 auto; width: 82%; background: #eff6ff; border: 1px solid #bfdbfe; padding: 12px 16px; text-align: left; }
    table.grid { width: 100%; border-collapse: collapse; margin: 4px 0 10px 0; }
    table.grid th { background: #1e3a8a; color: #ffffff; font-size: 8.8pt; text-align: left; padding: 5px 6px; }
    table.grid td { border: 1px solid #cbd5e1; padding: 5px 6px; font-size: 8.8pt; vertical-align: top; }
    table.grid tr:nth-child(even) td { background: #f8fafc; }
    table.compact td, table.compact th { padding: 3px 5px; }
    .code { font-family: "DejaVu Sans Mono", monospace; font-size: 8.4pt; background: #f1f5f9; padding: 0 3px; color: #0f172a; }
    .cmd { font-family: "DejaVu Sans Mono", monospace; font-size: 8.6pt; background: #0f172a; color: #e2e8f0; padding: 7px 9px; margin: 4px 0 8px 0; }
    .note { background: #fefce8; border-left: 4px solid #eab308; padding: 7px 10px; margin: 6px 0 10px 0; }
    .tip { background: #ecfdf5; border-left: 4px solid #10b981; padding: 7px 10px; margin: 6px 0 10px 0; }
    .warn { background: #fef2f2; border-left: 4px solid #ef4444; padding: 7px 10px; margin: 6px 0 10px 0; }
    .cb { font-size: 11pt; }
    .ok { color: #047857; font-weight: bold; }
    .muted { color: #64748b; }
    ol, ul { margin: 2px 0 8px 0; padding-left: 18px; }
    li { margin-bottom: 3px; }
    .step { font-weight: bold; color: #1d4ed8; }
    .toc td { border: none; padding: 2px 4px; font-size: 10pt; }
    /* Judul + isi pendek tidak boleh terpisah halaman */
    .keep { page-break-inside: avoid; }
    h2, h3 { page-break-after: avoid; }
</style>
</head>
<body>

<!-- ===================== SAMPUL ===================== -->
<div class="cover">
    <div class="logos">
        <?php if ($logoSby) { ?><img src="<?= $logoSby ?>" alt="Pemkot Surabaya"><?php } ?>
        <?php if ($logoDispusip) { ?><img src="<?= $logoDispusip ?>" alt="Dispusip"><?php } ?>
    </div>
    <h1 style="margin-top:28px;">Panduan Persiapan Demo &amp; Testing</h1>
    <div class="subtitle">SIP-MAGANG &mdash; Sistem Informasi Magang &amp; PKL<br>Dinas Perpustakaan dan Kearsipan Kota Surabaya</div>
    <div class="band">
        <b>Isi panduan:</b> persiapan sebelum hari-H, cara menjalankan server demo (tanpa perlu PostgreSQL),
        daftar akun demo semua peran, alur cerita presentasi, daftar uji per peran, batasan yang perlu
        diketahui, dan cara mengatasi masalah saat demo.
    </div>
    <div class="meta">Versi <?= $e($tanggal) ?> &middot; Cabang <span class="code">AlurPengajuan-Admin-Yasin</span></div>
</div>

<!-- ===================== DAFTAR ISI & RINGKASAN ===================== -->
<div class="page-break"></div>
<h2>Daftar Isi</h2>
<table class="toc">
    <tr><td>1.</td><td>Ringkasan sistem &amp; status revisi tim</td></tr>
    <tr><td>2.</td><td>Persiapan sebelum hari-H (daftar periksa)</td></tr>
    <tr><td>3.</td><td>Menjalankan server demo (SQLite atau PostgreSQL)</td></tr>
    <tr><td>4.</td><td>Akun demo semua peran</td></tr>
    <tr><td>5.</td><td>Alur cerita presentasi (&plusmn; 20 menit)</td></tr>
    <tr><td>6.</td><td>Daftar uji (testing) per peran</td></tr>
    <tr><td>7.</td><td>Hal penting &amp; batasan saat demo</td></tr>
    <tr><td>8.</td><td>Mengatasi masalah</td></tr>
    <tr><td>9.</td><td>Setelah demo: mengembalikan data</td></tr>
</table>

<h2 style="margin-top:16px;">1. Ringkasan Sistem &amp; Status Revisi</h2>
<p>SIP-MAGANG mengelola seluruh siklus magang/PKL di lingkungan Pemerintah Kota Surabaya: pendaftaran peserta,
verifikasi berkas, persetujuan berlapis (Double ACC), logbook harian, laporan akhir, penilaian, sertifikat, dan
verifikasi keaslian dokumen lewat kode QR. Peserta bisa berasal dari <b>perguruan tinggi</b> maupun <b>sekolah (SMA/SMK/MA)</b>.</p>

<?= $table(['Peran', 'Tugas utama di sistem'], [
    ['Super Admin', 'Mengelola seluruh data, master kampus &amp; sekolah, akun, dan menyetujui pembimbing dari sekolah yang belum punya admin.'],
    ['Admin Dinas', 'Memverifikasi berkas pengajuan, menetapkan mentor, menerbitkan sertifikat.'],
    ['Supervisor (Atasan)', 'Memberi ACC kedua atas pengajuan (Double ACC) dan mengesahkan sertifikat.'],
    ['Mentor Lapangan', 'Memeriksa logbook per hari, menyetujui laporan akhir, mengisi nilai lapangan.'],
    ['Pembimbing (dosen/guru)', 'Mendampingi peserta, memeriksa logbook, mengisi nilai pembimbing bila kebijakan institusinya memakai nilai gabungan.'],
    ['Admin Kampus/Sekolah', 'Mengelola pembimbing institusinya, menyetujui pembimbing yang daftar sendiri, memantau peserta.'],
    ['Peserta Magang', 'Mahasiswa atau siswa PKL: daftar, lengkapi profil, ajukan magang, isi logbook, unggah laporan, unduh sertifikat.'],
], ['28%', '72%']) ?>

<div class="keep"><h3>Status 4 poin revisi tim</h3>
<?= $table(['Poin revisi', 'Status', 'Cara melihatnya saat demo'], [
    ['Dosen &amp; guru bisa daftar akun sendiri (berbeda dengan mentor/pegawai dinas)', '<span class="ok">Selesai</span>', 'Halaman Daftar Akun &rarr; tab <b>Pembimbing</b>. Akun langsung bisa login, tetapi baru bisa dipilih peserta setelah disetujui Admin Kampus/Sekolah (mencegah akun pembimbing palsu).'],
    ['Profil peserta magang punya pilihan sekolah', '<span class="ok">Selesai</span>', 'Profil peserta &rarr; &ldquo;Saya peserta magang dari: Sekolah&rdquo;. Label berubah menjadi NIS, Jurusan, Kelas. Tersedia 283 SMA/SMK/MA Kota Surabaya.'],
    ['Tombol pembimbing tersambung ke dashboard pembimbing', '<span class="ok">Selesai</span>', 'Setelah daftar sebagai Pembimbing atau login, langsung masuk dashboard pembimbing.'],
    ['Istilah universal: Peserta Magang &amp; Pembimbing', '<span class="ok">Selesai</span>', 'Semua halaman, notifikasi, CSV, surat, sertifikat, dan halaman verifikasi QR. Peserta sekolah tampil dengan NIS, Kelas, Sekolah, tanpa kalimat MBKM.'],
], ['32%', '12%', '56%']) ?></div>

<p class="muted">Hasil pemeriksaan terakhir: 573 tes otomatis lulus, termasuk tes alur lengkap peserta sekolah dari daftar sampai verifikasi QR; penjelajahan otomatis 15 akun demo tidak menemukan halaman galat maupun istilah lama.</p>

<!-- ===================== 2. PERSIAPAN ===================== -->
<h2 style="margin-top:18px;">2. Persiapan Sebelum Hari-H</h2>
<div class="keep"><h3>H-2 sampai H-1 (di laptop pengembang)</h3>
<?= $table(['', 'Kegiatan', 'Keterangan'], [
    [$box, 'Tarik kode terbaru cabang demo', 'Pastikan semua perubahan sudah di laptop yang akan membuat zip.'],
    [$box, 'Bangun zip demo', $code('powershell -ExecutionPolicy Bypass -File scripts\make_demo_zip.ps1').'<br>Zip otomatis memakai SQLite (tanpa PostgreSQL), membawa PHP portabel, data demo, dan APP_DEBUG=false.'],
    [$box, 'Salin zip ke laptop server demo', 'Lewat flashdisk atau Google Drive. Jangan salin folder .git atau node_modules.'],
    [$box, 'Gladi bersih minimal satu kali', 'Jalankan sesuai Bab 3, coba alur Bab 5 dari awal sampai akhir dengan HP penguji.'],
], ['5%', '33%', '62%']) ?></div>

<div class="keep"><h3>Hari-H (di laptop server demo)</h3>
<?= $table(['', 'Kegiatan', 'Keterangan'], [
    [$box, 'Laptop terisi penuh &amp; charger terpasang', 'Matikan mode hemat daya / sleep selama demo.'],
    [$box, 'Terhubung ke WiFi yang sama dengan HP/laptop penguji', 'Hotspot HP juga bisa. WiFi publik yang memblokir antarperangkat (client isolation) tidak bisa dipakai.'],
    [$box, 'Firewall sudah diizinkan (sekali saja)', 'Klik kanan '.$code('izinkan_firewall.bat').' &rarr; Run as administrator &rarr; Y.'],
    [$box, 'Server dijalankan', 'Klik dua kali '.$code('start_demo.bat').'. Jangan tutup jendela hitam selama demo.'],
    [$box, 'Alamat untuk penguji sudah dibagikan', 'Baris &ldquo;Dari HP/laptop lain: http://192.168.x.x:8000&rdquo;. Tulis di papan atau buat QR alamat.'],
    [$box, 'Tab browser per peran sudah login', 'Gunakan beberapa browser/jendela samaran agar bisa berpindah peran cepat (lihat Bab 4).'],
    [$box, 'Berkas contoh siap diunggah', 'Siapkan 1&ndash;2 PDF kecil (surat pengantar, CV, laporan) dan 1 foto di laptop/HP penguji.'],
    [$box, 'HP dengan kamera untuk memindai QR', 'Untuk menunjukkan verifikasi surat dan sertifikat.'],
], ['5%', '38%', '57%']) ?></div>

<!-- ===================== 3. MENJALANKAN SERVER ===================== -->
<h2 style="margin-top:18px;">3. Menjalankan Server Demo</h2>

<div class="tip"><b>Laptop demo belum punya PostgreSQL? Tidak masalah.</b><br>
Untuk demo dan testing cukup <b>Pilihan A (SQLite)</b>. SQLite adalah database berbentuk satu berkas
(<span class="code">database\database.sqlite</span>) yang ikut di dalam zip, sehingga tidak perlu memasang
PostgreSQL, XAMPP, composer, atau npm. PostgreSQL hanya dipakai bila ingin menguji dengan server database
seperti di produksi (Pilihan B).</div>

<h3>Pilihan A &mdash; SQLite (disarankan untuk demo)</h3>
<ol>
    <li>Ekstrak zip demo, buka folder <span class="code">SIP-MAGANG</span>.</li>
    <li><b>Sekali saja:</b> klik kanan <span class="code">izinkan_firewall.bat</span> &rarr; <i>Run as administrator</i> &rarr; tekan <b>Y</b>.</li>
    <li>Klik dua kali <span class="code">start_demo.bat</span>. Skrip otomatis: mencari PHP (folder <span class="code">php\</span> di zip),
        menyiapkan <span class="code">.env</span> dan database, menjalankan migrasi, mengimpor daftar sekolah, memperbaiki tautan
        gambar, mengaktifkan magang yang tanggalnya sudah tiba, menaikkan batas unggah, dan menyalakan penjadwal.</li>
    <li>Bagikan alamat <b>&ldquo;Dari HP/laptop lain&rdquo;</b> kepada penguji.</li>
</ol>

<div class="keep"><h3>Pilihan B &mdash; PostgreSQL (pengujian kantor)</h3>
<p>Dipakai bila laptop sudah punya PostgreSQL portabel di <span class="code">C:\pgsql</span> (laptop pengembang sudah punya).
Klik dua kali <span class="code">start_demo_kantor.bat</span>: skrip menyalakan PostgreSQL, mengalihkan <span class="code">.env</span> ke
PostgreSQL (salinan lama disimpan di <span class="code">.env.backup</span>), lalu menjalankan server dengan sesi 8 jam.</p>
<div class="note">Laptop yang <b>tidak</b> punya <span class="code">C:\pgsql</span> cukup memakai Pilihan A. Jangan memasang PostgreSQL
mendadak di hari-H hanya untuk demo.</div></div>

<div class="keep"><h3>Memeriksa kesiapan tanpa mengubah apa pun</h3>
<div class="cmd">php scripts\demo_preflight.php --check</div>
<?= $table(['Tulisan yang muncul', 'Artinya'], [
    ['[DB] ... terhubung / Skema database sudah terbaru', 'Database siap.'],
    ['[SEKOLAH] 283 sekolah ... siap dipilih', 'Daftar SMA/SMK/MA sudah ada di pilihan profil peserta dan daftar akun guru.'],
    ['[PERHATIAN] ... migrasi database belum dijalankan', 'Jalankan start_demo.bat (otomatis) atau '.$code('php artisan migrate').'.'],
    ['[PERHATIAN] Daftar sekolah belum diimpor', 'Jalankan start_demo.bat (otomatis) atau '.$code('php artisan universities:sync-master --only-schools').'.'],
    ['[PERHATIAN] APP_DEBUG=true', 'Hanya peringatan; start_demo.bat sudah mematikan tampilan detail galat untuk perangkat lain.'],
    ['[GAGAL] ...', 'Server tidak dijalankan. Baca pesannya; lihat Bab 8.'],
], ['45%', '55%']) ?></div>

<!-- ===================== 4. AKUN DEMO ===================== -->
<h2 style="margin-top:18px;">4. Akun Demo Semua Peran</h2>
<p>Kata sandi semua akun: <span class="code">password</span>, kecuali <span class="code">admin@gmail.com</span> yang memakai
<span class="code">admin123</span> (bisa diganti lewat <span class="code">DEMO_ADMIN_PASSWORD</span> di .env sebelum data demo dibuat).</p>

<div class="keep"><h3>Staf &amp; institusi</h3>
<?= $table(['Peran', 'Email', 'Dipakai untuk'], [
    ['Super Admin', $code('admin@gmail.com').'<br>'.$code('admin@surabaya.go.id'), 'Master pengguna, master kampus &amp; sekolah, Login As, persetujuan pembimbing sekolah tanpa admin'],
    ['Admin Dinas', $code('admin.dispusip@surabaya.go.id'), 'Verifikasi pengajuan, tetapkan mentor, terbitkan sertifikat'],
    ['Supervisor (Atasan)', $code('supervisor@dispusip.surabaya.go.id'), 'ACC kedua pengajuan, pengesahan sertifikat'],
    ['Mentor Lapangan', $code('mentor.dispusip@surabaya.go.id'), 'Daftar Tugas, logbook per hari, laporan akhir, nilai, cetak lembar nilai'],
    ['Pembimbing kampus', $code('dosen.unesa@unesa.ac.id').'<br>'.$code('dosen.pens@pens.ac.id'), 'Pemantauan &amp; logbook; '.$code('dosen.pens').' untuk nilai gabungan 40/60'],
    ['Admin Kampus', $code('admin@unesa.ac.id'), 'Daftar pembimbing kampus, penugasan pembimbing'],
    ['Admin Sekolah', $code('admin.smkn7@demo.sch.id'), 'Menyetujui guru yang daftar sendiri (menu Pembimbing)'],
    ['Guru pembimbing (disetujui)', $code('guru.smkn7@demo.sch.id'), 'Memeriksa logbook &amp; memberi nilai siswa SMK'],
    ['Guru menunggu persetujuan', $code('guru.menunggu@demo.sch.id'), 'Contoh akun daftar sendiri yang belum disetujui'],
], ['24%', '34%', '42%'], 'compact') ?></div>

<h3>Peserta magang per skenario</h3>
<?= $table(['Keadaan', 'Email', 'Yang bisa diperagakan'], [
    ['Baru mendaftar', $code('baru.daftar@unesa.ac.id'), 'Melengkapi profil dari nol'],
    ['Profil lengkap, belum mengajukan', $code('siap.ajukan@unesa.ac.id'), 'Mengajukan magang + unggah berkas'],
    ['Menunggu verifikasi', $code('fajar.revisi@unesa.ac.id'), 'Admin Dinas memeriksa berkas'],
    ['Pengajuan ditolak', $code('gilang.ditolak@unesa.ac.id'), 'Melihat alasan, mengajukan ulang'],
    ['Menunggu ACC Atasan', $code('mahasiswa.waiting@unesa.ac.id'), 'Supervisor memberi ACC kedua'],
    ['Diterima, belum mulai', $code('dani.accepted@unesa.ac.id'), 'Surat balasan ber-QR'],
    ['Aktif magang', $code('mahasiswa.aktif@unesa.ac.id'), 'Mengisi logbook'],
    ['Logbook ada yang dikembalikan', $code('logbook.revisi@unesa.ac.id'), 'Catatan mentor, memperbaiki logbook'],
    ['Laporan menunggu tinjauan', $code('laporan.menunggu@unesa.ac.id'), 'Mentor menyetujui / meminta revisi'],
    ['Tinggal dinilai', $code('siap.dinilai@unesa.ac.id'), 'Mentor mengisi nilai &rarr; status selesai'],
    ['Nilai gabungan, menunggu pembimbing', $code('ganda.aktif@pens.ac.id'), 'Pembimbing mengisi nilai 60%'],
    ['Sertifikat menunggu pengesahan', $code('hana.sertifikat@unesa.ac.id'), 'Supervisor mengesahkan'],
    ['Lulus, sertifikat sah', $code('mahasiswa.lulus@unesa.ac.id'), 'Membuka sertifikat, memindai QR'],
    ['Siswa SMK aktif', $code('siswa.smk.aktif@demo.sch.id'), 'Profil sekolah (NIS, kelas), logbook'],
    ['Siswa SMK lulus', $code('siswa.smk.lulus@demo.sch.id'), 'Sertifikat &amp; QR berlabel sekolah'],
    ['Akun dinonaktifkan', $code('akun.nonaktif@unesa.ac.id'), 'Login ditolak'],
], ['30%', '34%', '36%'], 'compact') ?>
<p class="muted">Daftar lengkap ada di berkas <span class="code">PANDUAN_DEMO.md</span>.</p>

<!-- ===================== 5. ALUR PRESENTASI ===================== -->
<div class="page-break"></div>
<h2>5. Alur Cerita Presentasi (&plusmn; 20 menit)</h2>
<p>Urutan ini mengikuti perjalanan satu peserta, sehingga penonton mudah mengikuti. Buka satu tab per peran sebelum mulai.</p>
<?= $table(['No', 'Akun', 'Yang ditunjukkan', 'Pesan kunci'], [
    ['1', 'Tanpa login', 'Beranda &rarr; Daftar Akun: pilihan <b>Peserta Magang</b> atau <b>Pembimbing</b>.', 'Peserta dan dosen/guru bisa daftar sendiri.'],
    ['2', 'Akun baru (peserta sekolah)', 'Profil &rarr; pilih <b>Sekolah</b>, ketik &ldquo;smkn 7&rdquo;; label berubah menjadi NIS, Jurusan, Kelas.', 'Mendukung mahasiswa dan siswa PKL.'],
    ['3', $code('siap.ajukan'), 'Ajukan magang: pilih bidang, tanggal, unggah surat pengantar &amp; berkas.', 'Pengajuan lengkap dalam satu halaman.'],
    ['4', $code('admin.dispusip'), 'Daftar pengajuan &rarr; periksa berkas &rarr; terima &amp; tetapkan mentor &rarr; status &ldquo;Menunggu ACC Atasan&rdquo;.', 'Verifikasi pertama oleh Admin Dinas.'],
    ['5', $code('supervisor'), 'Menu pengesahan &rarr; ACC pengajuan '.$code('mahasiswa.waiting').'.', 'Double ACC: tidak ada penerimaan tanpa Atasan.'],
    ['6', $code('dani.accepted'), 'Unduh surat balasan &rarr; pindai QR dengan HP.', 'Keaslian surat bisa dicek siapa saja.'],
    ['7', $code('mahasiswa.aktif'), 'Isi logbook harian dengan lampiran.', 'Logbook harian = bukti kehadiran.'],
    ['8', $code('mentor.dispusip'), 'Beranda &rarr; Daftar Tugas &rarr; periksa logbook per hari (Sesuai / Perlu Diperbaiki); setujui laporan '.$code('laporan.menunggu').'; isi nilai '.$code('siap.dinilai').'; cetak lembar nilai.', 'Mentor tidak ada tugas yang terlewat.'],
    ['9', $code('dosen.pens'), 'Isi nilai pembimbing untuk '.$code('ganda.aktif').' (60%).', 'Nilai gabungan mentor 40% + pembimbing 60%.'],
    ['10', $code('admin.smkn7'), 'Menu Pembimbing &rarr; &ldquo;Pembimbing Menunggu Persetujuan&rdquo; &rarr; Setujui '.$code('guru.menunggu').'.', 'Pembimbing daftar sendiri tetap diverifikasi institusinya.'],
    ['11', $code('supervisor'), 'Sahkan sertifikat '.$code('hana.sertifikat').'.', 'Sertifikat terbit setelah pengesahan Atasan.'],
    ['12', $code('siswa.smk.lulus'), 'Buka sertifikat &rarr; pindai QR dengan HP.', 'Sertifikat siswa berlabel Sekolah &amp; NIS.'],
], ['5%', '19%', '48%', '28%'], 'compact') ?>
<div class="tip"><b>Tips:</b> tampilkan juga lonceng notifikasi tiap peran, mode HP (buka alamat di HP), dan Login As dari Super Admin untuk berpindah peran cepat.</div>

<!-- ===================== 6. DAFTAR UJI ===================== -->
<div class="page-break"></div>
<h2>6. Daftar Uji (Testing) per Peran</h2>
<p>Centang setelah berhasil. Tulis temuan di kolom catatan sendiri (nomor uji + akun + langkah + layar).</p>
<?= $table(['', 'No', 'Akun / peran', 'Langkah', 'Hasil yang diharapkan'], [
    [$box, '1', 'Tanpa login', 'Daftar akun sebagai Peserta Magang', 'Masuk halaman profil, pesan &ldquo;Akun berhasil dibuat&rdquo;'],
    [$box, '2', 'Peserta baru', 'Profil: pilih Sekolah, ketik &ldquo;smkn 7&rdquo;, NIS, kelas 11', 'Tersimpan; ringkasan menampilkan NIS, Sekolah, Kelas 11'],
    [$box, '3', 'Peserta baru', 'Profil sekolah dengan kelas 5', 'Ditolak: &ldquo;Kelas harus antara 10 sampai 13&rdquo;'],
    [$box, '4', 'Tanpa login', 'Daftar akun sebagai Pembimbing, sekolah &ldquo;smkn 7&rdquo;', 'Masuk dashboard pembimbing dengan keterangan &ldquo;menunggu persetujuan&rdquo;'],
    [$box, '5', $code('admin.smkn7'), 'Menu Pembimbing &rarr; Setujui', 'Guru berstatus Aktif dan mendapat notifikasi'],
    [$box, '6', 'Peserta', 'Pilih pembimbing yang belum disetujui', 'Ditolak dengan penjelasan; setelah disetujui bisa dipilih'],
    [$box, '7', $code('siap.ajukan'), 'Ajukan magang + unggah berkas PDF', 'Status &ldquo;Menunggu Verifikasi&rdquo;'],
    [$box, '8', $code('admin.dispusip'), 'Terima pengajuan + tetapkan mentor', 'Status &ldquo;Menunggu ACC Atasan&rdquo;'],
    [$box, '9', $code('supervisor'), 'ACC pengajuan', 'Status Diterima / Aktif Magang sesuai tanggal mulai'],
    [$box, '10', $code('dani.accepted'), 'Unduh surat, pindai QR dengan HP', 'Halaman verifikasi surat terbuka di HP'],
    [$box, '11', $code('mahasiswa.aktif'), 'Isi logbook + lampiran foto/PDF', 'Logbook muncul &ldquo;Menunggu Tinjauan&rdquo;'],
    [$box, '12', $code('mentor.dispusip'), 'Logbook: tandai satu hari &ldquo;Perlu Diperbaiki&rdquo; tanpa catatan', 'Ditolak, catatan wajib diisi'],
    [$box, '13', $code('mentor.dispusip'), 'Setujui laporan '.$code('laporan.menunggu'), 'Laporan Disetujui; langkah berikutnya isi nilai'],
    [$box, '14', $code('mentor.dispusip'), 'Isi nilai '.$code('siap.dinilai').' lalu cetak lembar nilai', 'Status Selesai; lembar nilai siap cetak'],
    [$box, '15', $code('dosen.pens'), 'Isi nilai pembimbing '.$code('ganda.aktif'), 'Nilai akhir = 40% mentor + 60% pembimbing'],
    [$box, '16', $code('admin.dispusip'), 'Terbitkan sertifikat peserta lulus', 'Sertifikat menunggu pengesahan Atasan'],
    [$box, '17', $code('supervisor'), 'Sahkan sertifikat '.$code('hana.sertifikat'), 'Sertifikat sah, peserta bisa mengunduh'],
    [$box, '18', $code('siswa.smk.lulus'), 'Buka sertifikat, pindai QR', 'Label Sekolah Asal &amp; NIS; tanpa kata &ldquo;Perguruan Tinggi&rdquo;'],
    [$box, '19', $code('siswa.smk.aktif'), 'Buka profil &amp; surat penerimaan', 'NIS, Kelas, &ldquo;Sekolah Saudara&rdquo;, tanpa MBKM'],
    [$box, '20', $code('admin@gmail.com'), 'Master Pengguna &rarr; filter Pembimbing &amp; status Menunggu Persetujuan', 'Tombol &ldquo;Setujui Pembimbing&rdquo; tersedia'],
    [$box, '21', $code('akun.nonaktif'), 'Login', 'Login ditolak'],
    [$box, '22', 'Semua peran', 'Buka di HP lewat alamat WiFi', 'Tampilan rapi, tidak ada halaman galat'],
], ['5%', '5%', '20%', '35%', '35%'], 'compact') ?>

<!-- ===================== 7. HAL PENTING ===================== -->
<h2 style="margin-top:18px;">7. Hal Penting &amp; Batasan Saat Demo</h2>
<ul>
    <li><b>Jangan tutup jendela hitam</b> <span class="code">start_demo.bat</span> selama demo; menutupnya menghentikan server.</li>
    <li><b>Magang baru berstatus &ldquo;Selesai&rdquo; setelah tanggal akhirnya lewat</b> (dan laporan, logbook, nilai lengkap). Gunakan akun yang sudah disiapkan (<span class="code">siap.dinilai</span>) agar tidak perlu menunggu.</li>
    <li><b>Sertifikat butuh dua langkah:</b> diterbitkan Admin Dinas, lalu disahkan Supervisor. Sebelum disahkan, peserta belum bisa mengunduhnya.</li>
    <li><b>Pembimbing yang daftar sendiri</b> belum bisa dipilih peserta sebelum disetujui Admin Kampus/Sekolah (atau Super Admin untuk sekolah yang belum punya admin).</li>
    <li><b>Akses lewat alamat WiFi (http://192.168...)</b> bukan koneksi aman, sehingga notifikasi push browser dan rekam suara di chat tidak tersedia di HP. Fitur lain berjalan normal.</li>
    <li><b>QR surat dan sertifikat</b> otomatis memakai alamat WiFi laptop, sehingga bisa dibuka dari HP yang terhubung ke WiFi yang sama.</li>
    <li><b>Batas unggah</b> berkas sampai 50 MB per berkas saat memakai start_demo.bat.</li>
    <li><b>Daftar kampus</b> berisi 251 perguruan tinggi; kampus atau sekolah yang belum terdaftar tetap bisa diketik dan akan masuk antrean verifikasi Super Admin.</li>
    <li><b>Daftar sekolah</b> berisi 283 SMA/SMK/MA Kota Surabaya (data resmi Kemendikdasmen).</li>
    <li>Demo bisa berjalan <b>tanpa internet</b>; huruf (font) dari internet dimuat bila tersedia saja.</li>
</ul>

<h2 style="margin-top:14px;">8. Mengatasi Masalah</h2>
<?= $table(['Masalah', 'Penyebab umum', 'Solusi'], [
    ['HP: &ldquo;Situs tidak dapat dijangkau&rdquo;', 'Firewall memblokir PHP, atau beda WiFi', 'Jalankan izinkan_firewall.bat sebagai administrator; pastikan HP di WiFi yang sama; gunakan alamat 192.168.x.x, bukan localhost.'],
    ['Halaman tampil polos tanpa warna', 'Berkas public/hot tertinggal', 'Tutup lalu jalankan ulang start_demo.bat (berkas itu dihapus otomatis).'],
    ['&ldquo;419 Page Expired&rdquo;', 'Sesi kedaluwarsa', 'Muat ulang halaman lalu login lagi.'],
    ['Logo/gambar tidak muncul', 'Tautan public/storage rusak setelah zip dipindah', 'Jalankan ulang start_demo.bat; bila masih, hapus folder public\storage lalu jalankan lagi.'],
    ['Pilihan sekolah kosong', 'Daftar sekolah belum diimpor', 'Jalankan start_demo.bat atau '.$code('php artisan universities:sync-master --only-schools').'.'],
    ['Galat setelah memperbarui kode', 'Migrasi belum dijalankan', 'Jalankan start_demo.bat atau '.$code('php artisan migrate').'.'],
    ['Port 8000 dipakai aplikasi lain', 'Server lain masih berjalan', 'start_demo.bat memilih port lain otomatis; bagikan alamat yang tampil.'],
    ['Lupa kata sandi akun demo', '&mdash;', 'Lihat Bab 4, atau Super Admin &rarr; Master Pengguna &rarr; Reset.'],
    ['Data demo berantakan setelah gladi', 'Data sudah diubah saat latihan', 'Lihat Bab 9.'],
], ['26%', '28%', '46%'], 'compact') ?>

<div class="keep"><h2 style="margin-top:14px;">9. Setelah Demo: Mengembalikan Data</h2>
<p>Mengembalikan semua akun skenario demo (termasuk akun sekolah) ke keadaan awal, misalnya setelah gladi bersih:</p>
<div class="cmd">php artisan db:seed --class=DemoScenarioSeeder</div>
<div class="warn">Perintah di atas hanya untuk database demo. Jangan jalankan seeder di database yang berisi data asli tanpa
membuat cadangan lebih dulu. Cadangan database lokal sebelum revisi tersimpan di <span class="code">storage\app\private\backups\</span>.</div></div>
<p class="muted" style="margin-top:14px;">Dokumen ini dibuat ulang dengan perintah <span class="code">php scripts\build_demo_guide.php</span>.</p>

</body>
</html>
<?php
$html = (string) ob_get_clean();

$options = new Options;
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', false);
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);
$options->set('chroot', $base);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Nomor halaman & judul pendek di kaki halaman (kecuali sampul)
$canvas = $dompdf->getCanvas();
$font = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
$canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) use ($font) {
    if ($pageNumber === 1) {
        return;
    }
    $canvas->text(51, 812, 'Panduan Persiapan Demo & Testing SIP-MAGANG', $font, 7.5, [0.4, 0.45, 0.55]);
    $canvas->text(500, 812, "Halaman {$pageNumber} / {$pageCount}", $font, 7.5, [0.4, 0.45, 0.55]);
});

$out = $base.'/docs/Panduan_Persiapan_Demo_SIP-MAGANG.pdf';
file_put_contents($out, $dompdf->output());
echo 'PDF dibuat: '.$out.' ('.round(filesize($out) / 1024).' KB, '.$canvas->get_page_count()." halaman)\n";
