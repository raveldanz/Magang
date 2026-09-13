<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.agencies.index') }}" 
                   class="p-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-slate-900 hover:border-slate-300 transition shadow-2xs cursor-pointer" 
                   title="Kembali ke Master Instansi">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 bg-blue-100 text-blue-800 text-[10px] font-black rounded-md uppercase tracking-wider">Pusat Kendali Dinas</span>
                        <span class="text-xs text-slate-400 font-medium">{{ $agency->city ?? 'Surabaya' }}</span>
                    </div>
                    <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight mt-0.5">
                        {{ $agency->agency_name }}
                    </h2>
                </div>
            </div>

            <!-- Header Quick Actions -->
            <div class="flex items-center flex-wrap gap-2">
                @php $firstAdmin = $adminUsers->first(); @endphp
                @if($isSuperAdmin && $firstAdmin)
                    <form action="{{ route('admin.impersonate', $firstAdmin->id) }}" method="POST" class="inline-block m-0">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition active:scale-95 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                            <span>Masuk Sebagai Admin Dinas (Login As)</span>
                        </button>
                    </form>
                @elseif($isSuperAdmin && ($stats['total_admins'] ?? 0) === 0)
                    <form method="POST" action="{{ route('admin.agencies.create_account', $agency->id) }}" class="inline-block m-0">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition active:scale-95 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                            <span>Buat Akun Admin Dinas</span>
                        </button>
                    </form>
                @endif

                <a href="{{ route('admin.agencies.edit', $agency->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-bold rounded-xl shadow-2xs transition">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit Profil & TTD</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ activeTab: 'users' }">
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

            <!-- Hero Profile Card Dinas -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-xs p-6 sm:p-7">
                <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">
                    <div class="flex items-start gap-4 sm:gap-5">
                        @php
                            $agencyLogoUrl = null;
                            if (!empty($agency->logo)) {
                                if (file_exists(public_path($agency->logo))) {
                                    $agencyLogoUrl = asset($agency->logo);
                                } elseif (file_exists(public_path('storage/' . $agency->logo))) {
                                    $agencyLogoUrl = asset('storage/' . $agency->logo);
                                } elseif (file_exists(storage_path('app/public/' . $agency->logo))) {
                                    $agencyLogoUrl = asset('storage/' . $agency->logo);
                                }
                            }
                            if (!$agencyLogoUrl) {
                                $agencyLogoUrl = asset('images/default-agency.svg');
                            }
                        @endphp
                        <div class="w-20 h-20 rounded-2xl bg-white border border-slate-100 shadow-sm flex items-center justify-center p-3 shrink-0 overflow-hidden" style="width: 80px; height: 80px; min-width: 80px; min-height: 80px;">
                            <img src="{{ $agencyLogoUrl }}" alt="Logo {{ $agency->agency_name }}" class="w-14 h-14 object-contain shrink-0" style="width: 56px; height: 56px; max-width: 56px; max-height: 56px; object-fit: contain;">
                        </div>

                        <div class="space-y-1.5">
                            <div class="flex items-center flex-wrap gap-2">
                                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">{{ $agency->agency_name }}</h1>
                                @if(($stats['total_admins'] ?? 0) > 0)
                                    <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-[10px] font-bold">Akun Terdaftar ({{ $stats['total_admins'] }})</span>
                                @else
                                    <span class="px-2.5 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-full text-[10px] font-bold animate-pulse">Belum Ada Akun Admin</span>
                                @endif
                            </div>
                            <p class="text-xs text-blue-600 font-bold uppercase tracking-wider">{{ $agency->government_name ?? 'Pemerintah Kota Surabaya' }}</p>
                            <p class="text-xs text-slate-500 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>{{ $agency->address ?? 'Alamat belum diatur' }}</span>
                            </p>

                            <!-- Kontak Kedinasan -->
                            <div class="flex items-center flex-wrap gap-4 pt-1 text-xs text-slate-600">
                                @if($agency->email)
                                    <span class="flex items-center gap-1 font-mono text-[11px]">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        {{ $agency->email }}
                                    </span>
                                @endif
                                @if($agency->phone)
                                    <span class="flex items-center gap-1 font-mono text-[11px]">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                        {{ $agency->phone }}
                                    </span>
                                @endif
                                @if($agency->website)
                                    <a href="{{ $agency->website }}" target="_blank" class="flex items-center gap-1 text-blue-600 hover:underline text-[11px]">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><line x1="2" y1="12" x2="22" y2="12" stroke-width="2"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" stroke-width="2"/></svg>
                                        {{ parse_url($agency->website, PHP_URL_HOST) ?? $agency->website }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Panel Pejabat Penandatangan Surat -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 shrink-0 lg:max-w-xs w-full">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Pejabat Penandatangan Resmi</span>
                        <div class="font-bold text-xs text-slate-800">{{ $agency->signee_name ?? '-' }}</div>
                        <div class="text-[11px] text-slate-500 font-mono">NIP. {{ $agency->signee_nip ?? '-' }}</div>
                        <div class="text-[11px] text-blue-600 font-medium mt-0.5">{{ $agency->signee_position ?? 'Kepala Dinas' }}</div>
                    </div>
                </div>
            </div>

            <!-- Metrik Alur Dinas Real-time -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-2xs">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Unit / Bidang Kerja</span>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-900">{{ $stats['total_units'] }}</span>
                        <span class="text-xs text-slate-500 font-medium">Divisi Aktif</span>
                    </div>
                    <a href="{{ route('admin.units.index', ['agency_id' => $agency->id]) }}" class="text-[11px] font-bold text-blue-600 hover:underline mt-2 inline-block">
                        Kelola Unit &rarr;
                    </a>
                </div>

                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-2xs">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kuota Magang Tersedia</span>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-teal-600">{{ $stats['total_remaining'] }}</span>
                        <span class="text-xs text-slate-500 font-medium">Slot ({{ $stats['total_filled'] }} terisi)</span>
                    </div>
                    <div class="text-[11px] text-slate-400 mt-2">
                        Total Kapasitas: <strong class="text-slate-700">{{ $stats['total_quota'] }}</strong>
                    </div>
                </div>

                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-2xs">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Personel Kedinasan</span>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-purple-600">{{ $stats['total_admins'] + $stats['total_mentors'] }}</span>
                        <span class="text-xs text-slate-500 font-medium">Akun Sistem</span>
                    </div>
                    <div class="text-[11px] text-slate-500 mt-2">
                        {{ $stats['total_admins'] }} Admin &bull; {{ $stats['total_mentors'] }} Mentor
                    </div>
                </div>

                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-2xs">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Mahasiswa Magang</span>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-emerald-600">{{ $stats['active_students'] }}</span>
                        <span class="text-xs text-slate-500 font-medium">Aktif Magang</span>
                    </div>
                    <div class="text-[11px] text-amber-600 font-bold mt-2">
                        {{ $stats['pending_applications'] }} Menunggu Seleksi
                    </div>
                </div>
            </div>

            <!-- Tabbed Management Control Panel -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-xs overflow-hidden">
                <!-- Tab Header Navigasi -->
                <div class="border-b border-slate-100 px-6 pt-4 flex items-center gap-4 overflow-x-auto">
                    <button type="button" 
                            @click="activeTab = 'users'" 
                            :class="activeTab === 'users' ? 'text-blue-600 border-blue-600 font-black' : 'text-slate-500 border-transparent hover:text-slate-800 font-bold'"
                            class="pb-3 px-1 border-b-2 text-xs uppercase tracking-wider transition whitespace-nowrap cursor-pointer flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>Personel & Akun Kedinasan ({{ $stats['total_admins'] + $stats['total_mentors'] }})</span>
                    </button>

                    <button type="button" 
                            @click="activeTab = 'units'" 
                            :class="activeTab === 'units' ? 'text-blue-600 border-blue-600 font-black' : 'text-slate-500 border-transparent hover:text-slate-800 font-bold'"
                            class="pb-3 px-1 border-b-2 text-xs uppercase tracking-wider transition whitespace-nowrap cursor-pointer flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Divisi & Kuota Magang ({{ $stats['total_units'] }})</span>
                    </button>

                    <button type="button" 
                            @click="activeTab = 'applications'" 
                            :class="activeTab === 'applications' ? 'text-blue-600 border-blue-600 font-black' : 'text-slate-500 border-transparent hover:text-slate-800 font-bold'"
                            class="pb-3 px-1 border-b-2 text-xs uppercase tracking-wider transition whitespace-nowrap cursor-pointer flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Pengajuan Magang Masuk ({{ $stats['total_applications'] }})</span>
                    </button>

                    <button type="button" 
                            @click="activeTab = 'students'" 
                            :class="activeTab === 'students' ? 'text-blue-600 border-blue-600 font-black' : 'text-slate-500 border-transparent hover:text-slate-800 font-bold'"
                            class="pb-3 px-1 border-b-2 text-xs uppercase tracking-wider transition whitespace-nowrap cursor-pointer flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Mahasiswa Aktif Magang ({{ $stats['active_students'] }})</span>
                    </button>
                </div>

                <!-- Konten Tab 1: Akun Personel Kedinasan -->
                <div x-show="activeTab === 'users'" class="p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h3 class="font-bold text-base text-slate-900">Daftar Akun Personel Kedinasan</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Seluruh akun Admin Dinas (PIC) dan Mentor Lapangan yang memiliki akses kelola magang di dinas ini</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.users.create', ['agency_id' => $agency->id, 'role' => 'admin']) }}" 
                               class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span>Tambah Akun Baru</span>
                            </a>
                            <a href="{{ route('admin.users.index', ['agency_id' => $agency->id]) }}" 
                               class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                                <span>Buka Tabel Pengguna Lengkap</span>
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-100">
                        <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                                <tr>
                                    <th class="py-3 px-4">Nama Personel</th>
                                    <th class="py-3 px-4">Email / Login</th>
                                    <th class="py-3 px-4">Role Kedinasan</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                    <th class="py-3 px-4 text-right">Aksi Manajemen</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($agency->users as $u)
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="py-3.5 px-4 font-bold text-slate-900">
                                            {{ $u->name }}
                                        </td>
                                        <td class="py-3.5 px-4 font-mono text-slate-600">
                                            {{ $u->email }}
                                        </td>
                                        <td class="py-3.5 px-4">
                                            @if($u->role === 'admin')
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-blue-50 text-blue-700 border border-blue-200">
                                                    Admin Dinas (PIC)
                                                </span>
                                            @elseif(in_array($u->role, ['mentor', 'pembimbing']))
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-purple-50 text-purple-700 border border-purple-200">
                                                    Mentor Lapangan
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">
                                                    {{ ucfirst($u->role) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            @if(($u->status ?? 'active') === 'active')
                                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-400">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Non-Aktif
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            <div class="btn-action-group">
                                                @if($isSuperAdmin)
                                                    <form action="{{ route('admin.impersonate', $u->id) }}" method="POST" class="btn-action-form">
                                                        @csrf
                                                        <button type="submit" class="btn-action-login" title="Login As ke Akun Ini">
                                                            Login As
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.users.reset_password', $u->id) }}" method="POST" class="btn-action-form" onsubmit="return confirm('Reset password akun {{ addslashes($u->name) }} ke password default (password)?');">
                                                        @csrf
                                                        <button type="submit" class="btn-action-reset" title="Reset Password ke default (password)">
                                                            Reset
                                                        </button>
                                                    </form>
                                                @endif
                                                <a href="{{ route('admin.users.edit', $u->id) }}" class="btn-action-edit" title="Edit Akun">
                                                    Edit
                                                </a>
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
                </div>

                <!-- Konten Tab 2: Unit Kerja & Kuota Magang -->
                <div x-show="activeTab === 'units'" class="p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h3 class="font-bold text-base text-slate-900">Divisi & Bidang Kerja Magang</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Daftar lowongan divisi di bawah {{ $agency->agency_name }} beserta kuota penerimaan</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.units.create', ['agency_id' => $agency->id]) }}" 
                               class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span>Tambah Divisi Baru</span>
                            </a>
                            <a href="{{ route('admin.units.index', ['agency_id' => $agency->id]) }}" 
                               class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                                <span>Buka Manajemen Kuota Lengkap</span>
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-100">
                        <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
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
                                        <td class="py-3.5 px-4 font-bold text-slate-900">
                                            {{ $unit->name }}
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 max-w-xs truncate">
                                            {{ $unit->description ?? '-' }}
                                        </td>
                                        <td class="py-3.5 px-4 text-center font-black text-slate-800">
                                            {{ $unit->quota }}
                                        </td>
                                        <td class="py-3.5 px-4 text-center font-black text-emerald-600">
                                            {{ $filled }}
                                        </td>
                                        <td class="py-3.5 px-4 text-center font-black {{ $remaining > 0 ? 'text-blue-600' : 'text-rose-600' }}">
                                            {{ $remaining }}
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            <div class="btn-action-group">
                                                <a href="{{ route('admin.units.edit', $unit->id) }}" class="btn-action-edit">
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
                </div>

                <!-- Konten Tab 3: Pengajuan Magang Mahasiswa Masuk -->
                <div x-show="activeTab === 'applications'" class="p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h3 class="font-bold text-base text-slate-900">Daftar Pengajuan Magang Masuk</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Semua berkas lamaran mahasiswa yang mendaftar ke divisi-divisi di bawah dinas ini</p>
                        </div>
                        <a href="{{ route('admin.applications.index', ['agency_id' => $agency->id]) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                            <span>Buka Verifikasi Pengajuan Lengkap</span>
                        </a>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-100">
                        <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
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
                                        <td class="py-3.5 px-4 font-bold text-slate-900">
                                            {{ $app->user->name ?? '-' }}
                                            <span class="block text-[11px] font-mono text-slate-400">{{ $app->user->studentProfile->nim ?? '-' }}</span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-600">
                                            {{ $app->user->studentProfile->universitas ?? '-' }}
                                            <span class="block text-[11px] text-slate-400">{{ $app->user->studentProfile->jurusan ?? '-' }}</span>
                                        </td>
                                        <td class="py-3.5 px-4 font-semibold text-slate-800">
                                            {{ $app->unit->name ?? '-' }}
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 text-[11px]">
                                            {{ $app->created_at ? $app->created_at->format('d M Y') : '-' }}
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            @php
                                                $status = strtolower($app->status ?? 'submitted');
                                                $badgeClasses = [
                                                    'submitted' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                    'verified' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                    'accepted' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                    'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
                                                    'completed' => 'bg-purple-50 text-purple-700 border-purple-200',
                                                ];
                                            @endphp
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black border {{ $badgeClasses[$status] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
                                                {{ ucfirst($status) }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            <a href="{{ route('admin.applications.show', $app->id) }}" class="btn-action-edit">
                                                Review
                                            </a>
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
                </div>

                <!-- Konten Tab 4: Mahasiswa Aktif & Bimbingan -->
                <div x-show="activeTab === 'students'" class="p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h3 class="font-bold text-base text-slate-900">Mahasiswa Aktif Magang & Mentor Pembimbing</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Peserta magang yang telah diterima dan sedang menjalani masa magang di {{ $agency->agency_name }}</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-100">
                        <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
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
                                        <td class="py-3.5 px-4 font-bold text-slate-900">
                                            {{ $placement->application->user->name ?? '-' }}
                                            <span class="block text-[11px] font-mono text-slate-400">{{ $placement->application->user->studentProfile->universitas ?? '-' }}</span>
                                        </td>
                                        <td class="py-3.5 px-4 font-semibold text-slate-800">
                                            {{ $placement->application->unit->name ?? '-' }}
                                        </td>
                                        <td class="py-3.5 px-4">
                                            @if($placement->mentor || $placement->pembimbing)
                                                <span class="font-bold text-slate-800">{{ $placement->mentor->name ?? $placement->pembimbing->name }}</span>
                                                <span class="block text-[11px] font-mono text-slate-400">{{ $placement->mentor->email ?? $placement->pembimbing->email }}</span>
                                            @else
                                                <span class="text-amber-600 text-[11px] font-bold italic">Belum ditentukan</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-center font-bold text-slate-700">
                                            {{ $placement->logbooks->count() }} Entri
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            <a href="{{ route('admin.applications.show', $placement->application_id) }}" class="btn-action-edit">
                                                Detail
                                            </a>
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
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
