<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Aktivitas & Logbook Magang') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm font-semibold">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Cek Penempatan --}}
            @if (!isset($placement) || !$placement)
                <div class="bg-amber-50 border-l-4 border-amber-400 p-6 rounded-xl shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-amber-700">
                                <strong>Kamu belum punya penempatan magang.</strong><br>
                                Logbook hanya bisa diisi setelah pengajuan magang disetujui (status: ACCEPTED) dan ditempatkan oleh Admin.
                            </p>
                        </div>
                    </div>
                </div>
            @else

                {{-- Card: Informasi Penempatan Magang --}}
                <div class="bg-gradient-to-r from-slate-700 to-slate-900 rounded-2xl p-6 text-white shadow-lg">
                    <h3 class="text-base font-semibold mb-4 flex items-center gap-2">
                        Informasi Penempatan Magang
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                        <div>
                            <p class="text-slate-300 text-xs uppercase tracking-wider mb-1">Unit Instansi</p>
                            <p class="font-bold text-lg">{{ $application->unit->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-slate-300 text-xs uppercase tracking-wider mb-1">Pembimbing Lapangan</p>
                            <p class="font-bold text-lg">{{ $placement->pembimbing->name ?? 'Belum Diplot' }}</p>
                        </div>
                        <div>
                            <p class="text-slate-300 text-xs uppercase tracking-wider mb-1">Periode Magang</p>
                            <p class="font-bold text-lg">{{ $application->start_date }} s/d {{ $application->end_date }}</p>
                        </div>
                    </div>
                </div>

                {{-- 4 Kartu Statistik Logbook --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-[0_4px_12px_rgba(100,116,139,0.08)] hover:shadow-md transition-all">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Logbook</p>
                        <p class="text-3xl font-bold text-slate-800 mt-2">{{ $stats['total'] ?? 0 }}</p>
                    </div>
                    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-[0_4px_12px_rgba(100,116,139,0.08)] hover:shadow-md transition-all">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Disetujui</p>
                        <p class="text-3xl font-bold text-emerald-600 mt-2">{{ $stats['approved'] ?? 0 }}</p>
                    </div>
                    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-[0_4px_12px_rgba(100,116,139,0.08)] hover:shadow-md transition-all">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Menunggu Review</p>
                        <p class="text-3xl font-bold text-amber-500 mt-2">{{ $stats['pending'] ?? 0 }}</p>
                    </div>
                    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-[0_4px_12px_rgba(100,116,139,0.08)] hover:shadow-md transition-all">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Ditolak</p>
                        <p class="text-3xl font-bold text-red-600 mt-2">{{ $stats['rejected'] ?? 0 }}</p>
                    </div>
                </div>

                {{-- Tabel Daftar Logbook Harian --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-[0_4px_12px_rgba(100,116,139,0.08)] overflow-hidden">
                    <div class="p-5 flex justify-between items-center border-b border-slate-100">
                        <h3 class="text-base font-bold text-slate-800">
                            Daftar Logbook Harian
                        </h3>
                        <a href="{{ route('student.logbook.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-800 rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-slate-900 transition-colors">
                            + Tambah Logbook
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50">
                                    <th class="p-4 text-xs font-semibold text-slate-400 uppercase">No</th>
                                    <th class="p-4 text-xs font-semibold text-slate-400 uppercase">Tanggal</th>
                                    <th class="p-4 text-xs font-semibold text-slate-400 uppercase">Kegiatan</th>
                                    <th class="p-4 text-xs font-semibold text-slate-400 uppercase">Lampiran</th>
                                    <th class="p-4 text-xs font-semibold text-slate-400 uppercase">Status</th>
                                    <th class="p-4 text-xs font-semibold text-slate-400 uppercase">Feedback</th>
                                    <th class="p-4 text-xs font-semibold text-slate-400 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-slate-50">
                                @forelse ($logbooks as $index => $log)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="p-4 text-slate-500">{{ $index + 1 }}</td>
                                        <td class="p-4 font-semibold text-slate-700 whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($log->date)->format('d M Y') }}
                                        </td>
                                        <td class="p-4 text-slate-700 max-w-xs">
                                            <p class="truncate" title="{{ $log->activity }}">{{ Str::limit($log->activity, 80) }}</p>
                                        </td>
                                        <td class="p-4">
                                            @if ($log->attachment)
                                                <a href="{{ asset('storage/' . $log->attachment) }}" target="_blank" class="text-slate-700 hover:text-slate-900 underline text-xs font-medium">
                                                    Lihat File
                                                </a>
                                            @else
                                                <span class="text-slate-400 text-xs">-</span>
                                            @endif
                                        </td>
                                        <td class="p-4">
                                            <span class="px-2.5 py-1 text-xs font-bold rounded-full
                                                {{ strtolower($log->status) === 'approved' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                                {{ strtolower($log->status) === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                                                {{ strtolower($log->status) === 'rejected' ? 'bg-red-100 text-red-700' : '' }}">
                                                {{ strtoupper($log->status) }}
                                            </span>
                                        </td>
                                        <td class="p-4 text-slate-500 text-xs">
                                            {{ $log->feedback ?? '-' }}
                                        </td>
                                        <td class="p-4">
                                            @if (strtolower($log->status) === 'pending')
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('student.logbook.edit', $log->id) }}" class="text-slate-700 hover:text-slate-900 font-semibold text-xs">
                                                        Edit
                                                    </a>
                                                </div>
                                            @else
                                                <span class="text-slate-300 text-xs">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="p-8 text-center text-slate-400">
                                            Belum ada logbook. Klik tombol <strong class="text-slate-700">"+ Tambah Logbook"</strong> untuk mulai mencatat.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            @endif

        </div>
    </div>
</x-app-layout>
