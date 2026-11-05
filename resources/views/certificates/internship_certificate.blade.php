<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Magang — {{ $student->name }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alfa+Slab+One&family=Roboto+Slab:wght@700;800;900&family=Cinzel:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;0,800;0,900;1,500;1,600&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>

    @php
        \Carbon\Carbon::setLocale('id');
        setlocale(LC_TIME, 'id_ID.utf8', 'indonesian');

        $theme = $theme ?? \App\Models\CertificateTheme::getActive();
        $primaryColor = $theme->primary_color ?? '#00A3C4';
        $secondaryColor = $theme->secondary_color ?? '#F36F21';
        $accentColor = $theme->accent_color ?? '#D97706';

        $pPalette = method_exists($theme, 'getPrimaryPalette') ? $theme->getPrimaryPalette() : [
            'darkest' => '#00587a', 'darker' => '#007ca5', 'dark' => '#0097c9',
            'base' => $primaryColor, 'medium' => '#40c4e4', 'light' => '#80daf0',
            'lighter' => '#b8edf8', 'pale' => '#e0f6fc'
        ];
        $sPalette = method_exists($theme, 'getSecondaryPalette') ? $theme->getSecondaryPalette() : [
            'darkest' => '#b84a00', 'darker' => '#d95700', 'dark' => '#f26200',
            'base' => $secondaryColor, 'medium' => '#ff8d38', 'light' => '#ffb37a',
            'lighter' => '#ffd5b8', 'pale' => '#ffece0'
        ];

        $pDarkest = $pPalette['darkest'];
        $pDarker  = $pPalette['darker'];
        $pDark    = $pPalette['dark'];
        $pBase    = $pPalette['base'];
        $pMedium  = $pPalette['medium'];
        $pLight   = $pPalette['light'];

        $sDarkest = $sPalette['darkest'];
        $sDarker  = $sPalette['darker'];
        $sDark    = $sPalette['dark'];
        $sBase    = $sPalette['base'];
        $sMedium  = $sPalette['medium'];
        $sLight   = $sPalette['light'];
    @endphp

    <style>
        :root {
            --theme-primary: {{ $primaryColor }};
            --theme-secondary: {{ $secondaryColor }};
            --theme-accent: {{ $accentColor }};
        }

        @page {
            size: A4 landscape;
            margin: 0;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            background-color: #0f172a;
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
            .sheet-scale-wrap {
                height: auto !important;
                width: auto !important;
            }
            .sheet-scale-wrap > .certificate-sheet {
                transform: none !important;
            }
        }

        @media screen {
            .sheet-scale-wrap {
                margin: 0 auto;
                overflow: hidden;
            }
            .sheet-scale-wrap > .certificate-sheet {
                transform-origin: top left;
            }
        }

        .mobile-hint { display: none; }
        @media screen and (max-width: 767px) {
            .mobile-hint { display: block; }
        }
    </style>
</head>
<body class="text-slate-900 antialiased selection:bg-blue-600 selection:text-white">

    @php
        $backUrl = route('dashboard');
        $backLabel = 'Kembali ke Dashboard';
        if (Auth::user()?->role === 'admin' || Auth::user()?->role === 'super_admin') {
            $backUrl = route('admin.certificates.index');
            $backLabel = 'Kembali ke Daftar Sertifikat';
        } elseif (Auth::user()?->role === 'supervisor') {
            $backUrl = route('supervisor.certificates.index');
            $backLabel = 'Kembali ke Pengesahan Sertifikat';
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
    <header class="no-print sticky top-0 z-50 bg-slate-900/95 backdrop-blur-md border-b border-slate-800 text-white px-3 sm:px-6 py-3 flex items-center justify-between gap-2 shadow-xl">
        <div class="flex items-center gap-3">
            <a href="{{ $backUrl }}" onclick="if(window.opener || window.history.length > 1){ if(window.opener){ window.close(); return false; } else { window.history.back(); return false; } }" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white rounded-xl text-xs font-bold transition border border-slate-700 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span class="hidden sm:inline">{{ $backLabel }}</span><span class="sm:hidden">Kembali</span>
            </a>
            <div class="hidden sm:block text-xs text-slate-400 border-l border-slate-700 pl-3">
                <span>Dokumen Resmi: </span>
                <strong class="text-slate-200">{{ $regNumber }}</strong>
                @if (!empty($themePreview))
                    <span class="ml-2 px-2 py-0.5 rounded-md bg-amber-400 text-slate-900 font-black uppercase tracking-wide">Pratinjau tema — belum disimpan</span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-black shadow-lg shadow-blue-600/30 transition transform active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span class="hidden sm:inline">Cetak / Simpan PDF (A4 Landscape)</span><span class="sm:hidden">Cetak / PDF</span>
            </button>
        </div>
    </header>
    <p class="no-print mobile-hint text-center text-[11px] text-slate-400 px-4 pt-3">
        Pratinjau diperkecil agar muat di layar. Gunakan tombol <strong class="text-slate-200">Cetak / PDF</strong> untuk hasil ukuran A4 penuh.
    </p>

    @include('certificates.partials.sheets')

    {{-- Skala otomatis agar lembar A4 landscape muat di layar HP/tablet (tampilan cetak tidak berubah) --}}
    <script>
        (function () {
            var sheets = Array.prototype.slice.call(document.querySelectorAll('.certificate-sheet'));
            sheets.forEach(function (sheet) {
                var wrap = document.createElement('div');
                wrap.className = 'sheet-scale-wrap';
                sheet.parentNode.insertBefore(wrap, sheet);
                wrap.appendChild(sheet);
            });

            function fit() {
                var available = document.documentElement.clientWidth - 16;
                sheets.forEach(function (sheet) {
                    var wrap = sheet.parentNode;
                    sheet.style.transform = 'none';
                    var w = sheet.offsetWidth, h = sheet.offsetHeight;
                    var styles = getComputedStyle(sheet);
                    var mt = parseFloat(styles.marginTop) || 0, mb = parseFloat(styles.marginBottom) || 0;
                    var scale = Math.min(1, available / w);
                    sheet.style.transform = scale < 1 ? 'scale(' + scale + ')' : 'none';
                    sheet.style.marginLeft = scale < 1 ? '0' : '';
                    wrap.style.width = scale < 1 ? (w * scale) + 'px' : '';
                    wrap.style.height = scale < 1 ? (h * scale + (mt + mb) * scale) + 'px' : '';
                });
            }

            fit();
            window.addEventListener('resize', fit);
            window.addEventListener('beforeprint', function () {
                sheets.forEach(function (s) { s.style.transform = 'none'; s.parentNode.style.height = ''; s.parentNode.style.width = ''; });
            });
            window.addEventListener('afterprint', fit);
        })();
    </script>
</body>
</html>
