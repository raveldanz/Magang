{{--
    Ornamen sudut sertifikat (kiri-atas = warna utama, kanan-bawah = warna sekunder).
    Dipakai bersama oleh sertifikat (internship_certificate) dan pratinjau live di Pengaturan Tema,
    sehingga desain keduanya selalu identik.

    Mosaik segitiga dibuat dari grid rapat tanpa celah: tiap sel dibagi 2 segitiga, tepi diagonal
    dipotong lurus, dan tiap segitiga diberi garis tepi setipis rambut berwarna sama agar tidak
    muncul garis putih halus ("bolong") saat dirender / dicetak ke PDF.

    Variabel: $pPalette, $sPalette (palet 'darkest'..'lighter'), $lite (pratinjau kecil: gelombang lebih sedikit).
--}}
@php
    $lite = $lite ?? false;
    $showWaves = $showWaves ?? true;   // false: hanya mosaik sudut (dipakai halaman 2)
    $k = $scale ?? 1;                  // skala ornamen (halaman 2 memakai versi kecil)
    $order = ['darkest', 'darker', 'dark', 'base', 'medium', 'light', 'lighter'];
    $cell = 9.9;      // ukuran sel (mm)
    $steps = 6;       // jumlah sel di sepanjang sisi => sudut ~59 mm

    $cornerTriangles = function (int $seed) use ($order, $cell, $steps): array {
        $out = [];
        for ($row = 0; $row < $steps; $row++) {
            for ($col = 0; $col < $steps; $col++) {
                $d = $col + $row;
                if ($d > $steps - 1) {
                    continue;
                }
                $x = round($col * $cell, 2);
                $y = round($row * $cell, 2);
                $x2 = round($x + $cell, 2);
                $y2 = round($y + $cell, 2);

                // hash deterministik: hasil sama tiap render, tampak acak alami
                $h = (($col + 1) * 73856093 ^ ($row + 1) * 19349663 ^ $seed * 83492791) & 0xFFFF;
                $t = $d / ($steps - 1);

                $shadeFor = function (int $salt) use ($t, $h, $order) {
                    $jitter = ((($h >> $salt) % 7) - 3) * 0.6;       // -1.8 .. +1.8 => kontras antar segitiga lebih jelas
                    $idx = (int) round($t * 4.0 + 0.4 + $jitter);
                    return $order[max(0, min(count($order) - 1, $idx))];
                };

                if ($d === $steps - 1) {
                    // sel tepi diagonal: setengah segitiga menghadap sudut
                    $out[] = ["$x,$y $x2,$y $x,$y2", $shadeFor(0)];
                    continue;
                }

                if (($h & 1) === 0) {
                    $out[] = ["$x,$y $x2,$y $x2,$y2", $shadeFor(1)];
                    $out[] = ["$x,$y $x2,$y2 $x,$y2", $shadeFor(3)];
                } else {
                    $out[] = ["$x,$y $x2,$y $x,$y2", $shadeFor(2)];
                    $out[] = ["$x2,$y $x2,$y2 $x,$y2", $shadeFor(4)];
                }
            }
        }
        return $out;
    };

    $leftWaves = [
        'M 0,46 C 12,82 28,120 0,160', 'M 0,47 C 14,83 30,121 0,162', 'M 0,48 C 16,84 32,122 0,164',
        'M 0,49 C 18,85 34,123 0,166', 'M 0,50 C 20,86 36,124 0,168', 'M 0,51 C 22,87 38,125 0,170',
        'M 0,52 C 24,88 40,126 0,172', 'M 0,53 C 26,89 42,127 0,175', 'M 0,54 C 28,90 44,128 0,178',
        'M 0,55 C 30,91 46,129 0,181', 'M 0,56 C 32,92 48,130 0,184', 'M 0,57 C 34,93 50,132 0,188',
        'M 0,58 C 36,94 52,134 0,192',
    ];
    $rightWaves = [
        'M 297,165 C 270,128 258,85 297,42', 'M 297,167 C 268,126 256,83 297,45', 'M 297,169 C 266,124 254,81 297,48',
        'M 297,171 C 264,122 252,79 297,51', 'M 297,173 C 262,120 250,77 297,54', 'M 297,175 C 260,118 248,75 297,57',
        'M 297,177 C 258,116 246,73 297,60', 'M 297,179 C 256,114 244,71 297,63', 'M 297,181 C 254,112 242,69 297,66',
        'M 297,183 C 252,110 240,67 297,70', 'M 297,185 C 250,108 238,65 297,74', 'M 297,187 C 248,106 236,63 297,78',
    ];
    if ($lite) {
        $leftWaves = array_values(array_filter($leftWaves, fn ($v, $i) => $i % 2 === 0, ARRAY_FILTER_USE_BOTH));
        $rightWaves = array_values(array_filter($rightWaves, fn ($v, $i) => $i % 2 === 0, ARRAY_FILTER_USE_BOTH));
    }
    $waveOpacity = fn (int $i, int $n) => round(0.40 + 0.45 * ($i / max(1, $n - 1)), 2);
    $waveWidth = $lite ? 0.35 : 0.45;
@endphp

{{-- Gelombang (digambar lebih dulu agar pangkalnya tertutup mosaik) --}}
@if ($showWaves)
<g fill="none" data-wave="p" stroke="{{ $pPalette['medium'] }}" stroke-width="{{ $waveWidth }}">
    @foreach ($leftWaves as $i => $d)
        <path d="{{ $d }}" opacity="{{ $waveOpacity($i, count($leftWaves)) }}" />
    @endforeach
</g>
<g fill="none" data-wave="s" stroke="{{ $sPalette['medium'] }}" stroke-width="{{ $waveWidth }}">
    @foreach ($rightWaves as $i => $d)
        <path d="{{ $d }}" opacity="{{ $waveOpacity($i, count($rightWaves)) }}" />
    @endforeach
</g>

@endif

{{-- Mosaik kiri-atas (warna utama) --}}
<g stroke-width="0.25" stroke-linejoin="round" transform="scale({{ $k }})">
    @foreach ($cornerTriangles(1) as [$pts, $shade])
        <polygon points="{{ $pts }}" data-pal="p" data-shade="{{ $shade }}" fill="{{ $pPalette[$shade] }}" stroke="{{ $pPalette[$shade] }}" />
    @endforeach
</g>

{{-- Mosaik kanan-bawah (warna sekunder) — geometri yang sama diputar 180° --}}
<g stroke-width="0.25" stroke-linejoin="round" transform="translate(297 210) scale(-{{ $k }} -{{ $k }})">
    @foreach ($cornerTriangles(7) as [$pts, $shade])
        <polygon points="{{ $pts }}" data-pal="s" data-shade="{{ $shade }}" fill="{{ $sPalette[$shade] }}" stroke="{{ $sPalette[$shade] }}" />
    @endforeach
</g>
