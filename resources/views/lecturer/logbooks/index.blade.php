<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    {{ __('Rekapitulasi & Feed Logbook Bimbingan') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Monitoring & Verifikasi Aktivitas Harian Mahasiswa Bimbingan &bull; <strong>{{ $user->university ?? 'Perguruan Tinggi' }}</strong>
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('lecturer.monitoring.index') }}" class="px-3.5 py-2 bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 text-xs font-bold rounded-xl transition shadow-xs">
                    Mahasiswa Bimbingan
                </a>
                <a href="{{ route('lecturer.dashboard') }}" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-xs cursor-pointer">
                    &larr; Dashboard Dosen
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Success Message -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Statistik Metrik Logbook -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Total Logbook</span>
                    <div class="text-2xl font-black text-gray-900 mt-1">{{ $totalLogs }}</div>
                    <p class="text-[11px] text-gray-400 mt-0.5">Semua entri aktivitas</p>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-amber-200 shadow-xs bg-amber-50/20">
                    <span class="text-xs font-bold text-amber-700 uppercase tracking-wider block">Menunggu ACC Dosen</span>
                    <div class="text-2xl font-black text-amber-600 mt-1">{{ $pendingDosenLogs }}</div>
                    <p class="text-[11px] text-amber-600/80 mt-0.5">Perlu diverifikasi</p>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-emerald-200 shadow-xs bg-emerald-50/20">
                    <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider block">Disetujui Dosen</span>
                    <div class="text-2xl font-black text-emerald-600 mt-1">{{ $approvedDosenLogs }}</div>
                    <p class="text-[11px] text-emerald-600/80 mt-0.5">ACC terverifikasi</p>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-rose-200 shadow-xs bg-rose-50/20">
                    <span class="text-xs font-bold text-rose-700 uppercase tracking-wider block">Minta Revisi</span>
                    <div class="text-2xl font-black text-rose-600 mt-1">{{ $rejectedDosenLogs }}</div>
                    <p class="text-[11px] text-rose-600/80 mt-0.5">Perlu perbaikan mhs</p>
                </div>
            </div>

            <!-- Filter & Search Bar -->
            <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm">
                <form method="GET" action="{{ route('lecturer.logbooks.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    
                    <!-- Filter Mahasiswa -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Mahasiswa Bimbingan:</label>
                        <select name="placement_id" onchange="this.form.submit()" class="w-full text-xs border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Semua Mahasiswa --</option>
                            @foreach ($supervisedPlacements as $pl)
                                <option value="{{ $pl->id }}" {{ request('placement_id') == $pl->id ? 'selected' : '' }}>
                                    {{ $pl->application->user->name }} ({{ $pl->application->user->studentProfile->nim ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Status Verifikasi Dosen -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Status Verifikasi Dosen:</label>
                        <select name="lecturer_status" onchange="this.form.submit()" class="w-full text-xs border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Semua Status Dosen --</option>
                            <option value="pending" {{ request('lecturer_status') === 'pending' ? 'selected' : '' }}>⏳ Menunggu (Pending)</option>
                            <option value="approved" {{ request('lecturer_status') === 'approved' ? 'selected' : '' }}>✅ Disetujui (Approved)</option>
                            <option value="rejected" {{ request('lecturer_status') === 'rejected' ? 'selected' : '' }}>❌ Minta Revisi (Rejected)</option>
                        </select>
                    </div>

                    <!-- Filter Status Mentor Dinas -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Status Mentor Dinas:</label>
                        <select name="mentor_status" onchange="this.form.submit()" class="w-full text-xs border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Semua Status Mentor --</option>
                            <option value="pending" {{ request('mentor_status') === 'pending' ? 'selected' : '' }}>⏳ Pending Mentor</option>
                            <option value="approved" {{ request('mentor_status') === 'approved' ? 'selected' : '' }}>✅ Approved Mentor</option>
                            <option value="rejected" {{ request('mentor_status') === 'rejected' ? 'selected' : '' }}>❌ Rejected Mentor</option>
                        </select>
                    </div>

                    <!-- Search Box -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Cari Keyword:</label>
                        <div class="flex items-center gap-1.5">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, NIM, kegiatan..." class="w-full text-xs border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-xs">
                            <button type="submit" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-xs shrink-0 cursor-pointer">
                                Cari
                            </button>
                        </div>
                    </div>

                </form>
            </div>

            <!-- Tabel Feed Logbook -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/75 border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                <th class="py-3.5 px-4 w-36">Tanggal</th>
                                <th class="py-3.5 px-4">Mahasiswa</th>
                                <th class="py-3.5 px-4">Instansi & Unit</th>
                                <th class="py-3.5 px-4">Aktivitas Kegiatan</th>
                                <th class="py-3.5 px-4 text-center">Status Mentor</th>
                                <th class="py-3.5 px-4 text-center">Status Dosen</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($logbooks as $log)
                                @php
                                    $mhs = $log->placement->application->user ?? null;
                                    $mProfile = $mhs?->studentProfile;
                                    $unit = $log->placement->application->unit ?? null;
                                    $agency = $unit?->agencyProfile ?? $log->placement->agencyProfile;
                                @endphp
                                <tr class="hover:bg-slate-50/75 transition-colors">
                                    
                                    <!-- Tanggal Logbook -->
                                    <td class="py-4 px-4 align-top">
                                        <div class="font-bold text-gray-900 text-xs">
                                            {{ \Carbon\Carbon::parse($log->date)->translatedFormat('d M Y') }}
                                        </div>
                                        <div class="text-[11px] text-gray-400 mt-0.5 font-mono">
                                            {{ \Carbon\Carbon::parse($log->date)->translatedFormat('l') }}
                                        </div>
                                    </td>

                                    <!-- Mahasiswa -->
                                    <td class="py-4 px-4 align-top">
                                        <div class="font-bold text-gray-900 leading-snug">{{ $mhs->name ?? '-' }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5 font-mono">NIM: {{ $mProfile->nim ?? '-' }}</div>
                                        <div class="text-[11px] text-blue-600 mt-0.5">{{ $mProfile->jurusan ?? '-' }}</div>
                                    </td>

                                    <!-- Instansi & Unit -->
                                    <td class="py-4 px-4 align-top">
                                        <div class="text-xs font-bold text-gray-800">🏛️ {{ $agency->agency_name ?? '-' }}</div>
                                        <div class="text-[11px] text-gray-500 mt-0.5">{{ $unit->name ?? '-' }}</div>
                                    </td>

                                    <!-- Cuplikan Aktivitas & Lampiran -->
                                    <td class="py-4 px-4 align-top">
                                        <p class="text-xs text-gray-700 leading-relaxed line-clamp-2">
                                            {{ $log->activity }}
                                        </p>
                                        @if ($log->attachment)
                                            <div class="mt-1.5 flex items-center gap-1 text-[11px] text-blue-600 font-semibold">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                                </svg>
                                                <span>Ada Lampiran Bukti</span>
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Status Mentor Dinas -->
                                    <td class="py-4 px-4 align-top text-center">
                                        @if ($log->status === 'approved')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-full">
                                                ✅ Disetujui
                                            </span>
                                        @elseif ($log->status === 'rejected')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-rose-100 text-rose-800 text-xs font-bold rounded-full">
                                                ❌ Ditolak
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-full">
                                                ⏳ Pending
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Status Dosen Kampus -->
                                    <td class="py-4 px-4 align-top text-center">
                                        @if ($log->lecturer_status === 'approved')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-full">
                                                ✅ disetujui   
                                            </span>
                                        @elseif ($log->lecturer_status === 'rejected')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-rose-100 text-rose-800 text-xs font-bold rounded-full">
                                                ❌ ditolak  
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-full">
                                                ⏳ Dalam Pengecekan
                                            </span>
                                        @endif

                                        @if ($log->lecturer_feedback)
                                            <div class="text-[10px] text-gray-500 italic mt-1 max-w-[120px] truncate" title="{{ $log->lecturer_feedback }}">
                                                "{{ $log->lecturer_feedback }}"
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Tombol Aksi Review Cepat -->
                                    <td class="py-4 px-4 align-top text-right">
                                        <a href="{{ route('lecturer.logbooks.show', $log->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition shadow-xs cursor-pointer">
                                            <span>Review / ACC</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-gray-400 text-xs">
                                        Tidak ditemukan catatan logbook mahasiswa bimbingan dengan kriteria filter yang dipilih.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($logbooks->hasPages())
                    <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                        {{ $logbooks->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
