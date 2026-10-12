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
                $univLogoUrl = $university->logo_url;
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

            @include('admin.universities.partials.tab-dosen')

            @include('admin.universities.partials.tab-mahasiswa')

            @include('admin.universities.partials.tab-admin')

            @include('admin.universities.partials.tab-policy')


        </div>

        @include('admin.universities.partials.modals')


    </div>
</x-app-layout>
