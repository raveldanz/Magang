<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Tugas Magang - {{ $student->name }} ({{ $profile->nim ?? 'NIM' }})</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Times+New+Roman&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 20mm 15mm 20mm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background-color: #f8fafc;
            line-height: 1.45;
        }

        .paper-container {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm 20mm 20mm 20mm;
            margin: 20px auto;
            background: #ffffff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            box-sizing: border-box;
            position: relative;
        }

        .kop-border {
            border-bottom: 3px solid #000;
            position: relative;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        .kop-border::after {
            content: "";
            display: block;
            border-bottom: 1px solid #000;
            margin-top: 2px;
        }

        .topbar-wrapper {
            background-color: #0f172a !important;
            color: #ffffff !important;
            padding: 12px 24px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            position: sticky !important;
            top: 0 !important;
            z-index: 9999 !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2) !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif !important;
        }

        .btn-back {
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            padding: 8px 16px !important;
            background-color: #1e293b !important;
            color: #f8fafc !important;
            border: 1px solid #475569 !important;
            border-radius: 8px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            text-decoration: none !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
        }

        .btn-back:hover {
            background-color: #334155 !important;
            color: #ffffff !important;
        }

        .btn-print {
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            padding: 8px 20px !important;
            background-color: #4f46e5 !important;
            color: #ffffff !important;
            border: none !important;
            border-radius: 8px !important;
            font-size: 13px !important;
            font-weight: 700 !important;
            cursor: pointer !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2) !important;
            transition: all 0.2s ease !important;
        }

        .btn-print:hover {
            background-color: #4338ca !important;
        }

        @media print {
            body {
                background: none;
                margin: 0;
                padding: 0;
            }

            .no-print, .topbar-wrapper {
                display: none !important;
            }

            .paper-container {
                width: 100%;
                min-height: auto;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body class="antialiased">

    <!-- Action Bar (Hidden when Printing) -->
    <div class="topbar-wrapper no-print">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="{{ route('university.dashboard') }}" class="btn-back">
                <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Dashboard</span>
            </a>
            <span style="font-size: 12px; color: #94a3b8; border-left: 1px solid #334155; padding-left: 12px;">
                Dokumen Resmi: <strong style="color: #ffffff;">Surat Tugas Pengantar Magang MBKM</strong>
            </span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <button onclick="window.print()" class="btn-print">
                <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Surat / Simpan PDF</span>
            </button>
        </div>
    </div>

    <!-- Paper Container A4 -->
    <div class="paper-container">

        <!-- 1. KOP SURAT RESMI UNIVERSITAS -->
        <div class="flex items-center justify-between gap-4 pb-2">
            <!-- Logo Kampus (Kiri) -->
            <div class="w-24 h-24 flex items-center justify-center shrink-0">
                @if ($university?->logo && file_exists(public_path($university->logo)))
                    <img src="{{ asset($university->logo) }}" alt="Logo {{ $university->name }}" class="max-h-24 max-w-24 object-contain">
                @elseif (file_exists(public_path('images/logos/unitomo.png')))
                    <img src="{{ asset('images/logos/unitomo.png') }}" alt="Logo Kampus" class="max-h-24 max-w-24 object-contain">
                @else
                    <div class="w-20 h-20 border-2 border-black rounded-full flex items-center justify-center text-2xl font-bold font-sans">
                        🏛️
                    </div>
                @endif
            </div>

            <!-- Teks Header Kop -->
            <div class="text-center flex-1">
                <p class="text-xs uppercase tracking-widest font-bold font-sans text-gray-700">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</p>
                <h1 class="text-lg sm:text-xl font-bold uppercase tracking-tight text-black leading-tight mt-0.5">
                    {{ $university->name ?? 'UNIVERSITAS DR. SOETOMO' }}
                </h1>
                @if ($university?->address)
                    <p class="text-[11px] text-gray-800 mt-1 leading-tight">{{ $university->address }}</p>
                @else
                    <p class="text-[11px] text-gray-800 mt-1 leading-tight">Jl. Semolowaru No. 84, Menur Pumpungan, Kec. Sukolilo, Kota Surabaya, Jawa Timur 60118</p>
                @endif
                <p class="text-[10px] text-gray-700 mt-0.5">
                    Telepon: {{ $university->phone ?? '(031) 5925970' }} | Email: {{ $university->email ?? 'info@unitomo.ac.id' }}
                </p>
            </div>
        </div>

        <!-- Garis Ganda Pembatas Kop -->
        <div class="kop-border"></div>

        <!-- 2. NOMOR SURAT & PERIHAL -->
        <div class="flex justify-between items-start text-[12px] mb-6">
            <div class="space-y-1">
                <table class="text-[12px]">
                    <tr>
                        <td class="w-20 font-semibold">Nomor</td>
                        <td class="w-3">:</td>
                        <td>{{ $application->id }}/UN.{{ $university->code ?? 'KMP' }}/KM-MAGANG/{{ date('Y') }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold">Lampiran</td>
                        <td>:</td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td class="font-semibold align-top">Perihal</td>
                        <td class="align-top">:</td>
                        <td class="font-bold">Surat Tugas / Pengantar Pelaksanaan Magang MBKM</td>
                    </tr>
                </table>
            </div>

            <div class="text-right text-[12px]">
                <p>Surabaya, {{ Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            </div>
        </div>

        <!-- 3. TUJUAN SURAT -->
        <div class="text-[12px] mb-5">
            <p>Kepada Yth.</p>
            <p class="font-bold">Kepala {{ $agency?->agency_name ?? 'Dinas Komunikasi dan Informatika' }}</p>
            <p>{{ $agency?->government_name ?? 'Pemerintah Kota Surabaya' }}</p>
            <p class="mt-0.5">di - <span class="underline">Tempat</span></p>
        </div>

        <!-- 4. ISI PEMBUKA SURAT -->
        <div class="text-[12px] text-justify space-y-3 mb-4">
            <p>
                Dengan hormat,<br>
                Sehubungan dengan program Merdeka Belajar Kampus Merdeka (MBKM) dan peningkatan kompetensi praktis mahasiswa di dunia kerja profesional, bersama ini Pimpinan <strong>{{ $university->name ?? 'Perguruan Tinggi' }}</strong> memberikan tugas dan merekomendasikan mahasiswa berikut untuk melaksanakan Praktik Kerja Lapangan / Magang:
            </p>
        </div>

        <!-- 5. TABEL IDENTITAS MAHASISWA -->
        <div class="mb-4 pl-4">
            <table class="text-[12px] w-full border-collapse">
                <tr class="py-1">
                    <td class="w-44 py-1 font-semibold">Nama Lengkap Mahasiswa</td>
                    <td class="w-3 py-1">:</td>
                    <td class="py-1 font-bold text-black uppercase">{{ $student->name }}</td>
                </tr>
                <tr class="py-1">
                    <td class="py-1 font-semibold">Nomor Induk Mahasiswa (NIM)</td>
                    <td class="py-1">:</td>
                    <td class="py-1 font-mono font-bold">{{ $profile?->nim ?? '-' }}</td>
                </tr>
                <tr class="py-1">
                    <td class="py-1 font-semibold">Fakultas / Program Studi</td>
                    <td class="py-1">:</td>
                    <td class="py-1">{{ $profile?->fakultas ?? 'Teknik' }} / {{ $profile?->jurusan ?? 'Informatika' }}</td>
                </tr>
                <tr class="py-1">
                    <td class="py-1 font-semibold">Nomor Telepon / WhatsApp</td>
                    <td class="py-1">:</td>
                    <td class="py-1">{{ $profile?->no_hp ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <!-- 6. DETAIL PENEMPATAN & PEMBIMBING -->
        <div class="text-[12px] text-justify space-y-3 mb-4">
            <p>
                Mahasiswa yang bersangkutan telah diterima dan ditugaskan untuk menjalankan kegiatan magang dengan rincian sebagai berikut:
            </p>
        </div>

        <div class="mb-5 pl-4">
            <table class="text-[12px] w-full border-collapse">
                <tr class="py-1">
                    <td class="w-44 py-1 font-semibold">Instansi Penempatan</td>
                    <td class="w-3 py-1">:</td>
                    <td class="py-1 font-bold">{{ $agency?->agency_name ?? 'Pemerintah Kota Surabaya' }}</td>
                </tr>
                <tr class="py-1">
                    <td class="py-1 font-semibold">Unit / Bidang Kerja</td>
                    <td class="py-1">:</td>
                    <td class="py-1">{{ $unit?->name ?? 'Bidang Terkait' }}</td>
                </tr>
                <tr class="py-1">
                    <td class="py-1 font-semibold">Dosen Pembimbing (DPL)</td>
                    <td class="py-1">:</td>
                    <td class="py-1 font-semibold">{{ $dosen?->name ?? 'Dosen Pembimbing Lapangan Terdaftar' }}</td>
                </tr>
                <tr class="py-1">
                    <td class="py-1 font-semibold">Pembimbing Lapangan (Mentor)</td>
                    <td class="py-1">:</td>
                    <td class="py-1 font-semibold">{{ $mentor?->name ?? 'Mentor Instansi Dinas' }}</td>
                </tr>
                <tr class="py-1">
                    <td class="py-1 font-semibold">Periode Pelaksanaan</td>
                    <td class="py-1">:</td>
                    <td class="py-1 font-bold">
                        @if ($application->start_date && $application->end_date)
                            {{ Carbon\Carbon::parse($application->start_date)->translatedFormat('d F Y') }} s.d. {{ Carbon\Carbon::parse($application->end_date)->translatedFormat('d F Y') }}
                        @else
                            Semester Ganjil / Genap Tahun Akademik {{ date('Y') }}/{{ date('Y')+1 }}
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- 7. PENUTUP SURAT -->
        <div class="text-[12px] text-justify space-y-2 mb-8">
            <p>
                Demikian surat tugas ini kami sampaikan. Kami mengucapkan terima kasih yang sebesar-besarnya atas kesempatan, bimbingan, dan kerja sama yang telah diberikan oleh jajaran Pemerintah Kota Surabaya kepada mahasiswa kami.
            </p>
        </div>

        <!-- 8. TANDA TANGAN PEJABAT RESMI KAMPUS -->
        <div class="flex justify-end text-[12px] pt-4">
            <div class="text-left w-72 space-y-1">
                <p>Surabaya, {{ Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p class="font-bold">{{ $university->pic_position ?? 'Rektor Universitas Dr. Soetomo' }}</p>
                
                <!-- Spasi Tanda Tangan / Stempel -->
                <div class="h-20 flex items-center">
                    <span class="text-[10px] text-gray-300 font-sans italic border border-dashed border-gray-200 px-3 py-1 rounded">
                        [ Tanda Tangan & Cap Resmi ]
                    </span>
                </div>

                <p class="font-bold text-black underline uppercase">{{ $university->pic_name ?? 'Dr. Siti Marwiyah, S.H., M.H.' }}</p>
                <p class="text-[11px] font-mono">NIP. {{ $university->pic_nip ?? '196808281993032001' }}</p>
            </div>
        </div>

    </div>

</body>
</html>
