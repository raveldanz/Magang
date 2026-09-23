<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                   
                    <span>Kelola Mentor Lapangan {{ $currentAgency->agency_name ?? ($isSuperAdmin ? 'Seluruh Instansi' : 'Dinas') }}</span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Kelola akun pembimbing teknis dinas yang bertugas membimbing dan mengevaluasi mahasiswa magang
                </p>
            </div>

            <a href="{{ route('admin.mentors.create') }}" 
               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer w-full sm:w-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                <span>Tambah Mentor Baru</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
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

            @if (session('error'))
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-xs flex items-center justify-between text-rose-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- Filter Instansi jika Super Admin -->
            @if($isSuperAdmin)
                <div class="bg-white rounded-2xl border border-gray-100 p-3.5 sm:p-4 shadow-xs">
                    <form method="GET" action="{{ route('admin.mentors.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3">
                        <select name="agency_id" onchange="this.form.submit()" class="w-full sm:w-auto text-xs py-2.5 rounded-xl border-gray-200 shadow-2xs font-semibold focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Semua Instansi Dinas</option>
                            @foreach($agencies as $ag)
                                <option value="{{ $ag->id }}" {{ request('agency_id') == $ag->id ? 'selected' : '' }}>
                                    {{ $ag->agency_name }}
                                </option>
                            @endforeach
                        </select>

                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none text-slate-400" style="padding-left: 1rem !important;">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email mentor..." 
                                   class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                                   style="padding-left: 2.75rem !important; padding-right: 1rem !important; padding-top: 0.65rem !important; padding-bottom: 0.65rem !important;">
                        </div>

                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition cursor-pointer w-full sm:w-auto text-center">
                            Cari
                        </button>
                    </form>
                </div>
            @endif

            <!-- Mentors Presentation Card (Desktop Table + Mobile Card View) -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">

                <!-- 1. Tampilan Tabel Desktop & Tablet (>= 768px) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-left text-xs">
                        <thead class="bg-gray-50/75 text-gray-500 font-bold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="py-3.5 px-4 ">Nama Mentor</th>
                                <th class="py-3.5 px-4 text-center">Email Resmi / Login</th>
                                <th class="py-3.5 px-4 text-center">Instansi Dinas</th>
                                <th class="py-3.5 px-4 text-center">Beban Bimbingan</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($mentors as $m)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-4 font-bold text-gray-900 text-xs sm:text-sm">
                                        {{ $m->name }}
                                    </td>
                                    <td class="py-4 px-4 font-mono text-gray-600">
                                        {{ $m->email }}
                                    </td>
                                    <td class="py-4 px-4 text-gray-700">
                                        {{ $m->agencyProfile?->agency_name ?? '-' }}
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <div class="inline-flex items-center gap-1.5 justify-center flex-wrap">
                                            <span class="px-2 py-0.5 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                {{ $m->active_students_count }} Aktif
                                            </span>
                                            <span class="px-2 py-0.5 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                {{ $m->completed_students_count }} Lulus
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        @if($m->status === 'on_leave')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Cuti</span>
                                        @elseif($m->status === 'inactive')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">Non-Aktif</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-right whitespace-nowrap">
                                        <div class="btn-action-group">
                                            
                                            <!-- Login As -->
                                            @if($isSuperAdmin && $m->id !== auth()->id())
                                                <form action="{{ route('admin.impersonate', $m->id) }}" method="POST" class="btn-action-form">
                                                    @csrf
                                                    <button type="submit" title="Masuk sebagai {{ $m->name }} (Login As)" class="btn-action-login">
                                                        Login As
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- Edit -->
                                            <a href="{{ route('admin.mentors.edit', $m->id) }}" 
                                               class="btn-action-edit">
                                                Edit
                                            </a>

                                            <!-- Reset Password -->
                                            <form action="{{ route('admin.mentors.reset_password', $m->id) }}" method="POST" onsubmit="return confirm('Reset password mentor {{ $m->name }} ke default (password)?');" class="btn-action-form">
                                                @csrf
                                                <button type="submit" class="btn-action-reset">
                                                    Reset
                                                </button>
                                            </form>

                                            <!-- Hapus -->
                                            <button type="button" 
                                                    @click="$dispatch('open-delete-modal', {
                                                        action: '{{ route('admin.mentors.destroy', $m->id) }}',
                                                        title: 'Hapus Mentor Lapangan',
                                                        name: '{{ addslashes($m->name) }}',
                                                        desc: 'NIP: {{ $m->nip ?? '-' }} &bull; Instansi: {{ addslashes($m->agencyProfile->agency_name ?? 'Dinas Terkait') }}'
                                                    })" 
                                                    class="btn-action-delete"
                                                    title="Hapus Mentor">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-gray-400">
                                        <div class="text-4xl mb-2"></div>
                                        <p class="font-bold text-gray-600 text-sm">Belum ada data mentor lapangan terdaftar.</p>
                                        <p class="text-xs text-gray-400 mt-1">Klik tombol <strong>"Tambah Mentor Baru"</strong> untuk mendaftarkan mentor lapangan.</p>
                                        <a href="{{ route('admin.mentors.create') }}" 
                                           class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                                            <span>Tambah Mentor Baru</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- 2. Tampilan Kartu Khusus Mobile (< 768px) -->
                <div class="md:hidden divide-y divide-gray-100">
                    @forelse($mentors as $m)
                        <div class="p-4 space-y-3 hover:bg-slate-50/50 transition">
                            <!-- Baris Atas: Nama, Status -->
                            <div class="flex items-start justify-between gap-2.5">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-teal-50 to-blue-50 border border-slate-200 flex items-center justify-center font-bold text-teal-700 text-xs shrink-0 shadow-2xs">
                                        {{ strtoupper(substr($m->name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-gray-900 text-sm leading-snug break-words">{{ $m->name }}</div>
                                        <div class="text-[11px] text-gray-500 font-mono break-all">{{ $m->email }}</div>
                                    </div>
                                </div>

                                <!-- Status Badge -->
                                <div class="shrink-0">
                                    @if($m->status === 'on_leave')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Cuti</span>
                                    @elseif($m->status === 'inactive')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">Non-Aktif</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Info Box: Instansi & Beban Bimbingan -->
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs space-y-1.5">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Instansi Dinas:</span>
                                    <span class="text-[11px] font-semibold text-slate-700 text-right truncate">{{ $m->agencyProfile?->agency_name ?? '-' }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Beban Bimbingan:</span>
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            {{ $m->active_students_count }} Aktif
                                        </span>
                                        <span class="px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            {{ $m->completed_students_count }} Lulus
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Aksi Mobile (Touch-Friendly) -->
                            <div class="pt-2 border-t border-gray-100 flex flex-wrap items-center gap-2 justify-end">
                                <!-- Login As -->
                                @if($isSuperAdmin && $m->id !== auth()->id())
                                    <form action="{{ route('admin.impersonate', $m->id) }}" method="POST" class="inline-block m-0">
                                        @csrf
                                        <button type="submit" title="Masuk sebagai {{ $m->name }} (Login As)"
                                                class="inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold text-xs rounded-xl shadow-xs transition active:scale-95 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                            <span>Login As</span>
                                        </button>
                                    </form>
                                @endif

                                <!-- Edit -->
                                <a href="{{ route('admin.mentors.edit', $m->id) }}" 
                                   class="inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition active:scale-95">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span>Edit</span>
                                </a>

                                <!-- Reset Password -->
                                <form action="{{ route('admin.mentors.reset_password', $m->id) }}" method="POST" onsubmit="return confirm('Reset password mentor {{ $m->name }} ke default (password)?');" class="inline-block m-0">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold text-xs rounded-xl transition active:scale-95 cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                        <span>Reset</span>
                                    </button>
                                </form>

                                <!-- Hapus -->
                                <button type="button" 
                                        @click="$dispatch('open-delete-modal', {
                                            action: '{{ route('admin.mentors.destroy', $m->id) }}',
                                            title: 'Hapus Mentor Lapangan',
                                            name: '{{ addslashes($m->name) }}',
                                            desc: 'NIP: {{ $m->nip ?? '-' }} &bull; Instansi: {{ addslashes($m->agencyProfile->agency_name ?? 'Dinas Terkait') }}'
                                        })" 
                                        class="inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs rounded-xl transition active:scale-95 cursor-pointer"
                                        title="Hapus Mentor">
                                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Hapus</span>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-gray-400 px-4">
                            <div class="text-4xl mb-2"></div>
                            <p class="font-bold text-gray-600 text-sm">Belum ada data mentor lapangan terdaftar.</p>
                            <p class="text-xs text-gray-400 mt-1">Klik tombol <strong>"Tambah Mentor Baru"</strong> untuk mendaftarkan mentor lapangan.</p>
                            <a href="{{ route('admin.mentors.create') }}" 
                               class="mt-3 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer w-full sm:w-auto">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                                <span>Tambah Mentor Baru</span>
                            </a>
                        </div>
                    @endforelse
                </div>

            </div>

        </div>

    </div>

</x-app-layout>
