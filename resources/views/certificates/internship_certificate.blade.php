<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Kelulusan Magang — {{ $student->name }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
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
    <header class="no-print sticky top-0 z-50 bg-slate-900/95 backdrop-blur-md border-b border-slate-800 text-white px-6 py-3 flex items-center justify-between shadow-xl">
        <div class="flex items-center gap-3">
            <a href="{{ $backUrl }}" onclick="if(window.opener || window.history.length > 1){ if(window.opener){ window.close(); return false; } else { window.history.back(); return false; } }" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white rounded-xl text-xs font-bold transition border border-slate-700 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>{{ $backLabel }}</span>
            </a>
            <div class="hidden sm:block text-xs text-slate-400 border-l border-slate-700 pl-3">
                <span>Dokumen Resmi: </span>
                <strong class="text-slate-200">{{ $regNumber }}</strong>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-black shadow-lg shadow-blue-600/30 transition transform active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak / Simpan PDF (A4 Landscape)</span>
            </button>
        </div>
    </header>

    @php
        // 1. Logo Dinas Instansi Tempat Mahasiswa Magang
        $agencyLogo = $agencyProfile->logo ?? null;
        if (!$agencyLogo && $agencyProfile && $agencyProfile->agency_name) {
            $aName = strtolower($agencyProfile->agency_name);
            if (str_contains($aName, 'kominfo') || str_contains($aName, 'komunikasi')) $agencyLogo = 'images/logos/diskominfo.png';
            elseif (str_contains($aName, 'penduduk') || str_contains($aName, 'dukcapil')) $agencyLogo = 'images/logos/dispendukcapil.png';
            elseif (str_contains($aName, 'pustaka') || str_contains($aName, 'pusip')) $agencyLogo = 'images/logos/dispusip.png';
        }
        if (!$agencyLogo || !file_exists(public_path($agencyLogo))) {
            $agencyLogo = 'images/logos/diskominfo.png';
        }

        // 2. Logo Universitas Mahasiswa
        $univLogo = $university->logo ?? null;
        if (!$univLogo && $profile && $profile->universitas) {
            $uName = strtolower($profile->universitas);
            if (str_contains($uName, 'unesa')) $univLogo = 'images/logos/unesa.png';
            elseif (str_contains($uName, 'its')) $univLogo = 'images/logos/its.png';
            elseif (str_contains($uName, 'unair')) $univLogo = 'images/logos/unair.png';
            elseif (str_contains($uName, 'upn')) $univLogo = 'images/logos/upnjatim.png';
            elseif (str_contains($uName, 'unitomo') || str_contains($uName, 'soetomo')) $univLogo = 'images/logos/unitomo.png';
        }

        // 3. Simple Grade (e.g. "A")
        $simpleGrade = $eval->grade_calculated ?? ($eval->grade ?? 'A');
    @endphp

    <!-- ================================================================= -->
    <!-- HALAMAN 1: SERTIFIKAT KELULUSAN RESMI                             -->
    <!-- ================================================================= -->
    <main class="certificate-sheet p-8 relative flex flex-col justify-between">
        
        <!-- Double Border Ornamen Resmi & Sudut Emas -->
        <div class="absolute inset-3 border-[2.5px] border-amber-600/50 rounded-2xl pointer-events-none"></div>
        <div class="absolute inset-4.5 border border-amber-500/25 rounded-xl pointer-events-none"></div>
        <div class="absolute inset-0 bg-[radial-gradient(#f8fafc_1px,transparent_1px)] [background-size:16px_16px] opacity-30 pointer-events-none"></div>

        <!-- Watermark Lambang Dinas Samar di Tengah -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.04]">
            <img src="{{ asset($agencyLogo) }}" alt="Watermark" class="w-[360px] object-contain">
        </div>

        <!-- 1. KOP RESMI DINAS INSTANSI MAGANG & UNIVERSITAS (Bagian Atas) -->
        <div class="relative z-10 px-6 pt-1">
            <div class="flex items-center justify-between border-b-2 border-amber-600/40 pb-3">
                
                <!-- Logo Dinas Instansi Tempat Magang (Kiri) -->
                <div class="w-16 h-16 flex items-center justify-center shrink-0">
                    <img src="{{ asset($agencyLogo) }}" 
                         alt="{{ $agencyProfile->agency_name ?? 'Logo Dinas' }}" 
                         class="max-h-16 max-w-full object-contain"
                         style="height: 58px; width: auto;">
                </div>

                <!-- Teks Instansi Pemerintah Kota -->
                <div class="text-center flex-1 px-4">
                    <h3 class="font-bold text-[11px] uppercase tracking-[0.25em] text-slate-600">
                        {{ $agencyProfile->government_name ?? 'Pemerintah Kota Surabaya' }}
                    </h3>
                    <h2 class="font-serif-title font-black text-lg text-slate-900 tracking-wider mt-0.5 uppercase">
                        {{ $agencyProfile->agency_name ?? 'Dinas Komunikasi Dan Informatika' }}
                    </h2>
                    <p class="text-[9.5px] text-slate-500 mt-0.5 leading-tight">
                        {{ $agencyProfile->address ?? 'Jl. Jimerto No. 25-27, Ketabang, Genteng, Kota Surabaya, Jawa Timur 60272' }} &bull; Website: {{ $agencyProfile->website ?? 'https://surabaya.go.id' }}
                    </p>
                </div>

                <!-- Logo Universitas Mitra (Kanan) -->
                <div class="w-16 h-16 flex items-center justify-center shrink-0">
                    @if($univLogo && file_exists(public_path($univLogo)))
                        <img src="{{ asset($univLogo) }}" alt="{{ $profile->universitas ?? 'Logo Kampus' }}" class="max-h-16 max-w-full object-contain" style="height: 56px; width: auto;">
                    @else
                        <div class="w-14 h-14 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-xl font-bold text-blue-700">
                            🎓
                        </div>
                    @endif
                </div>

            </div>
        </div>

        <!-- 2. KONTEN UTAMA SERTIFIKAT (Tengah - Spasi Vertikal Merata & Proporsional) -->
        <div class="relative z-10 px-6 my-auto text-center space-y-4">
            
            <!-- Judul Sertifikat & Nomor Registrasi -->
            <div class="space-y-1">
                <h1 class="font-serif-title text-2xl sm:text-3xl font-black tracking-widest text-slate-900 uppercase">
                    Sertifikat Kelulusan Magang
                </h1>
                <p class="font-mono text-xs font-bold text-amber-700 tracking-wider">
                    NOMOR: {{ $regNumber }}
                </p>
                <div class="w-32 h-0.5 bg-gradient-to-r from-transparent via-amber-600 to-transparent mx-auto mt-1"></div>
            </div>

            <!-- Isi Pernyataan & Identitas Mahasiswa -->
            <div class="max-w-4xl mx-auto space-y-3">
                <p class="font-serif text-xs italic text-slate-500">Diberikan secara resmi dan sah kepada:</p>
                
                <div class="space-y-1">
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight uppercase underline decoration-amber-500/50 decoration-2 underline-offset-4">
                        {{ $student->name }}
                    </h2>
                    <p class="text-xs font-bold text-blue-900">
                        NIM: {{ $profile->nim ?? '-' }} &bull; Program Studi: {{ $profile->jurusan ?? 'Informatika' }}
                    </p>
                    <p class="text-xs font-semibold text-slate-600">
                        {{ $profile->universitas ?? ($university->name ?? 'Universitas Dr. Soetomo') }}
                    </p>
                </div>

                <p class="text-xs text-slate-600 max-w-3xl mx-auto leading-relaxed pt-1">
                    Telah menyelesaikan seluruh rangkaian program <strong>Praktik Kerja Lapangan (PKL) / Magang MBKM</strong> pada unit kerja 
                    <strong>{{ $application->unit->name ?? 'Bidang Layanan Informatika & E-Government' }}</strong>, 
                    {{ $agencyProfile->agency_name ?? 'Dinas Komunikasi Dan Informatika' }}, Pemerintah Kota Surabaya 
                    terhitung sejak tanggal <strong>{{ \Carbon\Carbon::parse($application->start_date)->translatedFormat('d F Y') }}</strong> 
                    sampai dengan <strong>{{ \Carbon\Carbon::parse($application->end_date)->translatedFormat('d F Y') }}</strong> dengan predikat:
                </p>

                <!-- Predikat Badge Box Emas -->
                <div class="pt-2">
                    <div class="inline-flex items-center gap-3 px-8 py-2 bg-gradient-to-r from-amber-50 via-amber-100/80 to-amber-50 border border-amber-300 rounded-xl shadow-2xs">
                        <span class="text-xs font-black text-amber-900 uppercase tracking-wider">PREDIKAT:</span>
                        <span class="text-base font-black text-amber-950 font-serif-title tracking-wide">
                            GRADE {{ $simpleGrade }} (NILAI: {{ $eval->nilai_akhir ?? 90 }})
                        </span>
                    </div>
                </div>
            </div>

        </div>

        <!-- 3. FOOTER TANDA TANGAN DUA PIHAK (Bagian Bawah) -->
        <div class="relative z-10 grid grid-cols-2 gap-8 px-12 pb-4 text-center text-xs">
            
            <!-- TTD 1: Dosen Pembimbing Lapangan (DPL) / Kampus -->
            <div>
                <p class="text-slate-500 font-medium text-[10.5px]">Mengetahui & Menyetujui,</p>
                <p class="font-bold text-slate-800 text-xs mt-0.5">Dosen Pembimbing Lapangan (DPL)</p>
                <p class="text-[10px] text-slate-500">{{ $profile->universitas ?? ($university->name ?? 'Universitas') }}</p>
                
                <div class="h-14 flex items-center justify-center my-0.5">
                    <span class="font-quote text-blue-900/35 text-lg italic font-bold">Verified Digital Signature</span>
                </div>

                <div class="border-t border-slate-400/60 pt-1 max-w-[220px] mx-auto">
                    <p class="font-bold text-slate-900 text-xs">{{ $dosen->name ?? 'Dr. Ir. Bambang Supriyadi, M.Kom' }}</p>
                    <p class="text-[9.5px] text-slate-500">NIP/NIDN: {{ $dosen->nip ?? '-' }}</p>
                </div>
            </div>

            <!-- TTD 2: Kepala Dinas / Pembimbing Lapangan Instansi -->
            <div>
                <p class="text-slate-500 font-medium text-[10.5px]">Surabaya, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p class="font-bold text-slate-800 text-xs mt-0.5">{{ $agencyProfile->signee_position ?? 'Kepala Dinas' }}</p>
                <p class="text-[10px] text-slate-500">{{ $agencyProfile->agency_name ?? 'Pemerintah Kota Surabaya' }}</p>
                
                <div class="h-14 flex items-center justify-center my-0.5">
                    <span class="font-quote text-emerald-900/35 text-lg italic font-bold">Official Seal Verified</span>
                </div>

                <div class="border-t border-slate-400/60 pt-1 max-w-[220px] mx-auto">
                    <p class="font-bold text-slate-900 text-xs">{{ $agencyProfile->signee_name ?? 'Drs. H. M. NASER, M.Si' }}</p>
                    <p class="text-[9.5px] text-slate-500">NIP: {{ $agencyProfile->signee_nip ?? '19700101 199503 1 002' }}</p>
                </div>
            </div>

        </div>

    </main>

    <!-- ================================================================= -->
    <!-- HALAMAN 2: LAMPIRAN TRANSKRIP RINCIAN NILAI KOMPETENSI            -->
    <!-- ================================================================= -->
    <section class="certificate-sheet page-break p-8 relative flex flex-col justify-between">
        
        <!-- Border Ornamen Halaman 2 -->
        <div class="absolute inset-3 border-[2px] border-slate-300 rounded-2xl pointer-events-none"></div>

        <!-- Bagian Atas: KOP, BIODATA, DAN TABEL TRANSKRIP -->
        <div class="relative z-10 px-5 pt-1">
            
            <!-- 1. HEADER TRANSKRIP NILAI DENGAN LOGO DINAS -->
            <div class="flex items-center justify-between border-b-2 border-slate-800 pb-2.5">
                <div class="flex items-center gap-3">
                    <img src="{{ asset($agencyLogo) }}" 
                         alt="{{ $agencyProfile->agency_name ?? 'Logo Dinas' }}" 
                         class="w-11 h-11 object-contain">
                    <div>
                        <h3 class="font-bold text-[9.5px] uppercase tracking-wider text-slate-500">
                            {{ $agencyProfile->government_name ?? 'Pemerintah Kota Surabaya' }}
                        </h3>
                        <h2 class="font-black text-xs text-slate-900 uppercase">
                            {{ $agencyProfile->agency_name ?? 'Dinas Komunikasi Dan Informatika' }}
                        </h2>
                    </div>
                </div>

                <div class="text-right">
                    <h1 class="font-black text-xs uppercase tracking-wider text-slate-900">
                        Lampiran Transkrip Nilai Magang
                    </h1>
                    <p class="font-mono text-[10px] text-slate-500 font-bold">
                        NOMOR: {{ $regNumber }}
                    </p>
                </div>
            </div>

            <!-- 2. DATA MAHASISWA & PENEMPATAN (TABEL RAPI DENGAN KOLOM TITIK DUA TERSEJAJARKAN) -->
            <div class="grid grid-cols-2 gap-6 mt-3 p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs">
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
                        <td class="font-medium text-slate-800 py-0.5">{{ $profile->jurusan ?? 'Informatika' }}</td>
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
                        <td class="font-medium text-slate-800 py-0.5">{{ $application->unit->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="w-28 text-slate-500 font-medium py-0.5">Periode Magang</td>
                        <td class="w-3 text-slate-400 py-0.5">:</td>
                        <td class="font-medium text-slate-800 py-0.5">{{ \Carbon\Carbon::parse($application->start_date)->translatedFormat('d/m/Y') }} s.d. {{ \Carbon\Carbon::parse($application->end_date)->translatedFormat('d/m/Y') }}</td>
                    </tr>
                </table>
            </div>

            <!-- 3. TABEL TRANSKRIP RINCIAN PENILAIAN -->
            <div class="mt-4 overflow-hidden rounded-xl border border-slate-300">
                <table class="min-w-full text-left text-[11px] divide-y divide-slate-200">
                    <thead class="bg-slate-900 text-white font-bold uppercase text-[9.5px] tracking-wider">
                        <tr>
                            <th class="py-2.5 px-3 text-center w-10">No</th>
                            <th class="py-2.5 px-3">Komponen Penilaian & Aspek Kompetensi Magang</th>
                            <th class="py-2.5 px-3 text-center w-20">Bobot (%)</th>
                            <th class="py-2.5 px-3 text-center w-24">Skor (0-100)</th>
                            <th class="py-2.5 px-3 text-center w-28">Skor Tertimbang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        
@php
    $univ = $eval ? $eval->getUniversity() : null;
    $isMentorOnly = $univ && $univ->evaluation_scheme === 'mentor_only';
    $weightMentor = $univ ? (int)$univ->weight_mentor : 40;
    $weightLecturer = $univ ? (int)$univ->weight_lecturer : 60;
@endphp
                        <!-- ASPEK I: KINERJA LAPANGAN -->
                        <tr class="bg-slate-100/90 font-bold text-slate-900">
                            <td class="py-1.5 px-3 text-center font-bold">A</td>
                            <td colspan="4" class="py-1.5 px-3 uppercase text-[10px] text-blue-900">
                                Penilaian Pembimbing Lapangan Dinas (Instansi Pemerintah Kota)
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1.5 px-3 text-center text-slate-400">1</td>
                            <td class="py-1.5 px-3">Disiplin, Kehadiran, & Ketaatan Tata Tertib Kedinasan</td>
                            <td class="py-1.5 px-3 text-center text-slate-400">-</td>
                            <td class="py-1.5 px-3 text-center font-bold">{{ $eval->nilai_disiplin ?? 90 }}</td>
                            <td class="py-1.5 px-3 text-center text-slate-400">-</td>
                        </tr>
                        <tr>
                            <td class="py-1.5 px-3 text-center text-slate-400">2</td>
                            <td class="py-1.5 px-3">Kinerja Teknis, Kualitas Output Proyek, & Tanggung Jawab Kerja</td>
                            <td class="py-1.5 px-3 text-center text-slate-400">-</td>
                            <td class="py-1.5 px-3 text-center font-bold">{{ $eval->nilai_kinerja ?? 90 }}</td>
                            <td class="py-1.5 px-3 text-center text-slate-400">-</td>
                        </tr>
                        <tr>
                            <td class="py-1.5 px-3 text-center text-slate-400">3</td>
                            <td class="py-1.5 px-3">Inisiatif, Komunikasi Lapangan, & Penyusunan Laporan Dinas</td>
                            <td class="py-1.5 px-3 text-center text-slate-400">-</td>
                            <td class="py-1.5 px-3 text-center font-bold">{{ $eval->nilai_laporan ?? 90 }}</td>
                            <td class="py-1.5 px-3 text-center text-slate-400">-</td>
                        </tr>
                        <tr class="bg-blue-50/70 font-bold">
                            <td colspan="2" class="py-2 px-3 text-right text-blue-950">Subtotal Nilai Dinas (Rata-rata):</td>
                            <td class="py-2 px-3 text-center text-blue-900">{{ $isMentorOnly ? '100%' : ($weightMentor . '%') }}</td>
                            <td class="py-2 px-3 text-center text-blue-900">{{ $eval->nilai_pembimbing ?? 90 }}</td>
                            <td class="py-2 px-3 text-center text-blue-950 font-black">
                                {{ $isMentorOnly ? ($eval->nilai_pembimbing ?? 90) : round(($weightMentor / 100) * ($eval->nilai_pembimbing ?? 90), 2) }}
                            </td>
                        </tr>

                        <!-- ASPEK II: AKADEMIK DPL -->
                        <tr class="bg-slate-100/90 font-bold text-slate-900">
                            <td class="py-1.5 px-3 text-center font-bold">B</td>
                            <td colspan="4" class="py-1.5 px-3 uppercase text-[10px] text-blue-900">
                                Penilaian Akademik Dosen Pembimbing Lapangan (DPL Kampus)
                            </td>
                        </tr>
                        @if ($isMentorOnly)
                            <tr>
                                <td class="py-2 px-3 text-center text-slate-400">-</td>
                                <td colspan="4" class="py-2 px-3 text-slate-500 italic text-[11px]">
                                    Penilaian Akademik DPL dilewati (Skema Kebijakan Penilaian Penuh Dinas 100% oleh {{ $univ->name ?? 'Perguruan Tinggi' }}).
                                </td>
                            </tr>
                            <tr class="bg-blue-50/70 font-bold">
                                <td colspan="2" class="py-2 px-3 text-right text-blue-950">Subtotal Nilai DPL:</td>
                                <td class="py-2 px-3 text-center text-blue-900">0%</td>
                                <td class="py-2 px-3 text-center text-blue-900">N/A</td>
                                <td class="py-2 px-3 text-center text-blue-950 font-black">0.00</td>
                            </tr>
                        @else
                            <tr>
                                <td class="py-1.5 px-3 text-center text-slate-400">1</td>
                                <td class="py-1.5 px-3">Penguasaan Materi, Teori Ilmiah, & Solusi Teknis Magang</td>
                                <td class="py-1.5 px-3 text-center text-slate-400">-</td>
                                <td class="py-1.5 px-3 text-center font-bold">{{ $eval->score_mastery ?? ($eval->nilai_akademik ?? 95) }}</td>
                                <td class="py-1.5 px-3 text-center text-slate-400">-</td>
                            </tr>
                            <tr>
                                <td class="py-1.5 px-3 text-center text-slate-400">2</td>
                                <td class="py-1.5 px-3">Kualitas, Sistematika Penulisan, & Ketajaman Analisis Laporan Akhir</td>
                                <td class="py-1.5 px-3 text-center text-slate-400">-</td>
                                <td class="py-1.5 px-3 text-center font-bold">{{ $eval->score_report ?? ($eval->nilai_akademik ?? 90) }}</td>
                                <td class="py-1.5 px-3 text-center text-slate-400">-</td>
                            </tr>
                            <tr>
                                <td class="py-1.5 px-3 text-center text-slate-400">3</td>
                                <td class="py-1.5 px-3">Sikap, Komunikasi, & Keaktifan Konsultasi Bimbingan</td>
                                <td class="py-1.5 px-3 text-center text-slate-400">-</td>
                                <td class="py-1.5 px-3 text-center font-bold">{{ $eval->score_attitude ?? ($eval->nilai_akademik ?? 85) }}</td>
                                <td class="py-1.5 px-3 text-center text-slate-400">-</td>
                            </tr>
                            <tr class="bg-blue-50/70 font-bold">
                                <td colspan="2" class="py-2 px-3 text-right text-blue-950">Subtotal Nilai DPL (Rata-rata):</td>
                                <td class="py-2 px-3 text-center text-blue-900">{{ $weightLecturer }}%</td>
                                <td class="py-2 px-3 text-center text-blue-900">{{ $eval->nilai_dosen_calculated ?? 90 }}</td>
                                <td class="py-2 px-3 text-center text-blue-950 font-black">
                                    {{ round(($weightLecturer / 100) * ($eval->nilai_dosen_calculated ?? 90), 2) }}
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
                                {{ $eval->nilai_akhir ?? 90 }}
                            </td>
                            <td class="py-2.5 px-3 text-center text-amber-300 text-sm">
                                GRADE {{ $simpleGrade }}
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

        </div>

        <!-- 4. FOOTER TTD TRANSKRIP (Bagian Bawah) -->
        <div class="relative z-10 grid grid-cols-2 gap-8 px-12 pb-5 text-center text-xs">
            <div>
                <p class="font-bold text-slate-800 text-xs">{{ $isMentorOnly ? 'Pihak Perguruan Tinggi,' : 'Dosen Pembimbing Lapangan,' }}</p>
                <div class="h-12 flex items-center justify-center my-1">
                    <span class="font-quote text-blue-900/30 text-sm italic font-bold">Approved</span>
                </div>
                <div class="border-t border-slate-400 pt-1 max-w-[200px] mx-auto">
                    <p class="font-bold text-slate-900 text-xs">{{ $dosen->name ?? ($univ->pic_name ?? 'Dr. Ir. Bambang Supriyadi, M.Kom') }}</p>
                </div>
            </div>

            <div>
                <p class="font-bold text-slate-800 text-xs">Pembimbing Lapangan Dinas,</p>
                <div class="h-12 flex items-center justify-center my-1">
                    <span class="font-quote text-emerald-900/30 text-sm italic font-bold">Approved</span>
                </div>
                <div class="border-t border-slate-400 pt-1 max-w-[200px] mx-auto">
                    <p class="font-bold text-slate-900 text-xs">{{ $mentor->name ?? 'Retno Mumpuni, S.Kom., M.Sc' }}</p>
                </div>
            </div>
        </div>

    </section>

</body>
</html>
