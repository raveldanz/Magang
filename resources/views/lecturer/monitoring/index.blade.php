<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h2 class="font-bold text-xl sm:text-2xl text-slate-900 leading-tight">
                    Monitoring Mahasiswa Bimbingan
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Pantau logbook harian, status laporan akhir, evaluasi nilai, dan riwayat bimbingan
                </p>
            </div>

            <div class="grid grid-cols-2 sm:flex sm:items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('lecturer.monitoring.export', request()->query()) }}" 
                   class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-xs text-center cursor-pointer" 
                   title="Download data rekapitulasi ke format CSV/Excel">
                    Ekspor CSV
                </a>

                <a href="{{ route('lecturer.dashboard') }}" 
                   class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl transition shadow-xs border border-slate-200 text-center">
                    Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5 sm:space-y-6">

            <!-- Filter & Search Bar -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs">
                <form method="GET" action="{{ route('lecturer.monitoring.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3">
                    <input type="hidden" name="tab" value="{{ $tab ?? 'active' }}">

                    <div class="relative flex-1 min-w-0">
                        <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none pl-3 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama mahasiswa atau NIM..."
                               class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 pl-9 pr-3 py-2 shadow-2xs">
                    </div>

                    <select name="agency_id" onchange="this.form.submit()"
                            class="text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 py-2 px-3 shadow-2xs w-full sm:w-64">
                        <option value="">Semua Instansi</option>
                        @foreach ($agencies as $agency)
                            <option value="{{ $agency->id }}" {{ request('agency_id') == $agency->id ? 'selected' : '' }}>{{ $agency->agency_name }}</option>
                        @endforeach
                    </select>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="flex-1 sm:flex-initial px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-xl transition cursor-pointer">
                            Cari
                        </button>
                        @if (request()->filled('search') || request()->filled('agency_id'))
                            <a href="{{ route('lecturer.monitoring.index', ['tab' => $tab ?? 'active']) }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl transition text-center">Reset</a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Tab Switcher Navigation (Mobile Horizontal Swipeable) -->
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1 text-nowrap border-b border-slate-200">
                <a href="{{ route('lecturer.monitoring.index', array_merge(request()->query(), ['tab' => 'active'])) }}" 
                    class="px-3.5 py-2 text-xs font-bold rounded-xl transition flex items-center gap-1.5 shrink-0 {{ ($tab ?? 'active') === 'active' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                    <span>Mahasiswa Aktif</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] {{ ($tab ?? 'active') === 'active' ? 'bg-blue-700 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $stats['active'] }}</span>
                </a>
                <a href="{{ route('lecturer.monitoring.index', array_merge(request()->query(), ['tab' => 'completed'])) }}" 
                    class="px-3.5 py-2 text-xs font-bold rounded-xl transition flex items-center gap-1.5 shrink-0 {{ ($tab ?? '') === 'completed' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                    <span>Arsip Selesai</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] {{ ($tab ?? '') === 'completed' ? 'bg-blue-700 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $stats['completed'] }}</span>
                </a>
                <a href="{{ route('lecturer.monitoring.index', array_merge(request()->query(), ['tab' => 'upcoming'])) }}" 
                    class="px-3.5 py-2 text-xs font-bold rounded-xl transition flex items-center gap-1.5 shrink-0 {{ ($tab ?? '') === 'upcoming' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                    <span>Calon Peserta</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] {{ ($tab ?? '') === 'upcoming' ? 'bg-blue-700 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $stats['upcoming'] }}</span>
                </a>
                <a href="{{ route('lecturer.monitoring.index', array_merge(request()->query(), ['tab' => 'all'])) }}" 
                    class="px-3.5 py-2 text-xs font-bold rounded-xl transition flex items-center gap-1.5 shrink-0 {{ ($tab ?? '') === 'all' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                    <span>Semua Bimbingan</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] {{ ($tab ?? '') === 'all' ? 'bg-blue-700 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $stats['total'] }}</span>
                </a>
            </div>

            <!-- Daftar Mahasiswa Monitoring -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                
                <!-- 1. TAMPILAN MOBILE (< 640px) -->
                <div class="block sm:hidden divide-y divide-slate-100">
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
                        <div class="p-4 space-y-3 hover:bg-slate-50/60 transition">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-sm text-slate-900 leading-snug truncate">{{ $student->name ?? '-' }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">NIM: {{ $profile->nim ?? '-' }} &bull; {{ $profile->major ?? $profile->jurusan ?? '-' }}</p>
                                </div>
                                <div class="shrink-0">
                                    <x-status-badge :status="$appStatus" />
                                </div>
                            </div>

                            <div class="bg-slate-50/80 p-3 rounded-xl border border-slate-100 text-xs space-y-1.5">
                                <div class="text-slate-600">
                                    <span class="text-slate-500 font-medium">Instansi:</span>
                                    <span class="font-semibold text-slate-800">{{ $agency->agency_name ?? '-' }} ({{ $unit->name ?? '-' }})</span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-500">Logbook: <strong class="text-slate-800">{{ $approvedLog }} / {{ $totalLog }} Disetujui</strong></span>
                                    <span class="text-slate-500">Laporan: 
                                        @if ($finalReport)
                                            <x-status-badge type="review" :status="$finalReport->status" />
                                        @else
                                            <span class="text-slate-400 italic">Belum Ada</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="pt-1 border-t border-slate-200/60 flex items-center justify-between text-xs">
                                    <span class="text-slate-500 font-medium">Nilai DPL:</span>
                                    @if ($isMentorOnly)
                                        <span class="text-slate-500 text-[11px]">100% Nilai Dinas ({{ $nilaiDinas > 0 ? $nilaiDinas : 'Menunggu' }})</span>
                                    @else
                                        @if ($hasEval)
                                            <span class="text-blue-700 font-bold bg-blue-50 px-2 py-0.5 rounded border border-blue-200">{{ $nilaiDpl }}/100</span>
                                        @else
                                            <span class="text-amber-700 font-semibold bg-amber-50 px-2 py-0.5 rounded border border-amber-200">Belum Dinilai</span>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <div class="grid {{ !$isMentorOnly ? 'grid-cols-3' : 'grid-cols-2' }} gap-2 pt-1">
                                <a href="{{ route('lecturer.students.show', $placement->id) }}" 
                                   class="inline-flex items-center justify-center py-2 px-2 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg text-xs font-bold transition text-center min-h-[38px]">
                                    Detail
                                </a>
                                <a href="{{ route('lecturer.evaluations.grade_sheet', $placement->id) }}" target="_blank"
                                   class="inline-flex items-center justify-center py-2 px-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 rounded-lg text-xs font-semibold transition text-center min-h-[38px]">
                                    BAP
                                </a>
                                @if (!$isMentorOnly)
                                    <a href="{{ route('lecturer.evaluations.create', $placement->id) }}"
                                       class="inline-flex items-center justify-center py-2 px-2 {{ $hasEval ? 'bg-amber-600 hover:bg-amber-700' : 'bg-blue-600 hover:bg-blue-700' }} text-white rounded-lg text-xs font-bold transition text-center min-h-[38px]">
                                        {{ $hasEval ? 'Ubah' : 'Nilai' }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-slate-400 text-xs">
                            Tidak ditemukan data mahasiswa bimbingan pada tab ini.
                        </div>
                    @endforelse
                </div>

                <!-- 2. TAMPILAN DESKTOP (Tabel Bersih) -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/75 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="py-3.5 px-4">Mahasiswa</th>
                                <th class="py-3.5 px-4">Instansi & Unit</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-4 text-center">Logbook</th>
                                <th class="py-3.5 px-4 text-center">Laporan Akhir</th>
                                <th class="py-3.5 px-4 text-center">Nilai DPL</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
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
                                        <div class="font-bold text-slate-900 leading-snug">{{ $student->name ?? '-' }}</div>
                                        <div class="text-xs text-slate-500 mt-0.5">NIM: {{ $profile->nim ?? '-' }} &bull; {{ $profile->major ?? $profile->jurusan ?? '-' }}</div>
                                    </td>

                                    <td class="py-4 px-4">
                                        <div class="text-xs font-semibold text-slate-800">{{ $agency->agency_name ?? '-' }}</div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">{{ $unit->name ?? '-' }}</div>
                                    </td>

                                    <td class="py-4 px-4 text-center">
                                        <x-status-badge :status="$appStatus" />
                                    </td>

                                    <td class="py-4 px-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 bg-slate-100 text-slate-700 text-xs font-semibold rounded-md border border-slate-200">
                                            {{ $approvedLog }} / {{ $totalLog }}
                                        </span>
                                    </td>

                                    <td class="py-4 px-4 text-center">
                                        @if ($finalReport)
                                            <x-status-badge type="review" :status="$finalReport->status" />
                                        @else
                                            <span class="text-xs text-slate-400 italic">Belum Ada</span>
                                        @endif
                                    </td>

                                    <td class="py-4 px-4 text-center">
                                        @if ($isMentorOnly)
                                            @if ($nilaiDinas > 0)
                                                <div class="inline-flex flex-col items-center">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 bg-emerald-50 text-emerald-800 text-xs font-bold rounded border border-emerald-200">
                                                        Dinas: {{ $nilaiDinas }}
                                                    </span>
                                                    <span class="text-[10px] text-slate-400 mt-0.5">100% Nilai Dinas</span>
                                                </div>
                                            @else
                                                <div class="inline-flex flex-col items-center">
                                                    <span class="inline-flex items-center px-2 py-0.5 bg-amber-50 text-amber-700 text-xs font-medium rounded border border-amber-200">
                                                        Menunggu Dinas
                                                    </span>
                                                </div>
                                            @endif
                                        @else
                                            @if ($hasEval)
                                                <span class="inline-flex items-center px-2.5 py-0.5 bg-blue-50 text-blue-700 text-xs font-bold rounded border border-blue-200">
                                                    {{ $nilaiDpl }}/100
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 bg-slate-100 text-slate-500 text-xs font-medium rounded border border-slate-200">
                                                    Belum Dinilai
                                                </span>
                                            @endif
                                        @endif
                                    </td>

                                    <td class="py-4 px-4 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center justify-end gap-1.5 whitespace-nowrap">
                                            <a href="{{ route('lecturer.students.show', $placement->id) }}" 
                                               class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-lg border border-blue-200 transition">
                                                Detail
                                            </a>
                                            <a href="{{ route('lecturer.evaluations.grade_sheet', $placement->id) }}" target="_blank"
                                               class="px-2.5 py-1.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg border border-slate-300 transition" title="Cetak Berita Acara & Lembar Nilai Resmi (BAP)">
                                                BAP
                                            </a>
                                            @if (!$isMentorOnly)
                                                <a href="{{ route('lecturer.evaluations.create', $placement->id) }}" 
                                                   class="px-2.5 py-1.5 {{ $hasEval ? 'bg-amber-600 hover:bg-amber-700' : 'bg-blue-600 hover:bg-blue-700' }} text-white text-xs font-bold rounded-lg transition">
                                                    {{ $hasEval ? 'Ubah Nilai' : 'Input Nilai' }}
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400 text-xs">
                                        Tidak ditemukan data mahasiswa bimbingan pada tab ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(method_exists($placements, 'hasPages') && $placements->hasPages())
                    <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $placements->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
