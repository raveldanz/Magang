<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-bold text-xl sm:text-2xl text-slate-900 leading-tight">
                    Portal Mentor Lapangan
                </h2>
            </div>

            <div class="flex items-center">
                <span class="inline-flex items-center px-3.5 py-1.5 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl text-xs font-bold shadow-2xs">
                    {{ Auth::user()->agencyProfile->agency_name ?? 'Dinas Perpustakaan dan Kearsipan' }}@if(Auth::user()->unit) &bull; {{ Auth::user()->unit->name }}@endif
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5 sm:space-y-6">

            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-xs flex items-center gap-2.5 text-emerald-900 text-sm font-medium">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Pusat Tindakan Mentor: tampil hanya jika ada tugas yang memerlukan tindakan segera --}}
            @php
                $pendingLogbooksCount = (int) ($actionAlerts['pending_logbooks_count'] ?? 0);
                $pendingReports = $actionAlerts['pending_reports'] ?? collect();
                $urgentEnding = $actionAlerts['urgent_ending'] ?? collect();
                $pendingEvalReady = $actionAlerts['pending_eval_ready'] ?? collect();
                $evalTargetList = $urgentEnding->isNotEmpty() ? $urgentEnding : $pendingEvalReady;
                $hasUrgent = $pendingLogbooksCount > 0 || $pendingReports->isNotEmpty() || $urgentEnding->isNotEmpty();
            @endphp

            @if ($hasUrgent)
                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-2 pb-2 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm sm:text-base font-bold text-slate-900">Perlu Tindakan Segera</h3>
                            <span class="px-2.5 py-0.5 rounded-md text-xs font-bold bg-amber-100 text-amber-800">Prioritas</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
                        {{-- 1. Logbook Harian --}}
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 flex flex-col justify-between gap-3">
                            <div>
                                <div class="flex items-center justify-between gap-2 text-xs sm:text-sm font-semibold text-slate-800">
                                    <span>Logbook Harian</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $pendingLogbooksCount > 0 ? 'bg-blue-100 text-blue-800' : 'bg-slate-200 text-slate-700' }}">
                                        {{ $pendingLogbooksCount }} Menunggu
                                    </span>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-600 mt-2 leading-relaxed">
                                    @if ($pendingLogbooksCount > 0)
                                        Terdapat {{ $pendingLogbooksCount }} catatan aktivitas harian mahasiswa menunggu persetujuan Anda.
                                    @else
                                        Seluruh catatan logbook mahasiswa sudah selesai ditinjau.
                                    @endif
                                </p>
                            </div>
                            @if ($pendingLogbooksCount > 0)
                                <a href="{{ route('mentor.logbooks.index') }}"
                                   class="min-h-[44px] inline-flex items-center justify-center py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-xl transition shadow-xs">
                                    Tinjau Logbook
                                </a>
                            @else
                                <div class="pt-1 flex items-center gap-1.5 text-xs text-slate-600 font-medium">
                                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Seluruh logbook tuntas</span>
                                </div>
                            @endif
                        </div>

                        {{-- 2. Laporan Akhir --}}
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 flex flex-col justify-between gap-3">
                            <div>
                                <div class="flex items-center justify-between gap-2 text-xs sm:text-sm font-semibold text-slate-800">
                                    <span>Naskah Laporan Akhir</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $pendingReports->count() > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-200 text-slate-700' }}">
                                        {{ $pendingReports->count() }} Menunggu
                                    </span>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-600 mt-2 leading-relaxed">
                                    @if ($pendingReports->count() === 1)
                                        Laporan akhir dari {{ $pendingReports->first()->application?->user?->name ?? 'Mahasiswa' }} menunggu tinjauan Anda.
                                    @elseif ($pendingReports->count() > 1)
                                        {{ $pendingReports->count() }} naskah laporan akhir mahasiswa menunggu persetujuan Anda.
                                    @else
                                        Semua naskah laporan yang masuk sudah selesai diperiksa.
                                    @endif
                                </p>
                            </div>
                            @if ($pendingReports->count() > 0)
                                <a href="{{ route('mentor.students.show', $pendingReports->first()->id) }}#bagian-laporan"
                                   class="min-h-[44px] inline-flex items-center justify-center py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-xl transition shadow-xs">
                                    {{ $pendingReports->count() === 1 ? 'Tinjau Laporan' : 'Tinjau Laporan Pertama' }}
                                </a>
                            @else
                                <div class="pt-1 flex items-center gap-1.5 text-xs text-slate-600 font-medium">
                                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Semua naskah tuntas</span>
                                </div>
                            @endif
                        </div>

                        {{-- 3. Evaluasi / Penilaian Akhir --}}
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 flex flex-col justify-between gap-3">
                            <div>
                                <div class="flex items-center justify-between gap-2 text-xs sm:text-sm font-semibold text-slate-800">
                                    <span>Penilaian Akhir Mentor</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $urgentEnding->count() > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-200 text-slate-700' }}">
                                        {{ $urgentEnding->count() }} Perlu Dinilai
                                    </span>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-600 mt-2 leading-relaxed">
                                    @if ($urgentEnding->count() === 1)
                                        Masa magang {{ $urgentEnding->first()->application?->user?->name ?? 'Mahasiswa' }} mendekati selesai atau laporan disetujui. Silakan isi nilai akhir.
                                    @elseif ($urgentEnding->count() > 1)
                                        {{ $urgentEnding->count() }} mahasiswa siap dinilai untuk penerbitan sertifikat magang.
                                    @else
                                        Semua mahasiswa yang mendekati akhir magang sudah memiliki nilai mentor.
                                    @endif
                                </p>
                            </div>
                            @if ($urgentEnding->count() === 1)
                                <a href="{{ route('mentor.evaluations.create', $urgentEnding->first()->id) }}"
                                   class="min-h-[44px] inline-flex items-center justify-center py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-xl transition shadow-xs">
                                    Beri Nilai Akhir
                                </a>
                            @elseif ($urgentEnding->count() > 1)
                                <a href="{{ route('mentor.students.show', $evalTargetList->first()->id) }}#bagian-nilai"
                                   class="min-h-[44px] inline-flex items-center justify-center py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-xl transition shadow-xs">
                                    Mulai Beri Nilai ({{ $urgentEnding->count() }})
                                </a>
                            @else
                                <div class="pt-1 flex items-center gap-1.5 text-xs text-slate-600 font-medium">
                                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Penilaian tepat waktu</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- 1. RINGKASAN METRIK (4 KARTU JELAS & JUJUR) --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block">Total Bimbingan</span>
                        <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-1.5">{{ $stats['total_students'] }}</div>
                    </div>
                    <span class="text-xs text-slate-600 mt-2 block">{{ $stats['active_students'] }} aktif, {{ $stats['completed_students'] }} selesai</span>
                </div>

                <a href="{{ route('mentor.logbooks.index') }}" class="bg-white p-5 rounded-2xl border border-slate-100 hover:border-blue-300 shadow-xs flex flex-col justify-between transition group">
                    <div>
                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block group-hover:text-blue-700">Logbook Menunggu</span>
                        <div class="text-2xl sm:text-3xl font-black {{ $stats['pending_logbooks'] > 0 ? 'text-amber-700' : 'text-slate-900' }} mt-1.5">{{ $stats['pending_logbooks'] }}</div>
                    </div>
                    <span class="text-xs text-blue-700 font-semibold mt-2 block">Klik untuk tinjau logbook &rarr;</span>
                </a>

                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block">Laporan Disetujui</span>
                        <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-1.5">{{ $stats['total_reports_approved'] }}</div>
                    </div>
                    <span class="text-xs text-slate-600 mt-2 block">{{ $stats['total_reports_pending'] }} laporan menunggu atau revisi</span>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block">Perlu Nilai Akhir</span>
                        <div class="text-2xl sm:text-3xl font-black {{ $stats['total_pending_eval'] > 0 ? 'text-amber-700' : 'text-slate-900' }} mt-1.5">{{ $stats['total_pending_eval'] }}</div>
                    </div>
                    <span class="text-xs text-slate-600 mt-2 block">{{ $stats['total_evaluated'] }} mahasiswa sudah dinilai</span>
                </div>
            </div>

            {{-- 2. TAB STATUS & PENCARIAN --}}
            <div class="space-y-3">
                @php
                    $activeTab = $tab ?: 'all';
                    $tabBaseQuery = request()->except(['tab', 'page']);
                @endphp
                <div class="border-b border-slate-200 overflow-x-auto">
                    <nav class="flex space-x-5 sm:space-x-7 min-w-max" aria-label="Filter Status Mahasiswa">
                        <a href="{{ route('mentor.dashboard', array_merge($tabBaseQuery, ['tab' => 'all'])) }}"
                           class="py-3 px-1 border-b-2 text-xs sm:text-sm inline-flex items-center gap-2 transition {{ $activeTab === 'all' ? 'border-blue-600 text-blue-700 font-bold' : 'border-transparent text-slate-600 hover:text-slate-900 font-semibold' }}">
                            <span>Semua</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $activeTab === 'all' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700' }}">{{ $stats['total_students'] }}</span>
                        </a>
                        <a href="{{ route('mentor.dashboard', array_merge($tabBaseQuery, ['tab' => 'active'])) }}"
                           class="py-3 px-1 border-b-2 text-xs sm:text-sm inline-flex items-center gap-2 transition {{ $activeTab === 'active' ? 'border-blue-600 text-blue-700 font-bold' : 'border-transparent text-slate-600 hover:text-slate-900 font-semibold' }}">
                            <span>{{ \App\Enums\ApplicationStatus::ACTIVE->localizedLabel('mentor') }}</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $activeTab === 'active' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700' }}">{{ $stats['active_students'] }}</span>
                        </a>
                        <a href="{{ route('mentor.dashboard', array_merge($tabBaseQuery, ['tab' => 'upcoming'])) }}"
                           class="py-3 px-1 border-b-2 text-xs sm:text-sm inline-flex items-center gap-2 transition {{ $activeTab === 'upcoming' ? 'border-blue-600 text-blue-700 font-bold' : 'border-transparent text-slate-600 hover:text-slate-900 font-semibold' }}">
                            <span>{{ \App\Enums\ApplicationStatus::ACCEPTED->localizedLabel('mentor') }}</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $activeTab === 'upcoming' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700' }}">{{ $stats['upcoming_students'] }}</span>
                        </a>
                        <a href="{{ route('mentor.dashboard', array_merge($tabBaseQuery, ['tab' => 'completed'])) }}"
                           class="py-3 px-1 border-b-2 text-xs sm:text-sm inline-flex items-center gap-2 transition {{ $activeTab === 'completed' ? 'border-blue-600 text-blue-700 font-bold' : 'border-transparent text-slate-600 hover:text-slate-900 font-semibold' }}">
                            <span>{{ \App\Enums\ApplicationStatus::COMPLETED->localizedLabel('mentor') }}</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $activeTab === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700' }}">{{ $stats['completed_students'] }}</span>
                        </a>
                    </nav>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
                    <form method="GET" action="{{ route('mentor.dashboard') }}" class="flex flex-col lg:flex-row items-stretch lg:items-center gap-3">
                        @if(request('tab'))
                            <input type="hidden" name="tab" value="{{ request('tab') }}">
                        @endif
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none pl-3.5 text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search', request('q')) }}"
                                   placeholder="Cari nama mahasiswa, NIM, atau program studi..."
                                   class="w-full min-h-[44px] text-sm border-slate-300 rounded-xl pl-10 pr-4 py-2.5 focus:ring-blue-600 focus:border-blue-600 shadow-2xs">
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 shrink-0">
                            <select name="report_status" onchange="this.form.submit()"
                                    aria-label="Filter status laporan akhir"
                                    class="min-h-[44px] py-2.5 pl-3.5 pr-9 text-sm border-slate-300 rounded-xl focus:ring-blue-600 focus:border-blue-600 shadow-2xs font-medium bg-white">
                                <option value="">Semua Status Laporan</option>
                                <option value="pending" {{ request('report_status') == 'pending' ? 'selected' : '' }}>Menunggu Tinjauan</option>{{-- status-guard:ignore --}}
                                <option value="revision" {{ request('report_status') == 'revision' ? 'selected' : '' }}>Perlu Revisi</option>{{-- status-guard:ignore --}}
                                <option value="approved" {{ request('report_status') == 'approved' ? 'selected' : '' }}>Disetujui</option>{{-- status-guard:ignore --}}
                                <option value="none" {{ request('report_status') == 'none' ? 'selected' : '' }}>Belum Unggah Laporan</option>{{-- status-guard:ignore --}}
                            </select>

                            @if($universities->isNotEmpty())
                                <select name="university_id" onchange="this.form.submit()"
                                        aria-label="Filter perguruan tinggi"
                                        class="min-h-[44px] py-2.5 pl-3.5 pr-9 text-sm border-slate-300 rounded-xl focus:ring-blue-600 focus:border-blue-600 shadow-2xs font-medium bg-white max-w-full sm:max-w-xs truncate">
                                    <option value="">Semua Perguruan Tinggi</option>
                                    @foreach($universities as $u)
                                        <option value="{{ $u->id }}" {{ request('university_id') == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif

                            <div class="flex items-center gap-2">
                                <button type="submit" class="min-h-[44px] flex-1 sm:flex-initial px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-xs transition cursor-pointer">
                                    Cari
                                </button>

                                @if(request()->hasAny(['search', 'q', 'university_id', 'report_status']))
                                    <a href="{{ route('mentor.dashboard', array_filter(['tab' => request('tab')])) }}"
                                       class="min-h-[44px] inline-flex items-center justify-center px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-xl transition">
                                        Reset
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 3. DAFTAR MAHASISWA BIMBINGAN MENTOR --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between gap-2">
                    <h3 class="font-bold text-base text-slate-900">Daftar Mahasiswa Bimbingan Magang</h3>
                    <span class="text-xs text-slate-700 font-semibold bg-slate-100 px-3 py-1 rounded-full">
                        {{ method_exists($placements, 'total') ? $placements->total() : $placements->count() }} Mahasiswa
                    </span>
                </div>

                {{-- Tampilan Mobile / Tablet (< 1024px) --}}
                <div class="block lg:hidden divide-y divide-slate-100">
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
                            $finalReport = $p->finalreport;
                            $logbooksCount = $p->logbooks->count();
                            $pendingLogCount = (int) ($p->pending_logbooks_count ?? $p->logbooks->where('status', 'pending')->count());
                            $univName = $student?->universityRelation?->name ?? $student?->university ?? $profile?->universitas ?? '-';
                        @endphp
                        <div class="p-4 space-y-3 hover:bg-slate-50/60 transition">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-800 font-bold flex items-center justify-center text-xs shrink-0">
                                        {{ strtoupper(substr($student->name ?? 'M', 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-sm text-slate-900 leading-snug truncate">{{ $student->name ?? '-' }}</p>
                                        <p class="text-xs text-slate-600 font-mono mt-0.5">{{ $profile?->nim ?? '-' }} &bull; {{ $profile?->jurusan ?? '-' }}</p>
                                        <div class="mt-1.5">
                                            <x-status-badge :status="$appStatus" />
                                        </div>
                                    </div>
                                </div>
                                <div class="shrink-0 text-right space-y-1">
                                    @if($pendingLogCount > 0)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                            {{ $pendingLogCount }} Logbook Menunggu
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                            {{ $logbooksCount }} Logbook
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/60 text-xs sm:text-sm space-y-1.5">
                                <div class="text-slate-700">
                                    <span class="font-semibold text-slate-900">Kampus:</span> {{ $univName }}
                                </div>
                                <div class="text-slate-700">
                                    <span class="font-semibold text-slate-900">Unit Kerja:</span> {{ $unit->name ?? '-' }}
                                </div>
                                <div class="text-slate-700">
                                    <span class="font-semibold text-slate-900">Dosen Pembimbing:</span> {{ $dosen->name ?? 'Belum Ditugaskan' }}
                                </div>
                                <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-200/70 text-xs">
                                    <span class="text-slate-700">Laporan Akhir:
                                        @if(!$finalReport)
                                            <span class="text-slate-600 font-medium">Belum Mengunggah</span>
                                        @else
                                            <x-status-badge type="review" :status="$finalReport->status" />
                                        @endif
                                    </span>
                                    <span class="text-slate-700">Nilai Mentor:
                                        @if($hasMentorEval)
                                            <span class="text-emerald-800 font-bold">Sudah Dinilai ({{ $nilaiMentor }})</span>
                                        @else
                                            <span class="text-amber-800 font-bold">Belum Dinilai</span>
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <a href="{{ route('mentor.students.show', $p->id) }}"
                               class="w-full min-h-[44px] inline-flex items-center justify-center gap-1.5 py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition shadow-xs">
                                <span>Buka Bimbingan</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-600 text-sm">
                            Belum ada mahasiswa bimbingan magang yang sesuai dengan filter ini.
                        </div>
                    @endforelse
                </div>

                {{-- Tampilan Desktop (>= 1024px) --}}
                <div class="hidden lg:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-xs">
                            <tr>
                                <th class="py-3.5 px-5 w-3/12">Mahasiswa</th>
                                <th class="py-3.5 px-4 w-3/12">Perguruan Tinggi &amp; Unit</th>
                                <th class="py-3.5 px-4 w-2/12">Dosen Pembimbing</th>
                                <th class="py-3.5 px-3 text-center w-1/12 whitespace-nowrap">Logbook</th>
                                <th class="py-3.5 px-3 text-center w-1/12 whitespace-nowrap">Laporan Akhir</th>
                                <th class="py-3.5 px-4 text-center w-1/12 whitespace-nowrap">Nilai Mentor</th>
                                <th class="py-3.5 px-5 text-right w-1/12 whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
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
                                    $finalReport = $p->finalreport;
                                    $logbooksCount = $p->logbooks->count();
                                    $pendingLogCount = (int) ($p->pending_logbooks_count ?? $p->logbooks->where('status', 'pending')->count());
                                    $univName = $student?->universityRelation?->name ?? $student?->university ?? $profile?->universitas ?? '-';
                                    $mentorGrade = \App\Support\Grade::fromScore($nilaiMentor);
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-5 align-top">
                                        <div class="font-bold text-slate-900 text-sm leading-snug">{{ $student->name ?? '-' }}</div>
                                        <div class="text-xs text-slate-600 font-mono mt-0.5">NIM: {{ $profile?->nim ?? '-' }}</div>
                                        <div class="text-xs text-slate-600 mt-0.5">{{ $profile?->jurusan ?? '-' }}</div>
                                        <div class="mt-2">
                                            <x-status-badge :status="$appStatus" />
                                        </div>
                                    </td>

                                    <td class="py-4 px-4 align-top">
                                        <div class="font-bold text-slate-900 text-sm">{{ $univName }}</div>
                                        <div class="text-xs text-slate-600 mt-0.5">{{ $unit->name ?? '-' }}</div>
                                    </td>

                                    <td class="py-4 px-4 align-top">
                                        <div class="font-semibold text-slate-800 text-sm">{{ $dosen->name ?? 'Belum Ditugaskan' }}</div>
                                    </td>

                                    <td class="py-4 px-3 text-center whitespace-nowrap align-top">
                                        @if($pendingLogCount > 0)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                {{ $pendingLogCount }} Menunggu
                                            </span>
                                            <div class="text-xs text-slate-600 mt-1">{{ $logbooksCount }} total catatan</div>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                                {{ $logbooksCount }} Catatan
                                            </span>
                                        @endif
                                    </td>

                                    <td class="py-4 px-3 text-center whitespace-nowrap align-top">
                                        @if(!$finalReport)
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                                Belum Mengunggah
                                            </span>
                                        @else
                                            <x-status-badge type="review" :status="$finalReport->status" />
                                        @endif
                                    </td>

                                    <td class="py-4 px-4 text-center whitespace-nowrap align-top">
                                        @if($hasMentorEval)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                {{ $nilaiMentor }}/100
                                                @if($mentorGrade !== '-')
                                                    ({{ $mentorGrade }})
                                                @endif
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                                Belum Dinilai
                                            </span>
                                        @endif
                                    </td>

                                    <td class="py-4 px-5 text-right whitespace-nowrap align-top">
                                        <a href="{{ route('mentor.students.show', $p->id) }}"
                                           class="min-h-[40px] inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-2xs">
                                            <span>Buka Bimbingan</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-600 text-sm">
                                        Belum ada mahasiswa bimbingan magang yang sesuai dengan filter ini.
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
