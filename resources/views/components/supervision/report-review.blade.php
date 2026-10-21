@props([
    'finalReport' => null,
    'placement' => null,
    'projects' => null,
    'submitRoute' => '',
    'method' => 'POST',
    'role' => 'mentor',
    'canReview' => true,
])

@php
    $projectList = $projects ?? ($placement?->internshipProjects ?? ($finalReport?->placement?->internshipProjects ?? collect()));
@endphp

<div class="space-y-6">
    <!-- 1. BAGIAN PROYEK INOVASI MAGANG -->
    <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-base text-slate-900">
                    Proyek Inovasi Magang
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Hasil karya inovasi dan dampak nyata bagi masyarakat yang dibuat oleh mahasiswa
                </p>
            </div>
            <div>
                <span class="px-3 py-1 bg-slate-100 text-slate-700 text-xs font-bold rounded-full">
                    {{ $projectList->count() }} Proyek
                </span>
            </div>
        </div>

        @if ($projectList->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                @foreach ($projectList as $proj)
                    @php
                        $ssCount = is_array($proj->screenshots) ? count($proj->screenshots) : 0;
                        $cpCount = is_array($proj->community_photos) ? count($proj->community_photos) : 0;
                        $docCount = is_array($proj->documents) ? count($proj->documents) : 0;
                    @endphp
                    <div class="p-4 sm:p-5 rounded-2xl border border-slate-200/90 bg-white space-y-3.5 shadow-2xs flex flex-col justify-between">
                        <div class="space-y-3">
                            <!-- Foto Proyek Langsung Ditampilkan -->
                            <div class="relative w-full aspect-video rounded-xl overflow-hidden bg-slate-100 border border-slate-200 group">
                                <a href="{{ route('projects.show', $proj->id) }}" title="Klik untuk membuka detail lengkap proyek">
                                    <img src="{{ $proj->photo_url }}" alt="{{ $proj->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                </a>
                                @if($ssCount > 0 || $cpCount > 0 || $proj->video_url)
                                    <div class="absolute bottom-2 right-2 flex items-center gap-1">
                                        @if($ssCount > 0)
                                            <span class="px-2 py-0.5 bg-slate-900/80 backdrop-blur-xs text-white text-[10px] font-bold rounded-md">
                                                +{{ $ssCount }} Layar
                                            </span>
                                        @endif
                                        @if($proj->video_url)
                                            <span class="px-2 py-0.5 bg-rose-600/90 backdrop-blur-xs text-white text-[10px] font-bold rounded-md flex items-center gap-1">
                                                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                                Video
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="space-y-1">
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold border {{ $proj->category_badge_class }}">
                                    {{ $proj->category_label }}
                                </span>
                                <h4 class="font-bold text-sm sm:text-base text-slate-900 leading-snug">
                                    {{ $proj->title }}
                                </h4>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed line-clamp-2">
                                    {{ $proj->description }}
                                </p>
                            </div>

                            <!-- Manfaat untuk Masyarakat -->
                            <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">
                                    Manfaat untuk Masyarakat
                                </span>
                                <p class="text-xs text-slate-800 leading-relaxed font-medium line-clamp-2">
                                    {{ $proj->community_benefit }}
                                </p>
                            </div>

                            <!-- Indikator Media Tambahan -->
                            <div class="flex flex-wrap items-center gap-1.5 text-[11px] text-slate-500">
                                @if($cpCount > 0)
                                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 font-medium rounded-md border border-emerald-100">
                                        {{ $cpCount }} Dokumentasi Lapangan
                                    </span>
                                @endif
                                @if($docCount > 0)
                                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 font-medium rounded-md border border-blue-100">
                                        {{ $docCount }} Dokumen Lampiran
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                            @if ($proj->demo_url)
                                <a href="{{ $proj->demo_url }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-800 truncate max-w-[50%]">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    <span class="truncate">Tautan</span>
                                </a>
                            @else
                                <span></span>
                            @endif

                            <a href="{{ route('projects.show', $proj->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl transition cursor-pointer shrink-0">
                                <span>Lihat Detail</span>
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-6 bg-slate-50 rounded-2xl text-center text-slate-500 text-xs">
                Mahasiswa belum mengunggah dokumentasi proyek inovasi.
            </div>
        @endif
    </div>

    <!-- 2. BAGIAN NASKAH LAPORAN AKHIR -->
    <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-base text-slate-900">
                    Naskah Laporan Akhir
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Naskah laporan ilmiah dan persetujuan luaran magang
                </p>
            </div>
            <div>
                @if ($finalReport)
                    <x-status-badge type="review" :status="$finalReport->status" />
                @else
                    <span class="px-3 py-1 bg-slate-100 text-slate-600 text-xs font-semibold rounded-full">
                        Belum Mengunggah
                    </span>
                @endif
            </div>
        </div>

        @if ($finalReport && $finalReport->file_path)
            <div class="p-4 sm:p-5 bg-slate-50 rounded-2xl shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1.5">
                    <div class="text-sm font-bold text-slate-900">
                        {{ $finalReport->title ?? 'Laporan Akhir Magang MBKM' }}
                    </div>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                        @if ($finalReport->created_at)
                            <span>Diunggah: <strong class="text-slate-700">{{ $finalReport->created_at->translatedFormat('d M Y, H:i') }}</strong></span>
                        @endif
                        @if ($finalReport->updated_at && $finalReport->updated_at != $finalReport->created_at)
                            <span>&bull;</span>
                            <span>Diperbarui: <strong class="text-slate-700">{{ $finalReport->updated_at->translatedFormat('d M Y, H:i') }}</strong></span>
                        @endif
                        @if (strtolower($finalReport->status instanceof \BackedEnum ? $finalReport->status->value : $finalReport->status) === 'revision')
                            <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-rose-50 text-rose-700">Perlu Revisi</span>
                        @elseif (strtolower($finalReport->status instanceof \BackedEnum ? $finalReport->status->value : $finalReport->status) === 'approved')
                            <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700">Telah Disetujui</span>
                        @endif
                    </div>
                </div>

                <div class="shrink-0">
                    <a href="{{ route('final_reports.show', $finalReport->id) }}" target="_blank" 
                       class="inline-flex items-center justify-center px-4 py-2.5 bg-white hover:bg-slate-100 text-slate-700 text-sm font-bold rounded-xl shadow-2xs transition cursor-pointer">
                        <span>Buka Naskah Laporan (Tab Baru)</span>
                    </a>
                </div>
            </div>

            @if (! $canReview)
                <p class="text-xs text-slate-500">Persetujuan laporan dilakukan oleh mentor yang ditugaskan.</p>
            @else
            <form action="{{ $submitRoute }}" method="POST" class="space-y-4 pt-1" x-data="{ feedbackText: @js(old('feedback', $finalReport->feedback ?? '')) }">
                @csrf
                @if (strtoupper($method) !== 'POST')
                    @method($method)
                @endif

                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 mb-1.5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Catatan Evaluasi Laporan & Proyek:
                        </label>

                        <!-- Quick Feedback Chips -->
                        <div class="flex flex-wrap items-center gap-1.5">
                            <button type="button" @click="feedbackText = feedbackText ? (feedbackText + '. Format naskah laporan sudah sesuai panduan.') : 'Format naskah laporan sudah sesuai panduan.'"
                                    class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-medium rounded-lg transition cursor-pointer">
                                + Format Sesuai
                            </button>
                            <button type="button" @click="feedbackText = feedbackText ? (feedbackText + '. Lengkapi bukti lampiran dan dokumentasi proyek.') : 'Lengkapi bukti lampiran dan dokumentasi proyek.'"
                                    class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-medium rounded-lg transition cursor-pointer">
                                + Lengkapi Dokumentasi
                            </button>
                            <button type="button" @click="feedbackText = feedbackText ? (feedbackText + '. Perbaiki abstrak dan kesimpulan Bab penutup.') : 'Perbaiki abstrak dan kesimpulan Bab penutup.'"
                                    class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-medium rounded-lg transition cursor-pointer">
                                + Revisi Bab Akhir
                            </button>
                            <button type="button" @click="feedbackText = feedbackText ? (feedbackText + '. Naskah laporan dan proyek inovasi sudah baik dan disetujui (ACC).') : 'Naskah laporan dan proyek inovasi sudah baik dan disetujui (ACC).'"
                                    class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-medium rounded-lg transition cursor-pointer">
                                + Laporan & Proyek ACC
                            </button>
                        </div>
                    </div>

                    <textarea name="feedback" x-model="feedbackText" rows="3" placeholder="Tuliskan catatan arahan atau catatan revisi untuk mahasiswa..." 
                              class="w-full text-xs sm:text-sm bg-white border-0 ring-1 ring-slate-200 focus:ring-2 focus:ring-blue-600 rounded-xl p-3 shadow-2xs"></textarea>
                </div>

                <div class="flex items-center justify-between gap-3 pt-1">
                    <button type="submit" name="status" value="revision" 
                            class="inline-flex items-center justify-center px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-sm font-bold rounded-xl transition cursor-pointer">
                        Minta Revisi
                    </button>
                    <button type="submit" name="status" value="approved" 
                            class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-xs transition cursor-pointer">
                        Setujui Laporan & Proyek
                    </button>
                </div>
            </form>
            @endif
        @else
            <div class="p-8 bg-slate-50 rounded-2xl text-center text-slate-500 text-xs sm:text-sm">
                Mahasiswa belum mengunggah naskah laporan akhir.
            </div>
        @endif
    </div>
</div>
