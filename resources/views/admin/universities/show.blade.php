<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.universities.index') }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition shadow-2xs" title="Kembali ke Master Universitas">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                        <span>Pusat Kendali Perguruan Tinggi: {{ $university->name }}</span>
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                        Manajemen terpadu Dosen Pembimbing (DPL), Mahasiswa, Akun Admin Portal, dan Kebijakan Penilaian
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('admin.universities.export_students', $university->id) }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 shadow-2xs transition active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Export Rekap (.CSV)</span>
                </a>

                <a href="{{ route('admin.universities.edit', $university->id) }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit Profil & Kebijakan</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ 
        activeTab: '{{ request('tab', $activeTab ?? 'dosen') }}',
        showAddDosenModal: false,
        showAssignAdvisorModal: false,
        selectedApplicationId: null,
        selectedStudentName: ''
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Notifications -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-xs flex items-center justify-between text-rose-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- Hero Section: Identitas Resmi Kampus, PIC Rektorat, & Kebijakan Penilaian -->
            @php
                $univLogoUrl = null;
                if (!empty($university->logo)) {
                    if (file_exists(public_path($university->logo))) {
                        $univLogoUrl = asset($university->logo);
                    } elseif (file_exists(public_path('storage/' . $university->logo))) {
                        $univLogoUrl = asset('storage/' . $university->logo);
                    } elseif (file_exists(storage_path('app/public/' . $university->logo))) {
                        $univLogoUrl = asset('storage/' . $university->logo);
                    }
                }
                if (!$univLogoUrl) {
                    $uName = strtolower($university->name ?? '');
                    $uCode = strtolower($university->code ?? '');
                    if (str_contains($uName, 'unesa') || str_contains($uCode, 'unesa')) {
                        $univLogoUrl = asset('images/logos/unesa.png');
                    } elseif (str_contains($uName, 'its') || str_contains($uCode, 'its') || str_contains($uName, 'sepuluh nopember')) {
                        $univLogoUrl = asset('images/logos/its.png');
                    } elseif (str_contains($uName, 'unair') || str_contains($uCode, 'unair') || str_contains($uName, 'airlangga')) {
                        $univLogoUrl = asset('images/logos/unair.png');
                    } elseif (str_contains($uName, 'upn') || str_contains($uCode, 'upn') || str_contains($uName, 'veteran')) {
                        $univLogoUrl = asset('images/logos/upnjatim.png');
                    } elseif (str_contains($uName, 'unitomo') || str_contains($uCode, 'unitomo') || str_contains($uName, 'soetomo')) {
                        $univLogoUrl = asset('images/logos/unitomo.png');
                    }
                }
            @endphp

            <div class="relative overflow-hidden rounded-3xl shadow-xl border border-blue-400/30 p-6 sm:p-8"
                 style="background: linear-gradient(135deg, #09172e 0%, #0d2857 50%, #07152c 100%) !important; color: #ffffff !important;">
                
                <!-- Watermark Lambang Perisai Kedinasan Pemkot Surabaya -->
                <div class="absolute -right-8 -bottom-10 pointer-events-none opacity-[0.06] text-white">
                    <svg class="w-80 h-80" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 2.18l7 3.12v4.7c0 4.67-3.13 9.04-7 10.19-3.87-1.15-7-5.52-7-10.19V6.3l7-3.12z"/>
                    </svg>
                </div>

                <div class="relative z-10 flex flex-col lg:flex-row lg:items-start justify-between gap-6">
                    <!-- Sisi Kiri: Logo Resmi, Nama & Informasi Kontak -->
                    <div class="flex items-start gap-4 sm:gap-6 flex-1">
                        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-white p-3 shadow-md flex items-center justify-center shrink-0 overflow-hidden border border-white/20"
                             style="width: 88px; height: 88px; min-width: 88px; min-height: 88px; max-width: 88px; max-height: 88px;">
                            @if($univLogoUrl)
                                <img src="{{ $univLogoUrl }}" alt="Logo {{ $university->name }}" class="w-16 h-16 object-contain shrink-0" style="width: 64px; height: 64px; max-width: 64px; max-height: 64px; object-fit: contain;">
                            @else
                                <img src="{{ asset('images/default-university.svg') }}" alt="Default Logo {{ $university->name }}" class="w-16 h-16 object-contain shrink-0" style="width: 64px; height: 64px; max-width: 64px; max-height: 64px; object-fit: contain;">
                            @endif
                        </div>

                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-blue-500/30 text-blue-200 border border-blue-400/30">
                                    {{ $university->code }}
                                </span>
                                @if($university->universityAdmin)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                        Akun Admin Aktif
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-400/30 animate-pulse">
                                        Belum Ada Akun Admin
                                    </span>
                                @endif

                                @if($university->evaluation_scheme === 'mentor_only')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-500/20 text-purple-200 border border-purple-400/30">
                                        Skema: 100% Mentor Dinas
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-teal-500/20 text-teal-200 border border-teal-400/30">
                                        Skema Ganda (Mentor {{ $university->weight_mentor ?? 40 }}% : DPL {{ $university->weight_lecturer ?? 60 }}%)
                                    </span>
                                @endif
                            </div>

                            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight leading-snug">
                                {{ $university->name }}
                            </h1>

                            <p class="text-xs sm:text-sm text-blue-100/80 leading-relaxed max-w-2xl">
                                {{ $university->address ?? 'Alamat kampus belum diatur.' }}
                            </p>

                            <div class="flex flex-wrap items-center gap-4 text-xs text-blue-200/90 pt-1">
                                @if($university->email)
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        <span class="font-mono">{{ $university->email }}</span>
                                    </div>
                                @endif
                                @if($university->phone)
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                        <span>{{ $university->phone }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Sisi Kanan: Kartu PIC Rektorat & Quick Action Login As -->
                    <div class="w-full lg:w-80 p-4 rounded-2xl bg-slate-900/60 border border-blue-400/20 backdrop-blur-md space-y-3 shrink-0">
                        <div class="flex items-center justify-between border-b border-blue-400/10 pb-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-blue-300 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                PIC Kemahasiswaan / Rektorat
                            </span>
                        </div>

                        <div class="space-y-1">
                            <div class="text-sm font-black text-white truncate">
                                {{ $university->pic_name ?? 'Belum Ditentukan' }}
                            </div>
                            <div class="text-xs text-blue-200/80">
                                {{ $university->pic_position ?? 'Pimpinan / Penanggung Jawab MBKM' }}
                            </div>
                            @if($university->pic_nip)
                                <div class="text-[11px] font-mono text-blue-300/70">
                                    NIP: {{ $university->pic_nip }}
                                </div>
                            @endif
                        </div>

                        <div class="pt-2 border-t border-blue-400/10 flex items-center gap-2">
                            @if($university->universityAdmin)
                                @if($isSuperAdmin)
                                    <form action="{{ route('admin.impersonate', $university->universityAdmin->id) }}" method="POST" class="w-full m-0">
                                        @csrf
                                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white rounded-xl text-xs font-bold transition shadow-sm cursor-pointer active:scale-95">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                                            <span>Masuk Sebagai Admin Kampus</span>
                                        </button>
                                    </form>
                                @endif
                            @else
                                <form action="{{ route('admin.universities.create_account', $university->id) }}" method="POST" class="w-full m-0">
                                    @csrf
                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-sm cursor-pointer active:scale-95">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <span>Buatkan Akun Admin Kampus</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5 Macro Metrik Kampus -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 sm:gap-4">
                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-2xs">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Mahasiswa</span>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl font-black text-slate-900">{{ $stats['total_students'] }}</span>
                        <span class="text-xs text-slate-500 font-medium">Orang</span>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-2xs">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Dosen DPL</span>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl font-black text-indigo-600">{{ $stats['total_dosens'] }}</span>
                        <span class="text-xs text-slate-500 font-medium">Terdaftar</span>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-2xs">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Aktif Magang</span>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl font-black text-blue-600">{{ $stats['active_interns'] }}</span>
                        <span class="text-xs text-slate-500 font-medium">Di Dinas</span>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-2xs">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Lulus / Selesai</span>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl font-black text-emerald-600">{{ $stats['completed_interns'] }}</span>
                        <span class="text-xs text-slate-500 font-medium">Alumni</span>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-2xs col-span-2 sm:col-span-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Rata-Rata Nilai</span>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl font-black text-amber-600">{{ $stats['average_score'] ?? '-' }}</span>
                        <span class="text-xs text-slate-500 font-medium">Skala 100</span>
                    </div>
                </div>
            </div>

            <!-- Tab Navigasi Manajemen Terpadu -->
            <div class="border-b border-slate-200">
                <nav class="flex space-x-2 sm:space-x-4 overflow-x-auto pb-px" aria-label="Tabs">
                    <!-- Tab 1: Dosen Pembimbing (DPL) -->
                    <button type="button" 
                            @click="activeTab = 'dosen'" 
                            :class="activeTab === 'dosen' ? 'border-blue-600 text-blue-600 font-black' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-semibold'"
                            class="whitespace-nowrap py-3 px-3 sm:px-4 border-b-2 text-xs sm:text-sm flex items-center gap-2 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>Dosen Pembimbing (DPL)</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="activeTab === 'dosen' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600'">
                            {{ $dosens->count() }}
                        </span>
                    </button>

                    <!-- Tab 2: Mahasiswa Terdaftar & Status Magang -->
                    <button type="button" 
                            @click="activeTab = 'mahasiswa'" 
                            :class="activeTab === 'mahasiswa' ? 'border-blue-600 text-blue-600 font-black' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-semibold'"
                            class="whitespace-nowrap py-3 px-3 sm:px-4 border-b-2 text-xs sm:text-sm flex items-center gap-2 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Mahasiswa Kampus</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="activeTab === 'mahasiswa' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600'">
                            {{ $students->count() }}
                        </span>
                    </button>

                    <!-- Tab 3: Akun Admin Portal Kampus -->
                    <button type="button" 
                            @click="activeTab = 'admin'" 
                            :class="activeTab === 'admin' ? 'border-blue-600 text-blue-600 font-black' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-semibold'"
                            class="whitespace-nowrap py-3 px-3 sm:px-4 border-b-2 text-xs sm:text-sm flex items-center gap-2 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Akun Admin Kampus</span>
                    </button>

                    <!-- Tab 4: Kebijakan Penilaian & Dokumen -->
                    <button type="button" 
                            @click="activeTab = 'policy'" 
                            :class="activeTab === 'policy' ? 'border-blue-600 text-blue-600 font-black' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-semibold'"
                            class="whitespace-nowrap py-3 px-3 sm:px-4 border-b-2 text-xs sm:text-sm flex items-center gap-2 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Kebijakan Penilaian</span>
                    </button>
                </nav>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 1: DOSEN PEMBIMBING LAPANGAN (DPL) KAMPUS                  -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'dosen'" class="space-y-4" style="display: none;">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-100 shadow-2xs">
                    <div>
                        <h3 class="text-sm font-black text-slate-900">Dosen Pembimbing Lapangan (DPL) Terdaftar</h3>
                        <p class="text-xs text-slate-500">Kelola akun dosen pembimbing akademik dari {{ $university->name }} yang memantau & menilai logbook mahasiswa</p>
                    </div>

                    <button type="button" 
                            @click="showAddDosenModal = true"
                            class="inline-flex items-center gap-2 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Tambah DPL Baru</span>
                    </button>
                </div>

                <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                                    <th class="p-3.5 pl-4">Dosen Pembimbing</th>
                                    <th class="p-3.5">Email & Kontak</th>
                                    <th class="p-3.5 text-center">Bimbingan Aktif</th>
                                    <th class="p-3.5 text-center">Bimbingan Selesai</th>
                                    <th class="p-3.5 text-center">Total Mahasiswa</th>
                                    <th class="p-3.5 pr-4 text-right">Aksi Super Admin</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                                @forelse($dosens as $dosen)
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="p-3.5 pl-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs shrink-0">
                                                    {{ strtoupper(substr($dosen->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-900">{{ $dosen->name }}</div>
                                                    <div class="text-[11px] text-slate-500">Perguruan Tinggi: {{ $university->name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-3.5">
                                            <div class="font-mono text-slate-800">{{ $dosen->email }}</div>
                                            <div class="text-[11px] text-slate-400">{{ $dosen->phone ?? 'Nomor telp belum diisi' }}</div>
                                        </td>
                                        <td class="p-3.5 text-center">
                                            <span class="px-2.5 py-1 rounded-full text-[11px] font-black {{ $dosen->active_students_count > 0 ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-50 text-slate-500' }}">
                                                {{ $dosen->active_students_count }} Mhs
                                            </span>
                                        </td>
                                        <td class="p-3.5 text-center">
                                            <span class="px-2.5 py-1 rounded-full text-[11px] font-black {{ $dosen->completed_students_count > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-50 text-slate-500' }}">
                                                {{ $dosen->completed_students_count }} Mhs
                                            </span>
                                        </td>
                                        <td class="p-3.5 text-center font-bold text-slate-900">
                                            {{ $dosen->total_students_count }}
                                        </td>
                                        <td class="p-3.5 pr-4 text-right">
                                            <div class="inline-flex items-center gap-1.5">
                                                @if($isSuperAdmin)
                                                    <form action="{{ route('admin.impersonate', $dosen->id) }}" method="POST" class="inline-block m-0">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-[11px] font-bold transition shadow-xs cursor-pointer" title="Login As sebagai Dosen ini">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                                            <span>Login As</span>
                                                        </button>
                                                    </form>
                                                @endif

                                                <form action="{{ route('admin.universities.dosens.reset_password', [$university->id, $dosen->id]) }}" method="POST" class="inline-block m-0" onsubmit="return confirm('Reset password Dosen {{ $dosen->name }} ke password default (\'password\')?');">
                                                    @csrf
                                                    <button type="submit" class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg transition cursor-pointer" title="Reset Password ke default ('password')">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                                    </button>
                                                </form>

                                                <button type="button" 
                                                        @click="$dispatch('open-delete-modal', {
                                                            action: '{{ route('admin.universities.dosens.destroy', [$university->id, $dosen->id]) }}',
                                                            title: 'Hapus Dosen Pembimbing',
                                                            name: '{{ addslashes($dosen->name) }}',
                                                            desc: 'Email: {{ $dosen->email }} &bull; {{ $dosen->total_students_count }} Mahasiswa Bimbingan'
                                                        })" 
                                                        class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer"
                                                        title="Hapus Dosen Pembimbing">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="p-8 text-center text-slate-400">
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                                <p class="font-bold text-slate-600">Belum ada Dosen Pembimbing (DPL) terdaftar untuk kampus ini</p>
                                                <p class="text-xs text-slate-400">Tambahkan DPL baru menggunakan tombol di atas agar dapat ditugaskan membimbing mahasiswa.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 2: MAHASISWA KAMPUS TERDAFTAR & STATUS MAGANG              -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'mahasiswa'" class="space-y-4" style="display: none;">
                <!-- Filter Status Mahasiswa & Bilah Pencarian -->
                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-2xs space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-black text-slate-900">Daftar Mahasiswa Asal {{ $university->name }}</h3>
                            <p class="text-xs text-slate-500">Pantau status pendaftaran, instansi penempatan, DPL pembimbing, dan perkembangan nilai</p>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.applications.index', ['university_id' => $university->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold border border-blue-200 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <span>Buka di Pengajuan Masuk</span>
                            </a>
                        </div>
                    </div>

                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('admin.universities.show', $university->id) }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 pt-2 border-t border-slate-100">
                        <input type="hidden" name="tab" value="mahasiswa">
                        
                        <div class="relative flex-1">
                            <input type="text" name="student_search" value="{{ request('student_search') }}" placeholder="Cari nama mahasiswa, NIM, atau program studi..." 
                                   class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500"
                                   style="padding-left: 2.25rem !important; padding-right: 0.75rem !important; padding-top: 0.5rem !important; padding-bottom: 0.5rem !important;">
                            <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none text-slate-400 pl-3">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                        </div>

                        <select name="student_status" class="text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 py-2">
                            <option value="">Semua Status Mahasiswa</option>
                            <option value="active" {{ request('student_status') === 'active' ? 'selected' : '' }}>Aktif Magang (Accepted)</option>
                            <option value="completed" {{ request('student_status') === 'completed' ? 'selected' : '' }}>Lulus / Selesai (Completed)</option>
                            <option value="pending" {{ request('student_status') === 'pending' ? 'selected' : '' }}>Menunggu Seleksi (Pending)</option>
                            <option value="no_application" {{ request('student_status') === 'no_application' ? 'selected' : '' }}>Belum Mengajukan</option>
                        </select>

                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-2xs cursor-pointer">
                            Filter
                        </button>
                        @if(request('student_search') || request('student_status'))
                            <a href="{{ route('admin.universities.show', ['university' => $university->id, 'tab' => 'mahasiswa']) }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition text-center">
                                Reset
                            </a>
                        @endif
                    </form>
                </div>

                <!-- Table Mahasiswa -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-2xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                                    <th class="p-3.5 pl-4">Mahasiswa</th>
                                    <th class="p-3.5">Program Studi / NIM</th>
                                    <th class="p-3.5">Status & Penempatan</th>
                                    <th class="p-3.5">Dosen DPL</th>
                                    <th class="p-3.5">Mentor Dinas</th>
                                    <th class="p-3.5 text-center">Nilai Akhir</th>
                                    <th class="p-3.5 pr-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                                @forelse($students as $student)
                                    @php
                                        $profile = $student->studentProfile;
                                        $latestApp = $student->applications->first();
                                        $placement = $latestApp?->placement;
                                        $dosen = $placement?->academicAdvisor;
                                        $mentor = $placement?->mentor ?? $placement?->pembimbing;
                                        $eval = $placement?->evaluation;
                                    @endphp
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="p-3.5 pl-4">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-xl bg-blue-50 border border-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0">
                                                    {{ strtoupper(substr($student->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-900">{{ $student->name }}</div>
                                                    <div class="text-[11px] font-mono text-slate-400">{{ $student->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-3.5">
                                            <div class="font-bold text-slate-800">{{ $profile?->jurusan ?? '-' }}</div>
                                            <div class="text-[11px] font-mono text-slate-500">NIM: {{ $profile?->nim ?? '-' }} (Smt {{ $profile?->semester ?? '-' }})</div>
                                        </td>
                                        <td class="p-3.5">
                                            @if($latestApp)
                                                <div class="space-y-1">
                                                    @if($latestApp->status === 'accepted')
                                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                            Aktif Magang
                                                        </span>
                                                    @elseif($latestApp->status === 'completed')
                                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                            Selesai / Lulus
                                                        </span>
                                                    @elseif($latestApp->status === 'pending')
                                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 animate-pulse">
                                                            Menunggu Seleksi
                                                        </span>
                                                    @else
                                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                            {{ strtoupper($latestApp->status) }}
                                                        </span>
                                                    @endif

                                                    <div class="text-[11px] text-slate-700 font-semibold truncate max-w-xs">
                                                        {{ $latestApp->unit?->agencyProfile?->agency_name ?? 'Pemerintah Kota Surabaya' }}
                                                    </div>
                                                    <div class="text-[10px] text-slate-400 truncate max-w-xs">
                                                        Divisi: {{ $latestApp->unit?->name ?? '-' }}
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-[11px] text-slate-400 italic">Belum Mengajukan</span>
                                            @endif
                                        </td>
                                        <td class="p-3.5">
                                            @if($dosen)
                                                <div class="text-xs font-bold text-indigo-700 flex items-center gap-1">
                                                    <span>{{ $dosen->name }}</span>
                                                </div>
                                                @if($latestApp)
                                                    <button type="button" 
                                                            @click="selectedApplicationId = {{ $latestApp->id }}; selectedStudentName = '{{ addslashes($student->name) }}'; showAssignAdvisorModal = true;"
                                                            class="text-[10px] text-blue-600 hover:underline cursor-pointer">
                                                        Ganti DPL
                                                    </button>
                                                @endif
                                            @elseif($latestApp && $latestApp->status === 'accepted')
                                                <button type="button" 
                                                        @click="selectedApplicationId = {{ $latestApp->id }}; selectedStudentName = '{{ addslashes($student->name) }}'; showAssignAdvisorModal = true;"
                                                        class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-[10px] font-bold border border-indigo-200 transition cursor-pointer">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                    <span>Tugaskan DPL</span>
                                                </button>
                                            @else
                                                <span class="text-[11px] text-slate-400">-</span>
                                            @endif
                                        </td>
                                        <td class="p-3.5">
                                            @if($mentor)
                                                <div class="text-xs font-bold text-slate-800">{{ $mentor->name }}</div>
                                                <div class="text-[10px] text-slate-400">Mentor Kedinasan</div>
                                            @else
                                                <span class="text-[11px] text-slate-400 italic">Belum Diplot</span>
                                            @endif
                                        </td>
                                        <td class="p-3.5 text-center">
                                            @if($eval && ($eval->final_score > 0 || $eval->nilai_akademik > 0 || $eval->nilai_pembimbing > 0))
                                                @php
                                                    $fScore = $eval->final_score;
                                                    if (!$fScore && $eval->nilai_pembimbing > 0 && $eval->nilai_akademik > 0) {
                                                        $wM = $university->weight_mentor ?? 40;
                                                        $wL = $university->weight_lecturer ?? 60;
                                                        $fScore = round(($eval->nilai_pembimbing * $wM / 100) + ($eval->nilai_akademik * $wL / 100), 2);
                                                    } elseif (!$fScore && $university->evaluation_scheme === 'mentor_only') {
                                                        $fScore = $eval->nilai_pembimbing;
                                                    }
                                                @endphp
                                                <span class="px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono font-black text-xs">
                                                    {{ number_format($fScore, 1) }}
                                                </span>
                                            @else
                                                <span class="text-slate-300 font-bold">-</span>
                                            @endif
                                        </td>
                                        <td class="p-3.5 pr-4 text-right">
                                            <div class="inline-flex items-center gap-1.5">
                                                @if($latestApp)
                                                    <a href="{{ route('admin.applications.show', $latestApp->id) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Lihat Detail Pengajuan">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    </a>
                                                @endif
                                                @if($placement)
                                                    <a href="{{ route('admin.logbooks.show', $placement->id) }}" class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Lihat Aktivitas Logbook">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="p-8 text-center text-slate-400">
                                            <p class="font-bold text-slate-600">Tidak ada data mahasiswa terdaftar</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 3: AKUN ADMIN PORTAL KAMPUS                                -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'admin'" class="space-y-4" style="display: none;">
                <div class="bg-white rounded-3xl border border-slate-100 p-6 sm:p-7 shadow-xs space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                        <div>
                            <h3 class="font-black text-lg text-slate-900">Akun Resmi Administrator Portal Universitas</h3>
                            <p class="text-xs text-slate-500 mt-1">Akun yang memegang otoritas penuh di Portal Resmi Kampus untuk menetapkan DPL dan verifikasi dokumen pengantar</p>
                        </div>

                        @if($university->universityAdmin)
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-flex items-center gap-1.5 self-start">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Akun Aktif Terdaftar
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 self-start animate-pulse">
                                Belum Dibuatkan Akun
                            </span>
                        @endif
                    </div>

                    @if($university->universityAdmin)
                        @php $adminAccount = $university->universityAdmin; @endphp
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Nama Akun</span>
                                <div class="font-bold text-sm text-slate-900 mt-1">{{ $adminAccount->name }}</div>
                                <div class="text-[11px] text-slate-500">Role: Administrator Universitas</div>
                            </div>

                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Email Login</span>
                                <div class="font-mono font-bold text-sm text-indigo-700 mt-1 select-all">{{ $adminAccount->email }}</div>
                                <div class="text-[11px] text-slate-500">Verifikasi: {{ $adminAccount->email_verified_at ? 'Terverifikasi' : 'Belum' }}</div>
                            </div>

                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Waktu Pembuatan</span>
                                <div class="font-mono text-xs text-slate-700 mt-1">{{ $adminAccount->created_at ? $adminAccount->created_at->format('d M Y H:i') : '-' }}</div>
                                <div class="text-[11px] text-slate-500">Kredensial Default: password</div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-slate-100">
                            @if($isSuperAdmin)
                                <form action="{{ route('admin.impersonate', $adminAccount->id) }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                        <span>Masuk Sebagai Admin Kampus (Login As)</span>
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('admin.users.reset_password', $adminAccount->id) }}" method="POST" class="m-0" onsubmit="return confirm('Reset password akun Admin Kampus {{ $adminAccount->email }} ke default (\'password\')?');">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                    <span>Reset Password ke Default ('password')</span>
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="p-6 rounded-2xl bg-amber-50 border border-amber-200 text-center space-y-3">
                            <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mx-auto">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <h4 class="font-bold text-amber-900 text-sm">Universitas Ini Belum Memiliki Akun Admin Portal</h4>
                            <p class="text-xs text-amber-700 max-w-md mx-auto">Buatkan akun admin kampus sekarang agar perwakilan rektorat/kemahasiswaan dapat login dan mengelola plotting DPL secara mandiri.</p>
                            <form action="{{ route('admin.universities.create_account', $university->id) }}" method="POST" class="inline-block pt-2">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition shadow-sm cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>Buatkan Akun Admin Portal Sekarang</span>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 4: KEBIJAKAN PENILAIAN & DOKUMEN                           -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'policy'" class="space-y-4" style="display: none;">
                <div class="bg-white rounded-3xl border border-slate-100 p-6 sm:p-7 shadow-xs space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                        <div>
                            <h3 class="font-black text-lg text-slate-900">Kebijakan Evaluasi & Skema Penilaian Magang MBKM</h3>
                            <p class="text-xs text-slate-500 mt-1">Konfigurasi formula bobot penilaian akhir dan keterlibatan Dosen Pembimbing Lapangan (DPL)</p>
                        </div>

                        <a href="{{ route('admin.universities.edit', $university->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs self-start">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Ubah Kebijakan Penilaian</span>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Kartu Skema -->
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 space-y-3">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Model Skema Penilaian</span>
                            <div class="text-base font-black text-slate-900">
                                @if($university->evaluation_scheme === 'mentor_only')
                                    100% Penilaian Mentor Dinas (Single Scheme)
                                @else
                                    Penilaian Kolaboratif Ganda (Dual Evaluation)
                                @endif
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                @if($university->evaluation_scheme === 'mentor_only')
                                    Kampus menyerahkan sepenuhnya nilai evaluasi magang mahasiswa kepada Mentor Kedinasan Pemerintah Kota Surabaya. Nilai akhir diambil 100% dari nilai mentor lapangan.
                                @else
                                    Nilai akhir merupakan gabungan berbobot antara penilaian kinerja praktis oleh Mentor Kedinasan Pemkot Surabaya dan evaluasi akademis oleh Dosen Pembimbing Lapangan (DPL).
                                @endif
                            </p>
                        </div>

                        <!-- Kartu Pembobotan -->
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 space-y-3">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Komposisi Bobot Nilai</span>
                            
                            @if($university->evaluation_scheme === 'mentor_only')
                                <div class="flex items-center justify-between p-3 rounded-xl bg-white border border-slate-100">
                                    <span class="text-xs font-bold text-slate-700">Mentor Kedinasan Pemkot Surabaya</span>
                                    <span class="text-xs font-black text-purple-700 bg-purple-50 px-2.5 py-1 rounded-lg border border-purple-100">100%</span>
                                </div>
                            @else
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-white border border-slate-100">
                                        <span class="text-xs font-bold text-slate-700">Mentor Kedinasan Dinas Pemkot</span>
                                        <span class="text-xs font-black text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100">{{ $university->weight_mentor ?? 40 }}%</span>
                                    </div>
                                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-white border border-slate-100">
                                        <span class="text-xs font-bold text-slate-700">Dosen Pembimbing Kampus (DPL)</span>
                                        <span class="text-xs font-black text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">{{ $university->weight_lecturer ?? 60 }}%</span>
                                    </div>
                                </div>
                            @endif

                            <div class="text-[11px] text-slate-500 pt-1">
                                Syarat Penugasan DPL: <strong class="text-slate-800">{{ $university->require_dpl ? 'Wajib Ditetapkan' : 'Opsional / Fleksibel' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ============================================================== -->
        <!-- MODAL 1: TAMBAH DOSEN DPL BARU                                -->
        <!-- ============================================================== -->
        <div x-show="showAddDosenModal" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
             style="display: none;"
             x-transition>
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-5" @click.away="showAddDosenModal = false">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-black text-base text-slate-900">Tambah Dosen DPL Baru</h3>
                            <p class="text-xs text-slate-500">{{ $university->name }}</p>
                        </div>
                    </div>
                    <button type="button" @click="showAddDosenModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>

                <form method="POST" action="{{ route('admin.universities.dosens.store', $university->id) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap & Gelar Dosen <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: Dr. Budi Santoso, M.Kom." class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 py-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Resmi Dosen <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" required placeholder="budi@kampus.ac.id" class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 py-2.5">
                        <p class="text-[10px] text-slate-400 mt-1">Digunakan untuk login ke Portal Dosen dengan password default: 'password'</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">NIDN (Opsional)</label>
                            <input type="text" name="nidn" placeholder="0012345678" class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 py-2.5">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Telepon/WA</label>
                            <input type="text" name="phone" placeholder="08123456789" class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 py-2.5">
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button" @click="showAddDosenModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-xs">
                            Simpan DPL
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- MODAL 2: PENUGASAN / PLOTTING DPL UNTUK MAHASISWA              -->
        <!-- ============================================================== -->
        <div x-show="showAssignAdvisorModal" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
             style="display: none;"
             x-transition>
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-5" @click.away="showAssignAdvisorModal = false">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-black text-base text-slate-900">Penugasan Dosen Pembimbing</h3>
                            <p class="text-xs text-slate-500" x-text="'Mahasiswa: ' + selectedStudentName"></p>
                        </div>
                    </div>
                    <button type="button" @click="showAssignAdvisorModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>

                <form method="POST" action="{{ route('admin.universities.assign_advisor', $university->id) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="application_id" :value="selectedApplicationId">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Dosen Pembimbing (DPL) <span class="text-rose-500">*</span></label>
                        <select name="academic_advisor_id" required class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 py-2.5">
                            <option value="">-- Pilih Dosen DPL dari {{ $university->name }} --</option>
                            @foreach($dosens as $dsn)
                                <option value="{{ $dsn->id }}">{{ $dsn->name }} ({{ $dsn->email }})</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1">Dosen yang dipilih akan menerima hak akses untuk memantau logbook dan menilai laporan mahasiswa ini.</p>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button" @click="showAssignAdvisorModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-xs">
                            Tugaskan DPL
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
