<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200">
                        Double ACC Pengajuan Magang
                    </span>
                    <span class="text-xs text-slate-500 font-medium">Tim Kerja Umum & Kepegawaian Dispusip</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    Persetujuan Pengajuan & Surat Balasan
                </h1>
                <p class="text-sm text-slate-600 mt-1">
                    Tinjau berkas mahasiswa yang telah diverifikasi oleh Admin Dinas untuk disetujui (ACC) dan diterbitkan Surat Balasan resminya.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('supervisor.dashboard') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-bold text-sm border-2 border-slate-200 transition">
                    &larr; Kembali ke Monitoring
                </a>
            </div>
        </div>
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
            <div class="flex flex-wrap gap-2 border-b-2 border-slate-200 pb-3">
                <a href="{{ route('supervisor.applications.index', ['status' => 'waiting_approval']) }}" 
                   class="px-5 py-2.5 rounded-2xl text-sm font-black transition flex items-center gap-2.5 {{ $statusFilter === 'waiting_approval' ? 'bg-purple-600 text-white shadow-md' : 'bg-white hover:bg-slate-100 text-slate-700 border-2 border-slate-200' }}">
                    <span>Menunggu ACC Atasan</span>
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $statusFilter === 'waiting_approval' ? 'bg-white text-purple-700' : 'bg-purple-100 text-purple-800' }}">
                        {{ $waitingCount }}
                    </span>
                </a>

                <a href="{{ route('supervisor.applications.index', ['status' => 'approved']) }}" 
                   class="px-5 py-2.5 rounded-2xl text-sm font-black transition flex items-center gap-2.5 {{ $statusFilter === 'approved' ? 'bg-emerald-600 text-white shadow-md' : 'bg-white hover:bg-slate-100 text-slate-700 border-2 border-slate-200' }}">
                    <span>Telah Disetujui (ACC)</span>
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $statusFilter === 'approved' ? 'bg-white text-emerald-700' : 'bg-emerald-100 text-emerald-800' }}">
                        {{ $approvedCount }}
                    </span>
                </a>

                <a href="{{ route('supervisor.applications.index', ['status' => 'all']) }}" 
                   class="px-5 py-2.5 rounded-2xl text-sm font-black transition flex items-center gap-2 {{ $statusFilter === 'all' ? 'bg-blue-600 text-white shadow-md' : 'bg-white hover:bg-slate-100 text-slate-700 border-2 border-slate-200' }}">
                    <span>Semua Riwayat</span>
                </a>
            </div>

            {{-- FORM PENCARIAN & FILTER BIDANG --}}
            <form method="GET" action="{{ route('supervisor.applications.index') }}" class="bg-white p-4 sm:p-5 rounded-3xl border-2 border-slate-200 shadow-xs flex flex-col md:flex-row gap-3">
                <input type="hidden" name="status" value="{{ $statusFilter }}">

                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Cari nama mahasiswa, NIM, atau universitas..." 
                           class="w-full h-12 px-4 rounded-xl border-2 border-slate-200 focus:border-purple-600 focus:ring-0 text-sm font-medium text-slate-800">
                </div>

                <div class="w-full md:w-72">
                    <select name="unit_id" class="w-full h-12 px-4 rounded-xl border-2 border-slate-200 focus:border-purple-600 focus:ring-0 text-sm font-semibold text-slate-800">
                        <option value="">-- Semua Bidang Dispusip --</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-6 h-12 rounded-xl bg-slate-900 hover:bg-black text-white font-bold text-sm shadow-sm transition">
                        Cari
                    </button>
                    @if(request()->hasAny(['search', 'unit_id']))
                        <a href="{{ route('supervisor.applications.index', ['status' => $statusFilter]) }}" class="px-4 h-12 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm flex items-center justify-center transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            {{-- DAFTAR TABEL APLIKASI (SENIOR-FRIENDLY: TEKS JELAS, BARIS LAPANG, AKSI TEGAS) --}}
            <div class="bg-white rounded-3xl border-2 border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
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
                                    {{-- Mahasiswa & Kampus --}}
                                    <td class="py-4 px-6">
                                        <div class="font-black text-slate-900 text-base">{{ $app->user?->name }}</div>
                                        <div class="text-xs text-slate-600 mt-0.5">
                                            NIM: <span class="font-bold text-slate-700">{{ $app->user?->studentProfile?->nim ?? '-' }}</span>
                                        </div>
                                        <div class="text-xs font-semibold text-blue-700 mt-0.5">
                                            {{ $app->user?->studentProfile?->universitas ?? $app->user?->university ?? '-' }}
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
                                    <td class="py-4 px-4 text-xs font-medium text-slate-700">
                                        <div>Mulai: <strong>{{ $app->start_date ? \Carbon\Carbon::parse($app->start_date)->isoFormat('D MMM Y') : '-' }}</strong></div>
                                        <div class="mt-0.5">Selesai: <strong>{{ $app->end_date ? \Carbon\Carbon::parse($app->end_date)->isoFormat('D MMM Y') : '-' }}</strong></div>
                                    </td>

                                    {{-- Status --}}
                                    <td class="py-4 px-4 text-center">
                                        @php
                                            $st = $app->status instanceof \App\Enums\ApplicationStatus ? $app->status : \App\Enums\ApplicationStatus::tryFrom($app->status);
                                        @endphp
                                        @if($st)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black border {{ $st->badgeColor() }}">
                                                <span class="w-2 h-2 rounded-full {{ $st->dotColor() }}"></span>
                                                {{ $st->description() }}
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">{{ $app->status }}</span>
                                        @endif
                                    </td>

                                    {{-- Tindakan Atasan --}}
                                    <td class="py-4 px-6 text-center">
                                        <a href="{{ route('supervisor.applications.show', $app->id) }}" 
                                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl {{ $app->status === 'waiting_approval' ? 'bg-purple-600 hover:bg-purple-700 text-white font-black shadow-sm' : 'bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold' }} text-xs transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>{{ $app->status === 'waiting_approval' ? 'Tinjau & ACC' : 'Lihat Detail' }}</span>
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

                @if($applications->hasPages())
                    <div class="p-4 border-t border-slate-200 bg-slate-50">
                        {{ $applications->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
