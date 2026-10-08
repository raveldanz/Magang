<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-bold text-xl sm:text-2xl text-slate-900 leading-tight">
                    Portal Dosen Pembimbing
                </h2>
            </div>
            
            <div class="flex items-center">
                <span class="inline-flex items-center px-3 py-1 bg-slate-100 text-slate-700 rounded-full text-xs font-medium">
                   {{ is_string($lecturer->university) ? $lecturer->university : ($lecturer->universityRelation?->name ?? $lecturer->university?->name ?? 'Perguruan Tinggi Mitra') }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5 sm:space-y-6">

            <!-- Flash Alert Messages -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 rounded-2xl text-emerald-900 text-sm font-medium flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- PUSAT TINDAKAN DPL (Clean & Simple Alert Center) -->
            @php
                $hasUrgent = ($actionAlerts['pending_reports']->count() > 0) || 
                             ($actionAlerts['urgent_ending']->count() > 0) || 
                             ($actionAlerts['pending_logbooks_count'] > 0);
            @endphp
            @if ($hasUrgent)
                <div class="bg-white rounded-2xl p-5 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 pb-2">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-slate-900">Smart Action Center</h3>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800">Prioritas</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <!-- Alert 1: Laporan Akhir Pending -->
                        <div class="p-4 rounded-xl bg-slate-50 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between text-xs font-semibold text-slate-700">
                                    <span>Naskah Laporan Akhir</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $actionAlerts['pending_reports']->count() > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-200 text-slate-600' }}">
                                        {{ $actionAlerts['pending_reports']->count() }} Menunggu
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                                    @if ($actionAlerts['pending_reports']->count() > 0)
                                        Naskah laporan akhir menunggu persetujuan dari Anda.
                                    @else
                                        Semua naskah laporan sudah selesai diperiksa.
                                    @endif
                                </p>
                            </div>
                            @if ($actionAlerts['pending_reports']->count() > 0)
                                <a href="{{ route('lecturer.students.show', $actionAlerts['pending_reports']->first()->id) }}" class="mt-4 inline-flex items-center justify-center py-2 px-3 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-lg transition text-center">
                                    Review Laporan ({{ $actionAlerts['pending_reports']->first()->application->user->name ?? 'Mahasiswa' }})
                                </a>
                            @endif
                        </div>

                        <!-- Alert 2: Mahasiswa Mendekati Akhir Magang & Belum Dinilai -->
                        <div class="p-4 rounded-xl bg-slate-50 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between text-xs font-semibold text-slate-700">
                                    <span>Evaluasi Akhir</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $actionAlerts['urgent_ending']->count() > 0 ? 'bg-rose-100 text-rose-800' : 'bg-slate-200 text-slate-600' }}">
                                        {{ $actionAlerts['urgent_ending']->count() }} Belum Dinilai
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                                    @if ($actionAlerts['urgent_ending']->count() > 0)
                                        Mahasiswa mendekati akhir magang. Nilai DPL diperlukan untuk penerbitan sertifikat.
                                    @else
                                        Semua mahasiswa aktif yang mendekati akhir magang sudah dinilai.
                                    @endif
                                </p>
                            </div>
                            @if ($actionAlerts['urgent_ending']->count() > 0)
                                <a href="{{ route('lecturer.evaluations.create', $actionAlerts['urgent_ending']->first()->id) }}" class="mt-4 inline-flex items-center justify-center py-2 px-3 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg transition text-center">
                                    Input Nilai ({{ $actionAlerts['urgent_ending']->first()->application->user->name ?? 'Mahasiswa' }})
                                </a>
                            @endif
                        </div>

                        <!-- Alert 3: Logbook Perlu Review DPL -->
                        <div class="p-4 rounded-xl bg-slate-50 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between text-xs font-semibold text-slate-700">
                                    <span>Logbook Harian</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $actionAlerts['pending_logbooks_count'] > 0 ? 'bg-blue-100 text-blue-800' : 'bg-slate-200 text-slate-600' }}">
                                        {{ $actionAlerts['pending_logbooks_count'] }} Menunggu
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                                    @if ($actionAlerts['pending_logbooks_count'] > 0)
                                        Catatan aktivitas harian mahasiswa menunggu verifikasi Anda.
                                    @else
                                        Seluruh logbook mahasiswa telah selesai ditinjau.
                                    @endif
                                </p>
                            </div>
                            @if ($actionAlerts['pending_logbooks_count'] > 0)
                                <a href="{{ route('lecturer.logbooks.index') }}" class="mt-4 inline-flex items-center justify-center py-2 px-3 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition text-center">
                                    Tinjau Logbook
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
                        
            <!-- 1. STATS METRICS (Clean Minimalist Cards - No Heavy Borders) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                
                <!-- Total Mahasiswa Bimbingan -->
                <div class="bg-white p-5 rounded-2xl shadow-xs flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Total Bimbingan</span>
                        <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ $stats['total_students'] }}</div>
                    </div>
                    <span class="text-xs text-slate-400 mt-2 block">Mahasiswa aktif</span>
                </div>

                <!-- Evaluasi Selesai -->
                <div class="bg-white p-5 rounded-2xl shadow-xs flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Sudah Dinilai</span>
                        <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ $stats['total_evaluated'] }}</div>
                    </div>
                    <span class="text-xs text-slate-400 mt-2 block">Nilai DPL tersimpan</span>
                </div>

                <!-- Laporan Akhir Masuk / Pending -->
                <div class="bg-white p-5 rounded-2xl shadow-xs flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Laporan ACC</span>
                        <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ $stats['total_reports_approved'] }}</div>
                    </div>
                    <span class="text-xs text-slate-400 mt-2 block">Disetujui DPL</span>
                </div>

                <!-- Belum Dinilai -->
                <div class="bg-white p-5 rounded-2xl shadow-xs flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Belum Dinilai</span>
                        <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ $stats['total_pending_eval'] }}</div>
                    </div>
                    <span class="text-xs text-slate-400 mt-2 block">Menunggu penilaian DPL</span>
                </div>

            </div>

            <!-- 2. FILTER & SEARCH (Clean Minimalist Form) -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-xs">
                <form method="GET" action="{{ route('lecturer.dashboard') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3">
                    <div class="relative flex-1 w-full">
                        <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none text-slate-400 pl-3.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama mahasiswa, NIM, atau program studi..." 
                               class="w-full text-xs sm:text-sm bg-slate-50 focus:bg-white border-0 ring-1 ring-slate-200 focus:ring-2 focus:ring-blue-600 rounded-xl pl-9 pr-3.5 py-2.5 transition">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 sm:flex sm:items-center gap-2 w-full sm:w-auto">
                        <select name="report_status" class="py-2.5 px-3.5 text-xs sm:text-sm bg-slate-50 focus:bg-white border-0 ring-1 ring-slate-200 focus:ring-2 focus:ring-blue-600 rounded-xl font-medium w-full sm:w-auto transition">
                            <option value="">Semua Status Laporan</option>
                            <option value="pending" {{ request('report_status') == 'pending' ? 'selected' : '' }}>Menunggu Review (Pending)</option>
                            <option value="revision" {{ request('report_status') == 'revision' ? 'selected' : '' }}>Perlu Revisi</option>
                            <option value="approved" {{ request('report_status') == 'approved' ? 'selected' : '' }}>Disetujui (Approved)</option>
                            <option value="none" {{ request('report_status') == 'none' ? 'selected' : '' }}>Belum Unggah Laporan</option>
                        </select>

                        <select name="agency_id" class="py-2.5 px-3.5 text-xs sm:text-sm bg-slate-50 focus:bg-white border-0 ring-1 ring-slate-200 focus:ring-2 focus:ring-blue-600 rounded-xl font-medium w-full sm:w-auto transition">
                            <option value="">Semua Instansi</option>
                            @foreach($agencies as $ag)
                                <option value="{{ $ag->id }}" {{ request('agency_id') == $ag->id ? 'selected' : '' }}>
                                    {{ $ag->agency_name }}
                                </option>
                            @endforeach
                        </select>

                        <div class="flex items-center gap-2 col-span-1 sm:col-span-2 sm:flex-initial">
                            <button type="submit" class="flex-1 sm:flex-initial px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-xs transition cursor-pointer">
                                Filter
                            </button>

                            @if(request()->hasAny(['search', 'agency_id', 'report_status']))
                                <a href="{{ route('lecturer.dashboard') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl transition text-center">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <!-- 3. DAFTAR MAHASISWA BIMBINGAN DPL (Clean Surface, No Heavy Outer Border) -->
            <div class="bg-white rounded-2xl shadow-xs overflow-hidden">
                <div class="p-4 sm:p-5 flex items-center justify-between border-b border-slate-100">
                    <div>
                        <h3 class="font-bold text-sm sm:text-base text-slate-900">Mahasiswa Bimbingan Magang</h3>
                    </div>
                    <span class="text-xs text-slate-600 font-semibold bg-slate-100 px-3 py-1 rounded-full">{{ $placements->count() }} Mahasiswa</span>
                </div>

                <!-- 1. TAMPILAN MOBILE (< 640px) -->
                <div class="block sm:hidden divide-y divide-slate-100">
                    @forelse($placements as $p)
                        @php
                            $student = $p->application?->user;
                            $profile = $student?->studentProfile;
                            $unit = $p->application?->unit;
                            $agency = $unit?->agencyProfile;
                            $mentor = $p->mentor ?? $p->pembimbing;
                            $eval = $p->evaluation;
                            $hasEval = $eval && (($eval->nilai_akademik ?? 0) > 0 || ($eval->nilai_dosen ?? 0) > 0);
                            $finalReport = $p->finalreport;
                            $logbooksCount = $p->logbooks->count();
                            $nilaiDinas = $eval ? ($eval->nilai_pembimbing ?? 0) : 0;
                        @endphp
                        <div class="p-4 space-y-3 hover:bg-slate-50/60 transition">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                    <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-xs shrink-0">
                                        {{ strtoupper(substr($student->name ?? 'M', 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-sm text-slate-900 leading-snug truncate">{{ $student->name ?? '-' }}</p>
                                        <p class="text-xs text-slate-500 mt-0.5">{{ $profile?->nim ?? '-' }} &bull; {{ $profile?->jurusan ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="shrink-0">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                        {{ $logbooksCount }} Logbook
                                    </span>
                                </div>
                            </div>

                            <div class="bg-slate-50 p-3.5 rounded-xl text-xs space-y-2">
                                <div class="text-slate-600">
                                    <span class="text-slate-500 font-medium">Instansi:</span> 
                                    <span class="font-semibold text-slate-800">{{ $agency->agency_name ?? '-' }} ({{ $unit->name ?? '-' }})</span>
                                </div>
                                <div class="text-slate-600">
                                    <span class="text-slate-500 font-medium">Mentor:</span> 
                                    <span class="font-semibold text-slate-800">{{ $mentor->name ?? 'Belum Ditugaskan' }}</span>
                                    @if($nilaiDinas > 0)
                                        <span class="text-emerald-700 font-semibold ml-1">&bull; Dinas: {{ number_format($nilaiDinas, 1) }}</span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between pt-2 border-t border-slate-200/60 text-xs">
                                    <span class="text-slate-500">Laporan: 
                                        @if(!$finalReport)
                                            <span class="text-slate-400 italic">Belum Unggah</span>
                                        @else
                                            <x-status-badge type="review" :status="$finalReport->status" />
                                        @endif
                                    </span>
                                    <span>
                                        @if($hasEval)
                                            <span class="text-blue-700 font-bold bg-blue-50 px-2.5 py-0.5 rounded-md">Nilai DPL: {{ number_format($eval->nilai_dosen_calculated, 1) }}</span>
                                        @else
                                            <span class="text-amber-700 font-semibold bg-amber-50 px-2.5 py-0.5 rounded-md">Belum Dinilai</span>
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <div>
                                <a href="{{ route('lecturer.students.show', $p->id) }}" 
                                   class="inline-flex items-center justify-center w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-xs text-center min-h-[38px]">
                                    Detail & Nilai
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-slate-400 text-xs">
                            Belum ada mahasiswa bimbingan magang yang ditugaskan kepada Anda.
                        </div>
                    @endforelse
                </div>

                <!-- 2. TAMPILAN DESKTOP (Tabel Bersih & Minimalis) -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                        <thead class="bg-slate-50/75 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="py-3.5 px-4">Mahasiswa</th>
                                <th class="py-3.5 px-4">Instansi & Unit Kerja</th>
                                <th class="py-3.5 px-4">Pembimbing Dinas</th>
                                <th class="py-3.5 px-4 text-center">Logbook</th>
                                <th class="py-3.5 px-4 text-center">Laporan Akhir</th>
                                <th class="py-3.5 px-4 text-center">Evaluasi Nilai</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($placements as $p)
                                @php
                                    $student = $p->application?->user;
                                    $profile = $student?->studentProfile;
                                    $unit = $p->application?->unit;
                                    $agency = $unit?->agencyProfile;
                                    $mentor = $p->mentor ?? $p->pembimbing;
                                    $eval = $p->evaluation;
                                    $hasEval = $eval && (($eval->nilai_akademik ?? 0) > 0 || ($eval->nilai_dosen ?? 0) > 0);
                                    $finalReport = $p->finalreport;
                                    $logbooksCount = $p->logbooks->count();

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
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition">
                                    
                                    <!-- Student Info -->
                                    <td class="py-4 px-4">
                                        <div class="font-bold text-slate-900 text-xs sm:text-sm">{{ $student->name ?? '-' }}</div>
                                        <div class="text-[11px] text-slate-500 font-medium">NIM: {{ $profile?->nim ?? '-' }}</div>
                                        <div class="text-[11px] text-slate-600">{{ $profile?->jurusan ?? '-' }}</div>
                                    </td>

                                    <!-- Placement Location -->
                                    <td class="py-4 px-4">
                                        <div class="font-semibold text-slate-800 text-xs">{{ $agency->agency_name ?? '-' }}</div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">{{ $unit->name ?? '-' }}</div>
                                    </td>

                                    <!-- Mentor Info -->
                                    <td class="py-4 px-4">
                                        <div class="font-medium text-slate-800 text-xs">{{ $mentor->name ?? 'Belum Ditugaskan' }}</div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">
                                            @if($nilaiDinas > 0)
                                                <span class="text-emerald-700 font-semibold">Dinas: {{ number_format($nilaiDinas, 1) }}</span>
                                            @else
                                                <span class="text-slate-400">Belum dinilai</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Logbook Counter -->
                                    <td class="py-4 px-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                            {{ $logbooksCount }} Entri
                                        </span>
                                    </td>

                                    <!-- Final Report Status -->
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        @if(!$finalReport)
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500">
                                                Belum Unggah
                                            </span>
                                        @else
                                            <x-status-badge type="review" :status="$finalReport->status" />
                                        @endif
                                    </td>

                                    <!-- Dual-Status Nilai (Dinas & DPL) & Kumulatif -->
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <div class="flex flex-col items-center gap-1">
                                            @if($isMentorOnly)
                                                @if($nilaiDinas > 0)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-800">
                                                        Dinas: {{ number_format($nilaiDinas, 1) }}
                                                    </span>
                                                @else
                                                    <span class="px-2.5 py-0.5 rounded-md text-[11px] font-medium bg-amber-50 text-amber-700">
                                                        Dinas: Menunggu
                                                    </span>
                                                @endif
                                                <span class="text-[10px] text-slate-400">100% Nilai Dinas</span>
                                            @else
                                                <!-- Dual Indicator: Mentor + DPL -->
                                                <div class="flex items-center gap-1.5">
                                                    @if($nilaiDinas > 0)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-700" title="Nilai Pembimbing Lapangan Dinas">
                                                            Dinas: {{ number_format($nilaiDinas, 1) }}
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium bg-slate-100 text-slate-400" title="Mentor Dinas belum menilai">
                                                            Dinas: -
                                                        </span>
                                                    @endif

                                                    @if($hasEval)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700" title="Nilai Akademik DPL Kampus">
                                                            DPL: {{ number_format($eval->nilai_dosen_calculated, 1) }}
                                                        </span>
                                                    @else
                                                        <a href="{{ route('lecturer.evaluations.create', $p->id) }}" class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 hover:bg-rose-100 transition" title="DPL belum mengisi nilai">
                                                            Nilai
                                                        </a>
                                                    @endif
                                                </div>

                                                <!-- Kumulatif Final -->
                                                @if($eval && $eval->nilai_akhir > 0)
                                                    <span class="text-[11px] font-bold text-slate-900 mt-0.5 block">
                                                        Akhir: <span class="text-blue-700">{{ number_format($eval->nilai_akhir, 1) }}</span> <span class="text-emerald-700 font-bold">({{ $eval->grade_calculated }})</span>
                                                    </span>
                                                @else
                                                    <span class="text-[10px] text-slate-400 italic">Belum Lengkap</span>
                                                @endif
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Action Button (Detail only, zero BAP) -->
                                    <td class="py-4 px-4 text-right whitespace-nowrap">
                                        <a href="{{ route('lecturer.students.show', $p->id) }}" class="px-3.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-bold transition inline-block">
                                            Detail
                                        </a>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400">
                                        Belum ada mahasiswa bimbingan magang yang ditugaskan kepada Anda.
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
