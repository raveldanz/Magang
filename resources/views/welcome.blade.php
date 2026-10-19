<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Portal Magang Resmi - Dinas Perpustakaan dan Kearsipan Kota Surabaya</title>

        <!-- Favicon Resmi Pemerintah Kota Surabaya & Dispusip -->
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logos/dispusip.png') }}">
        <link rel="shortcut icon" href="{{ asset('images/logos/dispusip.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/logos/dispusip.png') }}">

        <!-- Fonts: Plus Jakarta Sans -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8FAFC] text-slate-900 font-sans antialiased selection:bg-blue-100 selection:text-blue-700 min-h-screen flex flex-col justify-between">

        @php
            $dispusip = \App\Models\AgencyProfile::first();
            $units = \App\Models\Unit::withCount(['applications as occupied_count_db' => function ($query) {
                $query->occupyingQuota();
            }])->where('agency_profile_id', $dispusip?->id)->get();
            $totalQuota = $units->sum('quota');
            $totalRemaining = $units->sum(fn($u) => $u->remaining_quota);
        @endphp

        <!-- Header Navigasi -->
        <header class="w-full bg-white/95 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-30 shrink-0">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
                
                <!-- Logo & Brand Dispusip Surabaya -->
                <div class="flex items-center gap-3 sm:gap-4">
                    <img src="{{ asset('images/logoPemkotSBY.png') }}" alt="Logo Pemkot Surabaya" 
                         class="h-9 sm:h-10 w-auto object-contain"
                         style="aspect-ratio: 960/1234; height: 38px; width: auto; object-fit: contain;">
                    <div class="h-6 w-px bg-slate-200"></div>
                    <img src="{{ asset('images/logos/dispusip.png') }}" alt="Logo Dispusip Surabaya" 
                         class="h-9 sm:h-10 w-auto object-contain"
                         style="height: 38px; width: auto; object-fit: contain;">
                    <div class="flex flex-col">
                        <span class="text-xs sm:text-sm font-extrabold tracking-tight text-slate-900 leading-tight">Portal Magang Dispusip</span>
                        <span class="text-[9px] sm:text-[10px] font-bold tracking-wider uppercase text-blue-700">Dinas Perpustakaan dan Kearsipan Kota Surabaya</span>
                    </div>
                </div>

                <!-- Right Header Actions -->
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-xs transition">
                            <span>Panel Dashboard</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold uppercase tracking-wider rounded-xl transition">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-xs transition">
                            <span>Daftar</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @endauth
                </div>

            </div>
        </header>

        <!-- Main Hero Section (2-Column Grid on Desktop) -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full my-auto py-8 sm:py-12">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                
                <!-- Kolom Kiri: Hero & Action -->
                <div class="lg:col-span-7 space-y-6 text-left">
                    
                    <!-- Badge Penerimaan Dispusip -->
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2 px-3 py-1 bg-blue-50 border border-blue-200 text-blue-700 text-xs font-bold rounded-lg shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                            <span>Penerimaan Magang Mahasiswa & Siswa</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-bold rounded-lg">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>Sisa Kuota: {{ $totalRemaining }} Kursi</span>
                        </span>
                    </div>

                    <!-- Headline Utama -->
                    <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black tracking-tight text-slate-900 leading-[1.12]">
                        Wujudkan Pengalaman Kerja Nyata di <span class="text-blue-600 inline-block">Dispusip Surabaya</span>
                    </h1>

                    <!-- Deskripsi Sistem Informasi Resmi -->
                    <p class="text-sm sm:text-base text-slate-600 leading-relaxed max-w-2xl">
                        Sistem Informasi Resmi Pelaksanaan Magang dan Praktik Kerja Lapangan (PKL) di lingkungan <strong>Dinas Perpustakaan dan Kearsipan Kota Surabaya</strong>. Bergabunglah dalam transformasi layanan otomasi perpustakaan modern, preservasi arsip digital, dan pembudayaan literasi masyarakat.
                    </p>

                    <!-- Tombol Aksi Utama -->
                    <div class="flex flex-col sm:flex-row items-center justify-start gap-3.5 pt-2">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto px-7 py-3.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs sm:text-sm font-bold uppercase tracking-wider rounded-xl shadow-md shadow-blue-500/20 transition text-center flex items-center justify-center gap-2">
                                <span>Masuk ke Panel Dashboard</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="w-full sm:w-auto px-7 py-3.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs sm:text-sm font-bold uppercase tracking-wider rounded-xl shadow-md shadow-blue-500/20 transition text-center flex items-center justify-center gap-2">
                                <span>Daftar Magang Sekarang</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                            <a href="{{ route('login') }}" class="w-full sm:w-auto px-6 py-3.5 bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-xs sm:text-sm font-bold uppercase tracking-wider rounded-xl transition text-center shadow-2xs">
                                Masuk ke Akun
                            </a>
                        @endauth
                    </div>

                    <!-- Info Kedinasan Dispusip -->
                    <div class="pt-3 border-t border-slate-200/80 flex flex-wrap items-center gap-y-2 gap-x-4 text-xs text-slate-500">
                        <div class="flex items-center gap-1.5 font-medium">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Jl. Rungkut Asri Tengah No. 5-7, Surabaya</span>
                        </div>
                        <div class="flex items-center gap-1.5 font-medium">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span>(031) 8707682</span>
                        </div>
                    </div>

                </div>

                <!-- Kolom Kanan: Kartu Bidang Magang Resmi Dispusip -->
                <div class="lg:col-span-5 w-full">
                    <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-xl shadow-slate-200/60 border border-slate-200/90 space-y-4">
                        
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div>
                                <h2 class="text-sm sm:text-base font-extrabold text-slate-900 tracking-tight">Bidang Pilihan Magang</h2>
                                <p class="text-[11px] text-slate-500 font-medium">4 Bidang Kerja Aktif di Dispusip Surabaya</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">Terbuka MBKM</span>
                        </div>

                        <!-- 4 Unit Divisi Dispusip (Tanpa Ikon) -->
                        <div class="space-y-2.5">
                            
                            <!-- Bidang 1: Bidang Sekretariat -->
                            @php $uSekretariat = $units->first(fn($u) => str_contains(strtolower($u->name), 'sekretariat')); @endphp
                            <div class="p-3.5 rounded-2xl bg-slate-50/80 hover:bg-blue-50/40 border border-slate-200/70 hover:border-blue-200 transition group">
                                <div class="flex items-center justify-between gap-2">
                                    <h3 class="text-xs font-bold text-slate-900 group-hover:text-blue-700 transition">Bidang Sekretariat</h3>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-100/70 px-2 py-0.5 rounded-md shrink-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        Sisa: {{ $uSekretariat?->remaining_quota ?? 8 }} Kuota
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-1 line-clamp-1">Administrasi umum, kepegawaian, tata persuratan, perencanaan & keuangan.</p>
                            </div>

                            <!-- Bidang 2: Bidang Pembinaan dan Pengelolaan Perpustakaan -->
                            @php $uPerpus = $units->first(fn($u) => str_contains(strtolower($u->name), 'pengelolaan perpustakaan') || str_contains(strtolower($u->name), 'otomasi perpustakaan')); @endphp
                            <div class="p-3.5 rounded-2xl bg-slate-50/80 hover:bg-blue-50/40 border border-slate-200/70 hover:border-blue-200 transition group">
                                <div class="flex items-center justify-between gap-2">
                                    <h3 class="text-xs font-bold text-slate-900 group-hover:text-blue-700 transition">Pembinaan & Pengelolaan Perpustakaan</h3>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-100/70 px-2 py-0.5 rounded-md shrink-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        Sisa: {{ $uPerpus?->remaining_quota ?? 9 }} Kuota
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-1 line-clamp-1">Pengembangan perpustakaan, otomasi katalog OPAC & layanan pemustaka.</p>
                            </div>

                            <!-- Bidang 3: Bidang Pembinaan dan Layanan Kearsipan -->
                            @php $uLayananArsip = $units->first(fn($u) => str_contains(strtolower($u->name), 'layanan kearsipan') || str_contains(strtolower($u->name), 'minat baca')); @endphp
                            <div class="p-3.5 rounded-2xl bg-slate-50/80 hover:bg-blue-50/40 border border-slate-200/70 hover:border-blue-200 transition group">
                                <div class="flex items-center justify-between gap-2">
                                    <h3 class="text-xs font-bold text-slate-900 group-hover:text-blue-700 transition">Pembinaan & Layanan Kearsipan</h3>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-100/70 px-2 py-0.5 rounded-md shrink-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        Sisa: {{ $uLayananArsip?->remaining_quota ?? 8 }} Kuota
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-1 line-clamp-1">Pembinaan kearsipan instansi, pemanfaatan arsip dinamis & statis.</p>
                            </div>

                            <!-- Bidang 4: Bidang Pengelolaan, Perlindungan, dan Penyelamatan Kearsipan -->
                            @php $uPenyelamatanArsip = $units->first(fn($u) => str_contains(strtolower($u->name), 'penyelamatan kearsipan') || str_contains(strtolower($u->name), 'preservasi')); @endphp
                            <div class="p-3.5 rounded-2xl bg-slate-50/80 hover:bg-blue-50/40 border border-slate-200/70 hover:border-blue-200 transition group">
                                <div class="flex items-center justify-between gap-2">
                                    <h3 class="text-xs font-bold text-slate-900 group-hover:text-blue-700 transition">Pengelolaan, Perlindungan & Penyelamatan Kearsipan</h3>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-100/70 px-2 py-0.5 rounded-md shrink-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        Sisa: {{ $uPenyelamatanArsip?->remaining_quota ?? 8 }} Kuota
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-1 line-clamp-1">Restorasi naskah kuno bersejarah, alih media digital & preservasi arsip.</p>
                            </div>

                        </div>

                        <!-- Footer Catatan -->
                        <div class="p-3 bg-blue-50/70 rounded-xl border border-blue-100 text-[11px] text-blue-900 leading-relaxed font-medium">
                            Terbuka bagi mahasiswa D3/D4/S1 semua jurusan & siswa SMK yang bermitra dengan Dinas Perpustakaan dan Kearsipan Kota Surabaya.
                        </div>

                    </div>
                </div>

            </div>
        </main>

        <!-- Footer -->
        <footer class="w-full bg-white border-t border-slate-200/80 py-4 shrink-0">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} Dinas Perpustakaan dan Kearsipan Kota Surabaya. Hak Cipta Dilindungi.</p>
                <p class="text-[11px] text-slate-400 font-medium">Sistem Informasi Penerimaan, Logbook, Evaluasi & Monitoring Magang Dispusip</p>
            </div>
        </footer>

    </body>
</html>