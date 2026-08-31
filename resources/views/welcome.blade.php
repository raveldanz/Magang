<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
    <body class="bg-[#F5F8FC] text-slate-900 font-sans antialiased selection:bg-blue-100 selection:text-blue-700 min-h-screen flex flex-col justify-between">

        <!-- Top Navigation -->
        <header class="w-full bg-white/80 backdrop-blur-md border-b border-slate-200/70 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                
                <!-- Logo & Brand -->
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logoPemkotSBY.png') }}" alt="Logo Pemkot Surabaya" class="h-9 w-auto object-contain">
                    <div class="flex flex-col">
                        <span class="text-sm font-bold tracking-tight text-slate-900 leading-tight">Portal Magang</span>
                        <span class="text-[10px] font-semibold tracking-wider uppercase text-slate-400">Pemerintah Kota Surabaya</span>
                    </div>
                </div>

                <!-- Auth Navigation -->
                <div class="flex items-center gap-3">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold uppercase tracking-wider rounded-xl shadow-sm shadow-blue-200 transition-all duration-200 hover:scale-[1.01]">
                                Ke Dashboard &rarr;
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-4 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-slate-50 rounded-xl transition-all duration-200">
                                Masuk
                            </a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold uppercase tracking-wider rounded-xl shadow-sm shadow-blue-200 transition-all duration-200 hover:scale-[1.01]">
                                    Daftar Magang
                                </a>
                            @endif
                        @endauth
                    @endif
                </div>

            </div>
        </header>

        <!-- Main Hero Section -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 my-auto">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                
                <!-- Left Column: Copywriting -->
                <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-blue-50 border border-blue-100 text-blue-700 text-xs font-semibold rounded-full">
                        <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                        Penerimaan Magang Mahasiswa & Siswa
                    </div>

                    <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-slate-900 leading-tight">
                        Wujudkan Pengalaman Kerja Nyata di <span class="text-blue-600">Kota Surabaya</span>
                    </h1>

                    <p class="text-sm sm:text-base text-slate-500 max-w-xl mx-auto lg:mx-0 leading-relaxed">
                        Sistem Informasi Terpadu Pelaksanaan Magang di seluruh Organisasi Perangkat Daerah (OPD) dan instansi Pemerintah Kota Surabaya secara transparan dan terstruktur.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3 pt-2">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-sm shadow-blue-200 transition-all duration-200 hover:scale-[1.01] text-center">
                                Masuk ke Panel Dashboard
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="w-full sm:w-auto px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-sm shadow-blue-200 transition-all duration-200 hover:scale-[1.01] text-center">
                                Ajukan Magang Sekarang
                            </a>
                            <a href="{{ route('login') }}" class="w-full sm:w-auto px-6 py-3 bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 text-xs font-semibold uppercase tracking-wider rounded-xl transition-all duration-200 text-center">
                                Masuk ke Akun
                            </a>
                        @endauth
                    </div>
                </div>

                <!-- Right Column: Features Card Surface -->
                <div class="lg:col-span-5 space-y-4">
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm shadow-slate-200/50 space-y-4">
                        
                        <div class="flex items-start gap-4 p-3 rounded-xl bg-slate-50 border border-slate-100/80">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm shrink-0">
                                📋
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Logbook Digital</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">Catat aktivitas magang harian dan dapatkan verifikasi langsung dari mentor lapangan.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4 p-3 rounded-xl bg-slate-50 border border-slate-100/80">
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm shrink-0">
                                🏛️
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Beragam Pilihan OPD</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">Pilih divisi dan dinas penempatan yang relevan dengan bidang studi kampus Anda.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4 p-3 rounded-xl bg-slate-50 border border-slate-100/80">
                            <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-sm shrink-0">
                                📜
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">E-Sertifikat Resmi</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">Terbitkan sertifikat dan transkrip penilaian resmi setelah menyelesaikan magang.</p>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </main>

        <!-- Footer -->
        <footer class="w-full bg-white border-t border-slate-100 py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-400">
                <p>&copy; {{ date('Y') }} Pemerintah Kota Surabaya. Hak Cipta Dilindungi.</p>
                <p class="text-[11px]">Sistem Informasi Penerimaan & Monitoring Magang</p>
            </div>
        </footer>

    </body>
</html>