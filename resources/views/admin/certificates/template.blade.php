<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sertifikat Magang</title>
    <style>
        @page {
            margin: 0px; /* Reset margin page agar posisi fixed akurat di ujung kertas */
            size: A4 landscape;
        }
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 40px; /* Padding untuk konten */
            color: #334155;
            text-align: center;
        }
        
        /* Elemen Fixed: Akan selalu muncul secara konsisten (posisi sama persis) di Halaman 1 & Halaman 2 */
        .page-border {
            position: fixed;
            top: 25px; left: 25px; right: 25px; bottom: 25px;
            border: 1px solid #94a3b8;
            z-index: -10;
        }
        .page-border-inner {
            position: fixed;
            top: 32px; left: 32px; right: 32px; bottom: 32px;
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
            top: 0px;
            left: 0px;
            width: 100px;
        }
        .header-logo img {
            width: 90px;
            background: #ffffff;
            padding: 8px;
            border-radius: 50%;
        }

        .title-area {
            padding-top: 60px;
            margin-bottom: 25px;
        }
        
        /* Judul Sertifikat (Warna lembut tapi tegas) */
        .title-box {
            display: inline-block;
            font-family: 'Arial Black', 'Arial', sans-serif;
            font-size: 45px;
            font-weight: bold;
            letter-spacing: 12px;
            color: #1e293b;
            padding: 10px 40px;
            border-top: 3px solid #0284c7;
            border-bottom: 3px solid #0284c7;
            text-transform: uppercase;
        }

        .subtitle {
            font-size: 18px;
            color: #64748b;
            margin-bottom: 35px;
            letter-spacing: 1px;
        }

        /* Banner Nama Terpusat Sempurna - Gaya Elegan Senada */
        .name-box {
            display: inline-block;
            background: #f8fafc;
            color: #0f172a;
            font-family: 'Times New Roman', Times, serif;
            font-size: 42px;
            font-style: italic;
            font-weight: bold;
            padding: 15px 70px;
            margin-bottom: 40px;
            letter-spacing: 2px;
            border: 1px solid #e2e8f0;
            border-left: 8px solid #0284c7;
            border-right: 8px solid #0284c7;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .description {
            font-size: 18px;
            line-height: 1.8;
            color: #475569;
            padding: 0 100px;
            margin-bottom: 40px;
        }
        .highlight {
            font-weight: bold;
            color: #1e293b;
        }

        /* Tabel Tanda Tangan */
        table.footer-table {
            width: 100%;
            margin-top: 20px;
        }
        table.footer-table td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            font-size: 16px;
        }
        .sig-line {
            width: 250px;
            border-bottom: 1px solid #64748b;
            margin: 60px auto 5px auto;
        }
        .sig-name { font-weight: bold; font-size: 16px; color: #1e293b; }
        .sig-title { font-size: 14px; color: #64748b; }

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
            padding-top: 60px;
        }
        .grade-title {
            font-size: 30px;
            font-weight: bold;
            color: #0284c7;
            margin-bottom: 15px;
            letter-spacing: 2px;
        }
        .grade-subtitle {
            font-size: 16px;
            color: #64748b;
            margin-bottom: 50px;
        }
        
        table.grade-table {
            width: 85%;
            margin: 0 auto;
            border-collapse: collapse;
            font-size: 18px;
            color: #1e293b;
        }
        table.grade-table th, table.grade-table td {
            border: 1px solid #cbd5e1;
            padding: 18px;
            text-align: left;
        }
        table.grade-table th {
            background-color: #0284c7;
            color: #ffffff;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 1px;
        }
        table.grade-table td.score {
            text-align: center;
            font-weight: bold;
            font-size: 24px;
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
        $imagePath = public_path('images/logo.png');
        $imageData = '';
        $mime = 'image/png';
        if(file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $mime = mime_content_type($imagePath) ?: 'image/png';
        }
    @endphp

    <!-- HALAMAN 1 : SERTIFIKAT UTAMA -->
    <div class="page-content">
        <div class="header-logo">
            @if($imageData)
                <img src="data:{{ $mime }};base64,{{ $imageData }}" alt="Logo">
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
            di instansi <span class="highlight">{{ $unit }}</span><br>
            dengan penuh dedikasi yang diselenggarakan pada periode<br>
            <span class="highlight">{{ $start_date }}</span> sampai dengan <span class="highlight">{{ $end_date }}</span>.
        </div>

        <table class="footer-table">
            <tr>
                <td></td>
                <td>
                    <span style="font-size: 14px; color: #64748b;">Ditandatangani secara elektronik oleh:</span><br>
                    <strong style="color: #0f172a;">Koordinator Program SIP Magang</strong>
                    <div class="sig-line"></div>
                    <div class="sig-name">Admin SIP Magang</div>
                    <div class="sig-title">Pemerintah Kota Surabaya</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- HALAMAN 2 : PENILAIAN -->
    <div class="page-break"></div>
    
    <div class="page-content">
        @if($imageData)
            <img src="data:{{ $mime }};base64,{{ $imageData }}" class="watermark" alt="Watermark Logo">
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
                        <td>Nilai Rata-rata Kumulatif Akhir <br><span style="font-size:14px; color:#64748b;">(Berdasarkan evaluasi dari Pembimbing Lapangan Instansi)</span></td>
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

            <table class="footer-table" style="margin-top: 80px;">
                <tr>
                    <td></td>
                    <td>
                        <span style="font-size: 14px; color: #64748b;">Surabaya, {{ $date_issued }}</span><br>
                        <strong style="color: #0f172a;">Pembimbing Lapangan / Mentor</strong>
                        <div class="sig-line"></div>
                        <div class="sig-name">Retno Mumpuni</div>
                        <div class="sig-title">NIP. 198001012010012001</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</body>
</html>
