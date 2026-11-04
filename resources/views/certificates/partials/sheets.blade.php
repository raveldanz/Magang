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
        $dispusipLogoUrl = asset('images/certificates/logo_dispusip.png');
        $berakhlakLogoUrl = asset('images/certificates/logo_berakhlak.png');
        $banggaLogoUrl = asset('images/certificates/logo_bangga_melayani.png');
        $zonaLogoUrl = asset('images/certificates/logo_zona_integritas.png');

        // Simple Grade untuk Halaman 2
        $simpleGrade = $eval->grade_calculated ?? ($eval->grade ?? 'A');
        $predikat = ['A' => 'Sangat Memuaskan', 'B' => 'Memuaskan', 'C' => 'Cukup'][strtoupper((string) $simpleGrade)] ?? 'Kurang';

        // Ringkasan kegiatan untuk lampiran (halaman 2) — dari data yang sudah ada
        $hariMagang = 0;
        try {
            $hariMagang = \Carbon\Carbon::parse($application->start_date)->startOfDay()->diffInDays(\Carbon\Carbon::parse($application->end_date)->startOfDay()) + 1;
        } catch (\Throwable $e) {
            $hariMagang = 0;
        }
        $mingguMagang = $hariMagang > 0 ? (int) ceil($hariMagang / 7) : 0;
        $logTotal = $placement ? $placement->logbooks()->count() : 0;
        $logApproved = $placement ? $placement->logbooks()->where('status', 'approved')->count() : 0;
        $laporanRaw = isset($finalReport) && $finalReport ? $finalReport->status : null;
        $laporanVal = $laporanRaw instanceof \BackedEnum ? $laporanRaw->value : $laporanRaw;
        $laporanLabel = $laporanVal ? (\App\Enums\ReviewStatus::tryFrom((string) $laporanVal)?->description() ?? 'Belum Ada') : 'Belum Ada';
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
        <div class="relative z-10 flex flex-col justify-between h-full pt-4 pb-10 sm:pb-12 md:pb-14 px-8 sm:px-12 text-center">
            
            <!-- 1. HEADER KOP 5 LOGO RESMI SEJAJAR HORIZONTAL (ULTRA-HD TRANSPARAN PRESISI) -->
            <div class="pt-2 flex items-center justify-center">
                <img src="{{ $officialLogosStripUrl }}?v={{ @filemtime(public_path('images/certificates/logos_official_strip.png')) ?: 1 }}" 
                     alt="Pemerintah Kota Surabaya, Dispusip, BerAKHLAK, Bangga Melayani Bangsa, Zona Integritas" 
                     class="h-11 sm:h-12 md:h-[56px] w-auto max-w-[88%] object-contain"
                     onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'flex items-center justify-center gap-6 sm:gap-7\'><img src=\'{{ $surabayaLogoUrl }}\' class=\'h-11 sm:h-12 w-auto object-contain\'><img src=\'{{ $dispusipLogoUrl }}\' class=\'h-11 sm:h-12 w-auto object-contain\'><img src=\'{{ $berakhlakLogoUrl }}\' class=\'h-9 sm:h-10 w-auto object-contain\'><img src=\'{{ $banggaLogoUrl }}\' class=\'h-8 sm:h-9 w-auto object-contain\'><img src=\'{{ $zonaLogoUrl }}\' class=\'h-9 sm:h-10 w-auto object-contain\'></div>';">
            </div>

            <!-- 2. JUDUL SERTIFIKAT & NOMOR REGISTRASI RESMI -->
            <div class="mt-2 space-y-1">
                <h1 class="font-cert-title text-4xl sm:text-5xl md:text-[64px] text-black tracking-[0.03em] uppercase leading-none">
                    SERTIFIKAT
                </h1>
                <h2 class="font-cert-serif text-3xl sm:text-4xl md:text-[42px] font-normal text-black tracking-[0.06em] uppercase leading-none pt-2">
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
                        $nameSize = $nameLen <= 18 ? 66 : ($nameLen <= 26 ? 58 : ($nameLen <= 32 ? 49 : ($nameLen <= 40 ? 42 : 36)));
                    @endphp
                    <h2 class="font-cert-name text-black tracking-tight leading-none whitespace-nowrap" style="font-size: {{ $nameSize }}px;">
                        {{ $student->name }}
                    </h2>
                </div>
                <p class="font-cert-body text-sm sm:text-base md:text-[20px] font-medium text-black leading-normal pt-1.5">
                    Mahasiswa Program Studi {{ $profile->jurusan ?? 'Semua Jurusan' }}{{ !empty($profile->fakultas) ? ' Fakultas ' . $profile->fakultas : '' }}
                </p>
                <p class="font-cert-body text-sm sm:text-base md:text-[20px] font-medium text-black leading-normal">
                    {{ $profile->universitas ?? ($university->name ?? 'Universitas') }}
                </p>
            </div>

            <!-- 4. NARASI KELULUSAN & PERIODE MAGANG (ITALIC) -->
            <div class="mt-2 space-y-1 max-w-3xl mx-auto">
                <p class="font-cert-body text-sm sm:text-base md:text-[20px] text-black leading-relaxed font-normal">
                    Atas partisipasinya dalam mengikuti kerja magang
                </p>
                <p class="font-cert-body text-sm sm:text-base md:text-[20px] text-black leading-relaxed font-normal">
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
                                     onerror="this.onerror=null; this.src='{{ asset('images/certificates/logo_surabaya_lambang.jpeg') }}';">
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
    <!-- HALAMAN 2: LAMPIRAN TRANSKRIP RINCIAN NILAI KOMPETENSI            -->
    <!-- ================================================================= -->
    <section class="certificate-sheet page-break px-12 pt-7 pb-6 relative flex flex-col justify-between bg-white select-none">

        <!-- Bingkai & ornamen sudut halaman 2 (selaras dengan halaman 1, versi kecil) -->
        <svg viewBox="0 0 297 210" class="absolute inset-0 w-full h-full pointer-events-none z-0" xmlns="http://www.w3.org/2000/svg">
            <rect x="0" y="0" width="297" height="2.6" fill="{{ $pDarkest }}" />
            <rect x="0" y="207.6" width="297" height="2.4" fill="{{ $accentColor }}" />
            @include('certificates.partials.corner-ornaments', ['showWaves' => false, 'scale' => 0.4])
        </svg>

        <!-- Bagian Atas: KOP, BIODATA, DAN TABEL TRANSKRIP -->
        <div class="relative z-10 px-4 pt-0.5">
            
            <!-- 1. HEADER TRANSKRIP NILAI DENGAN LOGO RESMI DISPUSIP & SURABAYA -->
            <div class="flex items-center justify-between border-b-2 border-slate-800 pb-2">
                <div class="flex items-center gap-3">
                    <img src="{{ $surabayaLogoUrl }}" 
                         alt="Pemerintah Kota Surabaya" 
                         class="w-9 h-9 object-contain"
                         onerror="this.onerror=null; this.src='{{ asset('images/certificates/logo_surabaya_lambang.jpeg') }}';">
                    <img src="{{ $dispusipLogoUrl }}" 
                         alt="Dinas Perpustakaan dan Kearsipan" 
                         class="w-9 h-9 object-contain"
                         onerror="this.onerror=null; this.src='{{ asset('images/logos/dispusip.png') }}';">
                    <div>
                        <h3 class="font-bold text-[9px] uppercase tracking-wider text-slate-500">
                            Pemerintah Kota Surabaya
                        </h3>
                        <h2 class="font-black text-xs text-slate-900 uppercase">
                            Dinas Perpustakaan dan Kearsipan
                        </h2>
                    </div>
                </div>

                <div class="text-right">
                    <h1 class="font-black text-xs uppercase tracking-wider text-slate-900">
                        Lampiran Transkrip Nilai Magang
                    </h1>
                    <p class="font-mono text-[9.5px] text-slate-600 font-bold">
                        Nomor : {{ $regNumber }}
                    </p>
                    <p class="text-[8.5px] text-slate-400 font-semibold">Halaman 2 dari 2</p>
                </div>
            </div>

            <!-- 2. DATA MAHASISWA & PENEMPATAN (TABEL RAPI DENGAN KOLOM TITIK DUA TERSEJAJARKAN) -->
            <div class="grid grid-cols-2 gap-4 mt-2 p-2 bg-slate-50 rounded-xl border border-slate-200 text-xs">
                <table class="w-full text-xs">
                    <tr>
                        <td class="w-28 text-slate-500 font-medium py-0.5">Nama Mahasiswa</td>
                        <td class="w-3 text-slate-400 py-0.5">:</td>
                        <td class="font-bold text-slate-900 py-0.5">{{ $student->name }}</td>
                    </tr>
                    <tr>
                        <td class="w-28 text-slate-500 font-medium py-0.5">NIM</td>
                        <td class="w-3 text-slate-400 py-0.5">:</td>
                        <td class="font-bold text-slate-900 py-0.5">{{ $profile->nim ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="w-28 text-slate-500 font-medium py-0.5">Program Studi</td>
                        <td class="w-3 text-slate-400 py-0.5">:</td>
                        <td class="font-medium text-slate-800 py-0.5">{{ $profile->jurusan ?? 'Semua Jurusan' }}</td>
                    </tr>
                </table>
                <table class="w-full text-xs">
                    <tr>
                        <td class="w-28 text-slate-500 font-medium py-0.5">Universitas Asal</td>
                        <td class="w-3 text-slate-400 py-0.5">:</td>
                        <td class="font-bold text-slate-900 py-0.5">{{ $profile->universitas ?? ($university->name ?? '-') }}</td>
                    </tr>
                    <tr>
                        <td class="w-28 text-slate-500 font-medium py-0.5">Unit Kerja Magang</td>
                        <td class="w-3 text-slate-400 py-0.5">:</td>
                        <td class="font-medium text-slate-800 py-0.5">{{ $application->unit->name ?? 'Dinas Perpustakaan dan Kearsipan' }}</td>
                    </tr>
                    <tr>
                        <td class="w-28 text-slate-500 font-medium py-0.5">Periode Magang</td>
                        <td class="w-3 text-slate-400 py-0.5">:</td>
                        <td class="font-medium text-slate-800 py-0.5">{{ \Carbon\Carbon::parse($application->start_date)->translatedFormat('d/m/Y') }} s.d. {{ \Carbon\Carbon::parse($application->end_date)->translatedFormat('d/m/Y') }}</td>
                    </tr>
                </table>
            </div>

            <!-- 3. TABEL TRANSKRIP RINCIAN PENILAIAN -->
            <div class="mt-2.5 overflow-hidden rounded-xl border border-slate-300">
                <table class="min-w-full text-left text-[10.5px] divide-y divide-slate-200">
                    <thead class="text-white font-bold uppercase text-[9px] tracking-wider" style="background-color: {{ $primaryColor }};">
                        <tr>
                            <th class="py-1.5 px-3 text-center w-10">No</th>
                            <th class="py-1.5 px-3">Komponen Penilaian & Aspek Kompetensi Magang</th>
                            <th class="py-1.5 px-3 text-center w-20">Bobot (%)</th>
                            <th class="py-1.5 px-3 text-center w-24">Skor (0-100)</th>
                            <th class="py-1.5 px-3 text-center w-28">Skor Tertimbang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        
@php
    $univ = $eval ? $eval->getUniversity() : ($university ?? null);
    if (! $univ && isset($student)) {
        $univ = app(\App\Services\UniversityResolver::class)->forUser($student);
    }
    $scheme = $univ->evaluation_scheme ?? 'mentor_only';
    $weightMentor = $univ ? (int) $univ->weight_mentor : 100;
    $weightLecturer = $univ ? (int) $univ->weight_lecturer : 0;

    $isMentorOnly = ($scheme === 'mentor_only' || $weightLecturer === 0);
    $isLecturerOnly = ($weightMentor === 0 && $weightLecturer > 0);
    $isDual = ! $isMentorOnly && ! $isLecturerOnly;

    $mentorComponents = $eval ? $eval->mentor_aspects_list : [];
    if (empty($mentorComponents) && $eval) {
        $mentorComponents = $eval->getMentorComponents();
    }

    $lecturerComponents = $eval ? $eval->dosen_aspects_list : [];
    if (empty($lecturerComponents) && $eval) {
        $lecturerComponents = $eval->getLecturerComponents();
    }
@endphp

                        @if (! $isLecturerOnly)
                            <!-- ASPEK I: KINERJA LAPANGAN DINAS -->
                            <tr class="bg-slate-100/90 font-bold text-slate-900">
                                <td class="py-1.5 px-3 text-center font-bold">A</td>
                                <td colspan="4" class="py-1.5 px-3 uppercase text-[10px] text-blue-900">
                                    Penilaian Pembimbing Lapangan Dinas (Dinas Perpustakaan dan Kearsipan)
                                </td>
                            </tr>
                            @foreach ($mentorComponents as $idx => $mComp)
                                <tr>
                                    <td class="py-1.5 px-3 text-center text-slate-400">{{ $idx + 1 }}</td>
                                    <td class="py-1.5 px-3 font-medium">{{ $mComp['name'] ?? $mComp['label'] ?? ('Aspek ' . ($idx + 1)) }}</td>
                                    <td class="py-1.5 px-3"></td>
                                    <td class="py-1.5 px-3 text-center font-bold">{{ isset($mComp['score']) && is_numeric($mComp['score']) ? ((float)$mComp['score'] == (int)$mComp['score'] ? (int)$mComp['score'] : number_format((float)$mComp['score'], 1)) : ($mComp['score'] ?? '-') }}</td>
                                    <td class="py-1.5 px-3"></td>
                                </tr>
                            @endforeach
                            <tr class="bg-blue-50/70 font-bold">
                                <td colspan="2" class="py-2 px-3 text-right text-blue-950">Subtotal Nilai Dinas (Rata-rata):</td>
                                <td class="py-2 px-3 text-center text-blue-900">{{ $isMentorOnly ? '100%' : ($weightMentor . '%') }}</td>
                                <td class="py-2 px-3 text-center text-blue-900">{{ $eval?->nilai_pembimbing ?? 0 }}</td>
                                <td class="py-2 px-3 text-center text-blue-950 font-black">
                                    {{ $isMentorOnly ? ($eval?->nilai_pembimbing ?? 0) : round(($weightMentor / 100) * ($eval?->nilai_pembimbing ?? 0), 2) }}
                                </td>
                            </tr>
                        @endif

                        @if (! $isMentorOnly)
                            <!-- ASPEK II: AKADEMIK DPL KAMPUS -->
                            <tr class="bg-slate-100/90 font-bold text-slate-900">
                                <td class="py-1.5 px-3 text-center font-bold">{{ $isLecturerOnly ? 'A' : 'B' }}</td>
                                <td colspan="4" class="py-1.5 px-3 uppercase text-[10px] text-blue-900">
                                    Penilaian Akademik Dosen Pembimbing Lapangan (DPL Kampus)
                                </td>
                            </tr>
                            @foreach ($lecturerComponents as $idx => $lComp)
                                <tr>
                                    <td class="py-1.5 px-3 text-center text-slate-400">{{ $idx + 1 }}</td>
                                    <td class="py-1.5 px-3 font-medium">{{ $lComp['name'] ?? $lComp['label'] ?? ('Aspek ' . ($idx + 1)) }}</td>
                                    <td class="py-1.5 px-3"></td>
                                    <td class="py-1.5 px-3 text-center font-bold">{{ isset($lComp['score']) && is_numeric($lComp['score']) ? ((float)$lComp['score'] == (int)$lComp['score'] ? (int)$lComp['score'] : number_format((float)$lComp['score'], 1)) : ($lComp['score'] ?? '-') }}</td>
                                    <td class="py-1.5 px-3"></td>
                                </tr>
                            @endforeach
                            <tr class="bg-blue-50/70 font-bold">
                                <td colspan="2" class="py-2 px-3 text-right text-blue-950">Subtotal Nilai DPL (Rata-rata):</td>
                                <td class="py-2 px-3 text-center text-blue-900">{{ $isLecturerOnly ? '100%' : ($weightLecturer . '%') }}</td>
                                <td class="py-2 px-3 text-center text-blue-900">{{ $eval?->nilai_dosen_calculated ?? 0 }}</td>
                                <td class="py-2 px-3 text-center text-blue-950 font-black">
                                    {{ $isLecturerOnly ? ($eval?->nilai_dosen_calculated ?? 0) : round(($weightLecturer / 100) * ($eval?->nilai_dosen_calculated ?? 0), 2) }}
                                </td>
                            </tr>
                        @endif

                        <!-- REKAPITULASI TOTAL NILAI AKHIR -->
                        <tr class="bg-slate-900 text-white font-black text-xs">
                            <td colspan="2" class="py-2.5 px-3 text-right uppercase tracking-wider">
                                Nilai Akhir Total & Mutu:
                            </td>
                            <td class="py-2.5 px-3 text-center">100%</td>
                            <td class="py-2.5 px-3 text-center text-emerald-400 text-sm">
                                {{ $eval?->nilai_akhir ?? 0 }}
                            </td>
                            <td class="py-2.5 px-3 text-center text-amber-300 text-sm">
                                GRADE {{ $simpleGrade }} <span class="block text-[8.5px] font-semibold tracking-wide text-amber-200/90 uppercase">{{ $predikat }}</span>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

        </div>

        <!-- 4. FOOTER: RINGKASAN KEGIATAN, SKALA NILAI, DAN TTE (satu kesatuan dengan halaman 1) -->
        <div class="relative z-10 border-t border-slate-200 pt-2.5 flex items-end justify-between gap-6">
            <div class="flex-1 min-w-0 space-y-2">
                <!-- Ringkasan kegiatan magang -->
                <div class="grid grid-cols-3 gap-2 max-w-md">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5">
                        <div class="text-[7.5px] font-bold uppercase tracking-wider text-slate-500">Lama Magang</div>
                        <div class="text-[11px] font-black text-slate-900 leading-tight">{{ $hariMagang }} hari</div>
                        <div class="text-[8px] text-slate-500">&asymp; {{ $mingguMagang }} minggu</div>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5">
                        <div class="text-[7.5px] font-bold uppercase tracking-wider text-slate-500">Logbook Disetujui</div>
                        <div class="text-[11px] font-black text-slate-900 leading-tight">{{ $logApproved }} <span class="text-[8px] font-semibold text-slate-500">dari {{ $logTotal }}</span></div>
                        <div class="text-[8px] text-slate-500">catatan harian</div>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5">
                        <div class="text-[7.5px] font-bold uppercase tracking-wider text-slate-500">Laporan Akhir</div>
                        <div class="text-[11px] font-black text-slate-900 leading-tight">{{ $laporanLabel }}</div>
                        <div class="text-[8px] text-slate-500">status review</div>
                    </div>
                </div>

                <div>
                    <p class="font-bold text-[9px] uppercase tracking-wider text-slate-700">Keterangan Skala Predikat &amp; Mutu Nilai:</p>
                    <p class="text-[9px] text-slate-600">
                        <strong>A (85 - 100)</strong> : Sangat Memuaskan &bull;
                        <strong>B (70 - 84)</strong> : Memuaskan &bull;
                        <strong>C (55 - 69)</strong> : Cukup
                    </p>
                </div>

                <p class="text-[8.5px] text-slate-500 leading-snug max-w-md">
                    Lampiran transkrip nilai ini merupakan satu kesatuan sah dan tak terpisahkan dari Sertifikat Magang Nomor:
                    <span class="font-bold text-slate-800">{{ $regNumber }}</span>.
                </p>
            </div>

            <!-- Blok pengesahan elektronik (QR sama dengan halaman 1) -->
            <div class="shrink-0 flex flex-col items-center">
                <p class="font-cert-body text-[10.5px] font-bold text-black">{{ $theme->city ?? 'Surabaya' }}, {{ \Carbon\Carbon::parse($placement->certificate?->issued_at ?? now())->translatedFormat('d F Y') }}</p>
                <p class="font-cert-body text-[10.5px] font-bold text-black mb-1">{{ $theme->mandate_text ?? 'a.n. Kepala Dinas Perpustakaan dan Kearsipan Kota Surabaya' }}</p>
                <div class="flex items-center gap-3 px-2.5 py-1.5 bg-white border-[1.5px] border-black text-left font-cert-tte" style="width: 340px; height: 82px;">
                    <div class="relative w-[68px] h-[68px] shrink-0 bg-white" style="padding: 2px;">
                        <div class="qr-wrap w-full h-full" aria-label="QR Verifikasi Sertifikat">
                            {!! $qrSvg !!}
                        </div>
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="w-[15px] h-[15px] bg-white rounded-full flex items-center justify-center" style="padding: 1.5px;">
                                <img src="{{ $surabayaLogoUrl }}" alt="SBY" class="w-full h-full object-contain">
                            </div>
                        </div>
                    </div>
                    <div class="leading-snug text-black flex-1 min-w-0">
                        <div class="text-[8.5px]">Ditandatangani secara elektronik oleh:</div>
                        <div class="text-[9.5px]">{{ $theme->signee_position ?? 'Sekretaris' }},</div>
                        <div class="font-bold text-[12px] tracking-tight">{{ $theme->signee_name ?? 'Dra. NOVELIJA, SH' }}</div>
                        <div class="text-[8.5px] uppercase">{{ $theme->signee_rank ?? 'PEMBINA TK I' }} &bull; NIP. {{ $theme->signee_nip ?? '197111141992012001' }}</div>
                    </div>
                </div>
            </div>
        </div>

    </section>
