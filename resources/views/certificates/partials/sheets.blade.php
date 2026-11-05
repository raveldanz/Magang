{{--
    Lembar sertifikat (halaman 1) + lampiran transkrip (halaman 2).
    Satu sumber desain untuk cetak satuan (internship_certificate) dan cetak kolektif (batch_print),
    sehingga keduanya selalu identik. Butuh variabel: $theme, $student, $profile, $university,
    $application, $placement, $eval, $finalReport, $regNumber, $qrSvg, $verifyCertificateUrl.
--}}
@once
<style>
        .font-cert-serif {
            font-family: 'Times New Roman', Times, 'Playfair Display', serif;
        }

        /* Judul tebal ala sertifikat resmi dinas (slab display) */
        .font-cert-title {
            font-family: 'Alfa Slab One', 'Roboto Slab', Rockwell, 'Playfair Display', Georgia, serif;
            font-weight: 400 !important; /* cegah faux-bold (bayangan ganda) pada font display satu bobot */
            -webkit-text-stroke: 0;
        }

        .font-cert-name {
            font-family: 'Roboto Slab', Rockwell, 'Playfair Display', Georgia, serif;
            font-weight: 800;
        }

        /* QR: tepi modul tajam (tanpa garis halus) saat dicetak / PDF */
        .qr-wrap svg { display: block; width: 100%; height: 100%; }
        .qr-wrap svg path,
        .qr-wrap svg rect { shape-rendering: crispEdges; }

        .font-cert-body {
            font-family: 'Times New Roman', 'Playfair Display', Georgia, serif;
        }

        .font-cert-tte {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }

</style>
@endonce
@php
    \Carbon\Carbon::setLocale('id'); // nama bulan Indonesia juga pada cetak kolektif
    $theme = $theme ?? \App\Models\CertificateTheme::getActive();
    $primaryColor = $theme->primary_color ?? '#00A3C4';
    $secondaryColor = $theme->secondary_color ?? '#F36F21';
    $accentColor = $theme->accent_color ?? '#D97706';
    $pPalette = $theme->getPrimaryPalette();
    $sPalette = $theme->getSecondaryPalette();
    $pDarkest = $pPalette['darkest'];
