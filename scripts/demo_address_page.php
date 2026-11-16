<?php

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * Membuat ALAMAT_DEMO.html di folder proyek: alamat demo dalam huruf besar + kode QR + akun demo.
 * Dipanggil scripts/demo_preflight.php setiap start_demo.bat dijalankan, lalu dibuka otomatis di
 * browser laptop server agar bisa ditampilkan ke proyektor/layar; peserta cukup memindai QR.
 * Tidak butuh internet: QR dibuat dengan bacon/bacon-qr-code (SVG).
 *
 * @param  array<int, string>  $ips  alamat IP jaringan laptop server (yang pertama dipakai untuk QR)
 */
function sipMagangWriteAddressPage(string $base, array $ips, int $port, int $workers): ?string
{
    if (! is_file($base.'/vendor/autoload.php')) {
        return null;
    }
    require_once $base.'/vendor/autoload.php';

    $e = fn (string $s) => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $mainUrl = 'http://'.($ips[0] ?? 'localhost').':'.$port;

    $qr = '';
    if (class_exists(Writer::class)) {
        $renderer = new ImageRenderer(
            new RendererStyle(320, 1),
            new SvgImageBackEnd
        );
        $qr = (new Writer($renderer))->writeString($mainUrl);
        $qr = preg_replace('/^<\?xml[^>]*>\s*/', '', $qr);
    }

    $envText = (string) @file_get_contents($base.'/.env');
    $adminPassword = preg_match('/^DEMO_ADMIN_PASSWORD=(.+)$/m', $envText, $m) ? trim($m[1], " \t\"'\r") : 'admin123';

    // Akun dari DatabaseSeeder (DemoE2ESeeder, DemoScenarioSeeder). Kata sandi semua "password" kecuali admin@gmail.com.
    $accounts = [
        ['Super Admin', 'admin@gmail.com', $adminPassword],
        ['Admin Dinas', 'admin.dispusip@surabaya.go.id', 'password'],
        ['Supervisor (pengesahan)', 'supervisor@dispusip.surabaya.go.id', 'password'],
        ['Mentor Lapangan', 'mentor.dispusip@surabaya.go.id', 'password'],
        ['Pembimbing Kampus', 'dosen.unesa@unesa.ac.id', 'password'],
        ['Admin Kampus', 'admin@unesa.ac.id', 'password'],
        ['Peserta Magang aktif', 'mahasiswa.aktif@unesa.ac.id', 'password'],
        ['Peserta Magang lulus', 'mahasiswa.lulus@unesa.ac.id', 'password'],
        ['Admin Sekolah (SMKN 7)', 'admin.smkn7@demo.sch.id', 'password'],
        ['Guru Pembimbing', 'guru.smkn7@demo.sch.id', 'password'],
        ['Siswa SMK aktif', 'siswa.smk.aktif@demo.sch.id', 'password'],
    ];
    $rows = '';
    foreach ($accounts as [$role, $email, $password]) {
        $rows .= '<tr><td>'.$e($role).'</td><td class="mono">'.$e($email).'</td><td class="mono">'.$e($password).'</td></tr>';
    }

    $others = '';
    foreach (array_slice($ips, 1) as $ip) {
        $others .= '<div class="mono small">http://'.$e($ip).':'.$port.'</div>';
    }

    $generated = date('d-m-Y H:i');
    $html = <<<HTML
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Alamat Demo SIP-MAGANG</title>
<style>
  body { margin: 0; font-family: "Segoe UI", Arial, sans-serif; background: #f1f5f9; color: #0f172a; }
  .wrap { max-width: 1100px; margin: 0 auto; padding: 28px 24px; }
  h1 { margin: 0 0 4px; font-size: 30px; color: #0f2a5c; }
  .sub { color: #475569; margin-bottom: 22px; }
  .hero { display: flex; gap: 28px; align-items: center; background: #fff; border-radius: 18px; padding: 26px; box-shadow: 0 2px 10px rgba(15,23,42,.08); flex-wrap: wrap; }
  .qr { background: #fff; padding: 10px; border: 2px solid #e2e8f0; border-radius: 12px; }
  .qr svg { display: block; width: 300px; height: 300px; }
  .url { font-size: 44px; font-weight: 800; color: #1d4ed8; letter-spacing: .5px; word-break: break-all; }
  .steps { margin: 14px 0 0; padding-left: 22px; font-size: 19px; line-height: 1.6; }
  .card { background: #fff; border-radius: 18px; padding: 20px 24px; margin-top: 20px; box-shadow: 0 2px 10px rgba(15,23,42,.08); }
  table { width: 100%; border-collapse: collapse; font-size: 16px; }
  th { text-align: left; background: #1e3a8a; color: #fff; padding: 8px 10px; }
  td { padding: 7px 10px; border-bottom: 1px solid #e2e8f0; }
  .mono { font-family: Consolas, "Courier New", monospace; }
  .small { font-size: 15px; color: #475569; }
  .note { background: #fefce8; border-left: 5px solid #eab308; padding: 10px 14px; margin-top: 14px; font-size: 15px; }
</style>
</head>
<body>
<div class="wrap">
  <h1>Demo SIP-MAGANG &mdash; Dispusip Kota Surabaya</h1>
  <div class="sub">Sambungkan HP/laptop ke <b>WiFi yang sama</b> dengan komputer ini, lalu pindai QR atau ketik alamat di bawah.</div>
  <div class="hero">
    <div class="qr">{$qr}</div>
    <div>
      <div class="small">Alamat aplikasi:</div>
      <div class="url mono">{$e($mainUrl)}</div>
      {$others}
      <ol class="steps">
        <li>Sambungkan ke WiFi yang sama dengan komputer server.</li>
        <li>Pindai QR dengan kamera HP, atau ketik alamat di browser (Chrome/Edge/Safari).</li>
        <li>Klik <b>MASUK</b>, lalu pakai salah satu akun di bawah.</li>
      </ol>
    </div>
  </div>
  <div class="card">
    <b style="font-size:18px">Akun demo</b>
    <table style="margin-top:10px">
      <thead><tr><th>Peran</th><th>Email</th><th>Kata sandi</th></tr></thead>
      <tbody>{$rows}</tbody>
    </table>
    <div class="note">Alamat di browser bisa berpindah angka port (misalnya :8001, :8003). Itu normal: komputer server membagi peserta ke {$workers} server agar tetap lancar. Bila alamat tidak bisa dibuka, pastikan HP di WiFi yang sama.</div>
  </div>
  <div class="small" style="margin-top:14px">Dibuat otomatis oleh start_demo.bat pada {$generated}. Halaman ini hanya untuk ditampilkan di layar; tidak perlu dibagikan.</div>
</div>
</body>
</html>
HTML;

    $path = $base.'/ALAMAT_DEMO.html';
    file_put_contents($path, $html);

    return $path;
}
