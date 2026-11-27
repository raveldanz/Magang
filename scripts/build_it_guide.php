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
$tanggal = '22 November 2026';

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
        <td style="width:30%"><div class="node"><b>Komputer server</b><br>menjalankan<br><span class="code">1_MULAI_DEMO.bat</span></div></td>
        <td style="width:8%" class="arrow">&harr;</td>
        <td style="width:24%"><div class="node"><b>WiFi / router</b><br>yang sama untuk semua<br><span class="muted">(internet tidak wajib)</span></div></td>
        <td style="width:8%" class="arrow">&harr;</td>
        <td style="width:30%"><div class="node"><b>&plusmn;20 perangkat peserta</b><br>HP / laptop dengan<br>Chrome, Edge, atau Safari</div></td>
    </tr>
</table>

<h3>Cukup 3 langkah</h3>
<?= $table(['', 'Langkah', 'Cara'], [
    ['1', 'Ekstrak zip', 'Klik kanan zip &rarr; <i>Properties</i> &rarr; centang <b>Unblock</b> (bila ada) &rarr; OK. Lalu klik kanan &rarr; <i>Extract All</i> &rarr; ke '.$code('C:\\').' (hasilnya '.$code('C:\\SIP-MAGANG').').'],
    ['2', 'Klik <b>1_MULAI_DEMO.bat</b>', 'Satu berkas ini mengerjakan semua persiapan: memeriksa lokasi &amp; PHP, <b>meminta izin Windows Firewall</b> (klik <b>YES</b> pada jendela izin), menjaga komputer tidak sleep, menyiapkan database, menyalakan &amp; memanaskan server. Tunggu &plusmn;1&ndash;2 menit.'],
    ['3', 'Peserta masuk', 'Browser otomatis membuka halaman <b>ALAMAT DEMO</b> (alamat besar + kode QR + akun). Tampilkan ke layar/proyektor; peserta di WiFi yang sama cukup memindai QR.'],
], ['6%', '24%', '70%']) ?>
<p class="muted">Berkas tambahan (tidak wajib): <?= $code('2_UJI_20_PENGGUNA.bat') ?> uji otomatis 20 peserta,
<?= $code('3_RESET_DATA_DEMO.bat') ?> kembalikan data ke awal, <?= $code('CEK_KESIAPAN.bat') ?> pemeriksaan saja,
<?= $code('SAMBUNGKAN_POSTGRESQL.bat') ?> pakai PostgreSQL kantor (bagian 7). WiFi kantor: daftar periksa di bagian 8.
Daftar sekolah/kampus &amp; internet: bagian 9.</p>

<div class="tip"><b>Sudah dioptimalkan untuk 20 orang.</b> <span class="code">1_MULAI_DEMO.bat</span> menyalakan beberapa server sekaligus
(4&ndash;8, sesuai jumlah inti prosesor) dan membagi peserta secara bergiliran. Hasil uji: 20 peserta serentak, separuh halaman terbuka
dalam &plusmn;0,1&ndash;0,3 detik dan 95% halaman dalam &lt; 2 detik, tanpa galat.</div>

<div class="warn"><b>Satu hal paling penting:</b> komputer server dan <b>semua</b> perangkat peserta harus tersambung ke <b>jaringan WiFi/router
yang sama</b>, dan jaringan itu <b>tidak boleh memblokir sesama perangkat</b> (lihat bagian 3).</div>

