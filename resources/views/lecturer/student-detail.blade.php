<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-xs sm:text-sm">
            <a href="{{ route('lecturer.monitoring.index') }}" 
               class="font-semibold text-slate-600 hover:text-slate-900 transition">
                Mahasiswa Bimbingan
            </a>
            <span class="text-slate-300">/</span>
            <span class="font-bold text-slate-900 truncate max-w-xs sm:max-w-md">
                {{ $student->name }}
            </span>
        </div>
    </x-slot>

    @php
        $mentor = $placement->mentor ?? $placement->pembimbing;
        $academicAdvisor = $placement->academicAdvisor;
        $agency = $unit?->agencyProfile ?? $placement->agencyProfile;
        $eval = $evaluation;
        $hasEval = $eval && (($eval->score_mastery ?? 0) > 0 || ($eval->nilai_akademik ?? 0) > 0 || ($eval->nilai_dosen ?? 0) > 0);
        $univ = $eval?->getUniversity();
        if (!$univ && $student) {
            $univ = app(\App\Services\UniversityResolver::class)->forUser($student);
        }
        $scheme = $univ->evaluation_scheme ?? 'dual_evaluation';
        $isMentorOnly = ($scheme === 'mentor_only');

        $app = $placement->application;
        $appStatusVal = $app ? ($app->status instanceof \BackedEnum ? $app->status->value : $app->status) : 'active';
        $rawSt = strtolower($appStatusVal ?? 'active');
        $statusLabel = \App\Enums\ApplicationStatus::resolve($rawSt)?->label() ?? strtoupper($rawSt);

        $logbookTotal = $logbooks->count();
        $finalReportStatusVal = $finalReport ? ($finalReport->status instanceof \BackedEnum ? $finalReport->status->value : $finalReport->status) : null;
        $rawFinalSt = strtolower($finalReportStatusVal ?? '');
        $isReportApproved = ($rawFinalSt === 'approved');

        $hasMentorScore = $eval && (($eval->nilai_disiplin ?? 0) > 0 || ($eval->nilai_kinerja ?? 0) > 0 || ($eval->nilai_laporan ?? 0) > 0);
        $nilaiDinas = $eval ? ($eval->nilai_pembimbing ?? 0) : 0;
        $finalScore = $eval?->final_score > 0 ? $eval->final_score : ($eval?->nilai_akhir > 0 ? $eval->nilai_akhir : null);

        // Durasi Hari Kerja
        $targetDays = 30;
        if ($app && $app->start_date && $app->end_date) {
            try {
                $start = \Carbon\Carbon::parse($app->start_date);
                $end = \Carbon\Carbon::parse($app->end_date);
                $diff = $start->diffInDaysFiltered(fn(\Carbon\Carbon $d) => !$d->isWeekend(), $end);
                if ($diff > 0) $targetDays = (int) $diff;
            } catch (\Exception $e) {
                $targetDays = 30;
            }
        }

        // Countdown Sisa Waktu Magang
        $daysRemaining = null;
        $isFinished = false;
        if ($app && $app->end_date) {
            try {
                $endDate = \Carbon\Carbon::parse($app->end_date)->endOfDay();
                if (now()->greaterThan($endDate)) {
                    $isFinished = true;
                } else {
                    $daysRemaining = (int) ceil(now()->floatDiffInDays($endDate, false));
                }
            } catch (\Exception $e) {
                $daysRemaining = null;
            }
        }

        // Status Keaktifan Logbook Terakhir
        $latestLogbook = $logbooks->first();
        $daysSinceLastLogbook = null;
        if ($latestLogbook && $latestLogbook->date) {
            try {
                $lastLogbookDate = \Carbon\Carbon::parse($latestLogbook->date);
                $daysSinceLastLogbook = (int) $lastLogbookDate->diffInDays(now());
            } catch (\Exception $e) {
                $daysSinceLastLogbook = null;
            }
        }

        // WhatsApp Direct Link
        $studentPhone = $student->phone ?? $profile->phone ?? null;
        $cleanStudentPhone = $studentPhone ? preg_replace('/[^0-9]/', '', $studentPhone) : null;
        if ($cleanStudentPhone && str_starts_with($cleanStudentPhone, '0')) {
            $cleanStudentPhone = '62' . substr($cleanStudentPhone, 1);
        }
        $waAgencyName = $agency->agency_name ?? 'instansi mitra';
        $waMessage = rawurlencode("Halo {$student->name}, perihal bimbingan magang Anda di {$waAgencyName}.");

        $studentInitial = strtoupper(substr($student->name ?? 'M', 0, 1));

        // 4 Indikator Kelengkapan Magang
        $readyLogbook = ($logbookTotal > 0);
        $readyReport = $isReportApproved;
        $readyLecturerScore = $isMentorOnly || $hasEval;
        $readyMentorScore = $hasMentorScore;
        $readyCount = ($readyLogbook ? 1 : 0) + ($readyReport ? 1 : 0) + ($readyLecturerScore ? 1 : 0) + ($readyMentorScore ? 1 : 0);
        $readinessPercent = round(($readyCount / 4) * 100);

        // Status Izin Chat & Grup Komunikasi
        $chatViewer = auth()->user();
        $canChatStudent = $chatViewer && $student && (int) $chatViewer->id !== (int) $student->id
            && app(\App\Services\Chat\ChatContactDirectory::class)->canContact($chatViewer, $student);
        $canChatMentor = $chatViewer && $mentor && (int) $chatViewer->id !== (int) $mentor->id
            && app(\App\Services\Chat\ChatContactDirectory::class)->canContact($chatViewer, $mentor);

        $groupMembers = $placement
            ? array_map('intval', array_filter([$placement->application?->user_id, $placement->mentor_id, $placement->pembimbing_id, $placement->academic_advisor_id]))
            : [];
        $showGroup = $chatViewer && count(array_unique($groupMembers)) >= 2 && in_array((int) $chatViewer->id, $groupMembers, true);
    @endphp

    <div class="py-6 sm:py-8" x-data="{ activeTab: 'evaluasi' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Messages -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 rounded-2xl text-emerald-900 text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 bg-rose-50 rounded-2xl text-rose-900 text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <!-- 1. HERO PROFILE CARD (Executive, Clean, Human-Crafted) -->
            <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                    
                    <!-- Left: Avatar & Identity -->
                    <div class="flex items-start sm:items-center gap-3.5 min-w-0 w-full lg:w-auto">
                        @if (!empty($profile?->photo_url))
                            <img src="{{ $profile->photo_url }}" alt="{{ $student->name }}" 
                                 class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl object-cover border border-slate-200/80 shadow-2xs shrink-0">
                        @else
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-bold text-lg sm:text-xl flex items-center justify-center shrink-0 shadow-2xs">
                                {{ $studentInitial }}
                            </div>
                        @endif

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h1 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight leading-tight">
                                    {{ $student->name }}
                                </h1>
                                <div class="inline-flex items-center gap-1.5 shrink-0 flex-wrap">
                                    <x-status-badge :status="$appStatusVal" />

                                    @if ($app?->group_id && $app?->group)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/80 shadow-2xs">
                                            Kelompok: {{ $app->group->name }} ({{ $app->role_in_group === 'leader' ? 'Ketua' : 'Anggota' }})
                                        </span>
                                    @endif

                                    @if ($isFinished)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200/80">
                                            <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Masa Magang Berakhir {{-- status-guard:ignore --}}
                                        </span>
                                    @elseif ($daysRemaining !== null)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $daysRemaining <= 7 ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-blue-50 text-blue-700 border border-blue-200/60' }}">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Sisa {{ $daysRemaining }} Hari
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <p class="text-xs text-slate-500 font-medium mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                <span>NIM {{ $profile->nim ?? '-' }}</span>
                                <span class="text-slate-300">&bull;</span>
                                <span>{{ $profile->jurusan ?? '-' }}</span>
                                <span class="text-slate-300">&bull;</span>
                                <span>{{ $profile->universitas ?? ($univ->name ?? '-') }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Right: Cohesive Communication Toolbar -->
                    <div class="grid grid-cols-2 sm:flex sm:items-center gap-2 w-full lg:w-auto shrink-0 pt-1 lg:pt-0">
                        @if ($showGroup)
                            <a href="{{ route('chat.placement', $placement->id) }}" 
                               title="Buka Forum Diskusi Bersama"
                               class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-xs transition active:scale-95 text-center cursor-pointer">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>Grup Bimbingan</span>
                            </a>
                        @endif

                        @if ($cleanStudentPhone)
                            <a href="https://wa.me/{{ $cleanStudentPhone }}?text={{ $waMessage }}" target="_blank"
                               title="Hubungi via WhatsApp ({{ $studentPhone }})"
                               class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200/90 text-xs font-semibold rounded-xl shadow-2xs transition active:scale-95 text-center cursor-pointer">
                                <svg class="w-4 h-4 text-emerald-600 fill-current shrink-0" viewBox="0 0 24 24">
                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.861.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/>
                                </svg>
                                <span>WhatsApp</span>
                            </a>
                        @endif

                        @if ($canChatStudent)
                            <form method="POST" action="{{ route('chat.start') }}" class="m-0 {{ !$mentor || !$canChatMentor ? 'col-span-2 sm:col-span-1' : '' }}">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $student->id }}">
                                <button type="submit" 
                                        title="Kirim pesan langsung ke {{ $student->name }}"
                                        class="inline-flex items-center justify-center gap-1.5 w-full px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/90 text-xs font-semibold rounded-xl shadow-2xs transition active:scale-95 text-center cursor-pointer">
                                    <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    <span>Chat Mhs</span>
                                </button>
                            </form>
                        @endif

                        @if ($mentor && $canChatMentor)
                            <form method="POST" action="{{ route('chat.start') }}" class="m-0">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $mentor->id }}">
                                <button type="submit" 
                                        title="Kirim pesan ke Mentor Lapangan"
                                        class="inline-flex items-center justify-center gap-1.5 w-full px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/90 text-xs font-semibold rounded-xl shadow-2xs transition active:scale-95 text-center cursor-pointer">
                                    <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span>Chat Mentor</span>
                                </button>
                            </form>
                        @endif
                    </div>

                </div>

                <!-- Structured Placement Details (Clean, Unified, Minimalist) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-3.5 border-t border-slate-100 text-xs">
                    <!-- 1. Instansi & Bidang -->
                    <div class="p-3 sm:p-3.5 rounded-xl bg-slate-50/70 border border-slate-100 min-w-0">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Instansi & Bidang</span>
                        <div class="font-bold text-slate-900 truncate mt-1" title="{{ $agency->agency_name ?? '-' }}">{{ $agency->agency_name ?? '-' }}</div>
                        <div class="text-[11px] text-slate-500 truncate mt-0.5" title="{{ $unit->name ?? '-' }}">{{ $unit->name ?? '-' }}</div>
                    </div>

                    <!-- 2. Pembimbing Dinas (Mentor) -->
                    <div class="p-3 sm:p-3.5 rounded-xl bg-slate-50/70 border border-slate-100 min-w-0">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Pembimbing Dinas</span>
                        <div class="font-bold text-slate-900 truncate mt-1" title="{{ $mentor->name ?? 'Belum Ditugaskan' }}">{{ $mentor->name ?? 'Belum Ditugaskan' }}</div>
                        <div class="text-[11px] text-slate-500 truncate mt-0.5">
                            @if($nilaiDinas > 0)
                                <span class="text-slate-700 font-semibold">Nilai Dinas: {{ number_format($nilaiDinas, 1) }}</span>
                            @else
                                <span>Pendamping Lapangan</span>
                            @endif
                        </div>
                    </div>

                    <!-- 3. Periode Waktu -->
                    <div class="p-3 sm:p-3.5 rounded-xl bg-slate-50/70 border border-slate-100 min-w-0">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Periode Magang</span>
                        <div class="font-bold text-slate-900 truncate mt-1">
                            {{ $app?->start_date ? \Carbon\Carbon::parse($app->start_date)->translatedFormat('d M') : '-' }} &ndash; {{ $app?->end_date ? \Carbon\Carbon::parse($app->end_date)->translatedFormat('d M Y') : '-' }}
                        </div>
                        <div class="text-[11px] text-slate-500 truncate mt-0.5">{{ $targetDays }} hari kerja</div>
                    </div>

                    <!-- 4. Skema Evaluasi Kampus -->
                    <div class="p-3 sm:p-3.5 rounded-xl bg-slate-50/70 border border-slate-100 min-w-0">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Skema Penilaian</span>
                        <div class="font-bold text-slate-900 truncate mt-1">{{ $isMentorOnly ? '100% Mentor Dinas' : 'DPL + Mentor Dinas' }}</div>
                        <div class="text-[11px] text-slate-500 truncate mt-0.5" title="{{ $univ->name ?? '-' }}">{{ $univ->name ?? 'Kampus Mitra' }}</div>
                    </div>
                </div>
            </div>

            <!-- 2. ACTIONABLE INACTIVITY ALERT (Muncul hanya jika telat isi logbook) -->
            @if ($latestLogbook && $daysSinceLastLogbook > 4 && !$isFinished)
                <div class="bg-gradient-to-r from-amber-50/90 via-orange-50/70 to-amber-50/90 border border-amber-200/90 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3.5 shadow-2xs">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 shadow-2xs mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="text-xs sm:text-sm font-bold text-amber-950">Peringatan Keaktifan Mahasiswa</h4>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-200/80 text-amber-900">
                                    {{ $daysSinceLastLogbook }} Hari Belum Mengisi Logbook
                                </span>
                            </div>
                            <p class="text-xs text-amber-800/90 mt-1">
                                Mahasiswa belum menginput entri logbook baru dalam {{ $daysSinceLastLogbook }} hari terakhir. Mohon hubungi mahasiswa untuk memastikan keteraturan pencatatan magang.
                            </p>
                        </div>
                    </div>
                    @if ($cleanStudentPhone)
                        <a href="https://wa.me/{{ $cleanStudentPhone }}?text={{ $waMessage }}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition shadow-xs shrink-0 cursor-pointer active:scale-95">
                            <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24">
                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.861.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/>
                            </svg>
                            <span>Ingatkan via WhatsApp</span>
                        </a>
                    @endif
                </div>
            @endif

            <!-- 3. READINESS STEPPER: KESIAPAN EVALUASI AKHIR (Modern Executive Strip) -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs space-y-3.5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-slate-900 leading-tight">Kesiapan Penilaian Akhir Magang</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Pantau kelengkapan berkas dan aktivitas sebelum penerbitan nilai akademik</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3 self-end sm:self-center">
                        <div class="text-right">
                            <span class="text-xs font-bold text-slate-900">{{ $readyCount }} dari 4 Syarat</span>
                            <div class="w-24 bg-slate-100 rounded-full h-1.5 mt-1 overflow-hidden">
                                <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-500" style="width: {{ $readinessPercent }}%"></div>
                            </div>
                        </div>
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $readinessPercent === 100 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80' : 'bg-slate-100 text-slate-700' }}">
                            {{ $readinessPercent }}%
                        </span>
                    </div>
                </div>

                <!-- 4 Interactive Micro-Cards -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3 text-xs">
                    <!-- 1. Logbook -->
                    <button type="button" @click="activeTab = 'logbook'"
                            class="p-3 sm:p-3.5 rounded-xl border transition text-left cursor-pointer group {{ $readyLogbook ? 'bg-emerald-50/50 border-emerald-200/80 hover:bg-emerald-50' : 'bg-slate-50/70 border-slate-200/70 hover:bg-slate-50' }}">
                        <div class="flex items-center justify-between gap-1">
                            <span class="text-[11px] font-semibold text-slate-500 group-hover:text-slate-800 transition">1. Logbook Harian</span>
                            @if($readyLogbook)
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            @else
                                <span class="w-2 h-2 rounded-full bg-slate-300 shrink-0"></span>
                            @endif
                        </div>
                        <div class="font-bold mt-1 text-slate-900 group-hover:text-blue-600 transition">
                            {{ $logbookTotal > 0 ? $logbookTotal.' Entri Terisi' : 'Belum Ada Entri' }}
                        </div>
                    </button>

                    <!-- 2. Laporan Akhir -->
                    <button type="button" @click="activeTab = 'laporan'"
                            class="p-3 sm:p-3.5 rounded-xl border transition text-left cursor-pointer group {{ $readyReport ? 'bg-emerald-50/50 border-emerald-200/80 hover:bg-emerald-50' : ($finalReport ? 'bg-amber-50/50 border-amber-200/80 hover:bg-amber-50' : 'bg-slate-50/70 border-slate-200/70 hover:bg-slate-50') }}">
                        <div class="flex items-center justify-between gap-1">
                            <span class="text-[11px] font-semibold text-slate-500 group-hover:text-slate-800 transition">2. Laporan Akhir</span>
                            @if($readyReport)
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            @elseif($finalReport)
                                <svg class="w-4 h-4 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                            @else
                                <span class="w-2 h-2 rounded-full bg-slate-300 shrink-0"></span>
                            @endif
                        </div>
                        <div class="font-bold mt-1 text-slate-900 group-hover:text-blue-600 transition">
                            @if($readyReport)
                                Disetujui DPL {{-- status-guard:ignore --}}
                            @elseif($finalReport)
                                Menunggu Review {{-- status-guard:ignore --}}
                            @else
                                Belum Unggah
                            @endif
                        </div>
                    </button>

                    <!-- 3. Nilai Mentor -->
                    <div class="p-3 sm:p-3.5 rounded-xl border text-left {{ $readyMentorScore ? 'bg-emerald-50/50 border-emerald-200/80' : 'bg-slate-50/70 border-slate-200/70' }}">
                        <div class="flex items-center justify-between gap-1">
                            <span class="text-[11px] font-semibold text-slate-500">3. Nilai Dinas (Mentor)</span>
                            @if($readyMentorScore)
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            @else
                                <span class="w-2 h-2 rounded-full bg-slate-300 shrink-0"></span>
                            @endif
                        </div>
                        <div class="font-bold mt-1 text-slate-900">
                            {{ $readyMentorScore ? 'Skor: '.number_format($eval->nilai_pembimbing ?? 0, 1) : 'Menunggu Dinas' }}
                        </div>
                    </div>

                    <!-- 4. Nilai DPL -->
                    <button type="button" @click="activeTab = 'evaluasi'"
                            class="p-3 sm:p-3.5 rounded-xl border transition text-left cursor-pointer group {{ $readyLecturerScore ? 'bg-emerald-50/50 border-emerald-200/80 hover:bg-emerald-50' : 'bg-blue-50/40 border-blue-200/80 hover:bg-blue-50/70' }}">
                        <div class="flex items-center justify-between gap-1">
                            <span class="text-[11px] font-semibold text-slate-500 group-hover:text-slate-800 transition">4. Nilai DPL</span>
                            @if($readyLecturerScore)
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            @else
                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-blue-100 text-blue-700">Input</span>
                            @endif
                        </div>
                        <div class="font-bold mt-1 text-slate-900 group-hover:text-blue-600 transition">
                            @if($isMentorOnly)
                                Skema 100% Mentor
                            @elseif($hasEval)
                                Skor: {{ number_format($eval->nilai_dosen_calculated ?? ($eval->nilai_akademik ?? 0), 1) }}
                            @else
                                Siap Diinput DPL
                            @endif
                        </div>
                    </button>
                </div>
            </div>

            <!-- 4. TABBED NAVIGATION (Clean Responsive Tabs - No Scrollbar) -->
            <div class="border-b border-slate-200">
                <nav class="flex items-center w-full sm:space-x-8 sm:w-auto" aria-label="Tabs">
                    <button type="button" @click="activeTab = 'evaluasi'"
                            :class="activeTab === 'evaluasi' 
                                ? 'border-blue-600 text-blue-600 font-bold' 
                                : 'border-transparent text-slate-500 hover:text-slate-700 font-medium'"
                            class="flex-1 sm:flex-initial py-3 px-1 border-b-2 text-xs sm:text-sm text-center transition cursor-pointer flex items-center justify-center gap-1 sm:gap-2">
                        <span class="sm:hidden">Nilai</span>
                        <span class="hidden sm:inline">Evaluasi & Nilai</span>
                    </button>

                    <button type="button" @click="activeTab = 'logbook'"
                            :class="activeTab === 'logbook' 
                                ? 'border-blue-600 text-blue-600 font-bold' 
                                : 'border-transparent text-slate-500 hover:text-slate-700 font-medium'"
                            class="flex-1 sm:flex-initial py-3 px-1 border-b-2 text-xs sm:text-sm text-center transition cursor-pointer flex items-center justify-center gap-1 sm:gap-2">
                        <span>Logbook</span>
                        <span class="px-1.5 sm:px-2 py-0.5 rounded-full text-[10px] sm:text-[11px] font-semibold"
                              :class="activeTab === 'logbook' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600'">
                            {{ $logbookTotal }}
                        </span>
                    </button>

                    <button type="button" @click="activeTab = 'laporan'"
                            :class="activeTab === 'laporan' 
                                ? 'border-blue-600 text-blue-600 font-bold' 
                                : 'border-transparent text-slate-500 hover:text-slate-700 font-medium'"
                            class="flex-1 sm:flex-initial py-3 px-1 border-b-2 text-xs sm:text-sm text-center transition cursor-pointer flex items-center justify-center gap-1 sm:gap-2">
                        <span>Laporan</span>
                        @if($isReportApproved)
                            <span class="px-1.5 py-0.5 rounded-full text-[9px] sm:text-[10px] font-semibold bg-emerald-100 text-emerald-800">ACC</span>
                        @elseif($finalReport)
                            <span class="px-1.5 py-0.5 rounded-full text-[9px] sm:text-[10px] font-semibold bg-amber-100 text-amber-800">Review</span>
                        @endif
                    </button>

                    <button type="button" @click="activeTab = 'bimbingan'"
                            :class="activeTab === 'bimbingan' 
                                ? 'border-blue-600 text-blue-600 font-bold' 
                                : 'border-transparent text-slate-500 hover:text-slate-700 font-medium'"
                            class="flex-1 sm:flex-initial py-3 px-1 border-b-2 text-xs sm:text-sm text-center transition cursor-pointer flex items-center justify-center gap-1 sm:gap-2">
                        <span>Bimbingan</span>
                        <span class="px-1.5 sm:px-2 py-0.5 rounded-full text-[10px] sm:text-[11px] font-semibold"
                              :class="activeTab === 'bimbingan' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600'">
                            {{ $consultations->count() }}
                        </span>
                    </button>
                </nav>
            </div>

            <!-- ========================================================================= -->
            <!-- TAB 1: EVALUASI & PENILAIAN -->
            <!-- ========================================================================= -->
            <div x-show="activeTab === 'evaluasi'" class="space-y-6">

                <!-- Lembar Evaluasi (Clean Surface, Soft Containers) -->
                <div class="bg-white rounded-2xl p-6 shadow-xs space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-3 border-b border-slate-100">
                        <h3 class="font-bold text-base text-slate-900">
                            Lembar Nilai Evaluasi Magang
                        </h3>

                        <div>
                            @if (! empty($evaluationLockReason))
                                <a href="{{ route('lecturer.evaluations.create', $placement->id) }}"
                                   class="inline-flex items-center justify-center w-full sm:w-auto px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                                    Lihat Nilai
                                </a>
                            @else
                                <a href="{{ route('lecturer.evaluations.create', $placement->id) }}"
                                   class="inline-flex items-center justify-center w-full sm:w-auto px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                                    {{ $hasEval ? 'Ubah Nilai Evaluasi' : 'Isi Nilai Evaluasi' }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Komparasi Nilai (Clean Soft Gray Cards - No Outlines) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Nilai DPL -->
                        <div class="p-5 bg-slate-50 rounded-2xl space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    Nilai Dosen Pembimbing
                                </span>
                                @if (!$isMentorOnly && ($eval?->nilai_dosen_calculated ?? 0) > 0)
                                    <span class="px-2.5 py-0.5 rounded-md text-xs font-bold bg-blue-100 text-blue-800">
                                        Rata-rata: {{ $eval->nilai_dosen_calculated }}
                                    </span>
                                @endif
                            </div>

                            @if ($isMentorOnly)
                                <p class="text-xs text-slate-500 italic p-2">
                                    Kebijakan kampus menerapkan 100% penilaian dari Mentor Lapangan.
                                </p>
                            @else
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-center">
                                    @forelse ($eval?->dosen_aspects_list ?? [] as $dAspect)
                                        <div class="p-2.5 bg-white rounded-xl shadow-2xs">
                                            <span class="text-[11px] text-slate-500 font-medium block truncate" title="{{ $dAspect['name'] }}">{{ $dAspect['name'] }}</span>
                                            <span class="text-base font-bold text-slate-900">{{ $dAspect['score'] ?? '-' }}</span>
                                        </div>
                                    @empty
                                        <div class="col-span-2 sm:col-span-3 p-3 bg-white rounded-xl text-slate-400 text-xs">
                                            Belum ada nilai bimbingan akademik DPL.
                                        </div>
                                    @endforelse
                                </div>
                                <div class="text-xs text-slate-500 flex justify-between pt-1">
                                    <span>Bobot Kampus: {{ $univ->weight_lecturer ?? 40 }}%</span>
                                    <span>Status: <strong class="{{ $hasEval ? 'text-emerald-700' : 'text-amber-700' }}">{{ $hasEval ? 'Telah Dinilai' : 'Belum Diisi' }}</strong></span>
                                </div>
                            @endif
                        </div>

                        <!-- Nilai Mentor -->
                        <div class="p-5 bg-slate-50 rounded-2xl space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    Nilai Mentor Lapangan
                                </span>
                                @if (($eval?->nilai_pembimbing ?? 0) > 0)
                                    <span class="px-2.5 py-0.5 rounded-md text-xs font-bold bg-emerald-100 text-emerald-800">
                                        Rata-rata: {{ $eval->nilai_pembimbing }}
                                    </span>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-center">
                                @forelse ($eval?->mentor_aspects_list ?? [] as $mAspect)
                                    <div class="p-2.5 bg-white rounded-xl shadow-2xs">
                                        <span class="text-[11px] text-slate-500 font-medium block truncate" title="{{ $mAspect['name'] }}">{{ $mAspect['name'] }}</span>
                                        <span class="text-base font-bold text-slate-900">{{ $mAspect['score'] ?? '-' }}</span>
                                    </div>
                                @empty
                                    <div class="col-span-2 sm:col-span-3 p-3 bg-white rounded-xl text-slate-400 text-xs">
                                        Belum ada nilai mentor lapangan.
                                    </div>
                                @endforelse
                            </div>

                            <div class="text-xs text-slate-500 flex justify-between pt-1">
                                <span>Bobot Lapangan: {{ $isMentorOnly ? 100 : ($univ->weight_mentor ?? 60) }}%</span>
                                <span>Status: <strong class="{{ ($eval?->nilai_pembimbing ?? 0) > 0 ? 'text-emerald-700' : 'text-amber-700' }}">{{ ($eval?->nilai_pembimbing ?? 0) > 0 ? 'Telah Dinilai' : 'Menunggu Mentor' }}</strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Nilai Akhir (Clean, Human, Soft Modern Design) -->
                    <div class="p-5 bg-blue-50/70 rounded-2xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <span class="text-xs font-semibold text-blue-900 uppercase tracking-wider block">Nilai Akhir Magang</span>
                            <div class="text-3xl font-extrabold text-blue-950 mt-0.5">
                                {{ $finalScore ? $finalScore : 'Belum Lengkap' }}
                            </div>
                        </div>

                        <div class="sm:text-right">
                            <span class="text-xs font-semibold text-blue-900 uppercase tracking-wider block">Predikat Mutu</span>
                            <div class="text-2xl font-black text-blue-700 mt-0.5">
                                {{ $eval?->grade_calculated ?? ($eval?->grade ?? '-') }}
                            </div>
                        </div>
                    </div>

                    @if ($eval?->feedback_dosen ?? $eval?->catatan_dosen)
                        <div class="p-4 bg-slate-50 rounded-xl text-xs text-slate-700">
                            <strong>Catatan Dosen:</strong> "{{ $eval->feedback_dosen ?? $eval->catatan_dosen }}"
                        </div>
                    @endif
                </div>

                <!-- 5. Catatan Internal Pribadi Dosen (Private Notes) -->
                <div class="bg-white rounded-2xl p-6 shadow-xs space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <div>
                            <h4 class="font-bold text-sm text-slate-900">Catatan Pribadi Dosen Pembimbing</h4>
                            <span class="text-[11px] text-slate-500">Catatan ini bersifat internal untuk arsip Anda dan tidak dapat dilihat oleh mahasiswa.</span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('lecturer.students.notes', $placement->id) }}" class="space-y-3">
                        @csrf
                        @method('PATCH')
                        <textarea name="advisor_notes" rows="3" placeholder="Tuliskan catatan internal mengenai progres, kendala khusus, atau poin evaluasi mahasiswa ini..."
                                  class="w-full text-xs sm:text-sm bg-slate-50 border-0 ring-1 ring-slate-200 focus:ring-2 focus:ring-blue-600 rounded-xl p-3 shadow-2xs">{{ old('advisor_notes', $placement->advisor_notes) }}</textarea>
                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-xs cursor-pointer">
                                Simpan Catatan Pribadi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- TAB 2: LOGBOOK AKTIVITAS MINGGUAN -->
            <!-- ========================================================================= -->
            <div x-show="activeTab === 'logbook'" class="space-y-5">
                <div class="bg-white rounded-2xl p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="font-bold text-base text-slate-900">
                            Logbook Aktivitas Mahasiswa
                        </h3>
                        <span class="px-3 py-1 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-full">
                            Verifikasi DPL
                        </span>
                    </div>

                    <x-supervision.logbook-weekly
                        :bundles="$weeklyBundles"
                        :reviewRoute="route('lecturer.logbooks.bulk_approve')"
                        :showBulkSelect="false"
                        role="lecturer"
                        emptyMessage="Mahasiswa belum menginputkan entri logbook kegiatan."
                    />
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- TAB 3: LAPORAN AKHIR -->
            <!-- ========================================================================= -->
            <div x-show="activeTab === 'laporan'" class="space-y-5">
                <x-supervision.report-review
                    :finalReport="$finalReport"
                    :placement="$placement"
                    :submitRoute="route('lecturer.final_report.updateStatus', $placement->id)"
                    method="POST"
                    role="lecturer"
                />
            </div>

            <!-- ========================================================================= -->
            <!-- TAB 4: CATATAN BIMBINGAN DPL -->
            <!-- ========================================================================= -->
            <div x-show="activeTab === 'bimbingan'" class="space-y-5">
                <div id="supervision-log" class="bg-white rounded-2xl p-6 shadow-xs space-y-5" x-data="{ openForm: {{ $consultations->isEmpty() ? 'true' : 'false' }} }">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-2 border-b border-slate-100">
                        <div>
                            <h3 class="font-bold text-base text-slate-900">
                                Riwayat Konsultasi Bimbingan DPL
                            </h3>
                        </div>

                        <div class="w-full sm:w-auto flex items-center justify-between sm:justify-end gap-2">
                            <span class="px-3 py-1 bg-slate-100 text-slate-700 text-xs font-semibold rounded-full">
                                {{ $consultations->count() }} Sesi
                            </span>
                            <button type="button" @click="openForm = !openForm" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                                <span x-text="openForm ? 'Tutup Form' : '+ Tambah Sesi Bimbingan'">+ Tambah Sesi Bimbingan</span>
                            </button>
                        </div>
                    </div>

                    <!-- Form Tambah Catatan Sesi Bimbingan -->
                    <div x-show="openForm" x-collapse x-cloak class="p-5 bg-slate-50 rounded-2xl space-y-4">
                        <form method="POST" action="{{ route('lecturer.consultations.store', $placement->id) }}" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Konsultasi <span class="text-rose-500">*</span></label>
                                    <input type="date" name="consultation_date" value="{{ date('Y-m-d') }}" required
                                           class="w-full text-xs bg-white border-0 ring-1 ring-slate-200 focus:ring-2 focus:ring-blue-600 rounded-xl py-2 px-3">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tahapan Bimbingan <span class="text-rose-500">*</span></label>
                                    <select name="stage" required class="w-full text-xs bg-white border-0 ring-1 ring-slate-200 focus:ring-2 focus:ring-blue-600 rounded-xl py-2 px-3 font-medium">
                                        <option value="Bab 1-3 (Pendahuluan & Metodologi)">Bab 1-3 (Pendahuluan & Metodologi)</option>
                                        <option value="Analisis Sistem & Pembahasan Masalah">Analisis Sistem & Pembahasan Masalah</option>
                                        <option value="Penyusunan Draf Laporan Akhir">Penyusunan Draf Laporan Akhir</option>
                                        <option value="Revisi & Penyempurnaan Bab Akhir">Revisi & Penyempurnaan Bab Akhir</option>
                                        <option value="Persiapan Ujian / Pasca-Magang">Persiapan Ujian / Pasca-Magang</option>
                                        <option value="Konsultasi Khusus & Evaluasi Kinerja">Konsultasi Khusus & Evaluasi Kinerja</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Topik Konsultasi <span class="text-rose-500">*</span></label>
                                <input type="text" name="topic" placeholder="Contoh: Pembahasan Bab 2 Landasan Teori" required
                                       class="w-full text-xs bg-white border-0 ring-1 ring-slate-200 focus:ring-2 focus:ring-blue-600 rounded-xl py-2 px-3">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan & Masukan DPL <span class="text-rose-500">*</span></label>
                                <textarea name="notes" rows="3" placeholder="Tuliskan arahan revisi atau masukan untuk mahasiswa..." required
                                          class="w-full text-xs bg-white border-0 ring-1 ring-slate-200 focus:ring-2 focus:ring-blue-600 rounded-xl py-2 px-3"></textarea>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-1">
                                <button type="button" @click="openForm = false" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition cursor-pointer">
                                    Batal
                                </button>
                                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                                    Simpan Catatan
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Timeline Sesi Bimbingan -->
                    <div class="space-y-3">
                        @forelse ($consultations as $session)
                            <div class="p-4 rounded-xl bg-slate-50 space-y-2">
                                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 pb-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="px-2.5 py-0.5 bg-slate-200 text-slate-700 rounded-full text-xs font-bold">
                                            Sesi #{{ $loop->iteration }}
                                        </span>
                                        <span class="text-xs font-semibold text-slate-800">
                                            {{ \Carbon\Carbon::parse($session->consultation_date)->translatedFormat('l, d F Y') }}
                                        </span>
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] bg-slate-200/80 text-slate-600">
                                            {{ $session->stage }}
                                        </span>
                                    </div>

                                    <form method="POST" action="{{ route('lecturer.consultations.destroy', $session->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan sesi bimbingan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-slate-400 hover:text-rose-600 px-2 py-1 transition cursor-pointer" title="Hapus catatan sesi">
                                            Hapus
                                        </button>
                                    </form>
                                </div>

                                <div class="space-y-1.5">
                                    <h5 class="text-xs font-bold text-slate-900 leading-snug">
                                        Topik: <span class="font-normal text-slate-700">{{ $session->topic }}</span>
                                    </h5>
                                    <div class="p-3.5 rounded-xl bg-white shadow-2xs text-xs text-slate-700 leading-relaxed italic">
                                        "{{ $session->notes }}"
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-slate-400 text-xs bg-slate-50 rounded-2xl p-6 space-y-1">
                                <p class="font-semibold text-slate-700 text-sm">Belum Ada Sesi Bimbingan</p>
                                <p class="text-xs text-slate-500">Gunakan tombol "+ Tambah Sesi Bimbingan" di atas untuk mencatat konsultasi naskah dan telaah akademik bersama mahasiswa.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