@endphp
    @php
        // Logo Utama Dinas & Kota Surabaya (Transparan Presisi)
        $officialLogosStripUrl = asset('images/certificates/logos_official_strip.png');
        $surabayaLogoUrl = asset('images/certificates/logo_surabaya.png');
        $surabayaWordmarkUrl = asset('images/certificates/logo_surabaya_wordmark.png');
        $dispusipLogoUrl = asset('images/certificates/logo_dispusip.png');
        $berakhlakLogoUrl = asset('images/certificates/logo_berakhlak.png');
        $banggaLogoUrl = asset('images/certificates/logo_bangga_melayani.png');
        $zonaLogoUrl = asset('images/certificates/logo_zona_integritas.png');

        // Simple Grade untuk Halaman 2
        $simpleGrade = $eval->grade_calculated ?? ($eval->grade ?? 'A');
        $predikat = ['A' => 'Sangat Memuaskan', 'B' => 'Memuaskan', 'C' => 'Cukup'][strtoupper((string) $simpleGrade)] ?? 'Kurang';

    @endphp

    <!-- ================================================================= -->
    <!-- HALAMAN 1: SERTIFIKAT PENGHARGAAN RESMI DISPUSIP (DESAIN PRESISI) -->
    <!-- ================================================================= -->
    <main class="certificate-sheet relative flex flex-col justify-between overflow-hidden bg-white select-none">
        
        <!-- DYNAMIC SVG VECTOR ARTWORK (SUDUT KIRI ATAS & SUDUT KANAN BAWAH) -->
        <svg viewBox="0 0 297 210" class="absolute inset-0 w-full h-full pointer-events-none z-0" xmlns="http://www.w3.org/2000/svg">

            <!-- 0. STRIP BINGKAI ATAS (BIRU) & BAWAH (EMAS) SEPERTI SERTIFIKAT ASLI DINAS -->
            <rect x="0" y="0" width="297" height="2.6" fill="{{ $pDarkest }}" />
            <rect x="0" y="207.6" width="297" height="2.4" fill="{{ $accentColor }}" />

            @include('certificates.partials.corner-ornaments')
        </svg>

        <!-- KONTEN SERTIFIKAT TERTATA PRESISI (LAYERING Z-INDEX 10) -->
        <div class="relative z-10 flex flex-col justify-between h-full pt-12 pb-9 px-8 sm:px-12 text-center">
            
            <!-- 1. HEADER KOP: LOGO RESMI DALAM KAPSUL (Dispusip, BerAKHLAK, Bangga Melayani Bangsa, Zona Integritas | Lambang + Surabaya City of Heroes) -->
            <div class="pt-1 flex items-center justify-center">
                <div class="inline-flex items-center justify-center gap-[16px] rounded-full px-7 h-[56px] max-w-[94%]"
                     style="background: linear-gradient(90deg, {{ $pPalette['lighter'] }}40 0%, #ffffff 28%, #ffffff 72%, {{ $sPalette['lighter'] }}40 100%); border: 1px solid {{ $pPalette['light'] }}; box-shadow: 0 1px 4px rgba(15, 23, 42, 0.10), inset 0 0 0 1px rgba(255,255,255,0.7);">
                    <img src="{{ $surabayaLogoUrl }}" alt="Pemerintah Kota Surabaya" class="h-[40px] w-auto object-contain" onerror="this.onerror=null; this.src='{{ asset('images/certificates/logo_surabaya.png') }}';">
                    <img src="{{ $dispusipLogoUrl }}" alt="Dispusip Kota Surabaya" class="h-[40px] w-auto object-contain" onerror="this.style.display='none'">
                    <img src="{{ $berakhlakLogoUrl }}" alt="BerAKHLAK" class="h-[30px] w-auto object-contain" onerror="this.style.display='none'">
                    <img src="{{ $banggaLogoUrl }}" alt="Bangga Melayani Bangsa" class="h-[34px] w-auto object-contain" onerror="this.style.display='none'">
                    <img src="{{ $zonaLogoUrl }}" alt="Zona Integritas" class="h-[30px] w-auto object-contain" onerror="this.style.display='none'">
                    <span class="self-center block w-[2px] h-[36px] bg-black rounded-full"></span>
                    <img src="{{ $surabayaWordmarkUrl }}" alt="Surabaya City of Heroes" class="h-[27px] w-auto object-contain" onerror="this.style.display='none'">
                </div>
            </div>

            <!-- 2. JUDUL SERTIFIKAT & NOMOR REGISTRASI RESMI -->
            <div class="mt-2 space-y-1">
                <h1 class="font-cert-title text-4xl sm:text-5xl md:text-[58px] text-black tracking-[0.03em] uppercase leading-none">
                    SERTIFIKAT
                </h1>
                <h2 class="font-cert-serif text-3xl sm:text-4xl md:text-[38px] font-normal text-black tracking-[0.06em] uppercase leading-none pt-2">
                    MAGANG
                </h2>
                <p class="font-cert-serif text-sm sm:text-base md:text-[18px] font-bold text-black tracking-wide pt-3">
                    Nomor : {{ $regNumber }}
                </p>
            </div>

            <!-- 3. NAMA MAHASISWA & GARIS BAWAH PRESISI DENGAN SUBTITLE -->
            <div class="mt-2 space-y-1.5">
                <div class="inline-block border-b-[3px] border-black pb-2 px-8 max-w-[94%]">
                    @php
                        // Ukuran nama menyesuaikan panjang agar selalu besar & tetap muat satu baris
                        $nameLen = mb_strlen(trim($student->name));
                        $nameSize = $nameLen <= 18 ? 60 : ($nameLen <= 26 ? 54 : ($nameLen <= 32 ? 46 : ($nameLen <= 40 ? 40 : 34)));
                    @endphp
                    <h2 class="font-cert-name text-black tracking-tight leading-none whitespace-nowrap" style="font-size: {{ $nameSize }}px;">
                        {{ $student->name }}
                    </h2>
                </div>
                <p class="font-cert-body text-sm sm:text-base md:text-[19px] font-medium text-black leading-normal pt-1.5">
                    Mahasiswa Program Studi {{ $profile->jurusan ?? 'Semua Jurusan' }}{{ !empty($profile->fakultas) ? ' Fakultas ' . $profile->fakultas : '' }}
                </p>
                <p class="font-cert-body text-sm sm:text-base md:text-[19px] font-medium text-black leading-normal">
                    {{ $profile->universitas ?? ($university->name ?? 'Universitas') }}
                </p>
            </div>

            <!-- 4. NARASI KELULUSAN & PERIODE MAGANG (ITALIC) -->
            <div class="mt-2 space-y-1 max-w-3xl mx-auto">
                <p class="font-cert-body text-sm sm:text-base md:text-[19px] text-black leading-relaxed font-normal">
                    Atas partisipasinya dalam mengikuti kerja magang
                </p>
                <p class="font-cert-body text-sm sm:text-base md:text-[19px] text-black leading-relaxed font-normal">
                    di Dinas Perpustakaan dan Kearsipan Kota Surabaya
                </p>
                <p class="font-cert-body text-sm sm:text-base md:text-[19px] font-normal text-black pt-2">
                    {{ \Carbon\Carbon::parse($application->start_date)->translatedFormat('d F Y') }} s.d. {{ \Carbon\Carbon::parse($application->end_date)->translatedFormat('d F Y') }}
                </p>
            </div>

            <!-- 5. KOTA TANGGAL & KOTAK TTE ELEKTRONIK RESMI STANDAR BSrE -->
            <div class="mt-2 mb-2 sm:mb-3 flex flex-col items-center">
                <p class="font-cert-body text-xs sm:text-sm md:text-[18px] font-bold text-black">
                    {{ $theme->city ?? 'Surabaya' }}, {{ \Carbon\Carbon::parse($placement->certificate?->issued_at ?? now())->translatedFormat('d F Y') }}
                </p>
                <p class="font-cert-body text-xs sm:text-sm md:text-[18px] font-bold text-black mt-0.5">
                    {{ $theme->mandate_text ?? 'a.n. Kepala Dinas Perpustakaan dan Kearsipan Kota Surabaya' }}
                </p>

                <!-- Kotak TTE Resmi Standar BSrE / Kominfo -->
                <div class="mt-2.5 flex items-center gap-4 px-3.5 py-2 bg-white border-[1.5px] border-black text-left font-cert-tte" style="width: 480px; max-width: 95%; height: 108px;">

                    <!-- Sisi Kiri Kotak: QR (zona tenang putih, ECC H, lambang Surabaya kecil di tengah) -->
                    <div class="relative w-[88px] h-[88px] shrink-0 bg-white" style="padding: 3px;">
                        <div class="qr-wrap w-full h-full" aria-label="QR Verifikasi Sertifikat">
                            {!! $qrSvg !!}
                        </div>
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="w-[20px] h-[20px] bg-white rounded-full flex items-center justify-center" style="padding: 2px;">
                                <img src="{{ $surabayaLogoUrl }}?v={{ @filemtime(public_path('images/certificates/logo_surabaya.png')) ?: 1 }}"
                                     alt="SBY"
                                     class="w-full h-full object-contain"
                                     onerror="this.onerror=null; this.src='{{ asset('images/certificates/logo_surabaya.png') }}';">
                            </div>
                        </div>
                    </div>

                    <!-- Sisi Kanan Kotak: Teks Penandatanganan Elektronik -->
                    <div class="leading-snug text-black flex-1 min-w-0">
                        <div class="text-[10px] text-black">Ditandatangani secara elektronik oleh:</div>
                        <div class="text-[11px] text-black">{{ $theme->signee_position ?? 'Sekretaris' }},</div>
                        <div class="mt-1 font-bold text-[14px] text-black tracking-tight">{{ $theme->signee_name ?? 'Dra. NOVELIJA, SH' }}</div>
                        <div class="text-[10px] text-black uppercase">{{ $theme->signee_rank ?? 'PEMBINA TK I' }}</div>
                        <div class="text-[10px] text-black">NIP. {{ $theme->signee_nip ?? '197111141992012001' }}</div>
                        @if (!empty($verifyCertificateUrl))
                            <div class="mt-1 pt-1 border-t border-slate-300 text-[7.5px] leading-tight text-slate-600 font-mono break-all">{{ preg_replace('#^https?://#', '', $verifyCertificateUrl) }}</div>
                        @endif
                    </div>

                </div>
            </div>

        </div>
    </main>

    <!-- ================================================================= -->
    <!-- ================================================================= -->
    <!-- HALAMAN 2: LAMPIRAN TRANSKRIP RINCIAN NILAI KOMPETENSI            -->
    <!-- ================================================================= -->
    @php
        // Tabel selalu terang (apa pun palet temanya): hanya tint pucat dari warna tema + teks gelap.
        $tHead = $pPalette['pale'];
        $tBand = \App\Models\CertificateTheme::adjustBrightness($primaryColor, 94);
        $tLine = $pPalette['lighter'];

        $univ = $eval ? $eval->getUniversity() : ($university ?? null);
        if (! $univ && isset($student)) {
            $univ = app(\App\Services\UniversityResolver::class)->forUser($student);
        }
        $scheme = $univ->evaluation_scheme ?? 'mentor_only';
        $weightMentor = $univ ? (int) $univ->weight_mentor : 100;
        $weightLecturer = $univ ? (int) $univ->weight_lecturer : 0;

        $isMentorOnly = ($scheme === 'mentor_only' || $weightLecturer === 0);
        $isLecturerOnly = ($weightMentor === 0 && $weightLecturer > 0);

        $mentorComponents = $eval ? $eval->mentor_aspects_list : [];
        if (empty($mentorComponents) && $eval) {
            $mentorComponents = $eval->getMentorComponents();
        }
        $lecturerComponents = $eval ? $eval->dosen_aspects_list : [];
        if (empty($lecturerComponents) && $eval) {
            $lecturerComponents = $eval->getLecturerComponents();
        }

        // Kepadatan tabel menyesuaikan jumlah aspek agar selalu muat satu halaman
        $rowCount = (! $isLecturerOnly ? count($mentorComponents) : 0) + (! $isMentorOnly ? count($lecturerComponents) : 0);
        [$cellPad, $cellText] = $rowCount <= 5 ? ['py-3.5', 'text-[13px]'] : ($rowCount <= 9 ? ['py-2.5', 'text-[12px]'] : ['py-1.5', 'text-[10.5px]']);

        $fmtScore = function ($v) {
            return isset($v) && is_numeric($v) ? ((float) $v == (int) $v ? (int) $v : number_format((float) $v, 1)) : ($v ?? '-');
        };
    @endphp

    <section class="certificate-sheet page-break px-12 pt-8 pb-7 relative flex flex-col bg-white select-none">

        <!-- Bingkai & ornamen sudut halaman 2 (selaras dengan halaman 1, versi kecil) -->
        <svg viewBox="0 0 297 210" class="absolute inset-0 w-full h-full pointer-events-none z-0" xmlns="http://www.w3.org/2000/svg">
            <rect x="0" y="0" width="297" height="2.6" fill="{{ $pDarkest }}" />
            <rect x="0" y="207.6" width="297" height="2.4" fill="{{ $accentColor }}" />
            @include('certificates.partials.corner-ornaments', ['showWaves' => false, 'scale' => 0.4])
        </svg>

        <!-- 1. KOP LAMPIRAN -->
        <div class="relative z-10 px-8">
            <div class="flex items-center justify-between pb-3">
                <div class="flex items-center gap-3">
                    <img src="{{ $surabayaLogoUrl }}" alt="Pemerintah Kota Surabaya" class="h-11 w-auto object-contain"
                         onerror="this.onerror=null; this.src='{{ asset('images/certificates/logo_surabaya.png') }}';">
                    <img src="{{ $dispusipLogoUrl }}" alt="Dinas Perpustakaan dan Kearsipan" class="h-11 w-auto object-contain"
                         onerror="this.onerror=null; this.src='{{ asset('images/logos/dispusip.png') }}';">
                    <div class="leading-tight">
                        <div class="font-bold text-[10px] uppercase tracking-[0.14em] text-slate-500">Pemerintah Kota Surabaya</div>
                        <div class="font-black text-[15px] text-slate-900 uppercase">Dinas Perpustakaan dan Kearsipan</div>
                    </div>
                </div>

                <div class="text-right leading-tight">
                    <div class="font-black text-[15px] uppercase tracking-[0.1em] text-slate-900">Lampiran Transkrip Nilai Magang</div>
                    <div class="font-mono text-[11px] text-slate-700 font-bold mt-1">Nomor : {{ $regNumber }}</div>
                    <div class="text-[10px] text-slate-500 font-semibold">Halaman 2 dari 2</div>
                </div>
            </div>
            <div class="h-[3px] rounded-full" style="background: linear-gradient(90deg, {{ $primaryColor }}, {{ $secondaryColor }});"></div>
        </div>

        <!-- 2. ISI: BIODATA + TABEL NILAI (di tengah ruang tersisa agar komposisi seimbang) -->
        <div class="relative z-10 flex-1 flex flex-col justify-center gap-5 px-8 py-4">

            <!-- Biodata -->
            <div class="grid grid-cols-2 gap-x-10 rounded-2xl px-6 py-4 text-[13px]" style="background-color: {{ $tBand }}; border: 1px solid {{ $tLine }};">
                <table class="w-full">
                    <tr>
                        <td class="w-36 text-slate-500 py-1">Nama Mahasiswa</td>
                        <td class="w-3 text-slate-400 py-1">:</td>
                        <td class="font-bold text-slate-900 py-1">{{ $student->name }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 py-1">NIM</td>
                        <td class="text-slate-400 py-1">:</td>
                        <td class="font-bold text-slate-900 py-1">{{ $profile->nim ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 py-1">Program Studi</td>
                        <td class="text-slate-400 py-1">:</td>
                        <td class="text-slate-800 py-1">{{ $profile->jurusan ?? 'Semua Jurusan' }}</td>
                    </tr>
                </table>
                <table class="w-full">
                    <tr>
                        <td class="w-36 text-slate-500 py-1">Universitas Asal</td>
                        <td class="w-3 text-slate-400 py-1">:</td>
                        <td class="font-bold text-slate-900 py-1">{{ $profile->universitas ?? ($university->name ?? '-') }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 py-1">Unit Kerja Magang</td>
                        <td class="text-slate-400 py-1">:</td>
                        <td class="text-slate-800 py-1">{{ $application->unit->name ?? 'Dinas Perpustakaan dan Kearsipan' }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 py-1">Periode Magang</td>
                        <td class="text-slate-400 py-1">:</td>
                        <td class="text-slate-800 py-1">{{ \Carbon\Carbon::parse($application->start_date)->translatedFormat('d/m/Y') }} s.d. {{ \Carbon\Carbon::parse($application->end_date)->translatedFormat('d/m/Y') }}</td>
                    </tr>
                </table>
            </div>

            <!-- Tabel nilai (selalu terang) -->
            <div class="overflow-hidden rounded-2xl bg-white" style="border: 1px solid {{ $tLine }};">
                <table class="w-full text-left {{ $cellText }} text-slate-800" style="border-collapse: collapse;">
                    <thead>
                        <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-800" style="background-color: {{ $tHead }};">
                            <th class="py-3 px-4 text-center w-12">No</th>
                            <th class="py-3 px-4">Komponen Penilaian &amp; Aspek Kompetensi Magang</th>
                            <th class="py-3 px-4 text-center w-24">Bobot</th>
                            <th class="py-3 px-4 text-center w-28">Skor (0-100)</th>
                            <th class="py-3 px-4 text-center w-32">Skor Tertimbang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (! $isLecturerOnly)
                            <tr style="background-color: {{ $tBand }}; border-top: 1px solid {{ $tLine }};">
                                <td class="py-2 px-4 text-center font-black text-slate-900">A</td>
                                <td colspan="4" class="py-2 px-4 font-bold uppercase text-[11px] tracking-wide text-slate-900">
                                    Penilaian Pembimbing Lapangan Dinas (Dinas Perpustakaan dan Kearsipan)
                                </td>
                            </tr>
                            @foreach ($mentorComponents as $idx => $mComp)
                                <tr style="border-top: 1px solid #e2e8f0;">
                                    <td class="{{ $cellPad }} px-4 text-center text-slate-400">{{ $idx + 1 }}</td>
                                    <td class="{{ $cellPad }} px-4 font-medium">{{ $mComp['name'] ?? $mComp['label'] ?? ('Aspek ' . ($idx + 1)) }}</td>
                                    <td class="{{ $cellPad }} px-4"></td>
                                    <td class="{{ $cellPad }} px-4 text-center font-bold">{{ $fmtScore($mComp['score'] ?? null) }}</td>
                                    <td class="{{ $cellPad }} px-4"></td>
                                </tr>
                            @endforeach
                            <tr class="font-bold" style="background-color: #f8fafc; border-top: 1px solid {{ $tLine }};">
                                <td colspan="2" class="py-2.5 px-4 text-right text-slate-700">Subtotal Nilai Dinas (Rata-rata):</td>
                                <td class="py-2.5 px-4 text-center">{{ $isMentorOnly ? '100%' : ($weightMentor . '%') }}</td>
                                <td class="py-2.5 px-4 text-center">{{ $eval?->nilai_pembimbing ?? 0 }}</td>
                                <td class="py-2.5 px-4 text-center font-black text-slate-900">
                                    {{ $isMentorOnly ? ($eval?->nilai_pembimbing ?? 0) : round(($weightMentor / 100) * ($eval?->nilai_pembimbing ?? 0), 2) }}
                                </td>
                            </tr>
                        @endif

                        @if (! $isMentorOnly)
                            <tr style="background-color: {{ $tBand }}; border-top: 1px solid {{ $tLine }};">
                                <td class="py-2 px-4 text-center font-black text-slate-900">{{ $isLecturerOnly ? 'A' : 'B' }}</td>
                                <td colspan="4" class="py-2 px-4 font-bold uppercase text-[11px] tracking-wide text-slate-900">
                                    Penilaian Akademik Dosen Pembimbing Lapangan (DPL Kampus)
                                </td>
                            </tr>
                            @foreach ($lecturerComponents as $idx => $lComp)
                                <tr style="border-top: 1px solid #e2e8f0;">
                                    <td class="{{ $cellPad }} px-4 text-center text-slate-400">{{ $idx + 1 }}</td>
                                    <td class="{{ $cellPad }} px-4 font-medium">{{ $lComp['name'] ?? $lComp['label'] ?? ('Aspek ' . ($idx + 1)) }}</td>
                                    <td class="{{ $cellPad }} px-4"></td>
                                    <td class="{{ $cellPad }} px-4 text-center font-bold">{{ $fmtScore($lComp['score'] ?? null) }}</td>
                                    <td class="{{ $cellPad }} px-4"></td>
                                </tr>
                            @endforeach
                            <tr class="font-bold" style="background-color: #f8fafc; border-top: 1px solid {{ $tLine }};">
                                <td colspan="2" class="py-2.5 px-4 text-right text-slate-700">Subtotal Nilai DPL (Rata-rata):</td>
                                <td class="py-2.5 px-4 text-center">{{ $isLecturerOnly ? '100%' : ($weightLecturer . '%') }}</td>
                                <td class="py-2.5 px-4 text-center">{{ $eval?->nilai_dosen_calculated ?? 0 }}</td>
                                <td class="py-2.5 px-4 text-center font-black text-slate-900">
                                    {{ $isLecturerOnly ? ($eval?->nilai_dosen_calculated ?? 0) : round(($weightLecturer / 100) * ($eval?->nilai_dosen_calculated ?? 0), 2) }}
                                </td>
                            </tr>
                        @endif

                        <!-- Nilai akhir: formal, satu warna teks (predikat sudah dijelaskan di keterangan skala) -->
                        <tr class="font-black text-slate-900" style="background-color: {{ $tHead }}; border-top: 2px solid {{ $primaryColor }};">
                            <td colspan="2" class="py-3.5 px-4 text-right uppercase tracking-wider text-[13px]">Nilai Akhir Total &amp; Mutu:</td>
                            <td class="py-3.5 px-4 text-center">100%</td>
                            <td class="py-3.5 px-4 text-center text-[15px]">{{ $eval?->nilai_akhir ?? 0 }}</td>
                            <td class="py-3.5 px-4 text-center uppercase tracking-wide">Grade {{ $simpleGrade }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. KETERANGAN SKALA NILAI & PERNYATAAN KEABSAHAN -->
        <div class="relative z-10 px-8 pt-3 flex items-end justify-between gap-8" style="border-top: 1px solid {{ $tLine }};">
            <div>
                <p class="font-bold text-[10px] uppercase tracking-wider text-slate-700">Keterangan Skala Predikat &amp; Mutu Nilai:</p>
                <p class="text-[10.5px] text-slate-600 mt-0.5">
                    <strong>A (85 - 100)</strong> : Sangat Memuaskan &bull;
                    <strong>B (70 - 84)</strong> : Memuaskan &bull;
                    <strong>C (55 - 69)</strong> : Cukup
                </p>
            </div>
            <div class="text-right text-[10px] text-slate-500 max-w-md leading-snug">
                <p class="font-semibold text-slate-700">
                    Lampiran transkrip nilai ini merupakan satu kesatuan sah dan tak terpisahkan dari Sertifikat Magang Nomor: <span class="font-bold text-slate-900">{{ $regNumber }}</span>.
                </p>
                <p class="text-[9px] text-slate-400 mt-0.5">Telah disahkan dan terverifikasi secara elektronik melalui Tanda Tangan Elektronik (TTE) pada lembar utama.</p>
            </div>
        </div>
    </section>
