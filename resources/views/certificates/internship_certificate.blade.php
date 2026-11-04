<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Magang — {{ $student->name }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alfa+Slab+One&family=Roboto+Slab:wght@700;800;900&family=Cinzel:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;0,800;0,900;1,500;1,600&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>

    @php
        \Carbon\Carbon::setLocale('id');
        setlocale(LC_TIME, 'id_ID.utf8', 'indonesian');

        $theme = $theme ?? \App\Models\CertificateTheme::getActive();
        $primaryColor = $theme->primary_color ?? '#00A3C4';
        $secondaryColor = $theme->secondary_color ?? '#F36F21';
        $accentColor = $theme->accent_color ?? '#D97706';

        $pPalette = method_exists($theme, 'getPrimaryPalette') ? $theme->getPrimaryPalette() : [
            'darkest' => '#00587a', 'darker' => '#007ca5', 'dark' => '#0097c9',
            'base' => $primaryColor, 'medium' => '#40c4e4', 'light' => '#80daf0',
            'lighter' => '#b8edf8', 'pale' => '#e0f6fc'
        ];
        $sPalette = method_exists($theme, 'getSecondaryPalette') ? $theme->getSecondaryPalette() : [
            'darkest' => '#b84a00', 'darker' => '#d95700', 'dark' => '#f26200',
            'base' => $secondaryColor, 'medium' => '#ff8d38', 'light' => '#ffb37a',
            'lighter' => '#ffd5b8', 'pale' => '#ffece0'
        ];

        $pDarkest = $pPalette['darkest'];
        $pDarker  = $pPalette['darker'];
        $pDark    = $pPalette['dark'];
        $pBase    = $pPalette['base'];
        $pMedium  = $pPalette['medium'];
        $pLight   = $pPalette['light'];

        $sDarkest = $sPalette['darkest'];
        $sDarker  = $sPalette['darker'];
        $sDark    = $sPalette['dark'];
        $sBase    = $sPalette['base'];
        $sMedium  = $sPalette['medium'];
        $sLight   = $sPalette['light'];
    @endphp

    <style>
        :root {
            --theme-primary: {{ $primaryColor }};
            --theme-secondary: {{ $secondaryColor }};
            --theme-accent: {{ $accentColor }};
        }

        @page {
            size: A4 landscape;
            margin: 0;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            background-color: #0f172a;
        }

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

        .certificate-sheet {
            width: 297mm;
            height: 210mm;
            max-height: 210mm;
            margin: 0 auto;
            background: #ffffff;
            box-sizing: border-box;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        @media screen {
            .certificate-sheet {
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                margin-top: 24px;
                margin-bottom: 24px;
            }
        }

        @media print {
            body {
                background: none;
            }
            .no-print {
                display: none !important;
            }
            .certificate-sheet {
                box-shadow: none;
                margin: 0;
                page-break-after: always;
            }
            .page-break {
                page-break-before: always;
            }
            .sheet-scale-wrap {
                height: auto !important;
                width: auto !important;
            }
            .sheet-scale-wrap > .certificate-sheet {
                transform: none !important;
            }
        }

        @media screen {
            .sheet-scale-wrap {
                margin: 0 auto;
                overflow: hidden;
            }
            .sheet-scale-wrap > .certificate-sheet {
                transform-origin: top left;
            }
        }

        .mobile-hint { display: none; }
        @media screen and (max-width: 767px) {
            .mobile-hint { display: block; }
        }
    </style>
</head>
<body class="text-slate-900 antialiased selection:bg-blue-600 selection:text-white">

    @php
        $backUrl = route('dashboard');
        $backLabel = 'Kembali ke Dashboard';
        if (Auth::user()?->role === 'admin' || Auth::user()?->role === 'super_admin') {
            $backUrl = route('admin.certificates.index');
            $backLabel = 'Kembali ke Daftar Sertifikat';
        } elseif (Auth::user()?->role === 'supervisor') {
            $backUrl = route('supervisor.certificates.index');
            $backLabel = 'Kembali ke Pengesahan Sertifikat';
        } elseif (Auth::user()?->role === 'dosen') {
            $backUrl = route('lecturer.dashboard');
            $backLabel = 'Kembali ke Dashboard Dosen';
        } elseif (Auth::user()?->role === 'pembimbing') {
            $backUrl = route('mentor.dashboard');
            $backLabel = 'Kembali ke Dashboard Mentor';
        } elseif (Auth::user()?->role === 'universitas') {
            $backUrl = route('university.dashboard');
            $backLabel = 'Kembali ke Dashboard Kampus';
        }
    @endphp

    {{-- Top Action Bar (Hidden on Print) --}}
    <header class="no-print sticky top-0 z-50 bg-slate-900/95 backdrop-blur-md border-b border-slate-800 text-white px-3 sm:px-6 py-3 flex items-center justify-between gap-2 shadow-xl">
        <div class="flex items-center gap-3">
            <a href="{{ $backUrl }}" onclick="if(window.opener || window.history.length > 1){ if(window.opener){ window.close(); return false; } else { window.history.back(); return false; } }" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white rounded-xl text-xs font-bold transition border border-slate-700 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span class="hidden sm:inline">{{ $backLabel }}</span><span class="sm:hidden">Kembali</span>
            </a>
            <div class="hidden sm:block text-xs text-slate-400 border-l border-slate-700 pl-3">
                <span>Dokumen Resmi: </span>
                <strong class="text-slate-200">{{ $regNumber }}</strong>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-black shadow-lg shadow-blue-600/30 transition transform active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span class="hidden sm:inline">Cetak / Simpan PDF (A4 Landscape)</span><span class="sm:hidden">Cetak / PDF</span>
            </button>
        </div>
    </header>
    <p class="no-print mobile-hint text-center text-[11px] text-slate-400 px-4 pt-3">
        Pratinjau diperkecil agar muat di layar. Gunakan tombol <strong class="text-slate-200">Cetak / PDF</strong> untuk hasil ukuran A4 penuh.
    </p>

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
                    <thead class="text-white font-bold uppercase text-[9px] tracking-wider" style="background-color: var(--theme-primary, #0f172a);">
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

    {{-- Skala otomatis agar lembar A4 landscape muat di layar HP/tablet (tampilan cetak tidak berubah) --}}
    <script>
        (function () {
            var sheets = Array.prototype.slice.call(document.querySelectorAll('.certificate-sheet'));
            sheets.forEach(function (sheet) {
                var wrap = document.createElement('div');
                wrap.className = 'sheet-scale-wrap';
                sheet.parentNode.insertBefore(wrap, sheet);
                wrap.appendChild(sheet);
            });

            function fit() {
                var available = document.documentElement.clientWidth - 16;
                sheets.forEach(function (sheet) {
                    var wrap = sheet.parentNode;
                    sheet.style.transform = 'none';
                    var w = sheet.offsetWidth, h = sheet.offsetHeight;
                    var styles = getComputedStyle(sheet);
                    var mt = parseFloat(styles.marginTop) || 0, mb = parseFloat(styles.marginBottom) || 0;
                    var scale = Math.min(1, available / w);
                    sheet.style.transform = scale < 1 ? 'scale(' + scale + ')' : 'none';
                    sheet.style.marginLeft = scale < 1 ? '0' : '';
                    wrap.style.width = scale < 1 ? (w * scale) + 'px' : '';
                    wrap.style.height = scale < 1 ? (h * scale + (mt + mb) * scale) + 'px' : '';
                });
            }

            fit();
            window.addEventListener('resize', fit);
            window.addEventListener('beforeprint', function () {
                sheets.forEach(function (s) { s.style.transform = 'none'; s.parentNode.style.height = ''; s.parentNode.style.width = ''; });
            });
            window.addEventListener('afterprint', fit);
        })();
    </script>
</body>
</html>
