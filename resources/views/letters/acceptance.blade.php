<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Penerimaan Magang - {{ $application->user->name }}</title>
    <style>
        @page {
            size: A4;
            margin: 12mm 18mm 12mm 18mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10.5pt;
            line-height: 1.36;
            color: #000;
            background-color: #f3f4f6;
            margin: 0;
            padding: 20px 0;
        }

        .no-print {
            max-width: 210mm;
            margin: 0 auto 16px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 16px;
            background-color: #1e293b;
            color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .btn-group {
            display: flex;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: background-color 0.15s ease-in-out;
        }

        .btn-primary {
            background-color: #2563eb;
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
        }

        .btn-secondary {
            background-color: #475569;
            color: #ffffff;
        }

        .btn-secondary:hover {
            background-color: #334155;
        }

        /* Container Lembar Kertas A4 */
        .page-container {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #ffffff;
            padding: 12mm 18mm 10mm 18mm;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            position: relative;
        }

        /* Kop Surat Resmi */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
        }

        .kop-logo {
            width: 65px;
            vertical-align: middle;
            text-align: center;
            padding-right: 12px;
        }

        .kop-logo img {
            max-width: 65px;
            max-height: 80px;
            width: auto;
            height: auto;
            object-fit: contain;
        }

        .kop-text {
            vertical-align: middle;
            text-align: center;
        }

        .kop-instansi-1 {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
            line-height: 1.15;
        }

        .kop-instansi-2 {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin: 2px 0 0 0;
            line-height: 1.2;
        }

        .kop-alamat {
            font-size: 9pt;
            margin: 3px 0 0 0;
            line-height: 1.25;
        }

        .kop-kontak {
            font-size: 8.5pt;
            margin: 1px 0 0 0;
            line-height: 1.25;
        }

        /* Garis Ganda Pemisah Kop */
        .kop-divider {
            border-top: 2.5px solid #000;
            border-bottom: 0.75px solid #000;
            height: 2px;
            margin-top: 5px;
            margin-bottom: 10px;
        }

        /* Tabel Atribut Surat */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 10.5pt;
        }

        .meta-table td {
            vertical-align: top;
            padding: 1px 0;
        }

        /* Tujuan Surat */
        .recipient-box {
            margin-bottom: 9px;
            font-size: 10.5pt;
            line-height: 1.3;
        }

        /* Konten Paragraf */
        .content-body {
            font-size: 10.5pt;
            text-align: justify;
            line-height: 1.35;
        }

        .content-body p {
            margin: 0 0 7px 0;
        }

        /* Tabel Data Mahasiswa */
        .student-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0 8px 0;
            font-size: 10pt;
        }

        .student-table th,
        .student-table td {
            border: 1px solid #000;
            padding: 3px 6px;
            vertical-align: middle;
        }

        .student-table th {
            background-color: #f8fafc;
            font-weight: bold;
            text-align: center;
        }

        /* Box Tanda Tangan Elektronik (TTE) Standar Dispusip / Pemkot */
        .tte-container {
            float: right;
            width: 340px;
            border: 1px solid #333333;
            padding: 6px 10px;
            margin-top: 6px;
            background-color: #ffffff;
        }

        .tte-table {
            width: 100%;
            border-collapse: collapse;
        }

        .tte-qr-cell {
            width: 70px;
            vertical-align: middle;
            text-align: center;
            padding-right: 8px;
        }

        .tte-qr-img {
            width: 65px;
            height: 65px;
            display: block;
            margin: 0 auto;
        }

        .tte-text-cell {
            vertical-align: middle;
            font-size: 8.5pt;
            line-height: 1.25;
            font-family: 'Times New Roman', Times, serif;
        }

        .clear-fix {
            clear: both;
        }

        /* Footer Legalitas BSrE Identik */
        .bsre-footer-wrapper {
            margin-top: 14px;
            padding-top: 0;
        }

        .bsre-divider-line {
            width: 100%;
            border-top: 1px solid #000000;
            margin-bottom: 5px;
        }

        .bsre-footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .bsre-logo-col {
            width: 135px;
            vertical-align: middle;
            padding-right: 10px;
        }

        .bsre-logo-img {
            height: 36px;
            width: auto;
            max-width: 135px;
            display: block;
            object-fit: contain;
        }

        .bsre-text-col {
            vertical-align: middle;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7.5pt;
            color: #000000;
            line-height: 1.35;
        }

        .bsre-quote {
            padding-left: 8px;
        }

        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
                margin: 0;
            }

            .page-container {
                width: 100%;
                min-height: auto;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    @php
        // Mengambil profil instansi dinamis dari unit magang atau default
        $agencyProfile = $application->unit->agencyProfile ?? $agencyProfile ?? \App\Models\AgencyProfile::first();
        $govName = $agencyProfile->government_name ?? 'Pemerintah Kota Surabaya';
        $agencyName = $agencyProfile->agency_name ?? 'Dinas Komunikasi Dan Informatika';
        $address = $agencyProfile->address ?? 'Jl. Jimerto No. 25-27, Ketabang, Genteng, Kota Surabaya, Jawa Timur 60272';
        $phone = $agencyProfile->phone ?? '(031) 5312144';
        $email = $agencyProfile->email ?? 'diskominfo@surabaya.go.id';
        $website = $agencyProfile->website ?? 'surabaya.go.id';
        $cityName = $agencyProfile->city ?? 'Surabaya';
        $signeeName = $agencyProfile->signee_name ?? 'Drs. H. M. NASER, M.Si';
        $signeeNip = $agencyProfile->signee_nip ?? '19700101 199503 1 002';
        $signeePosition = $agencyProfile->signee_position ?? 'KEPALA DINAS KOMUNIKASI DAN INFORMATIKA';

        // Logo Utama Instansi Dinamis Berbasis Base64 (Untuk Kompatibilitas Tinggi & DOMPDF)
        $logoPath = null;
        if (!empty($agencyProfile?->logo) && file_exists(storage_path('app/public/' . $agencyProfile->logo))) {
            $logoPath = storage_path('app/public/' . $agencyProfile->logo);
        } elseif (!empty($agencyProfile?->logo) && file_exists(public_path('storage/' . $agencyProfile->logo))) {
            $logoPath = public_path('storage/' . $agencyProfile->logo);
        } elseif (!empty($agencyProfile?->logo) && file_exists(public_path($agencyProfile->logo))) {
            $logoPath = public_path($agencyProfile->logo);
        } elseif (file_exists(public_path('images/logo-surabaya.png'))) {
            $logoPath = public_path('images/logo-surabaya.png');
        } elseif (file_exists(public_path('images/logo.png'))) {
            $logoPath = public_path('images/logo.png');
        }


        $logoData = $logoPath ? @file_get_contents($logoPath) : '';
        $mime = ($logoPath && function_exists('mime_content_type')) ? (@mime_content_type($logoPath) ?: 'image/png') : 'image/png';
        $logoSrc = $logoData ? 'data:' . $mime . ';base64,' . base64_encode($logoData) : '';

        // Data mahasiswa & permohonan
        $student = $application->user->studentProfile;
        $fakultas = $student?->fakultas ?? $student?->faculty ?? 'Fakultas Mahasiswa';
        $universitas = $student?->universitas ?? $application->user?->university ?? 'Perguruan Tinggi';
        $nim = $student?->nim ?? '-';
        $jurusan = $student?->jurusan ?? $student?->major ?? '-';
        $semester = $student?->semester ? 'Semester ' . $student->semester : '-';

        // Format tanggal
        \Carbon\Carbon::setLocale('id');
        $letterDateFormatted = $application->letter_date 
            ? \Carbon\Carbon::parse($application->letter_date)->translatedFormat('d F Y') 
            : \Carbon\Carbon::now()->translatedFormat('d F Y');

        $startDateFormatted = $application->start_date 
            ? \Carbon\Carbon::parse($application->start_date)->translatedFormat('d F Y') 
            : '-';

        $endDateFormatted = $application->end_date 
            ? \Carbon\Carbon::parse($application->end_date)->translatedFormat('d F Y') 
            : '-';

        // Data Pembimbing
        $pembimbing = optional($application->placement)->pembimbing;
        $pembimbingName = $pembimbing ? $pembimbing->name : 'Pembimbing Lapangan / Unit Kerja Terkait';
        $pembimbingPhone = $pembimbing->phone ?? optional($pembimbing->studentProfile)->phone ?? $phone ?? '-';

        // QR Code Verifikasi Dokumen
        $verifyUrl = route('verify.letter', $application->id);
        $qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($verifyUrl);

        // Logo BSrE Base64
        $bsreLogoPath = public_path('images/bsre-logo.png');
        $bsreLogoData = file_exists($bsreLogoPath) ? @file_get_contents($bsreLogoPath) : '';
        $bsreLogoSrc = $bsreLogoData ? 'data:image/png;base64,' . base64_encode($bsreLogoData) : null;

        // URL Kembali Cerdas
        $backUrl = route('admin.applications.show', $application->id);
        if (Auth::user()?->role === 'mahasiswa') {
            $backUrl = route('dashboard');
        } elseif (Auth::user()?->role === 'universitas') {
            $backUrl = route('university.dashboard');
        }
    @endphp

    <!-- Bar Navigasi Aksi Cetak (Sembunyi saat diprint) -->
    <div class="no-print">
        <div style="font-size: 13px; font-weight: 500;">
            Surat Penerimaan Magang - {{ $application->user->name }}
        </div>
        <div class="btn-group">
            <a href="{{ $backUrl }}" onclick="if(window.opener || window.history.length > 1){ if(window.opener){ window.close(); return false; } else { window.history.back(); return false; } }" class="btn btn-secondary">
                 Kembali
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- Lembar Kertas A4 Tunggal -->
    <div class="page-container">
        
        <!-- 1. KOP SURAT RESMI -->
        <table class="kop-table">
            <tr>
                <td class="kop-logo">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}" alt="Logo Instansi" style="max-width: 65px; max-height: 80px; width: auto; height: auto; object-fit: contain;">
                    @endif
                </td>
                <td class="kop-text">
                    <div class="kop-instansi-1">{{ $govName }}</div>
                    <div class="kop-instansi-2">{{ strtoupper($agencyName) }}</div>
                    <div class="kop-alamat">{{ $address }}</div>
                    <div class="kop-kontak">
                        Telp. {{ $phone }} | Laman: {{ $website }} | Pos-el: {{ $email }}
                    </div>
                </td>
            </tr>
        </table>
        <div class="kop-divider"></div>

        <!-- 2. ATRIBUT HEADER SURAT -->
        <table class="meta-table">
            <tr>
                <td style="width: 58%;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 75px;">Nomor</td>
                            <td style="width: 10px;">:</td>
                            <td><strong>{{ $application->letter_number ?? '500.12.2/' . str_pad($application->id, 3, '0', STR_PAD_LEFT) . '/436.7.14/' . date('Y') }}</strong></td>
                        </tr>
                        <tr>
                            <td>Sifat</td>
                            <td>:</td>
                            <td>Biasa / Terbuka</td>
                        </tr>
                        <tr>
                            <td>Lampiran</td>
                            <td>:</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>Hal</td>
                            <td>:</td>
                            <td><strong>Balasan Surat Permohonan Izin Praktik Kerja Magang</strong></td>
                        </tr>
                    </table>
                </td>
                <td style="width: 42%; text-align: right;">
                    <span>{{ $cityName }}, {{ $letterDateFormatted }}</span>
                </td>
            </tr>
        </table>

        <!-- 3. TUJUAN SURAT (FORMAT RESMI DINAS PEMKOT) -->
        <div class="recipient-box">
            Yth. Dekan {{ $fakultas }}<br>
            <div style="padding-left: 26px;">
                {{ $universitas }}<br>
                di -<br>
                <div style="padding-left: 20px; font-weight: bold;">{{ $cityName }}</div>
            </div>
        </div>

        <!-- 4. PARAGRAF PENGANTAR & TABEL IDENTITAS MAHASISWA -->
        <div class="content-body">
            <p>
                Menindaklanjuti surat permohonan izin Praktik Kerja Magang dari Universitas Saudara, bersama ini disampaikan bahwa Pemerintah Kota Surabaya melalui {{ $agencyName }} menyetujui / menerima mahasiswa berikut:
            </p>

            <table class="student-table">
                <thead>
                    <tr>
                        <th style="width: 32px;">No</th>
                        <th>Nama</th>
                        <th style="width: 120px;">NIM</th>
                        <th>Program Studi</th>
                        <th>Fakultas</th>
                        <th style="width: 80px;">Semester</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align: center;">1</td>
                        <td style="font-weight: bold;">{{ $application->user->name }}</td>
                        <td style="text-align: center;">{{ $nim }}</td>
                        <td>{{ $jurusan }}</td>
                        <td>{{ $fakultas }}</td>
                        <td style="text-align: center;">{{ $semester }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- 5. PARAGRAF PELAKSANAAN & NARAHUBUNG -->
            <p>
                Untuk melaksanakan Praktik Kerja Magang pada <strong>{{ $agencyName }}</strong> dengan jadwal pelaksanaan mulai tanggal <strong>{{ $startDateFormatted }}</strong> s.d. <strong>{{ $endDateFormatted }}</strong> pada Unit Kerja <strong>{{ $application->unit->name ?? 'Dinas' }}</strong>. Informasi lebih lanjut dapat menghubungi Sdr. <strong>{{ $pembimbingName }}</strong> dengan Nomor HP. <strong>{{ $pembimbingPhone }}</strong>.
            </p>

            <p>
                Demikian surat pemberitahuan ini disampaikan untuk dapat dipergunakan sebagaimana mestinya. Atas perhatian dan kerja samanya, diucapkan terima kasih.
            </p>
        </div>

        <!-- 6. BAGIAN KOTAK TANDA TANGAN ELEKTRONIK (TTE) STANDAR DISPUSIP / PEMKOT -->
        <div class="tte-container">
            <table class="tte-table">
                <tr>
                    <td class="tte-qr-cell">
                        <img src="{{ $qrApiUrl }}" alt="QR Code Verifikasi TTE" class="tte-qr-img">
                    </td>
                    <td class="tte-text-cell">
                        <div style="font-size: 8pt; color: #111;">Surat ini Ditandatangani Elektronik Oleh :</div>
                        <div style="font-weight: bold; text-transform: uppercase;">{{ $signeePosition }},</div>
                        <div style="margin-top: 2px;"><b><u>{{ $signeeName }}</u></b></div>
                        <div>Pembina Utama Muda / IV/c</div>
                        @if (!empty($signeeNip))
                            <div>NIP. {{ $signeeNip }}</div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        <div class="clear-fix"></div>

        <!-- 7. FOOTER LEGALITAS BSrE RESMI IDENTIK -->
        <div class="bsre-footer-wrapper">
            <div class="bsre-divider-line"></div>
            <table class="bsre-footer-table">
                <tr>
                    <td class="bsre-logo-col">
                        @if($bsreLogoSrc)
                            <img src="{{ $bsreLogoSrc }}" alt="Balai Besar Sertifikasi Elektronik" class="bsre-logo-img">
                        @else
                            <div style="font-family: Arial, sans-serif; font-weight: bold; color: #1ea7e4; font-size: 9pt; line-height: 1.1;">
                                Balai Besar<br>Sertifikasi<br>Elektronik
                            </div>
                        @endif
                    </td>
                    <td class="bsre-text-col">
                        <div>- Dokumen ini telah ditandatangani secara elektronik menggunakan sertifikat elektronik yang diterbitkan BSrE</div>
                        <div>- UU ITE No 11 Tahun 2008 Pasal 5 Ayat 1</div>
                        <div class="bsre-quote">&quot;Informasi Elektronik dan/atau Dokumen Elektronik dan/atau hasil cetaknya merupakan alat bukti hukum yang sah&quot;</div>
                    </td>
                </tr>
            </table>
        </div>

    </div>

</body>
</html>
