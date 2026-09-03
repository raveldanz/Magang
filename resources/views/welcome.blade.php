<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Portal Magang Resmi - Pemerintah Kota Surabaya</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/logoPemkotSBY.png') }}">

        <!-- Fonts: Plus Jakarta Sans -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8FAFC] text-slate-900 font-sans antialiased selection:bg-blue-100 selection:text-blue-700 h-screen overflow-hidden flex flex-col justify-between">

        <!-- Header Navigasi -->
        <header class="w-full bg-white/90 backdrop-blur-md border-b border-slate-200/80 shrink-0">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                
                <!-- Logo & Brand -->
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logoPemkotSBY.png') }}" alt="Logo Pemkot Surabaya" class="h-9 w-auto object-contain">
                    <div class="flex flex-col">
                        <span class="text-sm font-bold tracking-tight text-slate-900 leading-tight">Portal Magang</span>
                        <span class="text-[10px] font-semibold tracking-wider uppercase text-slate-400">Pemerintah Kota Surabaya</span>
                    </div>
                </div>

            </div>
        </header>

        <!-- Main Hero Section (Full Width Left-Aligned & Clean Original Layout) -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full my-auto py-6">
            <div class="w-full space-y-5 lg:space-y-6 text-left">
                
                <!-- Badge Penerimaan -->
                <div>
                    <span class="inline-flex items-center gap-2 px-3 py-1 bg-blue-50 border border-blue-200 text-blue-700 text-xs font-bold rounded-md">
                        <span>Penerimaan Magang Mahasiswa & Siswa</span>
                    </span>
                </div>

                <!-- Headline Lebar Full Kiri ke Kanan -->
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight text-slate-900 leading-[1.12] max-w-5xl">
                    Wujudkan Pengalaman Kerja Nyata di <span class="text-blue-600 inline-block">Kota Surabaya</span>
                </h1>

                <!-- Deskripsi Sistem Informasi -->
                <p class="text-sm sm:text-base text-slate-600 max-w-3xl leading-relaxed">
                    Sistem Informasi Terpadu Pelaksanaan Magang di seluruh Organisasi Perangkat Daerah (OPD) dan instansi Pemerintah Kota Surabaya secara transparan, akuntabel, dan terstruktur.
                </p>

                <!-- Tombol Aksi -->
                <div class="flex flex-col sm:flex-row items-center justify-start gap-3.5 pt-2">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto px-6 py-3 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-xs transition text-center">
                            Masuk ke Panel Dashboard &rarr;
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="w-full sm:w-auto px-6 py-3 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-xs transition text-center">
                            Daftar Akun
                        </a>
                        <a href="{{ route('login') }}" class="w-full sm:w-auto px-6 py-3 bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-xs font-semibold uppercase tracking-wider rounded-xl transition text-center">
                            Masuk ke Akun
                        </a>
                    @endauth
                </div>

            </div>
        </main>

        <!-- Footer -->
        <footer class="w-full bg-white border-t border-slate-100 py-4 shrink-0">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-400">
                <p>&copy; {{ date('Y') }} Pemerintah Kota Surabaya. Hak Cipta Dilindungi.</p>
                <p class="text-[11px]">Sistem Informasi Penerimaan & Monitoring Magang</p>
            </div>
        </footer>

    </body>
</html>