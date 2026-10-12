<x-app-layout>
    <x-responsive-table-style />
    <x-mobile-list-style />
    <x-control-center-style />
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.agencies.index') }}" 
                   class="p-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-slate-900 hover:border-slate-300 transition shadow-2xs cursor-pointer" 
                   title="Kembali ke Master Instansi">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </a>
                <div>
                    <h2 class="cc-title font-black text-xl sm:text-2xl text-gray-900 tracking-tight">
                        Pusat Kendali Instansi: {{ $agency->agency_name }}
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Manajemen terpadu Divisi Magang, Personel Kedinasan, Pengajuan, dan Penempatan Mahasiswa
                    </p>
                </div>
            </div>

            <!-- Header Quick Actions -->
            <div class="cc-head-actions flex items-center flex-wrap gap-2">
                <a href="{{ route('admin.agencies.edit', $agency->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit Profil Dinas</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="cc-wrap py-8" x-data="{ activeTab: 'users' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Alert -->
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

            <!-- Hero Profile Card Dinas (Style Simpel Selaras Univ) -->
            <div class="cc-hero bg-white rounded-2xl border border-slate-200/80 p-6 md:p-8 shadow-xs">
                <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">
                    <!-- Sisi Kiri: Logo Resmi, Nama & Informasi Kontak -->
                    <div class="cc-hero-main flex items-start gap-4 sm:gap-6 flex-1 min-w-0">
                        @php
                            $agencyLogoUrl = null;
                            if (!empty($agency->logo)) {
                                if (file_exists(public_path($agency->logo))) {
                                    $agencyLogoUrl = asset($agency->logo);
                                } elseif (file_exists(public_path('storage/' . $agency->logo)) || file_exists(storage_path('app/public/' . $agency->logo))) {
                                    $agencyLogoUrl = asset('storage/' . $agency->logo);
                                }
                            }
                            if (!$agencyLogoUrl) {
                                $agencyLogoUrl = asset('images/default-agency.svg');
                            }
                        @endphp
                        <div class="cc-logo border border-slate-200 rounded-xl p-2 bg-white shadow-2xs flex items-center justify-center shrink-0 overflow-hidden"
                             style="width: 88px; height: 88px; min-width: 88px; min-height: 88px; max-width: 88px; max-height: 88px;">
                            <img src="{{ $agencyLogoUrl }}" alt="Logo {{ $agency->agency_name }}" class="w-16 h-16 object-contain shrink-0" style="width: 64px; height: 64px; max-width: 64px; max-height: 64px; object-fit: contain;">
                        </div>

                        <div class="space-y-2 min-w-0 flex-1">
                            <div class="cc-badges flex flex-wrap items-center gap-2">
                                @if(($stats['total_admins'] ?? 0) > 0)
                                    <span class="whitespace-nowrap px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Akun Terdaftar
                                    </span>
                                @else
                                    <span class="whitespace-nowrap px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200/60">
                                        Belum Ada Akun Admin
                                    </span>
                                @endif
                            </div>

                            <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight leading-snug">
                                {{ $agency->agency_name }}
                            </h1>

                            <div class="flex items-start gap-1.5 text-xs sm:text-sm text-slate-600 leading-relaxed max-w-2xl pt-0.5">
                                <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>{{ $agency->address ?? 'Alamat kantor dinas belum diatur.' }}</span>
                            </div>

                            <!-- Kontak Kedinasan -->
                            <div class="cc-contacts flex flex-wrap items-center gap-4 text-xs text-slate-600 pt-1">
                                @if($agency->email)
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        <span class="font-mono">{{ $agency->email }}</span>
                                    </div>
                                @endif
                                @if($agency->phone)
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                        <span>{{ $agency->phone }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Ringkasan Instansi & Akun Admin -->
                            @php $firstAdmin = $adminUsers->first(); @endphp
                            <dl class="cc-facts grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 mt-3 border-t border-slate-100">
                                <div class="min-w-0">
                                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Induk Pemerintahan</dt>
                                    <dd class="mt-1 text-sm font-semibold text-slate-800">
                                        {{ $agency->government_name ?? 'Pemerintah Kota Surabaya' }}
                                    </dd>
                                </div>
                                <div class="min-w-0">
                                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Akun Admin Dinas</dt>
                                    <dd title="{{ $firstAdmin->email ?? '' }}" class="mt-1 text-sm font-semibold truncate {{ $firstAdmin ? 'text-slate-800 font-mono' : 'text-amber-600' }}">
                                        {{ $firstAdmin->email ?? 'Belum dibuat' }}
                                    </dd>
                                </div>
                                <div class="min-w-0">
                                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Situs Resmi</dt>
                                    <dd class="mt-1 text-sm font-semibold truncate">
                                        @if($agency->website)
                                            <a href="{{ $agency->website }}" target="_blank" rel="noopener" class="text-blue-600 hover:text-blue-700 hover:underline">{{ parse_url($agency->website, PHP_URL_HOST) ?? $agency->website }}</a>
                                        @else
                                            <span class="text-slate-400">Belum diatur</span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Sisi Kanan: Panel Pejabat Penandatangan Resmi & Quick Action Login As -->
                    <div class="cc-side w-full lg:w-80 bg-slate-50 border border-slate-200/80 rounded-xl p-5 space-y-3 shrink-0">
                        <div class="flex items-center gap-1.5 border-b border-slate-200/80 pb-2 text-slate-500 font-semibold text-xs uppercase tracking-wider">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>Pejabat Penandatangan Resmi</span>
                        </div>

                        <div class="space-y-1">
                            <div class="text-slate-900 font-bold text-base">{{ $agency->signee_name ?? '-' }}</div>
                            <div class="text-slate-600 text-xs">{{ $agency->signee_position ?? 'Kepala Dinas' }}</div>
                            <div class="text-slate-600 text-xs font-mono">NIP. {{ $agency->signee_nip ?? '-' }}</div>
                        </div>

                        <!-- Tombol Masuk Sebagai Admin Dinas (Login As) -->
                        @if($isSuperAdmin && ($firstAdmin || ($stats['total_admins'] ?? 0) === 0))
                        <div class="pt-3 border-t border-slate-200/80">
                            @if($firstAdmin)
                                <form action="{{ route('admin.impersonate', $firstAdmin->id) }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit"
                                            class="w-full inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs px-4 py-2.5 rounded-lg shadow-xs transition-colors cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                        <span>Masuk Sebagai Admin Dinas</span>
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.agencies.create_account', $agency->id) }}" class="m-0">
                                    @csrf
                                    <button type="button"
                                            @click="$dispatch('open-confirm-modal', {
                                                form: $el.form,
                                                title: 'Buat Akun Admin Dinas',
                                                message: 'Sistem akan membuat akun login untuk PIC dinas agar dapat mengelola divisi, kuota, dan pengajuan magang.',
                                                label: 'Instansi:',
                                                name: @js($agency->agency_name),
                                                desc: 'Password awal: password',
                                                confirmText: 'Ya, Buat Akun'
                                            })"
                                            class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs px-4 py-2.5 rounded-lg shadow-xs transition-colors cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <span>Buat Akun Admin Dinas</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Ringkasan Metrik Dinas (satu panel, garis pemisah tipis) -->
            <div class="cc-metrics grid grid-cols-2 lg:grid-cols-4 gap-px bg-slate-200/70 rounded-2xl border border-slate-200/80 overflow-hidden shadow-xs">
                <div class="bg-white p-4 sm:p-5">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Divisi Kerja</div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-2xl font-bold text-slate-900">{{ $stats['total_units'] }}</span>
                        <span class="text-sm text-slate-500">divisi aktif</span>
                    </div>
                </div>
                <div class="bg-white p-4 sm:p-5">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Sisa Kuota</div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-2xl font-bold text-blue-600">{{ $stats['total_remaining'] }}</span>
                        <span class="text-sm text-slate-500">dari {{ $stats['total_quota'] }} slot</span>
                    </div>
                </div>
                <div class="bg-white p-4 sm:p-5">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Personel</div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-2xl font-bold text-slate-900">{{ $stats['total_admins'] + $stats['total_mentors'] }}</span>
                        <span class="text-sm text-slate-500">akun</span>
                    </div>
                    <div class="mt-1 text-xs text-slate-500">{{ $stats['total_admins'] }} admin · {{ $stats['total_mentors'] }} mentor</div>
                </div>
                <div class="bg-white p-4 sm:p-5">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ \App\Enums\ApplicationStatus::ACTIVE->label() }}</div>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-2xl font-bold text-emerald-600">{{ $stats['active_students'] }}</span>
                        <span class="text-sm text-slate-500">mahasiswa</span>
                    </div>
                    @if(($stats['needs_action'] ?? 0) > 0)
                        <button type="button" @click="activeTab = 'applications'" class="mt-1 text-xs font-semibold text-amber-700 hover:underline cursor-pointer">
                            {{ $stats['needs_action'] }} pengajuan perlu tindakan
                        </button>
                    @endif
                </div>
            </div>

            <!-- Tab Navigasi Terpadu (Style Simpel Selaras Univ) -->
            <div class="cc-tabs-bare border-b border-slate-200">
                <nav class="cc-tabs flex flex-nowrap space-x-2 sm:space-x-4 overflow-x-auto pb-px scrollbar-none" aria-label="Tabs">
                    <!-- Tab 1: Personil & Akun Kedinasan (Angka 7 dihapus sesuai instruksi) -->
                    <button type="button" 
                            @click="activeTab = 'users'" 
                            :class="activeTab === 'users' ? 'cc-active border-blue-600 text-blue-600 font-black' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-semibold'"
                            class="cc-tab whitespace-nowrap py-3 px-3 sm:px-4 border-b-2 text-xs sm:text-sm flex items-center gap-2 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Personil & Akun Kedinasan</span>
                    </button>

                    <!-- Tab 2: Divisi & Kuota Magang -->
                    <button type="button" 
                            @click="activeTab = 'units'" 
                            :class="activeTab === 'units' ? 'cc-active border-blue-600 text-blue-600 font-black' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-semibold'"
                            class="cc-tab whitespace-nowrap py-3 px-3 sm:px-4 border-b-2 text-xs sm:text-sm flex items-center gap-2 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Divisi & Kuota Magang</span>
                    </button>

                    <!-- Tab 3: Pengajuan Magang Masuk -->
                    <button type="button" 
                            @click="activeTab = 'applications'" 
                            :class="activeTab === 'applications' ? 'cc-active border-blue-600 text-blue-600 font-black' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-semibold'"
                            class="cc-tab whitespace-nowrap py-3 px-3 sm:px-4 border-b-2 text-xs sm:text-sm flex items-center gap-2 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Pengajuan Magang Masuk</span>
                    </button>

                    <!-- Tab 4: Mahasiswa Aktif Magang -->
                    <button type="button" 
                            @click="activeTab = 'students'" 
                            :class="activeTab === 'students' ? 'cc-active border-blue-600 text-blue-600 font-black' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-semibold'"
                            class="cc-tab whitespace-nowrap py-3 px-3 sm:px-4 border-b-2 text-xs sm:text-sm flex items-center gap-2 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                        <span>Mahasiswa Aktif Magang</span>
                    </button>
                </nav>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 1: AKUN PERSONEL KEDINASAN                                 -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'users'" class="cc-card bg-white rounded-2xl border border-slate-100 shadow-2xs overflow-hidden">
                <div class="cc-panel-head flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-black text-slate-900">Daftar Akun Personel Kedinasan</h3>
                        <p class="text-xs text-slate-500">Admin Dinas (PIC) dan Mentor Lapangan yang memiliki akses kelola magang di dinas ini</p>
                    </div>
                    <div class="cc-head-actions flex items-center gap-2">
                        <a href="{{ route('admin.users.create', ['agency_id' => $agency->id, 'role' => 'admin', 'return_to' => url()->current()]) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <span>Tambah Akun Baru</span>
                        </a>
                        <a href="{{ route('admin.users.index', ['agency_id' => $agency->id]) }}" 
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer">
                            <span>Tabel Pengguna</span>
                        </a>
                    </div>
                </div>

                    <div class="d-only overflow-x-auto rounded-2xl border border-slate-100">
                        <table class="rtable min-w-full divide-y divide-slate-100 text-left text-[13px]">
                            <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-xs whitespace-nowrap">
                                <tr>
                                    <th class="py-3 px-4">Nama Personil</th>
                                    <th class="py-3 px-4">Email / Login</th>
                                    <th class="py-3 px-4">Role Kedinasan</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                    <th class="py-3 px-4 text-right">Aksi Manajemen</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($agency->users as $u)
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="rt-title py-3.5 px-4 font-bold text-slate-900" data-label="Nama Personil">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-xl {{ $u->role === 'admin' ? 'bg-blue-50 border border-blue-100 text-blue-700' : 'bg-purple-50 border border-purple-200 text-purple-700' }} flex items-center justify-center font-bold text-xs shrink-0">
                                                    {{ strtoupper(substr($u->name, 0, 2)) }}
                                                </div>
                                                <span>{{ $u->name }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4 font-mono text-slate-600" data-label="Email / Login">
                                            {{ $u->email }}
                                        </td>
                                        <td class="py-3.5 px-4" data-label="Role Kedinasan">
                                            @if($u->role === 'admin')
                                                <span class="whitespace-nowrap px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                    Admin Dinas (PIC)
                                                </span>
                                            @elseif(in_array($u->role, ['mentor', 'pembimbing']))
                                                <span class="whitespace-nowrap px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                                    Mentor Lapangan
                                                </span>
                                            @else
                                                <span class="whitespace-nowrap px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                                    {{ ucfirst($u->role) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-center" data-label="Status">
                                            <x-status-badge type="account" :status="$u->status ?? 'active'" />
                                        </td>
                                        <td class="rt-actions py-3.5 px-4 text-right" data-label="Aksi Manajemen">
                                            <div class="inline-flex items-center justify-end gap-1.5 align-middle">
                                                @if($isSuperAdmin)
                                                    <form action="{{ route('admin.impersonate', $u->id) }}" method="POST" class="inline-flex items-center m-0 p-0 align-middle">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap px-3 py-1.5 text-xs font-medium rounded-lg border transition-all duration-150 bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-800 hover:text-white hover:border-slate-800 cursor-pointer" title="Login As ke Akun Ini">
                                                            Login As
                                                        </button>
                                                    </form>
                                                @endif
                                                <a href="{{ route('admin.users.edit', ['user' => $u->id, 'return_to' => url()->current()]) }}" class="inline-flex items-center justify-center whitespace-nowrap px-3 py-1.5 text-xs font-medium rounded-lg border transition-all duration-150 bg-blue-50 border-blue-200 text-blue-600 hover:bg-blue-600 hover:text-white hover:border-blue-600 cursor-pointer" title="Edit Akun">
                                                    Edit
                                                </a>
                                                @if($isSuperAdmin)
                                                    @php $roleLabel = $u->role === 'admin' ? 'Admin Dinas' : (in_array($u->role, ['mentor', 'pembimbing']) ? 'Mentor Lapangan' : ucfirst($u->role)); @endphp
                                                    <button type="button"
                                                            @click="$dispatch('open-reset-modal', {
                                                                action: '{{ route('admin.users.reset_password', $u->id) }}',
                                                                name: @js($u->name),
                                                                email: @js($u->email),
                                                                role: @js($roleLabel)
                                                            })"
                                                            class="inline-flex items-center justify-center whitespace-nowrap px-3 py-1.5 text-xs font-medium rounded-lg border transition-all duration-150 bg-amber-50 border-amber-200 text-amber-700 hover:bg-amber-500 hover:text-white hover:border-amber-500 cursor-pointer" title="Reset Password ke default">
                                                        Reset
                                                    </button>
                                                    <button type="button"
                                                            @click="$dispatch('open-delete-modal', {
                                                                action: '{{ route('admin.users.destroy', $u->id) }}',
                                                                title: 'Hapus Akun Personel',
                                                                name: @js($u->name),
                                                                desc: @js('Email: ' . $u->email . ' • ' . $roleLabel . ' • Akun dengan riwayat penempatan tidak dapat dihapus')
                                                            })"
                                                            class="inline-flex items-center justify-center whitespace-nowrap px-3 py-1.5 text-xs font-medium rounded-lg border transition-all duration-150 bg-rose-50 border-rose-200 text-rose-600 hover:bg-rose-600 hover:text-white hover:border-rose-600 cursor-pointer" title="Hapus Akun">
                                                        Hapus
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-slate-400 font-medium">
                                            Belum ada akun personel yang terdaftar di dinas ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Versi HP: kartu personel -->
                    <div class="m-only mlist rounded-2xl">
                        @forelse($agency->users as $u)
                            @php $isAdminRole = $u->role === 'admin'; @endphp
                            <div class="mcard">
                                <div class="mcard-head">
                                    <div class="mavatar {{ $isAdminRole ? '' : 'mavatar-indigo' }}">{{ strtoupper(substr($u->name, 0, 2)) }}</div>
                                    <div class="mcard-main">
                                        <div class="mcard-title">{{ $u->name }}</div>
                                        <div class="mcard-sub"><span class="mono" style="overflow-wrap:anywhere">{{ $u->email }}</span></div>
                                    </div>
                                </div>
                                <div class="mcard-body">
                                    <div class="cc-pills">
                                        @if($isAdminRole)
                                            <span class="mpill mpill-blue">Admin Dinas (PIC)</span>
                                        @elseif(in_array($u->role, ['mentor', 'pembimbing']))
                                            <span class="mpill mpill-purple">Mentor Lapangan</span>
                                        @else
                                            <span class="mpill mpill-slate">{{ ucfirst($u->role) }}</span>
                                        @endif
                                        <x-status-badge type="account" :status="$u->status ?? 'active'" />
                                    </div>
                                </div>
                                <div class="mcard-foot">
                                    <div class="cc-actions">
                                        @if($isSuperAdmin)
                                            <form action="{{ route('admin.impersonate', $u->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="mbtn mbtn-login">Login As</button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.users.edit', ['user' => $u->id, 'return_to' => url()->current()]) }}" class="mbtn mbtn-soft">Edit</a>
                                        @if($isSuperAdmin)
                                            <button type="button" class="mbtn mbtn-amber"
                                                    @click="$dispatch('open-reset-modal', {
                                                        action: '{{ route('admin.users.reset_password', $u->id) }}',
                                                        name: @js($u->name),
                                                        email: @js($u->email),
                                                        role: @js($isAdminRole ? 'Admin Dinas' : 'Mentor Lapangan')
                                                    })">Reset</button>
                                            <button type="button" class="mbtn mbtn-danger"
                                                    @click="$dispatch('open-delete-modal', {
                                                        action: '{{ route('admin.users.destroy', $u->id) }}',
                                                        title: 'Hapus Akun Personel',
                                                        name: @js($u->name),
                                                        desc: @js('Email: ' . $u->email . ' • ' . ($isAdminRole ? 'Admin Dinas' : 'Mentor Lapangan') . ' • Akun dengan riwayat penempatan tidak dapat dihapus')
                                                    })">Hapus</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="mempty">Belum ada akun personel yang terdaftar di dinas ini.</div>
                        @endforelse
                    </div>
                </div>

            <!-- ============================================================== -->
            <!-- TAB 2: DIVISI & KUOTA MAGANG                                   -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'units'" class="cc-card bg-white rounded-2xl border border-slate-100 shadow-2xs overflow-hidden" style="display: none;">
                <div class="cc-panel-head flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-black text-slate-900">Divisi & Bidang Kerja Magang</h3>
                        <p class="text-xs text-slate-500">Daftar bidang unit kerja beserta kuota penerimaan peserta magang</p>
                    </div>
                    <a href="{{ route('admin.units.create', ['agency_id' => $agency->id, 'return_to' => url()->current()]) }}"
                       class="cc-head-actions inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer">
                       <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                       <span>Tambah Divisi Baru</span>
                    </a>
                </div>

                    <div class="d-only overflow-x-auto rounded-2xl border border-slate-100">
                        <table class="rtable min-w-full divide-y divide-slate-100 text-left text-[13px]">
                            <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-xs whitespace-nowrap">
                                <tr>
                                    <th class="py-3 px-4">Nama Divisi / Bidang</th>
                                    <th class="py-3 px-4">Deskripsi Kebutuhan</th>
                                    <th class="py-3 px-4 text-center">Total Kuota</th>
                                    <th class="py-3 px-4 text-center">Terisi</th>
                                    <th class="py-3 px-4 text-center">Sisa Kuota</th>
                                    <th class="py-3 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($agency->units as $unit)
                                    @php 
                                        $filled = $unit->accepted_count ?? 0;
                                        $remaining = max(0, $unit->quota - $filled);
                                    @endphp
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="rt-title py-3.5 px-4 font-bold text-slate-900" data-label="Nama Divisi / Bidang">
                                            {{ $unit->name }}
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 max-w-xs truncate" data-label="Deskripsi Kebutuhan">
                                            {{ $unit->description ?? '-' }}
                                        </td>
                                        <td class="py-3.5 px-4 text-center text-base font-bold text-slate-800" data-label="Total Kuota">
                                            {{ $unit->quota }}
                                        </td>
                                        <td class="py-3.5 px-4 text-center text-base font-bold text-emerald-600" data-label="Terisi">
                                            {{ $filled }}
                                        </td>
                                        <td class="py-3.5 px-4 text-center text-base font-bold {{ $remaining > 0 ? 'text-blue-600' : 'text-rose-600' }}" data-label="Sisa Kuota">
                                            {{ $remaining }}
                                        </td>
                                        <td class="rt-actions py-3.5 px-4 text-right" data-label="Aksi">
                                            <div class="inline-flex items-center justify-end gap-1.5 align-middle">
                                                <a href="{{ route('admin.units.edit', ['unit' => $unit->id, 'return_to' => url()->current()]) }}" class="inline-flex items-center justify-center whitespace-nowrap px-3 py-1.5 text-xs font-medium rounded-lg border transition-all duration-150 bg-blue-50 border-blue-200 text-blue-600 hover:bg-blue-600 hover:text-white hover:border-blue-600 cursor-pointer">
                                                    Edit
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-slate-400 font-medium">
                                            Belum ada divisi / bidang kerja magang yang dibuat untuk dinas ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Versi HP: kartu divisi -->
                    <div class="m-only mlist rounded-2xl">
                        @forelse($agency->units as $unit)
                            @php
                                $filled = $unit->accepted_count ?? 0;
                                $remaining = max(0, $unit->quota - $filled);
                                $pct = $unit->quota > 0 ? min(100, round($filled / $unit->quota * 100)) : 0;
                            @endphp
                            <div class="mcard">
                                <div class="mcard-head">
                                    <div class="mcard-main">
                                        <div class="mcard-title">{{ $unit->name }}</div>
                                        <div class="mcard-sub cc-clamp2">{{ $unit->description ?? 'Belum ada deskripsi kebutuhan.' }}</div>
                                    </div>
                                    <span class="mpill {{ $remaining > 0 ? 'mpill-blue' : 'mpill-red' }}">{{ $remaining > 0 ? 'Sisa ' . $remaining : 'Penuh' }}</span>
                                </div>
                                <div class="mcard-body">
                                    <div class="cc-stat3">
                                        <div><b>{{ $unit->quota }}</b><span>Kuota</span></div>
                                        <div><b style="color:#059669">{{ $filled }}</b><span>Terisi</span></div>
                                        <div><b style="color:{{ $remaining > 0 ? '#2563eb' : '#e11d48' }}">{{ $remaining }}</b><span>Sisa</span></div>
                                    </div>
                                    <div class="mbar"><i style="width: {{ $pct }}%; background: {{ $remaining > 0 ? '#3b82f6' : '#f43f5e' }};"></i></div>
                                </div>
                                <div class="mcard-foot">
                                    <a href="{{ route('admin.units.edit', ['unit' => $unit->id, 'return_to' => url()->current()]) }}" class="mbtn mbtn-soft mbtn-block">Edit Divisi</a>
                                </div>
                            </div>
                        @empty
                            <div class="mempty">Belum ada divisi / bidang kerja magang yang dibuat untuk dinas ini.</div>
                        @endforelse
                    </div>
                </div>

            <!-- ============================================================== -->
            <!-- TAB 3: PENGAJUAN MAGANG MASUK                                  -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'applications'" class="cc-card bg-white rounded-2xl border border-slate-100 shadow-2xs overflow-hidden" style="display: none;">
                <div class="cc-panel-head flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-black text-slate-900">Daftar Pengajuan Magang Masuk</h3>
                        <p class="text-xs text-slate-500">Semua berkas lamaran mahasiswa yang mendaftar ke divisi-divisi dinas ini</p>
                    </div>
                    <a href="{{ route('admin.applications.index', ['agency_id' => $agency->id]) }}" 
                       class="cc-head-actions inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer">
                        <span>Buka Verifikasi Lengkap</span>
                    </a>
                </div>

                    <div class="d-only overflow-x-auto rounded-2xl border border-slate-100">
                        <table class="rtable min-w-full divide-y divide-slate-100 text-left text-[13px]">
                            <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-xs whitespace-nowrap">
                                <tr>
                                    <th class="py-3 px-4">Mahasiswa</th>
                                    <th class="py-3 px-4">Universitas / Prodi</th>
                                    <th class="py-3 px-4">Divisi Dituju</th>
                                    <th class="py-3 px-4">Tanggal Daftar</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                    <th class="py-3 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($applications as $app)
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="rt-title py-3.5 px-4 font-bold text-slate-900" data-label="Mahasiswa">
                                            {{ $app->user->name ?? '-' }}
                                            <span class="block text-xs font-mono text-slate-400">{{ $app->user->studentProfile->nim ?? '-' }}</span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-600" data-label="Universitas / Prodi">
                                            {{ $app->user->studentProfile->universitas ?? '-' }}
                                            <span class="block text-xs text-slate-400">{{ $app->user->studentProfile->jurusan ?? '-' }}</span>
                                        </td>
                                        <td class="py-3.5 px-4 font-semibold text-slate-800" data-label="Divisi Dituju">
                                            {{ $app->unit->name ?? '-' }}
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 text-xs" data-label="Tanggal Daftar">
                                            {{ $app->created_at ? $app->created_at->format('d M Y') : '-' }}
                                        </td>
                                        <td class="py-3.5 px-4 text-center" data-label="Status">
                                            @php $actionHint = $app->actionHint(); @endphp
                                            <x-status-badge :status="$app->status" />
                                            @if($actionHint)
                                                <div class="mt-1.5 text-xs font-semibold text-amber-700 whitespace-nowrap">{{ $actionHint }}</div>
                                            @endif
                                        </td>
                                        <td class="rt-actions py-3.5 px-4 text-right" data-label="Aksi">
                                            <div class="inline-flex items-center justify-end gap-1.5 align-middle">
                                                <a href="{{ route('admin.applications.show', $app->id) }}" class="inline-flex items-center justify-center whitespace-nowrap px-3 py-1.5 text-xs font-medium rounded-lg border transition-all duration-150 bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-800 hover:text-white hover:border-slate-800 cursor-pointer">
                                                    Review
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-slate-400 font-medium">
                                            Belum ada pengajuan magang yang masuk ke dinas ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Versi HP: kartu pengajuan -->
                    <div class="m-only mlist rounded-2xl">
                        @forelse($applications as $app)
                            @php
                                $appName = $app->user->name ?? '-';
                                $actionHint = $app->actionHint();
                            @endphp
                            <div class="mcard">
                                <div class="mcard-head">
                                    <div class="mavatar">{{ strtoupper(substr($appName, 0, 2)) }}</div>
                                    <div class="mcard-main">
                                        <div class="mcard-title">{{ $appName }}</div>
                                        <div class="mcard-sub"><span class="mono">{{ $app->user->studentProfile->nim ?? '-' }}</span> &middot; {{ $app->created_at ? $app->created_at->format('d M Y') : '-' }}</div>
                                    </div>
                                </div>
                                <div class="mcard-body cc-grid2">
                                    <div class="cc-span2">
                                        <x-status-badge :status="$app->status" />
                                        @if($actionHint)
                                            <div class="text-xs font-semibold text-amber-700" style="margin-top:6px">{{ $actionHint }}</div>
                                        @endif
                                    </div>
                                    <div class="cc-span2">
                                        <div class="mfield-label">Universitas / Prodi</div>
                                        <div class="mfield-value">{{ $app->user->studentProfile->universitas ?? '-' }}<span class="block text-xs text-slate-400 font-medium">{{ $app->user->studentProfile->jurusan ?? '-' }}</span></div>
                                    </div>
                                    <div class="cc-span2">
                                        <div class="mfield-label">Divisi Dituju</div>
                                        <div class="mfield-value">{{ $app->unit->name ?? '-' }}</div>
                                    </div>
                                </div>
                                <div class="mcard-foot">
                                    <a href="{{ route('admin.applications.show', $app->id) }}" class="mbtn mbtn-primary mbtn-block">Review Pengajuan</a>
                                </div>
                            </div>
                        @empty
                            <div class="mempty">Belum ada pengajuan magang yang masuk ke dinas ini.</div>
                        @endforelse
                    </div>
                </div>

            <!-- ============================================================== -->
            <!-- TAB 4: MAHASISWA AKTIF MAGANG                                  -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'students'" class="cc-card bg-white rounded-2xl border border-slate-100 shadow-2xs overflow-hidden" style="display: none;">
                <div class="cc-panel-head p-4 border-b border-slate-100">
                    <h3 class="text-sm font-black text-slate-900">Mahasiswa Aktif Magang & Mentor Pembimbing</h3>
                    <p class="text-xs text-slate-500">Peserta magang yang sedang aktif menjalani program di lingkungan dinas</p>
                </div>

                    <div class="d-only overflow-x-auto rounded-2xl border border-slate-100">
                        <table class="rtable min-w-full divide-y divide-slate-100 text-left text-[13px]">
                            <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-xs whitespace-nowrap">
                                <tr>
                                    <th class="py-3 px-4">Mahasiswa Magang</th>
                                    <th class="py-3 px-4">Divisi Penempatan</th>
                                    <th class="py-3 px-4">Mentor Pembimbing Lapangan</th>
                                    <th class="py-3 px-4 text-center">Aktivitas Logbook</th>
                                    <th class="py-3 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($activePlacements as $placement)
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="rt-title py-3.5 px-4 font-bold text-slate-900" data-label="Mahasiswa Magang">
                                            {{ $placement->application->user->name ?? '-' }}
                                            <span class="block text-xs font-mono text-slate-400">{{ $placement->application->user->studentProfile->universitas ?? '-' }}</span>
                                        </td>
                                        <td class="py-3.5 px-4 font-semibold text-slate-800" data-label="Divisi Penempatan">
                                            {{ $placement->application->unit->name ?? '-' }}
                                        </td>
                                        <td class="py-3.5 px-4" data-label="Mentor Pembimbing Lapangan">
                                            @if($placement->mentor || $placement->pembimbing)
                                                <span class="font-bold text-slate-800">{{ $placement->mentor->name ?? $placement->pembimbing->name }}</span>
                                                <span class="block text-xs font-mono text-slate-400">{{ $placement->mentor->email ?? $placement->pembimbing->email }}</span>
                                            @else
                                                <span class="text-amber-600 text-xs font-bold italic">Belum ditentukan</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-center font-bold text-slate-700" data-label="Aktivitas Logbook">
                                            {{ $placement->logbooks_count }} Entri
                                        </td>
                                        <td class="rt-actions py-3.5 px-4 text-right" data-label="Aksi">
                                            <div class="inline-flex items-center justify-end gap-1.5 align-middle">
                                                <a href="{{ route('admin.applications.show', $placement->application_id) }}" class="inline-flex items-center justify-center whitespace-nowrap px-3 py-1.5 text-xs font-medium rounded-lg border transition-all duration-150 bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-800 hover:text-white hover:border-slate-800 cursor-pointer">
                                                    Detail
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-slate-400 font-medium">
                                            Belum ada mahasiswa yang aktif magang di dinas ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Versi HP: kartu mahasiswa aktif -->
                    <div class="m-only mlist rounded-2xl">
                        @forelse($activePlacements as $placement)
                            @php
                                $pName = $placement->application->user->name ?? '-';
                                $pMentor = $placement->mentor ?? $placement->pembimbing;
                            @endphp
                            <div class="mcard">
                                <div class="mcard-head">
                                    <div class="mavatar">{{ strtoupper(substr($pName, 0, 2)) }}</div>
                                    <div class="mcard-main">
                                        <div class="mcard-title">{{ $pName }}</div>
                                        <div class="mcard-sub">{{ $placement->application->user->studentProfile->universitas ?? '-' }}</div>
                                    </div>
                                    <span class="mpill mpill-blue">{{ $placement->logbooks_count }} Logbook</span>
                                </div>
                                <div class="mcard-body">
                                    <div>
                                        <div class="mfield-label">Divisi Penempatan</div>
                                        <div class="mfield-value">{{ $placement->application->unit->name ?? '-' }}</div>
                                    </div>
                                    <div>
                                        <div class="mfield-label">Mentor Lapangan</div>
                                        @if($pMentor)
                                            <div class="mfield-value">{{ $pMentor->name }}</div>
                                        @else
                                            <div class="mfield-value" style="color:#d97706">Belum ditentukan</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="mcard-foot">
                                    <a href="{{ route('admin.applications.show', $placement->application_id) }}" class="mbtn mbtn-soft mbtn-block">Lihat Detail</a>
                                </div>
                            </div>
                        @empty
                            <div class="mempty">Belum ada mahasiswa yang aktif magang di dinas ini.</div>
                        @endforelse
                    </div>
                </div>

        </div>
    </div>
</x-app-layout>