<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kolektif Sertifikat Magang ({{ count($certificatesData) }} Berkas)</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alfa+Slab+One&family=Roboto+Slab:wght@700;800;900&family=Cinzel:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
    
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
                background: none !important;
            }
            .no-print {
                display: none !important;
            }
            .certificate-sheet {
                box-shadow: none !important;
                margin: 0 !important;
                page-break-after: always !important;
                break-after: page !important;
            }
            .page-break {
                page-break-before: always !important;
                break-before: page !important;
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
    </style>
</head>
<body class="text-slate-800 antialiased selection:bg-blue-600 selection:text-white">

    {{-- Top Action Bar (Hidden on Print) --}}
    <header class="no-print sticky top-0 z-50 bg-slate-900/95 backdrop-blur-md border-b border-slate-800 text-white px-4 sm:px-6 py-3 flex items-center justify-between gap-3 shadow-xl">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.certificates.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white rounded-xl text-xs font-bold transition border border-slate-700 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Daftar Sertifikat</span>
            </a>
            <div class="hidden sm:block text-xs text-slate-300 border-l border-slate-700 pl-3">
                <span>Cetak Kolektif: </span>
                <strong class="text-emerald-400 font-bold">{{ count($certificatesData) }} Sertifikat Siap Cetak</strong>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-black shadow-lg shadow-emerald-600/30 transition transform active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Semua Halaman (Ctrl + P)</span>
            </button>
        </div>
    </header>

    @foreach ($certificatesData as $idx => $data)
        {{-- Desain identik dengan cetak satuan: satu sumber di partials/sheets --}}
        @include('certificates.partials.sheets', $data)
    @endforeach

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
                    var w = sheet.offsetWidth;
                    var scale = Math.min(1, available / w);
                    sheet.style.transform = scale < 1 ? 'scale(' + scale + ')' : 'none';
                    sheet.style.marginLeft = scale < 1 ? '0' : '';
                    wrap.style.width = scale < 1 ? (w * scale) + 'px' : '';
                    wrap.style.height = scale < 1 ? (sheet.offsetHeight * scale) + 'px' : '';
                });
            }

            window.addEventListener('resize', fit);
            window.addEventListener('load', fit);
            setTimeout(fit, 100);
        })();
    </script>
</body>
</html>