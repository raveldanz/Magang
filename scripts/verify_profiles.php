<?php

use App\Models\AgencyProfile;
use App\Models\University;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$upn = University::find(7);
if ($upn) {
    $upn->update([
        'name' => 'Universitas Pembangunan Nasional Veteran Jakarta',
        'code' => 'UPNVJ',
        'acronym' => 'UPNVJ',
        'logo' => 'images/logos/upnvj.png',
        'address' => 'Jl. RS. Fatmawati Raya, Pondok Labu, Kec. Cilandak, Kota Jakarta Selatan, DKI Jakarta 12450',
        'phone' => '(021) 7656971',
        'email' => 'humas@upnvj.ac.id',
        'pic_name' => 'Prof. Dr. Anter Venus, MA.Comm.',
        'pic_position' => 'Rektor UPN Veteran Jakarta',
        'is_verified' => true,
        'status' => 'active',
    ]);
}

echo '=== DAFTAR UNIVERSITAS & STATUS LOGO ==='.PHP_EOL;
$univs = University::orderBy('id')->get();
$missingUnivLogos = 0;
foreach ($univs as $u) {
    $exists = is_file(public_path($u->logo)) ? 'VALID (Ada)' : 'KOSONG / TIDAK DITEMUKAN';
    if ($exists !== 'VALID (Ada)') {
        $missingUnivLogos++;
    }
    echo sprintf("[%02d] %-12s | %-45s | Logo: %-30s | %s\n", $u->id, $u->code ?? '-', substr($u->name, 0, 45), $u->logo ?? 'NULL', $exists);
}

echo PHP_EOL.'=== DAFTAR INSTANSI DINAS & STATUS LOGO ==='.PHP_EOL;
$agencies = AgencyProfile::orderBy('id')->get();
$missingAgencyLogos = 0;
foreach ($agencies as $a) {
    $exists = is_file(public_path($a->logo)) ? 'VALID (Ada)' : 'KOSONG / TIDAK DITEMUKAN';
    if ($exists !== 'VALID (Ada)') {
        $missingAgencyLogos++;
    }
    echo sprintf("[%02d] %-15s | %-55s | Logo: %-35s | %s\n", $a->id, $a->acronym ?? '-', substr($a->agency_name, 0, 55), $a->logo ?? 'NULL', $exists);
}

echo PHP_EOL;
echo 'Total Universitas: '.$univs->count()." (Missing Logo: {$missingUnivLogos})".PHP_EOL;
echo 'Total Instansi Dinas: '.$agencies->count()." (Missing Logo: {$missingAgencyLogos})".PHP_EOL;
