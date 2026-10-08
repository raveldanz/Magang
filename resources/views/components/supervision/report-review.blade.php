@props([
    'finalReport' => null,
    'submitRoute' => '',
    'method' => 'POST',
    'role' => 'mentor',
    'canReview' => true,
])

<div class="bg-white rounded-2xl p-6 sm:p-7 shadow-xs space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
        <div>
            <h3 class="font-bold text-base text-slate-900">
                Laporan Akhir
            </h3>
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
                    <span>Buka Naskah Laporan<span class="sr-only"> (Tab Baru ↗)</span></span>
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
                        Catatan Evaluasi Laporan:
                    </label>

                    <!-- Quick Feedback Chips -->
                    <div class="flex flex-wrap items-center gap-1.5">
                        <button type="button" @click="feedbackText = feedbackText ? (feedbackText + '. Format naskah laporan sudah sesuai panduan.') : 'Format naskah laporan sudah sesuai panduan.'"
                                class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-medium rounded-lg transition cursor-pointer">
                            + Format Sesuai
                        </button>
                        <button type="button" @click="feedbackText = feedbackText ? (feedbackText + '. Lengkapi bukti lampiran dan dokumentasi.') : 'Lengkapi bukti lampiran dan dokumentasi.'"
                                class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-medium rounded-lg transition cursor-pointer">
                            + Lengkapi Lampiran
                        </button>
                        <button type="button" @click="feedbackText = feedbackText ? (feedbackText + '. Perbaiki abstrak dan kesimpulan Bab penutup.') : 'Perbaiki abstrak dan kesimpulan Bab penutup.'"
                                class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-medium rounded-lg transition cursor-pointer">
                            + Revisi Bab Akhir
                        </button>
                        <button type="button" @click="feedbackText = feedbackText ? (feedbackText + '. Naskah laporan akhir sudah baik dan disetujui (ACC).') : 'Naskah laporan akhir sudah baik dan disetujui (ACC).'"
                                class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-medium rounded-lg transition cursor-pointer">
                            + Laporan ACC
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
                    Setujui Laporan
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
