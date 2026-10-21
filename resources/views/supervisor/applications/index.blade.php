<x-app-layout>
    <x-slot name="header">
        <x-page-header 
            title="Persetujuan Pengajuan Magang" 
            description="Tinjau berkas mahasiswa yang telah diverifikasi oleh Admin Dispusip untuk disetujui dan diterbitkan Surat Balasan resminya.">
            <x-slot name="actions">
                <a href="{{ route('supervisor.dashboard') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-bold text-sm border border-slate-200 transition">
                    &larr; Monitoring Bidang
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-50 border-2 border-emerald-300 text-emerald-900 flex items-center justify-between gap-3 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold shrink-0">
                            ✓
                        </div>
                        <div class="text-sm font-bold">{{ session('success') }}</div>
                    </div>
                </div>
            @endif

            {{-- TABS FILTER --}}
            <div class="flex overflow-x-auto pb-2 scrollbar-none gap-2 border-b-2 border-slate-200">
                <a href="{{ route('supervisor.applications.index', ['status' => 'waiting_approval']) }}" 
                   class="whitespace-nowrap px-4 sm:px-5 py-2.5 rounded-2xl text-xs sm:text-sm font-black transition flex items-center gap-2 shrink-0 {{ $statusFilter === 'waiting_approval' ? 'bg-blue-600 text-white shadow-md' : 'bg-white hover:bg-slate-100 text-slate-700 border-2 border-slate-200' }}">
                    <span>Menunggu Persetujuan</span>
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $statusFilter === 'waiting_approval' ? 'bg-white text-blue-700' : 'bg-blue-100 text-blue-800' }}">
                        {{ $waitingCount }}
                    </span>
                </a>

                <a href="{{ route('supervisor.applications.index', ['status' => 'approved']) }}" 
                   class="whitespace-nowrap px-4 sm:px-5 py-2.5 rounded-2xl text-xs sm:text-sm font-black transition flex items-center gap-2 shrink-0 {{ $statusFilter === 'approved' ? 'bg-emerald-600 text-white shadow-md' : 'bg-white hover:bg-slate-100 text-slate-700 border-2 border-slate-200' }}">
                    <span>Telah Disetujui</span>
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $statusFilter === 'approved' ? 'bg-white text-emerald-700' : 'bg-emerald-100 text-emerald-800' }}">
                        {{ $approvedCount }}
                    </span>
                </a>

                <a href="{{ route('supervisor.applications.index', ['status' => 'all']) }}" 
                   class="whitespace-nowrap px-4 sm:px-5 py-2.5 rounded-2xl text-xs sm:text-sm font-black transition flex items-center gap-2 shrink-0 {{ $statusFilter === 'all' ? 'bg-blue-600 text-white shadow-md' : 'bg-white hover:bg-slate-100 text-slate-700 border-2 border-slate-200' }}">
                    <span>Semua Riwayat</span>
                </a>
            </div>

            {{-- FORM PENCARIAN & FILTER BIDANG --}}
            <form method="GET" action="{{ route('supervisor.applications.index') }}" class="bg-white p-4 sm:p-5 rounded-3xl border-2 border-slate-200 shadow-xs flex flex-col md:flex-row gap-3">
                <input type="hidden" name="status" value="{{ $statusFilter }}">

                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Cari nama mahasiswa atau universitas..." 
                           class="w-full h-12 px-4 rounded-xl border-2 border-slate-200 focus:border-blue-600 focus:ring-0 text-sm font-medium text-slate-800">
                </div>

                <div class="w-full md:w-72">
                    <select name="unit_id" class="w-full h-12 px-4 rounded-xl border-2 border-slate-200 focus:border-blue-600 focus:ring-0 text-sm font-semibold text-slate-800">
                        <option value="">-- Semua Bidang Dispusip --</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2 w-full md:w-auto">
                    <button type="submit" class="w-full md:w-auto px-6 h-12 rounded-xl bg-slate-900 hover:bg-black text-white font-bold text-sm shadow-sm transition cursor-pointer">
                        Cari
                    </button>
                    @if(request()->hasAny(['search', 'unit_id']))
                        <a href="{{ route('supervisor.applications.index', ['status' => $statusFilter]) }}" class="w-full md:w-auto px-4 h-12 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm flex items-center justify-center transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            {{-- DAFTAR APLIKASI (DUAL-VIEW: TABEL DESKTOP + KARTU MOBILE ERGONOMIS) --}}
            <div class="bg-white rounded-3xl border-2 border-slate-200 shadow-sm overflow-hidden">
                {{-- 1. Tabel Desktop & Tablet (>= 768px) --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b-2 border-slate-200 bg-slate-100/70 text-slate-700 text-xs font-bold uppercase tracking-wider">
                                <th class="py-4 px-6">Mahasiswa & Kampus</th>
                                <th class="py-4 px-6">Bidang & Mentor Dispusip</th>
                                <th class="py-4 px-4">Periode Magang</th>
                                <th class="py-4 px-4 text-center">Status</th>
                                <th class="py-4 px-6 text-center">Tindakan Atasan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-sm">
                            @forelse($applications as $app)
                                <tr class="hover:bg-slate-50 transition">
                                    {{-- Mahasiswa & Kampus (Tanpa NIM, Univ Teratur Truncate) --}}
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-slate-900 text-sm whitespace-nowrap">
                                            {{ preg_replace('/\s*\(.*?\)\s*/', '', $app->user?->name ?? '-') }}
                                        </div>
                                        @if($app->group_id && $app->group)
                                            <div class="mt-0.5">
                                                <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                    Kelompok ({{ $app->role_in_group === 'leader' ? 'Ketua' : 'Anggota' }})
                                                </span>
                                            </div>
                                        @endif
                                        @php
                                            $univName = $app->user?->studentProfile?->universitas ?? $app->user?->university ?? '-';
                                        @endphp
                                        <div class="text-xs font-semibold text-blue-700 mt-1 truncate max-w-[200px]" title="{{ $univName }}">
                                            {{ $univName }}
                                        </div>
                                    </td>

                                    {{-- Bidang & Mentor Dispusip --}}
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-slate-900">{{ $app->unit?->name ?? 'Belum Ditentukan' }}</div>
                                        <div class="text-xs text-slate-600 mt-1 flex items-center gap-1.5">
                                            <span class="text-slate-400 font-medium">Mentor:</span>
                                            <span class="font-bold text-slate-800">
                                                 {{ $app->placement?->mentor?->name ?? $app->placement?->pembimbing?->name ?? 'Belum Ditugaskan' }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Periode --}}
                                    <td class="py-4 px-4 text-xs font-medium text-slate-700 whitespace-nowrap">
                                        <div>Mulai: <strong>{{ $app->start_date ? \Carbon\Carbon::parse($app->start_date)->isoFormat('D MMM Y') : '-' }}</strong></div>
                                        <div class="mt-0.5">Selesai: <strong>{{ $app->end_date ? \Carbon\Carbon::parse($app->end_date)->isoFormat('D MMM Y') : '-' }}</strong></div>
                                    </td>

                                    {{-- Status (1 Baris Rapi dengan Titik Indikator) --}}
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        @php
                                            $st = $app->status instanceof \App\Enums\ApplicationStatus ? $app->status : \App\Enums\ApplicationStatus::tryFrom($app->status);
                                        @endphp
                                        @if($st)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border whitespace-nowrap {{ $st->badgeColor() }}">
                                                <span class="w-2 h-2 rounded-full shrink-0 {{ $st->dotColor() }}"></span>
                                                <span class="whitespace-nowrap">{{ $st->description('supervisor') }}</span>
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 whitespace-nowrap">{{ $app->status }}</span>
                                        @endif
                                    </td>

                                    {{-- Tindakan Atasan --}}
                                    <td class="py-4 px-6 text-center whitespace-nowrap">
                                        @php
                                            $isWaiting = ($app->status instanceof \App\Enums\ApplicationStatus && $app->status === \App\Enums\ApplicationStatus::WAITING_APPROVAL) || $app->status === 'waiting_approval';
                                        @endphp
                                        <a href="{{ route('supervisor.applications.show', $app->id) }}" 
                                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl {{ $isWaiting ? 'bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold' }} text-xs transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>{{ $isWaiting ? 'Tinjau Berkas' : 'Lihat Detail' }}</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-slate-400 font-semibold">
                                        Tidak ada berkas pengajuan magang yang sesuai dengan filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- 2. Tampilan Kartu Khusus Mobile (< 768px) --}}
                <div class="md:hidden p-3.5 sm:p-4 space-y-3.5">
                    @forelse($applications as $app)
                        @php
                            $isWaiting = ($app->status instanceof \App\Enums\ApplicationStatus && $app->status === \App\Enums\ApplicationStatus::WAITING_APPROVAL) || $app->status === 'waiting_approval';
                            $st = $app->status instanceof \App\Enums\ApplicationStatus ? $app->status : \App\Enums\ApplicationStatus::tryFrom($app->status);
                            $univName = $app->user?->studentProfile?->universitas ?? $app->user?->university ?? '-';
                            $majorName = $app->user?->studentProfile?->jurusan ?? '-';
                        @endphp
                        <div class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-2xs space-y-3 hover:border-blue-300 transition">
                            {{-- Header Kartu: Status Badge & Periode --}}
                            <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-slate-100">
                                @if($st)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border {{ $st->badgeColor() }}">
                                        <span class="w-2 h-2 rounded-full shrink-0 {{ $st->dotColor() }}"></span>
                                        <span>{{ $st->description('supervisor') }}</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">{{ $app->status }}</span>
                                @endif
                                <span class="text-[11px] font-mono text-slate-500">
                                    {{ $app->start_date ? \Carbon\Carbon::parse($app->start_date)->isoFormat('D MMM') : '-' }} - {{ $app->end_date ? \Carbon\Carbon::parse($app->end_date)->isoFormat('D MMM Y') : '-' }}
                                </span>
                            </div>

                            {{-- Nama Mahasiswa & Kampus (Full Width, Tidak Terpotong) --}}
                            <div>
                                <h3 class="font-extrabold text-sm sm:text-base text-slate-900 leading-snug">
                                    {{ preg_replace('/\s*\(.*?\)\s*/', '', $app->user?->name ?? '-') }}
                                </h3>
                                @if($app->group_id && $app->group)
                                    <div class="mt-0.5">
                                        <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            Kelompok: {{ $app->group->name }} ({{ $app->role_in_group === 'leader' ? 'Ketua' : 'Anggota' }})
                                        </span>
                                    </div>
                                @endif
                                <p class="text-xs text-blue-700 font-semibold mt-0.5">
                                    {{ $univName }}
                                    @if($majorName !== '-')
                                        <span class="text-slate-500 font-normal">&bull; {{ $majorName }}</span>
                                    @endif
                                </p>
                            </div>

                            {{-- Bidang & Mentor Mini Box --}}
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs space-y-1">
                                <div class="flex items-start justify-between gap-2">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider shrink-0">Bidang:</span>
                                    <span class="font-bold text-slate-800 text-right text-[11px]">{{ $app->unit?->name ?? 'Belum Ditentukan' }}</span>
                                </div>
                                <div class="flex items-start justify-between gap-2 pt-1 border-t border-slate-200/50">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider shrink-0">Mentor Dispusip:</span>
                                    <span class="font-semibold text-slate-700 text-right text-[11px]">{{ $app->placement?->mentor?->name ?? $app->placement?->pembimbing?->name ?? 'Belum Ditugaskan' }}</span>
                                </div>
                            </div>

                            {{-- Tombol Aksi Touch-Friendly --}}
                            <a href="{{ route('supervisor.applications.show', $app->id) }}"
                               class="w-full min-h-[44px] inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl {{ $isWaiting ? 'bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-xs active:scale-95' : 'bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold' }} text-xs transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>{{ $isWaiting ? 'Tinjau Berkas & Usulan Penempatan' : 'Lihat Detail Pengajuan' }}</span>
                            </a>
                        </div>
                    @empty
                        <div class="py-10 text-center text-slate-400 font-medium text-xs">
                            Tidak ada berkas pengajuan magang yang sesuai dengan filter ini.
                        </div>
                    @endforelse
                </div>

                @if($applications->hasPages())
                    <div class="p-4 border-t border-slate-200 bg-slate-50">
                        {{ $applications->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
