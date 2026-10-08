<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                    <span>Portal Mentor Lapangan</span>
                </h2>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="px-3.5 py-1.5 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl text-xs font-bold shadow-2xs">
                   {{ Auth::user()->agencyProfile->agency_name ?? 'Instansi Pemerintah' }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Alert Messages -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif
                        
            <!-- 1. STATS WIDGETS -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Total Mahasiswa Bimbingan -->
                <div class="bg-white p-5 rounded-2xl shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-900 uppercase tracking-wider">Total Bimbingan</span>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-black text-gray-900">{{ $stats['total_students'] }}</span>
                        <span class="text-[11px] text-gray-500 block mt-0.5">Mahasiswa magang aktif</span>
                    </div>
                </div>

                <!-- Evaluasi Selesai -->
                <div class="bg-white p-5 rounded-2xl shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-900 uppercase tracking-wider">Sudah Dinilai</span>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-black text-gray-900">{{ $stats['total_evaluated'] }}</span>
                        <span class="text-[11px] text-gray-500 block mt-0.5">Nilai mentor tersimpan</span>
                    </div>
                </div>

                <!-- Laporan Akhir Masuk / Approved -->
                <div class="bg-white p-5 rounded-2xl shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-900 uppercase tracking-wider">Laporan Akhir</span>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-black text-gray-900">{{ $stats['total_reports_approved'] }}</span>
                        <span class="text-[11px] text-gray-500 block mt-0.5">Telah di-ACC mentor</span>
                    </div>
                </div>

                <!-- Belum Dinilai -->
                <div class="bg-white p-5 rounded-2xl shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-900 uppercase tracking-wider">Belum Dinilai</span>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-black text-gray-900">{{ $stats['total_pending_eval'] }}</span>
                        <span class="text-[11px] text-gray-500 block mt-0.5">Menunggu penilaian mentor</span>
                    </div>
                </div>

            </div>

            <!-- 2. FILTER & SEARCH CARD -->
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs">
                <form method="GET" action="{{ route('mentor.dashboard') }}" class="flex flex-col sm:flex-row items-center gap-3">
                    @if(request('tab'))
                        <input type="hidden" name="tab" value="{{ request('tab') }}">
                    @endif
                    <div class="relative flex-1 w-full">
                        <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none text-slate-400" style="padding-left: 1rem !important;">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search', request('q')) }}" placeholder="Cari nama mahasiswa, NIM, atau program studi..." 
                               class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                               style="padding-left: 2.75rem !important; padding-right: 1rem !important; padding-top: 0.6rem !important; padding-bottom: 0.6rem !important;">
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <select name="report_status" class="py-2 px-3 text-xs border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-medium w-full sm:w-auto">
                            <option value="">Semua Status Laporan</option>
                            <option value="pending" {{ request('report_status') == 'pending' ? 'selected' : '' }}>Menunggu Review (Pending)</option>
                            <option value="revision" {{ request('report_status') == 'revision' ? 'selected' : '' }}>Perlu Revisi</option>
                            <option value="approved" {{ request('report_status') == 'approved' ? 'selected' : '' }}>Disetujui (Approved)</option>
                            <option value="none" {{ request('report_status') == 'none' ? 'selected' : '' }}>Belum Unggah Laporan</option>
                        </select>

                        <select name="university_id" class="py-2 px-3 text-xs border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-medium w-full sm:w-auto">
                            <option value="">Semua Perguruan Tinggi</option>
                            @foreach($universities as $u)
                                <option value="{{ $u->id }}" {{ request('university_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition shrink-0 cursor-pointer">
                            Filter
                        </button>

                        @if(request()->hasAny(['search', 'q', 'university_id', 'report_status']))
                            <a href="{{ route('mentor.dashboard', array_filter(['tab' => request('tab')])) }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold rounded-xl transition shrink-0">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- 3. TABEL MAHASISWA BIMBINGAN MENTOR -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-sm text-gray-900">Daftar Mahasiswa Bimbingan Magang</h3>
                    </div>
                    <span class="text-xs text-gray-400 font-mono">{{ method_exists($placements, 'total') ? $placements->total() : $placements->count() }} Mahasiswa</span>
                </div>

                <!-- 1. TAMPILAN KHUSUS MOBILE (< 640px) -->
                <div class="block sm:hidden divide-y divide-slate-100">
                    @forelse($placements as $p)
                        @php
                            $student = $p->application?->user;
                            $profile = $student?->studentProfile;
                            $unit = $p->application?->unit;
                            $appStatus = $p->application?->status;
                            $dosen = $p->academicAdvisor ?? $p->dosen;
                            $eval = $p->evaluation;
                            $nilaiMentor = $eval ? $eval->nilai_pembimbing : 0;
                            $hasMentorEval = $eval && $nilaiMentor > 0;
                            $nilaiDosen = $eval ? $eval->nilai_dosen_calculated : 0;
                            $finalReport = $p->finalreport;
                            $logbooksCount = $p->logbooks->count();
                            $univName = $student?->universityRelation?->name ?? $student?->university ?? $profile?->universitas ?? '-';
                        @endphp
                        <div class="p-4 space-y-3 hover:bg-slate-50/60 transition">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                    <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-xs shrink-0">
                                        {{ strtoupper(substr($student->name ?? 'M', 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="font-bold text-xs sm:text-sm text-slate-900 leading-snug truncate">{{ $student->name ?? '-' }}</p>
                                            <x-status-badge :status="$appStatus" class="shrink-0" />
                                        </div>
                                        <p class="text-[11px] text-slate-500 font-mono mt-0.5">{{ $profile?->nim ?? '-' }} &bull; {{ $profile?->jurusan ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="shrink-0">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ $logbooksCount }} Entri
                                    </span>
                                </div>
                            </div>

                            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 text-xs space-y-1.5">
                                <div class="text-[11px] text-slate-700 font-medium">
                                    <strong class="text-slate-900 font-bold">Kampus:</strong> {{ $univName }}
                                </div>
                                <div class="text-[11px] text-slate-700 font-medium">
                                    <strong class="text-slate-900 font-bold">Unit Kerja:</strong> {{ $unit->name ?? '-' }}
                                </div>
                                <div class="text-[11px] text-slate-700 font-medium">
                                    <strong class="text-slate-900 font-bold">Dosen Pembimbing:</strong> {{ $dosen->name ?? 'Belum Ditugaskan' }}
                                </div>
                                <div class="flex items-center justify-between text-[11px] pt-1.5 border-t border-slate-200/60">
                                    <span class="text-slate-500">Laporan Akhir:
                                        @if(!$finalReport)
                                            <span class="text-slate-400 italic">Belum Unggah</span>
                                        @else
                                            <x-status-badge type="review" :status="$finalReport->status" />
                                        @endif
                                    </span>
                                    <span class="text-slate-500">Nilai Mentor:
                                        @if($hasMentorEval)
                                            <span class="text-emerald-700 font-bold">Sudah Dinilai ({{ $nilaiMentor }})</span>
                                        @else
                                            <span class="text-amber-700 font-bold">Belum Dinilai</span>
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <div class="pt-1">
                                <a href="{{ route('mentor.students.show', $p->id) }}" 
                                   class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-3 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-xl transition border border-blue-200 shadow-2xs">
                                    <span>Detail & Evaluasi</span>
                                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-gray-400 text-xs">
                            Belum ada mahasiswa bimbingan magang yang ditugaskan kepada Anda.
                        </div>
                    @endforelse
                </div>

                <!-- 2. TAMPILAN KHUSUS DESKTOP (Tabel Lengkap) -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-left text-xs">
                        <thead class="bg-gray-50/75 text-gray-500 font-bold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="py-3.5 px-5 w-3/12">Mahasiswa</th>
                                <th class="py-3.5 px-4 w-3/12">Perguruan Tinggi & Unit</th>
                                <th class="py-3.5 px-4 w-2/12">Dosen Pembimbing</th>
                                <th class="py-3.5 px-3 text-center w-1/12 whitespace-nowrap">Logbook</th>
                                <th class="py-3.5 px-3 text-center w-1/12 whitespace-nowrap">Laporan Akhir</th>
                                <th class="py-3.5 px-4 text-center w-1/12 whitespace-nowrap">Nilai Mentor</th>
                                <th class="py-3.5 px-5 text-right w-1/12 whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($placements as $p)
                                @php
                                    $student = $p->application?->user;
                                    $profile = $student?->studentProfile;
                                    $unit = $p->application?->unit;
                                    $appStatus = $p->application?->status;
                                    $dosen = $p->academicAdvisor ?? $p->dosen;
                                    $eval = $p->evaluation;
                                    $nilaiMentor = $eval ? $eval->nilai_pembimbing : 0;
                                    $hasMentorEval = $eval && $nilaiMentor > 0;
                                    $nilaiDosen = $eval ? $eval->nilai_dosen_calculated : 0;
                                    $finalReport = $p->finalreport;
                                    $logbooksCount = $p->logbooks->count();
                                    $univName = $student?->universityRelation?->name ?? $student?->university ?? $profile?->universitas ?? '-';
                                    $mentorGrade = \App\Support\Grade::fromScore($nilaiMentor);
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition">
                                    
                                    <!-- Mahasiswa Info -->
                                    <td class="py-4 px-5">
                                        <div class="font-bold text-gray-900 text-xs sm:text-sm leading-snug">{{ $student->name ?? '-' }}</div>
                                        <div class="text-[11px] text-gray-500 font-mono mt-0.5">NIM: {{ $profile?->nim ?? '-' }}</div>
                                        <div class="text-[10px] text-blue-600 font-semibold mt-0.5">{{ $profile?->jurusan ?? '-' }}</div>
                                    </td>

                                    <!-- Perguruan Tinggi & Unit -->
                                    <td class="py-4 px-4">
                                        <div class="font-bold text-gray-800 text-xs">{{ $univName }}</div>
                                        <div class="text-[11px] text-gray-500 mt-0.5">{{ $unit->name ?? '-' }}</div>
                                    </td>

                                    <!-- Dosen Pembimbing Info -->
                                    <td class="py-4 px-4">
                                        <div class="font-semibold text-gray-800 text-xs">{{ $dosen->name ?? 'Belum Ditugaskan' }}</div>
                                    </td>

                                    <!-- Logbook Counter -->
                                    <td class="py-4 px-3 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            {{ $logbooksCount }} Entri
                                        </span>
                                    </td>

                                    <!-- Final Report Status -->
                                    <td class="py-4 px-3 text-center whitespace-nowrap">
                                        @if(!$finalReport)
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500">
                                                Belum Unggah
                                            </span>
                                        @else
                                            <x-status-badge type="review" :status="$finalReport->status" />
                                        @endif
                                    </td>

                                    <!-- Nilai Mentor Status -->
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        @if($hasMentorEval)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                {{ $nilaiMentor }}/100
                                                @if($mentorGrade !== '-')
                                                    ({{ $mentorGrade }})
                                                @endif
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                Belum Dinilai
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Action Buttons -->
                                    <td class="py-4 px-5 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('mentor.students.show', $p->id) }}" class="inline-flex items-center gap-1 px-3.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl text-xs font-bold transition cursor-pointer shadow-2xs">
                                                <span>Detail</span>
                                            </a>
                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-gray-400">
                                        Belum ada mahasiswa bimbingan magang yang ditugaskan kepada Anda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if (method_exists($placements, 'hasPages') && $placements->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $placements->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
