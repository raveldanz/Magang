<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Aktivitas & Logbook Magang') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="p-4 bg-green-100 text-green-800 rounded-lg text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 bg-red-100 text-red-800 rounded-lg text-sm font-semibold">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Cek Penempatan --}}
            @if (!isset($placement) || !$placement)
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Anda belum memiliki penempatan magang.</strong><br>
                                Logbook hanya bisa diisi setelah pengajuan magang Anda disetujui (status: ACCEPTED) dan ditempatkan oleh Admin.
                            </p>
                        </div>
                    </div>
                </div>
            @else

                {{-- Card 1: Informasi Penempatan Magang --}}
                <div class="bg-indigo-600 rounded-xl p-6 text-white shadow-lg">
                    <h3 class="text-base font-semibold mb-4 flex items-center gap-2">
                        📑 Informasi Penempatan Magang
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                        <div>
                            <p class="text-indigo-200 text-xs uppercase tracking-wider mb-1">Unit Instansi</p>
                            <p class="font-bold text-lg">{{ $application->unit->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-indigo-200 text-xs uppercase tracking-wider mb-1">Pembimbing Lapangan</p>
                            <p class="font-bold text-lg">{{ $placement->pembimbing->name ?? 'Belum Diplot' }}</p>
                        </div>
                        <div>
                            <p class="text-indigo-200 text-xs uppercase tracking-wider mb-1">Periode Magang</p>
                            <p class="font-bold text-lg">{{ $application->start_date }} s/d {{ $application->end_date }}</p>
                        </div>
                    </div>
                </div>

                {{-- Card 2: 4 Kartu Statistik Logbook --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-indigo-500">
                        <p class="text-xs text-gray-500 font-medium">Total Logbook</p>
                        <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['total'] ?? 0 }}</p>
                    </div>
                    <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-green-500">
                        <p class="text-xs text-gray-500 font-medium">Disetujui</p>
                        <p class="text-3xl font-bold text-green-600 mt-1">{{ $stats['approved'] ?? 0 }}</p>
                    </div>
                    <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-yellow-500">
                        <p class="text-xs text-gray-500 font-medium">Menunggu Review</p>
                        <p class="text-3xl font-bold text-yellow-500 mt-1">{{ $stats['pending'] ?? 0 }}</p>
                    </div>
                    <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-red-500">
                        <p class="text-xs text-gray-500 font-medium">Ditolak</p>
                        <p class="text-3xl font-bold text-red-600 mt-1">{{ $stats['rejected'] ?? 0 }}</p>
                    </div>
                </div>

                {{-- Card 3: Tabel Daftar Logbook Harian --}}
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 flex justify-between items-center border-b border-gray-100">
                        <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            📒 Daftar Logbook Harian
                        </h3>
                        {{-- Tombol Pindah ke Halaman Create --}}
                        <a href="{{ route('student.logbook.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                            + TAMBAH LOGBOOK
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500">
                                    <th class="p-4">No</th>
                                    <th class="p-4">Tanggal</th>
                                    <th class="p-4">Kegiatan</th>
                                    <th class="p-4">Lampiran</th>
                                    <th class="p-4">Status</th>
                                    <th class="p-4">Feedback</th>
                                    <th class="p-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-gray-50">
                                @forelse ($logbooks as $index => $log)
                                    <tr class="hover:bg-gray-50/50">
                                        <td class="p-4 text-gray-500">{{ $index + 1 }}</td>
                                        <td class="p-4 font-semibold text-gray-700 whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($log->date)->format('d M Y') }}
                                        </td>
                                        <td class="p-4 text-gray-700 max-w-xs">
                                            <p class="truncate" title="{{ $log->activity }}">{{ Str::limit($log->activity, 80) }}</p>
                                        </td>
                                        <td class="p-4">
                                            @if ($log->attachment)
                                                <a href="{{ asset('storage/' . $log->attachment) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 underline text-xs font-medium">
                                                    Lihat File
                                                </a>
                                            @else
                                                <span class="text-gray-400 text-xs">-</span>
                                            @endif
                                        </td>
                                        <td class="p-4">
                                            <span class="px-2.5 py-1 text-xs font-bold rounded-md 
                                                {{ strtolower($log->status) === 'approved' ? 'bg-green-100 text-green-700' : '' }}
                                                {{ strtolower($log->status) === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                                {{ strtolower($log->status) === 'rejected' ? 'bg-red-100 text-red-700' : '' }}">
                                                {{ strtoupper($log->status) }}
                                            </span>
                                        </td>
                                        <td class="p-4 text-gray-500 text-xs">
                                            {{ $log->feedback ?? '-' }}
                                        </td>
                                        <td class="p-4">
                                            @if (strtolower($log->status) === 'pending')
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('student.logbook.edit', $log->id) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold text-xs">
                                                        Edit
                                                    </a>
                                                </div>
                                            @else
                                                <span class="text-gray-400 text-xs">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="p-8 text-center text-gray-400">
                                            Belum ada logbook. Klik tombol <strong class="text-gray-700">"+ TAMBAH LOGBOOK"</strong> untuk mulai mencatat.
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