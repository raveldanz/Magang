<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                    <span>Master Pengguna Sistem</span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Kelola akun seluruh role: Mahasiswa, Admin Bidang, Mentor Lapangan, DPL Kampus, & Universitas
                </p>
            </div>

            @if($isSuperAdmin)
                <a href="{{ route('admin.users.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer w-full sm:w-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                    <span>Tambah Pengguna Baru</span>
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-6 sm:py-8" x-data="userBulkManagement()">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-5 sm:space-y-6">

            <!-- Flash Alert -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('warning'))
                <div class="p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-xl shadow-xs flex items-center justify-between text-amber-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>{{ session('warning') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-xs flex items-center justify-between text-rose-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- Active Agency Filter Indicator Banner -->
            @if(isset($selectedAgency) && $selectedAgency)
                <div class="p-4 bg-purple-50/80 border border-purple-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-purple-900 shadow-2xs">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-purple-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <div>
                            <span class="font-medium text-purple-700">Filter Aktif Personil Instansi Dinas:</span>
                            <h4 class="font-black text-sm text-purple-950">{{ $selectedAgency->agency_name }}</h4>
                        </div>
                    </div>
                    <div class="flex items-center flex-wrap gap-2 shrink-0">
                        <a href="{{ route('admin.users.create', ['agency_id' => $selectedAgency->id, 'role' => 'admin']) }}" class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl shadow-xs transition">
                            + Tambah Akun Dinas Ini
                        </a>
                        <a href="{{ route('admin.agencies.show', $selectedAgency->id) }}" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 font-bold rounded-xl transition">
                            Pusat Kendali Dinas
                        </a>
                        <a href="{{ route('admin.units.index', ['agency_id' => $selectedAgency->id]) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 font-bold rounded-xl transition">
                            Lihat Unit Dinas
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 font-bold rounded-xl transition">
                            Reset Filter
                        </a>
                    </div>
                </div>
            @endif

            <!-- Active University Filter Indicator Banner -->
            @if(isset($selectedUniversity) && $selectedUniversity)
                <div class="p-4 bg-indigo-50/80 border border-indigo-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-indigo-900 shadow-2xs">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                        </div>
                        <div>
                            <span class="font-medium text-indigo-700">Filter Aktif Sivitas Perguruan Tinggi:</span>
                            <h4 class="font-black text-sm text-indigo-950">{{ $selectedUniversity->name }} ({{ $selectedUniversity->code }})</h4>
                        </div>
                    </div>
                    <div class="flex items-center flex-wrap gap-2 shrink-0">
                        <a href="{{ route('admin.universities.show', $selectedUniversity->id) }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-xs transition">
                            Pusat Kendali Kampus
                        </a>
                        <a href="{{ route('admin.applications.index', ['university_id' => $selectedUniversity->id]) }}" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 font-bold rounded-xl transition">
                            Pengajuan Mahasiswa
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 font-bold rounded-xl transition">
                            Reset Filter
                        </a>
                    </div>
                </div>
            @endif

            <!-- Search & Filter Card -->
            <div class="bg-white rounded-2xl border border-gray-100 p-3.5 sm:p-4 shadow-xs">
                <form method="GET" action="{{ route('admin.users.index') }}" x-data="{ role: '{{ request('role', '') }}' }" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3">
                    <!-- Search Input -->
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none text-slate-400" style="padding-left: 1rem !important;">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, NIM..." 
                               class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                               style="padding-left: 2.75rem !important; padding-right: 1rem !important; padding-top: 0.65rem !important; padding-bottom: 0.65rem !important;">
                    </div>

                    <!-- Filter Role -->
                    <select name="role" x-model="role" class="w-full py-2.5 text-xs border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-medium">
                        <option value="">Semua Role Pengguna</option>
                        <option value="mahasiswa" {{ request('role') === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin Bidang / Dispusip</option>
                        <option value="supervisor" {{ request('role') === 'supervisor' ? 'selected' : '' }}>Atasan (Supervisor)</option>
                        <option value="mentor" {{ request('role') === 'mentor' ? 'selected' : '' }}>Mentor Lapangan</option>
                        <option value="dosen" {{ request('role') === 'dosen' ? 'selected' : '' }}>Dosen Pembimbing</option>
                        <option value="universitas" {{ request('role') === 'universitas' ? 'selected' : '' }}>Akun Universitas</option>
                    </select>

                    <!-- Filter Afiliasi / Instansi / Universitas Dinamis -->
                    <div>
                        <!-- Case 1: Universitas / Mahasiswa / Dosen -->
                        <div x-show="['universitas', 'dosen', 'mahasiswa'].includes(role)">
                            <select name="university_id" :disabled="!['universitas', 'dosen', 'mahasiswa'].includes(role)" class="w-full py-2.5 text-xs border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-medium">
                                <option value="">Semua Universitas / Kampus</option>
                                @foreach($universities as $u)
                                    <option value="{{ $u->id }}" {{ request('university_id') == $u->id ? 'selected' : '' }}>
                                        {{ $u->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Case 2: Admin Dinas / Mentor Lapangan / Default -->
                        @if(isset($agencies) && count($agencies) > 1)
                            <div x-show="!['universitas', 'dosen', 'mahasiswa'].includes(role)">
                                <select name="agency_id" :disabled="['universitas', 'dosen', 'mahasiswa'].includes(role)" class="w-full py-2.5 text-xs border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-medium">
                                    <option value="">Semua Instansi Dinas</option>
                                    @foreach($agencies as $ag)
                                        <option value="{{ $ag->id }}" {{ request('agency_id') == $ag->id ? 'selected' : '' }}>
                                            {{ $ag->agency_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>

                    <!-- Submit & Reset -->
                    <div class="flex items-center gap-2">
                        <button type="submit" class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition cursor-pointer text-center">
                            Terapkan Filter
                        </button>
                        @if(request()->hasAny(['search', 'role', 'agency_id', 'university_id']))
                            <a href="{{ route('admin.users.index') }}" class="px-3.5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold rounded-xl transition text-center">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Users Presentation Card (Desktop Table + Mobile Card View) -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
                
                <!-- 1. Tampilan Tabel Desktop & Tablet (>= 768px) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-left text-xs">
                        <thead class="bg-gray-50/75 text-gray-500 font-bold uppercase tracking-wider text-[11px] select-none">
                            <tr>
                                <th class="py-3.5 pl-8 sm:pl-10 pr-3 w-12 text-center whitespace-nowrap">
                                    <input type="checkbox" 
                                           :checked="isAllSelected" 
                                           @change="toggleSelectAll()" 
                                           title="Pilih / Batalkan Semua Pengguna di Halaman Ini"
                                           class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500 cursor-pointer">
                                </th>
                                <th class="py-3.5 px-4 text-left">Pengguna</th>
                                <th class="py-3.5 px-4 text-left">Role Sistem</th>
                                <th class="py-3.5 px-4 text-left">Instansi</th>
                                <th class="py-3.5 px-4 text-center whitespace-nowrap">Status</th>
                                <th class="py-3.5 px-6 sm:px-8 text-center whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($users as $u)
                                @php
                                    $isSuperAdminUser = $u->isSuperAdmin();
                                    $canSelect = ($u->id !== $currentUser->id && !$isSuperAdminUser);
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition" :class="isSelected({{ $u->id }}) ? 'bg-blue-50/40' : ''">
                                    
                                    <!-- Checkbox Selection (pl-8 sm:pl-10 lega di ujung kiri, tidak mepet border) -->
                                    <td class="py-4 pl-8 sm:pl-10 pr-3 w-12 text-center whitespace-nowrap">
                                        @if($canSelect)
                                            <input type="checkbox" 
                                                   :checked="isSelected({{ $u->id }})" 
                                                   @change="toggleSelectUser({ id: {{ $u->id }}, name: '{{ addslashes($u->name) }}', email: '{{ addslashes($u->email) }}', role: '{{ $u->role }}' })"
                                                   class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500 cursor-pointer">
                                        @else
                                            <span class="inline-flex text-slate-300 cursor-not-allowed" title="Akun Super Admin / Akun Sendiri Dilindungi">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            </span>
                                        @endif
                                    </td>

                                    <!-- User Info (Nama Dikebawahkan Rapi, Tidak Makan Banyak Tempat & Email & NIM) -->
                                    <td class="py-4 px-4 min-w-[220px] max-w-[240px]">
                                        <div>
                                            <div class="font-bold text-gray-900 text-xs sm:text-sm leading-snug break-words">{{ $u->name }}</div>
                                            <div class="text-[11px] text-gray-500 font-mono mt-0.5 truncate" title="{{ $u->email }}">{{ $u->email }}</div>
                                            @if($u->studentProfile?->nim)
                                                <div class="text-[10px] text-blue-600 font-mono mt-0.5">NIM: {{ $u->studentProfile?->nim }}</div>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Role Badge (Kompak & Elegan, tidak bertumpuk/bertabrakan) -->
                                    <td class="py-4 px-4 max-w-[220px]">
                                        @if($isSuperAdminUser)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-black bg-blue-100 text-blue-900 border border-blue-300 whitespace-nowrap">
                                                Super Admin
                                            </span>
                                        @elseif($u->role === 'admin')
                                            @if($u->unit_id)
                                                <div class="space-y-1">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200 whitespace-nowrap">
                                                        Admin Bidang
                                                    </span>
                                                    <div class="text-[11px] text-slate-600 font-medium leading-snug break-words">
                                                        {{ $u->unit->name ?? 'Bidang' }}
                                                    </div>
                                                </div>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200 whitespace-nowrap">
                                                    Admin Dispusip
                                                </span>
                                            @endif
                                        @elseif($u->role === 'supervisor')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-100 text-purple-800 border border-purple-200 whitespace-nowrap">
                                                Atasan (Supervisor)
                                            </span>
                                        @elseif(in_array($u->role, ['mentor', 'pembimbing']))
                                            @if($u->unit_id)
                                                <div class="space-y-1">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-teal-100 text-teal-800 border border-teal-200 whitespace-nowrap">
                                                        Mentor Bidang
                                                    </span>
                                                    <div class="text-[11px] text-slate-600 font-medium leading-snug break-words">
                                                        {{ $u->unit->name ?? 'Bidang' }}
                                                    </div>
                                                </div>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-teal-100 text-teal-800 border border-teal-200 whitespace-nowrap">
                                                    Mentor Dinas
                                                </span>
                                            @endif
                                        @elseif(in_array($u->role, ['dosen', 'academic_advisor']))
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200 whitespace-nowrap">
                                                Dosen Pembimbing
                                            </span>
                                        @elseif($u->role === 'universitas')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-100 text-sky-800 border border-sky-200 whitespace-nowrap">
                                                Universitas
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 whitespace-nowrap">
                                                Mahasiswa
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Instansi (Ringkas & Proporsional, teks membungkus rapi) -->
                                    <td class="py-4 px-4 text-xs text-slate-600 max-w-[220px]">
                                        @if($u->agencyProfile || in_array($u->role, ['admin', 'supervisor', 'mentor', 'pembimbing']))
                                            <div class="font-semibold text-gray-800 text-xs">Dispusip Surabaya</div>
                                            <div class="text-[10px] text-gray-400">Pemerintah Kota Surabaya</div>
                                        @elseif($u->role === 'mahasiswa')
                                            @php
                                                $univName = $u->studentProfile?->universitas ?? $u->studentProfile?->university?->name ?? (is_string($u->university) ? $u->university : $u->university?->name) ?? '-';
                                            @endphp
                                            <div class="font-semibold text-gray-800 text-xs leading-snug break-words" title="{{ $univName }}">{{ $univName }}</div>
                                            @if($u->studentProfile?->jurusan)
                                                <div class="text-[10px] text-gray-400 mt-0.5 leading-snug break-words" title="{{ $u->studentProfile->jurusan }}">{{ $u->studentProfile->jurusan }}</div>
                                            @endif
                                        @elseif(in_array($u->role, ['dosen', 'academic_advisor', 'universitas']))
                                            @php
                                                $univName = $u->university?->name ?? (is_string($u->university) ? $u->university : null) ?? '-';
                                            @endphp
                                            <div class="font-semibold text-gray-800 text-xs leading-snug break-words" title="{{ $univName }}">{{ $univName }}</div>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>

                                    <!-- Status -->
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        <x-status-badge type="account" :status="$u->status ?? 'active'" />
                                    </td>

                                    <!-- Action Buttons (px-6 sm:px-8 center-aligned and balanced) -->
                                    <td class="py-4 px-6 sm:px-8 text-center whitespace-nowrap">
                                        <div class="inline-flex items-center justify-center gap-1.5 align-middle">

                                            <!-- Tombol Impersonate / Login As -->
                                            @if($isSuperAdmin && $u->id !== $currentUser->id && !$isSuperAdminUser)
                                                <form action="{{ route('admin.impersonate', $u->id) }}" method="POST" class="inline-flex items-center m-0 p-0 align-middle">
                                                    @csrf
                                                    <button type="submit" title="Masuk sebagai {{ $u->name }} (Login As)"
                                                            class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium rounded-lg border transition-all duration-150 bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-800 hover:text-white hover:border-slate-800 cursor-pointer">
                                                        Login As
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- Tombol Edit -->
                                            <a href="{{ route('admin.users.edit', $u->id) }}"
                                               class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium rounded-lg border transition-all duration-150 bg-blue-50 border-blue-200 text-blue-600 hover:bg-blue-600 hover:text-white">
                                                Edit
                                            </a>

                                            <!-- Reset Password (Double Confirmation Modal) -->
                                            @if(!$isSuperAdminUser || $u->id !== $currentUser->id)
                                                <button type="button" 
                                                        @click="$dispatch('open-reset-modal', {
                                                            action: '{{ route('admin.users.reset_password', $u->id) }}',
                                                            name: '{{ addslashes($u->name) }}',
                                                            email: '{{ addslashes($u->email) }}',
                                                            role: '{{ addslashes($u->roleLabel()) }}'
                                                        })" 
                                                        class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium rounded-lg border transition-all duration-150 bg-amber-50 border-amber-200 text-amber-600 hover:bg-amber-600 hover:text-white cursor-pointer" 
                                                        title="Reset password ke default: password">
                                                    Reset
                                                </button>
                                            @endif

                                            <!-- Hapus User (Double Confirmation Modal) -->
                                            @if($u->id !== $currentUser->id && !$isSuperAdminUser)
                                                <button type="button" 
                                                        @click="$dispatch('open-delete-modal', {
                                                            action: '{{ route('admin.users.destroy', $u->id) }}',
                                                            title: 'Hapus Akun Pengguna',
                                                            name: '{{ addslashes($u->name) }}',
                                                            desc: 'Email: {{ $u->email }} &bull; Role: {{ addslashes($u->roleLabel()) }}'
                                                        })" 
                                                        class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium rounded-lg border transition-all duration-150 bg-rose-50 border-rose-200 text-rose-600 hover:bg-rose-600 hover:text-white cursor-pointer" 
                                                        title="Hapus Pengguna">
                                                    Hapus
                                                </button>
                                            @endif

                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-gray-400">
                                        Tidak ada data pengguna yang sesuai filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- 2. Tampilan Kartu Khusus Mobile (< 768px) -->
                <div class="md:hidden bg-slate-50/70 p-3 sm:p-4 space-y-3">
                    <!-- Mobile Select All Bar -->
                    <div class="flex items-center justify-between px-3.5 py-2.5 bg-white rounded-xl border border-slate-200/80 shadow-2xs text-xs text-slate-700">
                        <label class="inline-flex items-center gap-2.5 font-bold cursor-pointer select-none">
                            <input type="checkbox" 
                                   :checked="isAllSelected" 
                                   @change="toggleSelectAll()" 
                                   class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500 cursor-pointer">
                            <span>Pilih Semua Pengguna</span>
                        </label>
                        <span class="text-[11px] font-semibold text-slate-500" x-show="selectedCount > 0">
                            <span x-text="selectedCount" class="font-black text-blue-600"></span> terpilih
                        </span>
                    </div>

                    <!-- Mobile Individual User Cards -->
                    <div class="space-y-3">
                        @forelse($users as $u)
                            @php
                                $isSuperAdminUser = $u->isSuperAdmin();
                                $canSelect = ($u->id !== $currentUser->id && !$isSuperAdminUser);
                                $showLoginAs = ($isSuperAdmin && $u->id !== $currentUser->id && !$isSuperAdminUser);
                                $showReset = (!$isSuperAdminUser || $u->id !== $currentUser->id);
                                $showDelete = ($u->id !== $currentUser->id && !$isSuperAdminUser);
                            @endphp
                            <div class="bg-white rounded-2xl border border-slate-200/80 p-3.5 shadow-2xs transition-all space-y-3"
                                 :class="isSelected({{ $u->id }}) ? 'ring-2 ring-blue-500 bg-blue-50/20' : 'hover:border-slate-300'">
                                
                                <!-- 1. Identitas Atas & Status Badge -->
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-2.5 min-w-0">
                                        <div class="pt-0.5 shrink-0">
                                            @if($canSelect)
                                                <input type="checkbox" 
                                                       :checked="isSelected({{ $u->id }})" 
                                                       @change="toggleSelectUser({ id: {{ $u->id }}, name: '{{ addslashes($u->name) }}', email: '{{ addslashes($u->email) }}', role: '{{ $u->role }}' })"
                                                       class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500 cursor-pointer">
                                            @else
                                                <span class="inline-flex text-slate-300 cursor-not-allowed" title="Akun Super Admin / Akun Sendiri Dilindungi">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                </span>
                                            @endif
                                        </div>

                                        <div class="min-w-0">
                                            <div class="font-bold text-slate-900 text-sm leading-snug break-words">{{ $u->name }}</div>
                                            <div class="text-[11px] text-slate-500 font-mono mt-0.5 break-all">{{ $u->email }}</div>
                                            @if($u->studentProfile?->nim)
                                                <div class="inline-flex items-center mt-1 text-[10px] font-mono font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100">
                                                    NIM: {{ $u->studentProfile?->nim }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Status Badge -->
                                    <div class="shrink-0">
                                        <x-status-badge type="account" :status="$u->status ?? 'active'" />
                                    </div>
                                </div>

                                <!-- 2. Meta Strip: Role & Instansi -->
                                <div class="pt-2 border-t border-slate-100/80 flex flex-col gap-1.5 text-xs">
                                    <!-- Role Pill -->
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        @if($isSuperAdminUser)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black bg-blue-100 text-blue-900 border border-blue-300">
                                                Super Admin
                                            </span>
                                        @elseif($u->role === 'admin')
                                            @if($u->unit_id)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                                    Admin Bidang
                                                </span>
                                                <span class="text-[11px] text-slate-600 font-medium truncate max-w-[200px]">
                                                    {{ $u->unit->name ?? 'Bidang' }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                                    Admin Dispusip
                                                </span>
                                            @endif
                                        @elseif($u->role === 'supervisor')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                                Atasan (Supervisor)
                                            </span>
                                        @elseif(in_array($u->role, ['mentor', 'pembimbing']))
                                            @if($u->unit_id)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-teal-100 text-teal-800 border border-teal-200">
                                                    Mentor Bidang
                                                </span>
                                                <span class="text-[11px] text-slate-600 font-medium truncate max-w-[200px]">
                                                    {{ $u->unit->name ?? 'Bidang' }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-teal-100 text-teal-800 border border-teal-200">
                                                    Mentor Dinas
                                                </span>
                                            @endif
                                        @elseif(in_array($u->role, ['dosen', 'academic_advisor']))
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                Dosen Pembimbing
                                            </span>
                                        @elseif($u->role === 'universitas')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200">
                                                Universitas
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                Mahasiswa
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Instansi -->
                                    <div class="flex items-center gap-1.5 text-[11px] text-slate-600 font-medium">
                                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        <span class="truncate">
                                            @if($u->agencyProfile || in_array($u->role, ['admin', 'supervisor', 'mentor', 'pembimbing']))
                                                Dispusip Surabaya <span class="text-slate-400 text-[10px] font-normal">&bull; Pemkot Surabaya</span>
                                            @elseif($u->role === 'mahasiswa')
                                                {{ $u->studentProfile?->universitas ?? $u->studentProfile?->university?->name ?? (is_string($u->university) ? $u->university : $u->university?->name) ?? '-' }}
                                            @elseif(in_array($u->role, ['dosen', 'academic_advisor', 'universitas']))
                                                {{ $u->university?->name ?? (is_string($u->university) ? $u->university : null) ?? '-' }}
                                            @else
                                                <span class="text-slate-400 font-normal">-</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                <!-- 3. Action Buttons Mobile: Dedicated Symmetrical Grid Action Bar -->
                                <div class="pt-2.5 border-t border-slate-100">
                                    @if($showLoginAs)
                                        <!-- Format 4 Tombol: 2x2 Grid Simetris Sempurna -->
                                        <div class="grid grid-cols-2 gap-2">
                                            <!-- Login As -->
                                            <form action="{{ route('admin.impersonate', $u->id) }}" method="POST" class="m-0">
                                                @csrf
                                                <button type="submit" 
                                                        title="Masuk sebagai {{ $u->name }} (Login As)"
                                                        class="w-full py-2 px-2.5 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-xl transition active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                                    <span>Login As</span>
                                                </button>
                                            </form>

                                            <!-- Edit -->
                                            <a href="{{ route('admin.users.edit', $u->id) }}" 
                                               class="w-full py-2 px-2.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 font-semibold text-xs rounded-xl transition active:scale-95 flex items-center justify-center gap-1.5 text-center">
                                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                <span>Edit</span>
                                            </a>

                                            <!-- Reset -->
                                            @if($showReset)
                                                <button type="button" 
                                                        @click="$dispatch('open-reset-modal', {
                                                            action: '{{ route('admin.users.reset_password', $u->id) }}',
                                                            name: '{{ addslashes($u->name) }}',
                                                            email: '{{ addslashes($u->email) }}',
                                                            role: '{{ addslashes($u->roleLabel()) }}'
                                                        })" 
                                                        class="w-full py-2 px-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-semibold text-xs rounded-xl transition active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer" 
                                                        title="Reset password ke default: password">
                                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                                    <span>Reset</span>
                                                </button>
                                            @endif

                                            <!-- Hapus -->
                                            @if($showDelete)
                                                <button type="button" 
                                                        @click="$dispatch('open-delete-modal', {
                                                            action: '{{ route('admin.users.destroy', $u->id) }}',
                                                            title: 'Hapus Akun Pengguna',
                                                            name: '{{ addslashes($u->name) }}',
                                                            desc: 'Email: {{ $u->email }} &bull; Role: {{ addslashes($u->roleLabel()) }}'
                                                        })" 
                                                        class="w-full py-2 px-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-semibold text-xs rounded-xl transition active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer"
                                                        title="Hapus Pengguna">
                                                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    <span>Hapus</span>
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <!-- Format Non-SuperAdmin: Grid Proporsional -->
                                        <div class="grid {{ ($showReset && $showDelete) ? 'grid-cols-3' : (($showReset || $showDelete) ? 'grid-cols-2' : 'grid-cols-1') }} gap-2">
                                            <!-- Edit -->
                                            <a href="{{ route('admin.users.edit', $u->id) }}" 
                                               class="w-full py-2 px-2.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 font-semibold text-xs rounded-xl transition active:scale-95 flex items-center justify-center gap-1.5 text-center">
                                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                <span>Edit</span>
                                            </a>

                                            <!-- Reset -->
                                            @if($showReset)
                                                <button type="button" 
                                                        @click="$dispatch('open-reset-modal', {
                                                            action: '{{ route('admin.users.reset_password', $u->id) }}',
                                                            name: '{{ addslashes($u->name) }}',
                                                            email: '{{ addslashes($u->email) }}',
                                                            role: '{{ addslashes($u->roleLabel()) }}'
                                                        })" 
                                                        class="w-full py-2 px-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-semibold text-xs rounded-xl transition active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer" 
                                                        title="Reset password ke default: password">
                                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                                    <span>Reset</span>
                                                </button>
                                            @endif

                                            <!-- Hapus -->
                                            @if($showDelete)
                                                <button type="button" 
                                                        @click="$dispatch('open-delete-modal', {
                                                            action: '{{ route('admin.users.destroy', $u->id) }}',
                                                            title: 'Hapus Akun Pengguna',
                                                            name: '{{ addslashes($u->name) }}',
                                                            desc: 'Email: {{ $u->email }} &bull; Role: {{ addslashes($u->roleLabel()) }}'
                                                        })" 
                                                        class="w-full py-2 px-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-semibold text-xs rounded-xl transition active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer"
                                                        title="Hapus Pengguna">
                                                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    <span>Hapus</span>
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-12 text-center text-gray-400 text-xs bg-white rounded-2xl border border-slate-200/80">
                                Tidak ada data pengguna yang sesuai filter.
                            </div>
                        @endforelse
                    </div>
                </div>

                @if($users->hasPages())
                    <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>

        </div>

        <!-- Sticky Floating Bulk Action Bar (muncul jika ada user yang dicentang) -->
        <div x-show="selectedCount > 0"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-8 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 scale-95"
             class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[90] bg-slate-900/95 backdrop-blur-md text-white border border-slate-700/60 shadow-2xl rounded-2xl px-5 py-3 flex items-center justify-between w-max max-w-full gap-4 sm:gap-6"
             x-cloak style="display: none;">
            
            <!-- Left: Info & Cancel -->
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center bg-blue-600 text-white text-xs font-semibold px-2.5 py-1 rounded-full" x-text="selectedCount"></span>
                    <span class="text-sm font-medium text-slate-200 hidden sm:inline">akun dipilih</span>
                </div>
                <button type="button" @click="clearSelection()" class="text-xs text-slate-400 hover:text-white underline transition">
                    Batal
                </button>
            </div>
            
            <!-- Divider -->
            <div class="h-6 w-px bg-slate-700 hidden sm:block"></div>

            <!-- Right: Actions -->
            <div class="flex items-center gap-2">
                <!-- Reset Massal Button -->
                <button type="button" 
                        @click="openBulkResetModal()" 
                        class="bg-amber-600 hover:bg-amber-500 text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm inline-flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    <span>Reset</span>
                </button>

                <!-- Hapus Massal Button -->
                <button type="button" 
                        @click="openBulkDeleteModal()" 
                        class="bg-rose-600 hover:bg-rose-500 text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm inline-flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Hapus</span>
                </button>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function userBulkManagement() {
            return {
                selectedUsers: [],

                get selectedCount() {
                    return this.selectedUsers.length;
                },

                get isAllSelected() {
                    const selectable = this.getSelectableUsers();
                    return selectable.length > 0 && selectable.every(u => this.isSelected(u.id));
                },

                isSelected(id) {
                    return this.selectedUsers.some(u => u.id === id);
                },

                toggleSelectUser(user) {
                    const idx = this.selectedUsers.findIndex(u => u.id === user.id);
                    if (idx > -1) {
                        this.selectedUsers.splice(idx, 1);
                    } else {
                        this.selectedUsers.push(user);
                    }
                },

                toggleSelectAll() {
                    const selectable = this.getSelectableUsers();
                    if (this.isAllSelected) {
                        const selectableIds = selectable.map(u => u.id);
                        this.selectedUsers = this.selectedUsers.filter(u => !selectableIds.includes(u.id));
                    } else {
                        selectable.forEach(u => {
                            if (!this.isSelected(u.id)) {
                                this.selectedUsers.push({
                                    id: u.id,
                                    name: u.name,
                                    email: u.email,
                                    role: u.role
                                });
                            }
                        });
                    }
                },

                clearSelection() {
                    this.selectedUsers = [];
                },

                getSelectableUsers() {
                    return this.pageUsers.filter(u => u.selectable);
                },

                pageUsers: [
                    @foreach($users as $u)
                        @php
                            $isSuperAdminUser = $u->isSuperAdmin();
                            $canSelect = ($u->id !== $currentUser->id && !$isSuperAdminUser);
                        @endphp
                        {
                            id: {{ $u->id }},
                            name: '{{ addslashes($u->name) }}',
                            email: '{{ addslashes($u->email) }}',
                            role: '{{ $u->role }}',
                            selectable: {{ $canSelect ? 'true' : 'false' }}
                        },
                    @endforeach
                ],

                openBulkResetModal() {
                    if (this.selectedCount === 0) return;
                    window.dispatchEvent(new CustomEvent('open-bulk-modal', {
                        detail: {
                            action: '{{ route('admin.users.bulk_reset_password') }}',
                            mode: 'reset',
                            title: 'Reset Password Massal',
                            users: this.selectedUsers,
                            userIds: this.selectedUsers.map(u => u.id)
                        }
                    }));
                },

                openBulkDeleteModal() {
                    if (this.selectedCount === 0) return;
                    window.dispatchEvent(new CustomEvent('open-bulk-modal', {
                        detail: {
                            action: '{{ route('admin.users.bulk_delete') }}',
                            mode: 'delete',
                            title: 'Hapus Massal Akun Pengguna',
                            users: this.selectedUsers,
                            userIds: this.selectedUsers.map(u => u.id)
                        }
                    }));
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
