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

            $uSekretariat = $units->first(fn($u) => str_contains(strtolower($u->name), 'sekretariat'));
            $uPerpus = $units->first(fn($u) => str_contains(strtolower($u->name), 'pengelolaan perpustakaan') || str_contains(strtolower($u->name), 'otomasi perpustakaan'));
            $uLayananArsip = $units->first(fn($u) => str_contains(strtolower($u->name), 'layanan kearsipan') || str_contains(strtolower($u->name), 'minat baca'));
            $uPenyelamatanArsip = $units->first(fn($u) => str_contains(strtolower($u->name), 'penyelamatan kearsipan') || str_contains(strtolower($u->name), 'preservasi'));
        @endphp

        <!-- Styles Khusus Responsif & Alpine Cloak -->
        <style>
            [x-cloak] { display: none !important; }
            @media (min-width: 1024px) {
                .dispusip-address-desktop { display: flex !important; }
                .dispusip-address-mobile { display: none !important; }
            }
            @media (max-width: 1023.98px) {
                .dispusip-address-desktop { display: none !important; }
                .dispusip-address-mobile { display: flex !important; }
            }
        </style>

        <!-- Header Navigasi dengan Dukungan Hamburger Menu Responsif di Mobile -->
        <header x-data="{ mobileMenuOpen: false }" class="w-full bg-white/95 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-40 shrink-0">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
                
                <!-- Logo & Brand Dispusip Surabaya -->
                <a href="{{ url('/') }}" class="flex items-center gap-2.5 sm:gap-4 min-w-0 group" title="Portal Magang Dispusip Kota Surabaya">
                    <img src="{{ asset('images/logoPemkotSBY.png') }}" alt="Logo Pemkot Surabaya" 
                         class="h-8 sm:h-10 w-auto object-contain shrink-0"
                         style="aspect-ratio: 960/1234; height: 36px; width: auto; object-fit: contain;">
                    <div class="h-6 w-px bg-slate-200 shrink-0"></div>
                    <img src="{{ asset('images/logos/dispusip.png') }}" alt="Logo Dispusip Surabaya" 
                         class="h-8 sm:h-10 w-auto object-contain shrink-0"
                         style="height: 36px; width: auto; object-fit: contain;">
                    <div class="flex flex-col min-w-0">
                        <span class="text-xs sm:text-sm font-extrabold tracking-tight text-slate-900 leading-tight truncate">Portal Magang Dispusip</span>
                        <span class="text-[10px] sm:text-xs font-bold text-blue-700 tracking-tight sm:tracking-normal uppercase truncate">Dinas Perpustakaan dan Kearsipan Kota Surabaya</span>
                    </div>
                </a>

                <!-- Desktop Right Header Actions (Tampil Khusus Layar md ke Atas) -->
                <div class="hidden md:flex items-center gap-2 sm:gap-3">
                    <!-- Menu Proyek Dinas -->
                    <a href="{{ route('public.projects.index') }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-2 text-xs sm:text-sm font-bold text-slate-700 hover:text-blue-600 transition rounded-xl hover:bg-slate-50">
                        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <span>Proyek Dinas</span>
                    </a>

                    @auth
                        <!-- Jika Sudah Login: Tombol Berubah Menjadi Dashboard dengan Style Awal -->
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-xs transition">
                            <span>Dashboard</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @else
                        <!-- Jika Belum Login: Tombol Masuk & Daftar Sesuai Desain Awal -->
                        <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold uppercase tracking-wider rounded-xl transition">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-xs transition">
                            <span>Daftar</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @endauth
                </div>

                <!-- Mobile Hamburger Button (Hanya Tampil di Layar Kecil < md) -->
                <div class="flex items-center md:hidden">
                    <button type="button" 
                            @click="mobileMenuOpen = !mobileMenuOpen" 
                            class="inline-flex items-center justify-center p-2 rounded-xl text-slate-700 hover:text-blue-600 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all border border-slate-200/80 shadow-2xs"
                            aria-label="Toggle navigasi menu"
                            :aria-expanded="mobileMenuOpen">
                        <!-- Icon Garis 3 (Burger) Saat Menu Tertutup -->
                        <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <!-- Icon Silang (X) Saat Menu Terbuka -->
                        <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

            </div>

            <!-- Mobile Menu Dropdown Panel (Clean, Modern & Lega) -->
            <div x-show="mobileMenuOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2 scale-98"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 -translate-y-2 scale-98"
                 x-cloak
                 class="md:hidden border-b border-slate-200 bg-white/98 backdrop-blur-md px-4 pt-3 pb-6 shadow-xl space-y-4">
                
                <!-- Navigasi Menu Mobile -->
                <div class="space-y-1">
                    <a href="{{ url('/') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold text-slate-800 hover:text-blue-600 hover:bg-blue-50/70 transition">
                        <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Beranda Utama</span>
                    </a>
                    <a href="{{ route('public.projects.index') }}" 
                       class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-bold text-slate-800 hover:text-blue-600 hover:bg-blue-50/70 transition">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span>Proyek Dinas</span>
                        </div>
                        <span class="px-2 py-0.5 text-[10px] font-bold bg-blue-100 text-blue-700 rounded-full">Katalog</span>
                    </a>
                </div>

                <!-- Garis Pemisah -->
                <div class="h-px bg-slate-100"></div>

                <!-- Tombol Aksi Autentikasi Mobile -->
                <div class="space-y-2 pt-1">
                    @auth
                        <a href="{{ route('dashboard') }}" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 hover:bg-blue-700 active:scale-98 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-xs transition">
                            <span>Buka Dashboard</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 hover:bg-blue-700 active:scale-98 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-xs transition">
                            <span>Daftar Magang</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                        <a href="{{ route('login') }}" class="w-full flex items-center justify-center px-4 py-2.5 bg-slate-100 hover:bg-slate-200 active:scale-98 text-slate-700 text-xs font-bold uppercase tracking-wider rounded-xl transition">
                            Masuk ke Akun
                        </a>
                    @endauth
                </div>

                <!-- Informasi Kontak Singkat di Bawah Dropdown -->
                <div class="pt-2 text-center text-[11px] text-slate-400 font-medium">
                    📍 Gedung Siola Lt. 4, Surabaya &bull; 📞 (031) 8707329
                </div>
            </div>
        </header>

        <!-- Main Hero Section (2-Column Grid on Desktop) -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full my-auto py-8 sm:py-12">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                
                <!-- Kolom Kiri: Hero & Action -->
                <div class="lg:col-span-7 space-y-6 text-left">

                    <!-- Headline Utama -->
                    <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black tracking-tight text-slate-900 leading-[1.12]">
                        Wujudkan Pengalaman Kerja Nyata di <span class="text-blue-600 inline-block">Dispusip Surabaya</span>
                    </h1>

                    <!-- Deskripsi Sistem Informasi Resmi (Tanpa Style Bold) -->
                    <p class="text-sm sm:text-base text-slate-600 leading-relaxed max-w-2xl">
                        Sistem Informasi Resmi Pelaksanaan Magang dan Praktik Kerja Lapangan (PKL) di lingkungan Dinas Perpustakaan dan Kearsipan Kota Surabaya. Bergabunglah dalam transformasi layanan otomasi perpustakaan modern, preservasi arsip digital, dan pembudayaan literasi masyarakat.
                    </p>

                    <!-- Tombol Aksi Utama -->
                    <div class="flex flex-col sm:flex-row items-center justify-start gap-3.5 pt-2">
                        @auth
                            <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-7 py-3.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs sm:text-sm font-bold uppercase tracking-wider rounded-xl shadow-md shadow-blue-500/20 transition text-center flex items-center justify-center gap-2">
                                <span>Pindah ke Dashboard</span>
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

                    <!-- Info Kedinasan Dispusip (Desktop: Tetap di kolom kiri bawah tombol aksi) -->
                    <div class="dispusip-address-desktop pt-3 border-t border-slate-200/80 flex-wrap items-center gap-y-2 gap-x-4 text-xs text-slate-500">
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

                        <!-- 4 Unit Divisi Dispusip -->
                        <div class="space-y-2.5">
                            
                            <!-- Bidang 1: Bidang Sekretariat -->
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

            <!-- Info Kedinasan Dispusip (Mobile: Tampil Paling Akhir di Bawah Kartu Bidang) -->
            <div class="dispusip-address-mobile pt-6 mt-6 border-t border-slate-200/80 flex-col sm:flex-row items-center justify-center gap-2 sm:gap-x-4 text-xs text-slate-500 text-center sm:text-left">
                <div class="flex items-center gap-1.5 font-medium">
                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Jl. Rungkut Asri Tengah No. 5-7, Surabaya</span>
                </div>
                <div class="flex items-center gap-1.5 font-medium">
                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <span>(031) 8707682</span>
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