<!-- ===================== 1. KEBUTUHAN ===================== -->
<div class="page-break"></div>
<h2>1. Kebutuhan</h2>
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
Microsoft Visual C++ Runtime, database SQLite berisi data demo, dan tampilan yang sudah jadi.
Bila kantor <b>sudah punya PostgreSQL</b> dan ingin memakainya: bagian 7.</p>

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
<tr><td class="no"><span>1</span></td><td><b>Buka blokir &amp; ekstrak zip ke C:\</b><br>
Salin zip ke komputer server. Klik kanan zip &rarr; <i>Properties</i> &rarr; bila ada kotak <b>Unblock</b>, centang &rarr; OK
(mencegah peringatan keamanan Windows untuk berkas hasil unduhan). Lalu klik kanan &rarr; <i>Extract All</i> &rarr; tujuan <?= $code('C:\\') ?>.
Hasilnya folder <?= $code('C:\\SIP-MAGANG') ?>. Jangan menjalankan dari dalam zip, flashdisk, atau folder yang tersinkron OneDrive.<br>
<span class="ok">&#10003; Berhasil bila di dalam folder ada BACA_DULU.txt, 1_MULAI_DEMO.bat, dan folder php.</span></td></tr>

<tr><td class="no"><span>2</span></td><td><b>Klik dua kali 1_MULAI_DEMO.bat</b><br>
Jendela hitam menampilkan 5 tahap persiapan, masing-masing diakhiri <b>OK</b>:
<ol style="margin-top:3px">
<li><b>Lokasi folder</b> &mdash; menolak bila dijalankan dari dalam zip; memberi peringatan bila di OneDrive.</li>
<li><b>Buka blokir berkas</b> &mdash; membuang tanda "berkas dari internet" pada program di folder ini.</li>
<li><b>PHP</b> &mdash; memastikan PHP bawaan zip bisa berjalan.</li>
<li><b>Izin Windows Firewall</b> &mdash; bila belum ada, muncul jendela <i>"Izinkan aplikasi ini membuat perubahan?"</i>: klik <b>YES / YA</b>.
    Izin dibuat untuk <b>semua jenis jaringan</b> (Public, Private, Domain). Cukup sekali per komputer.</li>
<li><b>Daya</b> &mdash; mencegah sleep dan layar mati selama demo; memberi peringatan bila laptop memakai baterai.</li>
</ol>
Setelah itu demo dinyalakan otomatis (&plusmn;1&ndash;2 menit): database disiapkan, beberapa server aplikasi menyala di jendela kecil
(<b>SIP-MAGANG Server 1..N</b>, <b>Scheduler</b>, <b>Queue Worker</b>, <b>Jaga Agar Tidak Sleep</b>), lalu server dipanaskan.<br>
<span class="ok">&#10003; Berhasil bila muncul "Server berjalan" dan browser membuka halaman ALAMAT DEMO (alamat besar + QR + akun).</span><br>
<span class="muted">Bila muncul <i>"Windows protected your PC"</i>: klik <b>More info</b> &rarr; <b>Run anyway</b>. Bila jendela izin ditolak atau akun
bukan Administrator, demo tetap menyala tetapi HP peserta mungkin tidak bisa masuk; minta Administrator mengeklik kanan
<?= $code('izinkan_firewall.bat') ?> &rarr; <i>Run as administrator</i>.</span></td></tr>

<tr><td class="no"><span>3</span></td><td><b>Coba dari 1 HP</b><br>
Sambungkan HP ke WiFi yang sama &rarr; pindai QR di halaman ALAMAT DEMO (atau ketik alamatnya) &rarr; klik <b>MASUK</b> &rarr;
login <?= $code('admin@gmail.com') ?> / <?= $code('admin123') ?>.<br>
<span class="ok">&#10003; Berhasil bila dasbor tampil di HP. Bila "situs tidak dapat dijangkau": lihat bagian 3 dan 6.</span></td></tr>

<tr><td class="no"><span>4</span></td><td><b>Uji 20 pengguna (otomatis)</b><br>
Saat demo menyala, klik dua kali <?= $code('2_UJI_20_PENGGUNA.bat') ?>. Skrip meniru 20 peserta yang login lalu membuka dasbor, logbook,
notifikasi, dan chat selama 1 menit (hanya membaca, tidak mengubah data magang).<br>
<span class="ok">&#10003; Berhasil bila: HASIL: lancar, dan "Permintaan gagal: 0".</span><br>
<span class="muted">Lebih akurat bila dijalankan dari laptop lain yang juga berisi folder zip ini: buka Command Prompt di folder itu lalu ketik
<?= $code('2_UJI_20_PENGGUNA.bat http://ALAMAT-SERVER:PORT') ?>.</span></td></tr>

