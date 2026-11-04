<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Magang — {{ $student->name }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>

    @php
        $theme = $theme ?? \App\Models\CertificateTheme::getActive();
        $primaryColor = $theme->primary_color ?? '#00A3C4';
        $secondaryColor = $theme->secondary_color ?? '#F36F21';
        $accentColor = $theme->accent_color ?? '#D97706';
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
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            background-color: #0f172a;
        }

        .font-serif-title {
            font-family: 'Cinzel', serif;
        }

        .font-quote {
            font-family: 'Playfair Display', serif;
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
<body class="text-slate-800 antialiased selection:bg-blue-600 selection:text-white">

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
    <!-- HALAMAN 1: SERTIFIKAT PENGHARGAAN RESMI DISPUSIP (DESAIN BARU)    -->
    <!-- ================================================================= -->
    <main class="certificate-sheet relative flex flex-col justify-between overflow-hidden bg-white select-none">
        
        <!-- Aksen Garis Atas & Bawah -->
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-sky-500 via-blue-600 to-amber-500"></div>
        <div class="absolute bottom-0 left-0 right-0 h-2" style="background-color: var(--theme-accent, #D97706);"></div>

        <!-- 1. ORNAMEN SUDUT KIRI ATAS: Geometris Poligon Bernuansa Cyan/Blue + Guilloche Waves -->
        <div class="absolute top-0 left-0 pointer-events-none w-[270px] h-[270px] z-0 overflow-hidden">
            <svg viewBox="0 0 270 270" class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                <!-- Poligon Facet Mosaic -->
                <polygon points="0,0 120,0 60,60 0,60" fill="var(--theme-primary, #00A3C4)" opacity="0.95"/>
                <polygon points="60,60 120,0 160,40 100,100" fill="#0284C7" opacity="0.9"/>
                <polygon points="0,60 60,60 30,120 0,90" fill="#0369A1" opacity="0.85"/>
                <polygon points="30,120 100,100 70,160 10,150" fill="#0284C7" opacity="0.9"/>
                <polygon points="100,100 160,40 190,70 140,130" fill="var(--theme-primary, #00A3C4)" opacity="0.95"/>
                <polygon points="140,130 190,70 220,100 170,160" fill="#0369A1" opacity="0.8"/>
                <polygon points="70,160 140,130 120,200 40,190" fill="#007799" opacity="0.85"/>
                
                <!-- Guilloche Wave Curves Cyan -->
                <path d="M 0,90 C 60,110 90,160 70,260" fill="none" stroke="var(--theme-primary, #00A3C4)" stroke-width="1.2" opacity="0.45"/>
                <path d="M 0,110 C 70,130 110,180 90,270" fill="none" stroke="#0284C7" stroke-width="1.2" opacity="0.4"/>
                <path d="M 0,130 C 80,150 130,200 110,270" fill="none" stroke="#38BDF8" stroke-width="1.2" opacity="0.35"/>
                <path d="M 0,150 C 90,170 150,220 130,270" fill="none" stroke="var(--theme-primary, #00A3C4)" stroke-width="1.2" opacity="0.3"/>
            </svg>
        </div>

        <!-- 2. ORNAMEN SUDUT KANAN BAWAH: Stepped Polygon Tangga Bernuansa Orange/Coral + Guilloche Waves -->
        <div class="absolute bottom-0 right-0 pointer-events-none w-[270px] h-[270px] z-0 overflow-hidden">
            <svg viewBox="0 0 270 270" class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                <!-- Stepped Tangga Geometris Orange -->
                <polygon points="270,270 270,190 230,190 230,230 190,230 190,270" fill="var(--theme-secondary, #F36F21)" opacity="0.95"/>
                <polygon points="230,190 230,150 190,150 190,190" fill="#EA580C" opacity="0.9"/>
                <polygon points="190,230 190,190 150,190 150,230" fill="#F97316" opacity="0.9"/>
                <polygon points="190,150 190,110 150,110 150,150" fill="#FB923C" opacity="0.85"/>
                <polygon points="150,190 150,150 110,150 110,190" fill="#C2410C" opacity="0.85"/>
                <polygon points="270,190 270,130 230,150" fill="#FB923C" opacity="0.8"/>
                <polygon points="150,110 150,70 110,70 110,110" fill="var(--theme-secondary, #F36F21)" opacity="0.8"/>
                
                <!-- Guilloche Wave Curves Orange -->
                <path d="M 270,140 C 200,130 150,70 170,0" fill="none" stroke="var(--theme-secondary, #F36F21)" stroke-width="1.2" opacity="0.45"/>
                <path d="M 250,170 C 180,150 130,90 150,0" fill="none" stroke="#EA580C" stroke-width="1.2" opacity="0.4"/>
                <path d="M 230,200 C 160,170 110,110 130,0" fill="none" stroke="#FB923C" stroke-width="1.2" opacity="0.35"/>
                <path d="M 210,230 C 140,190 90,130 110,0" fill="none" stroke="#F97316" stroke-width="1.2" opacity="0.3"/>
            </svg>
        </div>

        <!-- 3. HEADER KOP LOGO RESMI (Deretan Horizontal Terpadu di Bagian Atas) -->
        <div class="relative z-10 pt-4 px-10">
            <div class="flex items-center justify-center gap-5 sm:gap-7">
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
                <div class="h-9 sm:h-10 flex items-center justify-center">
                    <img src="{{ $banggaLogoUrl }}" 
                         alt="Bangga Melayani Bangsa" 
                         class="max-h-9 sm:max-h-10 w-auto object-contain">
                </div>

                <!-- Logo Zona Integritas (WBK & WBBM) -->
                <div class="h-9 sm:h-10 flex items-center justify-center">
                    <img src="{{ $zonaLogoUrl }}" 
                         alt="Zona Integritas WBK WBBM" 
                         class="max-h-9 sm:max-h-10 w-auto object-contain">
                </div>
            </div>
        </div>

        <!-- 4. KONTEN UTAMA SERTIFIKAT (Tengah - Proporsional, Bersih, dan Berwibawa) -->
        <div class="relative z-10 px-8 my-auto text-center space-y-4">
            
            <!-- Judul Sertifikat & Nomor Registrasi Dinas -->
            <div class="space-y-0.5">
                <h1 class="font-black text-3xl sm:text-4xl text-slate-950 tracking-wider uppercase font-serif-title">
                    SERTIFIKAT
                </h1>
                <h2 class="font-extrabold text-lg sm:text-xl text-slate-900 tracking-[0.22em] uppercase">
                    MAGANG
                </h2>
                <p class="text-xs sm:text-[13px] font-semibold text-slate-700 tracking-wide pt-0.5 font-mono">
                    Nomor : {{ $regNumber }}
                </p>
            </div>

            <!-- Nama & Identitas Mahasiswa Penerima -->
            <div class="space-y-1 pt-1">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-950 tracking-tight font-sans">
                    {{ $student->name }}
                </h2>
                <p class="text-xs sm:text-[13px] font-semibold text-slate-700">
                    Mahasiswa Program Studi {{ $profile->jurusan ?? 'Semua Jurusan' }}{{ !empty($profile->fakultas) ? ' Fakultas ' . $profile->fakultas : '' }}
                </p>
                <p class="text-xs sm:text-[13px] font-semibold text-slate-700">
                    {{ $profile->universitas ?? ($university->name ?? 'Universitas') }}
                </p>
            </div>

            <!-- Narasi Penghargaan Selesai Magang -->
            <div class="max-w-3xl mx-auto space-y-1 pt-0.5">
                <p class="text-xs sm:text-[13px] text-slate-700 leading-relaxed font-medium">
                    Atas partisipasinya dalam mengikuti kerja magang di Dinas Perpustakaan dan Kearsipan Kota Surabaya
                </p>
                <p class="text-xs sm:text-[13px] font-bold text-slate-900 pt-0.5">
                    {{ \Carbon\Carbon::parse($application->start_date)->translatedFormat('d F Y') }} s.d. {{ \Carbon\Carbon::parse($application->end_date)->translatedFormat('d F Y') }}
                </p>
            </div>

            <!-- 5. TANDA TANGAN ELEKTRONIK RESMI KEPALA DINAS / SEKRETARIS (KOTAK TTE PRESISI) -->
            <div class="pt-2 flex flex-col items-center">
                <p class="text-[11.5px] font-medium text-slate-800">
                    {{ $theme->city ?? 'Surabaya' }}, {{ \Carbon\Carbon::parse($placement->certificate?->issued_at ?? now())->translatedFormat('d F Y') }}
                </p>
                <p class="text-[11.5px] font-semibold text-slate-800 mt-0.5">
                    {{ $theme->mandate_text ?? 'a.n. Kepala Dinas Perpustakaan dan Kearsipan Kota Surabaya' }}
                </p>

                <!-- Kotak TTE Resmi Standar BSrE / Kominfo -->
                <div class="mt-2 flex items-center gap-3.5 px-3.5 py-2.5 bg-white border border-slate-900 text-left shadow-2xs" style="width: 360px; max-width: 95%;">
                    
                    <!-- Sisi Kiri Kotak: QR Code dengan Lambang Surabaya di Tengah -->
                    <div class="relative w-[74px] h-[74px] flex items-center justify-center shrink-0 bg-white border border-slate-200 p-0.5">
                        <div class="w-full h-full flex items-center justify-center [&>svg]:w-full [&>svg]:h-full" aria-label="QR Verifikasi Sertifikat">
                            {!! $qrSvg !!}
                        </div>
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="w-[19px] h-[19px] bg-white rounded-xs p-[1px] shadow-2xs flex items-center justify-center border border-slate-300">
                                <img src="{{ $surabayaLogoUrl }}" 
                                     alt="SBY" 
                                     class="w-full h-full object-contain"
                                     onerror="this.onerror=null; this.src='{{ asset('images/certificates/logo_surabaya_lambang.jpeg') }}';">
                            </div>
                        </div>
                    </div>

                    <!-- Sisi Kanan Kotak: Teks Penandatanganan Elektronik Berjenjang -->
                    <div class="text-[9.5px] leading-snug text-slate-900 font-sans flex-1">
                        <div class="text-[9px] text-slate-600">Ditandatangani secara elektronik oleh:</div>
                        <div class="font-medium text-slate-800">{{ $theme->signee_position ?? 'Sekretaris' }},</div>
                        <div class="mt-1 font-bold text-[10.5px] text-slate-950 tracking-tight">{{ $theme->signee_name ?? 'Dra. NOVELIJA, SH' }}</div>
                        <div class="text-[8.5px] font-semibold text-slate-600 uppercase tracking-wide">{{ $theme->signee_rank ?? 'PEMBINA TK I' }}</div>
                        <div class="text-[8.5px] font-mono text-slate-700 font-medium">NIP. {{ $theme->signee_nip ?? '197111141992012001' }}</div>
                    </div>

                </div>
            </div>

        </div>

        <div class="pb-1"></div>
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
