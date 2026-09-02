<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    {{ __('Rekapitulasi Logbook Mahasiswa Magang') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Pantau progres pengisian logbook harian seluruh mahasiswa magang per unit kerja secara agregat
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Success Message -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-lg shadow-sm flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Summary Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-500 uppercase">Mahasiswa Aktif</span>
                        <span class="p-2 bg-blue-50 text-blue-600 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </span>
                    </div>
                    <div class="text-2xl font-black text-gray-900 mt-2">{{ $placements->total() }}</div>
                    <div class="text-[11px] text-gray-400 mt-0.5">Total mahasiswa terdaftar</div>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-500 uppercase">Total Logbook</span>
                        <span class="p-2 bg-blue-50 text-blue-600 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </span>
                    </div>
                    <div class="text-2xl font-black text-gray-900 mt-2">{{ $totalLogs }}</div>
                    <div class="text-[11px] text-gray-400 mt-0.5">Kegiatan dilaporkan</div>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-600 uppercase">Disetujui (Approved)</span>
                        <span class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                    </div>
                    <div class="text-2xl font-black text-emerald-700 mt-2">{{ $approvedLogs }}</div>
                    <div class="text-[11px] text-gray-400 mt-0.5">Diverifikasi pembimbing</div>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-amber-600 uppercase">Menunggu Review</span>
                        <span class="p-2 bg-amber-50 text-amber-600 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </div>
                    <div class="text-2xl font-black text-amber-700 mt-2">{{ $pendingLogs }}</div>
                    <div class="text-[11px] text-gray-400 mt-0.5">Perlu approval mentor</div>
                </div>
            </div>

            <!-- Filter & Search Controls -->
            <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-sm">
                <form method="GET" action="{{ route('admin.logbooks.index') }}" class="flex flex-col md:flex-row items-center justify-between gap-3">
                    
                    <div class="w-full md:w-auto flex flex-wrap items-center gap-3">
                        <!-- Filter Unit -->
                        <select name="unit_id" onchange="this.form.submit()" class="text-xs border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Semua Unit Penempatan --</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                     {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Filter Status Logbook -->
                        <select name="status_filter" onchange="this.form.submit()" class="text-xs border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Semua Status Logbook --</option>
                            <option value="pending" {{ request('status_filter') === 'pending' ? 'selected' : '' }}> Memiliki Logbook Pending</option>
                            <option value="approved" {{ request('status_filter') === 'approved' ? 'selected' : '' }}> Memiliki Logbook Approved</option>
                            <option value="empty" {{ request('status_filter') === 'empty' ? 'selected' : '' }}> Belum Mengisi Logbook</option>
                        </select>
                    </div>

                    <!-- Search Input -->
                    <div class="w-full md:w-80 flex items-center gap-2">
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none text-slate-400" style="padding-left: 0.85rem !important;">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama mahasiswa / NIM..." 
                                   class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                                   style="padding-left: 2.5rem !important; padding-right: 0.75rem !important; padding-top: 0.55rem !important; padding-bottom: 0.55rem !important;">
                        </div>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition cursor-pointer">
                            Cari
                        </button>
                        @if (request()->hasAny(['unit_id', 'status_filter', 'search', 'placement_id']))
                            <a href="{{ route('admin.logbooks.index') }}" class="p-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition" title="Reset Filter">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        @endif
                    </div>

                </form>
            </div>

            <!-- Panel Riwayat Logbook Spesifik Mahasiswa (Jika Dipilih) -->
            @if ($selectedPlacement)
                @php
                    $selStudent = $selectedPlacement->application->user;
                    $selProfile = $selStudent->studentProfile;
                    $selUnit = $selectedPlacement->application->unit;
                    $selMentor = $selectedPlacement->mentor ?? $selectedPlacement->pembimbing;
                @endphp
                <div class="bg-blue-50/50 border-2 border-blue-200 rounded-2xl p-6 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-blue-100 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-600 text-white font-bold rounded-xl flex items-center justify-center text-sm shadow-sm">
                                {{ strtoupper(substr($selStudent->name, 0, 2)) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
                                    Riwayat Logbook: {{ $selStudent->name }}
                                    <span class="text-xs font-mono font-normal bg-blue-100 text-blue-800 px-2 py-0.5 rounded-md">
                                        NIM: {{ $selProfile->nim ?? '-' }}
                                    </span>
                                </h3>
                                <p class="text-xs text-gray-600 mt-0.5">
                                    Unit: <strong>{{ $selUnit->name ?? '-' }}</strong> &bull; Pembimbing Lapangan: <strong>{{ $selMentor->name ?? 'Belum Ditentukan' }}</strong>
                                </p>
                            </div>
                        </div>

                        <a href="{{ route('admin.logbooks.index', request()->except('placement_id')) }}" class="inline-flex items-center gap-1 text-xs font-bold text-gray-600 bg-white hover:bg-gray-100 px-3 py-1.5 rounded-xl border border-gray-200 shadow-sm transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Tutup Riwayat
                        </a>
                    </div>

                    <!-- List Logbook Entries Mahasiswa -->
                    <div class="space-y-3">
                        @forelse ($selectedPlacement->logbooks as $log)
                            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs flex flex-col md:flex-row justify-between items-start md:items-center gap-3 hover:border-blue-300 transition">
                                <div class="space-y-1.5 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold font-mono text-gray-800 bg-gray-100 px-2.5 py-1 rounded-md">
                                             {{ \Carbon\Carbon::parse($log->date)->translatedFormat('l, d F Y') }}
                                        </span>
                                        <span class="px-2.5 py-0.5 text-xs font-bold rounded-full
                                            {{ $log->status === 'approved' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : '' }}
                                            {{ $log->status === 'pending' ? 'bg-amber-100 text-amber-800 border border-amber-200' : '' }}
                                            {{ $log->status === 'rejected' ? 'bg-rose-100 text-rose-800 border border-rose-200' : '' }}">
                                            {{ strtoupper($log->status) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-700 leading-relaxed line-clamp-2">
                                        {{ $log->activity }}
                                    </p>
                                    @if ($log->feedback)
                                        <p class="text-[11px] text-blue-900 bg-blue-50/75 px-2.5 py-1 rounded-md">
                                            <strong>Feedback Mentor:</strong> "{{ $log->feedback }}"
                                        </p>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    @if ($log->attachment)
                                        <a href="{{ asset('storage/' . $log->attachment) }}" target="_blank" class="px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition" title="Unduh Lampiran">
                                             Lampiran
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.logbooks.show', $log->id) }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                                        Detail &rarr;
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 bg-white rounded-xl text-center text-gray-400 text-xs">
                                Mahasiswa belum mengunggah entri logbook kegiatan.
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif

            <!-- Tabel Rekapitulasi Agregat per Mahasiswa -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/75 border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                <th class="py-3.5 px-4 text-center w-12">No</th>
                                <th class="py-3.5 px-4">Mahasiswa</th>
                                <th class="py-3.5 px-4">Unit & Pembimbing</th>
                                <th class="py-3.5 px-4 text-center">Rekap Logbook</th>
                                <th class="py-3.5 px-4 text-center">Progres Disetujui</th>
                                <th class="py-3.5 px-4 text-center">Logbook Terakhir</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($placements as $index => $placement)
                                @php
                                    $student = $placement->application->user;
                                    $profile = $student->studentProfile;
                                    $unit = $placement->application->unit;
                                    $mentor = $placement->mentor ?? $placement->pembimbing;
                                    $totalStudentLogs = $placement->logbooks->count();
                                    $approvedCount = $placement->logbooks->where('status', 'approved')->count();
                                    $pendingCount = $placement->logbooks->where('status', 'pending')->count();
                                    $rejectedCount = $placement->logbooks->where('status', 'rejected')->count();
                                    $progressPercent = $totalStudentLogs > 0 ? round(($approvedCount / $totalStudentLogs) * 100) : 0;
                                    $latestLog = $placement->logbooks->first();
                                    $isSelected = $selectedPlacement && $selectedPlacement->id === $placement->id;
                                @endphp
                                <tr class="hover:bg-slate-50/75 transition-colors {{ $isSelected ? 'bg-blue-50/40 font-semibold' : '' }}">
                                    <td class="py-4 px-4 text-center text-xs text-gray-500">
                                        {{ $placements->firstItem() + $index }}
                                    </td>

                                    <td class="py-4 px-4">
                                        <div class="font-bold text-gray-900 leading-snug">{{ $student->name }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5 font-mono">
                                            NIM: {{ $profile->nim ?? '-' }}
                                        </div>
                                        <div class="text-[11px] text-blue-600 mt-0.5">
                                            {{ $profile->universitas ?? '-' }} ({{ $profile->jurusan ?? '-' }})
                                        </div>
                                    </td>

                                    <td class="py-4 px-4">
                                        <div class="text-xs font-bold text-gray-800"> {{ $unit->name ?? '-' }}</div>
                                        <div class="text-[11px] text-gray-500 mt-0.5">
                                            Mentor: <strong>{{ $mentor->name ?? 'Belum Ditentukan' }}</strong>
                                        </div>
                                    </td>

                                    <td class="py-4 px-4 text-center">
                                        <div class="inline-flex flex-wrap items-center justify-center gap-1.5">
                                            <span class="px-2 py-0.5 bg-blue-50 text-blue-700 text-[11px] font-bold rounded-md" title="Total Kegiatan">
                                                 {{ $totalStudentLogs }} Total
                                            </span>
                                            @if ($approvedCount > 0)
                                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 text-[11px] font-bold rounded-md" title="Approved">
                                                     {{ $approvedCount }}
                                                </span>
                                            @endif
                                            @if ($pendingCount > 0)
                                                <span class="px-2 py-0.5 bg-amber-50 text-amber-700 text-[11px] font-bold rounded-md" title="Pending">
                                                     {{ $pendingCount }}
                                                </span>
                                            @endif
                                            @if ($rejectedCount > 0)
                                                <span class="px-2 py-0.5 bg-rose-50 text-rose-700 text-[11px] font-bold rounded-md" title="Rejected">
                                                     {{ $rejectedCount }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="py-4 px-4 text-center">
                                        <div class="w-32 mx-auto">
                                            <div class="flex justify-between text-[10px] text-gray-500 font-bold mb-1">
                                                <span>{{ $approvedCount }}/{{ $totalStudentLogs }}</span>
                                                <span>{{ $progressPercent }}%</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                                <div class="bg-emerald-500 h-2 rounded-full transition-all duration-300" style="width: {{ $progressPercent }}%"></div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="py-4 px-4 text-center whitespace-nowrap text-xs text-gray-600">
                                        @if ($latestLog)
                                            <span class="font-mono">{{ \Carbon\Carbon::parse($latestLog->date)->format('d M Y') }}</span>
                                        @else
                                            <span class="text-gray-400 italic text-[11px]">Belum Ada</span>
                                        @endif
                                    </td>

                                    <td class="py-4 px-4 text-right whitespace-nowrap">
                                        <a href="{{ route('admin.logbooks.index', array_merge(request()->query(), ['placement_id' => $placement->id])) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 {{ $isSelected ? 'bg-blue-700 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' }} text-xs font-bold rounded-xl transition shadow-xs">
                                            <span>Lihat Riwayat</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-gray-400 text-xs">
                                        Tidak ada data mahasiswa magang yang sesuai dengan kriteria pencarian.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($placements->hasPages())
                    <div class="p-4 border-t border-gray-100">
                        {{ $placements->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