<tr><td class="no"><span>5</span></td><td><b>Matikan &amp; kembalikan data</b><br>
Klik jendela hitam utama &rarr; tekan <b>Ctrl+C</b> &rarr; <b>Y</b>. Semua jendela server ikut berhenti. Agar data Rabu bersih dari uji coba Selasa,
klik dua kali <?= $code('3_RESET_DATA_DEMO.bat') ?> (data lama disimpan ke <?= $code('database\\cadangan') ?>).</td></tr>
</table>
<p class="muted">Bila 1_MULAI_DEMO.bat diklik lagi saat demo masih berjalan, demo <b>tidak</b> dimatikan: pilih <b>B</b> untuk membuka lagi
halaman alamat, atau <b>U</b> untuk mengulang dari awal (mis. setelah pindah WiFi).</p>

<!-- ===================== 3. JARINGAN ===================== -->
<h2 style="margin-top:16px">3. Jaringan &amp; Alamat</h2>
<p><b>Alamat demo = alamat IP komputer server di jaringan yang sedang dipakai</b>, ditambah nomor port. Contoh:
<?= $code('http://192.168.1.23:8000') ?>. Karena itu:</p>
<ul>
    <li><b>Semua peserta harus di jaringan yang sama</b> dengan komputer server.</li>
    <li><b>Bila komputer server pindah WiFi</b> (atau router di-restart dan IP berubah), alamat lama tidak bisa dibuka lagi.
        <b>Data tidak hilang.</b> Cukup klik lagi <?= $code('1_MULAI_DEMO.bat') ?> &rarr; pilih <b>U</b> (ulang), lalu tampilkan halaman ALAMAT DEMO yang baru.</li>
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
    [$box, '&minus;20 menit', 'Klik '.$code('1_MULAI_DEMO.bat').' (YES pada jendela izin bila muncul); tunggu &plusmn;1&ndash;2 menit; tampilkan halaman ALAMAT DEMO di layar/proyektor.'],
    [$box, '&minus;15 menit', 'Coba login dari 1&ndash;2 HP. Opsional: '.$code('2_UJI_20_PENGGUNA.bat').'.'],
    [$box, 'Mulai', 'Minta peserta login bergantian beberapa detik (login adalah halaman terberat). Bagikan akun sesuai peran.'],
    [$box, 'Selama uji', '<b>Jangan menutup</b> jendela hitam utama maupun jendela kecil "SIP-MAGANG Server n". Cek kesehatan: buka '.$code('http://localhost:PORT/__demo-status').'.'],
    [$box, 'Selesai', 'Ctrl+C di jendela utama &rarr; Y. Bila ingin mengulang dari awal: '.$code('3_RESET_DATA_DEMO.bat').'.'],
], ['6%', '18%', '76%']) ?>

<!-- ===================== 5. AKUN ===================== -->
<h2 style="margin-top:16px">5. Akun Demo</h2>
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
    ['Alamat lama tidak bisa dibuka', 'Komputer server pindah WiFi / IP berubah', 'Klik '.$code('1_MULAI_DEMO.bat').' &rarr; pilih <b>U</b> (ulang); pakai alamat baru.'],
    ['Halaman "Server demo belum siap"', 'Server baru dinyalakan atau jendela "SIP-MAGANG Server n" tertutup', 'Tunggu 10 detik lalu muat ulang. Bila tetap, jalankan ulang '.$code('start_demo.bat').'.'],
    ['[PERHATIAN] port 8000 sedang dipakai', 'Ada program lain di port itu', 'Tidak masalah: demo otomatis memakai port lain. Pakai alamat yang tertulis.'],
    ['Terasa lambat saat ramai', 'Komputer sibuk / prosesor sedikit', 'Tutup aplikasi lain, pastikan charger terpasang, cek '.$code('/__demo-status').'. Jumlah server bisa diatur: tambahkan baris '.$code('DEMO_WORKERS=6').' di berkas '.$code('.env').'.'],
    ['Tampilan polos tanpa warna', 'Berkas sisa pengembangan', 'Klik '.$code('1_MULAI_DEMO.bat').' &rarr; <b>U</b> (membersihkannya otomatis).'],
    ['Logo/gambar tidak muncul', 'Tautan folder unggahan belum dibuat', 'Klik '.$code('1_MULAI_DEMO.bat').' &rarr; <b>U</b>.'],
    ['Lupa kata sandi akun uji', '&mdash;', 'Login sebagai Super Admin &rarr; Master Pengguna &rarr; reset kata sandi.'],
], ['26%', '30%', '44%']) ?>

