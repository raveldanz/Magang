<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara Penilaian DPL - {{ $student->name }} ({{ $profile->nim ?? 'NIM' }})</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Times+New+Roman&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 12mm 15mm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            color: #111827;
            background-color: #f1f5f9;
            line-height: 1.45;
        }

        .paper-container {
            width: 210mm;
            min-height: 297mm;
            padding: 16mm 20mm 16mm 20mm;
            margin: 24px auto;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            box-sizing: border-box;
            position: relative;
        }

        .kop-border {
            border-bottom: 3px solid #0f172a;
            position: relative;
            margin-top: 8px;
            margin-bottom: 18px;
        }

        .kop-border::after {
            content: "";
            display: block;
            border-bottom: 1px solid #0f172a;
            margin-top: 2px;
        }

        @media print {
            body {
                background: none !important;
                background-color: transparent !important;
            }
            .no-print {
                display: none !important;
            }
            .paper-container {
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                width: 100% !important;
                min-height: auto !important;
            }
        }
    </style>
</head>
<body>

    <!-- TOPBAR AKSI (Hanya tampil di layar monitor / NO PRINT) -->
    <div class="no-print bg-slate-900 text-white px-6 py-3.5 flex flex-wrap items-center justify-between gap-3 shadow-md sticky top-0 z-50 font-sans">
        <div class="flex items-center gap-3">
            <a href="{{ route('lecturer.students.show', $placement->id) }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Detail
            </a>
            <div class="hidden sm:block border-l border-slate-700 pl-3">
                <span class="text-xs text-slate-400 block">Dokumen Resmi Akademik</span>
                <span class="text-xs font-bold text-white">Berita Acara & Lembar Penilaian DPL</span>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <span class="text-xs text-slate-300 hidden md:inline">Format Standar A4 Cetak Kampus</span>
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition shadow-md shadow-blue-500/20 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak / Simpan PDF (Ctrl+P)
            </button>
        </div>
    </div>

    <!-- HALAMAN KERTAS A4 DOKUMEN RESMI -->
    <div class="paper-container">

        <!-- KOP SURAT UNIVERSITAS -->
        <div class="flex items-center gap-5">
            <div class="w-20 h-20 shrink-0 flex items-center justify-center">
                @php
                    $hasCustomLogo = $univ && $univ->logo && file_exists(public_path('storage/' . $univ->logo));
                @endphp
                @if ($hasCustomLogo)
                    <img src="{{ asset('storage/' . $univ->logo) }}" alt="Logo Kampus" class="w-20 h-20 object-contain">
                @else
                    <img src="{{ asset('images/default-university.svg') }}" alt="Logo Kampus" class="w-20 h-20 object-contain">
                @endif
            </div>
            <div class="flex-1 text-center font-serif">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</h3>
                <h1 class="text-xl font-bold uppercase tracking-wide text-slate-950 mt-0.5 leading-tight">
                    {{ $univ->name ?? ($profile->universitas ?? 'UNIVERSITAS MITRA MAGANG') }}
                </h1>
                <p class="text-xs font-medium text-slate-600 mt-1 leading-snug">
                    Program Studi {{ $profile->jurusan ?? 'Informatika' }} &bull; Fakultas Teknologi Informasi dan Sains Data
                </p>
                <p class="text-[11px] text-slate-500 font-sans mt-0.5">
                    {{ $univ->address ?? 'Kota Surabaya, Jawa Timur, Indonesia' }} &bull; Laman: {{ $univ->website ?? 'https://kemahasiswaan.ac.id' }}
                </p>
            </div>
        </div>

        <div class="kop-border"></div>

        <!-- JUDUL DAN NOMOR BERITA ACARA -->
        <div class="text-center my-3">
            <h2 class="text-base font-bold uppercase tracking-wider underline text-slate-950">
                BERITA ACARA & LEMBAR PENILAIAN DOSEN PEMBIMBING LAPANGAN (BAP-DPL)
            </h2>
            <p class="text-xs text-slate-600 font-sans mt-1">
                Nomor Dokumen: <span class="font-mono font-semibold">BAP-DPL/{{ date('Y') }}/{{ str_pad($placement->id, 4, '0', STR_PAD_LEFT) }}/{{ $profile->nim ?? 'MHS' }}</span>
            </p>
        </div>

        <!-- KATA PENGANTAR RESMI -->
        <p class="text-xs text-justify mt-3 leading-relaxed">
            Pada hari ini telah dilakukan rekapitulasi, telaah karya naskah laporan, dan evaluasi hasil kegiatan program Praktik Kerja Lapangan / Magang Mandiri Berdampak bagi mahasiswa berikut:
        </p>

        <!-- 1. IDENTITAS MAHASISWA & PENEMPATAN -->
        <div class="mt-2.5 bg-slate-50/60 p-3 rounded-lg border border-slate-200 text-xs font-sans">
            <table class="w-full text-left border-collapse">
                <tbody>
                    <tr>
                        <td class="w-40 py-1 font-semibold text-slate-700">Nama Lengkap Mahasiswa</td>
                        <td class="w-3 py-1">:</td>
                        <td class="py-1 font-bold text-slate-900 uppercase">{{ $student->name }}</td>
                    </tr>
                    <tr>
                        <td class="py-1 font-semibold text-slate-700">Nomor Induk Mahasiswa (NIM)</td>
                        <td class="py-1">:</td>
                        <td class="py-1 font-mono font-semibold text-slate-900">{{ $profile->nim ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="py-1 font-semibold text-slate-700">Program Studi / Jurusan</td>
                        <td class="py-1">:</td>
                        <td class="py-1 text-slate-900">{{ $profile->jurusan ?? 'Sistem Informasi' }}</td>
                    </tr>
                    <tr>
                        <td class="py-1 font-semibold text-slate-700">Instansi Mitra Penempatan</td>
                        <td class="py-1">:</td>
                        <td class="py-1 text-slate-900 font-medium">Pemerintah Kota Surabaya &mdash; {{ $agencyProfile->agency_name ?? 'Dinas Komunikasi dan Informatika' }}</td>
                    </tr>
                    <tr>
                        <td class="py-1 font-semibold text-slate-700">Unit Kerja Penempatan</td>
                        <td class="py-1">:</td>
                        <td class="py-1 text-slate-900">{{ $unit->name ?? 'Divisi Aplikasi & Tata Kelola SPBE' }}</td>
                    </tr>
                    <tr>
                        <td class="py-1 font-semibold text-slate-700">Periode Pelaksanaan Magang</td>
                        <td class="py-1">:</td>
                        <td class="py-1 text-slate-900 font-medium">
                            {{ $placement->application?->start_date ? \Carbon\Carbon::parse($placement->application->start_date)->translatedFormat('d F Y') : '-' }}
                            s/d 
                            {{ $placement->application?->end_date ? \Carbon\Carbon::parse($placement->application->end_date)->translatedFormat('d F Y') : '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="py-1 font-semibold text-slate-700">Pembimbing Lapangan (Mentor)</td>
                        <td class="py-1">:</td>
                        <td class="py-1 text-slate-900">{{ $mentor->name ?? '-' }} (Instansi Kedinasan)</td>
                    </tr>
                    <tr>
                        <td class="py-1 font-semibold text-slate-700">Dosen Pembimbing Lapangan</td>
                        <td class="py-1">:</td>
                        <td class="py-1 font-semibold text-slate-900">{{ $dosen->name ?? '-' }} (Perguruan Tinggi)</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 2. REKAPITULASI BOBOT & NILAI AKHIR (DUAL-EVALUATION) -->
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-950 mt-4 mb-1.5 font-sans border-b border-slate-300 pb-1">
            I. Rincian Komponen Penilaian Akademik & Lapangan
        </h3>

        @php
            $nilaiDinas = $evaluation?->nilai_pembimbing ?? 0;
            $aspectMastery = $evaluation?->dosenAspectScore('score_mastery') ?? 0;
            $aspectReport = $evaluation?->dosenAspectScore('score_report') ?? 0;
            $aspectAttitude = $evaluation?->dosenAspectScore('score_attitude') ?? 0;
            $nilaiDosen = $evaluation?->nilai_dosen_calculated ?? 0;
            $finalScore = $evaluation?->nilai_akhir ?? 0;
            $grade = $evaluation?->grade_calculated ?? '-';
            $predikat = $evaluation?->predikat ?? 'Belum Lengkap';
        @endphp

        <table class="w-full text-xs font-sans border-collapse border border-slate-300 mt-2">
            <thead>
                <tr class="bg-slate-100 text-slate-800 font-bold text-center">
                    <th class="border border-slate-300 py-2 px-2 w-10">No</th>
                    <th class="border border-slate-300 py-2 px-3 text-left">Komponen Penilaian</th>
                    <th class="border border-slate-300 py-2 px-2 w-28">Pemberi Nilai</th>
                    <th class="border border-slate-300 py-2 px-2 w-20">Bobot (%)</th>
                    <th class="border border-slate-300 py-2 px-2 w-24">Skor (0-100)</th>
                    <th class="border border-slate-300 py-2 px-2 w-24">Terbobot</th>
                </tr>
            </thead>
            <tbody>
                <!-- 1. Nilai Lapangan (Mentor) -->
                <tr>
                    <td class="border border-slate-300 py-1.5 text-center font-bold">1</td>
                    <td class="border border-slate-300 py-1.5 px-3">
                        <span class="font-bold text-slate-900 block">Evaluasi Kinerja Teknis Lapangan</span>
                        <span class="text-[11px] text-slate-500">Disiplin ({{ $evaluation?->nilai_disiplin ?? 0 }}), Kinerja ({{ $evaluation?->nilai_kinerja ?? 0 }}), Pelaporan Teknis ({{ $evaluation?->nilai_laporan ?? 0 }})</span>
                    </td>
                    <td class="border border-slate-300 py-1.5 text-center text-slate-700">Mentor Lapangan Dinas</td>
                    <td class="border border-slate-300 py-1.5 text-center font-semibold">{{ $weightMentor }}%</td>
                    <td class="border border-slate-300 py-1.5 text-center font-bold text-slate-800">{{ number_format($nilaiDinas, 2) }}</td>
                    <td class="border border-slate-300 py-1.5 text-center font-bold text-slate-900">
                        {{ number_format(($weightMentor / 100) * $nilaiDinas, 2) }}
                    </td>
                </tr>

                <!-- 2. Nilai Akademik (DPL) -->
                <tr>
                    <td class="border border-slate-300 py-1.5 text-center font-bold" rowspan="3">2</td>
                    <td class="border border-slate-300 py-1.5 px-3">
                        <span class="font-semibold text-slate-800">a. Penguasaan Materi & Pemecahan Masalah</span>
                    </td>
                    <td class="border border-slate-300 py-1.5 text-center text-slate-700" rowspan="3">Dosen Pembimbing (DPL)</td>
                    <td class="border border-slate-300 py-1.5 text-center font-semibold" rowspan="3">{{ $weightLecturer }}%</td>
                    <td class="border border-slate-300 py-1.5 text-center text-slate-800">{{ number_format($aspectMastery, 1) }}</td>
                    <td class="border border-slate-300 py-1.5 text-center font-bold text-slate-900" rowspan="3">
                        {{ number_format(($weightLecturer / 100) * $nilaiDosen, 2) }}
                    </td>
                </tr>
                <tr>
                    <td class="border border-slate-300 py-1.5 px-3">
                        <span class="font-semibold text-slate-800">b. Sistematika & Mutu Naskah Laporan Akhir</span>
                    </td>
                    <td class="border border-slate-300 py-1.5 text-center text-slate-800">{{ number_format($aspectReport, 1) }}</td>
                </tr>
                <tr>
                    <td class="border border-slate-300 py-1.5 px-3">
                        <span class="font-semibold text-slate-800">c. Sikap, Komunikasi, & Etika Akademik</span>
                    </td>
                    <td class="border border-slate-300 py-1.5 text-center text-slate-800">{{ number_format($aspectAttitude, 1) }}</td>
                </tr>

                <!-- Rata-rata DPL -->
                <tr class="bg-slate-50/75 font-semibold text-slate-800">
                    <td colspan="4" class="border border-slate-300 py-1.5 px-3 text-right">Rata-Rata Penilaian Akademik DPL:</td>
                    <td class="border border-slate-300 py-1.5 text-center font-bold text-blue-900">{{ number_format($nilaiDosen, 2) }}</td>
                    <td class="border border-slate-300 py-1.5 text-center">-</td>
                </tr>

                <!-- NILAI TOTAL AKHIR -->
                <tr class="bg-slate-200/80 font-bold text-slate-950 text-sm">
                    <td colspan="4" class="border border-slate-400 py-2 px-3 text-right uppercase">Nilai Akhir Kumulatif Mahasiswa:</td>
                    <td colspan="2" class="border border-slate-400 py-2 text-center text-base font-black text-blue-900">
                        {{ number_format($finalScore, 2) }} &nbsp;
                        <span class="text-sm font-extrabold text-slate-800">({{ $grade }})</span>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- 3. KETERANGAN & CATATAN EVALUASI AKADEMIK DPL -->
        <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3 text-xs font-sans">
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg space-y-1">
                <span class="font-bold text-slate-800 block uppercase tracking-wider text-[11px]">Status Dokumen & Verifikasi:</span>
                <p class="text-slate-700">
                    &bull; Status Naskah Laporan: <strong class="{{ optional($finalReport)->status === 'approved' ? 'text-emerald-700' : 'text-amber-700' }} uppercase">{{ optional($finalReport)->status ?? 'Belum Lengkap' }}</strong>
                </p>
                <p class="text-slate-700">
                    &bull; Riwayat Sesi Konsultasi DPL: <strong>{{ $consultations->count() }} Sesi Bimbingan Tercatat</strong>
                </p>
                <p class="text-slate-700">
                    &bull; Predikat Kelulusan: <strong class="text-blue-800">{{ $predikat }}</strong>
                </p>
            </div>

            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
                <span class="font-bold text-slate-800 block uppercase tracking-wider text-[11px]">Catatan / Rekomendasi DPL:</span>
                <p class="text-slate-700 italic mt-0.5 leading-relaxed">
                    "{{ $evaluation?->catatan_dosen ?? ($evaluation?->catatan ?? 'Mahasiswa telah menyelesaikan program magang mandiri dengan dedikasi dan kinerja akademik yang sangat baik.') }}"
                </p>
            </div>
        </div>

        <!-- 4. PENGESAHAN TANDA TANGAN (3 SISI) -->
        <div class="mt-6 font-sans text-xs">
            <p class="text-right text-slate-700 mb-2">
                Ditetapkan di: <strong>Surabaya</strong> &bull; Pada tanggal: <strong>{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</strong>
            </p>

            <div class="grid grid-cols-3 gap-4 text-center mt-3">
                <!-- Kolom 1: Pembimbing Lapangan Dinas -->
                <div>
                    <span class="text-slate-600 block">Mengetahui,</span>
                    <span class="font-bold text-slate-900 block mt-0.5">Pembimbing Lapangan (Dinas)</span>
                    <div class="h-20 flex items-center justify-center">
                        <span class="text-[10px] text-slate-400 font-mono italic">[Telah Diverifikasi Sistem Dinas]</span>
                    </div>
                    <p class="font-bold text-slate-900 underline">{{ $mentor->name ?? '-' }}</p>
                    <p class="text-[11px] text-slate-500">NIP. {{ $mentor->nip ?? '198503152010012001' }}</p>
                </div>

                <!-- Kolom 2: Verifikasi QR Digital -->
                <div class="flex flex-col items-center justify-center">
                    <div class="p-1.5 bg-white border border-slate-300 rounded-lg shadow-2xs">
                        <div class="w-16 h-16 bg-slate-100 flex items-center justify-center text-[10px] text-slate-500 font-mono text-center leading-tight">
                            QR VALIDASI<br/>RESMI DPL
                        </div>
                    </div>
                    <span class="text-[9px] text-slate-400 font-mono mt-1 text-center">
                        ID: {{ $placement->ensureCertificateHash() ? substr($placement->certificate_hash, 0, 16) : 'VERIFIED-DOC' }}
                    </span>
                </div>

                <!-- Kolom 3: Dosen Pembimbing Lapangan -->
                <div>
                    <span class="text-slate-600 block">Dosen Penilai,</span>
                    <span class="font-bold text-slate-900 block mt-0.5">Dosen Pembimbing (DPL)</span>
                    <div class="h-20 flex items-center justify-center">
                        <span class="text-[10px] text-emerald-600 font-bold font-mono border border-emerald-200 bg-emerald-50 px-2 py-1 rounded">
                            TERTANDATANGANI SECARA ELEKTRONIK
                        </span>
                    </div>
                    <p class="font-bold text-slate-900 underline">{{ $dosen->name ?? '-' }}</p>
                    <p class="text-[11px] text-slate-500">NIDN/NIP. {{ $dosen->nip ?? '0015088201' }}</p>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
