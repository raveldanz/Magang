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
    <body class="bg-[#F8FAFC] text-slate-900 font-sans antialiased selection:bg-blue-100 selection:text-blue-700 min-h-screen lg:h-screen lg:overflow-hidden flex flex-col justify-between"
          x-data="{ openQuotaModal: false }">

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

        <!-- Header Navigasi -->
        <header class="w-full bg-white/95 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-30 shrink-0">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-18 flex items-center justify-between">
                <!-- Logo & Brand Dispusip Surabaya -->
                <div class="flex items-center gap-3 sm:gap-4">
                    <img src="{{ asset('images/logoPemkotSBY.png') }}" alt="Logo Pemkot Surabaya" 
                         class="h-8 sm:h-9 w-auto object-contain"
                         style="aspect-ratio: 960/1234; height: 34px; width: auto; object-fit: contain;">
                    <div class="h-5 w-px bg-slate-200"></div>
                    <img src="{{ asset('images/logos/dispusip.png') }}" alt="Logo Dispusip Surabaya" 
                         class="h-8 sm:h-9 w-auto object-contain"
                         style="height: 34px; width: auto; object-fit: contain;">
                    <div class="flex flex-col">
                        <span class="text-xs sm:text-sm font-extrabold tracking-tight text-slate-900 leading-tight">Portal Magang Dispusip</span>
                        <span class="text-[9px] sm:text-[10px] font-bold tracking-wider uppercase text-blue-700">Dinas Perpustakaan dan Kearsipan Kota Surabaya</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Hero Section (Responsif: App Card di Mobile, 2-Kolom di Desktop) -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full flex-1 flex items-center justify-center py-5 lg:py-0">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center w-full">
                
                <!-- Kolom Kiri: Bungkus Card di Mobile, Polosan di Desktop -->
                <div class="lg:col-span-7 w-full">
                    <div class="bg-white lg:bg-transparent rounded-3xl lg:rounded-none p-5 sm:p-7 lg:p-0 shadow-lg shadow-slate-200/50 lg:shadow-none border border-slate-200/80 lg:border-none space-y-4 lg:space-y-5">
                        
                        <!-- Pemicu Sisa Kuota (Desktop: Badge | Mobile: Tombol Banner Interaktif) -->
                        <div>
                            <!-- Desktop -->
                            <span class="hidden lg:inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-lg shadow-2xs">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span>Sisa Kuota: {{ $totalRemaining }} Kursi</span>
                            </span>

                            <!-- Mobile: Banner Interaktif yang Penuh dan Bernyawa -->
                            <button type="button" 
                                    @click="openQuotaModal = true"
                                    class="w-full lg:hidden flex items-center justify-between p-3 rounded-2xl bg-gradient-to-r from-emerald-50 via-teal-50/60 to-blue-50/40 border border-emerald-200/80 active:scale-[0.98] transition cursor-pointer text-left">
                                <div class="flex items-center gap-2.5">
                                    <span class="relative flex h-2.5 w-2.5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                                    </span>
                                    <div>
                                        <p class="text-xs font-extrabold text-slate-800 leading-tight">Sisa Kuota: {{ $totalRemaining }} Kursi Tersedia</p>
                                        <p class="text-[10px] text-slate-500 font-medium">Klik untuk rincian 4 bidang magang</p>
                                    </div>
                                </div>

                            </button>
                        </div>

                        <!-- Headline Utama -->
                        <h1 class="text-2xl sm:text-3xl lg:text-[42px] xl:text-5xl font-black tracking-tight text-slate-900 leading-[1.18]">
                            Wujudkan Pengalaman Kerja Nyata di <span class="text-blue-600 inline-block">Dispusip Surabaya</span>
                        </h1>

                        <!-- Deskripsi Sistem Informasi Resmi -->
                        <p class="text-xs sm:text-sm lg:text-[13px] xl:text-sm text-slate-600 leading-relaxed">
                            Sistem Informasi Resmi Pelaksanaan Magang dan Praktik Kerja Lapangan (PKL) di lingkungan <strong>Dinas Perpustakaan dan Kearsipan Kota Surabaya</strong>. Bergabunglah dalam transformasi layanan otomasi perpustakaan modern, preservasi arsip digital, dan pembudayaan literasi masyarakat.
                        </p>

                        <!-- Tombol Aksi Utama -->
                        <div class="flex flex-col sm:flex-row items-center justify-start gap-3 pt-1">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto px-6 py-3.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs sm:text-sm font-bold uppercase tracking-wider rounded-xl shadow-md shadow-blue-500/20 transition text-center flex items-center justify-center gap-2">
                                    <span>Masuk ke Panel Dashboard</span>
                                     </a>
                            @else
                                <a href="{{ route('register') }}" class="w-full sm:w-auto px-6 py-3.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs sm:text-sm font-bold uppercase tracking-wider rounded-xl shadow-md shadow-blue-500/20 transition text-center flex items-center justify-center gap-2">
                                    <span>Daftar Magang Sekarang</span>
                                    </a>
                                <a href="{{ route('login') }}" class="w-full sm:w-auto px-6 py-3.5 bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-xs sm:text-sm font-bold uppercase tracking-wider rounded-xl transition text-center shadow-2xs">
                                    Masuk ke Akun
                                </a>
                            @endauth
                        </div>

                        <!-- Info Lokasi & Kontak Kedinasan -->
                        <div class="pt-3 border-t border-slate-100 lg:border-slate-200/80 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-x-4 text-xs text-slate-500 font-medium">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>Mall Pelayanan Publik Siola, Lt. 4, Surabaya</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <span>(031) 99277752</span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Kolom Kanan: Kartu Bidang Magang (TETAP PERSIS SEPERTI ASLI DI DESKTOP, HILANG DI MOBILE) -->
                <div class="hidden lg:block lg:col-span-5 w-full">
                    <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-5 lg:p-5 shadow-lg shadow-slate-200/50 border border-slate-200/90 space-y-3">
                        
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                            <div>
                                <h2 class="text-xs sm:text-sm font-extrabold text-slate-900 tracking-tight">Bidang Pilihan Magang</h2>
                                <p class="text-[10px] sm:text-[11px] text-slate-500 font-medium">4 Bidang Kerja Aktif di Dispusip Surabaya</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-blue-100 text-blue-700">Terbuka MBKM</span>
                        </div>

                        <!-- 4 Unit Divisi Dispusip Compact -->
                        <div class="space-y-2">
                            
                            <!-- Bidang 1: Bidang Sekretariat -->
                            <div class="p-2.5 sm:p-3 rounded-xl bg-slate-50/80 hover:bg-blue-50/40 border border-slate-200/70 hover:border-blue-200 transition group">
                                <div class="flex items-center justify-between gap-2">
                                    <h3 class="text-xs font-bold text-slate-900 group-hover:text-blue-700 transition">Bidang Sekretariat</h3>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-100/70 px-1.5 py-0.5 rounded shrink-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        Sisa: {{ $uSekretariat?->remaining_quota ?? 8 }} Kuota
                                    </span>
                                </div>
                                <p class="text-[10px] sm:text-[11px] text-slate-500 mt-0.5 line-clamp-1">Administrasi umum, kepegawaian, tata persuratan, perencanaan & keuangan.</p>
                            </div>

                            <!-- Bidang 2: Bidang Pembinaan dan Pengelolaan Perpustakaan -->
                            <div class="p-2.5 sm:p-3 rounded-xl bg-slate-50/80 hover:bg-blue-50/40 border border-slate-200/70 hover:border-blue-200 transition group">
                                <div class="flex items-center justify-between gap-2">
                                    <h3 class="text-xs font-bold text-slate-900 group-hover:text-blue-700 transition">Pembinaan & Pengelolaan Perpustakaan</h3>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-100/70 px-1.5 py-0.5 rounded shrink-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        Sisa: {{ $uPerpus?->remaining_quota ?? 9 }} Kuota
                                    </span>
                                </div>
                                <p class="text-[10px] sm:text-[11px] text-slate-500 mt-0.5 line-clamp-1">Pengembangan perpustakaan, otomasi katalog OPAC & layanan pemustaka.</p>
                            </div>

                            <!-- Bidang 3: Bidang Pembinaan dan Layanan Kearsipan -->
                            <div class="p-2.5 sm:p-3 rounded-xl bg-slate-50/80 hover:bg-blue-50/40 border border-slate-200/70 hover:border-blue-200 transition group">
                                <div class="flex items-center justify-between gap-2">
                                    <h3 class="text-xs font-bold text-slate-900 group-hover:text-blue-700 transition">Pembinaan & Layanan Kearsipan</h3>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-100/70 px-1.5 py-0.5 rounded shrink-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        Sisa: {{ $uLayananArsip?->remaining_quota ?? 8 }} Kuota
                                    </span>
                                </div>
                                <p class="text-[10px] sm:text-[11px] text-slate-500 mt-0.5 line-clamp-1">Pembinaan kearsipan instansi, pemanfaatan arsip dinamis & statis.</p>
                            </div>

                            <!-- Bidang 4: Bidang Pengelolaan, Perlindungan, dan Penyelamatan Kearsipan -->
                            <div class="p-2.5 sm:p-3 rounded-xl bg-slate-50/80 hover:bg-blue-50/40 border border-slate-200/70 hover:border-blue-200 transition group">
                                <div class="flex items-center justify-between gap-2">
                                    <h3 class="text-xs font-bold text-slate-900 group-hover:text-blue-700 transition">Pengelolaan, Perlindungan & Penyelamatan Kearsipan</h3>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-100/70 px-1.5 py-0.5 rounded shrink-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        Sisa: {{ $uPenyelamatanArsip?->remaining_quota ?? 8 }} Kuota
                                    </span>
                                </div>
                                <p class="text-[10px] sm:text-[11px] text-slate-500 mt-0.5 line-clamp-1">Restorasi naskah kuno bersejarah, alih media digital & preservasi arsip.</p>
                            </div>

                        </div>

                        <!-- Footer Catatan -->
                        <div class="p-2.5 bg-blue-50/70 rounded-xl border border-blue-100 text-[10px] sm:text-[11px] text-blue-900 leading-relaxed font-medium">
                            Terbuka bagi mahasiswa D3/D4/S1 semua jurusan & siswa SMK yang bermitra dengan Dispusip Kota Surabaya.
                        </div>

                    </div>
                </div>

            </div>
        </main>

        <!-- Footer -->
        <footer class="w-full bg-white border-t border-slate-200/80 py-2.5 shrink-0">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-1 text-[11px] sm:text-xs text-slate-500 text-center sm:text-left">
                <p>&copy; {{ date('Y') }} Dinas Perpustakaan dan Kearsipan Kota Surabaya. Hak Cipta Dilindungi.</p>
                </div>
        </footer>

        <!-- MODAL POPUP KHUSUS (Dipanggil di Mobile saat tombol Sisa Kuota ditekan) -->
        <div x-show="openQuotaModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto"
             role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4 text-center">
                
                <!-- Backdrop Gelap -->
                <div x-show="openQuotaModal" 
                     x-transition:enter="ease-out duration-200" 
                     x-transition:enter-start="opacity-0" 
                     x-transition:enter-end="opacity-100" 
                     x-transition:leave="ease-in duration-150" 
                     x-transition:leave-start="opacity-100" 
                     x-transition:leave-end="opacity-0" 
                     @click="openQuotaModal = false" 
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

                <!-- Kontainer Dialog Modal Pop-up Mobile -->
                <div x-show="openQuotaModal" 
                     x-transition:enter="ease-out duration-200" 
                     x-transition:enter-start="opacity-0 scale-95" 
                     x-transition:enter-end="opacity-100 scale-100" 
                     x-transition:leave="ease-in duration-150" 
                     x-transition:leave-start="opacity-100 scale-100" 
                     x-transition:leave-end="opacity-0 scale-95" 
                     class="relative w-full max-w-sm sm:max-w-md transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all border border-slate-200 p-5 space-y-4 z-10">

                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="text-sm sm:text-base font-extrabold text-slate-900">Bidang Pilihan Magang</h3>
                            <p class="text-[11px] text-slate-500">4 Bidang Kerja Aktif di Dispusip Surabaya</p>
                        </div>
                        <button type="button" 
                                @click="openQuotaModal = false" 
                                class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- 4 Bidang Magang Pop-up -->
                    <div class="space-y-2.5 max-h-[60vh] overflow-y-auto pr-1">
                        <!-- Bidang 1 -->
                        <div class="p-3 rounded-2xl bg-slate-50/80 border border-slate-200/80">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="text-xs font-bold text-slate-900">Bidang Sekretariat</h4>
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-md shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    Sisa: {{ $uSekretariat?->remaining_quota ?? 8 }} Kuota
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">Administrasi umum, kepegawaian, tata persuratan, perencanaan & keuangan.</p>
                        </div>

                        <!-- Bidang 2 -->
                        <div class="p-3 rounded-2xl bg-slate-50/80 border border-slate-200/80">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="text-xs font-bold text-slate-900">Pembinaan & Pengelolaan Perpustakaan</h4>
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-md shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    Sisa: {{ $uPerpus?->remaining_quota ?? 9 }} Kuota
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">Pengembangan perpustakaan, otomasi katalog OPAC & layanan pemustaka.</p>
                        </div>

                        <!-- Bidang 3 -->
                        <div class="p-3 rounded-2xl bg-slate-50/80 border border-slate-200/80">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="text-xs font-bold text-slate-900">Pembinaan & Layanan Kearsipan</h4>
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-md shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    Sisa: {{ $uLayananArsip?->remaining_quota ?? 8 }} Kuota
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">Pembinaan kearsipan instansi, pemanfaatan arsip dinamis & statis.</p>
                        </div>

                        <!-- Bidang 4 -->
                        <div class="p-3 rounded-2xl bg-slate-50/80 border border-slate-200/80">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="text-xs font-bold text-slate-900">Pengelolaan, Perlindungan & Penyelamatan Kearsipan</h4>
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-md shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    Sisa: {{ $uPenyelamatanArsip?->remaining_quota ?? 8 }} Kuota
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">Restorasi naskah kuno bersejarah, alih media digital & preservasi arsip.</p>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="pt-2 flex items-center justify-between border-t border-slate-100">
                        <span class="text-[10px] text-slate-400 font-medium">Terbuka MBKM & SMK</span>
                        <button type="button" 
                                @click="openQuotaModal = false" 
                                class="px-4 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer">
                            Tutup
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </body>
</html>