<!-- ===================== 6a. RISIKO ===================== -->
<h2 style="margin-top:16px">Risiko yang Sudah Diantisipasi</h2>
<?= $table(['Risiko', 'Yang sudah dilakukan otomatis', 'Yang tetap perlu dicek manusia'], [
    ['Laptop sleep / layar mati di tengah demo', '1_MULAI_DEMO.bat menjaga komputer &amp; layar tetap menyala selama server hidup (tanpa mengubah pengaturan daya).', 'Charger terpasang. Peringatan muncul bila memakai baterai.'],
    ['Izin firewall hanya untuk sebagian jaringan', 'Izin dibuat untuk semua jenis jaringan; izin otomatis Windows yang hanya "Public" tidak dianggap cukup.', 'Klik <b>YES</b> pada jendela izin. Butuh akun Administrator sekali.'],
    ['Dijalankan dari dalam zip', 'Ditolak dengan pesan "ekstrak dulu".', '&mdash;'],
    ['Berkas diblokir karena dari internet', 'Tanda blokir pada program di folder dibuang otomatis.', 'Bila muncul "Windows protected your PC": More info &rarr; Run anyway.'],
    ['PHP tidak jalan (Visual C++ Runtime)', 'Runtime disertakan di folder php; bila tetap gagal, pesan berisi tautan resmi Microsoft.', '&mdash;'],
    ['Tombol diklik dua kali', 'Demo yang sedang berjalan tidak dimatikan; pengguna memilih buka halaman alamat (B) atau ulang (U).', '&mdash;'],
    ['20 orang login bersamaan', 'Beberapa server + pembagi beban + server dipanaskan sebelum dibuka; diuji 20 pengguna tanpa galat.', 'Minta peserta login bergantian beberapa detik.'],
    ['Port 8000 dipakai program lain', 'Otomatis pindah ke port lain dan alamatnya ditampilkan.', 'Pakai alamat di halaman ALAMAT DEMO.'],
    ['Komputer pindah WiFi / IP berubah', 'Data tetap aman di komputer server.', 'Klik 1_MULAI_DEMO.bat &rarr; U, bagikan alamat baru. Saran: reservasi DHCP.'],
    ['WiFi memblokir sesama perangkat', 'Tidak bisa diatasi dari aplikasi.', 'Uji 1 HP pada hari Selasa; siapkan router WiFi sendiri sebagai cadangan.'],
    ['Data uji Selasa terbawa ke Rabu', '3_RESET_DATA_DEMO.bat mengembalikan data awal (data lama dicadangkan).', 'Jalankan setelah uji Selasa.'],
    ['Data pribadi ikut di zip', 'Zip berisi database demo baru; unggahan &amp; chat hasil uji pengembang tidak disertakan.', '&mdash;'],
], ['24%', '44%', '32%']) ?>

