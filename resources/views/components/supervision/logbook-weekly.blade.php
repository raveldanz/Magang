@props([
    'bundles' => [],
    'reviewRoute' => '',
    'role' => 'mentor',
    'emptyMessage' => 'Belum ada paket logbook mingguan yang ditemukan.',
])

@php
    $selectableValues = collect($bundles instanceof \Illuminate\Pagination\AbstractPaginator ? $bundles->items() : $bundles)->filter(fn ($b) => ($b['status'] ?? '') !== 'approved')->map(fn ($b) => implode(',', $b['logbook_ids']))->values();
@endphp
<div class="space-y-4" x-data="{ selected: [], all: @js($selectableValues) }">
    {{-- Bar Setujui Banyak Sekaligus --}}
    @if ($selectableValues->isNotEmpty())
        <form action="{{ $reviewRoute }}" method="POST"
              class="bg-white border border-slate-200 rounded-xl p-3 sm:p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            @csrf
            <input type="hidden" name="status" value="approved">
            <template x-for="id in selected.flatMap(v => v.split(','))" :key="id">
                <input type="hidden" name="logbook_ids[]" :value="id">
            </template>

            <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                <input type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                       :checked="selected.length === all.length && all.length > 0"
                       @change="selected = $event.target.checked ? [...all] : []">
                <span>Pilih semua yang belum disetujui</span>
                <span class="text-slate-400" x-show="selected.length > 0" x-text="'(' + selected.length + ' minggu dipilih)'"></span>
            </label>

            <button type="submit" :disabled="selected.length === 0"
                    class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition cursor-pointer"
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

        <div x-data="{ open: false }" class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
            <!-- Baris Ringkasan Mingguan -->
            <div class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                @if ($isSelectable)
                    <input type="checkbox" value="{{ implode(',', $bundle['logbook_ids']) }}" x-model="selected"
                           class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer shrink-0"
                           title="Pilih untuk disetujui sekaligus">
                @else
                    <span class="w-5 h-5 shrink-0 hidden md:block"></span>
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
                            <span class="mt-1 inline-flex px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200 text-[11px] font-bold">
                                Validasi sementara sebagai Kepala Unit (mentor belum ditunjuk)
                            </span>
                        @endif
                        @if ($role === 'lecturer')
                            <span class="mt-1 inline-flex px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[11px] font-medium">
                                Verifikasi DPL
                            </span>
                        @endif
                    </div>

                    <!-- Periode -->
                    <div>
                        <span class="text-xs text-slate-500 block">Periode</span>
                        <span class="text-sm font-medium text-slate-800">
                            {{ \Carbon\Carbon::parse($bundle['min_date'])->translatedFormat('d M Y') }} – {{ \Carbon\Carbon::parse($bundle['max_date'])->translatedFormat('d M Y') }}
                        </span>
                    </div>

                    <!-- Jumlah Hari -->
                    <div>
                        <span class="text-xs text-slate-500 block">Jumlah Hari</span>
                        <span class="text-sm font-medium text-slate-800">
                            {{ $bundle['entries_count'] }} Hari Terisi
                        </span>
                        <span class="block text-xs {{ $attachmentCount > 0 ? 'text-blue-700 font-semibold' : 'text-slate-400' }}">
                            {{ $attachmentCount > 0 ? $attachmentCount.' bukti foto/berkas' : 'Tanpa lampiran' }}
                        </span>
                    </div>

                    <!-- Status -->
                    <div>
                        <span class="text-xs text-slate-500 block mb-1">Status</span>
                        <x-status-badge type="review" :status="$bundle['status']" />
                    </div>
                </div>

                <!-- Tombol Periksa -->
                <div class="flex items-center justify-end shrink-0">
                    <button type="button" @click="open = !open" 
                            class="inline-flex items-center justify-center px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 text-sm font-bold rounded-xl border border-slate-300 transition cursor-pointer">
                        <span x-text="open ? 'Tutup' : 'Periksa'">Periksa</span>
                    </button>
                </div>
            </div>

            <!-- Bagian Accordion Terbuka ke Bawah -->
            <div x-show="open" x-collapse x-cloak class="border-t border-slate-200 bg-slate-50/60 p-4 sm:p-6 space-y-4">
                <h4 class="text-sm font-bold text-slate-800">
                    Rincian Aktivitas Harian ({{ \Carbon\Carbon::parse($bundle['min_date'])->translatedFormat('d M Y') }} – {{ \Carbon\Carbon::parse($bundle['max_date'])->translatedFormat('d M Y') }})
                </h4>

                <!-- Daftar Harian -->
                <div class="space-y-3">
                    @foreach ($bundle['entries'] as $entry)
                        <div class="p-4 bg-white rounded-xl border border-slate-200 space-y-2 shadow-2xs">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 border-b border-slate-100 pb-2">
                                <div class="text-sm font-semibold text-slate-900 font-mono">
                                    {{ \Carbon\Carbon::parse($entry['date'])->translatedFormat('l, d F Y') }}
                                </div>
                                <div class="flex items-center gap-3">
                                    <x-status-badge type="review" :status="$entry['status']" />
                                    @if ($role === 'mentor' && !empty($entry['other_status']))
                                        <span class="text-xs text-slate-500">
                                            Dosen Pembimbing: <span class="font-medium text-slate-700">{{ $entry['other_status'] === 'approved' ? 'Disetujui' : ($entry['other_status'] === 'rejected' ? 'Perlu Revisi' : 'Menunggu') }}</span>
                                        </span>
                                    @elseif ($role === 'lecturer' && !empty($entry['other_status']))
                                        <span class="text-xs text-slate-500">
                                            Mentor Lapangan: <span class="font-medium text-slate-700">{{ $entry['other_status'] === 'approved' ? 'Disetujui' : ($entry['other_status'] === 'rejected' ? 'Perlu Revisi' : 'Menunggu') }}</span>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <p class="text-sm text-slate-800 whitespace-pre-line leading-relaxed">
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
                                                 loading="lazy" class="rounded-lg border border-slate-200 object-cover"
                                                 style="width: 160px; height: 110px;">
                                        </a>
                                        <span class="block text-xs text-slate-400 mt-1">Klik foto untuk memperbesar</span>
                                    @else
                                        <a href="{{ $attUrl }}" target="_blank"
                                           class="inline-flex items-center px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-lg border border-slate-300 transition">
                                            Buka Berkas Bukti ({{ strtoupper($attExt ?: 'file') }})
                                        </a>
                                    @endif
                                </div>
                            @endif

                            @if (!empty($entry['feedback']))
                                <div class="text-xs text-slate-600 bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                                    <strong>Catatan Anda Sebelumnya:</strong> {{ $entry['feedback'] }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Formulir Ulasan Catatan & Dua Tombol -->
                <form action="{{ $reviewRoute }}" method="POST" class="pt-4 border-t border-slate-200 space-y-4">
                    @csrf
                    @foreach ($bundle['logbook_ids'] as $logId)
                        <input type="hidden" name="logbook_ids[]" value="{{ $logId }}">
                    @endforeach

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Catatan Arahan / Feedback:
                        </label>
                        <textarea name="feedback" rows="2" placeholder="Tuliskan catatan arahan, apresiasi, atau masukan untuk mahasiswa..." 
                                  class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 p-3 bg-white shadow-2xs">{{ $bundle['feedback'] }}</textarea>
                    </div>

                    <div class="flex items-center justify-between gap-3 pt-1">
                        <button type="submit" name="status" value="rejected" 
                                class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-white hover:bg-rose-50 text-rose-600 hover:text-rose-700 text-xs font-bold rounded-xl border border-rose-200 hover:border-rose-300 shadow-2xs transition active:scale-95 cursor-pointer">
                            Minta Revisi
                        </button>
                        <button type="submit" name="status" value="approved" 
                                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer">
                            Setujui Minggu Ini
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-xl p-8 text-center text-slate-500 border border-slate-200">
            <p class="text-sm">{{ $emptyMessage }}</p>
        </div>
    @endforelse

    @if ($bundles instanceof \Illuminate\Pagination\LengthAwarePaginator && $bundles->hasPages())
        <div class="p-4 bg-white rounded-xl border border-slate-200">
            {{ $bundles->links() }}
        </div>
    @endif
</div>
