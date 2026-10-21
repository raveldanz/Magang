<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('supervisor.reports.index') }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition" title="Kembali">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl sm:text-2xl text-slate-900 leading-tight">
                        {{ $student?->name ?? 'Mahasiswa' }}
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        NIM: {{ $profile?->nim ?? '-' }} &bull; {{ $profile?->universitas ?? '-' }} ({{ $profile?->jurusan ?? '-' }})
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-slate-100 text-slate-700 text-xs font-bold rounded-full">
                    Bidang: {{ $unit?->name ?? '-' }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- 1. Data Informasi Pembimbing & Periode -->
            <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/90 shadow-2xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-xs sm:text-sm">
                    <div>
                        <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider block">Mentor Lapangan</span>
                        <span class="font-bold text-slate-800 mt-0.5 block">{{ $mentor?->name ?? 'Belum Ditugaskan' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider block">Dosen Pembimbing</span>
                        <span class="font-bold text-slate-800 mt-0.5 block">{{ $academicAdvisor?->name ?? 'Belum Ditugaskan' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider block">Periode Magang</span>
                        <span class="font-bold text-slate-800 mt-0.5 block">
                            {{ $placement->application?->start_date ? \Carbon\Carbon::parse($placement->application->start_date)->translatedFormat('d M Y') : '-' }} s/d {{ $placement->application?->end_date ? \Carbon\Carbon::parse($placement->application->end_date)->translatedFormat('d M Y') : '-' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider block">Logbook Aktivitas</span>
                        <span class="font-bold text-slate-800 mt-0.5 block">
                            {{ $approvedLogbookCount }} disetujui dari {{ $logbookCount }} catatan
                        </span>
                    </div>
                </div>
            </div>

            <!-- 2. Proyek Inovasi Mahasiswa (Card Ringkas Menuju Halaman Detail Penuh) -->
            <div class="bg-white rounded-2xl p-5 sm:p-7 border border-slate-200/90 shadow-2xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="font-bold text-base text-slate-900">
                            Proyek Inovasi Mahasiswa
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Dokumentasi karya dan manfaat nyata kegiatan bagi masyarakat
                        </p>
                    </div>
                    <span class="px-3 py-1 bg-slate-100 text-slate-700 text-xs font-bold rounded-full">
                        {{ $projects->count() }} Proyek
                    </span>
                </div>

                @if ($projects->isNotEmpty())
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($projects as $proj)
                            <a href="{{ route('projects.show', $proj->id) }}"
                               class="p-4 rounded-2xl border border-slate-200 bg-white hover:border-blue-500 hover:shadow-xs transition flex gap-3.5 items-start group">
                                <!-- Thumbnail Rapi & Terkendali -->
                                <div class="w-20 h-20 sm:w-22 sm:h-22 rounded-xl overflow-hidden bg-slate-100 border border-slate-200 shrink-0 relative">
                                    <img src="{{ $proj->photo_url }}" alt="{{ $proj->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-200">
                                </div>

                                <!-- Info Penting Saja -->
                                <div class="flex-1 min-w-0 space-y-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $proj->category_badge_class }}">
                                            {{ $proj->category_label }}
                                        </span>
                                        <span class="text-[11px] font-semibold text-blue-600 group-hover:text-blue-800 flex items-center gap-0.5 shrink-0">
                                            Buka Halaman Detail &rarr;
                                        </span>
                                    </div>
                                    <h4 class="font-bold text-sm sm:text-base text-slate-900 group-hover:text-blue-700 transition leading-snug line-clamp-1">
                                        {{ $proj->title }}
                                    </h4>
                                    <p class="text-xs text-slate-500 line-clamp-1 leading-relaxed">
                                        {{ $proj->description }}
                                    </p>
                                    <p class="text-[11px] text-slate-600 line-clamp-1 leading-relaxed">
                                        <span class="font-semibold text-slate-700">Manfaat:</span> {{ $proj->community_benefit }}
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 bg-slate-50 rounded-2xl text-center text-slate-500 text-xs">
                        Mahasiswa belum menambahkan proyek inovasi.
                    </div>
                @endif
            </div>

            <!-- 3. Naskah Laporan Akhir -->
            <div class="bg-white rounded-2xl p-5 sm:p-7 border border-slate-200/90 shadow-2xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="font-bold text-base text-slate-900">
                            Naskah Laporan Akhir
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Dokumen naskah ilmiah PKL / Magang yang diunggah mahasiswa
                        </p>
                    </div>
                    <div>
                        @if ($finalReport)
                            <x-status-badge type="review" :status="$finalReport->status" />
                        @else
                            <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-medium">
                                Belum Mengunggah
                            </span>
                        @endif
                    </div>
                </div>

                @if ($finalReport && $finalReport->file_path)
                    <div class="p-4 sm:p-5 bg-slate-50 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <h4 class="font-bold text-sm text-slate-900">{{ $finalReport->title ?? 'Naskah Laporan Akhir' }}</h4>
                            <p class="text-xs text-slate-500">
                                Diunggah pada: {{ $finalReport->created_at ? $finalReport->created_at->translatedFormat('d F Y, H:i') : '-' }}
                            </p>
                        </div>
                        <a href="{{ route('final_reports.show', $finalReport->id) }}" target="_blank" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-2xs">
                            Buka Naskah Laporan
                        </a>
                    </div>

                    @if ($finalReport->feedback)
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-1">
                            <span class="font-bold text-slate-700 uppercase tracking-wider text-[10px] block">Catatan Pembimbing:</span>
                            <p class="text-slate-800 italic leading-relaxed">"{{ $finalReport->feedback }}"</p>
                        </div>
                    @endif
                @else
                    <div class="p-8 bg-slate-50 rounded-2xl text-center text-slate-500 text-xs">
                        Mahasiswa belum mengunggah naskah laporan akhir.
                    </div>
                @endif
            </div>

            <!-- 4. Ringkasan Nilai & Evaluasi (Read-Only) -->
            @if ($evaluation)
                <div class="bg-white rounded-2xl p-5 sm:p-7 border border-slate-200/90 shadow-2xs space-y-4">
                    <div class="pb-3 border-b border-slate-100">
                        <h3 class="font-bold text-base text-slate-900">
                            Evaluasi & Nilai Akhir
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Rekapitulasi penilaian kinerja magang dari pembimbing lapangan dan dosen
                        </p>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Disiplin</span>
                            <span class="text-lg font-bold text-slate-900 mt-1 block">{{ number_format($evaluation->nilai_disiplin ?? 0, 1) }}</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Kinerja</span>
                            <span class="text-lg font-bold text-slate-900 mt-1 block">{{ number_format($evaluation->nilai_kinerja ?? 0, 1) }}</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Laporan</span>
                            <span class="text-lg font-bold text-slate-900 mt-1 block">{{ number_format($evaluation->nilai_laporan ?? 0, 1) }}</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Nilai Akhir</span>
                            <span class="text-lg font-bold text-slate-900 mt-1 block">
                                {{ number_format((float) $evaluation->nilai_akhir, 1) }}
                                <span class="text-xs font-semibold text-slate-500 font-mono">({{ $evaluation->grade_calculated ?? '-' }})</span>
                            </span>
                        </div>
                    </div>

                    @if ($evaluation->catatan)
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 text-xs">
                            <strong class="text-slate-700">Catatan Mentor:</strong>
                            <span class="text-slate-600 italic">"{{ $evaluation->catatan }}"</span>
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
