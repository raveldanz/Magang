<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Kelulusan Magang - {{ $student->name }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,400&display=swap" rel="stylesheet">
    
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
            min-height: 210mm;
            margin: 0 auto;
            background: #ffffff;
            box-sizing: border-box;
            position: relative;
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
<body class="text-slate-800 antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Top Action Bar (Hidden on Print) -->
    <header class="no-print sticky top-0 z-50 bg-slate-900/90 backdrop-blur-md border-b border-slate-800 text-white px-6 py-3.5 flex items-center justify-between shadow-xl">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white rounded-xl text-xs font-bold transition border border-slate-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Dashboard</span>
            </a>
            <div class="hidden sm:block text-xs text-slate-400 border-l border-slate-700 pl-3">
                <span>Dokumen Resmi: </span>
                <strong class="text-slate-200">{{ $regNumber }}</strong>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-black shadow-lg shadow-indigo-600/30 transition transform active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Sertifikat (A4 Landscape / PDF)</span>
            </button>
        </div>
    </header>

    <!-- ================================================================= -->
    <!-- HALAMAN 1: SERTIFIKAT KELULUSAN RESMI                             -->
    <!-- ================================================================= -->
    <main class="certificate-sheet p-8 flex flex-col justify-between relative overflow-hidden">
        
        <!-- Background Ornaments & Watermark -->
        <div class="absolute inset-4 border-[3px] border-amber-600/40 rounded-2xl pointer-events-none"></div>
        <div class="absolute inset-6 border border-amber-500/20 rounded-xl pointer-events-none"></div>
        <div class="absolute inset-0 bg-[radial-gradient(#f8fafc_1px,transparent_1px)] [background-size:16px_16px] opacity-40 pointer-events-none"></div>

        <!-- Watermark Lambang Surabaya Samar di Tengah -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.035]">
            <img src="{{ asset('images/logos/surabaya_logo.png') }}" onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/b/ba/Coat_of_arms_of_Surabaya.svg';" alt="Surabaya Watermark" class="w-[450px]">
        </div>

        <div class="relative z-10 px-6 pt-2">
            
            <!-- HEADER / KOP SERTIFIKAT DENGAN DUA LOGO -->
            <div class="flex items-center justify-between border-b-2 border-amber-600/30 pb-4">
                
                <!-- Logo Pemkot Surabaya (Kiri) -->
                <div class="w-20 h-20 flex items-center justify-center shrink-0">
                    <img src="{{ asset('images/logos/surabaya_logo.png') }}" 
                         onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/b/ba/Coat_of_arms_of_Surabaya.svg';" 
                         alt="Logo Pemkot Surabaya" 
                         class="max-h-20 max-w-full object-contain">
                </div>

                <!-- Judul Instansi -->
                <div class="text-center flex-1 px-4">
                    <h3 class="font-bold text-xs uppercase tracking-[0.25em] text-slate-500">
                        {{ $agencyProfile->government_name ?? 'Pemerintah Kota Surabaya' }}
                    </h3>
                    <h2 class="font-serif-title font-black text-xl text-slate-900 tracking-wider mt-0.5 uppercase">
                        {{ $agencyProfile->agency_name ?? 'Dinas Komunikasi Dan Informatika' }}
                    </h2>
                    <p class="text-[10px] text-slate-500 mt-0.5">
                        {{ $agencyProfile->address ?? 'Jl. Jimerto No. 25-27, Kota Surabaya, Jawa Timur' }} &bull; Website: {{ $agencyProfile->website ?? 'surabaya.go.id' }}
                    </p>
                </div>

                <!-- Logo Universitas Mitra (Kanan) -->
                <div class="w-20 h-20 flex items-center justify-center shrink-0">
                    @php
                        $univLogo = $university->logo ?? null;
                        if (!$univLogo && $profile && $profile->universitas) {
                            $uName = strtolower($profile->universitas);
                            if (str_contains($uName, 'unesa')) $univLogo = 'images/logos/unesa.png';
                            elseif (str_contains($uName, 'its')) $univLogo = 'images/logos/its.png';
                            elseif (str_contains($uName, 'unair')) $univLogo = 'images/logos/unair.png';
                            elseif (str_contains($uName, 'upn')) $univLogo = 'images/logos/upnjatim.png';
                            elseif (str_contains($uName, 'unitomo')) $univLogo = 'images/logos/unitomo.png';
                        }
                    @endphp
                    @if($univLogo && file_exists(public_path($univLogo)))
                        <img src="{{ asset($univLogo) }}" alt="{{ $profile->universitas ?? 'Logo Kampus' }}" class="max-h-20 max-w-full object-contain">
                    @else
                        <div class="w-16 h-16 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-2xl font-bold text-indigo-700">
                            🎓
                        </div>
                    @endif
                </div>

            </div>

            <!-- TITLE & REGISTRATION NUMBER -->
            <div class="text-center mt-5">
                <h1 class="font-serif-title text-2xl sm:text-3xl font-black tracking-widest text-slate-900 uppercase">
                    Sertifikat Kelulusan Magang
                </h1>
                <p class="font-mono text-xs font-semibold text-amber-700 tracking-wider mt-1">
                    {{ $regNumber }}
                </p>
                <div class="w-24 h-0.5 bg-gradient-to-r from-transparent via-amber-600 to-transparent mx-auto mt-2"></div>
            </div>

            <!-- ISI PERNYATAAN KELULUSAN -->
            <div class="text-center mt-4 max-w-4xl mx-auto space-y-2 text-slate-700 text-xs sm:text-sm leading-relaxed">
                <p class="font-serif text-xs italic text-slate-500">Diberikan secara resmi dan sah kepada:</p>
                
                <div class="py-1">
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight uppercase underline decoration-amber-500/50 decoration-2 underline-offset-4">
                        {{ $student->name }}
                    </h2>
                    <p class="font-mono text-xs font-bold text-indigo-800 mt-1">
                        NIM: {{ $profile->nim ?? '-' }} &bull; Program Studi: {{ $profile->jurusan ?? 'Informatika' }}
                    </p>
                    <p class="text-xs font-semibold text-slate-600">
                        {{ $profile->universitas ?? ($university->name ?? 'Perguruan Tinggi Mitra') }}
                    </p>
                </div>

                <p class="text-xs text-slate-600 max-w-3xl mx-auto pt-1">
                    Telah menyelesaikan program <strong>Praktik Kerja Lapangan (PKL) / Magang MBKM</strong> di unit kerja 
                    <strong>{{ $application->unit->name ?? 'Divisi Terkait' }}</strong>, 
                    {{ $agencyProfile->agency_name ?? 'Instansi Dinas Terkait' }}, Pemerintah Kota Surabaya 
                    terhitung sejak tanggal <strong>{{ \Carbon\Carbon::parse($application->start_date)->format('d F Y') }}</strong> 
                    sampai dengan <strong>{{ \Carbon\Carbon::parse($application->end_date)->format('d F Y') }}</strong> dengan predikat:
                </p>

                <!-- PREDIKAT BADGE BOX -->
                <div class="pt-2">
                    <div class="inline-flex items-center gap-3 px-6 py-2 bg-gradient-to-r from-amber-50 via-amber-100/70 to-amber-50 border border-amber-300 rounded-2xl shadow-xs">
                        <span class="text-xs font-black text-amber-900 uppercase tracking-widest">Predikat:</span>
                        <span class="text-base font-black text-amber-950 font-serif-title">
                            {{ $eval->predikat ?? 'Sangat Baik' }} (Nilai: {{ $eval->nilai_akhir ?? 90 }} / Grade {{ $eval->grade_calculated ?? 'A' }})
                        </span>
                    </div>
                </div>
            </div>

        </div>

        <!-- FOOTER TANDA TANGAN (DUAL SIGNATURES) -->
        <div class="relative z-10 grid grid-cols-2 gap-8 px-12 pb-4 text-center text-xs">
            
            <!-- TTD 1: Dosen Pembimbing Lapangan (DPL) / Kampus -->
            <div>
                <p class="text-slate-500 font-medium text-[11px]">Mengetahui & Menyetujui,</p>
                <p class="font-bold text-slate-800 text-xs mt-0.5">Dosen Pembimbing Lapangan (DPL)</p>
                <p class="text-[10px] text-slate-500">{{ $profile->universitas ?? ($university->name ?? 'Perguruan Tinggi') }}</p>
                
                <div class="h-16 flex items-center justify-center my-1">
                    <span class="font-quote text-indigo-900/40 text-xl italic font-bold">Verified Digital Signature</span>
                </div>

                <div class="border-t border-slate-400/60 pt-1 max-w-[220px] mx-auto">
                    <p class="font-bold text-slate-900 text-xs">{{ $dosen->name ?? 'Dosen Pembimbing Lapangan' }}</p>
                    <p class="font-mono text-[10px] text-slate-500">NIP/NIDN: {{ $dosen->nip ?? '-' }}</p>
                </div>
            </div>

            <!-- TTD 2: Kepala Dinas / Pembimbing Lapangan Instansi -->
            <div>
                <p class="text-slate-500 font-medium text-[11px]">Surabaya, {{ \Carbon\Carbon::now()->format('d F Y') }}</p>
                <p class="font-bold text-slate-800 text-xs mt-0.5">{{ $agencyProfile->signee_position ?? 'Kepala Dinas' }}</p>
                <p class="text-[10px] text-slate-500">{{ $agencyProfile->agency_name ?? 'Pemerintah Kota Surabaya' }}</p>
                
                <div class="h-16 flex items-center justify-center my-1">
                    <span class="font-quote text-emerald-900/40 text-xl italic font-bold">Official Seal Verified</span>
                </div>

                <div class="border-t border-slate-400/60 pt-1 max-w-[220px] mx-auto">
                    <p class="font-bold text-slate-900 text-xs">{{ $agencyProfile->signee_name ?? 'Drs. H. M. NASER, M.Si' }}</p>
                    <p class="font-mono text-[10px] text-slate-500">NIP: {{ $agencyProfile->signee_nip ?? '19700101 199503 1 002' }}</p>
                </div>
            </div>

        </div>

    </main>

    <!-- ================================================================= -->
    <!-- HALAMAN 2: LAMPIRAN TRANSKRIP RINCIAN NILAI KOMPETENSI            -->
    <!-- ================================================================= -->
    <section class="certificate-sheet page-break p-8 flex flex-col justify-between relative overflow-hidden">
        
        <div class="absolute inset-4 border-[2px] border-slate-200 rounded-2xl pointer-events-none"></div>

        <div class="relative z-10 px-6 pt-2">
            
            <!-- HEADER TRANSKRIP NILAI -->
            <div class="flex items-center justify-between border-b-2 border-slate-800 pb-3">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logos/surabaya_logo.png') }}" 
                         onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/b/ba/Coat_of_arms_of_Surabaya.svg';" 
                         alt="Logo Pemkot Surabaya" 
                         class="w-12 h-12 object-contain">
                    <div>
                        <h3 class="font-bold text-[10px] uppercase tracking-wider text-slate-500">
                            {{ $agencyProfile->government_name ?? 'Pemerintah Kota Surabaya' }}
                        </h3>
                        <h2 class="font-black text-sm text-slate-900 uppercase">
                            {{ $agencyProfile->agency_name ?? 'Dinas Komunikasi Dan Informatika' }}
                        </h2>
                    </div>
                </div>

                <div class="text-right">
                    <h1 class="font-black text-sm uppercase tracking-wider text-slate-900">
                        Lampiran Transkrip Nilai Magang
                    </h1>
                    <p class="font-mono text-[11px] text-slate-500 font-bold">
                        {{ $regNumber }}
                    </p>
                </div>
            </div>

            <!-- DATA MAHASISWA & PENEMPATAN -->
            <div class="grid grid-cols-2 gap-4 mt-4 p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs">
                <div>
                    <div class="flex"><span class="w-28 text-slate-500 font-semibold">Nama Mahasiswa</span><span class="font-bold text-slate-900">: {{ $student->name }}</span></div>
                    <div class="flex mt-1"><span class="w-28 text-slate-500 font-semibold">NIM</span><span class="font-mono font-bold text-slate-900">: {{ $profile->nim ?? '-' }}</span></div>
                    <div class="flex mt-1"><span class="w-28 text-slate-500 font-semibold">Program Studi</span><span class="font-medium text-slate-800">: {{ $profile->jurusan ?? 'Informatika' }}</span></div>
                </div>
                <div>
                    <div class="flex"><span class="w-28 text-slate-500 font-semibold">Universitas Asal</span><span class="font-bold text-slate-900">: {{ $profile->universitas ?? ($university->name ?? '-') }}</span></div>
                    <div class="flex mt-1"><span class="w-28 text-slate-500 font-semibold">Unit Kerja Magang</span><span class="font-medium text-slate-800">: {{ $application->unit->name ?? '-' }}</span></div>
                    <div class="flex mt-1"><span class="w-28 text-slate-500 font-semibold">Periode Magang</span><span class="font-medium text-slate-800">: {{ \Carbon\Carbon::parse($application->start_date)->format('d/m/Y') }} s.d. {{ \Carbon\Carbon::parse($application->end_date)->format('d/m/Y') }}</span></div>
                </div>
            </div>

            <!-- TABEL TRANSKRIP RINCIAN PENILAIAN -->
            <div class="mt-4 overflow-hidden rounded-xl border border-slate-200">
                <table class="min-w-full text-left text-xs divide-y divide-slate-200">
                    <thead class="bg-slate-900 text-white font-bold uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="py-2.5 px-3 text-center w-12">No</th>
                            <th class="py-2.5 px-4">Komponen Penilaian & Aspek Kompetensi Magang</th>
                            <th class="py-2.5 px-3 text-center w-24">Bobot (%)</th>
                            <th class="py-2.5 px-3 text-center w-24">Skor (0-100)</th>
                            <th class="py-2.5 px-3 text-center w-28">Skor Tertimbang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        
                        <!-- ASPEK I: KINERJA LAPANGAN (40%) -->
                        <tr class="bg-slate-50/80 font-bold text-slate-900">
                            <td class="py-2 px-3 text-center">A</td>
                            <td colspan="4" class="py-2 px-4 uppercase text-[11px] text-indigo-900">
                                Penilaian Pembimbing Lapangan Dinas (Instansi Pemerintah Kota)
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1.5 px-3 text-center text-slate-400">1</td>
                            <td class="py-1.5 px-4">Disiplin, Kehadiran, & Ketaatan Tata Tertib Kedinasan</td>
                            <td class="py-1.5 px-3 text-center text-slate-500">-</td>
                            <td class="py-1.5 px-3 text-center font-mono font-bold">{{ $eval->nilai_disiplin ?? 90 }}</td>
                            <td class="py-1.5 px-3 text-center text-slate-400">-</td>
                        </tr>
                        <tr>
                            <td class="py-1.5 px-3 text-center text-slate-400">2</td>
                            <td class="py-1.5 px-4">Kinerja Teknis, Kualitas Output Proyek, & Tanggung Jawab Kerja</td>
                            <td class="py-1.5 px-3 text-center text-slate-500">-</td>
                            <td class="py-1.5 px-3 text-center font-mono font-bold">{{ $eval->nilai_kinerja ?? 95 }}</td>
                            <td class="py-1.5 px-3 text-center text-slate-400">-</td>
                        </tr>
                        <tr>
                            <td class="py-1.5 px-3 text-center text-slate-400">3</td>
                            <td class="py-1.5 px-4">Inisiatif, Komunikasi Lapangan, & Penyusunan Laporan Dinas</td>
                            <td class="py-1.5 px-3 text-center text-slate-500">-</td>
                            <td class="py-1.5 px-3 text-center font-mono font-bold">{{ $eval->nilai_laporan ?? 85 }}</td>
                            <td class="py-1.5 px-3 text-center text-slate-400">-</td>
                        </tr>
                        <tr class="bg-indigo-50/40 font-bold">
                            <td colspan="2" class="py-2 px-4 text-right text-indigo-950">Subtotal Nilai Dinas (Rata-rata):</td>
                            <td class="py-2 px-3 text-center font-mono text-indigo-900">40%</td>
                            <td class="py-2 px-3 text-center font-mono text-indigo-900">{{ $eval->nilai_pembimbing ?? 90 }}</td>
                            <td class="py-2 px-3 text-center font-mono text-indigo-950 font-black">
                                {{ round(0.40 * ($eval->nilai_pembimbing ?? 90), 2) }}
                            </td>
                        </tr>

                        <!-- ASPEK II: AKADEMIK DPL (60%) -->
                        <tr class="bg-slate-50/80 font-bold text-slate-900">
                            <td class="py-2 px-3 text-center">B</td>
                            <td colspan="4" class="py-2 px-4 uppercase text-[11px] text-purple-900">
                                Penilaian Akademik Dosen Pembimbing Lapangan (DPL Kampus)
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1.5 px-3 text-center text-slate-400">1</td>
                            <td class="py-1.5 px-4">Penguasaan Materi, Teori Ilmiah, & Solusi Teknis Magang</td>
                            <td class="py-1.5 px-3 text-center text-slate-500">-</td>
                            <td class="py-1.5 px-3 text-center font-mono font-bold">{{ $eval->score_mastery ?? ($eval->nilai_akademik ?? 90) }}</td>
                            <td class="py-1.5 px-3 text-center text-slate-400">-</td>
                        </tr>
                        <tr>
                            <td class="py-1.5 px-3 text-center text-slate-400">2</td>
                            <td class="py-1.5 px-4">Kualitas, Sistematika Penulisan, & Ketajaman Analisis Laporan Akhir</td>
                            <td class="py-1.5 px-3 text-center text-slate-500">-</td>
                            <td class="py-1.5 px-3 text-center font-mono font-bold">{{ $eval->score_report ?? ($eval->nilai_akademik ?? 90) }}</td>
                            <td class="py-1.5 px-3 text-center text-slate-400">-</td>
                        </tr>
                        <tr>
                            <td class="py-1.5 px-3 text-center text-slate-400">3</td>
                            <td class="py-1.5 px-4">Sikap, Komunikasi, & Keaktifan Konsultasi Bimbingan</td>
                            <td class="py-1.5 px-3 text-center text-slate-500">-</td>
                            <td class="py-1.5 px-3 text-center font-mono font-bold">{{ $eval->score_attitude ?? ($eval->nilai_akademik ?? 90) }}</td>
                            <td class="py-1.5 px-3 text-center text-slate-400">-</td>
                        </tr>
                        <tr class="bg-purple-50/40 font-bold">
                            <td colspan="2" class="py-2 px-4 text-right text-purple-950">Subtotal Nilai DPL (Rata-rata):</td>
                            <td class="py-2 px-3 text-center font-mono text-purple-900">60%</td>
                            <td class="py-2 px-3 text-center font-mono text-purple-900">{{ $eval->nilai_dosen_calculated ?? 90 }}</td>
                            <td class="py-2 px-3 text-center font-mono text-purple-950 font-black">
                                {{ round(0.60 * ($eval->nilai_dosen_calculated ?? 90), 2) }}
                            </td>
                        </tr>

                        <!-- REKAPITULASI TOTAL NILAI AKHIR -->
                        <tr class="bg-slate-900 text-white font-black text-xs">
                            <td colspan="2" class="py-3 px-4 text-right uppercase tracking-wider">
                                Nilai Akhir Total & Konversi Mutu:
                            </td>
                            <td class="py-3 px-3 text-center font-mono">100%</td>
                            <td class="py-3 px-3 text-center font-mono text-emerald-400 text-sm">
                                {{ $eval->nilai_akhir ?? 90 }}
                            </td>
                            <td class="py-3 px-3 text-center font-mono text-amber-300 text-sm">
                                {{ $eval->grade_calculated ?? 'A' }} ({{ $eval->predikat ?? 'Sangat Baik' }})
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

            <!-- CATATAN EVALUASI & FEEDBACK -->
            <div class="grid grid-cols-2 gap-4 mt-3 text-[11px]">
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="font-bold text-slate-700 block">💬 Catatan Pembimbing Lapangan Dinas:</span>
                    <p class="text-slate-600 italic mt-0.5">"{{ $eval->catatan ?? 'Mahasiswa berdedikasi tinggi dan berkinerja sangat memuaskan di dinas.' }}"</p>
                </div>
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="font-bold text-slate-700 block">💬 Catatan DPL Kampus:</span>
                    <p class="text-slate-600 italic mt-0.5">"{{ $eval->feedback_dosen ?? ($eval->catatan_dosen ?? 'Analisis laporan komprehensif dan memenuhi standar kelulusan MBKM.') }}"</p>
                </div>
            </div>

        </div>

        <!-- FOOTER TTD HALAMAN 2 -->
        <div class="relative z-10 grid grid-cols-2 gap-8 px-12 pb-2 text-center text-xs">
            <div>
                <p class="font-bold text-slate-800 text-xs">Dosen Pembimbing Lapangan,</p>
                <div class="h-12 flex items-center justify-center my-0.5">
                    <span class="font-quote text-indigo-900/30 text-base italic font-bold">Approved</span>
                </div>
                <div class="border-t border-slate-400 pt-0.5 max-w-[200px] mx-auto">
                    <p class="font-bold text-slate-900 text-xs">{{ $dosen->name ?? 'Dosen Pembimbing Lapangan' }}</p>
                </div>
            </div>

            <div>
                <p class="font-bold text-slate-800 text-xs">Pembimbing Lapangan Dinas,</p>
                <div class="h-12 flex items-center justify-center my-0.5">
                    <span class="font-quote text-emerald-900/30 text-base italic font-bold">Approved</span>
                </div>
                <div class="border-t border-slate-400 pt-0.5 max-w-[200px] mx-auto">
                    <p class="font-bold text-slate-900 text-xs">{{ $mentor->name ?? 'Pembimbing Dinas' }}</p>
                </div>
            </div>
        </div>

    </section>

</body>
</html>
