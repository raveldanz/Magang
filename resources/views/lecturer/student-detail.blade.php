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

            <!-- 1. HERO PROFILE CARD (Clean, Professional, Human-Crafted) -->
            <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-xs">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                    <div class="flex items-start gap-4 sm:gap-5">
                        <!-- Initials Avatar -->
                        <div class="w-13 h-13 sm:w-16 sm:h-16 rounded-2xl bg-blue-50 text-blue-700 font-black text-xl sm:text-2xl flex items-center justify-center shrink-0">
                            {{ $studentInitial }}
                        </div>

                        <div class="space-y-2">
                            <!-- Status & Keaktifan Badges -->
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-800">
                                    Status: {{ $statusLabel }}
                                </span>

                                @if ($isFinished)
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-600">
                                        Magang Selesai
                                    </span>
                                @elseif ($daysRemaining !== null)
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $daysRemaining <= 7 ? 'bg-amber-50 text-amber-800' : 'bg-emerald-50 text-emerald-800' }}">
                                        Sisa {{ $daysRemaining }} Hari
                                    </span>
                                @endif

                                @if ($latestLogbook)
                                    @if ($daysSinceLastLogbook === 0)
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-800">
                                            Logbook: Hari ini
                                        </span>
                                    @elseif ($daysSinceLastLogbook === 1)
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-800">
                                            Logbook: Kemarin
                                        </span>
                                    @elseif ($daysSinceLastLogbook <= 4)
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700">
                                            Logbook: {{ $daysSinceLastLogbook }} hari lalu
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-800" title="Mahasiswa belum mengisi logbook baru dalam {{ $daysSinceLastLogbook }} hari">
                                            Perlu Diingatkan ({{ $daysSinceLastLogbook }} hari belum isi)
                                        </span>
                                    @endif
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-800">
                                        Belum Ada Logbook
                                    </span>
                                @endif

                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-600">
                                    {{ $isMentorOnly ? 'Skema 100% Mentor' : 'Skema DPL + Mentor' }}
                                </span>
                            </div>

                            <!-- Identitas Mahasiswa -->
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 leading-tight">
                                    {{ $student->name }}
                                </h1>
                                <p class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">
                                    NIM {{ $profile->nim ?? '-' }} &bull; {{ $profile->jurusan ?? '-' }} &bull; {{ $profile->universitas ?? ($univ->name ?? '-') }}
                                </p>
                            </div>

                            <!-- Meta Penempatan -->
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500 pt-0.5">
                                <span>Instansi: <strong class="text-slate-800 font-semibold">{{ $agency->agency_name ?? '-' }} ({{ $unit->name ?? '-' }})</strong></span>
                                <span>&bull;</span>
                                <span>Mentor: <strong class="text-slate-800 font-semibold">{{ $mentor->name ?? '-' }}</strong></span>
                                <span>&bull;</span>
                                <span>Periode: <strong class="text-slate-800 font-semibold">{{ $app?->start_date ? \Carbon\Carbon::parse($app->start_date)->translatedFormat('d M Y') : '-' }} &ndash; {{ $app?->end_date ? \Carbon\Carbon::parse($app->end_date)->translatedFormat('d M Y') : '-' }}</strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi Komunikasi & WhatsApp -->
                    <div class="w-full lg:w-auto flex flex-wrap items-center gap-2 pt-2 lg:pt-0">
                        @if ($cleanStudentPhone)
                            <a href="https://wa.me/{{ $cleanStudentPhone }}?text={{ $waMessage }}" target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold rounded-xl transition cursor-pointer"
                               title="Kirim pesan WhatsApp langsung ke {{ $student->name }}">
                                <span>WhatsApp</span>
                            </a>
                        @endif

                        <x-chat-button :user="$student" label="Chat" />
                        @if ($placement->mentor ?? $placement->pembimbing)
                            <x-chat-button :user="$placement->mentor ?? $placement->pembimbing" label="Chat Mentor" />
                        @endif
                        <x-chat-group-button :placement="$placement" />
                    </div>
                </div>
            </div>

            <!-- 2. TABBED NAVIGATION (Responsive Grid on Mobile: No Horizontal Scrollbar) -->
            <div class="grid grid-cols-2 sm:flex sm:items-center gap-1.5 p-1.5 bg-slate-100 rounded-2xl">
                <button type="button" @click="activeTab = 'evaluasi'"
                        :class="activeTab === 'evaluasi' 
                            ? 'bg-white text-blue-700 shadow-xs font-bold' 
                            : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-3.5 py-2.5 rounded-xl text-xs sm:text-sm transition cursor-pointer text-center">
                    Evaluasi & Nilai
                </button>

                <button type="button" @click="activeTab = 'logbook'"
                        :class="activeTab === 'logbook' 
                            ? 'bg-white text-blue-700 shadow-xs font-bold' 
                            : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-3.5 py-2.5 rounded-xl text-xs sm:text-sm transition cursor-pointer text-center">
                    Logbook ({{ $logbookTotal }})
                </button>

                <button type="button" @click="activeTab = 'laporan'"
                        :class="activeTab === 'laporan' 
                            ? 'bg-white text-blue-700 shadow-xs font-bold' 
                            : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-3.5 py-2.5 rounded-xl text-xs sm:text-sm transition cursor-pointer text-center">
                    Laporan Akhir
                    @if($isReportApproved)
                        <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] bg-emerald-100 text-emerald-800">ACC</span>
                    @elseif($finalReport)
                        <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] bg-amber-100 text-amber-800">Review</span>
                    @endif
                </button>

                <button type="button" @click="activeTab = 'bimbingan'"
                        :class="activeTab === 'bimbingan' 
                            ? 'bg-white text-blue-700 shadow-xs font-bold' 
                            : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-3.5 py-2.5 rounded-xl text-xs sm:text-sm transition cursor-pointer text-center">
                    Bimbingan ({{ $consultations->count() }})
                </button>
            </div>

            <!-- ========================================================================= -->
            <!-- TAB 1: EVALUASI & PENILAIAN -->
            <!-- ========================================================================= -->
            <div x-show="activeTab === 'evaluasi'" class="space-y-6">
                <!-- Status Kelengkapan Berkas Magang (Clean & Non-Robotic) -->
                <div class="bg-white rounded-2xl p-5 sm:p-6 shadow-xs space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <h3 class="font-bold text-slate-900 text-sm">
                            Status Kelengkapan Berkas
                        </h3>
                        <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-3 py-1 rounded-full">
                            {{ $readyCount }} dari 4 Syarat Terpenuhi
                        </span>
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3 text-xs">
                        <div class="p-3.5 sm:p-4 rounded-xl {{ $readyLogbook ? 'bg-emerald-50/70 text-emerald-950' : 'bg-slate-50 text-slate-700' }}">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-slate-900">Logbook Harian</span>
                                <span class="font-bold {{ $readyLogbook ? 'text-emerald-700' : 'text-slate-400' }}">{{ $readyLogbook ? 'Terisi' : 'Kosong' }}</span>
                            </div>
                            <span class="text-[11px] text-slate-500 mt-1 block">{{ $logbookTotal }} hari kerja</span>
                        </div>

                        <div class="p-3.5 sm:p-4 rounded-xl {{ $readyReport ? 'bg-emerald-50/70 text-emerald-950' : 'bg-slate-50 text-slate-700' }}">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-slate-900">Laporan Akhir</span>
                                <span class="font-bold {{ $readyReport ? 'text-emerald-700' : 'text-slate-400' }}">{{ $readyReport ? 'Disetujui' : ($finalReport ? 'Proses' : 'Belum') }}</span>
                            </div>
                            <span class="text-[11px] text-slate-500 mt-1 block">{{ $readyReport ? 'Sudah ACC' : ($finalReport ? 'Menunggu review' : 'Belum unggah') }}</span>
                        </div>

                        <div class="p-3.5 sm:p-4 rounded-xl {{ $readyLecturerScore ? 'bg-emerald-50/70 text-emerald-950' : 'bg-slate-50 text-slate-700' }}">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-slate-900">Nilai DPL</span>
                                <span class="font-bold {{ $readyLecturerScore ? 'text-emerald-700' : 'text-amber-700' }}">{{ $readyLecturerScore ? 'Lengkap' : 'Belum' }}</span>
                            </div>
                            <span class="text-[11px] text-slate-500 mt-1 block">{{ $isMentorOnly ? '100% Mentor' : ($hasEval ? "Nilai: {$eval->nilai_dosen_calculated}" : 'Belum diinput') }}</span>
                        </div>

                        <div class="p-3.5 sm:p-4 rounded-xl {{ $readyMentorScore ? 'bg-emerald-50/70 text-emerald-950' : 'bg-slate-50 text-slate-700' }}">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-slate-900">Nilai Mentor</span>
                                <span class="font-bold {{ $readyMentorScore ? 'text-emerald-700' : 'text-amber-700' }}">{{ $readyMentorScore ? 'Lengkap' : 'Belum' }}</span>
                            </div>
                            <span class="text-[11px] text-slate-500 mt-1 block">{{ $readyMentorScore ? "Nilai: {$eval->nilai_pembimbing}" : 'Menunggu mentor' }}</span>
                        </div>
                    </div>
                </div>

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
                                    Nilai Dosen Pembimbing (DPL)
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
                                <div class="grid grid-cols-3 gap-2.5 text-center">
                                    <div class="p-3 bg-white rounded-xl shadow-2xs">
                                        <span class="text-[11px] text-slate-400 font-medium block">Materi</span>
                                        <span class="text-base font-bold text-slate-900">{{ $eval?->score_mastery ?? '-' }}</span>
                                    </div>
                                    <div class="p-3 bg-white rounded-xl shadow-2xs">
                                        <span class="text-[11px] text-slate-400 font-medium block">Laporan</span>
                                        <span class="text-base font-bold text-slate-900">{{ $eval?->score_report ?? '-' }}</span>
                                    </div>
                                    <div class="p-3 bg-white rounded-xl shadow-2xs">
                                        <span class="text-[11px] text-slate-400 font-medium block">Sikap</span>
                                        <span class="text-base font-bold text-slate-900">{{ $eval?->score_attitude ?? '-' }}</span>
                                    </div>
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

                            <div class="grid grid-cols-3 gap-2.5 text-center">
                                <div class="p-3 bg-white rounded-xl shadow-2xs">
                                    <span class="text-[11px] text-slate-400 font-medium block">Disiplin</span>
                                    <span class="text-base font-bold text-slate-900">{{ $eval?->nilai_disiplin ?? '-' }}</span>
                                </div>
                                <div class="p-3 bg-white rounded-xl shadow-2xs">
                                    <span class="text-[11px] text-slate-400 font-medium block">Kinerja</span>
                                    <span class="text-base font-bold text-slate-900">{{ $eval?->nilai_kinerja ?? '-' }}</span>
                                </div>
                                <div class="p-3 bg-white rounded-xl shadow-2xs">
                                    <span class="text-[11px] text-slate-400 font-medium block">Laporan</span>
                                    <span class="text-base font-bold text-slate-900">{{ $eval?->nilai_laporan ?? '-' }}</span>
                                </div>
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
                            <h4 class="font-bold text-sm text-slate-900">Catatan Pribadi Pembimbing (DPL)</h4>
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
