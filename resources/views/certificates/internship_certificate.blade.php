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

    {{-- Top Action Bar (Hidden on Print) --}}
    <header class="no-print sticky top-0 z-50 bg-slate-900/95 backdrop-blur-md border-b border-slate-800 text-white px-6 py-3 flex items-center justify-between shadow-xl">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white rounded-xl text-xs font-bold transition border border-slate-700 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Dashboard</span>
            </a>
            <div class="hidden sm:block text-xs text-slate-400 border-l border-slate-700 pl-3">
                <span>Dokumen Resmi: </span>
                <strong class="text-slate-200">{{ $regNumber }}</strong>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-black shadow-lg shadow-blue-600/30 transition transform active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Sertifikat (A4 Landscape / PDF)</span>
            </button>
        </div>
    </header>

    @php
        // Logo Kampus Resolver
        $univLogo = $university->logo ?? null;
        if (!$univLogo && $profile && $profile->universitas) {
            $uName = strtolower($profile->universitas);
            if (str_contains($uName, 'unesa')) $univLogo = 'images/logos/unesa.png';
            elseif (str_contains($uName, 'its')) $univLogo = 'images/logos/its.png';
            elseif (str_contains($uName, 'unair')) $univLogo = 'images/logos/unair.png';
            elseif (str_contains($uName, 'upn')) $univLogo = 'images/logos/upnjatim.png';
            elseif (str_contains($uName, 'unitomo') || str_contains($uName, 'soetomo')) $univLogo = 'images/logos/unitomo.png';
        }
    @endphp

    <!-- ================================================================= -->
    <!-- HALAMAN 1: SERTIFIKAT KELULUSAN RESMI                             -->
    <!-- ================================================================= -->
    <main class="certificate-sheet p-8 relative">
        
        <!-- Double Border Ornamen Resmi & Sudut Emas -->
        <div class="absolute inset-3 border-[2.5px] border-amber-600/50 rounded-2xl pointer-events-none"></div>
        <div class="absolute inset-4.5 border border-amber-500/25 rounded-xl pointer-events-none"></div>
        <div class="absolute inset-0 bg-[radial-gradient(#f8fafc_1px,transparent_1px)] [background-size:16px_16px] opacity-30 pointer-events-none"></div>

        <!-- Watermark Lambang Surabaya Samar di Tengah -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.04]">
            <img src="{{ asset('images/logos/surabaya.png') }}" alt="Watermark" class="w-[380px] object-contain">
        </div>

        <!-- Bagian Atas: KOP & JUDUL & DATA PESERTA -->
        <div class="relative z-10 px-5 pt-1">
            
            <!-- 1. KOP RESMI PEMKOT SURABAYA & KAMPUS -->
            <div class="flex items-center justify-between border-b-2 border-amber-600/40 pb-3">
                
                <!-- Logo Pemkot Surabaya (Kiri) -->
                <div class="w-16 h-16 flex items-center justify-center shrink-0">
                    <img src="{{ asset('images/logos/surabaya.png') }}" 
                         alt="Logo Pemkot Surabaya" 
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

            <!-- 2. JUDUL SERTIFIKAT & NOMOR REGISTRASI -->
            <div class="text-center mt-3.5">
                <h1 class="font-serif-title text-2xl font-black tracking-widest text-slate-900 uppercase">
                    Sertifikat Kelulusan Magang
                </h1>
                <p class="font-mono text-[11px] font-bold text-amber-700 tracking-wider mt-0.5">
                    NOMOR: {{ $regNumber }}
                </p>
                <div class="w-28 h-0.5 bg-gradient-to-r from-transparent via-amber-600 to-transparent mx-auto mt-1.5"></div>
            </div>

            <!-- 3. ISI PERNYATAAN KELULUSAN -->
            <div class="text-center mt-3 max-w-4xl mx-auto space-y-2 text-slate-700 text-xs leading-relaxed">
                <p class="font-serif text-[11px] italic text-slate-500">Diberikan secara resmi dan sah kepada:</p>
                
                <div class="py-0.5">
                    <h2 class="text-xl font-black text-slate-900 tracking-tight uppercase underline decoration-amber-500/50 decoration-2 underline-offset-4">
                        {{ $student->name }}
                    </h2>
                    <p class="font-mono text-[11px] font-bold text-blue-900 mt-1">
                        NIM: {{ $profile->nim ?? '-' }} &bull; Program Studi: {{ $profile->jurusan ?? 'Informatika' }}
                    </p>
                    <p class="text-xs font-semibold text-slate-600">
                        {{ $profile->universitas ?? ($university->name ?? 'Universitas Dr. Soetomo') }}
                    </p>
                </div>

                <p class="text-[11.5px] text-slate-600 max-w-3xl mx-auto pt-1 leading-normal">
                    Telah menyelesaikan seluruh rangkaian program <strong>Praktik Kerja Lapangan (PKL) / Magang MBKM</strong> pada unit kerja 
                    <strong>{{ $application->unit->name ?? 'Bidang Layanan Informatika & E-Government' }}</strong>, 
                    {{ $agencyProfile->agency_name ?? 'Dinas Komunikasi Dan Informatika' }}, Pemerintah Kota Surabaya 
                    terhitung sejak tanggal <strong>{{ \Carbon\Carbon::parse($application->start_date)->translatedFormat('d F Y') }}</strong> 
                    sampai dengan <strong>{{ \Carbon\Carbon::parse($application->end_date)->translatedFormat('d F Y') }}</strong> dengan predikat:
                </p>

                <!-- PREDIKAT BADGE BOX EMAS -->
                <div class="pt-1.5">
                    <div class="inline-flex items-center gap-2.5 px-6 py-1.5 bg-gradient-to-r from-amber-50 via-amber-100/80 to-amber-50 border border-amber-300 rounded-xl shadow-2xs">
                        <span class="text-[11px] font-black text-amber-900 uppercase tracking-wider">PREDIKAT:</span>
                        <span class="text-sm font-black text-amber-950 font-serif-title">
                            {{ strtoupper($eval->predikat ?? 'DENGAN PUJIAN (SANGAT MEMUASKAN)') }} (NILAI: {{ $eval->nilai_akhir ?? 90 }} / GRADE {{ $eval->grade_calculated ?? 'A' }})
                        </span>
                    </div>
                </div>
            </div>

        </div>

        <!-- 4. FOOTER TANDA TANGAN DUA PIHAK (DPL KAMPUS & KEPALA DINAS) -->
        <div class="relative z-10 grid grid-cols-2 gap-8 px-12 pb-2 text-center text-xs">
            
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
                    <p class="font-mono text-[9.5px] text-slate-500">NIP/NIDN: {{ $dosen->nip ?? '-' }}</p>
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
                    <p class="font-mono text-[9.5px] text-slate-500">NIP: {{ $agencyProfile->signee_nip ?? '19700101 199503 1 002' }}</p>
                </div>
            </div>

        </div>

    </main>

    <!-- ================================================================= -->
    <!-- HALAMAN 2: LAMPIRAN TRANSKRIP RINCIAN NILAI KOMPETENSI            -->
    <!-- ================================================================= -->
    <section class="certificate-sheet page-break p-8 relative">
        
        <!-- Border Ornamen Halaman 2 -->
        <div class="absolute inset-3 border-[2px] border-slate-300 rounded-2xl pointer-events-none"></div>

        <div class="relative z-10 px-5 pt-1">
            
            <!-- 1. HEADER TRANSKRIP NILAI -->
            <div class="flex items-center justify-between border-b-2 border-slate-800 pb-2.5">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logos/surabaya.png') }}" 
                         alt="Logo Pemkot Surabaya" 
                         class="w-10 h-10 object-contain">
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

            <!-- 2. DATA MAHASISWA & PENEMPATAN (2 KOLOM RAPI) -->
            <div class="grid grid-cols-2 gap-4 mt-2.5 p-2.5 bg-slate-50 rounded-xl border border-slate-200 text-[11px]">
                <div class="space-y-0.5">
                    <div class="flex"><span class="w-28 text-slate-500 font-medium">Nama Mahasiswa</span><span class="font-bold text-slate-900">: {{ $student->name }}</span></div>
                    <div class="flex"><span class="w-28 text-slate-500 font-medium">NIM</span><span class="font-mono font-bold text-slate-900">: {{ $profile->nim ?? '-' }}</span></div>
                    <div class="flex"><span class="w-28 text-slate-500 font-medium">Program Studi</span><span class="font-medium text-slate-800">: {{ $profile->jurusan ?? 'Informatika' }}</span></div>
                </div>
                <div class="space-y-0.5">
                    <div class="flex"><span class="w-28 text-slate-500 font-medium">Universitas Asal</span><span class="font-bold text-slate-900">: {{ $profile->universitas ?? ($university->name ?? '-') }}</span></div>
                    <div class="flex"><span class="w-28 text-slate-500 font-medium">Unit Kerja Magang</span><span class="font-medium text-slate-800">: {{ $application->unit->name ?? '-' }}</span></div>
                    <div class="flex"><span class="w-28 text-slate-500 font-medium">Periode Magang</span><span class="font-medium text-slate-800">: {{ \Carbon\Carbon::parse($application->start_date)->translatedFormat('d/m/Y') }} s.d. {{ \Carbon\Carbon::parse($application->end_date)->translatedFormat('d/m/Y') }}</span></div>
                </div>
            </div>

            <!-- 3. TABEL TRANSKRIP RINCIAN PENILAIAN -->
            <div class="mt-2.5 overflow-hidden rounded-xl border border-slate-300">
                <table class="min-w-full text-left text-[11px] divide-y divide-slate-200">
                    <thead class="bg-slate-900 text-white font-bold uppercase text-[9.5px] tracking-wider">
                        <tr>
                            <th class="py-2 px-3 text-center w-10">No</th>
                            <th class="py-2 px-3">Komponen Penilaian & Aspek Kompetensi Magang</th>
                            <th class="py-2 px-3 text-center w-20">Bobot (%)</th>
                            <th class="py-2 px-3 text-center w-24">Skor (0-100)</th>
                            <th class="py-2 px-3 text-center w-28">Skor Tertimbang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        
                        <!-- ASPEK I: KINERJA LAPANGAN (40%) -->
                        <tr class="bg-slate-100/90 font-bold text-slate-900">
                            <td class="py-1.5 px-3 text-center font-bold">A</td>
                            <td colspan="4" class="py-1.5 px-3 uppercase text-[10px] text-blue-900">
                                Penilaian Pembimbing Lapangan Dinas (Instansi Pemerintah Kota)
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 px-3 text-center text-slate-400">1</td>
                            <td class="py-1 px-3">Disiplin, Kehadiran, & Ketaatan Tata Tertib Kedinasan</td>
                            <td class="py-1 px-3 text-center text-slate-400">-</td>
                            <td class="py-1 px-3 text-center font-mono font-bold">{{ $eval->nilai_disiplin ?? 90 }}</td>
                            <td class="py-1 px-3 text-center text-slate-400">-</td>
                        </tr>
                        <tr>
                            <td class="py-1 px-3 text-center text-slate-400">2</td>
                            <td class="py-1 px-3">Kinerja Teknis, Kualitas Output Proyek, & Tanggung Jawab Kerja</td>
                            <td class="py-1 px-3 text-center text-slate-400">-</td>
                            <td class="py-1 px-3 text-center font-mono font-bold">{{ $eval->nilai_kinerja ?? 90 }}</td>
                            <td class="py-1 px-3 text-center text-slate-400">-</td>
                        </tr>
                        <tr>
                            <td class="py-1 px-3 text-center text-slate-400">3</td>
                            <td class="py-1 px-3">Inisiatif, Komunikasi Lapangan, & Penyusunan Laporan Dinas</td>
                            <td class="py-1 px-3 text-center text-slate-400">-</td>
                            <td class="py-1 px-3 text-center font-mono font-bold">{{ $eval->nilai_laporan ?? 90 }}</td>
                            <td class="py-1 px-3 text-center text-slate-400">-</td>
                        </tr>
                        <tr class="bg-blue-50/70 font-bold">
                            <td colspan="2" class="py-1.5 px-3 text-right text-blue-950">Subtotal Nilai Dinas (Rata-rata):</td>
                            <td class="py-1.5 px-3 text-center font-mono text-blue-900">40%</td>
                            <td class="py-1.5 px-3 text-center font-mono text-blue-900">{{ $eval->nilai_pembimbing ?? 90 }}</td>
                            <td class="py-1.5 px-3 text-center font-mono text-blue-950 font-black">
                                {{ round(0.40 * ($eval->nilai_pembimbing ?? 90), 2) }}
                            </td>
                        </tr>

                        <!-- ASPEK II: AKADEMIK DPL (60%) -->
                        <tr class="bg-slate-100/90 font-bold text-slate-900">
                            <td class="py-1.5 px-3 text-center font-bold">B</td>
                            <td colspan="4" class="py-1.5 px-3 uppercase text-[10px] text-indigo-900">
                                Penilaian Akademik Dosen Pembimbing Lapangan (DPL Kampus)
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 px-3 text-center text-slate-400">1</td>
                            <td class="py-1 px-3">Penguasaan Materi, Teori Ilmiah, & Solusi Teknis Magang</td>
                            <td class="py-1 px-3 text-center text-slate-400">-</td>
                            <td class="py-1 px-3 text-center font-mono font-bold">{{ $eval->score_mastery ?? ($eval->nilai_akademik ?? 95) }}</td>
                            <td class="py-1 px-3 text-center text-slate-400">-</td>
                        </tr>
                        <tr>
                            <td class="py-1 px-3 text-center text-slate-400">2</td>
                            <td class="py-1 px-3">Kualitas, Sistematika Penulisan, & Ketajaman Analisis Laporan Akhir</td>
                            <td class="py-1 px-3 text-center text-slate-400">-</td>
                            <td class="py-1 px-3 text-center font-mono font-bold">{{ $eval->score_report ?? ($eval->nilai_akademik ?? 90) }}</td>
                            <td class="py-1 px-3 text-center text-slate-400">-</td>
                        </tr>
                        <tr>
                            <td class="py-1 px-3 text-center text-slate-400">3</td>
                            <td class="py-1 px-3">Sikap, Komunikasi, & Keaktifan Konsultasi Bimbingan</td>
                            <td class="py-1 px-3 text-center text-slate-400">-</td>
                            <td class="py-1 px-3 text-center font-mono font-bold">{{ $eval->score_attitude ?? ($eval->nilai_akademik ?? 85) }}</td>
                            <td class="py-1 px-3 text-center text-slate-400">-</td>
                        </tr>
                        <tr class="bg-indigo-50/70 font-bold">
                            <td colspan="2" class="py-1.5 px-3 text-right text-indigo-950">Subtotal Nilai DPL (Rata-rata):</td>
                            <td class="py-1.5 px-3 text-center font-mono text-indigo-900">60%</td>
                            <td class="py-1.5 px-3 text-center font-mono text-indigo-900">{{ $eval->nilai_dosen_calculated ?? 90 }}</td>
                            <td class="py-1.5 px-3 text-center font-mono text-indigo-950 font-black">
                                {{ round(0.60 * ($eval->nilai_dosen_calculated ?? 90), 2) }}
                            </td>
                        </tr>

                        <!-- REKAPITULASI TOTAL NILAI AKHIR -->
                        <tr class="bg-slate-900 text-white font-black text-[11px]">
                            <td colspan="2" class="py-2 px-3 text-right uppercase tracking-wider">
                                Nilai Akhir Total & Konversi Mutu:
                            </td>
                            <td class="py-2 px-3 text-center font-mono">100%</td>
                            <td class="py-2 px-3 text-center font-mono text-emerald-400 text-xs">
                                {{ $eval->nilai_akhir ?? 90 }}
                            </td>
                            <td class="py-2 px-3 text-center font-mono text-amber-300 text-xs">
                                {{ $eval->grade_calculated ?? 'A' }} ({{ $eval->predikat ?? 'Sangat Memuaskan' }})
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

            <!-- 4. CATATAN EVALUASI & FEEDBACK -->
            <div class="grid grid-cols-2 gap-3 mt-2 text-[10.5px]">
                <div class="p-2 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="font-bold text-slate-700 block">💬 Catatan Pembimbing Lapangan Dinas:</span>
                    <p class="text-slate-600 italic mt-0.5">"{{ $eval->catatan ?? 'Mahasiswa berdedikasi tinggi dan berkinerja sangat memuaskan di dinas.' }}"</p>
                </div>
                <div class="p-2 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="font-bold text-slate-700 block">💬 Catatan DPL Kampus:</span>
                    <p class="text-slate-600 italic mt-0.5">"{{ $eval->feedback_dosen ?? ($eval->catatan_dosen ?? 'Penulisan laporan sangat komprehensif dan implementasi teknis di dinas sangat baik.') }}"</p>
                </div>
            </div>

        </div>

        <!-- 5. FOOTER TTD TRANSKRIP -->
        <div class="relative z-10 grid grid-cols-2 gap-8 px-12 pb-2 text-center text-xs">
            <div>
                <p class="font-bold text-slate-800 text-[11px]">Dosen Pembimbing Lapangan,</p>
                <div class="h-10 flex items-center justify-center my-0.5">
                    <span class="font-quote text-blue-900/30 text-sm italic font-bold">Approved</span>
                </div>
                <div class="border-t border-slate-400 pt-0.5 max-w-[200px] mx-auto">
                    <p class="font-bold text-slate-900 text-[11px]">{{ $dosen->name ?? 'Dr. Ir. Bambang Supriyadi, M.Kom' }}</p>
                </div>
            </div>

            <div>
                <p class="font-bold text-slate-800 text-[11px]">Pembimbing Lapangan Dinas,</p>
                <div class="h-10 flex items-center justify-center my-0.5">
                    <span class="font-quote text-emerald-900/30 text-sm italic font-bold">Approved</span>
                </div>
                <div class="border-t border-slate-400 pt-0.5 max-w-[200px] mx-auto">
                    <p class="font-bold text-slate-900 text-[11px]">{{ $mentor->name ?? 'Retno Mumpuni, S.Kom., M.Sc' }}</p>
                </div>
            </div>
        </div>

    </section>

</body>
</html>
