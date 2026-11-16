<?php

/**
 * Membuat PDF "Panduan Pemasangan & Uji Coba Demo SIP-MAGANG" untuk Tim IT (A4) dengan dompdf.
 * Pemakaian: php scripts/build_it_guide.php  -> docs/Panduan_IT_Demo_SIP-MAGANG.pdf
 * make_demo_zip.ps1 menyalinnya ke folder teratas zip sebagai PANDUAN_IT_SIP-MAGANG.pdf.
 * Tidak butuh internet maupun database.
 */
$base = dirname(__DIR__);
require $base.'/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$img = function (string $rel) use ($base): string {
    $path = $base.'/public/'.$rel;

    return is_file($path) ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($path)) : '';
};
$table = function (array $head, array $rows, array $widths = []): string {
    $h = '<table class="grid"><thead><tr>';
    foreach ($head as $i => $col) {
        $w = isset($widths[$i]) ? ' style="width:'.$widths[$i].'"' : '';
        $h .= "<th{$w}>{$col}</th>";
    }
    $h .= '</tr></thead><tbody>';
    foreach ($rows as $row) {
        $h .= '<tr><td>'.implode('</td><td>', $row).'</td></tr>';
    }

    return $h.'</tbody></table>';
};
$code = fn (string $s) => '<span class="code">'.htmlspecialchars($s, ENT_QUOTES, 'UTF-8').'</span>';
$box = '<span class="cb">&#9744;</span>';

$logoSby = $img('images/logoPemkotSBY.png') ?: $img('images/logos/surabaya.png');
$logoDispusip = $img('images/logos/dispusip.png');
$tanggal = '17 November 2026';

