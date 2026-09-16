<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                    <span>Master Pengguna Sistem</span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Kelola akun seluruh role: Mahasiswa, Admin Dinas, Mentor Lapangan, DPL Kampus, & Universitas
                </p>
            </div>

            @if($isSuperAdmin)
                <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                    <span>Tambah Pengguna Baru</span>
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8" x-data="userBulkManagement()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

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
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs">
                <form method="GET" action="{{ route('admin.users.index') }}" x-data="{ role: '{{ request('role', '') }}' }" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <!-- Search Input -->
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none text-slate-400" style="padding-left: 1rem !important;">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, NIM..." 
                               class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                               style="padding-left: 2.75rem !important; padding-right: 1rem !important; padding-top: 0.6rem !important; padding-bottom: 0.6rem !important;">
                    </div>

                    <!-- Filter Role -->
                    <select name="role" x-model="role" class="w-full py-2 text-xs border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-medium">
                        <option value="">Semua Role Pengguna</option>
                        <option value="mahasiswa" {{ request('role') === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin Dinas / Super Admin</option>
                        <option value="mentor" {{ request('role') === 'mentor' ? 'selected' : '' }}>Mentor Lapangan</option>
                        <option value="dosen" {{ request('role') === 'dosen' ? 'selected' : '' }}>Dosen Pembimbing (DPL)</option>
                        <option value="universitas" {{ request('role') === 'universitas' ? 'selected' : '' }}>Akun Universitas</option>
                    </select>

                    <!-- Filter Afiliasi / Instansi / Universitas Dinamis -->
                    <div>
                        <!-- Case 1: Universitas / Mahasiswa / Dosen -->
                        <div x-show="['universitas', 'dosen', 'mahasiswa'].includes(role)">
                            <select name="university_id" :disabled="!['universitas', 'dosen', 'mahasiswa'].includes(role)" class="w-full py-2 text-xs border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-medium">
                                <option value="">Semua Universitas / Kampus</option>
                                @foreach($universities as $u)
                                    <option value="{{ $u->id }}" {{ request('university_id') == $u->id ? 'selected' : '' }}>
                                        {{ $u->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Case 2: Admin Dinas / Mentor Lapangan / Default -->
                        <div x-show="!['universitas', 'dosen', 'mahasiswa'].includes(role)">
                            <select name="agency_id" :disabled="['universitas', 'dosen', 'mahasiswa'].includes(role)" class="w-full py-2 text-xs border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-medium">
                                <option value="">Semua Instansi Dinas</option>
                                @foreach($agencies as $ag)
                                    <option value="{{ $ag->id }}" {{ request('agency_id') == $ag->id ? 'selected' : '' }}>
                                        {{ $ag->agency_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Submit & Reset -->
                    <div class="flex items-center gap-2">
                        <button type="submit" class="flex-1 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition cursor-pointer">
                            Terapkan Filter
                        </button>
                        @if(request()->hasAny(['search', 'role', 'agency_id', 'university_id']))
                            <a href="{{ route('admin.users.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold rounded-xl transition">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Users Table Card -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-left text-xs">
                        <thead class="bg-gray-50/75 text-gray-500 font-bold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="py-3.5 px-3 w-10 text-center">
                                    <input type="checkbox" 
                                           :checked="isAllSelected" 
                                           @change="toggleSelectAll()" 
                                           title="Pilih / Batalkan Semua Pengguna di Halaman Ini"
                                           class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500 cursor-pointer">
                                </th>
                                <th class="py-3.5 px-4">Pengguna</th>
                                <th class="py-3.5 px-4">Role Sistem</th>
                                <th class="py-3.5 px-4">Afiliasi / Instansi / Kampus</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-4 text-right">Aksi Manajemen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($users as $u)
                                @php
                                    $isSuperAdminUser = ($u->role === 'super_admin' || ($u->role === 'admin' && is_null($u->agency_profile_id)));
                                    $canSelect = ($u->id !== $currentUser->id && !$isSuperAdminUser);
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition" :class="isSelected({{ $u->id }}) ? 'bg-blue-50/40' : ''">
                                    
                                    <!-- Checkbox Selection -->
                                    <td class="py-4 px-3 text-center whitespace-nowrap">
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

                                    <!-- User Info -->
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-slate-100 to-blue-50 border border-slate-200 flex items-center justify-center font-bold text-slate-700 text-xs">
                                                {{ strtoupper(substr($u->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900 text-xs sm:text-sm">{{ $u->name }}</div>
                                                <div class="text-[11px] text-gray-500 font-mono">{{ $u->email }}</div>
                                                @if($u->studentProfile?->nim)
                                                    <div class="text-[10px] text-blue-600 font-mono">NIM: {{ $u->studentProfile?->nim }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Role Badge -->
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        @if($isSuperAdminUser)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-black bg-blue-100 text-blue-900 border border-blue-300">
                                                Super Admin
                                            </span>
                                        @elseif($u->role === 'admin')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                                Admin Dinas
                                            </span>
                                        @elseif(in_array($u->role, ['mentor', 'pembimbing']))
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-teal-100 text-teal-800 border border-teal-200">
                                                Mentor Dinas
                                            </span>
                                        @elseif(in_array($u->role, ['dosen', 'academic_advisor']))
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                Dosen DPL
                                            </span>
                                        @elseif($u->role === 'universitas')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-sky-100 text-sky-800 border border-sky-200">
                                                Universitas
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                Mahasiswa
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Affiliation -->
                                    <td class="py-4 px-4 text-xs text-slate-600">
                                        @if($u->agencyProfile)
                                            <div class="font-semibold text-gray-800">{{ $u->agencyProfile->agency_name ?? $u->agencyProfile->name ?? 'Dinas Terkait' }}</div>
                                            <div class="text-[10px] text-gray-400">{{ $u->agencyProfile->government_name ?? 'Pemerintah Kota Surabaya' }}</div>
                                        @elseif($u->role === 'mahasiswa')
                                            <div class="font-semibold text-gray-800">{{ $u->studentProfile?->university?->name ?? $u->studentProfile?->universitas ?? (is_string($u->university) ? $u->university : $u->university?->name) ?? '-' }}</div>
                                            <div class="text-[10px] text-gray-400">NIM: {{ $u->studentProfile?->nim ?? '-' }}</div>
                                        @elseif(in_array($u->role, ['dosen', 'academic_advisor', 'universitas']))
                                            <div class="font-semibold text-gray-800">{{ $u->university?->name ?? (is_string($u->university) ? $u->university : null) ?? '-' }}</div>
                                            <div class="text-[10px] text-gray-400">Mitra Perguruan Tinggi</div>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>

                                    <!-- Status -->
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        @if($u->status === 'on_leave')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Cuti</span>
                                        @elseif($u->status === 'inactive')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">Non-Aktif</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                                        @endif
                                    </td>

                                    <!-- Action Buttons -->
                                    <td class="py-4 px-4 text-right whitespace-nowrap">
                                        <div class="btn-action-group">

                                            <!-- Tombol Impersonate / Login As -->
                                            @if($isSuperAdmin && $u->id !== $currentUser->id && !$isSuperAdminUser)
                                                <form action="{{ route('admin.impersonate', $u->id) }}" method="POST" class="btn-action-form">
                                                    @csrf
                                                    <button type="submit" title="Masuk sebagai {{ $u->name }} (Login As)" class="btn-action-login">
                                                        Login As
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- Tombol Edit -->
                                            <a href="{{ route('admin.users.edit', $u->id) }}" class="btn-action-edit">
                                                Edit
                                            </a>

                                            <!-- Reset Password (Double Confirmation Modal) -->
                                            @if(!$isSuperAdminUser || $u->id !== $currentUser->id)
                                                <button type="button" 
                                                        @click="$dispatch('open-reset-modal', {
                                                            action: '{{ route('admin.users.reset_password', $u->id) }}',
                                                            name: '{{ addslashes($u->name) }}',
                                                            email: '{{ addslashes($u->email) }}',
                                                            role: '{{ strtoupper($u->role) }}'
                                                        })" 
                                                        class="btn-action-reset" 
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
                                                            desc: 'Email: {{ $u->email }} &bull; Role: {{ strtoupper($u->role) }}'
                                                        })" 
                                                        class="btn-action-delete"
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
             class="fixed bottom-6 inset-x-0 z-40 max-w-2xl mx-auto px-4 pointer-events-none"
             x-cloak>
            <div class="pointer-events-auto bg-slate-900/95 backdrop-blur-md text-white rounded-3xl p-3.5 sm:p-4 shadow-2xl border border-slate-700/80 flex flex-col sm:flex-row items-center justify-between gap-3.5">
                
                <!-- Info Terpilih -->
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-2xl bg-blue-600 text-white font-black text-xs flex items-center justify-center shadow-md shadow-blue-500/20" x-text="selectedCount"></span>
                    <div>
                        <div class="text-xs font-black text-white tracking-wide">
                            <span x-text="selectedCount"></span> Akun Pengguna Dipilih
                        </div>
                        <button type="button" @click="clearSelection()" class="text-[11px] text-slate-400 hover:text-slate-200 underline transition cursor-pointer">
                            Batalkan Semua Pilihan
                        </button>
                    </div>
                </div>

                <!-- Tombol Aksi Massal -->
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <!-- Reset Massal Button -->
                    <button type="button" 
                            @click="openBulkResetModal()" 
                            class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                        <span>Reset Password Massal</span>
                    </button>

                    <!-- Hapus Massal Button -->
                    <button type="button" 
                            @click="openBulkDeleteModal()" 
                            class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>Hapus Massal</span>
                    </button>
                </div>

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
                            $isSuperAdminUser = ($u->role === 'super_admin' || ($u->role === 'admin' && is_null($u->agency_profile_id)));
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