<!-- ===================== 7. POSTGRESQL ===================== -->
<div class="page-break"></div>
<h2>7. Memakai PostgreSQL yang Sudah Ada di Kantor (opsional)</h2>
<p>Bawaan zip memakai database <b>SQLite</b> (satu berkas, tanpa pemasangan) dan sudah cukup untuk demo 20 orang.
Bila komputer server, atau server lain di kantor, <b>sudah terpasang PostgreSQL</b> dan ingin dipakai, jalankan
<?= $code('SAMBUNGKAN_POSTGRESQL.bat') ?> <b>sekali</b> sebelum <?= $code('1_MULAI_DEMO.bat') ?>. Tidak ada yang perlu diketik di Command Prompt.</p>

<h3>Syarat</h3>
<?= $table(['Syarat', 'Cara memeriksa'], [
    ['PostgreSQL versi <b>12 atau lebih baru</b> (disarankan 16)', 'pgAdmin &rarr; klik server &rarr; tab <i>Properties</i>, atau Start Menu &rarr; "PostgreSQL 16".'],
    ['Layanan PostgreSQL <b>menyala</b>', 'Tekan Win+R &rarr; '.$code('services.msc').' &rarr; cari <i>postgresql-x64-NN</i> &rarr; status <b>Running</b> (bila tidak: klik kanan &rarr; Start; <i>Startup type</i> sebaiknya Automatic).'],
    ['Tahu <b>kata sandi</b> pengguna '.$code('postgres').'', 'Kata sandi yang diisi saat memasang PostgreSQL. Boleh juga pengguna lain yang punya izin membuat database (CREATEDB).'],
    ['Port (bawaan '.$code('5432').')', 'pgAdmin &rarr; server &rarr; <i>Properties</i> &rarr; <i>Connection</i>.'],
], ['38%', '62%']) ?>
<p class="muted">Alat PostgreSQL (pgAdmin, psql) <b>tidak</b> wajib ada di komputer server: PHP bawaan zip sudah membawa penghubung PostgreSQL.</p>

<h3>Langkah</h3>
<table class="step">
<tr><td class="no"><span>1</span></td><td><b>Pastikan demo mati</b>, lalu klik dua kali <?= $code('SAMBUNGKAN_POSTGRESQL.bat') ?> di folder <?= $code('C:\\SIP-MAGANG') ?>.</td></tr>
<tr><td class="no"><span>2</span></td><td><b>Jawab 6 pertanyaan</b> (tekan Enter untuk nilai di dalam kurung), lihat tabel di bawah.</td></tr>
<tr><td class="no"><span>3</span></td><td><b>Tunggu sampai [SELESAI]</b>. Yang dikerjakan otomatis: uji login &rarr; buat database &rarr; simpan pengaturan lama ke
<?= $code('.env.sqlite.backup') ?> &rarr; tulis pengaturan PostgreSQL ke <?= $code('.env') ?> &rarr; buat tabel &amp; isi data demo (akun yang sama dengan bagian 5)
&rarr; impor daftar sekolah.<br>
<span class="ok">&#10003; Berhasil bila baris terakhir: [SELESAI] SIP-MAGANG tersambung ke PostgreSQL.</span></td></tr>
<tr><td class="no"><span>4</span></td><td><b>Klik <?= $code('1_MULAI_DEMO.bat') ?></b> seperti biasa. Semua langkah bagian 2&ndash;4 tetap sama.</td></tr>
</table>
<?= $table(['Pertanyaan', 'Isi'], [
    ['Alamat server', $code('127.0.0.1').' bila PostgreSQL ada di komputer ini; alamat IP server bila di komputer lain.'],
    ['Port', $code('5432').' (kecuali diubah saat pemasangan).'],
    ['Nama pengguna', $code('postgres').''],
    ['Kata sandi', 'Tidak tampil saat diketik (normal). Tidak disimpan di layar maupun log, hanya di berkas '.$code('.env').'.'],
    ['Nama database', $code('sip_magang').': <b>dibuat otomatis</b>. Database lain di server tidak disentuh.'],
    ['Ambil daftar sekolah se-Indonesia sekarang?', $code('Y').' bila ada internet (3&ndash;5 menit); '.$code('T').' bila tidak (diambil otomatis nanti).'],
], ['36%', '64%']) ?>
<p class="muted">Bila database <?= $code('sip_magang') ?> sudah berisi data (mis. berkas dijalankan ulang), data itu <b>dipakai apa adanya</b>, tidak ditimpa.
<?= $code('3_RESET_DATA_DEMO.bat') ?> pada mode PostgreSQL mengosongkan database itu lalu mengisi ulang data demo awal
(bila pg_dump tersedia, data lama dicadangkan dulu ke <?= $code('database\\cadangan') ?>).</p>

