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

                // Certificate Gate (Application::certificateBlockers): evaluasi mentor lengkap + laporan ACC
                // + logbook terisi + status COMPLETED. Tombol unduh hanya aktif bila daftar blocker kosong.
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
                            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                                Selamat Datang, {{ Auth::user()->name }}!
                            </h1>
                            <p class="text-xs sm:text-sm leading-relaxed text-blue-100">
                                Portal Terpadu Pelaksanaan Magang & Praktik Kerja Lapangan Pemerintah Kota Surabaya
                            </p>
                        </div>

                        <!-- Executive Info Chips (Desktop Only: Sharp Rectangular Badges, Zero Circles) -->
                        <div class="hidden md:flex flex-wrap items-center gap-2 pt-1 text-xs">
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
                                    <span class="text-blue-200">Dosen Pembimbing:</span>
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

                <!-- MODAL: RINCIAN KELENGKAPAN & KEKURANGAN BERKAS MAGANG -->
                <div x-show="showDetailModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
                    aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    @php
                        $isAccepted = !empty($application) && in_array($rawSt, ['accepted', 'active', 'completed'], true);
                        $canProceed = $isAccepted && !empty($academicAdvisor);
                    @endphp
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <!-- Backdrop -->
                        <div x-show="showDetailModal" x-transition:enter="ease-out duration-200"
                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0" @click="showDetailModal = false"
                            class="fixed inset-0 bg-slate-900/60 transition-opacity"></div>

                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                            aria-hidden="true">&#8203;</span>

                        <div x-show="showDetailModal" x-transition:enter="ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave="ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                            class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200">

                            <!-- Header Modal -->
                            <div
                                class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                                <div>
                                    <h3 class="text-sm font-bold text-slate-800" id="modal-title">Rincian Kelengkapan &
                                        Kekurangan Berkas Magang</h3>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $stepCount }} dari 7 syarat telah
                                        terpenuhi ({{ $progressPercent }}%)</p>
                                </div>
                                <button type="button" @click="showDetailModal = false"
                                    class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg border border-slate-200 hover:bg-white transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- List Status & Kekurangan dengan Navigasi Arahan -->
                            <div class="p-6 divide-y divide-slate-100 text-xs max-h-[70vh] overflow-y-auto space-y-1">

                                <!-- Box Arahan Langkah Selanjutnya (Next Actionable Step) -->
                                <div class="pb-3">
                                    <div
                                        class="p-3.5 bg-blue-50 border border-blue-200 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div class="space-y-0.5">
                                            <span
                                                class="text-[11px] font-bold uppercase tracking-wider text-blue-800 block">
                                                Arahan Tahapan Anda Saat Ini:
                                            </span>
                                            <p class="text-slate-700 leading-relaxed font-medium">
                                                @if(empty($profile?->nim))
                                                    Lengkapi biodata dan Nomor Induk Mahasiswa (NIM) terlebih dahulu.
                                                @elseif(!$application)
                                                    Anda belum mengajukan magang. Silakan buat permohonan magang untuk memulai program magang.
                                                @elseif(in_array($rawSt, ['resigned', 'rejected'], true))
                                                    {{ $rawSt === 'rejected' ? 'Pengajuan magang Anda sebelumnya ditolak.' : 'Anda telah mengundurkan diri dari magang sebelumnya.' }}
                                                    Silakan buat permohonan magang baru untuk melanjutkan program magang.
                                                @elseif($rawSt === 'verified')
                                                    Berkas Anda sudah lolos verifikasi. Menunggu keputusan penerimaan dari instansi dinas terkait.
                                                @elseif(!in_array($rawSt, ['accepted', 'active', 'completed'], true))
                                                    Menunggu verifikasi dan persetujuan dari instansi dinas terkait.
                                                @elseif($isPassed)
                                                    Selamat! Seluruh kewajiban telah terpenuhi. E-Sertifikat resmi siap
                                                    diunduh.
                                                @elseif(!$academicAdvisor)
                                                    Pengajuan diterima! Silakan <strong>pilih Dosen Pembimbing</strong> dari perguruan tinggi Anda.
                                                @elseif($logbooksCount < 30)
                                                    Terus catat aktivitas kerja harian Anda (sudah terisi
                                                    <strong>{{ $logbooksCount }} hari</strong>, tersisa
                                                    <strong>{{ max(0, 30 - $logbooksCount) }} hari kerja</strong>).
                                                @elseif(!$finalReport || $rawFinalSt !== 'approved')
                                                    Program magang Anda sedang berlangsung. Anda dapat mencatat aktivitas
                                                    harian (terisi <strong>{{ $logbooksCount }} hari</strong>) serta
                                                    mengunggah/mencicil draf Laporan Akhir kapan saja.
                                                @else
                                                    Laporan akhir Anda sudah disetujui. E-Sertifikat akan terbit setelah syarat berikut terpenuhi:
                                                    {{ implode(' ', $certificateBlockers) }}
                                                @endif
                                            </p>
                                        </div>

                                        {{-- Tombol Tindakan Cepat Dinamis Sesuai Tahapan --}}
                                        <div class="shrink-0">
                                            @if(empty($profile?->nim))
                                                <a href="{{ route('student.profile.edit') }}"
                                                    class="px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition inline-block">
                                                    Lengkapi Profil
                                                </a>
                                            @elseif(!$application || in_array($rawSt, ['resigned', 'rejected']))
                                                <a href="{{ route('student.application.create') }}"
                                                    class="px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition inline-block">
                                                    Daftar Magang
                                                </a>
                                            @elseif(!in_array($rawSt, ['accepted', 'active', 'completed']))
                                                <span
                                                    class="px-3 py-1 rounded-lg bg-amber-100 text-amber-800 text-xs font-bold border border-amber-200 inline-block">
                                                    Dalam Peninjauan
                                                </span>
                                            @elseif($isPassed)
                                                <a href="{{ route('student.certificate.show', $application->id) }}"
                                                    target="_blank"
                                                    class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition inline-block">
                                                    Unduh Sertifikat
                                                </a>
                                            @elseif(!$academicAdvisor)
                                                <a href="#change-advisor-box" @click="showDetailModal = false"
                                                    class="px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition inline-block">
                                                    Pilih Dosen
                                                </a>
                                            @elseif($logbooksCount < 30)
                                                <a href="{{ route('student.logbook.create') }}"
                                                    class="px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition inline-block">
                                                    + Isi Logbook
                                                </a>
                                            @elseif(!$finalReport || $rawFinalSt !== 'approved')
                                                <a href="{{ route('student.final_report.index') }}"
                                                    class="px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition inline-block">
                                                    Unggah Laporan
                                                </a>
                                            @else
                                                <span title="{{ implode(' ', $certificateBlockers) }}"
                                                    class="px-3 py-1 rounded-lg bg-amber-100 text-amber-800 text-xs font-bold border border-amber-200 inline-block cursor-help">
                                                    Menunggu Nilai &amp; Kelulusan
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <!-- 1. Profil -->
                                <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div>
                                        <div class="font-bold text-slate-800">1. Biodata & NIM Mahasiswa</div>
                                    </div>
                                    <div class="shrink-0">
                                        @if(!empty($profile?->nim))
                                            <span
                                                class="px-2.5 py-1 rounded-md font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-block">
                                                Lengkap
                                            </span>
                                        @else
                                            <a href="{{ route('student.profile.edit') }}"
                                                class="px-3 py-1.5 rounded-md font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition inline-block">
                                                Lengkapi Profil
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                <!-- 2. Unit Penempatan -->
                                <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div>
                                        <div class="font-bold text-slate-800">2. Penempatan Instansi & Unit Kerja</div>
                                    </div>
                                    <div class="shrink-0">
                                        @if($application && in_array($rawSt, ['accepted', 'active', 'completed'], true))
                                            <span class="px-2.5 py-1 rounded-md font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-block">
                                                Diterima
                                            </span>
                                        @elseif($application && in_array($rawSt, ['resigned', 'rejected'], true))
                                            <a href="{{ route('student.application.create') }}"
                                                class="px-3 py-1.5 rounded-md font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition inline-block">
                                                Buat Pengajuan Baru
                                            </a>
                                        @elseif($application)
                                            <span class="px-2.5 py-1 rounded-md font-bold bg-amber-50 text-amber-700 border border-amber-200 inline-block">
                                                {{ $rawSt === 'verified' ? 'Menunggu Keputusan Dinas' : 'Menunggu Verifikasi Dinas' }}
                                            </span>
                                        @else
                                            <a href="{{ route('student.application.create') }}"
                                                class="px-3 py-1.5 rounded-md font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition inline-block">
                                                Pilih Unit
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                <!-- 3. Dosen Pembimbing -->
                                <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div>
                                        <div class="font-bold text-slate-800">3. Dosen Pembimbing</div>
                                    </div>
                                    <div class="shrink-0">
                                        @if($academicAdvisor)
                                            <span
                                                class="px-2.5 py-1 rounded-md font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-block">
                                                Terdaftar
                                            </span>
                                        @elseif($isAccepted)
                                            <a href="#change-advisor-box" @click="showDetailModal = false; const box = document.getElementById('change-advisor-box'); if (box) { box.classList.remove('hidden'); box.scrollIntoView({behavior: 'smooth'}); }"
                                                class="px-3 py-1.5 rounded-md font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition inline-block">
                                                Pilih Dosen
                                            </a>
                                        @else
                                            <button type="button" disabled
                                                class="px-3 py-1.5 rounded-md font-bold bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed select-none inline-flex items-center gap-1 shadow-none"
                                                title="Lengkapi verifikasi pengajuan dan pilih dosen pembimbing terlebih dahulu">
                                                <span>Pilih Dosen</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <!-- 4. Logbook Kegiatan Harian -->
                                <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div>
                                        <div class="font-bold text-slate-800">4. Logbook Kegiatan Harian</div>
                                        <div class="text-slate-500 mt-0.5">
                                            Status: <strong>{{ $logbooksCount }} hari terisi</strong>
                                        </div>
                                    </div>
                                    <div class="shrink-0 flex items-center gap-2">
                                        @if ($canProceed)
                                            <a href="{{ route('student.logbook.create') }}"
                                                class="px-3 py-1.5 rounded-md font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition inline-flex items-center gap-1">
                                                <span>+ Isi Logbook</span>
                                            </a>
                                            <a href="{{ route('student.logbook.index') }}"
                                                class="px-3 py-1.5 rounded-md font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 transition inline-block">
                                                Lihat Riwayat
                                            </a>
                                        @else
                                          <button type="button" disabled
                                                class="px-3 py-1.5 rounded-md font-bold bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed select-none inline-flex items-center gap-1 shadow-none"
                                                title="Lengkapi verifikasi pengajuan dan pilih dosen pembimbing terlebih dahulu">
                                                <span>+ Isi Logbook</span>
                                            </button>
                                            <button type="button" disabled
                                                class="px-3 py-1.5 rounded-md font-bold bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed select-none inline-block shadow-none"
                                                title="Lengkapi verifikasi pengajuan dan pilih dosen pembimbing terlebih dahulu">
                                                Lihat Riwayat
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <!-- 5. Laporan Akhir Ilmiah Magang -->
                                <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div>
                                        <div class="font-bold text-slate-800">5. Laporan Akhir Ilmiah Magang</div>
                                        <div class="text-slate-500 mt-0.5">Dokumen pertanggungjawaban kegiatan magang
                                            yang disahkan Dosen Pembimbing dan Mentor</div>
                                    </div>
                                    <div class="shrink-0">
                                        @if (!$canProceed)
                                            <button type="button" disabled
                                                class="px-3 py-1.5 rounded-md font-bold bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed select-none inline-flex items-center gap-1 shadow-none"
                                                title="Lengkapi verifikasi pengajuan dan pilih dosen pembimbing terlebih dahulu">
                                                <span>Unggah Laporan</span>
                                            </button>
                                        @elseif ($finalReport && $rawFinalSt === 'approved')
                                            <span
                                                class="px-2.5 py-1 rounded-md font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-block">
                                                Disetujui
                                            </span>
                                        @elseif ($finalReport)
                                            <a href="{{ route('student.final_report.index') }}"
                                                class="px-3 py-1.5 rounded-md font-bold bg-amber-100 hover:bg-amber-200 text-amber-900 border border-amber-300 transition inline-block">
                                                Cek Status Review
                                            </a>
                                        @else
                                            <a href="{{ route('student.final_report.index') }}"
                                                class="px-3 py-1.5 rounded-md font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition inline-block">
                                                Unggah Laporan
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                <!-- 6. Nilai & Sertifikat -->
                                <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div>
                                        <div class="font-bold text-slate-800">6. Nilai Kelulusan & E-Sertifikat Resmi</div>
                                    </div>
                                    <div class="shrink-0">
                                        @if($isPassed)
                                            <a href="{{ route('student.certificate.show', $application->id) }}"
                                                target="_blank"
                                                class="px-3.5 py-1.5 rounded-md font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition inline-block">
                                                Unduh E-Sertifikat
                                            </a>
                                        @else
                                            <button type="button" disabled
                                                class="px-3 py-1.5 rounded-md font-bold bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed select-none inline-flex items-center gap-1 shadow-none"
                                                title="Sertifikat belum dapat diunduh: {{ implode(' ', $certificateBlockers) }}">
                                                <span>Unduh E-Sertifikat</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                            </div>

                            <!-- Footer Modal (Rapi & Selaras) -->
                            <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-200 flex justify-end">
                                <button type="button" @click="showDetailModal = false"
                                    class="px-5 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-bold rounded-xl hover:bg-slate-100 hover:text-slate-900 transition shadow-xs cursor-pointer">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. BANNER KELULUSAN RESMI & UNDUH E-SERTIFIKAT (TEMA EMERALD-SURABAYA BLUE SOLID) -->
                @if ($isPassed)
                    <div class="rounded-3xl p-6 sm:p-8 text-white shadow-xl space-y-6 relative overflow-hidden"
                        style="background: linear-gradient(135deg, #065f46 0%, #047857 50%, #1e3a8a 100%) !important; color: #ffffff !important;">
                        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                            <div class="flex items-start space-x-4">
                                <div class="space-y-1">
                                    <h2 class="text-xl sm:text-2xl font-black" style="color: #ffffff !important;">
                                        Selamat, {{ Auth::user()->name }}! Anda Telah Lulus Magang MBKM
                                    </h2>
                                    <p class="text-xs sm:text-sm max-w-2xl leading-relaxed"
                                        style="color: #d1fae5 !important;">
                                        Seluruh kewajiban logbook harian, laporan akhir ilmiah, dan evaluasi instansi dinas
                                        serta bimbingan akademik dosen pembimbing telah lengkap. E-Sertifikat resmi kelulusan
                                        telah
                                        diterbitkan.
                                    </p>
                                </div>
                            </div>

                            <div class="shrink-0 w-full md:w-auto">
                                <a href="{{ route('student.certificate.show', $application->id) }}" target="_blank"
                                    class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-2xl shadow-xl transition transform hover:scale-105 active:scale-95 cursor-pointer font-black text-xs sm:text-sm"
                                    style="background-color: #ffffff !important; color: #065f46 !important; border: 2px solid #ffffff !important;">
                                    <span>Cetak Sertifikat Resmi</span>
                                </a>
                            </div>
                        </div>

                        <!-- Rekapitulasi Nilai Transparan -->
                        @if($eval)
                            @php
                                $nilaiDinas = $eval->nilai_pembimbing ?? round((($eval->nilai_disiplin ?? 0) + ($eval->nilai_kinerja ?? 0) + ($eval->nilai_laporan ?? 0)) / 3, 1);
                                $nilaiDosen = $eval->nilai_dosen_calculated ?? ($eval->nilai_akademik ?? 0);
                                $nilaiAkhir = $eval->nilai_akhir;
                                $grade = $eval->grade_calculated ?? ($eval->grade ?? ($nilaiAkhir >= 85 ? 'A' : ($nilaiAkhir >= 70 ? 'B' : 'C')));
                            @endphp
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-4 border-t text-center"
                                style="border-top-color: rgba(255, 255, 255, 0.2) !important;">
                                <div class="p-3.5 rounded-2xl"
                                    style="background-color: rgba(255, 255, 255, 0.15) !important; border: 1px solid rgba(255, 255, 255, 0.25) !important;">
                                    <span class="text-[10px] font-bold uppercase tracking-wider block"
                                        style="color: #a7f3d0 !important;">Nilai Dinas</span>
                                    <p class="text-xl sm:text-2xl font-black mt-1" style="color: #ffffff !important;">
                                        {{ $nilaiDinas }}/100
                                    </p>
                                </div>
                                <div class="p-3.5 rounded-2xl"
                                    style="background-color: rgba(255, 255, 255, 0.15) !important; border: 1px solid rgba(255, 255, 255, 0.25) !important;">
                                    <span class="text-[10px] font-bold uppercase tracking-wider block"
                                        style="color: #a7f3d0 !important;">Nilai Dosen Pembimbing</span>
                                    <p class="text-xl sm:text-2xl font-black mt-1" style="color: #ffffff !important;">
                                        {{ $nilaiDosen }}/100
                                    </p>
                                </div>
                                <div class="p-3.5 rounded-2xl"
                                    style="background-color: rgba(255, 255, 255, 0.2) !important; border: 1px solid rgba(255, 255, 255, 0.35) !important;">
                                    <span class="text-[10px] font-bold uppercase tracking-wider block"
                                        style="color: #fde047 !important;">Nilai Akhir Total</span>
                                    <p class="text-xl sm:text-2xl font-black mt-1" style="color: #fde047 !important;">
                                        {{ $nilaiAkhir }}
                                    </p>
                                </div>
                                <div class="p-3.5 rounded-2xl"
                                    style="background-color: rgba(255, 255, 255, 0.2) !important; border: 1px solid rgba(255, 255, 255, 0.35) !important;">
                                    <span class="text-[10px] font-bold uppercase tracking-wider block"
                                        style="color: #fde047 !important;">Grade Kelulusan</span>
                                    <p class="text-xl sm:text-2xl font-black mt-1" style="color: #ffffff !important;">
                                        {{ $grade }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif



                <!-- Modal Input Dosen Baru -->
                <div x-show="openNewDosenModal" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 z-[9999] overflow-y-auto"
                    style="display: none;">

                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm transition-opacity"
                        @click="openNewDosenModal = false"></div>

                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                        <div
                            class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100 p-6 sm:p-7 space-y-4 my-auto max-h-[90vh] overflow-y-auto">

                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-lg font-bold text-slate-900 leading-6">Input Dosen Pembimbing Baru
                                    </h3>
                                </div>
                                <button type="button" @click="openNewDosenModal = false"
                                    class="text-slate-400 hover:text-slate-600 text-sm cursor-pointer">
                                    ✕
                                </button>
                            </div>

                            <p class="text-xs text-slate-500 leading-relaxed">
                                Jika dosen pembimbing Anda belum terdaftar di sistem, silakan isi data di bawah ini.
                                Akun
                                portal dosen akan otomatis dibuatkan dan terhubung ke universitas Anda.
                            </p>

                            <form action="{{ route('student.create_advisor') }}" method="POST" class="space-y-4">
                                @csrf
                                <input type="hidden" name="university_id" value="{{ Auth::user()->university_id }}">

                                <div>
                                    <label for="modal_name"
                                        class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                        Nama Lengkap Beserta Gelar <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" id="modal_name" name="name" required
                                        placeholder="Contoh: Dr. Ir. Ahmad Sudrajat, M.Kom"
                                        class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                                </div>

                                <div>
                                    <label for="modal_email"
                                        class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                        Email Resmi / Kampus Dosen <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="email" id="modal_email" name="email" required
                                        placeholder="Contoh: ahmad.sudrajat@kampus.ac.id"
                                        class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                                </div>

                                <div>
                                    <label for="modal_nidn"
                                        class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                        NIDN / NIP Dosen (Opsional)
                                    </label>
                                    <input type="text" id="modal_nidn" name="nidn" placeholder="Contoh: 0012345678"
                                        class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                        Asal Perguruan Tinggi:
                                    </label>
                                    <input type="text"
                                        value="{{ $univName ?? $profile->universitas ?? 'Universitas Mahasiswa' }}"
                                        disabled
                                        class="w-full text-xs bg-slate-100 text-slate-600 border-slate-200 rounded-xl shadow-2xs cursor-not-allowed">
                                </div>

                                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                                    <button type="button" @click="openNewDosenModal = false"
                                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer">
                                        Batal
                                    </button>
                                    <button type="submit"
                                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition cursor-pointer">
                                        Daftarkan & Pilih Sebagai Dosen Pembimbing
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>

                <!-- Modal Popup Kredensial Akun Dosen Baru -->
                @if (session('new_advisor_credential'))
                    @php
                        $cred = session('new_advisor_credential');
                        $waText = "Halo Bapak/Ibu {$cred['name']},\n\nBerikut adalah akun akses Portal Dosen Pembimbing Magang Anda:\n- Portal Login: {$cred['login_url']}\n- Email: {$cred['email']}\n- Password: {$cred['password']}\n\nSilakan login untuk memonitor logbook mingguan dan memberikan nilai akhir magang mahasiswa. Terima kasih.";
                    @endphp
                    <div x-show="showCredentialModal" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0" class="fixed inset-0 z-[9999] overflow-y-auto"
                        style="display: none;">

                        <!-- Backdrop -->
                        <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm transition-opacity"
                            @click="showCredentialModal = false"></div>

                        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                            <div
                                class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-emerald-100 p-6 sm:p-8 space-y-5 my-auto max-h-[90vh] overflow-y-auto">

                                <!-- Header Modal -->
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3">

                                        <div>
                                            <h3 class="text-lg font-black text-slate-900 leading-snug">Akun Dosen Pembimbing
                                                Berhasil Dibuat</h3>
                                            <p class="text-xs text-emerald-600 font-semibold">Tersambung ke
                                                {{ $cred['univ_name'] }}
                                            </p>
                                        </div>
                                    </div>
                                    <button type="button" @click="showCredentialModal = false"
                                        class="text-slate-400 hover:text-slate-600 text-lg p-1 cursor-pointer">
                                        ✕
                                    </button>
                                </div>

                                <!-- Box Kredensial -->
                                <div
                                    class="rounded-2xl bg-slate-900 text-slate-100 p-5 space-y-3 font-mono text-xs border border-slate-800 shadow-inner">
                                    <div class="flex justify-between items-center pb-2 border-b border-slate-800">
                                        <span
                                            class="text-slate-400 font-sans text-[11px] font-bold uppercase tracking-wider">Kredensial
                                            Akses Dosen</span>
                                        <span
                                            class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-sans font-bold">{{ \App\Enums\AccountStatus::ACTIVE->label() }}</span>
                                    </div>

                                    <div class="grid grid-cols-3 gap-1">
                                        <span class="text-slate-400 font-sans">Nama Dosen:</span>
                                        <span class="col-span-2 font-bold text-white">{{ $cred['name'] }}</span>
                                    </div>

                                    <div class="grid grid-cols-3 gap-1">
                                        <span class="text-slate-400 font-sans">Email Login:</span>
                                        <span
                                            class="col-span-2 font-bold text-amber-300 select-all">{{ $cred['email'] }}</span>
                                    </div>

                                    <div class="grid grid-cols-3 gap-1">
                                        <span class="text-slate-400 font-sans">Password Default:</span>
                                        <span
                                            class="col-span-2 font-bold text-emerald-300 select-all">{{ $cred['password'] }}</span>
                                    </div>

                                    <div class="grid grid-cols-3 gap-1">
                                        <span class="text-slate-400 font-sans">Portal Login:</span>
                                        <span
                                            class="col-span-2 font-bold text-sky-300 break-all select-all">{{ $cred['login_url'] }}</span>
                                    </div>
                                </div>

                                <!-- Instruksi Mahasiswa -->
                                <div
                                    class="p-4 bg-amber-50/80 rounded-2xl border border-amber-200/80 text-xs text-amber-900 flex items-start gap-3 leading-relaxed">
                                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <strong class="font-bold">Instruksi Mahasiswa:</strong>
                                        <p class="mt-1 text-amber-800">Harap simpan dan teruskan kredensial di atas kepada
                                            <strong>Dosen Pembimbing</strong> Anda agar beliau dapat login ke
                                            Portal Dosen untuk memonitor logbook mingguan dan memberikan penilaian akhir
                                            magang.
                                        </p>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex flex-col sm:flex-row items-center gap-3 pt-2">
                                    <button type="button"
                                        @click="navigator.clipboard.writeText(`{{ addslashes($waText) }}`); copied = true; setTimeout(() => copied = false, 3000)"
                                        class="w-full inline-flex justify-center items-center gap-2 px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md transition transform active:scale-98 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                        </svg>
                                        <span
                                            x-text="copied ? 'Berhasil Disalin ke Clipboard!' : 'Salin Informasi Login (WhatsApp)'">Salin
                                            Informasi Login</span>
                                    </button>

                                    <button type="button" @click="showCredentialModal = false"
                                        class="w-full sm:w-auto px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer">
                                        Tutup
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                @endif

                <!-- ==================================================== -->
                <!-- 4 & 5. DESKTOP VIEW: STATUS CARDS & DETAIL PENEMPATAN -->
                <!-- ==================================================== -->
                @php
                    $rawAppSt = $rawSt;
                @endphp
                <!-- ==================================================== -->
                <!-- 4 & 5. DESKTOP VIEW: STATUS CARDS & DETAIL PENEMPATAN -->
                <!-- ==================================================== -->
                <div class="hidden md:block space-y-6">

                    <!-- STATUS CARD GRID (3 KARTU INFORMASI UTAMA: STATUS, DOSEN, MENTOR - DESKTOP) -->
                    <div class="grid grid-cols-3 gap-6">

                        <!-- Card 1: Status Magang (Desktop) -->
                        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col justify-between">
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Magang</p>
                                <div class="mt-2">
                                    @if ($application)
                                        <div class="flex items-center gap-2">
                                            <x-status-badge :status="$application->status" />
                                        </div>
                                        <p class="text-xs text-slate-500 mt-2 font-medium line-clamp-1">
                                            {{ $application->unit->name ?? $application->unit->agencyProfile->agency_name ?? 'Program Magang' }}
                                        </p>
                                    @else
                                        <p class="text-base font-bold text-slate-800">Belum Mengajukan</p>
                                        <p class="text-xs text-slate-400 mt-1">Belum ada pengajuan magang aktif.</p>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-4 pt-2 border-t border-slate-50 flex items-center justify-between">
                                @if ($application && in_array($rawAppSt, ['accepted', 'active', 'completed'], true))
                                    <a href="{{ route('student.application.letter', $application->id) }}" target="_blank"
                                        class="text-xs font-bold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        <span>Surat Balasan</span>
                                    </a>
                                @elseif (!$application)
                                    <a href="{{ route('student.application.create') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                                        <span>Buat Pengajuan</span> &rarr;
                                    </a>
                                @elseif ($rawAppSt === 'rejected')
                                    <a href="{{ route('student.application.create') }}" class="text-xs font-bold text-rose-600 hover:text-rose-800 inline-flex items-center gap-1">
                                        <span>Ajukan Magang Baru</span> &rarr;
                                    </a>
                                @elseif ($rawAppSt === 'resigned')
                                    <a href="{{ route('student.application.create') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                                        <span>Daftar Magang Baru</span> &rarr;
                                    </a>
                                @else
                                    <span class="text-[11px] text-amber-600 font-semibold">Dalam proses verifikasi</span>
                                @endif
                            </div>
                        </div>

                        <!-- Card 2: Dosen Pembimbing (Desktop) -->
                        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Dosen Pembimbing</p>
                                    @if ($academicAdvisor)
                                        <span class="px-2.5 py-0.5 text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full">
                                            Terpilih
                                        </span>
                                    @elseif ($application && in_array($rawAppSt, ['accepted', 'active', 'completed'], true))
                                        <span class="px-2.5 py-0.5 text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 rounded-full animate-pulse">
                                            Perlu Ditentukan
                                        </span>
                                    @endif
                                </div>
                                <div class="mt-2">
                                    @if ($academicAdvisor)
                                        <p class="text-base font-bold text-slate-800 line-clamp-1" title="{{ $academicAdvisor->name }}">
                                            {{ $academicAdvisor->name }}
                                        </p>
                                        <div class="text-xs text-slate-500 mt-1 space-y-0.5">
                                            <p class="truncate"><span class="text-slate-400">Email:</span> <span class="font-mono text-slate-700">{{ $academicAdvisor->email }}</span></p>
                                            <p class="truncate"><span class="text-slate-400">Kampus:</span> <strong>{{ is_string($academicAdvisor->university) ? $academicAdvisor->university : ($academicAdvisor->universityRelation?->name ?? $academicAdvisor->university?->name ?? $univName ?? $profile->universitas) }}</strong></p>
                                        </div>
                                    @elseif ($application && in_array($rawAppSt, ['accepted', 'active', 'completed'], true))
                                        <p class="text-sm font-semibold text-slate-700">Belum memilih dosen pembimbing</p>
                                        <p class="text-xs text-slate-400 mt-1">Dosen akademik dari perguruan tinggi Anda.</p>
                                    @else
                                        <p class="text-sm font-semibold text-slate-400">Belum Tersedia</p>
                                        <p class="text-xs text-slate-400 mt-1">Tersedia setelah diterima magang.</p>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-4 pt-2 border-t border-slate-50 flex items-center justify-between">
                                @if ($application && in_array($rawAppSt, ['accepted', 'active', 'completed'], true))
                                    <button type="button"
                                        onclick="const el = document.getElementById('change-advisor-box'); el.classList.toggle('hidden'); if(!el.classList.contains('hidden')) el.scrollIntoView({behavior: 'smooth'});"
                                        class="text-xs font-bold text-blue-600 hover:text-blue-800 cursor-pointer">
                                        {{ $academicAdvisor ? 'Ganti Dosen Pembimbing' : 'Pilih Dosen Pembimbing' }} &rarr;
                                    </button>
                                @else
                                    <span class="text-[11px] text-slate-400">-</span>
                                @endif
                            </div>
                        </div>

                        <!-- Card 3: Mentor Lapangan (Desktop) -->
                        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col justify-between">
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Mentor Lapangan</p>
                                <div class="mt-2">
                                    <p class="text-base font-bold text-slate-800 line-clamp-1" title="{{ $mentor ? $mentor->name : 'Belum Diplot Dinas' }}">
                                        {{ $mentor ? $mentor->name : 'Belum Diplot Dinas' }}
                                    </p>
                                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                                        Ditugaskan resmi oleh instansi penempatan magang Anda.
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4 pt-2 border-t border-slate-50">
                                @if ($mentor || $placement)
                                    <div class="flex flex-wrap gap-2">
                                        @if ($mentor)
                                            <x-chat-button :user="$mentor" label="Chat Mentor" />
                                        @endif
                                        @if ($placement)
                                            <x-chat-group-button :placement="$placement" />
                                        @endif
                                    </div>
                                @else
                                    <span class="text-[11px] text-slate-400">-</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- FORM PILIH DOSEN PEMBIMBING (DESKTOP - MUNCUL TEPAT DI BAWAH 3 KARTU, DETAIL PENEMPATAN TURUN KE BAWAH) -->
                    @if ($application && in_array($rawAppSt, ['accepted', 'active', 'completed'], true))
                        <div id="change-advisor-box" class="{{ $academicAdvisor ? 'hidden' : '' }} bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4 scroll-mt-6">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <h4 class="font-bold text-slate-800 text-base">Pilih Dosen Pembimbing</h4>
                                @if ($academicAdvisor)
                                    <button type="button"
                                        onclick="document.getElementById('change-advisor-box').classList.add('hidden')"
                                        class="text-xs text-slate-400 hover:text-slate-600 font-bold transition cursor-pointer">
                                        ✕ Tutup
                                    </button>
                                @endif
                            </div>
                            <form action="{{ route('student.select_advisor') }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label for="academic_advisor_id"
                                        class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                        Pilih Dosen Terdaftar: {{ $univName ?? $profile->universitas ?? 'Kampus Mahasiswa' }}
                                    </label>
                                    <select id="academic_advisor_id" name="academic_advisor_id" required
                                        class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs p-2.5">
                                        <option value="">-- Pilih Dosen Pembimbing --</option>
                                        @if(isset($availableDosens))
                                            @foreach ($availableDosens as $dosen)
                                                <option value="{{ $dosen->id }}" {{ optional($placement)->academic_advisor_id == $dosen->id ? 'selected' : '' }}>
                                                    {{ $dosen->name }} —
                                                    {{ is_string($dosen->university) ? $dosen->university : ($dosen->universityRelation?->name ?? $dosen->university?->name ?? 'Dosen') }}
                                                    ({{ $dosen->email }})
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <!-- Baris Aksi Terpadu -->
                                <div class="pt-1 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div class="flex items-center gap-2">
                                        <button type="submit"
                                            class="w-full sm:w-auto px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                                            {{ __('Simpan Dosen Pembimbing') }}
                                        </button>
                                        @if ($academicAdvisor)
                                            <button type="button"
                                                onclick="document.getElementById('change-advisor-box').classList.add('hidden')"
                                                class="px-3 py-2.5 text-xs font-bold text-slate-600 hover:text-slate-800 transition rounded-xl hover:bg-slate-100 cursor-pointer">
                                                Batal
                                            </button>
                                        @endif
                                    </div>
                                    <button type="button" @click="openNewDosenModal = true"
                                        class="text-xs text-blue-600 hover:text-blue-800 font-semibold underline cursor-pointer text-left sm:text-right">
                                        Dosen tidak ditemukan? Input Dosen Baru
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                    <!-- DETAIL BANNER PENGAJUAN AKTIF (DESKTOP) -->
                    @if ($application)
                        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm space-y-4">
                            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                                <h4 class="font-bold text-slate-800 text-base">Detail Penempatan Magang</h4>
                                @if (in_array($rawAppSt, ['accepted', 'active', 'completed'], true))
                                    <a href="{{ route('student.application.letter', $application->id) }}" target="_blank"
                                        class="text-xs font-bold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        <span>Unduh Surat Balasan Dinas</span>
                                    </a>
                                @endif
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <p><span class="text-slate-500">Instansi Penempatan:</span>
                                    <strong>{{ $application->unit->agencyProfile->agency_name ?? '-' }}</strong>
                                </p>
                                <p><span class="text-slate-500">Unit Kerja:</span>
                                    <strong>{{ $application->unit->name ?? '-' }}</strong>
                                </p>
                                <p><span class="text-slate-500">Periode Magang:</span>
                                    <strong>{{ \Carbon\Carbon::parse($application->start_date)->translatedFormat('d M Y') }} s/d
                                        {{ \Carbon\Carbon::parse($application->end_date)->translatedFormat('d M Y') }}</strong>
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div>
                                <h4 class="font-bold text-slate-800 text-base">Detail Penempatan Magang</h4>
                                <p class="text-xs text-slate-500 mt-1">Anda belum memiliki pengajuan magang yang aktif.</p>
                            </div>
                            <a href="{{ route('student.application.create') }}"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs transition shadow-sm">
                                <span>Buat Pengajuan Baru</span>
                            </a>
                        </div>
                    @endif

                </div>

                <!-- ==================================================== -->
                <!-- 4 & 5. MOBILE VIEW: STATUS CARDS & DETAIL PENEMPATAN  -->
                <!-- ==================================================== -->
                <div class="block md:hidden space-y-3.5">

                    <!-- Card 1: Status Magang (Mobile) -->
                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
                        <div class="flex items-center justify-between">
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status Magang</p>
                            @if ($application)
                                <x-status-badge :status="$application->status" />
                            @endif
                        </div>
                        <div class="mt-1">
                            @if ($application)
                                <p class="text-xs text-slate-600 font-medium">{{ $application->unit->name ?? $application->unit->agencyProfile->agency_name ?? 'Program Magang' }}</p>
                            @else
                                <p class="text-sm font-bold text-slate-800">Belum Mengajukan</p>
                            @endif
                        </div>
                        <div class="mt-3 pt-2 border-t border-slate-50 flex items-center justify-between">
                            @if ($application && in_array($rawAppSt, ['accepted', 'active', 'completed'], true))
                                <a href="{{ route('student.application.letter', $application->id) }}" target="_blank"
                                    class="text-xs font-bold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    <span>Surat Balasan</span>
                                </a>
                            @elseif (!$application)
                                <a href="{{ route('student.application.create') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                                    <span>Buat Pengajuan</span> &rarr;
                                </a>
                            @elseif ($rawAppSt === 'rejected')
                                <a href="{{ route('student.application.create') }}" class="text-xs font-bold text-rose-600 hover:text-rose-800 inline-flex items-center gap-1">
                                    <span>Ajukan Magang Baru</span> &rarr;
                                </a>
                            @elseif ($rawAppSt === 'resigned')
                                <a href="{{ route('student.application.create') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                                    <span>Daftar Magang Baru</span> &rarr;
                                </a>
                            @else
                                <span class="text-[11px] text-amber-600 font-semibold">Dalam proses verifikasi</span>
                            @endif
                        </div>
                    </div>

                    <!-- Card 2: Dosen Pembimbing (Mobile) -->
                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
                        <div class="flex items-center justify-between">
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Dosen Pembimbing</p>
                            @if ($academicAdvisor)
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-md">
                                    Terpilih
                                </span>
                            @elseif ($application && in_array($rawAppSt, ['accepted', 'active', 'completed'], true))
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 rounded-md animate-pulse">
                                    Perlu Ditentukan
                                </span>
                            @endif
                        </div>
                        <div class="mt-1">
                            @if ($academicAdvisor)
                                <p class="text-sm font-bold text-slate-800">{{ $academicAdvisor->name }}</p>
                                <div class="text-xs text-slate-500 mt-0.5 space-y-0.5">
                                    <p class="truncate font-mono">{{ $academicAdvisor->email }}</p>
                                    <p class="truncate">{{ is_string($academicAdvisor->university) ? $academicAdvisor->university : ($academicAdvisor->universityRelation?->name ?? $academicAdvisor->university?->name ?? $univName ?? $profile->universitas) }}</p>
                                </div>
                            @elseif ($application && in_array($rawAppSt, ['accepted', 'active', 'completed'], true))
                                <p class="text-xs text-slate-600">Belum memilih dosen pembimbing</p>
                            @else
                                <p class="text-xs text-slate-400">Belum Tersedia</p>
                            @endif
                        </div>
                        @if ($application && in_array($rawAppSt, ['accepted', 'active', 'completed'], true))
                            <div class="mt-2.5 pt-2 border-t border-slate-50">
                                <button type="button"
                                    onclick="const el = document.getElementById('change-advisor-box-mobile'); el.classList.toggle('hidden'); if(!el.classList.contains('hidden')) el.scrollIntoView({behavior: 'smooth'});"
                                    class="text-xs font-bold text-blue-600 hover:text-blue-800 cursor-pointer">
                                    {{ $academicAdvisor ? 'Ganti Dosen Pembimbing' : 'Pilih Dosen Pembimbing' }} &rarr;
                                </button>
                            </div>
                        @endif
                    </div>

                    <!-- FORM PILIH DOSEN PEMBIMBING (MOBILE - TEPAT DI BAWAH KARTU DOSEN PEMBIMBING) -->
                    @if ($application && in_array($rawAppSt, ['accepted', 'active', 'completed'], true))
                        <div id="change-advisor-box-mobile" class="{{ $academicAdvisor ? 'hidden' : '' }} bg-white rounded-2xl p-4 border border-slate-200 shadow-sm space-y-3 scroll-mt-6">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                                <h4 class="font-bold text-slate-800 text-sm">Pilih Dosen Pembimbing</h4>
                                @if ($academicAdvisor)
                                    <button type="button"
                                        onclick="document.getElementById('change-advisor-box-mobile').classList.add('hidden')"
                                        class="text-xs text-slate-400 hover:text-slate-600 font-bold transition cursor-pointer">
                                        ✕ Tutup
                                    </button>
                                @endif
                            </div>
                            <form action="{{ route('student.select_advisor') }}" method="POST" class="space-y-3">
                                @csrf
                                <div>
                                    <label for="academic_advisor_id_mobile"
                                        class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                        Pilih Dosen Terdaftar: {{ $univName ?? $profile->universitas ?? 'Kampus Mahasiswa' }}
                                    </label>
                                    <select id="academic_advisor_id_mobile" name="academic_advisor_id" required
                                        class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs p-2.5">
                                        <option value="">-- Pilih Dosen Pembimbing --</option>
                                        @if(isset($availableDosens))
                                            @foreach ($availableDosens as $dosen)
                                                <option value="{{ $dosen->id }}" {{ optional($placement)->academic_advisor_id == $dosen->id ? 'selected' : '' }}>
                                                    {{ $dosen->name }} —
                                                    {{ is_string($dosen->university) ? $dosen->university : ($dosen->universityRelation?->name ?? $dosen->university?->name ?? 'Dosen') }}
                                                    ({{ $dosen->email }})
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <div class="pt-1 flex flex-col gap-2">
                                    <button type="submit"
                                        class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                                        {{ __('Simpan Dosen Pembimbing') }}
                                    </button>
                                    @if ($academicAdvisor)
                                        <button type="button"
                                            onclick="document.getElementById('change-advisor-box-mobile').classList.add('hidden')"
                                            class="w-full py-1.5 text-xs font-bold text-slate-600 hover:text-slate-800 transition rounded-xl hover:bg-slate-100 cursor-pointer">
                                            Batal
                                        </button>
                                    @endif
                                    <button type="button" @click="openNewDosenModal = true"
                                        class="text-xs text-blue-600 hover:text-blue-800 font-semibold underline cursor-pointer text-center pt-1">
                                        Dosen tidak ditemukan? Input Dosen Baru
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                    <!-- Card 3: Mentor Lapangan (Mobile) -->
                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
                        <div>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Mentor Lapangan</p>
                            <p class="text-sm font-bold mt-1 text-slate-800">
                                {{ $mentor ? $mentor->name : 'Belum Diplot Dinas' }}
                            </p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Ditugaskan resmi oleh instansi penempatan magang Anda.</p>
                        </div>
                        @if ($mentor || $placement)
                            <div class="mt-2.5 pt-2 border-t border-slate-50 flex flex-wrap gap-2">
                                @if ($mentor)
                                    <x-chat-button :user="$mentor" label="Chat Mentor" />
                                @endif
                                @if ($placement)
                                    <x-chat-group-button :placement="$placement" />
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- DETAIL BANNER PENGAJUAN AKTIF (Mobile) -->
                    @if ($application)
                        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm space-y-3">
                            <div class="flex justify-between items-center border-b border-slate-100 pb-2.5">
                                <h4 class="font-bold text-slate-800 text-sm">Detail Penempatan Magang</h4>
                                @if (in_array($rawAppSt, ['accepted', 'active', 'completed'], true))
                                    <a href="{{ route('student.application.letter', $application->id) }}" target="_blank"
                                        class="text-xs font-bold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        <span>Unduh Surat</span>
                                    </a>
                                @endif
                            </div>
                            <div class="space-y-2 text-xs">
                                <div class="flex items-start justify-between py-1 border-b border-slate-50">
                                    <span class="text-slate-500">Instansi:</span>
                                    <span class="font-bold text-slate-800 text-right ml-2">{{ $application->unit->agencyProfile->agency_name ?? '-' }}</span>
                                </div>
                                <div class="flex items-start justify-between py-1 border-b border-slate-50">
                                    <span class="text-slate-500">Unit Kerja:</span>
                                    <span class="font-bold text-slate-800 text-right ml-2">{{ $application->unit->name ?? '-' }}</span>
                                </div>
                                <div class="flex items-start justify-between py-1">
                                    <span class="text-slate-500">Periode:</span>
                                    <span class="font-bold text-slate-800 text-right ml-2">
                                        {{ \Carbon\Carbon::parse($application->start_date)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($application->end_date)->translatedFormat('d M Y') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between gap-3">
                            <div>
                                <h4 class="font-bold text-slate-800 text-xs">Detail Penempatan Magang</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5">Belum ada pengajuan aktif.</p>
                            </div>
                            <a href="{{ route('student.application.create') }}"
                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg text-xs transition shadow-sm shrink-0">
                                <span>Daftar Magang</span>
                            </a>
                        </div>
                    @endif

                </div>

            </div>
        </div>
</x-app-layout>