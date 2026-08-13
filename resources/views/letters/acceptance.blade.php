<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Penerimaan Magang - {{ $application->user->name }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.6;
            margin: 40px;
            color: #000;
        }
        .kop-surat {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
            position: relative;
        }
        .kop-surat h2 {
            margin: 0;
            font-size: 14pt;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .kop-surat h3 {
            margin: 0;
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .kop-surat p {
            margin: 0;
            font-size: 10pt;
            font-style: italic;
        }
        .no-print {
            margin-bottom: 20px;
            text-align: right;
        }
        .btn-print {
            background-color: #2563eb;
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-family: sans-serif;
            font-size: 14px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn-print:hover {
            background-color: #1d4ed8;
        }
        .table-data {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .table-data td {
            vertical-align: top;
            padding: 3px 0;
        }
        .signature-section {
            float: right;
            width: 300px;
            text-align: center;
            margin-top: 30px;
        }
        .qr-code-img {
            width: 110px;
            height: 110px;
            margin: 10px auto;
            display: block;
        }
        .qr-caption {
            font-size: 8pt;
            font-family: sans-serif;
            color: #4b5563;
            margin-top: 2px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>

    @php
        $agency = \App\Models\AgencyProfile::first();
        $govName = $agency->government_name ?? 'Pemerintah Kota Surabaya';
        $agencyName = $agency->agency_name ?? 'Dinas Komunikasi Dan Informatika';
        $address = $agency->address ?? 'Jl. Jimerto No. 25-27, Ketabang, Genteng, Kota Surabaya, Jawa Timur 60272';
        $cityName = $agency->city ?? 'Surabaya';
        $signeeName = $agency->signee_name ?? 'Drs. H. M. NASER, M.Si';
        $signeeNip = $agency->signee_nip ?? '19700101 199503 1 002';
        $signeePosition = $agency->signee_position ?? 'Kepala Dinas Komunikasi dan Informatika';
    @endphp

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak / Simpan ke PDF</button>
    </div>

    <!-- Kop Surat Resmi -->
    <div class="kop-surat">
        @if (!empty($agency->logo))
            <img src="{{ asset('storage/' . $agency->logo) }}" alt="Logo Instansi" style="height: 70px; position: absolute; left: 10px; top: 0;">
        @endif
        <h2>{{ $govName }}</h2>
        <h3>{{ $agencyName }}</h3>
        <p>{{ $address }}</p>
    </div>

    <!-- Tanggal Surat -->
    <div style="text-align: right; margin-bottom: 15px;">
        {{ $cityName }}, {{ $application->letter_date ? \Carbon\Carbon::parse($application->letter_date)->translatedFormat('d F Y') : date('d F Y') }}
    </div>

    <!-- Nomor & Hal -->
    <table class="table-data" style="width: 100%;">
        <tr>
            <td style="width: 15%;">Nomor</td>
            <td style="width: 2%;">:</td>
            <td><strong>{{ $application->letter_number ?? '500/123/APTIKA/' . date('Y') }}</strong></td>
        </tr>
        <tr>
            <td>Sifat</td>
            <td>:</td>
            <td>Biasa</td>
        </tr>
        <tr>
            <td>Hal</td>
            <td>:</td>
            <td><strong>Persetujuan / Penerimaan Praktik Kerja Magang</strong></td>
        </tr>
    </table>

    <p style="margin-bottom: 15px;">
        Kepada Yth.<br>
        <strong>Pimpinan / Dekan {{ $application->user->studentProfile->universitas ?? 'Perguruan Tinggi' }}</strong><br>
        di Tempat
    </p>

    <p style="text-align: justify;">Dengan hormat,</p>
    <p style="text-align: justify; text-indent: 30px;">
        Sehubungan dengan surat pengajuan magang yang telah dikirimkan, bersama ini kami sampaikan bahwa mahasiswa berikut:
    </p>

    <table class="table-data" style="margin-left: 30px; width: 90%;">
        <tr>
            <td style="width: 180px;">Nama Mahasiswa</td>
            <td style="width: 10px;">:</td>
            <td><strong>{{ $application->user->name }}</strong></td>
        </tr>
        <tr>
            <td>NIM / NPM</td>
            <td>:</td>
            <td>{{ $application->user->studentProfile->nim ?? '-' }}</td>
        </tr>
        <tr>
            <td>Program Studi</td>
            <td>:</td>
            <td>{{ $application->user->studentProfile->jurusan ?? '-' }}</td>
        </tr>
        <tr>
            <td>Perguruan Tinggi</td>
            <td>:</td>
            <td>{{ $application->user->studentProfile->universitas ?? '-' }}</td>
        </tr>
    </table>

    <p style="text-align: justify;">
        Dinyatakan <strong>DITERIMA</strong> untuk melaksanakan kegiatan Praktik Kerja Magang pada instansi kami dengan rincian sebagai berikut:
    </p>

    <table class="table-data" style="margin-left: 30px; width: 90%;">
        <tr>
            <td style="width: 180px;">Unit Kerja / Bidang</td>
            <td style="width: 10px;">:</td>
            <td><strong>{{ $application->unit->name ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td>Periode Magang</td>
            <td>:</td>
            <td>{{ \Carbon\Carbon::parse($application->start_date)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($application->end_date)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td>Pembimbing Lapangan</td>
            <td>:</td>
            <td>{{ optional($application->placement)->pembimbing->name ?? 'Dikerjakan di lokasi unit kerja' }}</td>
        </tr>
    </table>

    <p style="text-align: justify; text-indent: 30px;">
        Demikian surat pemberitahuan penerimaan ini disampaikan untuk dapat dipergunakan sebagaimana mestinya. Atas perhatian dan kerja samanya, kami ucapkan terima kasih.
    </p>

    <!-- Tanda Tangan & QR Code Verification -->
    <div class="signature-section">
        <p style="margin-bottom: 5px;">{{ $signeePosition }}</p>

        @php
            $verifyUrl = route('verify.letter', $application->id);
            $qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode($verifyUrl);
        @endphp

        <!-- QR Code Verification Image -->
        <img src="{{ $qrApiUrl }}" alt="QR Code Verifikasi Dokumen" class="qr-code-img">
        <div class="qr-caption">Scan untuk Verifikasi Keaslian Dokumen</div>

        <p style="margin-top: 10px;"><strong><u>{{ $signeeName }}</u></strong><br>
        @if (!empty($signeeNip))
            NIP. {{ $signeeNip }}
        @endif
        </p>
    </div>

</body>
</html>