<h3>PostgreSQL di komputer lain (server database kantor)</h3>
<ol>
    <li>Di komputer PostgreSQL, buka <?= $code('postgresql.conf') ?> (folder <i>data</i>), pastikan <?= $code("listen_addresses = '*'") ?>.</li>
    <li>Di <?= $code('pg_hba.conf') ?> tambahkan satu baris untuk komputer server demo, contoh:
        <?= $code('host  all  postgres  192.168.1.23/32  scram-sha-256') ?> (ganti dengan IP komputer server demo; pada PostgreSQL lama
        pakai <?= $code('md5') ?>). Database <?= $code('all') ?> diperlukan karena penyambung juga membuka database <?= $code('postgres') ?> untuk membuat
        <?= $code('sip_magang') ?>; setelah tersambung boleh dipersempit menjadi <?= $code('sip_magang') ?>.</li>
    <li>Restart layanan PostgreSQL, lalu izinkan port <?= $code('5432') ?> masuk di Windows Firewall komputer PostgreSQL
        (Command Prompt sebagai Administrator):<br>
        <?= $code('netsh advfirewall firewall add rule name="PostgreSQL 5432" dir=in action=allow protocol=TCP localport=5432') ?></li>
    <li>Di komputer server demo jalankan <?= $code('SAMBUNGKAN_POSTGRESQL.bat') ?>, isi alamat server dengan IP komputer PostgreSQL.</li>
</ol>
<p class="muted">Peserta tetap membuka alamat komputer server demo; mereka tidak pernah tersambung langsung ke PostgreSQL.</p>

<h3>Pesan yang mungkin muncul</h3>
<?= $table(['Pesan', 'Arti &amp; solusi'], [
    ['Login ditolak: nama pengguna atau kata sandi PostgreSQL salah', 'Ulangi dengan kata sandi yang benar. '.$code('.env').' tidak diubah bila gagal.'],
    ['Tidak bisa terhubung ke PostgreSQL di ...', 'Layanan mati (services.msc &rarr; Start), port salah, atau (server lain) pg_hba.conf / firewall belum diizinkan.'],
    ['... tidak punya izin CREATEDB', 'Buat database '.$code('sip_magang').' lewat pgAdmin (Databases &rarr; Create &rarr; Database), lalu ulangi.'],
    ['PostgreSQL ... terlalu lama', 'Versi di bawah 12. Pakai SQLite bawaan zip, atau perbarui PostgreSQL.'],
    ['Kata sandi berisi tanda petik tunggal', 'Buat pengguna khusus aplikasi dengan kata sandi tanpa tanda petik.'],
    ['Server demo masih menyala', 'Matikan demo (Ctrl+C &rarr; Y), lalu ulangi.'],
    ['[PERHATIAN] saat mengambil daftar sekolah', 'Tidak ada internet / diblokir proxy. Tidak apa-apa: diambil otomatis nanti (bagian 9).'],
], ['40%', '60%']) ?>

<h3>Kembali ke SQLite</h3>
<p>Matikan demo, hapus berkas <?= $code('.env') ?>, ganti nama <?= $code('.env.sqlite.backup') ?> menjadi <?= $code('.env') ?>, lalu klik
<?= $code('1_MULAI_DEMO.bat') ?>. Database PostgreSQL tidak dihapus dan bisa dipakai lagi kapan saja.</p>

