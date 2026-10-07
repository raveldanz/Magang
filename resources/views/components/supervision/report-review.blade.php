@props([
    'finalReport' => null,
    'submitRoute' => '',
    'method' => 'POST',
    'role' => 'mentor',
    'canReview' => true,
])

<div class="bg-white rounded-2xl p-6 sm:p-7 border border-slate-200 shadow-xs space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
        <div>
            <h3 class="font-bold text-base text-slate-900">
                Laporan Akhir
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">
                Tinjau naskah laporan akhir magang dan berikan persetujuan atau catatan revisi
            </p>
        </div>
        <div>
            @if ($finalReport)
                <x-status-badge type="review" :status="$finalReport->status" />
            @else
                <span class="px-3 py-1 bg-slate-100 text-slate-600 text-xs font-semibold rounded-full border border-slate-200">
                    Belum Mengunggah
                </span>
            @endif
        </div>
    </div>

    @if ($finalReport && $finalReport->file_path)
        <div class="p-4 sm:p-5 bg-slate-50/80 rounded-2xl border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="text-sm font-bold text-slate-900">
                    {{ $finalReport->title ?? 'Laporan Akhir Magang MBKM' }}
                </div>
                <div class="text-xs text-slate-500">
                    Diunggah: {{ $finalReport->updated_at ? $finalReport->updated_at->format('d M Y, H:i') : '-' }}
                </div>
            </div>

            <div class="shrink-0">
                <a href="{{ route('final_reports.show', $finalReport->id) }}" target="_blank" 
                   class="inline-flex items-center justify-center px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 text-sm font-bold rounded-xl border border-slate-300 transition cursor-pointer">
                    <span>Buka Naskah Laporan<span class="sr-only"> (Tab Baru ↗)</span></span>
                </a>
            </div>
        </div>

        @if (! $canReview)
            <p class="text-xs text-slate-500">Persetujuan laporan dilakukan oleh mentor yang ditugaskan.</p>
        @else
        <form action="{{ $submitRoute }}" method="POST" class="space-y-4 pt-1">
            @csrf
            @if (strtoupper($method) !== 'POST')
                @method($method)
            @endif

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Catatan Evaluasi Laporan:
                </label>
                <textarea name="feedback" rows="3" placeholder="Tuliskan catatan arahan atau catatan revisi untuk mahasiswa..." 
                          class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 p-3 bg-white shadow-2xs">{{ old('feedback', $finalReport->feedback) }}</textarea>
            </div>

            <div class="flex items-center justify-between gap-3 pt-1">
                <button type="submit" name="status" value="revision" 
                        class="inline-flex items-center justify-center px-4 py-2.5 bg-white hover:bg-rose-50 text-rose-700 text-sm font-bold rounded-xl border border-rose-300 transition cursor-pointer">
                    Minta Revisi
                </button>
                <button type="submit" name="status" value="approved" 
                        class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition cursor-pointer">
                    Setujui Laporan
                </button>
            </div>
        </form>
        @endif
    @else
        <div class="p-8 bg-slate-50 rounded-2xl text-center text-slate-500 text-xs sm:text-sm border border-slate-100">
            Mahasiswa belum mengunggah naskah laporan akhir.
        </div>
    @endif
</div>
