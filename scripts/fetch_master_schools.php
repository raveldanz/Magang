<?php

/**
 * Ambil daftar sekolah menengah (SMA/SMK/MA dan sederajat) dari portal resmi
 * Data Referensi Kemendikdasmen lalu simpan ke database/data/master_schools.json.
 *
 * Dataset ini dipakai MasterUniversityService (pencarian) dan `php artisan universities:sync-master`
 * (impor ke tabel universities sebagai institution_type = sekolah). Aplikasi TIDAK memanggil portal
 * ini saat berjalan; skrip hanya dijalankan manual bila data perlu diperbarui atau diperluas.
 *
 * Pemakaian:
 *   php scripts/fetch_master_schools.php                 # Kota Surabaya (kode wilayah 056000)
 *   php scripts/fetch_master_schools.php 056000 050500   # beberapa kab/kota (kode tingkat 2)
 *
 * Kode kab/kota terlihat di alamat https://referensi.data.kemendikdasmen.go.id/satpen/dikmen/050000/1
 * (Prov. Jawa Timur). SLB tidak diambil karena bukan sasaran program magang sekolah menengah.
 */
const BASE_URL = 'https://referensi.data.kemendikdasmen.go.id';
const OUTPUT = __DIR__.'/../database/data/master_schools.json';
const SKIPPED_FORMS = ['SLB'];

/** Nama resmi Dapodik memakai singkatan; bentuk panjang lebih mudah dicari dan tampil rapi di surat. */
const PREFIX_EXPANSIONS = [
    'SMAN' => 'SMA NEGERI',
    'SMKN' => 'SMK NEGERI',
    'SMAS' => 'SMA',
    'SMKS' => 'SMK',
    'MAS' => 'MA',
];

function fetchPage(string $path): string
{
    $context = stream_context_create(['http' => [
        'timeout' => 60,
        'header' => "User-Agent: SIP-MAGANG data refresh\r\nAccept: text/html\r\n",
    ]]);

    for ($attempt = 1; $attempt <= 3; $attempt++) {
        $html = @file_get_contents(BASE_URL.$path, false, $context);
        if ($html !== false && $html !== '') {
            return $html;
        }
        sleep($attempt * 2);
    }

    fwrite(STDERR, "Gagal mengambil {$path}\n");
    exit(1);
}

function text(string $html): string
{
    return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
}

function expandName(string $official): string
{
    $parts = explode(' ', $official, 2);
    $prefix = PREFIX_EXPANSIONS[$parts[0]] ?? null;

    return $prefix === null ? $official : trim($prefix.' '.($parts[1] ?? ''));
}

$regionCodes = array_slice($argv, 1) ?: ['056000'];
$schools = [];

foreach ($regionCodes as $regionCode) {
    if (! preg_match('/^\d{6}$/', $regionCode)) {
        fwrite(STDERR, "Kode wilayah tidak valid: {$regionCode}\n");
        exit(1);
    }

    $regionHtml = fetchPage("/satpen/dikmen/{$regionCode}/2");
    preg_match('/<span class="current">([^<]+)<\/span>/', $regionHtml, $cityMatch);
    preg_match_all('/<a href="\/satpen\/dikmen\/\d{6}\/1">([^<]+)<\/a>/', $regionHtml, $provinceMatch);
    $city = text($cityMatch[1] ?? $regionCode);
    $province = preg_replace('/^Prov\.\s*/', '', text(end($provinceMatch[1]) ?: 'Indonesia'));

    // Rekap per kecamatan: jumlah total dipakai untuk memastikan tidak ada baris yang terlewat
    preg_match_all(
        '/\/satpen\/dikmen\/(\d{6})\/3[^"]*">([^<]+)<\/a><\/td><td class="number">\d+<\/td><td class="number">\d+<\/td><td class="number total">(\d+)</',
        $regionHtml,
        $districts,
        PREG_SET_ORDER
    );

    if ($districts === []) {
        fwrite(STDERR, "Tidak ada kecamatan ditemukan untuk {$regionCode}; struktur halaman mungkin berubah.\n");
        exit(1);
    }

    foreach ($districts as [, $districtCode, $districtName, $expected]) {
        $html = fetchPage("/satpen/dikmen/{$districtCode}/3?jalur=jf&status=all");
        preg_match_all(
            '/<tr><td>\d+<\/td><td>(\d+)<\/td><td><a[^>]*>([^<]+)<\/a><\/td><td>([^<]*)<\/td><td>([^<]*)<\/td><td>([^<]*)<\/td><td>([^<]*)<\/td><\/tr>/',
            $html,
            $rows,
            PREG_SET_ORDER
        );

        if (count($rows) !== (int) $expected) {
            fwrite(STDERR, sprintf("%s: %d baris, rekap menyebut %d. Hentikan agar data tidak setengah.\n", text($districtName), count($rows), $expected));
            exit(1);
        }

        foreach ($rows as [, $npsn, $name, $form, $status, $address, $village]) {
            $form = text($form);
            if (in_array($form, SKIPPED_FORMS, true)) {
                continue;
            }

            $official = text($name);
            $schools[$npsn] = [
                'npsn' => $npsn,
                'name' => expandName($official),
                'official_name' => $official,
                'form' => $form,
                'status' => text($status),
                'address' => text($address),
                'village' => text($village),
                'district' => preg_replace('/^Kec\.\s*/', '', text($districtName)),
                'city' => $city,
                'province' => $province,
            ];
        }

        printf("%-28s %3d sekolah\n", text($districtName), count($rows));
        usleep(500_000);
    }
}

$schools = array_values($schools);
usort($schools, fn ($a, $b) => [$a['city'], $a['name']] <=> [$b['city'], $b['name']]);

file_put_contents(OUTPUT, json_encode($schools, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
printf("Tersimpan %d sekolah ke %s\n", count($schools), realpath(OUTPUT));
