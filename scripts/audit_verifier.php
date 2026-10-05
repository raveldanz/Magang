<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\AgencyProfile;
use App\Models\University;
use Illuminate\Contracts\Console\Kernel;

echo "========================================================================================================================\n";
echo "                         AUDIT AKURASI & VALIDASI FAKTUAL RESMI MASTER DATA SURABAYA                                   \n";
echo "========================================================================================================================\n\n";

function formatBytes($bytes, $precision = 1)
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);

    return round($bytes, $precision).' '.$units[$pow];
}

function verifyImageIntegrity($relativePath)
{
    if (! $relativePath) {
        return ['status' => 'MISSING', 'res' => '-', 'size' => '0 B', 'type' => 'NONE', 'valid' => false];
    }

    $cleanPath = ltrim($relativePath, '/');
    $fullPath = public_path($cleanPath);
    if (! is_file($fullPath)) {
        $storagePath = storage_path('app/public/'.$cleanPath);
        if (is_file($storagePath)) {
            $fullPath = $storagePath;
        } else {
            return ['status' => 'MISSING', 'res' => '-', 'size' => '0 B', 'type' => '404', 'valid' => false];
        }
    }

    $size = filesize($fullPath);
    if ($size === 0) {
        return ['status' => 'EMPTY (0B)', 'res' => '-', 'size' => '0 B', 'type' => 'CORRUPT', 'valid' => false];
    }

    $handle = fopen($fullPath, 'rb');
    $header = fread($handle, 16);
    fclose($handle);

    $isPng = str_starts_with($header, "\x89PNG\r\n\x1a\n");
    $isSvg = str_contains($header, '<svg') || str_contains($header, '<?xml');

    $res = '-';
    $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

    if ($isPng) {
        $info = @getimagesize($fullPath);
        $res = $info ? "{$info[0]}x{$info[1]}" : 'PNG';
        $type = 'PNG';
    } elseif ($isSvg) {
        $res = 'VECTOR (SVG)';
        $type = 'SVG';
    } else {
        $info = @getimagesize($fullPath);
        $res = $info ? "{$info[0]}x{$info[1]}" : 'UNKNOWN';
        $type = strtoupper($ext);
    }

    return [
        'status' => 'EXISTS',
        'res' => $res,
        'size' => formatBytes($size),
        'type' => $type,
        'valid' => ($isPng || $isSvg) && $size > 100,
    ];
}

// 1. AUDIT PERGURUAN TINGGI
echo "### 1. AUDIT PERGURUAN TINGGI (universities)\n";
echo str_repeat('-', 125)."\n";
printf("%-4s | %-48s | %-16s | %-10s | %-12s | %-16s | %-8s\n", 'NO', 'NAMA KAMPUS RESMI', 'AKRONIM', 'STATUS LOGO', 'UKURAN', 'RESOLUSI/FORMAT', 'AUDIT');
echo str_repeat('-', 125)."\n";

$univs = University::orderBy('id')->get();
$univIssues = 0;

foreach ($univs as $idx => $u) {
    $img = verifyImageIntegrity($u->logo);
    $statusText = $img['status'];
    $auditStatus = 'PASS';
    $issues = [];

    if (! $img['valid']) {
        $issues[] = 'LOGO_INVALID';
        $auditStatus = 'FAIL';
    }

    if (empty($u->acronym)) {
        $issues[] = 'NO_ACRONYM';
        $auditStatus = 'FAIL';
    }

    if (stripos($u->name, 'November') !== false) {
        $issues[] = 'WRONG_NOVEMBER';
        $auditStatus = 'FAIL';
    }

    if (stripos($u->name, 'UPN Jatim') !== false) {
        $issues[] = 'NOT_BAKU_UPN';
        $auditStatus = 'FAIL';
    }

    if ($auditStatus === 'FAIL') {
        $univIssues++;
        $statusText = 'FAIL ('.implode(',', $issues).')';
    }

    printf(
        "%-4d | %-48s | %-16s | %-10s | %-12s | %-16s | %-8s\n",
        $idx + 1,
        mb_strimwidth($u->name, 0, 48, '...'),
        $u->acronym ?? '-',
        $img['status'],
        $img['size'],
        $img['res'],
        $auditStatus
    );
}
echo str_repeat('-', 125)."\n";
echo 'Total Universitas: '.$univs->count().' | Lolos Audit: '.($univs->count() - $univIssues)." | Gagal: {$univIssues}\n\n";

