<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                    <span>👥</span>
                    <span>Master Pengguna Sistem</span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Kelola akun seluruh role: Mahasiswa, Admin Dinas, Mentor Lapangan, DPL Kampus, & Universitas
                </p>
            </div>

            @if($isSuperAdmin)
                <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                    <span>Tambah Pengguna Baru</span>
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Alert -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <span>✅</span>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-xs flex items-center justify-between text-rose-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <span>⚠️</span>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- Search & Filter Card -->
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs">
                <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
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
                    <select name="role" class="w-full py-2 text-xs border-gray-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-2xs font-medium">
                        <option value="">Semua Role Pengguna</option>
                        <option value="mahasiswa" {{ request('role') === 'mahasiswa' ? 'selected' : '' }}>🎓 Mahasiswa</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>🏢 Admin Dinas / Super Admin</option>
                        <option value="mentor" {{ request('role') === 'mentor' ? 'selected' : '' }}>👔 Mentor Lapangan</option>
                        <option value="dosen" {{ request('role') === 'dosen' ? 'selected' : '' }}>👨‍🏫 Dosen Pembimbing (DPL)</option>
                        <option value="universitas" {{ request('role') === 'universitas' ? 'selected' : '' }}>🏛️ Akun Universitas</option>
                    </select>

                    <!-- Filter Instansi -->
                    <select name="agency_id" class="w-full py-2 text-xs border-gray-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-2xs font-medium">
                        <option value="">Semua Instansi Dinas</option>
                        @foreach($agencies as $ag)
                            <option value="{{ $ag->id }}" {{ request('agency_id') == $ag->id ? 'selected' : '' }}>
                                {{ $ag->agency_name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Submit & Reset -->
                    <div class="flex items-center gap-2">
                        <button type="submit" class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
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

            <!-- Users Table -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-left text-xs">
                        <thead class="bg-gray-50/75 text-gray-500 font-bold uppercase tracking-wider text-[11px]">
                            <tr>
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
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition">
                                    
                                    <!-- User Info -->
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-slate-100 to-indigo-50 border border-slate-200 flex items-center justify-center font-bold text-slate-700 text-xs">
                                                {{ strtoupper(substr($u->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900 text-xs sm:text-sm">{{ $u->name }}</div>
                                                <div class="text-[11px] text-gray-500 font-mono">{{ $u->email }}</div>
                                                @if($u->studentProfile?->nim)
                                                    <div class="text-[10px] text-indigo-600 font-mono">NIM: {{ $u->studentProfile->nim }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Role Badge -->
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        @if($isSuperAdminUser)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-black bg-purple-100 text-purple-800 border border-purple-300">
                                                👑 Super Admin
                                            </span>
                                        @elseif($u->role === 'admin')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                                🏢 Admin Dinas
                                            </span>
                                        @elseif(in_array($u->role, ['mentor', 'pembimbing']))
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-teal-100 text-teal-800 border border-teal-200">
                                                👔 Mentor Dinas
                                            </span>
                                        @elseif(in_array($u->role, ['dosen', 'academic_advisor']))
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                👨‍🏫 Dosen DPL
                                            </span>
                                        @elseif($u->role === 'universitas')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-sky-100 text-sky-800 border border-sky-200">
                                                🏛️ Universitas
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                🎓 Mahasiswa
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Affiliation -->
                                    <td class="py-4 px-4 text-xs text-slate-600">
                                        @if($u->agencyProfile)
                                            <div class="font-semibold text-gray-800">🏢 {{ $u->agencyProfile->agency_name ?? $u->agencyProfile->name ?? 'Dinas Terkait' }}</div>
                                            <div class="text-[10px] text-gray-400">{{ $u->agencyProfile->government_name ?? 'Pemerintah Kota Surabaya' }}</div>
                                        @elseif($u->role === 'mahasiswa')
                                            <div class="font-semibold text-gray-800">🎓 {{ $u->studentProfile?->university?->name ?? $u->studentProfile?->universitas ?? (is_string($u->university) ? $u->university : $u->university?->name) ?? '-' }}</div>
                                            <div class="text-[10px] text-gray-400">NIM: {{ $u->studentProfile?->nim ?? '-' }}</div>
                                        @elseif(in_array($u->role, ['dosen', 'academic_advisor', 'universitas']))
                                            <div class="font-semibold text-gray-800">🎓 {{ $u->university?->name ?? (is_string($u->university) ? $u->university : null) ?? '-' }}</div>
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
                                        <div class="flex items-center justify-end gap-1.5">

                                            <!-- Tombol Impersonate / Login As (Hanya Super Admin & Bukan Akun Sendiri / Super Admin Lain) -->
                                            @if($isSuperAdmin && $u->id !== $currentUser->id && !$isSuperAdminUser)
                                                <form action="{{ route('admin.impersonate', $u->id) }}" method="POST" onsubmit="return confirm('Masuk sebagai {{ $u->name }}? Anda dapat kembali ke Super Admin kapan saja.');">
                                                    @csrf
                                                    <button type="submit" title="Masuk / Login sebagai pengguna ini" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-gradient-to-r from-amber-500 to-rose-500 hover:from-amber-600 hover:to-rose-600 text-white rounded-lg text-xs font-bold shadow-xs transition active:scale-95 cursor-pointer">
                                                        <span>⚡</span>
                                                        <span>Login As</span>
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- Tombol Edit -->
                                            <a href="{{ route('admin.users.edit', $u->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-lg text-xs font-bold transition">
                                                Edit
                                            </a>

                                            <!-- Reset Password -->
                                            <form action="{{ route('admin.users.reset_password', $u->id) }}" method="POST" onsubmit="return confirm('Reset password akun {{ $u->name }} ke default (password)?');">
                                                @csrf
                                                <button type="submit" title="Reset password ke default: password" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-lg text-xs font-bold transition">
                                                    Reset
                                                </button>
                                            </form>

                                            <!-- Hapus User -->
                                            @if($u->id !== $currentUser->id)
                                                <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ $u->name }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-xs font-bold transition">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif

                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-gray-400">
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
    </div>
</x-app-layout>
