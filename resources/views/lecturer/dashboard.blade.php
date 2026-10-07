<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                    <span>Portal Dosen Pembimbing</span>
                </h2>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="px-3.5 py-1.5 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl text-xs font-bold shadow-2xs">
                   {{ is_string($lecturer->university) ? $lecturer->university : ($lecturer->universityRelation?->name ?? $lecturer->university?->name ?? 'Perguruan Tinggi Mitra') }}
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

            <!-- SMART ACTION ALERTS BANNER (Peringatan & Panduan Tindakan DPL) -->
            @php
                $hasUrgent = ($actionAlerts['pending_reports']->count() > 0) || 
                             ($actionAlerts['urgent_ending']->count() > 0) || 
                             ($actionAlerts['pending_logbooks_count'] > 0);
            @endphp
            @if ($hasUrgent)
                <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 text-white rounded-2xl p-5 shadow-sm border border-blue-800/80 space-y-3">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-blue-800/60 pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="p-2 bg-blue-500/20 text-cyan-400 rounded-xl border border-cyan-500/30">
                                <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-white tracking-wide flex items-center gap-2">
                                    <span>Pusat Tindakan & Peringatan Bimbingan (Smart Action Center)</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500/90 text-white uppercase tracking-wider">Perlu Tindakan</span>
                                </h3>
                                <p class="text-xs text-blue-200">Perhatikan mahasiswa bimbingan yang membutuhkan verifikasi atau penilaian segera</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-1">
                        <!-- Alert 1: Laporan Akhir Pending -->
                        <div class="p-3.5 rounded-xl bg-white/5 border border-white/10 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between text-xs font-semibold text-blue-200">
                                    <span>Naskah Laporan Akhir</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $actionAlerts['pending_reports']->count() > 0 ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-emerald-500/20 text-emerald-300' }}">
                                        {{ $actionAlerts['pending_reports']->count() }} Menunggu
                                    </span>
                                </div>
                                <p class="text-xs text-slate-300 mt-1.5 leading-snug">
                                    @if ($actionAlerts['pending_reports']->count() > 0)
                                        Mahasiswa telah mengunggah naskah laporan akhir yang menunggu persetujuan (ACC) dari Anda.
                                    @else
                                        Seluruh naskah laporan mahasiswa telah selesai diperiksa.
                                    @endif
                                </p>
                            </div>
                            @if ($actionAlerts['pending_reports']->count() > 0)
                                <a href="{{ route('lecturer.students.show', $actionAlerts['pending_reports']->first()->id) }}" class="mt-3 inline-flex items-center justify-center gap-1.5 py-1.5 px-3 bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold rounded-lg transition shadow-xs">
                                    <span>Review Laporan ({{ $actionAlerts['pending_reports']->first()->application->user->name ?? 'Mahasiswa' }})</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @endif
                        </div>

                        <!-- Alert 2: Mahasiswa Mendekati Akhir Magang & Belum Dinilai -->
                        <div class="p-3.5 rounded-xl bg-white/5 border border-white/10 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between text-xs font-semibold text-blue-200">
                                    <span>Masa Berakhir / Evaluasi</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $actionAlerts['urgent_ending']->count() > 0 ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : 'bg-emerald-500/20 text-emerald-300' }}">
                                        {{ $actionAlerts['urgent_ending']->count() }} Belum Dinilai
                                    </span>
                                </div>
                                <p class="text-xs text-slate-300 mt-1.5 leading-snug">
                                    @if ($actionAlerts['urgent_ending']->count() > 0)
                                        Mahasiswa aktif mendekati akhir periode magang. Nilai akademik DPL diperlukan untuk penerbitan sertifikat.
                                    @else
                                        Semua mahasiswa aktif yang mendekati akhir magang sudah dinilai.
                                    @endif
                                </p>
                            </div>
                            @if ($actionAlerts['urgent_ending']->count() > 0)
                                <a href="{{ route('lecturer.evaluations.create', $actionAlerts['urgent_ending']->first()->id) }}" class="mt-3 inline-flex items-center justify-center gap-1.5 py-1.5 px-3 bg-rose-500 hover:bg-rose-400 text-white text-xs font-bold rounded-lg transition shadow-xs">
                                    <span>Input Nilai ({{ $actionAlerts['urgent_ending']->first()->application->user->name ?? 'Mahasiswa' }})</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @endif
                        </div>

                        <!-- Alert 3: Logbook Perlu Review DPL -->
                        <div class="p-3.5 rounded-xl bg-white/5 border border-white/10 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between text-xs font-semibold text-blue-200">
                                    <span>Logbook Harian</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $actionAlerts['pending_logbooks_count'] > 0 ? 'bg-blue-500/20 text-blue-300 border border-blue-500/30' : 'bg-emerald-500/20 text-emerald-300' }}">
                                        {{ $actionAlerts['pending_logbooks_count'] }} Menunggu
                                    </span>
                                </div>
                                <p class="text-xs text-slate-300 mt-1.5 leading-snug">
                                    @if ($actionAlerts['pending_logbooks_count'] > 0)
                                        Terdapat catatan aktivitas harian mahasiswa bimbingan yang menunggu validasi akademik Anda.
                                    @else
                                        Seluruh logbook mahasiswa telah selesai ditinjau.
                                    @endif
                                </p>
                            </div>
                            @if ($actionAlerts['pending_logbooks_count'] > 0)
                                <a href="{{ route('lecturer.logbooks.index') }}" class="mt-3 inline-flex items-center justify-center gap-1.5 py-1.5 px-3 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-lg transition shadow-xs">
                                    <span>Tinjau Feed Logbook</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @endif
                        </div>
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
                        <span class="text-[11px] text-gray-500 block mt-0.5">Nilai DPL tersimpan</span>
                    </div>
                </div>

                <!-- Laporan Akhir Masuk / Pending -->
                <div class="bg-white p-5 rounded-2xl shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-900 uppercase tracking-wider">Laporan Akhir</span>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-black text-gray-900">{{ $stats['total_reports_approved'] }}</span>
                        <span class="text-[11px] text-gray-500 block mt-0.5">Telah di-ACC DPL</span>
                    </div>
                </div>

                <!-- Belum Dinilai -->
                <div class="bg-white p-5 rounded-2xl shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-900 uppercase tracking-wider">Belum Dinilai</span>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-black text-gray-900">{{ $stats['total_pending_eval'] }}</span>
                        <span class="text-[11px] text-gray-500 block mt-0.5">Menunggu penilaian DPL</span>
                    </div>
                </div>

            </div>

            <!-- 2. FILTER & SEARCH CARD -->
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs">
                <form method="GET" action="{{ route('lecturer.dashboard') }}" class="flex flex-col sm:flex-row items-center gap-3">
                    <div class="relative flex-1 w-full">
                        <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none text-slate-400" style="padding-left: 1rem !important;">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama mahasiswa, NIM, atau program studi..." 
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

                        <select name="agency_id" class="py-2 text-xs border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-medium w-full sm:w-auto">
                            <option value="">Semua Instansi Dinas</option>
                            @foreach($agencies as $ag)
                                <option value="{{ $ag->id }}" {{ request('agency_id') == $ag->id ? 'selected' : '' }}>
                                    {{ $ag->agency_name }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition shrink-0 cursor-pointer">
                            Filter
                        </button>

                        @if(request()->hasAny(['search', 'agency_id', 'report_status']))
                            <a href="{{ route('lecturer.dashboard') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold rounded-xl transition shrink-0">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- 3. TABEL MAHASISWA BIMBINGAN DPL -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-sm text-gray-900">Daftar Mahasiswa Bimbingan Magang</h3>
                    </div>
                    <span class="text-xs text-gray-400 font-mono">{{ $placements->count() }} Mahasiswa</span>
                </div>

                <!-- 1. TAMPILAN KHUSUS MOBILE (< 640px) -->
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
                                    <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-xs shrink-0">
                                        {{ strtoupper(substr($student->name ?? 'M', 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-xs sm:text-sm text-slate-900 leading-snug truncate">{{ $student->name ?? '-' }}</p>
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
                                    <strong class="text-slate-900 font-bold">Instansi:</strong> {{ $agency->agency_name ?? '-' }} ({{ $unit->name ?? '-' }})
                                </div>
                                <div class="text-[11px] text-slate-700 font-medium">
                                    <strong class="text-slate-900 font-bold">Mentor:</strong> {{ $mentor->name ?? 'Belum Ditugaskan' }}
                                    @if($nilaiDinas > 0)
                                        <span class="text-emerald-700 font-bold ml-1">• Skor: {{ $nilaiDinas }}/100</span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between text-[11px] pt-1.5 border-t border-slate-200/60">
                                    <span class="text-slate-500">Laporan Akhir:
                                        @if(!$finalReport)
                                            <span class="text-slate-400 italic">Belum Unggah</span>
                                        @else
                                            <x-status-badge type="review" :status="$finalReport->status" />
                                        @endif
                                    </span>
                                    <span class="text-slate-500">Nilai DPL:
                                        @if($hasEval)
                                            <span class="text-blue-700 font-bold">Sudah Dinilai</span>
                                        @else
                                            <span class="text-amber-700 font-bold">Belum Dinilai</span>
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <div class="pt-1">
                                <a href="{{ route('lecturer.students.show', $p->id) }}" 
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
                                <th class="py-3.5 px-4">Mahasiswa</th>
                                <th class="py-3.5 px-4">Instansi & Divisi Magang</th>
                                <th class="py-3.5 px-4">Pembimbing Dinas</th>
                                <th class="py-3.5 px-4 text-center">Logbook</th>
                                <th class="py-3.5 px-4 text-center">Laporan Akhir</th>
                                <th class="py-3.5 px-4 text-center">Nilai DPL</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
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
                                        <div class="font-bold text-gray-900 text-xs sm:text-sm">{{ $student->name ?? '-' }}</div>
                                        <div class="text-[11px] text-gray-500 font-mono">NIM: {{ $profile?->nim ?? '-' }}</div>
                                        <div class="text-[10px] text-blue-600 font-semibold">{{ $profile?->jurusan ?? '-' }}</div>
                                    </td>

                                    <!-- Placement Location -->
                                    <td class="py-4 px-4">
                                        <div class="font-bold text-gray-800 text-xs">{{ $agency->agency_name ?? '-' }}</div>
                                        <div class="text-[11px] text-gray-500">{{ $unit->name ?? '-' }}</div>
                                    </td>

                                    <!-- Mentor Info -->
                                    <td class="py-4 px-4">
                                        <div class="font-semibold text-gray-800 text-xs">{{ $mentor->name ?? 'Belum Ditugaskan' }}</div>
                                        <div class="text-[10px] text-emerald-600 font-semibold">
                                            @if($nilaiDinas > 0)
                                                Skor Dinas: {{ $nilaiDinas }}/100
                                            @else
                                                <span class="text-gray-400">Belum dinilai dinas</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Logbook Counter -->
                                    <td class="py-4 px-4 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            {{ $logbooksCount }} Entri
                                        </span>
                                    </td>

                                    <!-- Final Report Status -->
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        @if(!$finalReport)
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500">
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
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                        <span>🏢 Dinas: {{ number_format($nilaiDinas, 1) }}</span>
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                        🏢 Dinas: Menunggu
                                                    </span>
                                                @endif
                                                <span class="text-[9px] text-slate-400 font-medium">100% Nilai Dinas</span>
                                            @else
                                                <!-- Dual Indicator: Mentor + DPL -->
                                                <div class="flex items-center gap-1.5">
                                                    @if($nilaiDinas > 0)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" title="Nilai Pembimbing Lapangan Dinas">
                                                            🏢 {{ number_format($nilaiDinas, 1) }}
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium bg-slate-100 text-slate-500 border border-slate-200" title="Mentor Dinas belum menilai">
                                                            🏢 -
                                                        </span>
                                                    @endif

                                                    @if($hasEval)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200" title="Nilai Akademik DPL Kampus">
                                                            🎓 {{ number_format($eval->nilai_dosen_calculated, 1) }}
                                                        </span>
                                                    @else
                                                        <a href="{{ route('lecturer.evaluations.create', $p->id) }}" class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100" title="DPL belum mengisi nilai">
                                                            🎓 Nilai
                                                        </a>
                                                    @endif
                                                </div>

                                                <!-- Kumulatif Final -->
                                                @if($eval && $eval->nilai_akhir > 0)
                                                    <span class="text-[11px] font-black text-slate-900 mt-0.5 block">
                                                        Akhir: <strong class="text-blue-700">{{ number_format($eval->nilai_akhir, 1) }}</strong> <span class="text-[10px] text-emerald-700 font-bold">({{ $eval->grade_calculated }})</span>
                                                    </span>
                                                @else
                                                    <span class="text-[9px] text-slate-400 italic">Nilai Belum Lengkap</span>
                                                @endif
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Action Buttons -->
                                    <td class="py-4 px-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('lecturer.students.show', $p->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl text-xs font-bold transition cursor-pointer">
                                                <span>Detail</span>
                                            </a>
                                            <a href="{{ route('lecturer.students.grade_sheet', $p->id) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 rounded-xl text-xs font-bold transition cursor-pointer" title="Cetak Berita Acara & Nilai Resmi BAP">
                                                <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                                <span>BAP</span>
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
            </div>

        </div>
    </div>
</x-app-layout>
