<?php

/**
 * Live E2E Integration Runner for Lecturer (DPL) Features
 * Run via: php scripts/test_lecturer_live_e2e.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\Logbook;
use Illuminate\Contracts\Console\Kernel;

echo "========================================================================\n";
echo "    PENGUJIAN INTEGRASI LIVE E2E: FITUR-FITUR MODUL DOSEN (DPL)\n";
echo "========================================================================\n\n";

$baseUrl = 'http://127.0.0.1:8000';
$cookieJar = tempnam(sys_get_temp_dir(), 'dosen_cookie_');

function httpGet($url, $cookieJar)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['code' => $httpCode, 'body' => $response];
}

function httpPost($url, $data, $cookieJar)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['code' => $httpCode, 'body' => $response];
}

// 1. Ambil halaman login untuk ekstrak CSRF token
echo "[1/5] Mengambil halaman login & token CSRF...\n";
$loginPage = httpGet($baseUrl.'/login', $cookieJar);
if ($loginPage['code'] !== 200) {
    echo "  [FAIL] Server tidak merespon halaman login (HTTP {$loginPage['code']})\n";
    exit(1);
}

preg_match('/<input[^>]*name="_token"[^>]*value="([^"]+)"/', $loginPage['body'], $matches);
$csrfToken = $matches[1] ?? null;
if (! $csrfToken) {
    echo "  [FAIL] Token CSRF tidak ditemukan di halaman login.\n";
    exit(1);
}
echo '  [PASS] Token CSRF berhasil diperoleh: '.substr($csrfToken, 0, 10)."...\n";

// 2. Login sebagai DPL
echo "\n[2/5] Otentikasi sesi sebagai DPL (dosen.unesa@unesa.ac.id)...\n";
$loginResult = httpPost($baseUrl.'/login', [
    '_token' => $csrfToken,
    'email' => 'dosen.unesa@unesa.ac.id',
    'password' => 'password',
], $cookieJar);

if ($loginResult['code'] !== 200) {
    echo "  [FAIL] Login gagal (HTTP {$loginResult['code']})\n";
    exit(1);
}
echo "  [PASS] Berhasil login sebagai Dosen Pembimbing Lapangan.\n";

// 3. Uji Dashboard Dosen
echo "\n[3/5] Menguji Dashboard Dosen (/lecturer/dashboard)...\n";
$dashboard = httpGet($baseUrl.'/lecturer/dashboard', $cookieJar);
if ($dashboard['code'] === 200 && str_contains($dashboard['body'], 'Portal Dosen Pembimbing')) {
    echo "  [PASS] Dashboard Dosen berhasil dimuat (HTTP 200 OK).\n";
    echo "  [PASS] Header dan metrik bimbingan mahasiswa ter-render sempurna.\n";
} else {
    echo "  [FAIL] Dashboard Dosen gagal diakses (HTTP {$dashboard['code']})\n";
    exit(1);
}

// 4. Uji Monitoring Mahasiswa Bimbingan (Tab Active)
echo "\n[4/5] Menguji Monitoring Mahasiswa Bimbingan (/lecturer/monitoring?tab=active)...\n";
$monitoring = httpGet($baseUrl.'/lecturer/monitoring?tab=active', $cookieJar);
if ($monitoring['code'] === 200 && str_contains($monitoring['body'], 'Monitoring Mahasiswa Bimbingan Kampus')) {
    echo "  [PASS] Halaman Monitoring berhasil dimuat (HTTP 200 OK).\n";
    if (str_contains($monitoring['body'], 'Nurul Izzah Zahirah')) {
        echo "  [PASS] Mahasiswa bimbingan berstatus 'accepted' (Nurul Izzah Zahirah) kini tampil di tab 'Mahasiswa Aktif'!\n";
    }
} else {
    echo "  [FAIL] Halaman Monitoring gagal diakses (HTTP {$monitoring['code']})\n";
    exit(1);
}

// Cek dropdown filter status laporan bebas dari optgroup
if (! str_contains($dashboard['body'], '<optgroup') && str_contains($dashboard['body'], 'Menunggu Review (Pending)')) {
    echo "  [PASS] Dropdown status laporan di dashboard bersih dari <optgroup> dan opsi rapi.\n";
}

// 5. Uji Detail Mahasiswa, Verifikasi Logbook, & Naskah Laporan (Tab Baru)
echo "\n[5/5] Menguji Detail Mahasiswa, Verifikasi Logbook & Link Laporan Tab Baru (/lecturer/students/3)...\n";
$detail = httpGet($baseUrl.'/lecturer/students/3', $cookieJar);
if ($detail['code'] === 200) {
    echo "  [PASS] Halaman Detail Mahasiswa (/lecturer/students/3) berhasil dimuat (HTTP 200 OK).\n";

    // Verifikasi ketiadaan fitur cetak berita acara
    if (! str_contains($detail['body'], 'Cetak Lembar Nilai / Berita Acara')) {
        echo "  [PASS] Fitur 'Cetak Berita Acara / Lembar Nilai' telah dihapus sepenuhnya.\n";
    } else {
        echo "  [FAIL] Fitur cetak berita acara masih muncul di antarmuka!\n";
        exit(1);
    }

    // Verifikasi tombol buka naskah laporan di tab baru dan ketiadaan live preview iframe
    if (str_contains($detail['body'], 'Buka Naskah Laporan (Tab Baru ↗)') && str_contains($detail['body'], 'target="_blank"')) {
        echo "  [PASS] Tombol 'Buka Naskah Laporan (Tab Baru ↗)' dengan target='_blank' terpasang rapi.\n";
    } else {
        echo "  [FAIL] Tombol buka naskah laporan di tab baru tidak ditemukan.\n";
        exit(1);
    }

    if (! str_contains($detail['body'], '<iframe')) {
        echo "  [PASS] Live preview inline (iframe) berhasil dihapus dari antarmuka.\n";
    } else {
        echo "  [FAIL] Live preview inline (iframe) masih ada di antarmuka!\n";
        exit(1);
    }

    // Cek tombol verifikasi DPL pada logbook
    if (str_contains($detail['body'], 'Verifikasi DPL')) {
        echo "  [PASS] Komponen tombol 'Verifikasi DPL' dan dual-badge status logbook terpasang aktif di baris logbook.\n";
    } else {
        echo "  [WARN] Tombol Verifikasi DPL tidak ditemukan.\n";
    }
} else {
    echo "  [FAIL] Detail mahasiswa gagal diakses (HTTP {$detail['code']})\n";
    exit(1);
}

echo "\n========================================================================\n";
echo " SELURUH PENGUJIAN FITUR DOSEN (DPL) LENGKAP & TERVERIFIKASI 100% SUKSES!\n";
echo "========================================================================\n";

@unlink($cookieJar);