<!-- ===================== 8. WIFI KANTOR ===================== -->
<h2 style="margin-top:18px">8. WiFi Kantor &mdash; Daftar Periksa Bagian IT</h2>
<p>Aplikasi berjalan sepenuhnya di komputer server; WiFi hanya perlu <b>mengantar HP peserta ke komputer server</b>. Internet tidak wajib.</p>
<?= $table(['', 'Periksa', 'Mengapa / cara'], [
    [$box, 'Komputer server &amp; HP peserta di <b>SSID dan subnet yang sama</b>', 'IP keduanya berawalan sama (mis. '.$code('192.168.10.x').'). WiFi tamu dan WiFi pegawai sering berbeda jaringan.'],
    [$box, '<b>Client/AP isolation</b> mati untuk SSID itu', 'Bila menyala, internet di HP jalan tetapi komputer server tidak bisa dibuka. Matikan sementara di pengaturan access point, atau pakai router sendiri.'],
    [$box, 'Komputer server memakai <b>WiFi yang sama</b>, bukan kabel LAN di VLAN lain', 'Bila kabel dan WiFi berbeda VLAN, cabut kabel dan sambungkan server ke WiFi peserta.'],
    [$box, '<b>Izin Windows Firewall</b> sudah dibuat', '1_MULAI_DEMO.bat memintanya (klik YES). Cadangan: klik kanan '.$code('izinkan_firewall.bat').' &rarr; Run as administrator. Izin berlaku untuk program PHP di folder ini, di semua jenis jaringan (Public, Private, Domain), jadi nomor port tidak perlu dibuka satu per satu.'],
    [$box, 'Antivirus/firewall pihak ketiga (bila ada) tidak memblokir '.$code('C:\\SIP-MAGANG\\php\\php.exe'), 'Tambahkan pengecualian untuk program itu, atau untuk port TCP '.$code('8000').'&ndash;'.$code('8010').' masuk.'],
    [$box, '<b>Alamat tetap</b> untuk komputer server', 'Reservasi DHCP di router/controller agar alamat (dan QR) tidak berubah antara Selasa dan Rabu.'],
    [$box, 'Kapasitas WiFi &plusmn;25 perangkat', 'Access point kantor umumnya cukup. Hotspot HP tidak disarankan (bagian 1).'],
    [$box, 'Uji 1 HP dari WiFi kantor', 'Bagian 2 langkah 3. Bila gagal padahal di komputer server bisa: isolation / beda jaringan (bagian 3).'],
], ['6%', '38%', '56%']) ?>
<div class="note"><b>Proxy kantor tidak berpengaruh pada peserta</b>: peserta membuka alamat IP lokal, bukan internet. Bila browser peserta memakai
proxy otomatis, alamat lokal biasanya tetap langsung; bila tidak, tambahkan alamat server ke pengecualian proxy.</div>