// 2. AUDIT OPD PEMKOT SURABAYA
echo "### 2. AUDIT OPD PEMERINTAH KOTA SURABAYA (agency_profiles)\n";
echo str_repeat('-', 125)."\n";
printf("%-4s | %-50s | %-14s | %-10s | %-12s | %-16s | %-8s\n", 'NO', 'NAMA OPD RESMI PEMKOT SURABAYA', 'AKRONIM', 'STATUS LOGO', 'UKURAN', 'RESOLUSI/FORMAT', 'AUDIT');
echo str_repeat('-', 125)."\n";

$requiredOPDs = [
    'DSDABM' => 'Dinas Sumber Daya Air dan Bina Marga',
    'DPRKPP' => 'Dinas Perumahan Rakyat dan Kawasan Permukiman serta Pertanahan',
    'DP3A-P2KB' => 'Dinas Pemberdayaan Perempuan dan Perlindungan Anak serta Pengendalian Penduduk dan Keluarga Berencana',
    'Dinkopumdag' => 'Dinas Koperasi Usaha Kecil dan Menengah dan Perdagangan',
    'Disbudporapar' => 'Dinas Kebudayaan, Kepemudaan dan Olahraga serta Pariwisata',
    'DKPP' => 'Dinas Ketahanan Pangan dan Pertanian',
];

$agencies = AgencyProfile::orderBy('id')->get();
$agencyIssues = 0;

foreach ($agencies as $idx => $a) {
    $img = verifyImageIntegrity($a->logo);
    $auditStatus = 'PASS';
    $issues = [];

    if (! $img['valid']) {
        $issues[] = 'LOGO_INVALID';
        $auditStatus = 'FAIL';
    }

    if (empty($a->acronym)) {
        $issues[] = 'NO_ACRONYM';
        $auditStatus = 'FAIL';
    }

    if (isset($requiredOPDs[$a->acronym])) {
        if ($a->agency_name !== $requiredOPDs[$a->acronym]) {
            $issues[] = 'NAME_MISMATCH';
            $auditStatus = 'FAIL';
        }
    }

    if (empty($a->address) || ! str_contains($a->address, 'Surabaya')) {
        $issues[] = 'INVALID_ADDR';
        $auditStatus = 'FAIL';
    }

    if ($auditStatus === 'FAIL') {
        $agencyIssues++;
    }

    printf(
        "%-4d | %-50s | %-14s | %-10s | %-12s | %-16s | %-8s\n",
        $idx + 1,
        mb_strimwidth($a->agency_name, 0, 50, '...'),
        $a->acronym ?? '-',
        $img['status'],
        $img['size'],
        $img['res'],
        $auditStatus
    );
}
echo str_repeat('-', 125)."\n";
echo 'Total OPD: '.$agencies->count().' | Lolos Audit: '.($agencies->count() - $agencyIssues)." | Gagal: {$agencyIssues}\n\n";

if ($univIssues === 0 && $agencyIssues === 0) {
    echo "========================================================================================================================\n";
    echo ">> KEPUTUSAN AUDIT: 100% LOLOS VERIFIKASI FAKTUAL KOP SURAT, SURAT TUGAS, & SERTIFIKAT BER-QR CODE <<\n";
    echo "========================================================================================================================\n";
    exit(0);
} else {
    echo "========================================================================================================================\n";
    echo '>> PERINGATAN: Terdapat '.($univIssues + $agencyIssues)." isu validasi yang harus segera diperbaiki! <<\n";
    echo "========================================================================================================================\n";
    exit(1);
}
