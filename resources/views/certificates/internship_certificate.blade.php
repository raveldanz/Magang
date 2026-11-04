<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Magang — {{ $student->name }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;0,800;0,900;1,500;1,600&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>

    @php
        $theme = $theme ?? \App\Models\CertificateTheme::getActive();
        $primaryColor = $theme->primary_color ?? '#00A3C4';
        $secondaryColor = $theme->secondary_color ?? '#F36F21';
        $accentColor = $theme->accent_color ?? '#D97706';

        $pPalette = method_exists($theme, 'getPrimaryPalette') ? $theme->getPrimaryPalette() : [
            'darkest' => '#004958', 'darker' => '#006a7f', 'dark' => '#0086a1',
            'base' => $primaryColor, 'medium' => '#40bad3', 'light' => '#80d1e2',
            'lighter' => '#b8e5ee', 'pale' => '#e0f4f8'
        ];
        $sPalette = method_exists($theme, 'getSecondaryPalette') ? $theme->getSecondaryPalette() : [
            'darkest' => '#6d320f', 'darker' => '#9e4815', 'dark' => '#c75b1b',
            'base' => $secondaryColor, 'medium' => '#f69359', 'light' => '#f9b790',
            'lighter' => '#fcd7c1', 'pale' => '#feeee4'
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

        .font-cert-name {
            font-family: 'Playfair Display', 'Times New Roman', Times, Georgia, serif;
        }

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
        // Logo Utama Dinas & Kota Surabaya
        $surabayaLogoUrl = asset('images/certificates/logo_surabaya.webp');
        $dispusipLogoUrl = asset('images/certificates/logo_dispusip.jpg');
        $berakhlakLogoUrl = asset('images/certificates/logo_berakhlak.png');
        $banggaLogoUrl = asset('images/certificates/logo_bangga_melayani.png');
        $zonaLogoUrl = asset('images/certificates/logo_zona_integritas.png');

        // Simple Grade untuk Halaman 2
        $simpleGrade = $eval->grade_calculated ?? ($eval->grade ?? 'A');
    @endphp

    <!-- ================================================================= -->
    <!-- HALAMAN 1: SERTIFIKAT PENGHARGAAN RESMI DISPUSIP (DESAIN PRESISI) -->
    <!-- ================================================================= -->
    <main class="certificate-sheet relative flex flex-col justify-between overflow-hidden bg-white select-none">
        
        <!-- DYNAMIC SVG VECTOR ARTWORK (SUDUT KIRI ATAS, SUDUT KANAN BAWAH & TOP STRIPE) -->
        <svg viewBox="0 0 297 210" class="absolute inset-0 w-full h-full pointer-events-none z-0" xmlns="http://www.w3.org/2000/svg">
            <!-- Top Stripe (Aksen Tepi Atas) -->
            <rect x="52" y="0" width="245" height="3.2" fill="{{ $pDarkest }}" />
            
            <!-- 1. ORNAMEN SUDUT KIRI ATAS (Geometris Facet Poligon Mozaik Presisi) -->
            <!-- Col 0 (x: 0 - 10.4) -->
            <rect x="0" y="0" width="10.4" height="10.4" fill="{{ $pDarker }}" />
            <rect x="0" y="10.4" width="10.4" height="10.4" fill="{{ $pBase }}" />
            <polygon points="0,20.8 10.4,20.8 0,31.2" fill="{{ $pDarkest }}" />
            <rect x="0" y="31.2" width="10.4" height="10.4" fill="{{ $pMedium }}" />
            <polygon points="0,41.6 10.4,41.6 0,52.0" fill="{{ $pLight }}" />
            
            <!-- Col 1 (x: 10.4 - 20.8) -->
            <rect x="10.4" y="0" width="10.4" height="10.4" fill="{{ $pDarkest }}" />
            <polygon points="10.4,10.4 20.8,10.4 20.8,20.8" fill="{{ $pDark }}" />
            <polygon points="10.4,10.4 20.8,20.8 10.4,20.8" fill="{{ $pMedium }}" />
            <rect x="10.4" y="20.8" width="10.4" height="10.4" fill="{{ $pBase }}" />
            <polygon points="10.4,31.2 20.8,31.2 10.4,41.6" fill="{{ $pLight }}" />

            <!-- Col 2 (x: 20.8 - 31.2) -->
            <rect x="20.8" y="0" width="10.4" height="10.4" fill="{{ $pDark }}" />
            <polygon points="20.8,10.4 31.2,10.4 20.8,20.8" fill="{{ $pBase }}" />
            <polygon points="20.8,20.8 31.2,10.4 31.2,20.8" fill="{{ $pLight }}" />
            <polygon points="20.8,20.8 31.2,20.8 20.8,31.2" fill="{{ $pMedium }}" />

            <!-- Col 3 (x: 31.2 - 41.6) -->
            <polygon points="31.2,0 41.6,0 31.2,10.4" fill="{{ $pDarkest }}" />
            <polygon points="31.2,10.4 41.6,10.4 31.2,20.8" fill="{{ $pMedium }}" />

            <!-- Col 4 (x: 41.6 - 52.0) -->
            <polygon points="41.6,0 52.0,0 41.6,10.4" fill="{{ $pDark }}" />

            <!-- Guilloche Wave Curves Kiri (Pita Gelombang S-Curve Halus) -->
            <g fill="none" stroke="{{ $pMedium }}" stroke-width="0.32">
                <path d="M 0,46 C 12,82 28,120 0,160" opacity="0.25" />
                <path d="M 0,47 C 14,83 30,121 0,162" opacity="0.27" />
                <path d="M 0,48 C 16,84 32,122 0,164" opacity="0.30" />
                <path d="M 0,49 C 18,85 34,123 0,166" opacity="0.32" />
                <path d="M 0,50 C 20,86 36,124 0,168" opacity="0.35" />
                <path d="M 0,51 C 22,87 38,125 0,170" opacity="0.38" />
                <path d="M 0,52 C 24,88 40,126 0,172" opacity="0.40" />
                <path d="M 0,53 C 26,89 42,127 0,175" opacity="0.42" />
                <path d="M 0,54 C 28,90 44,128 0,178" opacity="0.45" />
                <path d="M 0,55 C 30,91 46,129 0,181" opacity="0.48" />
                <path d="M 0,56 C 32,92 48,130 0,184" opacity="0.50" />
                <path d="M 0,57 C 34,93 50,132 0,188" opacity="0.52" />
                <path d="M 0,58 C 36,94 52,134 0,192" opacity="0.55" />
            </g>

            <!-- 2. ORNAMEN SUDUT KANAN BAWAH (Stepped Tangga Geometris Hangat) -->
            <!-- Col 4 (Paling Kanan: x: 286.6 - 297.0) -->
            <rect x="286.6" y="199.6" width="10.4" height="10.4" fill="{{ $sDarker }}" />
            <rect x="286.6" y="189.2" width="10.4" height="10.4" fill="{{ $sDarkest }}" />
            <rect x="286.6" y="178.8" width="10.4" height="10.4" fill="{{ $sDarkest }}" />
            <rect x="286.6" y="168.4" width="10.4" height="10.4" fill="{{ $sDarker }}" />
            <polygon points="286.6,168.4 297,168.4 297,158.0" fill="{{ $sDarkest }}" />

            <!-- Col 3 (x: 276.2 - 286.6) -->
            <rect x="276.2" y="199.6" width="10.4" height="10.4" fill="{{ $sDark }}" />
            <polygon points="276.2,189.2 286.6,189.2 286.6,199.6" fill="{{ $sDark }}" />
            <polygon points="276.2,189.2 286.6,199.6 276.2,199.6" fill="{{ $sBase }}" />
            <rect x="276.2" y="178.8" width="10.4" height="10.4" fill="{{ $sDark }}" />
            <polygon points="276.2,178.8 286.6,178.8 286.6,168.4" fill="{{ $sDark }}" />

            <!-- Col 2 (x: 265.8 - 276.2) -->
            <rect x="265.8" y="199.6" width="10.4" height="10.4" fill="{{ $sBase }}" />
            <polygon points="265.8,189.2 276.2,189.2 265.8,199.6" fill="{{ $sBase }}" />
            <polygon points="265.8,199.6 276.2,189.2 276.2,199.6" fill="{{ $sMedium }}" />
            <polygon points="265.8,189.2 276.2,189.2 276.2,178.8" fill="{{ $sBase }}" />

            <!-- Col 1 (x: 255.4 - 265.8) -->
            <rect x="255.4" y="199.6" width="10.4" height="10.4" fill="{{ $sMedium }}" />
            <polygon points="255.4,199.6 265.8,199.6 265.8,189.2" fill="{{ $sMedium }}" />

            <!-- Col 0 (x: 245.0 - 255.4) -->
            <polygon points="245.0,210.0 255.4,210.0 255.4,199.6" fill="{{ $sLight }}" />

            <!-- Guilloche Wave Curves Kanan (Pita Gelombang S-Curve Menyapu ke Atas) -->
            <g fill="none" stroke="{{ $sMedium }}" stroke-width="0.32">
                <path d="M 297,165 C 270,128 258,85 297,42" opacity="0.25" />
                <path d="M 297,167 C 268,126 256,83 297,45" opacity="0.27" />
                <path d="M 297,169 C 266,124 254,81 297,48" opacity="0.30" />
                <path d="M 297,171 C 264,122 252,79 297,51" opacity="0.32" />
                <path d="M 297,173 C 262,120 250,77 297,54" opacity="0.35" />
                <path d="M 297,175 C 260,118 248,75 297,57" opacity="0.38" />
                <path d="M 297,177 C 258,116 246,73 297,60" opacity="0.40" />
                <path d="M 297,179 C 256,114 244,71 297,63" opacity="0.42" />
                <path d="M 297,181 C 254,112 242,69 297,66" opacity="0.45" />
                <path d="M 297,183 C 252,110 240,67 297,70" opacity="0.48" />
                <path d="M 297,185 C 250,108 238,65 297,74" opacity="0.50" />
                <path d="M 297,187 C 248,106 236,63 297,78" opacity="0.55" />
            </g>
        </svg>

        <!-- KONTEN SERTIFIKAT TERTATA PRESISI (LAYERING Z-INDEX 10) -->
        <div class="relative z-10 flex flex-col justify-between h-full pt-4 pb-3 px-8 sm:px-12 text-center">
            
            <!-- 1. HEADER KOP 5 LOGO RESMI SEJAJAR HORIZONTAL -->
            <div class="pt-1 flex items-center justify-center gap-6 sm:gap-7">
                <!-- Lambang Kota Surabaya -->
                <div class="h-11 sm:h-12 flex items-center justify-center">
                    <img src="{{ $surabayaLogoUrl }}" 
                         alt="Pemerintah Kota Surabaya" 
                         class="max-h-11 sm:max-h-12 w-auto object-contain"
                         onerror="this.onerror=null; this.src='{{ asset('images/certificates/logo_surabaya_lambang.jpeg') }}';">
                </div>

                <!-- Logo Baru Dispusip Kota Surabaya -->
                <div class="h-11 sm:h-12 flex items-center justify-center">
                    <img src="{{ $dispusipLogoUrl }}" 
                         alt="Dinas Perpustakaan dan Kearsipan Kota Surabaya" 
                         class="max-h-11 sm:max-h-12 w-auto object-contain"
                         onerror="this.onerror=null; this.src='{{ asset('images/logos/dispusip.png') }}';">
                </div>

                <!-- Logo ASN BerAKHLAK -->
                <div class="h-9 sm:h-10 flex items-center justify-center">
                    <img src="{{ $berakhlakLogoUrl }}" 
                         alt="ASN BerAKHLAK" 
                         class="max-h-9 sm:max-h-10 w-auto object-contain">
                </div>

                <!-- Logo Bangga Melayani Bangsa -->
                <div class="h-8 sm:h-9 flex items-center justify-center">
                    <img src="{{ $banggaLogoUrl }}" 
                         alt="Bangga Melayani Bangsa" 
                         class="max-h-8 sm:max-h-9 w-auto object-contain">
                </div>

                <!-- Logo Zona Integritas (WBK & WBBM) -->
                <div class="h-9 sm:h-10 flex items-center justify-center">
                    <img src="{{ $zonaLogoUrl }}" 
                         alt="Zona Integritas WBK WBBM" 
                         class="max-h-9 sm:max-h-10 w-auto object-contain">
                </div>
            </div>

            <!-- 2. JUDUL SERTIFIKAT & NOMOR REGISTRASI RESMI -->
            <div class="mt-2 space-y-0.5">
                <h1 class="font-cert-serif text-3xl sm:text-4xl md:text-[40px] font-bold text-black tracking-[0.05em] uppercase leading-none">
                    SERTIFIKAT
                </h1>
                <h2 class="font-cert-serif text-xl sm:text-2xl md:text-[23px] font-bold text-black tracking-[0.18em] uppercase leading-none pt-1">
                    MAGANG
                </h2>
                <p class="font-cert-serif text-xs sm:text-[13.5px] font-bold text-black tracking-wide pt-2">
                    Nomor : {{ $regNumber }}
                </p>
            </div>

            <!-- 3. NAMA MAHASISWA & GARIS BAWAH PRESISI DENGAN SUBTITLE -->
            <div class="mt-2 space-y-1">
                <div class="inline-block border-b-2 border-black pb-1 px-4 max-w-[85%]">
                    <h2 class="font-cert-name text-3xl sm:text-4xl md:text-[42px] font-extrabold text-black tracking-tight leading-none">
                        {{ $student->name }}
                    </h2>
                </div>
                <p class="font-cert-body text-xs sm:text-[14.5px] font-medium text-black leading-snug pt-1">
                    Mahasiswa Program Studi {{ $profile->jurusan ?? 'Semua Jurusan' }}{{ !empty($profile->fakultas) ? ' Fakultas ' . $profile->fakultas : '' }}
                </p>
                <p class="font-cert-body text-xs sm:text-[14.5px] font-medium text-black leading-snug">
                    {{ $profile->universitas ?? ($university->name ?? 'Universitas') }}
                </p>
            </div>

            <!-- 4. NARASI KELULUSAN & PERIODE MAGANG (ITALIC) -->
            <div class="mt-2 space-y-0.5 max-w-3xl mx-auto">
                <p class="font-cert-body text-xs sm:text-[14px] text-black leading-relaxed font-normal">
                    Atas partisipasinya dalam mengikuti kerja magang
                </p>
                <p class="font-cert-body text-xs sm:text-[14px] text-black leading-relaxed font-normal">
                    di Dinas Perpustakaan dan Kearsipan Kota Surabaya
                </p>
                <p class="font-cert-body text-xs sm:text-[14px] italic font-medium text-black pt-1">
                    {{ \Carbon\Carbon::parse($application->start_date)->translatedFormat('d F Y') }} s.d. {{ \Carbon\Carbon::parse($application->end_date)->translatedFormat('d F Y') }}
                </p>
            </div>

            <!-- 5. KOTA TANGGAL & KOTAK TTE ELEKTRONIK RESMI STANDAR BSrE -->
            <div class="mt-2 flex flex-col items-center">
                <p class="font-cert-body text-xs sm:text-[13px] text-black">
                    {{ $theme->city ?? 'Surabaya' }}, {{ \Carbon\Carbon::parse($placement->certificate?->issued_at ?? now())->translatedFormat('d F Y') }}
                </p>
                <p class="font-cert-body text-xs sm:text-[13px] text-black mt-0.5">
                    {{ $theme->mandate_text ?? 'a.n. Kepala Dinas Perpustakaan dan Kearsipan Kota Surabaya' }}
                </p>

                <!-- Kotak TTE Resmi Standar BSrE / Kominfo -->
                <div class="mt-1.5 flex items-center gap-3.5 px-3.5 py-2 bg-white border-[1.5px] border-black text-left font-cert-tte shadow-2xs" style="width: 360px; max-width: 95%; height: 78px;">
                    
                    <!-- Sisi Kiri Kotak: QR Code dengan Lambang Surabaya di Tengah -->
                    <div class="relative w-[64px] h-[64px] flex items-center justify-center shrink-0 bg-white border border-slate-300 p-0.5">
                        <div class="w-full h-full flex items-center justify-center [&>svg]:w-full [&>svg]:h-full" aria-label="QR Verifikasi Sertifikat">
                            {!! $qrSvg !!}
                        </div>
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="w-[18px] h-[18px] bg-white rounded-xs p-[1px] shadow-2xs flex items-center justify-center border border-slate-300">
                                <img src="{{ $surabayaLogoUrl }}" 
                                     alt="SBY" 
                                     class="w-full h-full object-contain"
                                     onerror="this.onerror=null; this.src='{{ asset('images/certificates/logo_surabaya_lambang.jpeg') }}';">
                            </div>
                        </div>
                    </div>

                    <!-- Sisi Kanan Kotak: Teks Penandatanganan Elektronik Berjenjang -->
                    <div class="text-[9.5px] leading-tight text-black flex-1">
                        <div class="text-[8.5px] text-slate-600">Ditandatangani secara elektronik oleh:</div>
                        <div class="font-medium text-slate-800">{{ $theme->signee_position ?? 'Sekretaris' }},</div>
                        <div class="mt-0.5 font-bold text-[11px] text-black tracking-tight">{{ $theme->signee_name ?? 'Dra. NOVELIJA, SH' }}</div>
                        <div class="text-[8px] font-semibold text-slate-600 uppercase tracking-wide">{{ $theme->signee_rank ?? 'PEMBINA TK I' }}</div>
                        <div class="text-[8.5px] font-mono text-slate-700 font-medium">NIP. {{ $theme->signee_nip ?? '197111141992012001' }}</div>
                    </div>

                </div>
            </div>

        </div>
    </main>

    <!-- ================================================================= -->
    <!-- HALAMAN 2: LAMPIRAN TRANSKRIP RINCIAN NILAI KOMPETENSI            -->
    <!-- ================================================================= -->
    <section class="certificate-sheet page-break px-8 py-5 relative flex flex-col justify-between bg-white select-none">
        
        <!-- Border Ornamen Halaman 2 -->
        <div class="absolute inset-3 border-[2px] border-slate-300 rounded-2xl pointer-events-none"></div>

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
                                    <td class="py-1.5 px-3 text-center text-slate-400">-</td>
                                    <td class="py-1.5 px-3 text-center font-bold">{{ isset($mComp['score']) && is_numeric($mComp['score']) ? ((float)$mComp['score'] == (int)$mComp['score'] ? (int)$mComp['score'] : number_format((float)$mComp['score'], 1)) : ($mComp['score'] ?? '-') }}</td>
                                    <td class="py-1.5 px-3 text-center text-slate-400">-</td>
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
                                    <td class="py-1.5 px-3 text-center text-slate-400">-</td>
                                    <td class="py-1.5 px-3 text-center font-bold">{{ isset($lComp['score']) && is_numeric($lComp['score']) ? ((float)$lComp['score'] == (int)$lComp['score'] ? (int)$lComp['score'] : number_format((float)$lComp['score'], 1)) : ($lComp['score'] ?? '-') }}</td>
                                    <td class="py-1.5 px-3 text-center text-slate-400">-</td>
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
                                GRADE {{ $simpleGrade }}
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

        </div>

        <!-- 4. FOOTER KETERANGAN TRANSKRIP (Dokumen Sah Tak Terpisahkan) -->
        <div class="relative z-10 border-t border-slate-200 pt-2.5 px-4 pb-2 flex items-center justify-between text-xs text-slate-500">
            <div class="space-y-0.5">
                <p class="font-bold text-[9.5px] uppercase tracking-wider text-slate-700">Keterangan Skala Predikat & Mutu Nilai:</p>
                <p class="text-[9px] text-slate-600">
                    <strong>A (85 - 100)</strong> : Sangat Memuaskan &bull; 
                    <strong>B (70 - 84)</strong> : Memuaskan &bull; 
                    <strong>C (55 - 69)</strong> : Cukup
                </p>
            </div>
            <div class="text-right text-[9px] text-slate-500 max-w-md leading-tight">
                <p class="font-semibold text-slate-700">
                    Lampiran transkrip nilai ini merupakan satu kesatuan sah dan tak terpisahkan dari Sertifikat Magang Nomor: <span class="font-bold text-slate-900">{{ $regNumber }}</span>.
                </p>
                <p class="text-[8px] text-slate-400 mt-0.5">
                    Telah disahkan dan terverifikasi secara elektronik melalui Tanda Tangan Elektronik (TTE) pada lembar utama.
                </p>
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