<!-- ===================== 9. INTERNET & API ===================== -->
<h2 style="margin-top:16px">9. Internet, Daftar Sekolah &amp; Kampus</h2>
<?= $table(['Data', 'Asal', 'Butuh internet?'], [
    ['Kampus / perguruan tinggi', 'Daftar bawaan di dalam aplikasi (252 kampus) + kampus yang ditambahkan Super Admin/peserta.', '<b>Tidak</b>'],
    ['Sekolah demo (SMKN 7, dll.) &amp; sekolah Jawa Timur bawaan', 'Ikut di database zip / diimpor saat persiapan.', '<b>Tidak</b>'],
    ['SMA/SMK/SLB se-Indonesia (&plusmn;25.000)', 'Ikut dari berkas data awal di dalam aplikasi; pembaruan dari direktori publik (api-sekolah-indonesia.vercel.app).', 'Hanya saat memperbarui'],
    ['Madrasah Aliyah (MA)', 'Tidak tersedia di direktori (di bawah Kemenag).', 'Diketik manual'],
], ['30%', '50%', '20%']) ?>
<ul>
    <li><b>Data awal tanpa internet:</b> <?= $code('php\\php.exe artisan institutions:import-snapshot') ?> (dijalankan otomatis saat persiapan; aman diulang).</li>
    <li><b>Tombol admin:</b> menu Kampus &amp; Sekolah &rarr; <b>Muat/Perbarui dari API</b> (Super Admin). Perubahan nama/alamat menunggu persetujuan.</li>
    <li><b>Otomatis:</b> selama demo menyala, penjadwal memeriksa tiap jam; bila belum pernah atau sudah &ge; 7 hari, daftar sekolah se-Indonesia
        diambil (3&ndash;5 menit, berjalan di latar, peserta tidak terganggu). Bila tidak ada internet, dicoba lagi jam berikutnya. Data lama tidak dihapus.</li>
    <li><b>Tanpa internet tetap aman:</b> pendaftaran tetap jalan. Sekolah/kampus yang tidak ada di daftar bisa <b>diketik manual</b>,
        lalu diverifikasi admin.</li>
    <li><b>Sertifikat SSL disertakan</b> (<?= $code('resources\\certs\\cacert.pem') ?>), jadi koneksi aman (HTTPS) ke direktori berhasil walau PHP
        tidak diatur khusus.</li>
    <li><b>Kantor dengan proxy/inspeksi SSL:</b> bila pengambilan selalu gagal, tambahkan sertifikat root kantor ke sebuah berkas .pem lalu tulis
        baris <?= $code('SCHOOL_DIRECTORY_CA_BUNDLE=C:\\path\\sertifikat-kantor.pem') ?> di <?= $code('.env') ?>. Atau izinkan domain
        <?= $code('api-sekolah-indonesia.vercel.app') ?> di proxy.</li>
    <li><b>Mengambil manual</b> (opsional, saat demo mati): Command Prompt di folder SIP-MAGANG &rarr;
        <?= $code('php\\php.exe artisan institutions:sync-external') ?>.</li>
</ul>
<p class="muted">Yang dikirim ke internet hanya permintaan daftar sekolah dan kampus; ketikan pencarian peserta tidak dikirim. Data peserta, akun, dan berkas tidak pernah dikirim keluar.</p>

<!-- ===================== 10. ISI ZIP ===================== -->
<div class="page-break"></div>
<h2>10. Isi Folder SIP-MAGANG</h2>
<?= $table(['Berkas / folder', 'Fungsi'], [
    [$code('BACA_DULU.txt'), 'Ringkasan langkah dalam bentuk teks.'],
    [$code('PANDUAN_IT_SIP-MAGANG.pdf'), 'Panduan ini.'],
    [$code('1_MULAI_DEMO.bat'), '<b>Satu-satunya yang perlu diklik.</b> Semua persiapan (lokasi, PHP, izin firewall, anti-sleep, database, server) lalu membuka halaman ALAMAT DEMO.'],
    [$code('2_UJI_20_PENGGUNA.bat'), 'Uji beban otomatis 20 pengguna selama 1 menit.'],
    [$code('3_RESET_DATA_DEMO.bat'), 'Mengembalikan data demo ke keadaan saat zip diekstrak (server harus dimatikan dulu).'],
    [$code('SAMBUNGKAN_POSTGRESQL.bat'), 'Opsional: memakai PostgreSQL yang sudah terpasang (bagian 7). Pengaturan lama disimpan di '.$code('.env.sqlite.backup').'.'],
    [$code('izinkan_firewall.bat'), 'Cadangan manual izin firewall (klik kanan &rarr; Run as administrator). Biasanya tidak perlu karena sudah diminta 1_MULAI_DEMO.bat.'],
    [$code('CEK_KESIAPAN.bat'), 'Memeriksa kesiapan tanpa mengubah apa pun.'],
    [$code('start_demo.bat'), 'Mesin penyala demo (dipanggil 1_MULAI_DEMO.bat); tidak perlu diklik langsung.'],
    [$code('ALAMAT_DEMO.html'), 'Dibuat setiap demo dinyalakan: alamat + QR + akun, untuk ditampilkan ke layar.'],
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
