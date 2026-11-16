<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lembar Penilaian Mentor Lapangan - {{ $student?->name }} ({{ $profile?->nim ?? '-' }})</title>
    <style>
        @page { size: A4 portrait; margin: 15mm 18mm; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Times New Roman', Times, serif; color: #111827; background: #e5e7eb; font-size: 12pt; line-height: 1.45; }
        .toolbar { position: sticky; top: 0; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between; padding: 12px 16px; background: #0f172a; color: #fff; font-family: Arial, Helvetica, sans-serif; font-size: 16px; }
        .toolbar a, .toolbar button { min-height: 48px; display: inline-flex; align-items: center; padding: 0 18px; border-radius: 10px; font-size: 16px; font-weight: 700; text-decoration: none; cursor: pointer; border: 0; }
        .toolbar a { background: #334155; color: #fff; }
        .toolbar button { background: #2563eb; color: #fff; }
        .paper { width: 210mm; min-height: 297mm; margin: 16px auto; padding: 16mm 18mm; background: #fff; box-shadow: 0 4px 16px rgba(0,0,0,.12); }
        .kop { text-align: center; border-bottom: 3px double #111827; padding-bottom: 8px; margin-bottom: 14px; }
        .kop .instansi { font-size: 15pt; font-weight: 700; text-transform: uppercase; }
        .kop .sub { font-size: 12pt; }
        h1 { font-size: 14pt; text-align: center; text-transform: uppercase; margin: 14px 0 16px; text-decoration: underline; }
        table { width: 100%; border-collapse: collapse; }
        .identitas td { padding: 3px 4px; vertical-align: top; }
        .identitas td:first-child { width: 34%; }
        .identitas td:nth-child(2) { width: 3%; }
        .nilai { margin-top: 14px; }
        .nilai th, .nilai td { border: 1px solid #374151; padding: 6px 8px; }
        .nilai th { background: #f3f4f6; }
        .center { text-align: center; }
        .catatan { margin-top: 14px; border: 1px solid #374151; padding: 8px 10px; min-height: 60px; }
        .legend { margin-top: 8px; font-size: 10.5pt; }
        .ttd { margin-top: 32px; display: flex; justify-content: flex-end; }
        .ttd .kolom { width: 46%; text-align: center; }
        .ttd .ruang { height: 72px; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .paper { margin: 0; padding: 0; box-shadow: none; width: auto; min-height: auto; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ route('mentor.students.show', $placement->id) }}">&larr; Kembali ke Halaman Mahasiswa</a>
        <span>Periksa isinya, lalu klik Cetak. Tanda tangani setelah dicetak.</span>
        <button type="button" onclick="window.print()">Cetak Lembar Nilai</button>
    </div>

    <div class="paper">
        <div class="kop">
            <div class="sub">PEMERINTAH KOTA SURABAYA</div>
            <div class="instansi">{{ $agency->agency_name ?? 'Instansi Penempatan Magang' }}</div>
            @if ($agency?->address)
                <div class="sub">{{ $agency->address }}</div>
            @endif
        </div>

        <h1>Lembar Penilaian Mentor Lapangan</h1>

        <table class="identitas">
            <tr><td>Nama Mahasiswa</td><td>:</td><td><strong>{{ $student?->name ?? '-' }}</strong></td></tr>
            <tr><td>NIM</td><td>:</td><td>{{ $profile?->nim ?? '-' }}</td></tr>
            <tr><td>Perguruan Tinggi / Program Studi</td><td>:</td><td>{{ $profile?->universitas ?? '-' }} / {{ $profile?->jurusan ?? '-' }}</td></tr>
            <tr><td>Bidang / Unit Penempatan</td><td>:</td><td>{{ $unit?->name ?? '-' }}</td></tr>
            <tr><td>Periode Magang</td><td>:</td><td>
                {{ $placement->application?->start_date ? \Carbon\Carbon::parse($placement->application->start_date)->translatedFormat('d F Y') : '-' }}
                s/d
                {{ $placement->application?->end_date ? \Carbon\Carbon::parse($placement->application->end_date)->translatedFormat('d F Y') : '-' }}
            </td></tr>
            <tr><td>Dosen Pembimbing</td><td>:</td><td>{{ $placement->academicAdvisor?->name ?? '-' }}</td></tr>
        </table>

        <table class="nilai">
            <thead>
                <tr>
                    <th class="center" style="width: 8%">No</th>
                    <th>Aspek Penilaian</th>
                    <th class="center" style="width: 18%">Nilai (0–100)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($aspects as $i => $aspect)
                    <tr>
                        <td class="center">{{ $i + 1 }}</td>
                        <td>{{ $aspect['name'] ?? '-' }}</td>
                        <td class="center">{{ $aspect['score'] ?? '-' }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="2" style="text-align: right"><strong>Rata-rata Nilai Mentor Lapangan</strong></td>
                    <td class="center"><strong>{{ number_format($average, 2, ',', '.') }}</strong></td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: right"><strong>Predikat</strong></td>
                    <td class="center"><strong>{{ $grade }}</strong></td>
                </tr>
            </tbody>
        </table>
        <p class="legend">Panduan predikat: A (&ge; 85), AB (75–84,99), B (65–74,99), BC (55–64,99), C (40–54,99), E (&lt; 40).</p>

        <p style="margin: 14px 0 4px;"><strong>Catatan Mentor Lapangan:</strong></p>
        <div class="catatan">{{ $evaluation->catatan ?: '-' }}</div>

        <div class="ttd">
            <div class="kolom">
                <div>Surabaya, {{ now()->translatedFormat('d F Y') }}</div>
                <div>Mentor Lapangan,</div>
                <div class="ruang"></div>
                <div><strong><u>{{ $mentorUser?->name ?? '..............................' }}</u></strong></div>
                <div>NIP. {{ $mentorUser?->nip ?? '..............................' }}</div>
            </div>
        </div>
    </div>
</body>
</html>
