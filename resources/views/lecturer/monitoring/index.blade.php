<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    {{ __('Monitoring Mahasiswa Bimbingan Kampus') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Kampus: <strong>{{ $lecturer->university }}</strong> &bull; Dosen Pembimbing: {{ $lecturer->name }}
                </p>
            </div>

            <a href="{{ route('lecturer.dashboard') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition shadow-xs">
                &larr; Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Filter & Search Bar -->
            <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
                <form method="GET" action="{{ route('lecturer.monitoring.index') }}" class="w-full flex flex-col sm:flex-row items-center justify-between gap-3">
                    <input type="hidden" name="tab" value="{{ $tab ?? 'active' }}">

                    <div class="w-full sm:w-auto flex items-center gap-3">
                        <select name="agency_id" onchange="this.form.submit()" class="text-xs border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-xs">
                            <option value="">-- Semua Dinas Penempatan --</option>
                            @foreach ($agencies as $agency)
                                <option value="{{ $agency->id }}" {{ request('agency_id') == $agency->id ? 'selected' : '' }}>
                                    🏛️ {{ $agency->agency_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full sm:w-72 flex items-center gap-2">
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none text-slate-400" style="padding-left: 0.85rem !important;">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama mahasiswa atau NIM..." 
                                   class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                                   style="padding-left: 2.5rem !important; padding-right: 0.75rem !important; padding-top: 0.55rem !important; padding-bottom: 0.55rem !important;">
                        </div>
                        <button type="submit" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs transition shrink-0">
                            Cari
                        </button>
                    </div>

                </form>
            </div>

            <!-- Tab Switcher Navigation -->
            <div class="flex flex-wrap items-center gap-2 border-b border-gray-200 pb-2">
                <a href="{{ route('lecturer.monitoring.index', array_merge(request()->query(), ['tab' => 'active'])) }}" 
                    class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2 {{ ($tab ?? 'active') === 'active' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
                    <span>⚡ Mahasiswa Aktif</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] {{ ($tab ?? 'active') === 'active' ? 'bg-indigo-700 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $stats['active'] }}</span>
                </a>
                <a href="{{ route('lecturer.monitoring.index', array_merge(request()->query(), ['tab' => 'completed'])) }}" 
                    class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2 {{ ($tab ?? '') === 'completed' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
                    <span>🎓 Arsip Alumni Selesai</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] {{ ($tab ?? '') === 'completed' ? 'bg-indigo-700 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $stats['completed'] }}</span>
                </a>
                <a href="{{ route('lecturer.monitoring.index', array_merge(request()->query(), ['tab' => 'upcoming'])) }}" 
                    class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2 {{ ($tab ?? '') === 'upcoming' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
                    <span>📅 Calon Peserta</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] {{ ($tab ?? '') === 'upcoming' ? 'bg-indigo-700 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $stats['upcoming'] }}</span>
                </a>
                <a href="{{ route('lecturer.monitoring.index', array_merge(request()->query(), ['tab' => 'all'])) }}" 
                    class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2 {{ ($tab ?? '') === 'all' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
                    <span>📁 Semua Bimbingan</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] {{ ($tab ?? '') === 'all' ? 'bg-indigo-700 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $stats['total'] }}</span>
                </a>
            </div>

            <!-- Tabel Monitoring -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/75 border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                <th class="py-3.5 px-4">Mahasiswa</th>
                                <th class="py-3.5 px-4">Dinas & Unit</th>
                                <th class="py-3.5 px-4 text-center">STATUS</th>
                                <th class="py-3.5 px-4 text-center">LOGBOOK</th>
                                <th class="py-3.5 px-4 text-center">Laporan Akhir</th>
                                <th class="py-3.5 px-4 text-center">Nilai DPL</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($placements as $placement)
                                @php
                                    $student = $placement->application->user;
                                    $profile = $student?->studentProfile;
                                    $unit = $placement->application->unit;
                                    $agency = $unit?->agencyProfile ?? $placement->agencyProfile;
                                    $totalLog = $placement->logbooks->count();
                                    $approvedLog = $placement->logbooks->where('lecturer_status', 'approved')->count();
                                    $finalReport = $placement->finalreport;
                                    $hasEval = $placement->evaluation && $placement->evaluation->nilai_akademik > 0;
                                    $lifecycle = $placement->application?->lifecycle_status ?? 'ACCEPTED';
                                @endphp
                                <tr class="hover:bg-slate-50/75 transition-colors">
                                    <td class="py-4 px-4">
                                        <div class="font-bold text-gray-900 leading-snug">{{ $student->name ?? '-' }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5">NIM: {{ $profile->nim ?? '-' }} &bull; {{ $profile->major ?? $profile->jurusan ?? '-' }}</div>
                                    </td>

                                    <td class="py-4 px-4">
                                        <div class="text-xs font-semibold text-gray-800">🏛️ {{ $agency->agency_name ?? '-' }}</div>
                                        <div class="text-[11px] text-gray-500 mt-0.5">{{ $unit->name ?? '-' }}</div>
                                    </td>

                                    <td class="py-4 px-4 text-center">
                                        <span class="px-2.5 py-1 text-[11px] font-bold rounded-full 
                                            {{ $lifecycle === 'ACTIVE' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : '' }}
                                            {{ $lifecycle === 'ACCEPTED' ? 'bg-blue-100 text-blue-800 border border-blue-300' : '' }}
                                            {{ $lifecycle === 'COMPLETED' ? 'bg-purple-100 text-purple-800 border border-purple-300' : '' }}">
                                            {{ $lifecycle }}
                                        </span>
                                    </td>

                                    <td class="py-4 px-4 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-lg border border-indigo-100">
                                            📝 ACC {{ $approvedLog }} / {{ $totalLog }}
                                        </span>
                                    </td>

                                    <td class="py-4 px-4 text-center">
                                        @if ($finalReport && $finalReport->status === 'approved')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-full border border-emerald-200">
                                                ✅ Disetujui
                                            </span>
                                        @elseif ($finalReport)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-full border border-amber-200">
                                                ⏳ Menunggu
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Belum Ada</span>
                                        @endif
                                    </td>

                                    <td class="py-4 px-4 text-center">
                                        @if ($hasEval)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-100 text-emerald-800 text-xs font-black rounded-full border border-emerald-300 shadow-xs">
                                                ⭐ {{ $placement->evaluation->nilai_akademik }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-50 text-amber-700 text-xs font-semibold rounded-md border border-amber-200">
                                                Belum Dinilai
                                            </span>
                                        @endif
                                    </td>

                                    <td class="py-4 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('lecturer.students.show', $placement->id) }}" 
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition shadow-xs border border-gray-200">
                                                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                <span>Detail</span>
                                            </a>
                                            <a href="{{ route('lecturer.evaluations.create', $placement->id) }}" 
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 {{ $hasEval ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'bg-indigo-600 hover:bg-indigo-700 text-white' }} text-xs font-bold rounded-xl transition shadow-xs">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                <span>{{ $hasEval ? 'Edit Nilai' : 'Input Nilai' }}</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-gray-400 text-xs">
                                        Tidak ditemukan data mahasiswa bimbingan pada tab ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
