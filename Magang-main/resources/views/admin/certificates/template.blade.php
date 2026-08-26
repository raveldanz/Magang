<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sertifikat Magang - {{ $name }}</title>
    <style>
        @page {
            margin: 0px; /* Reset margin page agar posisi fixed akurat di ujung kertas */
            size: A4 landscape;
        }
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 30px 40px; /* Padding untuk konten */
            color: #334155;
            text-align: center;
        }
        
        /* Elemen Fixed: Akan selalu muncul secara konsisten (posisi sama persis) di Halaman 1 & Halaman 2 */
        .page-border {
            position: fixed;
            top: 20px; left: 20px; right: 20px; bottom: 20px;
            border: 1px solid #94a3b8;
            z-index: -10;
        }
        .page-border-inner {
            position: fixed;
            top: 26px; left: 26px; right: 26px; bottom: 26px;
            border: 1px solid #cbd5e1;
            z-index: -10;
        }

        /* Warna Lembut & Senada: Calm Blue (#0284c7) dan Soft Cyan (#38bdf8) */
        .curve-tl-main { 
            position: fixed; 
            top: -200px; left: -150px; 
            width: 450px; height: 450px; 
            background: #0284c7; 
            border-radius: 50%; 
            z-index: -9;
            opacity: 0.95;
        }
        .curve-br-main { 
            position: fixed; 
            bottom: -250px; right: -150px; 
            width: 550px; height: 550px; 
            background: #38bdf8; 
            border-radius: 50%; 
            z-index: -9; 
            opacity: 0.85;
        }

        /* Konten Halaman */
        .page-content {
            position: relative;
            z-index: 10;
            height: 100%;
            width: 100%;
        }

        .header-logo {
            position: absolute;
            top: -10px;
            left: 5px;
            width: 90px;
        }
        .header-logo img {
            max-height: 90px;
            max-width: 85px;
            background: #ffffff;
            padding: 6px;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            object-fit: contain;
        }

        .title-area {
            padding-top: 30px;
            margin-bottom: 12px;
        }
        
        /* Judul Sertifikat (Warna lembut tapi tegas) */
        .title-box {
            display: inline-block;
            font-family: 'Arial Black', 'Arial', sans-serif;
            font-size: 38px;
            font-weight: bold;
            letter-spacing: 10px;
            color: #1e293b;
            padding: 6px 35px;
            border-top: 3px solid #0284c7;
            border-bottom: 3px solid #0284c7;
            text-transform: uppercase;
        }

        .subtitle {
            font-size: 15px;
            color: #64748b;
            margin-bottom: 16px;
            letter-spacing: 1px;
        }

        /* Banner Nama Terpusat Sempurna - Gaya Elegan Senada */
        .name-box {
            display: inline-block;
            background: #f8fafc;
            color: #0f172a;
            font-family: 'Times New Roman', Times, serif;
            font-size: 34px;
            font-style: italic;
            font-weight: bold;
            padding: 8px 50px;
            margin-bottom: 18px;
            letter-spacing: 1.5px;
            border: 1px solid #e2e8f0;
            border-left: 8px solid #0284c7;
            border-right: 8px solid #0284c7;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .description {
            font-size: 15px;
            line-height: 1.5;
            color: #475569;
            padding: 0 80px;
            margin-bottom: 16px;
        }
        .highlight {
            font-weight: bold;
            color: #1e293b;
        }

        /* Tabel Tanda Tangan */
        table.footer-table {
            width: 100%;
            margin-top: 5px;
            border-collapse: collapse;
        }
        table.footer-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 14px;
        }
        .sig-line {
            width: 240px;
            border-bottom: 1px solid #64748b;
            margin: 45px auto 4px auto;
        }
        .sig-name { font-weight: bold; font-size: 15px; color: #1e293b; }
        .sig-title { font-size: 13px; color: #64748b; }

        /* Legalitas BSrE Footer */
        .bsre-legal-note {
            margin-top: 18px;
            text-align: center;
            font-size: 9.5px;
            color: #64748b;
            font-family: Arial, sans-serif;
            line-height: 1.3;
        }

        /* Halaman Kedua (Nilai) */
        .page-break {
            page-break-before: always;
        }
        
        .watermark {
            position: absolute;
            top: 25%;
            left: 35%;
            width: 30%;
            opacity: 0.06;
            z-index: -1;
        }

        .grade-content {
            padding-top: 40px;
        }
        .grade-title {
            font-size: 26px;
            font-weight: bold;
            color: #0284c7;
            margin-bottom: 10px;
            letter-spacing: 2px;
        }
        .grade-subtitle {
            font-size: 15px;
            color: #64748b;
            margin-bottom: 35px;
        }
        
        table.grade-table {
            width: 85%;
            margin: 0 auto;
            border-collapse: collapse;
            font-size: 16px;
            color: #1e293b;
        }
        table.grade-table th, table.grade-table td {
            border: 1px solid #cbd5e1;
            padding: 14px 18px;
            text-align: left;
        }
        table.grade-table th {
            background-color: #0284c7;
            color: #ffffff;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 1px;
        }
        table.grade-table td.score {
            text-align: center;
            font-weight: bold;
            font-size: 22px;
            color: #0284c7;
        }
    </style>
</head>
<body>

    <!-- ELEMEN FIXED: Muncul di semua halaman -->
    <div class="page-border"></div>
    <div class="page-border-inner"></div>
    <div class="curve-tl-main"></div>
    <div class="curve-br-main"></div>

    @php
        // 1. Logo Utama Instansi Dinamis Berbasis Base64 (Untuk DOMPDF)
        $logoPath = null;
        if (!empty($agencyProfile?->logo) && file_exists(storage_path('app/public/' . $agencyProfile->logo))) {
            $logoPath = storage_path('app/public/' . $agencyProfile->logo);
        } elseif (!empty($agencyProfile?->logo) && file_exists(public_path('storage/' . $agencyProfile->logo))) {
            $logoPath = public_path('storage/' . $agencyProfile->logo);
        } elseif (file_exists(public_path('images/logo-surabaya.png'))) {
            $logoPath = public_path('images/logo-surabaya.png');
        } elseif (file_exists(public_path('images/logo.png'))) {
            $logoPath = public_path('images/logo.png');
        }

        $logoData = $logoPath ? @file_get_contents($logoPath) : '';
        $mime = ($logoPath && function_exists('mime_content_type')) ? (@mime_content_type($logoPath) ?: 'image/png') : 'image/png';
        $logoSrc = $logoData ? 'data:' . $mime . ';base64,' . base64_encode($logoData) : '';

        // 2. QR Code Verifikasi TTE URL
        $verifyUrl = route('verify.certificate', $placement->id ?? 1);
        $qrBase64 = '';
        if (class_exists('SimpleSoftwareIO\QrCode\Facades\QrCode')) {
            $qrBase64 = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(80)->generate($verifyUrl));
        } else {
            // Fallback generation via secure QR API base64
            $qrApi = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' . urlencode($verifyUrl);
            $rawQr = @file_get_contents($qrApi);
            if ($rawQr) {
                $qrBase64 = base64_encode($rawQr);
            }
        }
    @endphp

    <!-- HALAMAN 1 : SERTIFIKAT UTAMA -->
    <div class="page-content">
        <div class="header-logo">
            @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="Logo Instansi" style="max-height: 90px; object-fit: contain;">
            @else
                <div style="font-size:12px; color:#999; border:1px solid #ccc; padding:20px; width:80px; background:#fff; border-radius:50%;">[LOGO]</div>
            @endif
        </div>

        <div class="title-area">
            <div class="title-box">SERTIFIKAT</div>
        </div>
        
        <div class="subtitle">Diberikan secara resmi kepada:</div>
        
        <div class="name-box">
            {{ $name }}
        </div>

        <div class="description">
            Telah menyelesaikan <span class="highlight">Program Magang / Praktik Kerja Lapangan (PKL)</span><br>
            pada unit kerja <span class="highlight">{{ $unit }}</span><br>
            dengan penuh dedikasi yang diselenggarakan pada periode<br>
            <span class="highlight">{{ $start_date }}</span> sampai dengan <span class="highlight">{{ $end_date }}</span>.
        </div>

        <!-- Area Tanda Tangan Elektronik (TTE BSrE) -->
        <table class="footer-table">
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%; text-align: center;">
                    <div style="font-size: 13.5px; line-height: 1.3;">
                        <span style="color: #64748b; font-size: 12.5px;">Ditandatangani secara elektronik oleh:</span><br>
                        <strong style="color: #0f172a; text-transform: uppercase;">{{ $agencyProfile->signee_position ?? 'KEPALA DINAS KOMUNIKASI DAN INFORMATIKA' }}</strong><br>
                        
                        <!-- QR Code Verifikasi TTE -->
                        <div style="margin: 6px 0;">
                            @if($qrBase64)
                                <img src="data:image/png;base64,{{ $qrBase64 }}" alt="QR Code Verifikasi TTE" style="width: 75px; height: 75px; display: inline-block;">
                            @else
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($verifyUrl) }}" alt="QR Code Verifikasi TTE" style="width: 75px; height: 75px; display: inline-block;">
                            @endif
                        </div>

                        <div style="font-weight: bold; color: #0f172a; font-size: 14.5px;">
                            <u>{{ $agencyProfile->signee_name ?? 'Drs. H. M. NASER, M.Si' }}</u>
                        </div>
                        <div style="font-size: 12px; color: #475569; margin-top: 1px;">
                            NIP. {{ $agencyProfile->signee_nip ?? '19700101 199503 1 002' }}
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Footer Legalitas BSrE Standar BSSN -->
        <div class="bsre-legal-note">
            <em>Dokumen ini telah ditandatangani secara elektronik menggunakan sertifikat elektronik yang diterbitkan oleh Balai Sertifikasi Elektronik (BSrE), BSSN.</em>
        </div>
    </div>

    <!-- HALAMAN 2 : TRANSKRIP NILAI -->
    <div class="page-break"></div>
    
    <div class="page-content">
        @if($logoSrc)
            <img src="{{ $logoSrc }}" class="watermark" alt="Watermark Logo">
        @endif

        <div class="grade-content">
            <div class="grade-title">TRANSKRIP NILAI MAGANG</div>
            <div class="grade-subtitle">Nomor Registrasi: {{ strtoupper(substr(md5($name.$nim), 0, 10)) }}</div>
            
            <table class="grade-table">
                <thead>
                    <tr>
                        <th style="width: 70%;">Komponen Penilaian</th>
                        <th style="width: 30%;">Nilai / Predikat</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Nilai Rata-rata Kumulatif Akhir <br><span style="font-size:13px; color:#64748b;">(Berdasarkan evaluasi dari Pembimbing Lapangan Instansi)</span></td>
                        <td class="score">{{ $rataRata }}</td>
                    </tr>
                    <tr>
                        <td>Kategori Predikat Kelulusan</td>
                        <td class="score">{{ strtoupper($grade) }}</td>
                    </tr>
                    <tr>
                        <td>Status Laporan Akhir Magang</td>
                        <td class="score">DITERIMA</td>
                    </tr>
                </tbody>
            </table>

            <table class="footer-table" style="margin-top: 50px;">
                <tr>
                    <td style="width: 50%;"></td>
                    <td style="width: 50%; text-align: center;">
                        <span style="font-size: 13.5px; color: #64748b;">Surabaya, {{ $date_issued }}</span><br>
                        <strong style="color: #0f172a;">Pembimbing Lapangan / Mentor</strong>
                        <div class="sig-line" style="margin: 50px auto 4px auto;"></div>
                        <div class="sig-name">{{ $pembimbing->name ?? 'Retno Mumpuni, S.Kom., M.Sc' }}</div>
                        <div class="sig-title">NIP. {{ $pembimbing->studentProfile->nim ?? $pembimbing->phone ?? '198001012010012001' }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</body>
</html>
