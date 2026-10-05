<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    {{ __('Monitoring Mahasiswa Bimbingan Kampus') }}
                </h2>
            </div>

            <a href="{{ route('lecturer.dashboard') }}" class="px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 text-xs font-bold rounded-xl transition shadow-xs border border-gray-200 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Filter & Search Bar (satu baris: cari mahasiswa + pilih dinas + tombol) -->
            <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-sm">
                <form method="GET" action="{{ route('lecturer.monitoring.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <input type="hidden" name="tab" value="{{ $tab ?? 'active' }}">

                    <div class="relative flex-1 min-w-0">
                        <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none" style="padding-left: 0.85rem;">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama mahasiswa atau NIM..."
                               class="w-full text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500"
                               style="padding-left: 2.5rem; padding-top: 0.6rem; padding-bottom: 0.6rem;">
                    </div>

                    <select name="agency_id" onchange="this.form.submit()"
                            class="text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500"
                            style="padding-top: 0.6rem; padding-bottom: 0.6rem; min-width: 260px;">
                        <option value="">Semua Dinas</option>
                        @foreach ($agencies as $agency)
                            <option value="{{ $agency->id }}" {{ request('agency_id') == $agency->id ? 'selected' : '' }}>{{ $agency->agency_name }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition shrink-0 cursor-pointer">
                        Cari
                    </button>
                    @if (request()->filled('search') || request()->filled('agency_id'))
                        <a href="{{ route('lecturer.monitoring.index', ['tab' => $tab ?? 'active']) }}" class="text-sm font-semibold text-slate-500 hover:text-slate-800 text-center shrink-0">Reset</a>
                    @endif
                </form>
            </div>

            <!-- Tab Switcher Navigation -->
            <div class="flex flex-wrap items-center gap-2 border-b border-gray-200 pb-2">
                <a href="{{ route('lecturer.monitoring.index', array_merge(request()->query(), ['tab' => 'active'])) }}" 
                    class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2 {{ ($tab ?? 'active') === 'active' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
                    <span> Mahasiswa Aktif</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] {{ ($tab ?? 'active') === 'active' ? 'bg-blue-700 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $stats['active'] }}</span>
                </a>
                <a href="{{ route('lecturer.monitoring.index', array_merge(request()->query(), ['tab' => 'completed'])) }}" 
                    class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2 {{ ($tab ?? '') === 'completed' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
                    <span> Arsip Alumni Selesai</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] {{ ($tab ?? '') === 'completed' ? 'bg-blue-700 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $stats['completed'] }}</span>
                </a>
                <a href="{{ route('lecturer.monitoring.index', array_merge(request()->query(), ['tab' => 'upcoming'])) }}" 
                    class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2 {{ ($tab ?? '') === 'upcoming' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
                    <span> Calon Peserta</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] {{ ($tab ?? '') === 'upcoming' ? 'bg-blue-700 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $stats['upcoming'] }}</span>
                </a>
                <a href="{{ route('lecturer.monitoring.index', array_merge(request()->query(), ['tab' => 'all'])) }}" 
                    class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2 {{ ($tab ?? '') === 'all' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
                    <span> Semua Bimbingan</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] {{ ($tab ?? '') === 'all' ? 'bg-blue-700 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $stats['total'] }}</span>
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
                                    $student = $placement->application?->user;
                                    $profile = $student?->studentProfile;
                                    $unit = $placement->application?->unit;
                                    $agency = $unit?->agencyProfile ?? $placement->agencyProfile;
                                    $totalLog = $placement->logbooks->count();
                                    $approvedLog = $placement->logbooks->where('lecturer_status', 'approved')->count();
                                    $finalReport = $placement->finalreport;
                                    $eval = $placement->evaluation;
                                    $hasEval = ($eval?->nilai_akademik ?? 0) > 0 || ($eval?->nilai_dosen ?? 0) > 0;
                                    $appStatus = $placement->application?->status;
                                    $rawStatus = $appStatus instanceof \App\Enums\ApplicationStatus ? $appStatus->value : strtolower((string)$appStatus);

                                    $univ = $eval?->getUniversity();
                                    if (!$univ && $student) {
                                        if ($student->university_id) {
                                            $univ = \App\Models\University::find($student->university_id);
                                        } else {
                                            $name = $student->university ?? ($profile?->universitas ?? null);
                                            if ($name) {
                                                $univ = \App\Models\University::where('name', 'like', "%{$name}%")->orWhere('code', 'like', "%{$name}%")->first();
                                            }
                                        }
                                    }
                                    $isMentorOnly = $univ && $univ->evaluation_scheme === 'mentor_only';
                                    $nilaiDinas = $eval ? ($eval->nilai_pembimbing ?? 0) : 0;
                                    $nilaiDpl = $eval ? ($eval->nilai_dosen_calculated ?? ($eval->nilai_akademik ?? 0)) : 0;
                                @endphp
                                <tr class="hover:bg-slate-50/75 transition-colors">
                                    <td class="py-4 px-4">
                                        <div class="font-bold text-gray-900 leading-snug">{{ $student->name ?? '-' }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5">NIM: {{ $profile->nim ?? '-' }} &bull; {{ $profile->major ?? $profile->jurusan ?? '-' }}</div>
                                    </td>

                                    <td class="py-4 px-4">
                                        <div class="text-xs font-semibold text-gray-800"> {{ $agency->agency_name ?? '-' }}</div>
                                        <div class="text-[11px] text-gray-500 mt-0.5">{{ $unit->name ?? '-' }}</div>
                                    </td>

                                    <td class="py-4 px-4 text-center">
                                        <x-status-badge :status="$appStatus" />
                                    </td>

                                    <td class="py-4 px-4 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-lg border border-blue-100">
                                             {{ $approvedLog }} / {{ $totalLog }}
                                        </span>
                                    </td>

                                    <td class="py-4 px-4 text-center">
                                        @if ($finalReport)
                                            <x-status-badge type="review" :status="$finalReport->status" />
                                        @else
                                            <span class="text-xs text-gray-400 italic">Belum Ada</span>
                                        @endif
                                    </td>

                                    <td class="py-4 px-4 text-center">
                                        @if ($isMentorOnly)
                                            @if ($nilaiDinas > 0)
                                                <div class="inline-flex flex-col items-center">
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-full border border-emerald-300 shadow-2xs" title="100% Nilai Pembimbing Lapangan Dinas">
                                                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        <span>Sudah Dinilai Dinas ({{ $nilaiDinas }})</span>
                                                    </span>
                                                    <span class="text-[9px] text-slate-400 font-medium mt-0.5">100% Nilai Dinas</span>
                                                </div>
                                            @else
                                                <div class="inline-flex flex-col items-center">
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 text-amber-700 text-xs font-medium rounded-full border border-amber-200">
                                                        <span>Menunggu Nilai Dinas</span>
                                                    </span>
                                                    <span class="text-[9px] text-slate-400 font-medium mt-0.5">100% Nilai Dinas</span>
                                                </div>
                                            @endif
                                        @else
                                            @if ($hasEval)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-100 text-emerald-800 text-xs font-black rounded-full border border-emerald-300 shadow-xs">
                                                     {{ $nilaiDpl }}/100
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-50 text-amber-700 text-xs font-semibold rounded-md border border-amber-200">
                                                    Belum Dinilai
                                                </span>
                                            @endif
                                        @endif
                                    </td>

                                    <td class="py-4 px-4 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center justify-end gap-2 whitespace-nowrap">
                                            <a href="{{ route('lecturer.students.show', $placement->id) }}"
                                               class="inline-flex items-center px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-lg border border-slate-300 transition whitespace-nowrap">
                                                Detail
                                            </a>
                                            @if (!$isMentorOnly)
                                                <a href="{{ route('lecturer.evaluations.create', $placement->id) }}"
                                                   class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition whitespace-nowrap">
                                                    {{ $hasEval ? 'Ubah Nilai' : 'Input Nilai' }}
                                                </a>
                                            @endif
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

                @if(method_exists($placements, 'hasPages') && $placements->hasPages())
                    <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                        {{ $placements->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
