<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('lecturer.dashboard') }}" 
                   class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition shadow-2xs" 
                   title="Kembali ke Dashboard">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl sm:text-2xl text-slate-900 leading-tight">
                        {{ $student->name }}
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        NIM: {{ $profile->nim ?? '-' }} &bull; {{ $profile->universitas ?? '-' }} ({{ $profile->jurusan ?? '-' }})
                    </p>
                </div>
            </div>

            <!-- Tombol Chat -->
            <div class="flex flex-wrap items-center gap-2">
                <x-chat-button :user="$student" label="Chat Mahasiswa" />
                @if ($placement->mentor ?? $placement->pembimbing)
                    <x-chat-button :user="$placement->mentor ?? $placement->pembimbing" label="Chat Mentor" />
                @endif
                <x-chat-group-button :placement="$placement" />
            </div>
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
    @endphp

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Message -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-lg flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-lg text-rose-900 text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <!-- 2. Card: Data Magang -->
            <div class="bg-white rounded-xl p-6 border border-slate-200 space-y-4">
                <h3 class="font-bold text-base text-slate-900 pb-3 border-b border-slate-200">
                    Data Magang
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-xs text-slate-500 block">Perguruan Tinggi</span>
                        <span class="font-medium text-slate-800">{{ $profile->universitas ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Program Studi / Jurusan</span>
                        <span class="font-medium text-slate-800 text-sm block mt-0.5">{{ $profile->jurusan ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Instansi & Unit Kerja</span>
                        <span class="font-medium text-slate-800 text-sm block mt-0.5">{{ $agency->agency_name ?? '-' }} &mdash; {{ $unit->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Mentor Lapangan</span>
                        <span class="font-medium text-slate-800 text-sm block mt-0.5">{{ $mentor->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Dosen Pembimbing</span>
                        <span class="font-medium text-slate-800 text-sm block mt-0.5">{{ $academicAdvisor->name ?? 'Belum Ditugaskan' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Periode Pelaksanaan</span>
                        <span class="font-medium text-slate-800 text-sm block mt-0.5">
                            {{ $placement->application?->start_date ? \Carbon\Carbon::parse($placement->application->start_date)->translatedFormat('d M Y') : '-' }} s/d {{ $placement->application?->end_date ? \Carbon\Carbon::parse($placement->application->end_date)->translatedFormat('d M Y') : '-' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- 3. Card: Penilaian -->
            <div class="bg-white rounded-2xl p-6 sm:p-7 border border-slate-200 shadow-xs space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="font-bold text-base text-slate-900">
                            Penilaian
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Ringkasan nilai evaluasi akhir magang mahasiswa
                        </p>
                    </div>
                    <div class="text-right">
                        @if (! empty($evaluationLockReason))
                            <a href="{{ route('lecturer.evaluations.create', $placement->id) }}"
                               class="inline-flex items-center px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 text-sm font-bold rounded-xl border border-slate-300 transition">
                                Lihat Nilai
                            </a>
                            <p class="text-xs text-slate-500 mt-1 max-w-xs">{{ $evaluationLockReason }}</p>
                        @else
                            <a href="{{ route('lecturer.evaluations.create', $placement->id) }}"
                               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition">
                                {{ $hasEval ? 'Ubah Nilai' : 'Isi Nilai' }}
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Grid Komparasi Nilai -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Nilai Dosen Pembimbing -->
                    <div class="p-4 sm:p-5 bg-slate-50/80 rounded-2xl border border-slate-200 space-y-3 shadow-2xs">
                        <span class="text-xs font-bold text-slate-600 uppercase tracking-wider block">Nilai Dosen Pembimbing</span>
                        @if ($isMentorOnly)
                            <p class="text-xs text-slate-500 italic">
                                Kebijakan kampus memberlakukan 100% penilaian dari Mentor Lapangan.
                            </p>
                        @else
                            <div class="grid grid-cols-3 gap-2 text-center">
                                <div class="p-2.5 bg-white rounded-xl border border-slate-200 shadow-2xs">
                                    <span class="text-xs text-slate-400 font-semibold block">Materi</span>
                                    <span class="text-base font-bold text-slate-900">{{ $eval?->score_mastery ?? '-' }}</span>
                                </div>
                                <div class="p-2.5 bg-white rounded-xl border border-slate-200 shadow-2xs">
                                    <span class="text-xs text-slate-400 font-semibold block">Laporan</span>
                                    <span class="text-base font-bold text-slate-900">{{ $eval?->score_report ?? '-' }}</span>
                                </div>
                                <div class="p-2.5 bg-white rounded-xl border border-slate-200 shadow-2xs">
                                    <span class="text-xs text-slate-400 font-semibold block">Sikap</span>
                                    <span class="text-base font-bold text-slate-900">{{ $eval?->score_attitude ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="text-xs text-slate-600 pt-1">
                                Rata-rata Dosen: <strong class="text-slate-900">{{ $eval?->nilai_dosen_calculated > 0 ? $eval->nilai_dosen_calculated : '-' }}</strong>
                            </div>
                        @endif
                    </div>

                    <!-- Nilai Mentor Lapangan -->
                    <div class="p-4 sm:p-5 bg-slate-50/80 rounded-2xl border border-slate-200 space-y-3 shadow-2xs">
                        <span class="text-xs font-bold text-slate-600 uppercase tracking-wider block">Nilai Mentor Lapangan</span>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="p-2.5 bg-white rounded-xl border border-slate-200 shadow-2xs">
                                <span class="text-xs text-slate-400 font-semibold block">Disiplin</span>
                                <span class="text-base font-bold text-slate-900">{{ $eval?->nilai_disiplin ?? '-' }}</span>
                            </div>
                            <div class="p-2.5 bg-white rounded-xl border border-slate-200 shadow-2xs">
                                <span class="text-xs text-slate-400 font-semibold block">Kinerja</span>
                                <span class="text-base font-bold text-slate-900">{{ $eval?->nilai_kinerja ?? '-' }}</span>
                            </div>
                            <div class="p-2.5 bg-white rounded-xl border border-slate-200 shadow-2xs">
                                <span class="text-xs text-slate-400 font-semibold block">Laporan</span>
                                <span class="text-base font-bold text-slate-900">{{ $eval?->nilai_laporan ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="text-xs text-slate-600 pt-1">
                            Rata-rata Mentor: <strong class="text-slate-900">{{ $eval?->nilai_pembimbing > 0 ? $eval->nilai_pembimbing : '-' }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Nilai Akhir & Predikat -->
                <div class="p-4 sm:p-5 bg-white rounded-2xl border border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-4 shadow-2xs">
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Nilai Akhir Magang</span>
                        <div class="text-2xl font-black text-slate-900 mt-0.5">
                            {{ $eval?->final_score > 0 ? $eval->final_score : ($eval?->nilai_akhir > 0 ? $eval->nilai_akhir : '-') }}
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Predikat Mutu</span>
                        <div class="text-2xl font-black text-blue-700 mt-0.5">
                            {{ $eval?->grade_calculated ?? '-' }}
                        </div>
                    </div>
                </div>

                @if ($eval?->feedback_dosen ?? $eval?->catatan_dosen)
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-700">
                        <strong>Catatan Dosen:</strong> "{{ $eval->feedback_dosen ?? $eval->catatan_dosen }}"
                    </div>
                @endif
            </div>

            <!-- 4. Card: Laporan Akhir (Komponen Bersama) -->
            <x-supervision.report-review
                :finalReport="$finalReport"
                :submitRoute="route('lecturer.final_report.updateStatus', $placement->id)"
                method="POST"
                role="lecturer"
            />

            <!-- 5. Card: Logbook (Komponen Bersama) -->
            <div class="bg-white rounded-xl p-6 border border-slate-200 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div>
                        <h3 class="font-bold text-base text-slate-900">
                            Logbook
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Daftar riwayat aktivitas harian per paket mingguan mahasiswa bimbingan Anda
                        </p>
                    </div>
                    <span class="px-3 py-1 bg-slate-100 text-slate-700 text-xs font-medium rounded-full">
                        Verifikasi DPL
                    </span>
                </div>

                <x-supervision.logbook-weekly
                    :bundles="$weeklyBundles"
                    :reviewRoute="route('lecturer.logbooks.bulk_approve')"
                    role="lecturer"
                    emptyMessage="Mahasiswa belum menginputkan entri logbook kegiatan."
                />
            </div>

        </div>
    </div>
</x-app-layout>
