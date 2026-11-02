<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200 uppercase tracking-wider">
                        Histori & Galeri Resmi
                    </span>
                    <span class="text-xs text-slate-400">&bull;</span>
                    <span class="text-xs text-slate-500 font-medium">Dispusip Kota Surabaya</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-1">
                    Katalog Karya Inovasi dan Proyek Dinas
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Dokumentasi perjalanan karya inovasi dan kontribusi nyata mahasiswa magang dari tahun ke tahun.
                </p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Beranda Utama</span>
                </a>
                @auth
                    @if(Auth::user()->role === 'mahasiswa')
                        <a href="{{ route('student.projects.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-xs transition active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Unggah Karya Proyek</span>
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 sm:space-y-8">

            {{-- 1. KARTU METRIK STATISTIK KATALOG (Clean & Netral Sesuai Arahan Pengguna) --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-2xs">
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Karya Proyek</div>
                    <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">{{ number_format($stats['total_projects'] ?? 0) }}</div>
                    <div class="text-[11px] text-slate-500 font-medium mt-0.5">Inovasi Terpublikasi</div>
                </div>
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-2xs">
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Apresiasi Suka</div>
                    <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">{{ number_format($stats['total_likes'] ?? 0) }}</div>
                    <div class="text-[11px] text-slate-500 font-medium mt-0.5">Total Like Pengunjung</div>
                </div>
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-2xs">
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Ulasan & Diskusi</div>
                    <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">{{ number_format($stats['total_comments'] ?? 0) }}</div>
                    <div class="text-[11px] text-slate-500 font-medium mt-0.5">Komentar Masukan</div>
                </div>
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-2xs">
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Kategori Bidang</div>
                    <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">{{ number_format($stats['total_categories'] ?? 7) }}</div>
                    <div class="text-[11px] text-slate-500 font-medium mt-0.5">Ragam Fokus Inovasi</div>
                </div>
            </div>

            {{-- 2. BAR PENCARIAN & FILTER MULTI-DIMENSI --}}
            <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200 shadow-sm">
                <form method="GET" action="{{ route('public.projects.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 sm:gap-4 items-center">
                    
                    {{-- Pencarian Kata Kunci --}}
                    <div class="lg:col-span-4 relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" 
                               name="search" 
                               value="{{ $search }}" 
                               placeholder="Cari judul proyek, nama mahasiswa, kampus..." 
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                    </div>

                    {{-- Filter Kategori --}}
                    <div class="lg:col-span-3">
                        <select name="category" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition bg-white cursor-pointer">
                            <option value="all">Semua Kategori Karya</option>
                            @foreach(\App\Models\InternshipProject::CATEGORIES as $catKey => $catData)
                                <option value="{{ $catKey }}" {{ $category === $catKey ? 'selected' : '' }}>
                                    {{ $catData['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Tahun Perjalanan --}}
                    <div class="lg:col-span-2">
                        <select name="year" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition bg-white cursor-pointer">
                            <option value="all">Semua Tahun</option>
                            @foreach($availableYears as $yr)
                                <option value="{{ $yr }}" {{ (string)$year === (string)$yr ? 'selected' : '' }}>
                                    Tahun {{ $yr }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Pengurutan --}}
                    <div class="lg:col-span-2">
                        <select name="sort" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition bg-white cursor-pointer">
                            <option value="latest" {{ $sort === 'latest' ? 'selected' : '' }}>Terbaru</option>
                            <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>Paling Banyak Disukai</option>
                            <option value="discussed" {{ $sort === 'discussed' ? 'selected' : '' }}>Paling Banyak Didiskusikan</option>
                            <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Terlama</option>
                        </select>
                    </div>

                    {{-- Tombol Terapkan --}}
                    <div class="lg:col-span-1 flex gap-2">
                        <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-bold text-xs rounded-xl transition cursor-pointer shadow-sm flex items-center justify-center">
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            {{-- 3. DAFTAR TIMELINE PROYEK BERDASARKAN TAHUN --}}
            @if($groupedProjects->isEmpty())
                <div class="bg-white rounded-3xl p-10 sm:p-14 text-center border border-slate-200 shadow-2xs space-y-4">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-800">Belum Ada Proyek Ditemukan</h3>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-md mx-auto">
                            Tidak ada proyek inovasi yang sesuai dengan kata kunci atau filter yang Anda pilih. Coba sesuaikan kata pencarian Anda.
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('public.projects.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                            Reset Semua Filter
                        </a>
                    </div>
                </div>
            @else
                <div class="space-y-10 sm:space-y-12">
                    @foreach($groupedProjects as $yr => $projectList)
                        @if(!$loop->first)
                            {{-- Pembatas Gelombang Elegan Kustom Transisi Antar-Tahun --}}
                            <div class="py-2 sm:py-3 w-full">
                                <x-section-divider />
                            </div>
                        @endif

                        <div class="space-y-6 sm:space-y-8">
                            
                            {{-- Header Section Pembatas Tahun (Elegan & Minimalis Modern) --}}
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-slate-200/80">
                                <div class="flex items-center gap-3.5 sm:gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/30 shrink-0" style="background: #2563eb !important; background-color: #2563eb !important; color: #ffffff !important;">
                                        <svg class="w-6 h-6 text-white" style="color: #ffffff !important;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2.5 flex-wrap">
                                            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                                                Tahun Perjalanan {{ $yr }}
                                            </h2>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200/80 shadow-2xs">
                                                <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                                                <span>{{ $projectList->count() }} Karya Inovasi</span>
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-400 font-medium mt-0.5">
                                            Dokumentasi luaran karya dan kontribusi nyata mahasiswa magang pada periode tahun {{ $yr }}
                                        </p>
                                    </div>
                                </div>

                                <div class="hidden sm:flex items-center gap-2 text-xs font-semibold text-slate-400">
                                    <span>Arsip Karya Magang</span>
                                    <span class="text-slate-300">&bull;</span>
                                    <span class="font-mono text-[11px] font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100">
                                        DISPUSIP-{{ $yr }}
                                    </span>
                                </div>
                            </div>

                            {{-- Grid Kartu Proyek Inovasi Tahun Terkait --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-7">
                                @foreach($projectList as $project)
                                    @php
                                        $mhs = $project->placement?->application?->user;
                                        $univ = $mhs?->studentProfile?->universitas ?? $mhs?->university ?? 'Perguruan Tinggi Mitra';
                                        $unit = $project->placement?->application?->unit?->name ?? 'Dispusip Surabaya';
                                    @endphp

                                    <div class="group bg-white rounded-3xl border border-slate-200 hover:border-blue-300 hover:shadow-xl hover:shadow-blue-500/10 transition-all duration-300 flex flex-col overflow-hidden">
                                        
                                        {{-- Foto Sampul Proyek (Klik Langsung ke Detail) --}}
                                        <div class="relative w-full aspect-video bg-slate-100 overflow-hidden shrink-0">
                                            <a href="{{ route('projects.show', $project->id) }}" class="block w-full h-full cursor-pointer">
                                                @if($project->photo_url)
                                                    <img src="{{ $project->photo_url }}" 
                                                         alt="{{ $project->title }}" 
                                                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                                         loading="lazy">
                                                @else
                                                    <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 text-slate-400">
                                                        <svg class="w-10 h-10 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                        <span class="text-[10px] font-semibold">Dokumentasi Proyek</span>
                                                    </div>
                                                @endif
                                            </a>

                                            {{-- Badge Kategori Floating --}}
                                            <div class="absolute top-3 left-3 flex flex-wrap gap-1.5 z-10 pointer-events-none">
                                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold shadow-sm backdrop-blur-md {{ $project->category_badge_class }}">
                                                    {{ $project->category_label }}
                                                </span>
                                                @if(! $project->is_public)
                                                    <span class="px-2 py-1 rounded-xl text-[10px] font-bold bg-amber-500 text-white shadow-sm">
                                                        Privat
                                                    </span>
                                                @endif
                                            </div>

                                            {{-- Menu Opsi 3-Titik Khusus Admin & Supervisor --}}
                                            @auth
                                                @if(Auth::user()->isSuperAdmin() || in_array(Auth::user()->role, ['super_admin', 'superadmin', 'supervisor']))
                                                    <div x-data="{ menuOpen: false, copied: false }" class="absolute top-3 right-3 z-20" @click.outside="menuOpen = false">
                                                        <button @click.prevent.stop="menuOpen = !menuOpen" 
                                                                type="button" 
                                                                class="w-7 h-7 rounded-xl bg-slate-900/70 hover:bg-slate-900 text-white flex items-center justify-center transition backdrop-blur-xs cursor-pointer shadow-sm"
                                                                title="Opsi Proyek">
                                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                                                        </button>
                                                        <div x-show="menuOpen" 
                                                             x-cloak 
                                                             x-transition:enter="transition ease-out duration-100"
                                                             x-transition:enter-start="opacity-0 scale-95"
                                                             x-transition:enter-end="opacity-100 scale-100"
                                                             x-transition:leave="transition ease-in duration-75"
                                                             x-transition:leave-start="opacity-100 scale-100"
                                                             x-transition:leave-end="opacity-0 scale-95"
                                                             class="absolute right-0 top-full mt-1.5 w-48 bg-white rounded-2xl shadow-xl border border-slate-200 py-1.5 z-30 text-xs">
                                                            
                                                            {{-- 1. Sembunyikan / Publikasikan --}}
                                                            <form action="{{ route('projects.toggle_visibility', $project->id) }}" method="POST" @click.stop>
                                                                @csrf
                                                                <button type="submit" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-2 text-slate-700 font-semibold cursor-pointer">
                                                                    @if($project->is_public)
                                                                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                                                        <span>Sembunyikan Proyek</span>
                                                                    @else
                                                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                                        <span>Tampilkan ke Publik</span>
                                                                    @endif
                                                                </button>
                                                            </form>

                                                            {{-- 2. Salin Tautan --}}
                                                            <button type="button" 
                                                                    @click.prevent.stop="navigator.clipboard.writeText('{{ route('projects.show', $project->id) }}'); copied = true; setTimeout(() => { copied = false; menuOpen = false; }, 1800);" 
                                                                    class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-2 text-slate-700 font-semibold cursor-pointer">
                                                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                                <span x-text="copied ? 'Tautan Tersalin! ✓' : 'Salin Tautan'">Salin Tautan</span>
                                                            </button>

                                                            @if(Auth::user()->isSuperAdmin() || in_array(Auth::user()->role, ['super_admin', 'superadmin']))
                                                                {{-- 3. Hapus Proyek (Khusus Super Admin) --}}
                                                                <div class="h-px bg-slate-100 my-1"></div>
                                                                <form action="{{ route('projects.destroy', $project->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus proyek ini secara permanen?');" @click.stop>
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="w-full text-left px-3.5 py-2 hover:bg-rose-50 flex items-center gap-2 text-rose-600 font-semibold cursor-pointer">
                                                                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                                        <span>Hapus Proyek</span>
                                                                    </button>
                                                                </form>
                                                            @endif

                                                        </div>
                                                    </div>
                                                @endif
                                            @endauth

                                            {{-- Quick Stats (Likes & Comments Overlay - Pure White Outline Icons Sesuai Permintaan Pengguna) --}}
                                            <div class="absolute bottom-2.5 right-3 flex items-center gap-2 px-2.5 py-1 rounded-lg bg-slate-900/70 backdrop-blur-xs text-white text-[11px] font-bold pointer-events-none">
                                                <span class="flex items-center gap-1 text-white">
                                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                                    <span class="text-white">{{ $project->likes_count }}</span>
                                                </span>
                                                <span class="text-white/40">&bull;</span>
                                                <span class="flex items-center gap-1 text-white">
                                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                                    <span class="text-white">{{ $project->comments_count }}</span>
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Konten Informasi Kartu --}}
                                        <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                                            <div class="space-y-2">
                                                <div class="text-[11px] text-blue-600 font-bold uppercase tracking-wider line-clamp-1">
                                                    {{ $unit }}
                                                </div>
                                                {{-- Judul Proyek (Klik Langsung ke Detail) --}}
                                                <a href="{{ route('projects.show', $project->id) }}" class="block group/title">
                                                    <h3 class="font-extrabold text-sm sm:text-base text-slate-900 group-hover/title:text-blue-600 transition-colors line-clamp-2 leading-snug">
                                                        {{ $project->title }}
                                                    </h3>
                                                </a>
                                                <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">
                                                    {{ $project->description }}
                                                </p>
                                            </div>

                                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                                                <div class="min-w-0">
                                                    <div class="text-xs font-bold text-slate-800 truncate">
                                                        {{ $mhs->name ?? 'Mahasiswa Magang' }}
                                                    </div>
                                                    <div class="text-[10px] text-slate-400 font-medium truncate">
                                                        {{ $univ }}
                                                    </div>
                                                </div>

                                                <a href="{{ route('projects.show', $project->id) }}" 
                                                   class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-50 group-hover:bg-blue-600 group-hover:text-white text-slate-700 text-xs font-bold transition">
                                                    <span>Buka Detail</span>
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                                </a>
                                            </div>

                                        </div>

                                    </div>
                                @endforeach
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
