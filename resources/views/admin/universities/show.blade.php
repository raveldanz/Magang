<x-app-layout>
    <x-responsive-table-style />
    <x-mobile-list-style />
    <x-control-center-style />
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.universities.index') }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition shadow-2xs" title="Kembali ke Master Universitas">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h2 class="cc-title font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                        <span>Pusat Kendali Perguruan Tinggi<span class="cc-hide-m">: {{ $university->name }}</span></span>
                    </h2>
                    <p class="hidden sm:block text-xs sm:text-sm text-gray-500 mt-0.5">
                        Manajemen terpadu Dosen Pembimbing, Mahasiswa, Akun Admin Portal, dan Kebijakan Penilaian
                    </p>
                </div>
            </div>

            <div class="cc-head-actions flex flex-wrap items-center gap-2.5">
                <a href="{{ route('admin.universities.export_students', $university->id) }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 shadow-2xs transition active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="whitespace-nowrap">Export Rekap (.CSV)</span>
                </a>

                @if($isSuperAdmin)
                <a href="{{ route('admin.universities.edit', $university->id) }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit Profil & Kebijakan</span>
                </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="cc-wrap py-8" x-data="{ 
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
                    if ($univLogoUrl && !file_exists(public_path(ltrim(parse_url($univLogoUrl, PHP_URL_PATH) ?? '', '/')))) {
                        $univLogoUrl = null;
                    }
                }
            @endphp

            <div class="cc-hero bg-white rounded-2xl border border-slate-200/80 p-6 md:p-8 shadow-xs">
                <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">
                    <!-- Sisi Kiri: Logo Resmi, Nama & Informasi Kontak -->
                    <div class="cc-hero-main flex items-start gap-4 sm:gap-6 flex-1 min-w-0">
                        <div class="cc-logo border border-slate-200 rounded-xl p-2 bg-white shadow-2xs flex items-center justify-center shrink-0 overflow-hidden"
                             style="width: 88px; height: 88px; min-width: 88px; min-height: 88px; max-width: 88px; max-height: 88px;">
                            @if($univLogoUrl)
                                <img src="{{ $univLogoUrl }}" alt="Logo {{ $university->name }}" class="w-16 h-16 object-contain shrink-0" style="width: 64px; height: 64px; max-width: 64px; max-height: 64px; object-fit: contain;">
                            @else
                                <img src="{{ asset('images/default-university.svg') }}" alt="Default Logo {{ $university->name }}" class="w-16 h-16 object-contain shrink-0" style="width: 64px; height: 64px; max-width: 64px; max-height: 64px; object-fit: contain;">
                            @endif
                        </div>

                        <div class="space-y-2 min-w-0 flex-1">
                            <div class="cc-badges flex flex-wrap items-center gap-2">
                                <span class="whitespace-nowrap px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/60">
                                    {{ $university->code }}
                                </span>
                                @if($university->universityAdmin)
                                    <span class="whitespace-nowrap px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Akun Admin Aktif
                                    </span>
                                @else
                                    <span class="whitespace-nowrap px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200/60">
                                        Belum Ada Akun Admin
                                    </span>
                                @endif
                            </div>

                            <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight leading-snug">
                                {{ $university->name }}
                            </h1>

                            <div class="flex items-start gap-1.5 text-xs sm:text-sm text-slate-600 leading-relaxed max-w-2xl">
                                <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>{{ $university->address ?? 'Alamat kampus belum diatur.' }}</span>
                            </div>

                            <div class="cc-contacts flex flex-wrap items-center gap-4 text-xs text-slate-600 pt-1">
                                @if($university->email)
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        <span class="font-mono">{{ $university->email }}</span>
                                    </div>
                                @endif
                                @if($university->phone)
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                        <span>{{ $university->phone }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Ringkasan Kebijakan & Akun Portal -->
                            <dl class="cc-facts grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 mt-3 border-t border-slate-100">
                                <div class="min-w-0">
                                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Skema Penilaian</dt>
                                    <dd class="mt-1 text-sm font-semibold text-slate-800">
                                        @if($university->evaluation_scheme === 'mentor_only')
                                            100% Mentor Dinas
                                        @else
                                            Mentor {{ $university->weight_mentor ?? 40 }}% · Dosen {{ $university->weight_lecturer ?? 60 }}%
                                        @endif
                                    </dd>
                                </div>
                                <div class="min-w-0">
                                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Penugasan Dosen</dt>
                                    <dd class="mt-1 text-sm font-semibold text-slate-800">
                                        {{ $university->require_dpl ? 'Wajib ditetapkan' : 'Opsional' }}
                                    </dd>
                                </div>
                                <div class="min-w-0">
                                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Akun Admin Portal</dt>
                                    <dd title="{{ $university->universityAdmin->email ?? '' }}" class="mt-1 text-sm font-semibold truncate {{ $university->universityAdmin ? 'text-slate-800 font-mono' : 'text-amber-600' }}">
                                        {{ $university->universityAdmin->email ?? 'Belum dibuat' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Sisi Kanan: Kartu PIC Rektorat & Quick Action Login As -->
                    <div class="cc-side w-full lg:w-80 bg-slate-50 border border-slate-200/80 rounded-xl p-5 space-y-3 shrink-0">
                        <div class="flex items-center gap-1.5 border-b border-slate-200/80 pb-2 text-slate-500 font-semibold text-xs uppercase tracking-wider">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>PIC Kemahasiswaan / Rektorat</span>
                        </div>

                        <div class="space-y-1">
                            <div class="text-slate-900 font-bold text-base truncate">
                                {{ $university->pic_name ?? 'Belum Ditentukan' }}
                            </div>
                            <div class="text-slate-600 text-xs">
                                {{ $university->pic_position ?? 'Pimpinan / Penanggung Jawab MBKM' }}
                            </div>
                            @if($university->pic_nip)
                                <div class="text-slate-600 text-xs font-mono">
                                    NIP: {{ $university->pic_nip }}
                                </div>
                            @endif
                        </div>

                        @if($isSuperAdmin)
                        <div class="pt-3 border-t border-slate-200/80">
                            @if($university->universityAdmin)
                                <form action="{{ route('admin.impersonate', $university->universityAdmin->id) }}" method="POST" class="w-full m-0">
                                    @csrf
                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs px-4 py-2.5 rounded-lg shadow-xs transition-colors cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                                        <span>Masuk Sebagai Admin Kampus</span>
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.universities.create_account', $university->id) }}" method="POST" class="w-full m-0">
                                    @csrf
                                    <button type="button"
                                            @click="$dispatch('open-confirm-modal', {
                                                form: $el.form,
                                                title: 'Buat Akun Admin Kampus',
                                                message: 'Sistem akan membuat akun login portal untuk perwakilan kampus agar dapat mengelola dosen pembimbing secara mandiri.',
                                                label: 'Perguruan tinggi:',
                                                name: @js($university->name),
                                                desc: 'Password awal: password',
                                                confirmText: 'Ya, Buat Akun'
                                            })"
                                            class="w-full inline-flex items-center justify-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs px-4 py-2.5 rounded-lg shadow-xs transition-colors cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <span>Buatkan Akun Admin Kampus</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Ringkasan Metrik Kampus (satu panel, garis pemisah tipis) -->
            <div class="cc-metrics grid grid-cols-2 lg:grid-cols-5 gap-px bg-slate-200/70 rounded-2xl border border-slate-200/80 overflow-hidden shadow-xs">
                <div class="bg-white p-4 sm:p-5">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Mahasiswa</div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-2xl font-bold text-slate-900">{{ $stats['total_students'] }}</span>
                        <span class="text-sm text-slate-500">orang</span>
                    </div>
                    @if(($stats['needs_action'] ?? 0) > 0)
                        <button type="button" @click="activeTab = 'mahasiswa'" class="mt-1 text-xs font-semibold text-amber-700 hover:underline cursor-pointer">
                            {{ $stats['needs_action'] }} perlu tindakan
                        </button>
                    @endif
                </div>
                <div class="bg-white p-4 sm:p-5">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Dosen Pembimbing</div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-2xl font-bold text-slate-900">{{ $stats['total_dosens'] }}</span>
                        <span class="text-sm text-slate-500">terdaftar</span>
                    </div>
                </div>
                <div class="bg-white p-4 sm:p-5">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ \App\Enums\ApplicationStatus::ACTIVE->label() }}</div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-2xl font-bold text-blue-600">{{ $stats['active_interns'] }}</span>
                        <span class="text-sm text-slate-500">di dinas</span>
                    </div>
                </div>
                <div class="bg-white p-4 sm:p-5">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ \App\Enums\ApplicationStatus::COMPLETED->label() }}</div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-2xl font-bold text-emerald-600">{{ $stats['completed_interns'] }}</span>
                        <span class="text-sm text-slate-500">alumni</span>
                    </div>
                </div>
                <div class="bg-white p-4 sm:p-5 col-span-2 lg:col-span-1">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Rata-Rata Nilai</div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-2xl font-bold text-slate-900">{{ $stats['average_score'] ?? '-' }}</span>
                        <span class="text-sm text-slate-500">skala 100</span>
                    </div>
                </div>
            </div>

            <!-- Tab Navigasi Manajemen Terpadu -->
            <div class="cc-tabs-bare border-b border-slate-200">
                <nav class="cc-tabs flex flex-nowrap space-x-2 sm:space-x-4 overflow-x-auto pb-px scrollbar-none" aria-label="Tabs">
                    <!-- Tab 1: Dosen Pembimbing -->
                    <button type="button" 
                            @click="activeTab = 'dosen'" 
                            :class="activeTab === 'dosen' ? 'cc-active border-blue-600 text-blue-600 font-black' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-semibold'"
                            class="cc-tab whitespace-nowrap py-3 px-3 sm:px-4 border-b-2 text-xs sm:text-sm flex items-center gap-2 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>Dosen Pembimbing</span>
                    </button>

                    <!-- Tab 2: Mahasiswa Terdaftar & Status Magang -->
                    <button type="button" 
                            @click="activeTab = 'mahasiswa'" 
                            :class="activeTab === 'mahasiswa' ? 'cc-active border-blue-600 text-blue-600 font-black' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-semibold'"
                            class="cc-tab whitespace-nowrap py-3 px-3 sm:px-4 border-b-2 text-xs sm:text-sm flex items-center gap-2 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Mahasiswa Kampus</span>
                    </button>

                    <!-- Tab 3: Akun Admin Portal Kampus -->
                    <button type="button" 
                            @click="activeTab = 'admin'" 
                            :class="activeTab === 'admin' ? 'cc-active border-blue-600 text-blue-600 font-black' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-semibold'"
                            class="cc-tab whitespace-nowrap py-3 px-3 sm:px-4 border-b-2 text-xs sm:text-sm flex items-center gap-2 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Akun Admin Kampus</span>
                    </button>

                    <!-- Tab 4: Kebijakan Penilaian & Dokumen -->
                    <button type="button" 
                            @click="activeTab = 'policy'" 
                            :class="activeTab === 'policy' ? 'cc-active border-blue-600 text-blue-600 font-black' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-semibold'"
                            class="cc-tab whitespace-nowrap py-3 px-3 sm:px-4 border-b-2 text-xs sm:text-sm flex items-center gap-2 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Kebijakan Penilaian</span>
                    </button>
                </nav>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 1: DOSEN PEMBIMBING LAPANGAN KAMPUS                  -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'dosen'" class="cc-card bg-white rounded-2xl border border-slate-100 shadow-2xs overflow-hidden" style="display: none;">
                <div class="cc-panel-head flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 border-b border-slate-100">
    <div>
        <h3 class="text-sm font-black text-slate-900">Dosen Pembimbing Terdaftar</h3>
        <p class="text-xs text-slate-500">Kelola akun dosen pembimbing akademik dari {{ $university->name }} yang memantau & menilai logbook mahasiswa</p>
    </div>

    <!-- Tombol Tambah DPL (Mengarah ke form create & otomatis kembali ke sini) -->
    @if($isSuperAdmin)
    <a href="{{ route('admin.users.create', ['university_id' => $university->id, 'role' => 'dosen', 'return_to' => url()->current()]) }}" 
       class="cc-head-actions inline-flex items-center gap-2 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        <span>Tambah DPL Baru</span>
    </a>
    @endif
