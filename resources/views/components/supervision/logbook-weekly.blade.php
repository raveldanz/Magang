@props([
    'bundles' => [],
    'reviewRoute' => '',
    'role' => 'mentor',
    'emptyMessage' => 'Belum ada paket logbook mingguan yang ditemukan.',
    'showBulkSelect' => true,
])

@php
    $selectableValues = collect($bundles instanceof \Illuminate\Pagination\AbstractPaginator ? $bundles->items() : $bundles)->filter(fn ($b) => ($b['status'] ?? '') !== 'approved')->map(fn ($b) => implode(',', $b['logbook_ids']))->values();
@endphp
<div class="space-y-4" x-data="{ selected: [], all: @js($selectableValues) }">
    {{-- Bar Setujui Banyak Sekaligus (Hanya tampil jika showBulkSelect aktif) --}}
    @if ($showBulkSelect && $selectableValues->isNotEmpty())
        <form action="{{ $reviewRoute }}" method="POST"
              class="bg-white rounded-2xl p-4 sm:p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            @csrf
            <input type="hidden" name="status" value="approved">
            <template x-for="id in selected.flatMap(v => v.split(','))" :key="id">
                <input type="hidden" name="logbook_ids[]" :value="id">
            </template>

            <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                <input type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                       :checked="selected.length === all.length && all.length > 0"
                       @change="selected = $event.target.checked ? [...all] : []">
                <span class="font-medium">Pilih semua yang belum disetujui</span>
                <span class="text-slate-400" x-show="selected.length > 0" x-text="'(' + selected.length + ' minggu dipilih)'"></span>
            </label>

            <button type="submit" :disabled="selected.length === 0"
                    class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition cursor-pointer shadow-xs"
                    :class="selected.length === 0 ? 'opacity-50 cursor-not-allowed' : ''">
                <span x-text="selected.length > 0 ? 'Setujui ' + selected.length + ' Minggu Terpilih' : 'Setujui yang Dipilih'"></span>
            </button>
        </form>
    @endif

    @forelse ($bundles as $bundle)
        @php
            $student = $bundle['student'] ?? null;
            $profile = $student?->studentProfile ?? null;
            $placement = $bundle['placement'] ?? null;
            $unit = $bundle['unit'] ?? null;
            $attachmentCount = collect($bundle['entries'])->filter(fn ($e) => !empty($e['attachment']))->count();
            $isSelectable = ($bundle['status'] ?? '') !== 'approved';
        @endphp

        <div x-data="{ open: false }" class="bg-white rounded-2xl overflow-hidden shadow-xs">
            <!-- Baris Ringkasan Mingguan -->
            <div class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                @if ($showBulkSelect && $isSelectable)
                    <input type="checkbox" value="{{ implode(',', $bundle['logbook_ids']) }}" x-model="selected"
                           class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer shrink-0"
                           title="Pilih untuk disetujui sekaligus">
                @endif
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 flex-1 items-center">
                    <!-- Mahasiswa -->
                    <div>
                        <div class="font-bold text-slate-900 text-sm">
                            {{ $student->name ?? 'Mahasiswa' }}
                        </div>
                        <div class="text-xs text-slate-500 font-mono mt-0.5">
                            NIM: {{ $profile->nim ?? '-' }}
                        </div>
                        @if ($role === 'mentor' && !empty($bundle['is_fallback_unit_head']))
                            <span class="mt-1 inline-flex px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 text-[11px] font-bold">
                                Validasi sementara sebagai Kepala Unit
                            </span>
                        @endif
                        @if ($role === 'lecturer')
                            <span class="mt-1 inline-flex px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[11px] font-medium">
                                Verifikasi DPL
                            </span>
                        @endif
                    </div>

                    <!-- Periode -->
                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Periode</span>
                        <span class="text-sm font-semibold text-slate-800">
                            {{ \Carbon\Carbon::parse($bundle['min_date'])->translatedFormat('d M Y') }} – {{ \Carbon\Carbon::parse($bundle['max_date'])->translatedFormat('d M Y') }}
                        </span>
                    </div>

                    <!-- Jumlah Hari -->
                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Jumlah Hari</span>
                        <span class="text-sm font-semibold text-slate-800">
                            {{ $bundle['entries_count'] }} Hari Terisi
                        </span>
                        <span class="block text-xs {{ $attachmentCount > 0 ? 'text-blue-700 font-semibold' : 'text-slate-400' }}">
                            {{ $attachmentCount > 0 ? $attachmentCount.' bukti foto/berkas' : 'Tanpa lampiran' }}
                        </span>
                    </div>

                    <!-- Status -->
                    <div>
                        <span class="text-xs text-slate-400 block font-medium mb-1">Status</span>
                        <x-status-badge type="review" :status="$bundle['status']" />
                    </div>
                </div>

                <!-- Tombol Periksa -->
                <div class="flex items-center justify-end shrink-0">
                    <button type="button" @click="open = !open" 
                            class="inline-flex items-center justify-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-bold rounded-xl transition cursor-pointer">
                        <span x-text="open ? 'Tutup Rincian' : 'Periksa Aktivitas'">Periksa Aktivitas</span>
                    </button>
                </div>
            </div>

            <!-- Bagian Accordion Terbuka ke Bawah -->
            <div x-show="open" x-collapse x-cloak class="border-t border-slate-100 bg-slate-50/70 p-4 sm:p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-bold text-slate-800">
                        Rincian Aktivitas Harian ({{ \Carbon\Carbon::parse($bundle['min_date'])->translatedFormat('d M Y') }} – {{ \Carbon\Carbon::parse($bundle['max_date'])->translatedFormat('d M Y') }})
                    </h4>
                </div>

                <!-- Daftar Harian -->
                <div class="space-y-3">
                    @foreach ($bundle['entries'] as $entry)
                        <div class="p-4 bg-white rounded-xl shadow-2xs space-y-2">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 border-b border-slate-100 pb-2">
                                <div class="text-xs sm:text-sm font-semibold text-slate-900 font-mono">
                                    {{ \Carbon\Carbon::parse($entry['date'])->translatedFormat('l, d F Y') }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-status-badge type="review" :status="$entry['status']" />
                                    @if ($role === 'mentor' && !empty($entry['other_status']))
                                        <span class="text-xs text-slate-500">
                                            DPL: <span class="font-medium text-slate-700">{{ $entry['other_status'] === 'approved' ? 'Disetujui' : ($entry['other_status'] === 'rejected' ? 'Perlu Revisi' : 'Menunggu') }}</span>
                                        </span>
                                    @elseif ($role === 'lecturer' && !empty($entry['other_status']))
                                        <span class="text-xs text-slate-500">
                                            Mentor: <span class="font-medium text-slate-700">{{ $entry['other_status'] === 'approved' ? 'Disetujui' : ($entry['other_status'] === 'rejected' ? 'Perlu Revisi' : 'Menunggu') }}</span>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <p class="text-xs sm:text-sm text-slate-800 whitespace-pre-line leading-relaxed">
                                {{ $entry['activity'] }}
                            </p>

                            @if (!empty($entry['attachment']))
                                @php
                                    $attUrl = $entry['attachment_url'] ?? asset('storage/' . $entry['attachment']);
                                    $attExt = strtolower(pathinfo($entry['attachment'], PATHINFO_EXTENSION));
                                    $isImage = in_array($attExt, ['jpg', 'jpeg', 'png', 'webp'], true);
                                @endphp
                                <div class="pt-1">
                                    @if ($isImage)
                                        <a href="{{ $attUrl }}" target="_blank" title="Klik untuk memperbesar" class="inline-block">
                                            <img src="{{ $attUrl }}" alt="Bukti kegiatan {{ \Carbon\Carbon::parse($entry['date'])->translatedFormat('d M Y') }}"
                                                 loading="lazy" class="rounded-xl object-cover shadow-2xs"
                                                 style="width: 160px; height: 110px;">
                                        </a>
                                        <span class="block text-[11px] text-slate-400 mt-1">Klik foto untuk memperbesar</span>
                                    @else
                                        <a href="{{ $attUrl }}" target="_blank"
                                           class="inline-flex items-center px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
                                            Buka Berkas Bukti ({{ strtoupper($attExt ?: 'file') }})
                                        </a>
                                    @endif
                                </div>
                            @endif

                            @if (!empty($entry['feedback']))
                                <div class="text-xs text-slate-600 bg-slate-50 p-3 rounded-xl">
                                    <strong>Catatan Anda Sebelumnya:</strong> {{ $entry['feedback'] }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Formulir Ulasan Catatan & Dua Tombol -->
                <form action="{{ $reviewRoute }}" method="POST" class="pt-4 border-t border-slate-200 space-y-4" x-data="{ feedbackText: @js($bundle['feedback'] ?? '') }">
                    @csrf
                    @foreach ($bundle['logbook_ids'] as $logId)
                        <input type="hidden" name="logbook_ids[]" value="{{ $logId }}">
                    @endforeach

                    <div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 mb-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Catatan Arahan / Feedback:
                            </label>
                            
                            <!-- Quick Feedback Chips -->
                            <div class="flex flex-wrap items-center gap-1.5">
                                <button type="button" @click="feedbackText = feedbackText ? (feedbackText + '. Uraian kegiatan lengkap dan sesuai.') : 'Uraian kegiatan lengkap dan sesuai.'"
                                        class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-medium rounded-lg transition cursor-pointer">
                                    + Uraian Sesuai
                                </button>
                                <button type="button" @click="feedbackText = feedbackText ? (feedbackText + '. Lengkapi bukti foto kegiatan.') : 'Lengkapi bukti foto kegiatan.'"
                                        class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-medium rounded-lg transition cursor-pointer">
                                    + Foto Kegiatan
                                </button>
                                <button type="button" @click="feedbackText = feedbackText ? (feedbackText + '. Perjelas kendala dan solusi yang dihadapi.') : 'Perjelas kendala dan solusi yang dihadapi.'"
                                        class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-medium rounded-lg transition cursor-pointer">
                                    + Kendala & Solusi
                                </button>
                                <button type="button" @click="feedbackText = feedbackText ? (feedbackText + '. Pertahankan dan lanjutkan ke minggu berikutnya.') : 'Pertahankan dan lanjutkan ke minggu berikutnya.'"
                                        class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-medium rounded-lg transition cursor-pointer">
                                    + Lanjutkan
                                </button>
                            </div>
                        </div>

                        <textarea name="feedback" x-model="feedbackText" rows="2" placeholder="Tuliskan catatan arahan, apresiasi, atau masukan untuk mahasiswa..." 
                                  class="w-full text-xs sm:text-sm bg-white border-0 ring-1 ring-slate-200 focus:ring-2 focus:ring-blue-600 rounded-xl p-3 shadow-2xs"></textarea>
                    </div>

                    <div class="flex items-center justify-between gap-3 pt-1">
                        <button type="submit" name="status" value="rejected" 
                                class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-xl transition cursor-pointer">
                            Minta Revisi
                        </button>
                        <button type="submit" name="status" value="approved" 
                                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                            Setujui Minggu Ini
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl p-8 text-center text-slate-400 shadow-xs">
            <p class="text-sm font-medium">{{ $emptyMessage }}</p>
        </div>
    @endforelse

    @if ($bundles instanceof \Illuminate\Pagination\LengthAwarePaginator && $bundles->hasPages())
        <div class="p-4 bg-white rounded-2xl shadow-xs">
            {{ $bundles->links() }}
        </div>
    @endif
</div>
