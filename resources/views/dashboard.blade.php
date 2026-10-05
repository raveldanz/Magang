<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl sm:text-2xl text-slate-800 leading-tight">
            {{ __('Dashboard Mahasiswa') }}
        </h2>
    </x-slot>

    <div class="py-8"
        x-data="{ openNewDosenModal: false, showCredentialModal: {{ session('new_advisor_credential') ? 'true' : 'false' }}, copied: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Success Message -->
            @if (session('success'))
                <div
                    class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-2xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @php
                $profile = Auth::user()->studentProfile;
                $application = $application ?? ($profile ? App\Models\Application::where('user_id', Auth::id())->latest()->first() : null);
                $placement = $application ? $application->placement : null;
                $eval = optional($placement)->evaluation;
                $finalReport = optional($placement)->finalreport ?? optional($placement)->finalReport;
                $mentor = $placement ? ($placement->mentor ?? $placement->pembimbing) : null;
                $academicAdvisor = $placement ? ($placement->academicAdvisor ?? $placement->dosen) : null;
                $logbooksCount = $placement ? ($placement->logbooks ? $placement->logbooks->count() : 0) : 0;

                // Hitung target hari kerja secara dinamis berdasarkan periode magang diajukan
                $targetDays = 30;
                if ($application && $application->start_date && $application->end_date) {
                    try {
                        $start = \Carbon\Carbon::parse($application->start_date);
                        $end = \Carbon\Carbon::parse($application->end_date);
                        $diff = $start->diffInDaysFiltered(function (\Carbon\Carbon $date) {
                            return !$date->isWeekend();
                        }, $end);
                        if ($diff > 0) {
                            $targetDays = (int) $diff;
                        }
                    } catch (\Exception $e) {
                        $targetDays = 30;
                    }
                }

                $appStatusVal = $application ? ($application->status instanceof \BackedEnum ? $application->status->value : $application->status) : null;
                $rawSt = strtolower($appStatusVal ?? '');

                $finalReportStatusVal = $finalReport ? ($finalReport->status instanceof \BackedEnum ? $finalReport->status->value : $finalReport->status) : null;
                $rawFinalSt = strtolower($finalReportStatusVal ?? '');

                // Certificate Gate (Application::certificateBlockers): evaluasi mentor lengkap + laporan ACC + status COMPLETED
                $certificateBlockers = $application ? $application->certificateBlockers() : ['Belum ada pengajuan magang.'];
                $isPassed = $application && $certificateBlockers === [];

                // Nama status untuk banner: kode sistem standar (sama di semua role)
                $appStatusLabel = $application
                    ? (\App\Enums\ApplicationStatus::resolve($rawSt)?->label() ?? strtoupper($rawSt))
                    : 'Registrasi Akun';

                // Hitung persentase progress operasional
                $stepCount = 0;
                if (!empty($profile?->nim))
                    $stepCount++;
                if (!empty($application) && !in_array($rawSt, ['resigned', 'rejected']))
                    $stepCount++;
                if ($application && in_array($rawSt, ['accepted', 'active', 'completed']))
                    $stepCount++;
                if (!empty($academicAdvisor) && !in_array($rawSt, ['resigned', 'rejected']))
                    $stepCount++;
                if ($logbooksCount > 0)
                    $stepCount++;
                if ($finalReport && $rawFinalSt === 'approved')
                    $stepCount++;
                if ($isPassed)
                    $stepCount++;
                $progressPercent = round(($stepCount / 7) * 100);
            @endphp

            <!-- 1. EXECUTIVE CIVIC BANNER (MATCHING PEMKOT SURABAYA ROYAL BLUE BRANDING) -->
            <div x-data="{ showDetailModal: false }" class="space-y-6">
                <div
                    class="rounded-2xl p-6 sm:p-8 text-white shadow-md relative overflow-hidden flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 bg-gradient-to-r from-blue-700 via-blue-800 to-indigo-900 border border-blue-900">
                    <div class="space-y-3 z-10 max-w-2xl">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold tracking-wide bg-white/15 border border-white/20 text-white">
                                    <span>Status: {{ $appStatusLabel }}</span>
                                </span>
                            </div>
                            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                                Selamat Datang, {{ Auth::user()->name }}!
                            </h1>
                            <p class="text-xs sm:text-sm leading-relaxed text-blue-100">
                                Portal Terpadu Pelaksanaan Magang & Praktik Kerja Lapangan Pemerintah Kota Surabaya
                            </p>
                        </div>

                        <!-- Executive Info Chips (Sharp Rectangular Badges, Zero Circles) -->
                        <div class="flex flex-wrap items-center gap-2 pt-1 text-xs">
                            <div
                                class="px-3 py-1.5 rounded-md font-medium flex items-center gap-1.5 bg-white/10 border border-white/20 text-white">
                                <span class="text-blue-200">NIM:</span>
                                <strong class="font-mono text-white">{{ $profile->nim ?? 'Belum Diisi' }}</strong>
                            </div>

                            <div
                                class="px-3 py-1.5 rounded-md font-medium flex items-center gap-1.5 bg-white/10 border border-white/20 text-white">
                                <span class="text-blue-200">Logbook:</span>
                                <strong class="text-amber-300">{{ $logbooksCount }} Entri Tercatat</strong>
                            </div>

                            @if($academicAdvisor)
                                <div
                                    class="px-3 py-1.5 rounded-md font-medium flex items-center gap-1.5 bg-white/10 border border-white/20 text-white">
                                    <span class="text-blue-200">DPL:</span>
                                    <strong class="text-white">{{ $academicAdvisor->name }}</strong>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Right Side: Sleek Progress Bar with "Lihat Detail" -->
                    <div class="w-full lg:w-80 shrink-0 p-4 rounded-xl bg-white/10 border border-white/20 space-y-3">
                        <div class="flex items-center justify-between text-xs text-white">
                            <span class="text-blue-200">Institusi:</span>
                            <span class="font-bold truncate max-w-[160px]">
                                {{ $univName ?? $profile->universitas ?? 'Perguruan Tinggi' }}
                            </span>
                        </div>

                        <div class="space-y-1.5 pt-2 border-t border-white/15">
                            <div class="flex items-center justify-between text-xs font-bold text-white">
                                <span>Kelengkapan Berkas Magang</span>
                                <span class="text-amber-300 font-mono">{{ $progressPercent }}%</span>
                            </div>
                            <div class="w-full bg-black/20 rounded h-2 overflow-hidden">
                                <div class="h-2 bg-emerald-400 rounded transition-all duration-500"
                                    style="width: {{ $progressPercent }}%;"></div>
                            </div>
                            <div class="pt-1 flex items-center justify-between text-[11px]">
                                <span class="text-blue-200">{{ $stepCount }} dari 7 Syarat Terpenuhi</span>
                                <button type="button" @click="showDetailModal = true"
                                    class="font-bold text-white hover:text-amber-300 underline transition cursor-pointer flex items-center gap-1">
                                    <span>Lihat Detail</span>
                                    <span></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                @include('student.dashboard.progress-modal')

                @include('student.dashboard.graduation-banner')

                @include('student.dashboard.advisor-section')

                @include('student.dashboard.status-overview')


            </div>
        </div>
</x-app-layout>