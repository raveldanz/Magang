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
                    Hasil karya inovasi dan dampak nyata bagi masyarakat yang dibuat oleh peserta magang
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
                                            <span class="px-2 py-0.5 bg-slate-900/80 backdrop-blur-xs text-white text-xs font-bold rounded-md">
                                                +{{ $ssCount }} Layar
                                            </span>
                                        @endif
                                        @if($proj->video_url)
                                            <span class="px-2 py-0.5 bg-rose-600/90 backdrop-blur-xs text-white text-xs font-bold rounded-md flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                                Video
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="space-y-1">
                                <span class="inline-block px-2 py-0.5 rounded text-xs font-bold border {{ $proj->category_badge_class }}">
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
                                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider block">
                                    Manfaat untuk Masyarakat
                                </span>
                                <p class="text-xs text-slate-800 leading-relaxed font-medium line-clamp-2">
                                    {{ $proj->community_benefit }}
                                </p>
                            </div>

                            <!-- Indikator Media Tambahan -->
                            <div class="flex flex-wrap items-center gap-1.5 text-xs text-slate-600">
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
                Peserta Magang belum mengunggah dokumentasi proyek inovasi.
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
                            <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-rose-50 text-rose-700">Perlu Revisi</span>
                        @elseif (strtolower($finalReport->status instanceof \BackedEnum ? $finalReport->status->value : $finalReport->status) === 'approved')
                            <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700">Telah Disetujui</span> {{-- status-guard:ignore --}}
                        @endif
                    </div>
                </div>

                <div class="shrink-0">
                    <a href="{{ route('final_reports.show', $finalReport->id) }}" target="_blank" 
                       class="min-h-[44px] inline-flex items-center justify-center px-4 py-2.5 bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-sm font-bold rounded-xl shadow-2xs transition cursor-pointer">
                        <span>Buka Naskah Laporan (Tab Baru)</span>
                    </a>
                </div>
            </div>

            @php
                $reportStatusValue = strtolower($finalReport->status instanceof \BackedEnum ? $finalReport->status->value : (string) $finalReport->status);
                $isLockedReport = $placement?->application?->statusValue() === 'completed';
            @endphp

            @if (! empty($finalReport->feedback))
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-base text-slate-800">
                    <span class="block text-sm font-bold text-slate-600 mb-1">Catatan terakhir pada laporan ini</span>
                    {{ $finalReport->feedback }}
                </div>
            @endif

            @if (! $canReview)
                <p class="text-base text-slate-700">Persetujuan laporan dilakukan oleh mentor yang ditugaskan.</p>
            @elseif ($isLockedReport)
                <p class="text-base text-slate-700">Magang sudah dinyatakan selesai, sehingga laporan akhir tidak dapat diubah lagi.</p>
            @elseif (in_array($reportStatusValue, ['revision', 'rejected'], true))
                <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-base text-amber-900">
                    Laporan sedang diperbaiki peserta magang. Tombol pemeriksaan akan muncul lagi setelah peserta magang mengunggah naskah perbaikan.
                </div>
            @else
            <form action="{{ $submitRoute }}" method="POST" class="space-y-4 pt-1"
                  x-data="{
                      feedbackText: @js((string) old('feedback', '')),
                      showNoteError: @js($errors->has('feedback')),
                      askRevision(button) {
                          if (this.feedbackText.trim() === '') {
                              this.showNoteError = true;
                              this.$nextTick(() => this.$refs.reportNote && this.$refs.reportNote.focus());
                              return;
                          }
                          this.$dispatch('open-confirm-modal', {
                              form: button.closest('form'),
                              submitter: button,
                              title: 'Kembalikan Laporan untuk Diperbaiki',
                              message: 'Naskah laporan akhir akan dikembalikan ke peserta magang beserta catatan Anda.',
                              label: 'Dokumen:',
                              name: @js($finalReport->title ?? 'Laporan Akhir Magang'),
                              confirmText: 'Ya, Kembalikan',
                              tone: 'rose'
                          });
                      }
                  }">
                @csrf
                @if (strtoupper($method) !== 'POST')
                    @method($method)
                @endif

                <ol class="list-decimal list-inside text-base text-slate-800 space-y-1">
                    <li>Klik <strong>Buka Naskah Laporan</strong> di atas dan baca isinya.</li>
                    <li>Bila sudah layak, klik <strong>Setujui Laporan</strong>.</li>
                    <li>Bila ada yang kurang, tulis catatan perbaikan lalu klik <strong>Kembalikan untuk Diperbaiki</strong>.</li>
                </ol>

                <div>
                    <label for="catatan-laporan-{{ $finalReport->id }}" class="block text-base font-bold text-slate-900 mb-1">
                        Catatan untuk peserta magang <span class="font-normal text-slate-600">(wajib bila dikembalikan)</span>
                    </label>

                    <!-- Contoh catatan siap pakai -->
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="text-sm text-slate-600">Contoh cepat:</span>
                        <button type="button" @click="feedbackText = (feedbackText ? feedbackText + ' ' : '') + 'Lengkapi bukti lampiran dan dokumentasi proyek.'; showNoteError = false"
                                class="min-h-[40px] px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-100 text-slate-800 text-sm font-semibold rounded-xl transition cursor-pointer">
                            + Lengkapi dokumentasi
                        </button>
                        <button type="button" @click="feedbackText = (feedbackText ? feedbackText + ' ' : '') + 'Perbaiki abstrak dan kesimpulan pada bab penutup.'; showNoteError = false"
                                class="min-h-[40px] px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-100 text-slate-800 text-sm font-semibold rounded-xl transition cursor-pointer">
                            + Perbaiki bab penutup
                        </button>
                        <button type="button" @click="feedbackText = (feedbackText ? feedbackText + ' ' : '') + 'Format naskah sudah sesuai panduan.'"
                                class="min-h-[40px] px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-100 text-slate-800 text-sm font-semibold rounded-xl transition cursor-pointer">
                            + Format sudah sesuai
                        </button>
                    </div>

                    <textarea id="catatan-laporan-{{ $finalReport->id }}" name="feedback" x-ref="reportNote" x-model="feedbackText" rows="3"
                              @input="if (feedbackText.trim() !== '') showNoteError = false"
                              placeholder="Tulis catatan untuk peserta magang di sini..."
                              class="w-full text-base bg-white border focus:ring-2 focus:ring-blue-600 focus:border-blue-600 rounded-xl p-3 shadow-2xs"
                              :class="showNoteError ? 'border-rose-500' : 'border-slate-300'"></textarea>
                    <p x-show="showNoteError" x-cloak class="mt-1 text-base font-semibold text-rose-700" role="alert">
                        {{ $errors->first('feedback') ?: 'Tulis dulu apa yang harus diperbaiki sebelum mengembalikan laporan.' }}
                    </p>
                </div>

                <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-between gap-3 pt-1">
                    <button type="button" name="status" value="revision"
                            @click="askRevision($el)"
                            class="min-h-[52px] inline-flex items-center justify-center px-5 py-3 bg-white hover:bg-rose-50 text-rose-800 border-2 border-rose-300 text-base font-bold rounded-xl transition cursor-pointer">
                        Kembalikan untuk Diperbaiki
                    </button>
                    <button type="button" name="status" value="approved"
                            @click="$dispatch('open-confirm-modal', {
                                form: $el.closest('form'),
                                submitter: $el,
                                title: 'Setujui Laporan Akhir',
                                message: 'Naskah laporan akhir dan proyek inovasi peserta magang akan disetujui sebagai syarat kelulusan magang.',
                                label: 'Dokumen:',
                                name: @js($finalReport->title ?? 'Laporan Akhir Magang'),
                                confirmText: 'Ya, Setujui Laporan',
                                tone: 'emerald'
                            })"
                            class="min-h-[52px] inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-base font-bold rounded-xl shadow-xs transition cursor-pointer">
                        Setujui Laporan
                    </button>
                </div>
            </form>
            @endif
        @else
            <div class="p-8 bg-slate-50 rounded-2xl text-center text-slate-600 text-sm">
                Peserta Magang belum mengunggah naskah laporan akhir.
            </div>
        @endif
    </div>
</div>