</div>

                <div class="d-only">
                    <div class="overflow-x-auto">
                        <table class="rtable w-full text-left border-collapse text-[13px]">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider text-xs whitespace-nowrap">
                                    <th class="p-3.5 pl-4">Dosen Pembimbing</th>
                                    <th class="p-3.5">Email & Kontak</th>
                                    <th class="p-3.5 text-center">Bimbingan Aktif</th>
                                    <th class="p-3.5 text-center">Bimbingan Selesai</th>
                                    <th class="p-3.5 text-center">Total Mahasiswa</th>
                                    @if($isSuperAdmin)
                                    <th class="p-3.5 pr-4 text-right">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                                @forelse($dosens as $dosen)
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="rt-title p-3.5 pl-4" data-label="Dosen Pembimbing">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs shrink-0">
                                                    {{ strtoupper(substr($dosen->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-900">{{ $dosen->name }}</div>
                                                    <div class="text-xs text-slate-500">Perguruan Tinggi: {{ $university->name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-3.5" data-label="Email &amp; Kontak">
                                            <div class="font-mono text-slate-800">{{ $dosen->email }}</div>
                                            <div class="text-xs text-slate-400">{{ $dosen->phone ?? 'Nomor telp belum diisi' }}</div>
                                        </td>
                                        <td class="p-3.5 text-center" data-label="Bimbingan Aktif">
                                            <span class="whitespace-nowrap px-2.5 py-1 rounded-full text-xs font-semibold {{ $dosen->active_students_count > 0 ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-50 text-slate-500' }}">
                                                {{ $dosen->active_students_count }} Mhs
                                            </span>
                                        </td>
                                        <td class="p-3.5 text-center" data-label="Bimbingan Selesai">
                                            <span class="whitespace-nowrap px-2.5 py-1 rounded-full text-xs font-semibold {{ $dosen->completed_students_count > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-50 text-slate-500' }}">
                                                {{ $dosen->completed_students_count }} Mhs
                                            </span>
                                        </td>
                                        <td class="p-3.5 text-center font-bold text-slate-900" data-label="Total Mahasiswa">
                                            {{ $dosen->total_students_count }}
                                        </td>
                                        @if($isSuperAdmin)
                                        <td class="rt-actions p-3.5 pr-4 text-right" data-label="Aksi">
                                            <div class="inline-flex items-center justify-end gap-1.5 align-middle">
                                                @if($isSuperAdmin)
                                                    <form action="{{ route('admin.impersonate', $dosen->id) }}" method="POST" class="inline-flex items-center m-0 p-0 align-middle">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap px-3 py-1.5 text-xs font-medium rounded-lg border transition-all duration-150 bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-800 hover:text-white hover:border-slate-800 cursor-pointer" title="Login As sebagai Dosen ini">
                                                            Login As
                                                        </button>
                                                    </form>

                                                    <a href="{{ route('admin.users.edit', ['user' => $dosen->id, 'return_to' => url()->current() . '?tab=dosen']) }}" class="inline-flex items-center justify-center whitespace-nowrap px-3 py-1.5 text-xs font-medium rounded-lg border transition-all duration-150 bg-blue-50 border-blue-200 text-blue-600 hover:bg-blue-600 hover:text-white hover:border-blue-600 cursor-pointer" title="Edit Akun Dosen">
                                                        Edit
                                                    </a>

                                                    <button type="button"
                                                            @click="$dispatch('open-reset-modal', {
                                                                action: '{{ route('admin.universities.dosens.reset_password', [$university->id, $dosen->id]) }}',
                                                                name: @js($dosen->name),
                                                                email: @js($dosen->email),
                                                                role: 'Dosen Pembimbing'
                                                            })"
                                                            class="inline-flex items-center justify-center whitespace-nowrap px-3 py-1.5 text-xs font-medium rounded-lg border transition-all duration-150 bg-amber-50 border-amber-200 text-amber-700 hover:bg-amber-500 hover:text-white hover:border-amber-500 cursor-pointer" title="Reset Password ke default ('password')">
                                                        Reset
                                                    </button>

                                                    <button type="button" 
                                                            @click="$dispatch('open-delete-modal', {
                                                                action: '{{ route('admin.universities.dosens.destroy', [$university->id, $dosen->id]) }}',
                                                                title: 'Hapus Dosen Pembimbing',
                                                                name: '{{ addslashes($dosen->name) }}',
                                                                desc: 'Email: {{ $dosen->email }} &bull; {{ $dosen->total_students_count }} Mahasiswa Bimbingan'
                                                            })" 
                                                            class="inline-flex items-center justify-center whitespace-nowrap px-3 py-1.5 text-xs font-medium rounded-lg border transition-all duration-150 bg-rose-50 border-rose-200 text-rose-600 hover:bg-rose-600 hover:text-white hover:border-rose-600 cursor-pointer"
                                                            title="Hapus Dosen Pembimbing">
                                                        Hapus
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $isSuperAdmin ? 6 : 5 }}" class="p-8 text-center text-slate-400">
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                                <p class="font-bold text-slate-600">Belum ada Dosen Pembimbing terdaftar untuk kampus ini</p>
                                                <p class="text-xs text-slate-400">Tambahkan DPL baru menggunakan tombol di atas agar dapat ditugaskan membimbing mahasiswa.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Versi HP: kartu dosen DPL -->
                <div class="m-only mlist">
                    @forelse($dosens as $dosen)
                        <div class="mcard">
                            <div class="mcard-head">
                                <div class="mavatar mavatar-indigo">{{ strtoupper(substr($dosen->name, 0, 2)) }}</div>
                                <div class="mcard-main">
                                    <div class="mcard-title">{{ $dosen->name }}</div>
                                    <div class="mcard-sub"><span class="mono" style="overflow-wrap:anywhere">{{ $dosen->email }}</span><br>{{ $dosen->phone ?? 'Nomor telp belum diisi' }}</div>
                                </div>
                            </div>
                            <div class="mcard-body">
                                <div class="cc-stat3">
                                    <div><b style="color:#2563eb">{{ $dosen->active_students_count }}</b><span>{{ \App\Enums\ApplicationStatus::ACTIVE->label() }}</span></div>
                                    <div><b style="color:#059669">{{ $dosen->completed_students_count }}</b><span>{{ \App\Enums\ApplicationStatus::COMPLETED->label() }}</span></div>
                                    <div><b>{{ $dosen->total_students_count }}</b><span>Total Mhs</span></div>
                                </div>
                            </div>
                            @if($isSuperAdmin)
                                <div class="mcard-foot">
                                    <div class="cc-actions">
                                        <form action="{{ route('admin.impersonate', $dosen->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="mbtn mbtn-login">Login As</button>
                                        </form>
                                        <a href="{{ route('admin.users.edit', ['user' => $dosen->id, 'return_to' => url()->current() . '?tab=dosen']) }}" class="mbtn mbtn-soft">Edit</a>
                                        <button type="button" class="mbtn mbtn-amber"
                                                @click="$dispatch('open-reset-modal', {
                                                    action: '{{ route('admin.universities.dosens.reset_password', [$university->id, $dosen->id]) }}',
                                                    name: @js($dosen->name),
                                                    email: @js($dosen->email),
                                                    role: 'Dosen Pembimbing'
                                                })">Reset</button>
                                        <button type="button"
                                                @click="$dispatch('open-delete-modal', {
                                                    action: '{{ route('admin.universities.dosens.destroy', [$university->id, $dosen->id]) }}',
                                                    title: 'Hapus Dosen Pembimbing',
                                                    name: '{{ addslashes($dosen->name) }}',
                                                    desc: 'Email: {{ $dosen->email }} &bull; {{ $dosen->total_students_count }} Mahasiswa Bimbingan'
                                                })"
                                                class="mbtn mbtn-danger">Hapus</button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="mempty">Belum ada Dosen Pembimbing terdaftar untuk kampus ini.</div>
                    @endforelse
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 2: MAHASISWA KAMPUS TERDAFTAR & STATUS MAGANG              -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'mahasiswa'" class="cc-card bg-white rounded-2xl border border-slate-100 shadow-2xs overflow-hidden" style="display: none;">
                <!-- Filter Status Mahasiswa & Bilah Pencarian -->
                <div class="cc-panel-head p-4 space-y-3 border-b border-slate-100">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-black text-slate-900">Daftar Mahasiswa Asal {{ $university->name }}</h3>
                            <p class="text-xs text-slate-500">Pantau status pendaftaran, instansi penempatan, DPL pembimbing, dan perkembangan nilai</p>
                        </div>

                        <div class="cc-head-actions flex items-center gap-2">
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
                            <option value="action" {{ request('student_status') === 'action' ? 'selected' : '' }}>Perlu Tindakan</option>
                            <optgroup label="Status Pengajuan">
                                @foreach(\App\Enums\ApplicationStatus::cases() as $statusCase)
                                    <option value="{{ $statusCase->value }}" {{ request('student_status') === $statusCase->value ? 'selected' : '' }}>{{ $statusCase->label() }}</option>
                                @endforeach
                            </optgroup>
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

                <!-- Table Mahasiswa (layar lebar >= 1200px) -->
                <div class="stt-wide">
                    <div class="overflow-x-auto">
                        <table class="stt w-full text-left border-collapse text-[13px]">
                            <colgroup>
                                <col style="width: 21%"><col style="width: 15%"><col style="width: 19%"><col style="width: 22%"><col style="width: 8%"><col style="width: 15%">
                            </colgroup>
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider text-xs">
                                    <th class="p-3.5 pl-4">Mahasiswa</th>
                                    <th class="p-3.5">Status</th>
                                    <th class="p-3.5">Penempatan</th>
                                    <th class="p-3.5">Pembimbing</th>
                                    <th class="p-3.5 text-center">Nilai</th>
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
                                        $actionHint = $latestApp?->actionHint($requireAdvisor);
                                        $canAssignDpl = $isSuperAdmin && $latestApp && in_array($latestApp->statusValue(), ['accepted', 'active'], true);
                                        $metaLine = collect([
                                            $profile?->jurusan,
                                            $profile?->nim ? 'NIM ' . $profile->nim : null,
                                            $profile?->semester ? 'Smt ' . $profile->semester : null,
                                        ])->filter()->implode(' · ');
                                    @endphp
                                    <tr class="hover:bg-slate-50/80 transition">
                                        {{-- 1. Identitas mahasiswa (email tampil saat kursor diarahkan ke nama) --}}
                                        <td class="p-3.5 pl-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0">
                                                    {{ strtoupper(substr($student->name, 0, 2)) }}
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="stt-title" title="{{ $student->email }}">{{ $student->name }}</div>
                                                    <div class="stt-meta" title="{{ $metaLine }}">{{ $profile?->jurusan ?? '-' }}</div>
                                                    <div class="stt-meta stt-mono">NIM {{ $profile?->nim ?? '-' }} · Smt {{ $profile?->semester ?? '-' }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- 2. Status + tindakan yang dibutuhkan --}}
                                        <td class="p-3.5">
                                            @if($latestApp)
                                                <x-status-badge :status="$latestApp->status" />
                                                @if($actionHint)
                                                    <div class="stt-hint">{{ $actionHint }}</div>
                                                @endif
                                            @else
                                                <span class="stt-empty">Belum mengajukan</span>
                                            @endif
                                        </td>

                                        {{-- 3. Instansi & divisi --}}
                                        <td class="p-3.5">
                                            @if($latestApp)
                                                @php $agencyName = $latestApp->unit?->agencyProfile?->agency_name ?? 'Pemerintah Kota Surabaya'; @endphp
                                                <div class="stt-place">
                                                    <span class="stt-clamp2 font-semibold text-slate-800" title="{{ $agencyName }}">{{ $agencyName }}</span>
                                                    <span class="stt-line stt-sub" title="{{ $latestApp->unit?->name }}">{{ $latestApp->unit?->name ?? '-' }}</span>
                                                </div>
                                            @else
                                                <span class="stt-empty">-</span>
                                            @endif
                                        </td>

                                        {{-- 4. Pembimbing: dosen DPL & mentor dinas dalam satu kolom --}}
                                        <td class="p-3.5">
                                            <div class="stt-people">
                                                <div class="stt-person">
                                                    <span class="stt-role">DPL</span>
                                                    @if($dosen)
                                                        <span class="stt-name text-indigo-700" title="{{ $dosen->name }}">{{ $dosen->name }}</span>
                                                        @if($isSuperAdmin && $latestApp)
                                                            <button type="button"
                                                                    @click="selectedApplicationId = {{ $latestApp->id }}; selectedStudentName = '{{ addslashes($student->name) }}'; showAssignAdvisorModal = true;"
                                                                    class="stt-link" title="Ganti Dosen Pembimbing" aria-label="Ganti Dosen Pembimbing">
                                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.232-6.232a2.5 2.5 0 113.536 3.536L12.536 16.536 9 17l.464-3.536z"/></svg>
                                                            </button>
                                                        @endif
                                                    @elseif($canAssignDpl)
                                                        <button type="button"
                                                                @click="selectedApplicationId = {{ $latestApp->id }}; selectedStudentName = '{{ addslashes($student->name) }}'; showAssignAdvisorModal = true;"
                                                                class="stt-assign">+ Tugaskan DPL</button>
                                                    @else
                                                        <span class="stt-empty">Belum ada</span>
                                                    @endif
                                                </div>
                                                <div class="stt-person">
                                                    <span class="stt-role">Mentor</span>
                                                    @if($mentor)
                                                        <span class="stt-name" title="{{ $mentor->name }}">{{ $mentor->name }}</span>
                                                    @else
                                                        <span class="stt-empty">Belum diplot</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        {{-- 5. Nilai akhir --}}
                                        <td class="p-3.5 text-center">
                                            @php $finalScore = $eval ? (float) $eval->nilai_akhir : 0; @endphp
                                            @if($finalScore > 0)
                                                <span class="stt-score">
                                                    <b>{{ number_format($finalScore, 1) }}</b>
                                                    <span>{{ $eval->grade_calculated }}</span>
                                                </span>
                                            @elseif($eval && ($eval->nilai_pembimbing > 0 || $eval->nilai_dosen_calculated > 0))
                                                <span class="stt-sub whitespace-nowrap">Menunggu nilai {{ $eval->nilai_pembimbing > 0 ? 'dosen' : 'mentor' }}</span>
                                            @else
                                                <span class="text-slate-300 font-bold">-</span>
                                            @endif
                                        </td>

                                        {{-- 6. Aksi --}}
                                        <td class="p-3.5 pr-4 text-right">
                                            <div class="stt-actions">
                                                @if($latestApp)
                                                    <a href="{{ route('admin.applications.show', $latestApp->id) }}" class="inline-flex items-center justify-center whitespace-nowrap px-2.5 py-1.5 text-xs font-medium rounded-lg border transition-all duration-150 bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-800 hover:text-white hover:border-slate-800 cursor-pointer" title="Lihat Detail Pengajuan">
                                                        Detail
                                                    </a>
                                                @endif
                                                @if($placement)
                                                    <a href="{{ route('admin.logbooks.show', $placement->id) }}" class="inline-flex items-center justify-center whitespace-nowrap px-2.5 py-1.5 text-xs font-medium rounded-lg border transition-all duration-150 bg-blue-50 border-blue-200 text-blue-600 hover:bg-blue-600 hover:text-white hover:border-blue-600 cursor-pointer" title="Lihat Aktivitas Logbook">
                                                        Logbook
                                                    </a>
                                                @endif
                                                @if(!$latestApp && !$placement)
                                                    <span class="text-slate-300 font-bold">-</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="p-8 text-center text-slate-400">
                                            <p class="font-bold text-slate-600">Tidak ada data mahasiswa terdaftar</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Versi kartu: HP (1 kolom) & tablet/laptop kecil < 1200px (2 kolom) -->
                <div class="stt-cards mlist">
                    @forelse($students as $student)
                        @php
                            $profile = $student->studentProfile;
                            $latestApp = $student->applications->first();
                            $placement = $latestApp?->placement;
                            $dosen = $placement?->academicAdvisor;
                            $mentor = $placement?->mentor ?? $placement?->pembimbing;
                            $eval = $placement?->evaluation;
                            $mScore = $eval ? (float) $eval->nilai_akhir : 0;
                            $mSt = $latestApp?->statusValue();
                            $mHint = $latestApp?->actionHint($requireAdvisor);
                        @endphp
                        <div class="mcard">
                            <div class="mcard-head">
                                <div class="mavatar">{{ strtoupper(substr($student->name, 0, 2)) }}</div>
                                <div class="mcard-main">
                                    <div class="mcard-title">{{ $student->name }}</div>
                                    <div class="mcard-sub">{{ $profile?->jurusan ?? '-' }}<br><span class="mono">NIM {{ $profile?->nim ?? '-' }}</span> &middot; Smt {{ $profile?->semester ?? '-' }}</div>
                                </div>
                                @if($mScore > 0)
                                    <div class="mscore"><b>{{ number_format($mScore, 1) }}</b><span>Grade {{ $eval->grade_calculated }}</span></div>
                                @endif
                            </div>
                            <div class="mcard-body cc-grid2">
                                <div class="cc-span2">
                                    @if($latestApp)
                                        <x-status-badge :status="$latestApp->status" />
                                    @else
                                        <span class="mpill mpill-slate">Belum Mengajukan</span>
                                    @endif
                                    @if($mHint)
                                        <div class="text-xs font-semibold text-amber-700" style="margin-top:6px">{{ $mHint }}</div>
                                    @endif
                                    @if($latestApp)
                                        <div class="mfield-value" style="margin-top:6px">{{ $latestApp->unit?->agencyProfile?->agency_name ?? 'Pemerintah Kota Surabaya' }}</div>
                                        <div class="text-xs text-slate-400">Divisi: {{ $latestApp->unit?->name ?? '-' }}</div>
                                    @endif
                                </div>
                                <div>
                                    <div class="mfield-label">Dosen DPL</div>
                                    <div class="mfield-value">{{ $dosen->name ?? '-' }}</div>
                                    @if($isSuperAdmin && $latestApp && ($dosen || in_array($mSt, ['accepted', 'active'], true)))
                                        <button type="button"
                                                @click="selectedApplicationId = {{ $latestApp->id }}; selectedStudentName = '{{ addslashes($student->name) }}'; showAssignAdvisorModal = true;"
                                                class="cc-link" style="margin-top:2px">{{ $dosen ? 'Ganti DPL' : '+ Tugaskan DPL' }}</button>
                                    @endif
                                </div>
                                <div>
                                    <div class="mfield-label">Mentor Dinas</div>
                                    <div class="mfield-value">{{ $mentor->name ?? 'Belum Diplot' }}</div>
                                </div>
                            </div>
                            @if($latestApp || $placement)
                                <div class="mcard-foot">
                                    <div class="cc-actions">
                                        @if($latestApp)
                                            <a href="{{ route('admin.applications.show', $latestApp->id) }}" class="mbtn mbtn-soft">Detail Pengajuan</a>
                                        @endif
                                        @if($placement)
                                            <a href="{{ route('admin.logbooks.show', $placement->id) }}" class="mbtn mbtn-indigo">Logbook</a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="mempty">Tidak ada data mahasiswa terdaftar.</div>
                    @endforelse
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 3: AKUN ADMIN PORTAL KAMPUS                                -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'admin'" class="space-y-4" style="display: none;">
                <div class="cc-box bg-white rounded-3xl border border-slate-100 p-6 sm:p-7 shadow-xs space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                        <div>
                            <h3 class="font-black text-lg text-slate-900">Akun Resmi Administrator Portal Universitas</h3>
                            <p class="text-xs text-slate-500 mt-1">Akun yang memegang otoritas penuh di Portal Resmi Kampus untuk menetapkan DPL dan verifikasi dokumen pengantar</p>
                        </div>

                        @if($university->universityAdmin)
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-flex items-center gap-1.5 self-start">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Akun Aktif Terdaftar
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 self-start animate-pulse">
                                Belum Dibuatkan Akun
                            </span>
                        @endif
                    </div>

                    @if($university->universityAdmin)
                        @php $adminAccount = $university->universityAdmin; @endphp
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Nama Akun</span>
                                <div class="font-bold text-sm text-slate-900 mt-1">{{ $adminAccount->name }}</div>
                                <div class="text-xs text-slate-500">Role: Administrator Universitas</div>
                            </div>

                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Email Login</span>
                                <div class="font-mono font-bold text-sm text-indigo-700 mt-1 select-all">{{ $adminAccount->email }}</div>
                                <div class="text-xs text-slate-500">Verifikasi: {{ $adminAccount->email_verified_at ? 'Terverifikasi' : 'Belum' }}</div>
                            </div>

                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Waktu Pembuatan</span>
                                <div class="font-mono text-xs text-slate-700 mt-1">{{ $adminAccount->created_at ? $adminAccount->created_at->format('d M Y H:i') : '-' }}</div>
                                <div class="text-xs text-slate-500">Kredensial Default: password</div>
                            </div>
                        </div>

                        @if($isSuperAdmin)
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

                            @if($isSuperAdmin)
                            <button type="button"
                                    @click="$dispatch('open-reset-modal', {
                                        action: '{{ route('admin.users.reset_password', $adminAccount->id) }}',
                                        name: @js($adminAccount->name),
                                        email: @js($adminAccount->email),
                                        role: 'Admin Kampus'
                                    })"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                <span>Reset Password ke Default ('password')</span>
                            </button>
                            @endif
                        </div>
                        @endif
                    @else
                        <div class="p-6 rounded-2xl bg-amber-50 border border-amber-200 text-center space-y-3">
                            <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mx-auto">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <h4 class="font-bold text-amber-900 text-sm">Universitas Ini Belum Memiliki Akun Admin Portal</h4>
                            <p class="text-xs text-amber-700 max-w-md mx-auto">Buatkan akun admin kampus sekarang agar perwakilan rektorat/kemahasiswaan dapat login dan mengelola plotting DPL secara mandiri.</p>
                            @if($isSuperAdmin)
                            <form action="{{ route('admin.universities.create_account', $university->id) }}" method="POST" class="inline-block pt-2">
                                @csrf
                                <button type="button"
                                        @click="$dispatch('open-confirm-modal', {
                                            form: $el.form,
                                            title: 'Buat Akun Admin Kampus',
                                            message: 'Sistem akan membuat akun login portal untuk perwakilan kampus agar dapat mengelola dosen pembimbing secara mandiri.',
                                            label: 'Perguruan tinggi:',
                                            name: @js($university->name),
                                            desc: 'Password awal: password',
                                            confirmText: 'Ya, Buat Akun'
                                        })"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition shadow-sm cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>Buatkan Akun Admin Portal Sekarang</span>
                                </button>
                            </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 4: KEBIJAKAN PENILAIAN & DOKUMEN                           -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'policy'" class="space-y-4" style="display: none;">
                <div class="cc-box bg-white rounded-3xl border border-slate-100 p-6 sm:p-7 shadow-xs space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                        <div>
                            <h3 class="font-black text-lg text-slate-900">Kebijakan Evaluasi & Skema Penilaian Magang MBKM</h3>
                            <p class="text-xs text-slate-500 mt-1">Konfigurasi formula bobot penilaian akhir dan keterlibatan Dosen Pembimbing Lapangan</p>
                        </div>

                        @if($isSuperAdmin)
                        <a href="{{ route('admin.universities.edit', $university->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs self-start">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Ubah Kebijakan Penilaian</span>
                        </a>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Kartu Skema -->
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 space-y-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Model Skema Penilaian</span>
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
                                    Nilai akhir merupakan gabungan berbobot antara penilaian kinerja praktis oleh Mentor Kedinasan Pemkot Surabaya dan evaluasi akademis oleh Dosen Pembimbing Lapangan.
                                @endif
                            </p>
                        </div>

                        <!-- Kartu Pembobotan -->
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 space-y-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Komposisi Bobot Nilai</span>
                            
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
                                        <span class="text-xs font-bold text-slate-700">Dosen Pembimbing Kampus</span>
                                        <span class="text-xs font-black text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">{{ $university->weight_lecturer ?? 60 }}%</span>
                                    </div>
                                </div>
                            @endif

                            <div class="text-xs text-slate-500 pt-1">
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
                        <p class="text-xs text-slate-400 mt-1">Digunakan untuk login ke Portal Dosen dengan password default: 'password'</p>
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
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Dosen Pembimbing <span class="text-rose-500">*</span></label>
                        <select name="academic_advisor_id" required class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 py-2.5">
                            <option value="">-- Pilih Dosen DPL dari {{ $university->name }} --</option>
                            @foreach($dosens as $dsn)
                                <option value="{{ $dsn->id }}">{{ $dsn->name }} ({{ $dsn->email }})</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-400 mt-1">Dosen yang dipilih akan menerima hak akses untuk memantau logbook dan menilai laporan mahasiswa ini.</p>
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