ob_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Panduan Pemasangan &amp; Uji Coba Demo SIP-MAGANG</title>
<style>
    @page { margin: 20mm 17mm 19mm 17mm; }
    body { font-family: "DejaVu Sans", sans-serif; font-size: 9.4pt; color: #1e293b; line-height: 1.42; }
    h1 { font-size: 23pt; color: #0f2a5c; margin: 0 0 6px 0; line-height: 1.2; }
    h2 { font-size: 14.5pt; color: #0f2a5c; border-bottom: 2px solid #2563eb; padding-bottom: 4px; margin: 0 0 10px 0; }
    h3 { font-size: 11.2pt; color: #1d4ed8; margin: 14px 0 6px 0; }
    p { margin: 0 0 7px 0; }
    .page-break { page-break-before: always; }
    .cover { text-align: center; padding-top: 60px; }
    .cover .logos img { height: 72px; margin: 0 14px; }
    .cover .subtitle { font-size: 13pt; color: #334155; margin-top: 6px; }
    .cover .for { font-size: 12pt; margin-top: 26px; color: #0f172a; }
    .cover .band { margin: 30px auto 0 auto; width: 84%; background: #eff6ff; border: 1px solid #bfdbfe; padding: 12px 18px; text-align: left; }
    .cover .meta { margin-top: 34px; font-size: 9.5pt; color: #475569; }
    table.grid { width: 100%; border-collapse: collapse; margin: 4px 0 10px 0; }
    table.grid th { background: #1e3a8a; color: #fff; font-size: 9pt; text-align: left; padding: 5px 6px; }
    table.grid td { border: 1px solid #cbd5e1; padding: 5px 6px; font-size: 9pt; vertical-align: top; }
    table.grid tr:nth-child(even) td { background: #f8fafc; }
    .code { font-family: "DejaVu Sans Mono", monospace; font-size: 8.6pt; background: #f1f5f9; padding: 0 3px; color: #0f172a; }
    .note { background: #fefce8; border-left: 4px solid #eab308; padding: 7px 10px; margin: 6px 0 10px 0; }
    .tip { background: #ecfdf5; border-left: 4px solid #10b981; padding: 7px 10px; margin: 6px 0 10px 0; }
    .warn { background: #fef2f2; border-left: 4px solid #ef4444; padding: 7px 10px; margin: 6px 0 10px 0; }
    .cb { font-size: 11.5pt; }
    .step { width: 100%; border-collapse: collapse; margin: 0 0 8px 0; }
    .step td { vertical-align: top; padding: 6px 6px; border-bottom: 1px solid #e2e8f0; }
    .step .no { width: 26px; text-align: center; }
    .step .no span { display: inline-block; width: 22px; height: 22px; line-height: 22px; border-radius: 11px; background: #1d4ed8; color: #fff; font-weight: bold; font-size: 10pt; }
    .step .ok { color: #047857; font-size: 9pt; }
    .diagram { width: 100%; border-collapse: collapse; margin: 6px 0 12px 0; text-align: center; }
    .diagram td { vertical-align: middle; padding: 4px; }
    .node { border: 2px solid #1d4ed8; border-radius: 8px; padding: 8px 6px; background: #eff6ff; font-size: 9pt; }
    .node b { color: #0f2a5c; font-size: 10pt; }
    .arrow { color: #1d4ed8; font-size: 16pt; font-weight: bold; }
    .muted { color: #64748b; }
    ol, ul { margin: 2px 0 8px 0; padding-left: 18px; }
    li { margin-bottom: 3px; }
    .keep { page-break-inside: avoid; }
    h2, h3 { page-break-after: avoid; }
</style>
</head>
<body>

<!-- ===================== SAMPUL ===================== -->
<div class="cover">
    <div class="logos">
        <?php if ($logoSby) { ?><img src="<?= $logoSby ?>" alt=""><?php } ?>
        <?php if ($logoDispusip) { ?><img src="<?= $logoDispusip ?>" alt=""><?php } ?>
    </div>
    <h1 style="margin-top:28px">Panduan Pemasangan &amp; Uji Coba<br>Demo SIP-MAGANG</h1>
    <div class="subtitle">Sistem Informasi Pengelolaan Magang &mdash; Dinas Perpustakaan dan Kearsipan Kota Surabaya</div>
    <div class="for">Untuk: <b>Bapak Rheza</b> (Bagian IT)</div>
    <div class="band">
        <b>Jadwal</b>
        <table style="width:100%; margin-top:4px; font-size:10pt">
            <tr><td style="width:28%"><b>Selasa</b></td><td>Pemasangan zip di komputer kantor dan uji coba awal (bagian 2 &amp; 3).</td></tr>
            <tr><td><b>Rabu</b></td><td>Uji nyata oleh &plusmn;20 peserta dari berbagai perangkat (bagian 4).</td></tr>
        </table>
        <p style="margin-top:8px">Yang diserahkan: <b>1 berkas zip</b> <span class="code">SIP-MAGANG-demo-....zip</span> dan panduan ini
        (salinannya juga ada di dalam zip: <span class="code">PANDUAN_IT_SIP-MAGANG.pdf</span>).</p>
    </div>
    <div class="meta">Disusun oleh Tim Pengembang SIP-MAGANG &middot; <?= $tanggal ?></div>
</div>

<!-- ===================== RINGKASAN ===================== -->
<div class="page-break"></div>
<h2>Ringkasan 1 Halaman</h2>
<p>Satu komputer kantor menjadi <b>server</b>. Peserta membuka aplikasi dari HP/laptop masing-masing lewat
<b>WiFi yang sama</b>, seperti membuka situs biasa. <b>Tidak perlu internet</b> dan <b>tidak perlu memasang apa pun</b>:
PHP, database, dan semua komponen sudah ada di dalam zip.</p>

<table class="diagram">
    <tr>
        <td style="width:30%"><div class="node"><b>Komputer server</b><br>menjalankan<br><span class="code">start_demo.bat</span></div></td>
        <td style="width:8%" class="arrow">&harr;</td>
        <td style="width:24%"><div class="node"><b>WiFi / router</b><br>yang sama untuk semua<br><span class="muted">(internet tidak wajib)</span></div></td>
        <td style="width:8%" class="arrow">&harr;</td>
        <td style="width:30%"><div class="node"><b>&plusmn;20 perangkat peserta</b><br>HP / laptop dengan<br>Chrome, Edge, atau Safari</div></td>
    </tr>
</table>

<h3>5 langkah inti</h3>
<?= $table(['', 'Langkah', 'Cara'], [
    ['1', 'Ekstrak zip', 'Klik kanan zip &rarr; <i>Extract All</i> &rarr; ke '.$code('C:\\').' (hasilnya '.$code('C:\\SIP-MAGANG').').'],
    ['2', 'Izinkan firewall (sekali saja)', 'Klik kanan '.$code('izinkan_firewall.bat').' &rarr; <i>Run as administrator</i> &rarr; tekan <b>Y</b>.'],
    ['3', 'Cek kesiapan', 'Klik dua kali '.$code('CEK_KESIAPAN.bat').' &rarr; harus muncul <b>HASIL: siap</b>.'],
    ['4', 'Nyalakan demo', 'Klik dua kali '.$code('start_demo.bat').'. Browser otomatis membuka halaman <b>ALAMAT DEMO</b> (alamat + kode QR + akun).'],
    ['5', 'Peserta masuk', 'Tampilkan halaman ALAMAT DEMO di layar/proyektor. Peserta di WiFi yang sama cukup memindai QR.'],
], ['6%', '26%', '68%']) ?>

<div class="tip"><b>Sudah dioptimalkan untuk 20 orang.</b> <span class="code">start_demo.bat</span> menyalakan beberapa server sekaligus
(4&ndash;8, sesuai jumlah inti prosesor) dan membagi peserta secara bergiliran. Hasil uji: 20 peserta serentak, separuh halaman terbuka
dalam &plusmn;0,1&ndash;0,3 detik dan 95% halaman dalam &lt; 2 detik, tanpa galat.</div>

<div class="warn"><b>Satu hal paling penting:</b> komputer server dan <b>semua</b> perangkat peserta harus tersambung ke <b>jaringan WiFi/router
yang sama</b>, dan jaringan itu <b>tidak boleh memblokir sesama perangkat</b> (lihat bagian 3).</div>

<!-- ===================== 1. KEBUTUHAN ===================== -->
<h2 style="margin-top:16px">1. Kebutuhan</h2>
<h3>Komputer server (komputer kantor)</h3>
<?= $table(['Komponen', 'Minimal', 'Disarankan'], [
    ['Sistem operasi', 'Windows 10 64-bit', 'Windows 10/11 64-bit terbaru'],
    ['Prosesor', '4 inti (core)', '6&ndash;8 inti atau lebih'],
    ['RAM', '8 GB', '16 GB'],
    ['Ruang disk kosong', '2 GB', '5 GB'],
    ['Hak akses', 'Administrator <b>sekali</b> untuk izin firewall', '&mdash;'],
    ['Daya', 'Charger terpasang', 'Mode daya <i>Best performance</i>, sleep dimatikan'],
], ['30%', '35%', '35%']) ?>
<p><b>Tidak perlu dipasang:</b> internet, PHP, XAMPP, Node.js, Composer, PostgreSQL/MySQL. Zip sudah membawa PHP 8.3 portabel,
Microsoft Visual C++ Runtime, database SQLite berisi data demo, dan tampilan yang sudah jadi.</p>

<h3>Perangkat peserta</h3>
<p>HP Android/iPhone atau laptop apa pun dengan browser terbaru (Chrome, Edge, Safari, Firefox). Tidak perlu memasang aplikasi.</p>

<h3>Jaringan</h3>
<?= $table(['Pilihan', 'Cocok untuk 20 orang?', 'Catatan'], [
    ['<b>Router WiFi sendiri</b> (mis. router travel)', '<b>Ya &mdash; paling disarankan</b>', 'Tidak perlu internet. Alamat server tidak berubah selama router menyala. Bebas dari aturan WiFi kantor.'],
    ['WiFi kantor', 'Ya, <b>bila</b> sesama perangkat boleh saling terhubung', 'Banyak WiFi kantor/tamu memakai <i>client isolation</i>: internet jalan, tapi HP tidak bisa membuka komputer server. Uji dulu pada hari Selasa.'],
    ['Komputer server pakai kabel LAN, peserta pakai WiFi kantor', 'Tergantung', 'Kabel dan WiFi kantor kadang berada di jaringan berbeda (VLAN). Bila HP tidak bisa membuka alamat, sambungkan server ke WiFi yang sama.'],
    ['Hotspot HP', 'Tidak', 'Kebanyakan hotspot HP hanya menerima 5&ndash;10 perangkat. Hanya untuk uji kecil/cadangan.'],
], ['30%', '24%', '46%']) ?>

<!-- ===================== 2. PEMASANGAN ===================== -->
<div class="page-break"></div>
<h2>2. Pemasangan &amp; Uji Coba Awal (Selasa)</h2>

<table class="step">
<tr><td class="no"><span>1</span></td><td><b>Ekstrak zip ke C:\</b><br>
Salin zip ke komputer server, klik kanan &rarr; <i>Extract All</i> &rarr; tujuan <?= $code('C:\\') ?>. Hasilnya folder <?= $code('C:\\SIP-MAGANG') ?>.
Hindari menjalankan langsung dari flashdisk, Desktop yang tersinkron OneDrive, atau dari dalam zip tanpa diekstrak.<br>
<span class="ok">&#10003; Berhasil bila di dalam folder ada BACA_DULU.txt, start_demo.bat, dan folder php.</span></td></tr>

<tr><td class="no"><span>2</span></td><td><b>Izinkan Windows Firewall (sekali saja)</b><br>
Klik kanan <?= $code('izinkan_firewall.bat') ?> &rarr; <i>Run as administrator</i> &rarr; tekan <b>Y</b>. Skrip menghapus aturan blokir untuk php.exe
(bila ada) dan menambah aturan izin <?= $code('SIP-MAGANG Demo (PHP)') ?>. Tanpa langkah ini, perangkat lain melihat "situs tidak dapat dijangkau".<br>
<span class="ok">&#10003; Berhasil bila muncul pesan aturan dibuat.</span></td></tr>

<tr><td class="no"><span>3</span></td><td><b>Cek kesiapan</b><br>
Klik dua kali <?= $code('CEK_KESIAPAN.bat') ?>. Skrip hanya memeriksa (PHP, ekstensi, database, daftar sekolah, port, alamat WiFi, firewall) dan tidak mengubah apa pun.<br>
<span class="ok">&#10003; Berhasil bila baris terakhir: HASIL: siap. Baris [PERHATIAN] cukup dibaca; [GAGAL] harus diperbaiki (bagian 6).</span></td></tr>

<tr><td class="no"><span>4</span></td><td><b>Nyalakan demo</b><br>
Klik dua kali <?= $code('start_demo.bat') ?>. Tunggu &plusmn;1 menit (server dipanaskan dulu agar peserta pertama tidak menunggu lama). Yang terjadi:
<ul>
<li>Jendela hitam utama menampilkan alamat, misalnya <?= $code('http://192.168.1.23:8000') ?>, lalu "Server berjalan" dan "... server aplikasi sudah dipanaskan".</li>
<li>Muncul beberapa jendela kecil (diperkecil di taskbar): <b>SIP-MAGANG Server 1..N</b>, <b>Scheduler</b>, dan <b>Queue Worker</b>.</li>
<li>Browser membuka <b>ALAMAT_DEMO.html</b>: alamat besar, kode QR, dan daftar akun demo.</li>
</ul>
<span class="ok">&#10003; Berhasil bila halaman ALAMAT DEMO terbuka dan alamat di halaman itu bisa dibuka di browser komputer server.</span></td></tr>

<tr><td class="no"><span>5</span></td><td><b>Coba dari 1 HP</b><br>
Sambungkan HP ke WiFi yang sama &rarr; pindai QR di halaman ALAMAT DEMO (atau ketik alamatnya) &rarr; klik <b>MASUK</b> &rarr;
login <?= $code('admin@gmail.com') ?> / <?= $code('admin123') ?>.<br>
<span class="ok">&#10003; Berhasil bila dasbor tampil di HP. Bila "situs tidak dapat dijangkau": lihat bagian 3 dan 6.</span></td></tr>

<tr><td class="no"><span>6</span></td><td><b>Uji 20 pengguna (otomatis)</b><br>
Saat demo menyala, klik dua kali <?= $code('UJI_20_PENGGUNA.bat') ?>. Skrip meniru 20 peserta yang login lalu membuka dasbor, logbook,
notifikasi, dan chat selama 1 menit (hanya membaca, tidak mengubah data magang).<br>
<span class="ok">&#10003; Berhasil bila: HASIL: lancar, dan "Permintaan gagal: 0".</span><br>
<span class="muted">Lebih akurat bila dijalankan dari laptop lain yang juga berisi folder zip ini: buka Command Prompt di folder itu lalu ketik
<?= $code('UJI_20_PENGGUNA.bat http://ALAMAT-SERVER:PORT') ?>.</span></td></tr>

<tr><td class="no"><span>7</span></td><td><b>Matikan &amp; kembalikan data</b><br>
Klik jendela hitam utama &rarr; tekan <b>Ctrl+C</b> &rarr; <b>Y</b>. Semua jendela server ikut berhenti. Agar data Rabu bersih dari uji coba Selasa,
klik dua kali <?= $code('RESET_DATA_DEMO.bat') ?> (data lama disimpan ke <?= $code('database\\cadangan') ?>).</td></tr>
</table>

<!-- ===================== 3. JARINGAN ===================== -->
<h2 style="margin-top:16px">3. Jaringan &amp; Alamat</h2>
<p><b>Alamat demo = alamat IP komputer server di jaringan yang sedang dipakai</b>, ditambah nomor port. Contoh:
<?= $code('http://192.168.1.23:8000') ?>. Karena itu:</p>
<ul>
    <li><b>Semua peserta harus di jaringan yang sama</b> dengan komputer server.</li>
    <li><b>Bila komputer server pindah WiFi</b> (atau router di-restart dan IP berubah), alamat lama tidak bisa dibuka lagi.
        <b>Data tidak hilang.</b> Cukup tutup <?= $code('start_demo.bat') ?>, jalankan lagi, dan tampilkan halaman ALAMAT DEMO yang baru.</li>
    <li><b>Saran untuk IT:</b> beri komputer server alamat tetap (reservasi DHCP di router) agar alamat tidak berubah selama uji coba.</li>
    <li>Bila jendela server menampilkan <b>beberapa alamat</b> (komputer punya LAN, WiFi, atau adaptor virtual), pakai alamat yang
        satu jaringan dengan WiFi peserta (umumnya diawali <?= $code('192.168.') ?> atau <?= $code('10.') ?>).</li>
    <li><b>Angka port di alamat browser bisa berubah</b> (misalnya dari :8000 menjadi :8003). Itu normal: gerbang membagi peserta ke
        beberapa server. Login tetap berlaku.</li>
</ul>

<h3>Uji client isolation (WiFi memblokir sesama perangkat)</h3>
<ol>
    <li>Di komputer server, alamat demo bisa dibuka di browser, tetapi dari HP di WiFi yang sama tidak bisa.</li>
    <li>Pastikan firewall sudah diizinkan (langkah 2) dan HP benar-benar di WiFi yang sama (bukan data seluler).</li>
    <li>Bila tetap tidak bisa, WiFi tersebut memblokir sesama perangkat. Solusi: pakai <b>router WiFi sendiri</b>, atau minta pengecualian
        dari pengelola jaringan untuk sesi uji coba.</li>
</ol>

<div class="note"><b>Tanpa internet tetap jalan.</b> Semua halaman, gambar, PDF surat/sertifikat, dan kode QR dibuat di komputer server.
Yang memang tidak tersedia lewat alamat IP (keterbatasan browser untuk koneksi non-HTTPS): notifikasi push dan rekam pesan suara di chat.
Email "lupa kata sandi" tidak dikirim; reset kata sandi lewat Super Admin.</div>

<!-- ===================== 4. HARI-H ===================== -->
<h2 style="margin-top:16px">4. Hari Uji Nyata (Rabu) &mdash; Daftar Periksa</h2>
<?= $table(['', 'Waktu', 'Yang dilakukan'], [
    [$box, '&minus;30 menit', 'Komputer server: charger terpasang, mode daya <i>Best performance</i>, sleep/hibernate dimatikan, aplikasi berat lain ditutup.'],
    [$box, '&minus;30 menit', 'Router/WiFi menyala; komputer server tersambung ke jaringan yang sama dengan peserta.'],
    [$box, '&minus;20 menit', 'Jalankan '.$code('start_demo.bat').'; tampilkan halaman ALAMAT DEMO di layar/proyektor.'],
    [$box, '&minus;15 menit', 'Coba login dari 1&ndash;2 HP. Opsional: '.$code('UJI_20_PENGGUNA.bat').'.'],
    [$box, 'Mulai', 'Minta peserta login bergantian beberapa detik (login adalah halaman terberat). Bagikan akun sesuai peran.'],
    [$box, 'Selama uji', '<b>Jangan menutup</b> jendela hitam utama maupun jendela kecil "SIP-MAGANG Server n". Cek kesehatan: buka '.$code('http://localhost:PORT/__demo-status').'.'],
    [$box, 'Selesai', 'Ctrl+C di jendela utama &rarr; Y. Bila ingin mengulang dari awal: '.$code('RESET_DATA_DEMO.bat').'.'],
], ['6%', '18%', '76%']) ?>

<!-- ===================== 5. AKUN ===================== -->
<div class="page-break"></div>
<h2>5. Akun Demo</h2>
<p>Kata sandi semua akun: <?= $code('password') ?>, kecuali Super Admin <?= $code('admin@gmail.com') ?>: <?= $code('admin123') ?>.
Daftar ini juga tampil di halaman ALAMAT DEMO. Beberapa peserta boleh memakai akun yang sama.</p>
<?= $table(['Peran', 'Email', 'Yang bisa dicoba'], [
    ['Super Admin', $code('admin@gmail.com'), 'Seluruh menu, master pengguna, kampus/sekolah, pantauan sistem'],
    ['Admin Dinas', $code('admin.dispusip@surabaya.go.id'), 'Verifikasi pengajuan, penempatan, logbook, sertifikat'],
    ['Supervisor (pengesahan)', $code('supervisor@dispusip.surabaya.go.id'), 'Persetujuan tahap 2 (Double ACC), pengesahan sertifikat'],
    ['Mentor Lapangan', $code('mentor.dispusip@surabaya.go.id'), 'Memeriksa logbook harian, laporan akhir, nilai mentor'],
    ['Pembimbing Kampus', $code('dosen.unesa@unesa.ac.id'), 'Pantauan peserta bimbingan, nilai pembimbing'],
    ['Admin Kampus', $code('admin@unesa.ac.id'), 'Peserta &amp; pembimbing kampusnya, surat pengantar'],
    ['Peserta Magang aktif', $code('mahasiswa.aktif@unesa.ac.id'), 'Mengisi logbook, proyek, chat'],
    ['Peserta Magang lulus', $code('mahasiswa.lulus@unesa.ac.id'), 'Unduh sertifikat, pindai QR verifikasi'],
    ['Peserta baru', $code('baru.daftar@unesa.ac.id'), 'Melengkapi profil dari nol lalu mengajukan magang'],
    ['Admin Sekolah (SMKN 7)', $code('admin.smkn7@demo.sch.id'), 'Menyetujui guru pembimbing yang mendaftar sendiri'],
    ['Guru Pembimbing', $code('guru.smkn7@demo.sch.id'), 'Memeriksa logbook siswa, nilai pembimbing'],
    ['Siswa SMK aktif', $code('siswa.smk.aktif@demo.sch.id'), 'Profil sekolah (NIS, kelas), logbook'],
], ['24%', '38%', '38%']) ?>
<p class="muted">Akun skenario lain (pengajuan ditolak, revisi laporan, sertifikat menunggu pengesahan, kelompok, dll.) ada di
<?= $code('PANDUAN_DEMO.md') ?> bagian 2. Peserta juga boleh mendaftar akun baru lewat tombol DAFTAR.</p>

<!-- ===================== 6. MASALAH ===================== -->
<h2 style="margin-top:14px">6. Bila Ada Masalah</h2>
<?= $table(['Gejala', 'Penyebab', 'Solusi'], [
    ['[GAGAL] PHP ditemukan tetapi tidak bisa dijalankan', 'Windows belum punya Visual C++ Runtime (jarang, karena sudah disertakan di zip)', 'Pasang <b>VC_redist.x64.exe</b> (Visual C++ 2015&ndash;2022, x64) dari situs resmi Microsoft: '.$code('aka.ms/vs/17/release/vc_redist.x64.exe').', lalu ulangi.'],
    ['HP: "situs tidak dapat dijangkau"', 'Firewall belum diizinkan, beda WiFi, atau client isolation', 'Ulangi langkah 2 (Run as administrator); pastikan HP di WiFi yang sama; lihat bagian 3.'],
    ['Alamat lama tidak bisa dibuka', 'Komputer server pindah WiFi / IP berubah', 'Tutup dan jalankan ulang '.$code('start_demo.bat').'; pakai alamat baru.'],
    ['Halaman "Server demo belum siap"', 'Server baru dinyalakan atau jendela "SIP-MAGANG Server n" tertutup', 'Tunggu 10 detik lalu muat ulang. Bila tetap, jalankan ulang '.$code('start_demo.bat').'.'],
    ['[PERHATIAN] port 8000 sedang dipakai', 'Ada program lain di port itu', 'Tidak masalah: demo otomatis memakai port lain. Pakai alamat yang tertulis.'],
    ['Terasa lambat saat ramai', 'Komputer sibuk / prosesor sedikit', 'Tutup aplikasi lain, pastikan charger terpasang, cek '.$code('/__demo-status').'. Jumlah server bisa diatur: tambahkan baris '.$code('DEMO_WORKERS=6').' di berkas '.$code('.env').'.'],
    ['Tampilan polos tanpa warna', 'Berkas sisa pengembangan', 'Jalankan ulang '.$code('start_demo.bat').' (membersihkannya otomatis).'],
    ['Logo/gambar tidak muncul', 'Tautan folder unggahan belum dibuat', 'Jalankan ulang '.$code('start_demo.bat').'.'],
    ['Lupa kata sandi akun uji', '&mdash;', 'Login sebagai Super Admin &rarr; Master Pengguna &rarr; reset kata sandi.'],
], ['26%', '30%', '44%']) ?>

<!-- ===================== 7. ISI ZIP ===================== -->
<h2 style="margin-top:16px">7. Isi Folder SIP-MAGANG</h2>
<?= $table(['Berkas / folder', 'Fungsi'], [
    [$code('BACA_DULU.txt'), 'Ringkasan langkah dalam bentuk teks.'],
    [$code('PANDUAN_IT_SIP-MAGANG.pdf'), 'Panduan ini.'],
    [$code('izinkan_firewall.bat'), 'Sekali saja, sebagai Administrator: izin Windows Firewall untuk php.exe.'],
    [$code('CEK_KESIAPAN.bat'), 'Memeriksa kesiapan tanpa mengubah apa pun.'],
    [$code('start_demo.bat'), 'Menyalakan demo (database, beberapa server, gerbang pembagi beban, penjadwal) dan membuka halaman ALAMAT DEMO.'],
    [$code('UJI_20_PENGGUNA.bat'), 'Uji beban otomatis 20 pengguna selama 1 menit.'],
    [$code('RESET_DATA_DEMO.bat'), 'Mengembalikan data demo ke keadaan saat zip diekstrak (server harus dimatikan dulu).'],
    [$code('ALAMAT_DEMO.html'), 'Dibuat setiap '.$code('start_demo.bat').' dijalankan: alamat + QR + akun, untuk ditampilkan ke layar.'],
    [$code('PANDUAN_DEMO.md'), 'Panduan teknis lengkap, termasuk semua akun skenario.'],
    [$code('php\\'), 'PHP 8.3 portabel + Visual C++ Runtime.'],
    [$code('database\\database.sqlite'), 'Database demo. '.$code('database.awal.sqlite').' = salinan awal untuk reset; '.$code('database\\cadangan\\').' = cadangan otomatis saat reset.'],
], ['34%', '66%']) ?>

<h3>Hasil uji kecepatan (komputer pengembang, 8 inti)</h3>
<?= $table(['Susunan', 'Separuh halaman terbuka dalam', '95% halaman terbuka dalam'], [
    ['Cara lama: 1 server, 20 peserta', '11 detik', '26 detik'],
    ['Sekarang: 4&ndash;6 server + gerbang + OPcache, 20 peserta', '0,1&ndash;0,3 detik', '0,5&ndash;1,8 detik'],
    ['Sekarang: 6 server, 30 peserta', '0,4 detik', '2,3 detik'],
], ['50%', '25%', '25%']) ?>
<p class="muted">Peserta tiruan dijalankan di komputer yang sama dengan server, jadi angka di atas adalah gambaran terburuk.
Pada uji nyata, peserta memakai perangkat masing-masing sehingga komputer server lebih ringan.</p>

<h3>Setelah seluruh uji coba selesai</h3>
<ul>
    <li>Matikan server: Ctrl+C di jendela utama &rarr; Y.</li>
    <li>Cabut izin firewall bila tidak dipakai lagi (Command Prompt sebagai Administrator):<br>
        <?= $code('netsh advfirewall firewall delete rule name="SIP-MAGANG Demo (PHP)"') ?></li>
    <li>Folder <?= $code('C:\\SIP-MAGANG') ?> boleh dihapus; tidak ada yang terpasang di luar folder itu.</li>
</ul>

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

$canvas = $dompdf->getCanvas();
$font = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
$canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) use ($font) {
    if ($pageNumber === 1) {
        return;
    }
    $canvas->text(48, 812, 'Panduan Pemasangan & Uji Coba Demo SIP-MAGANG - Tim IT', $font, 7.5, [0.4, 0.45, 0.55]);
    $canvas->text(500, 812, "Halaman {$pageNumber} / {$pageCount}", $font, 7.5, [0.4, 0.45, 0.55]);
});

$out = $base.'/docs/Panduan_IT_Demo_SIP-MAGANG.pdf';
file_put_contents($out, $dompdf->output());
echo 'PDF dibuat: '.$out.' ('.round(filesize($out) / 1024).' KB, '.$canvas->get_page_count()." halaman)\n";
