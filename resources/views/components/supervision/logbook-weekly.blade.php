@props([
    'bundles' => [],
    'reviewRoute' => '',
    'role' => 'mentor',
    'emptyMessage' => 'Belum ada paket logbook mingguan yang ditemukan.',
    'showBulkSelect' => true,
    // Buka otomatis paket pertama yang perlu diperiksa, supaya pemeriksa langsung melihat isinya.
    'openFirstPending' => true,
])

@php
    $bundleItems = collect($bundles instanceof \Illuminate\Pagination\AbstractPaginator ? $bundles->items() : $bundles);
    $selectableValues = $bundleItems->filter(fn ($b) => ! empty($b['pending_ids']))->map(fn ($b) => implode(',', $b['pending_ids']))->values();
    $firstPendingKey = $openFirstPending ? optional($bundleItems->first(fn ($b) => ! empty($b['pending_ids'])))['bundle_key'] : null;
    $otherPartyLabel = $role === 'lecturer' ? 'Mentor Lapangan' : 'Pembimbing';
    $statusText = fn ($s) => (\App\Enums\ReviewStatus::tryFrom((string) $s) ?? \App\Enums\ReviewStatus::PENDING)->localizedLabel('mentor');
    $errorBundleKey = old('bundle_key');
@endphp
<div class="space-y-4" x-data="{ selected: [], all: @js($selectableValues), lightboxUrl: null, lightboxCaption: '' }" @keydown.escape.window="lightboxUrl = null">
    {{-- Modal Lightbox Foto Bukti Kegiatan --}}
    <div x-show="lightboxUrl" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs"
         @click.self="lightboxUrl = null"
         role="dialog" aria-modal="true" aria-label="Pratinjau Foto Bukti Kegiatan">
        <div class="bg-white rounded-2xl max-w-3xl w-full p-4 sm:p-5 shadow-2xl space-y-3" @click.stop>
            <div class="flex items-center justify-between gap-3 pb-2 border-b border-slate-200">
                <p class="text-base font-bold text-slate-900" x-text="lightboxCaption || 'Foto Bukti Kegiatan'"></p>
                <button type="button" @click="lightboxUrl = null"
                        class="min-h-[44px] px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 text-sm font-bold rounded-xl transition cursor-pointer">
                    Tutup Foto
                </button>
            </div>
            <div class="flex items-center justify-center bg-slate-50 rounded-xl p-2 max-h-[70vh] overflow-auto">
                <img :src="lightboxUrl" :alt="lightboxCaption" class="max-w-full max-h-[65vh] rounded-lg object-contain">
            </div>
            <div class="flex justify-end">
                <a :href="lightboxUrl" target="_blank" rel="noopener noreferrer"
                   class="text-sm font-semibold text-blue-700 hover:underline">
                    Buka Gambar Penuh di Tab Baru
                </a>
            </div>
        </div>
    </div>

    {{-- Galat validasi dari pemeriksaan sebelumnya (mis. catatan wajib belum diisi) --}}
    @php
        $reviewErrorKey = collect(['feedback', 'bulk_feedback', 'logbook_ids', 'revise_ids', 'status', 'action'])->first(fn ($key) => $errors->has($key));
    @endphp
    @if ($reviewErrorKey)
        <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl text-rose-900 text-base" role="alert">
            <p class="font-bold">Pemeriksaan belum tersimpan.</p>
            <p class="mt-1">{{ $errors->first($reviewErrorKey) }}</p>
        </div>
    @endif

    {{-- Bar Setujui Banyak Sekaligus (Hanya tampil jika showBulkSelect aktif) --}}
    @if ($showBulkSelect && $selectableValues->count() > 1)
        <form action="{{ $reviewRoute }}" method="POST"
              class="hidden sm:flex bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-100 flex-col sm:flex-row sm:items-center justify-between gap-3">
            @csrf
            <input type="hidden" name="status" value="approved">
            <template x-for="id in selected.flatMap(v => v.split(','))" :key="id">
                <input type="hidden" name="logbook_ids[]" :value="id">
            </template>

            <label class="inline-flex items-center gap-3 text-base text-slate-800 cursor-pointer min-h-[44px]">
                <input type="checkbox" class="w-6 h-6 rounded border-slate-400 text-blue-600 focus:ring-blue-500"
                       :checked="selected.length === all.length && all.length > 0"
                       @change="selected = $event.target.checked ? [...all] : []">
                <span>
                    <span class="font-semibold block">Pilih semua minggu yang menunggu</span>
                    <span class="text-sm text-slate-600">Untuk menyetujui banyak minggu sekaligus yang isinya sudah Anda baca.</span>
                </span>
                <span class="text-slate-700 font-semibold" x-show="selected.length > 0" x-text="'(' + selected.length + ' minggu dipilih)'"></span>
            </label>

            <button type="button" :disabled="selected.length === 0"
                    @click="if (selected.length > 0) $dispatch('open-confirm-modal', {
                        form: $el.closest('form'),
                        title: 'Setujui Minggu yang Dipilih',
                        message: 'Semua hari yang masih menunggu pada ' + selected.length + ' minggu yang dipilih akan ditandai Disetujui.',
                        label: 'Minggu dipilih:',
                        name: selected.length + ' Minggu',
                        confirmText: 'Ya, Setujui Semua',
                        tone: 'blue'
                    })"
                    class="min-h-[48px] px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white text-base font-bold rounded-xl transition cursor-pointer shadow-xs"
                    :class="selected.length === 0 ? 'opacity-50 cursor-not-allowed' : ''">
                <span x-text="selected.length > 0 ? 'Setujui ' + selected.length + ' Minggu Terpilih' : 'Setujui yang Dipilih'"></span>
            </button>
        </form>
    @endif

    @forelse ($bundles as $bundle)
        @php
            $student = $bundle['student'] ?? null;
            $profile = $student?->studentProfile ?? null;
            $pendingIds = array_values($bundle['pending_ids'] ?? []);
            $pendingCountInBundle = count($pendingIds);
            $waitingStudentCount = (int) ($bundle['rejected_count'] ?? 0);
            $attachmentCount = collect($bundle['entries'])->filter(fn ($e) => !empty($e['attachment']))->count();
            $isSelectable = $pendingCountInBundle > 0;
            $periodText = \Carbon\Carbon::parse($bundle['min_date'])->translatedFormat('d M Y') . ' – ' . \Carbon\Carbon::parse($bundle['max_date'])->translatedFormat('d M Y');
            $isErrorBundle = $errorBundleKey !== null && $errorBundleKey === $bundle['bundle_key'];
            $startOpen = $isErrorBundle || ($errorBundleKey === null && $firstPendingKey !== null && $firstPendingKey === $bundle['bundle_key']);
            $initialRevise = $isErrorBundle ? array_values(array_map('intval', (array) old('revise_ids', []))) : [];
            $initialNote = $isErrorBundle ? (string) old('feedback', '') : '';
        @endphp

        <div x-data="{ open: @js($startOpen) }" id="minggu-{{ $bundle['bundle_key'] }}"
             class="bg-white rounded-2xl overflow-hidden shadow-xs border {{ $isSelectable ? 'border-amber-300' : 'border-slate-100' }}">
            <!-- Baris Ringkasan Mingguan -->
            <div class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                @if ($showBulkSelect && $isSelectable && $selectableValues->count() > 1)
                    <input type="checkbox" value="{{ implode(',', $pendingIds) }}" x-model="selected"
                           class="hidden sm:block w-6 h-6 rounded border-slate-400 text-blue-600 focus:ring-blue-500 cursor-pointer shrink-0"
                           aria-label="Pilih minggu {{ $periodText }} untuk disetujui sekaligus">
                @endif
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4 flex-1 items-center">
                    <!-- Mahasiswa -->
                    <div>
                        <div class="font-bold text-slate-900 text-lg sm:text-base">
                            {{ $student->name ?? 'Peserta Magang' }}
                        </div>
                        <div class="hidden sm:block text-sm text-slate-600 mt-0.5">
                            NIM/NIS: {{ $profile->nim ?? '-' }}
                        </div>
                        @if ($role === 'mentor' && !empty($bundle['is_fallback_unit_head']))
                            <span class="mt-1 inline-flex px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 text-sm font-bold">
                                Validasi sementara sebagai Kepala Unit
                            </span>
                        @endif
                        @if ($role === 'lecturer')
                            <span class="mt-1 hidden sm:inline-flex px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 text-sm font-medium">
                                Verifikasi Pembimbing
                            </span>
                        @endif
                    </div>

                    <!-- Periode -->
                    <div>
                        <span class="hidden sm:block text-sm text-slate-600">Minggu</span>
                        <span class="text-base font-semibold text-slate-900">
                            <span class="sm:hidden">Minggu </span>{{ $periodText }}
                        </span>
                    </div>

                    <!-- Jumlah Hari (di HP cukup terlihat di rincian) -->
                    <div class="hidden sm:block">
                        <span class="text-sm text-slate-600 block">Jumlah Hari</span>
                        <span class="text-base font-semibold text-slate-900">
                            {{ $bundle['entries_count'] }} hari diisi
                        </span>
                        <span class="block text-sm {{ $attachmentCount > 0 ? 'text-blue-700 font-semibold' : 'text-slate-600' }}">
                            {{ $attachmentCount > 0 ? $attachmentCount.' bukti foto/berkas' : 'Tanpa lampiran' }}
                        </span>
                    </div>

                    <!-- Status -->
                    <div>
                        <span class="hidden sm:block text-sm text-slate-600 mb-1">Keadaan</span>
                        @if ($pendingCountInBundle > 0)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                {{ $pendingCountInBundle }} hari perlu diperiksa
                            </span>
                        @elseif ($waitingStudentCount > 0)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-rose-50 text-rose-800 border border-rose-200">
                                {{ $waitingStudentCount }} hari sedang diperbaiki peserta magang
                            </span>
                        @else
                            <x-status-badge type="review" :status="$bundle['status']" />
                        @endif
                    </div>
                </div>

                <!-- Tombol Periksa -->
                <div class="flex items-center justify-end shrink-0">
                    <button type="button" @click="open = !open" :aria-expanded="open.toString()"
                            class="w-full sm:w-auto min-h-[52px] sm:min-h-[48px] inline-flex items-center justify-center px-5 py-2.5 text-base font-bold rounded-xl transition cursor-pointer {{ $isSelectable ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-800' }}">
                        <span x-text="open ? 'Tutup Rincian' : @js($isSelectable ? 'Periksa Sekarang' : 'Lihat Rincian')">{{ $isSelectable ? 'Periksa Sekarang' : 'Lihat Rincian' }}</span>
                    </button>
                </div>
            </div>

            <!-- Rincian & Formulir Pemeriksaan -->
            <div x-show="open" x-collapse x-cloak class="border-t border-slate-100 bg-slate-50/70 p-4 sm:p-6"
                 x-data="{
                     revise: @js($initialRevise),
                     note: @js($initialNote),
                     showNoteError: @js($isErrorBundle && ($errors->has('feedback') || $errors->has('bulk_feedback'))),
                     pendingIds: @js($pendingIds),
                     toggle(id, needsFix) {
                         this.revise = this.revise.filter(r => r !== id);
                         if (needsFix) this.revise.push(id);
                         if (this.revise.length === 0) this.showNoteError = false;
                     },
                     get approveCount() { return this.pendingIds.length - this.revise.length; },
                     get buttonText() {
                         if (this.revise.length === 0) return 'Setujui ' + this.pendingIds.length + ' Hari';
                         if (this.approveCount === 0) return 'Kembalikan ' + this.revise.length + ' Hari untuk Diperbaiki';
                         return 'Simpan: ' + this.approveCount + ' Disetujui, ' + this.revise.length + ' Diperbaiki';
                     },
                     submit(form) {
                         if (this.revise.length > 0 && this.note.trim() === '') {
                             this.showNoteError = true;
                             this.$nextTick(() => this.$refs.note && this.$refs.note.focus());
                             return;
                         }
                         const parts = [];
                         if (this.approveCount > 0) parts.push(this.approveCount + ' hari disetujui');
                         if (this.revise.length > 0) parts.push(this.revise.length + ' hari dikembalikan ke peserta magang untuk diperbaiki');
                         this.$dispatch('open-confirm-modal', {
                             form: form,
                             title: 'Simpan Pemeriksaan Logbook',
                             message: 'Hasil pemeriksaan: ' + parts.join(' dan ') + '.',
                             label: 'Minggu:',
                             name: @js($periodText),
                             confirmText: 'Ya, Simpan',
                             tone: this.revise.length > 0 ? 'amber' : 'blue'
                         });
                     }
                 }">
                <h4 class="text-base font-bold text-slate-900 mb-1">
                    Rincian Kegiatan Harian ({{ $periodText }})
                </h4>
                @if ($pendingCountInBundle > 0)
                    <p class="hidden sm:block text-sm text-slate-700 mb-4">
                        Baca kegiatan setiap hari. Bila sudah sesuai, biarkan pilihan <strong>Sesuai</strong>.
                        Bila ada yang kurang, pilih <strong>Perlu Diperbaiki</strong> lalu tulis apa yang harus diperbaiki di bawah.
                    </p>
                    <p class="sm:hidden text-base text-slate-800 mb-3">
                        Sudah benar? Biarkan <strong>Sesuai</strong>. Ada yang kurang? Pilih <strong>Perlu Diperbaiki</strong>.
                    </p>
                @endif

                <!-- Daftar Harian -->
                <div class="space-y-3">
                    @foreach ($bundle['entries'] as $entry)
                        @php
                            $entryStatus = $entry['status'] ?? 'pending';
                            $isPendingEntry = $entryStatus === 'pending';
                            $entryId = (int) $entry['id'];
                        @endphp
                        <div class="p-4 bg-white rounded-xl shadow-2xs space-y-3 border"
                             :class="revise.includes({{ $entryId }}) ? 'border-rose-300 ring-1 ring-rose-200' : 'border-slate-100'">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-2">
                                <div class="text-base font-bold text-slate-900">
                                    {{ \Carbon\Carbon::parse($entry['date'])->translatedFormat('l, d F Y') }}
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    @unless ($isPendingEntry)
                                        <x-status-badge type="review" :status="$entryStatus" />
                                    @endunless
                                    @if (!empty($entry['other_status']))
                                        <span class="text-sm text-slate-600">
                                            {{ $otherPartyLabel }}: <span class="font-semibold text-slate-800">{{ $statusText($entry['other_status']) }}</span>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <p class="text-base text-slate-900 whitespace-pre-line leading-relaxed">{{ $entry['activity'] }}</p>

                            @if (!empty($entry['attachment']))
                                @php
                                    $attUrl = $entry['attachment_url'] ?? asset('storage/' . $entry['attachment']);
                                    $attExt = strtolower(pathinfo($entry['attachment'], PATHINFO_EXTENSION));
                                    $isImage = in_array($attExt, ['jpg', 'jpeg', 'png', 'webp'], true);
                                    $entryDateLabel = \Carbon\Carbon::parse($entry['date'])->translatedFormat('d M Y');
                                @endphp
                                <div>
                                    @if ($isImage)
                                        <button type="button"
                                                @click="lightboxUrl = @js($attUrl); lightboxCaption = @js('Bukti kegiatan ' . $entryDateLabel)"
                                                title="Klik untuk memperbesar"
                                                class="inline-block text-left group cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-600 rounded-xl">
                                            <img src="{{ $attUrl }}" alt="Bukti kegiatan {{ $entryDateLabel }}"
                                                 loading="lazy" class="rounded-xl object-cover shadow-2xs group-hover:opacity-90 transition"
                                                 style="width: 180px; height: 124px;">
                                            <span class="block text-sm text-blue-700 font-semibold mt-1">Klik foto untuk memperbesar</span>
                                        </button>
                                    @else
                                        <a href="{{ $attUrl }}" target="_blank"
                                           class="min-h-[44px] inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 text-sm font-semibold rounded-xl transition">
                                            Buka Berkas Bukti ({{ strtoupper($attExt ?: 'file') }})
                                        </a>
                                    @endif
                                </div>
                            @endif

                            @if (!empty($entry['feedback']) && ! $isPendingEntry)
                                <div class="text-sm text-slate-800 bg-slate-50 p-3 rounded-xl">
                                    <strong>Catatan Anda:</strong> {{ $entry['feedback'] }}
                                </div>
                            @elseif (!empty($entry['feedback']))
                                <div class="text-sm text-slate-700 bg-slate-50 p-3 rounded-xl">
                                    <strong>Catatan Anda sebelumnya (sudah diperbaiki peserta magang):</strong> {{ $entry['feedback'] }}
                                </div>
                            @endif

                            @if (in_array($entryStatus, ['rejected', 'revision'], true))
                                <p class="text-sm text-rose-800 font-semibold">Menunggu peserta magang memperbaiki kegiatan ini.</p>
                            @endif

                            @if ($isPendingEntry)
                                <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2 pt-1" role="group" aria-label="Keputusan untuk {{ \Carbon\Carbon::parse($entry['date'])->translatedFormat('d F Y') }}">
                                    <span class="hidden sm:inline text-sm font-semibold text-slate-700 mr-1">Keputusan:</span>
                                    <button type="button" @click="toggle({{ $entryId }}, false)"
                                            :aria-pressed="(! revise.includes({{ $entryId }})).toString()"
                                            :class="! revise.includes({{ $entryId }}) ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50'"
                                            class="min-h-[44px] px-4 py-2 rounded-xl border-2 text-base font-bold transition cursor-pointer">
                                        Sesuai
                                    </button>
                                    <button type="button" @click="toggle({{ $entryId }}, true)"
                                            :aria-pressed="revise.includes({{ $entryId }}).toString()"
                                            :class="revise.includes({{ $entryId }}) ? 'bg-rose-600 text-white border-rose-600' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50'"
                                            class="min-h-[44px] px-4 py-2 rounded-xl border-2 text-base font-bold transition cursor-pointer">
                                        Perlu Diperbaiki
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if ($pendingCountInBundle > 0)
                    <!-- Formulir Pemeriksaan: satu tombol simpan dengan ringkasan keputusan -->
                    <form action="{{ $reviewRoute }}" method="POST" class="mt-5 pt-5 border-t border-slate-200 space-y-4" @submit.prevent="submit($el)">
                        @csrf
                        <input type="hidden" name="status" value="per_day">
                        <input type="hidden" name="bundle_key" value="{{ $bundle['bundle_key'] }}">
                        @foreach ($pendingIds as $logId)
                            <input type="hidden" name="logbook_ids[]" value="{{ $logId }}">
                        @endforeach
                        <template x-for="id in revise" :key="id">
                            <input type="hidden" name="revise_ids[]" :value="id">
                        </template>

                        <div>
                            <label for="catatan-{{ $bundle['bundle_key'] }}" class="block text-base font-bold text-slate-900 mb-1">
                                <span x-show="revise.length > 0">Tulis apa yang harus diperbaiki peserta magang <span class="text-rose-700">(wajib)</span></span>
                                <span x-show="revise.length === 0">Catatan atau pujian untuk peserta magang <span class="font-normal text-slate-600">(boleh dikosongkan)</span></span>
                            </label>

                            <!-- Contoh catatan siap pakai -->
                            <div class="flex-wrap items-center gap-2 mb-2" :class="revise.length > 0 ? 'flex' : 'hidden sm:flex'">
                                <span class="text-sm text-slate-600">Contoh cepat:</span>
                                <template x-if="revise.length > 0">
                                    <span class="contents">
                                        <button type="button" @click="note = (note ? note + ' ' : '') + 'Uraikan lebih rinci apa yang Anda kerjakan.'; showNoteError = false"
                                                class="min-h-[40px] px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-100 text-slate-800 text-sm font-semibold rounded-xl transition cursor-pointer">+ Uraian kurang rinci</button>
                                        <button type="button" @click="note = (note ? note + ' ' : '') + 'Lampirkan foto bukti kegiatan.'; showNoteError = false"
                                                class="min-h-[40px] px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-100 text-slate-800 text-sm font-semibold rounded-xl transition cursor-pointer">+ Tambah foto bukti</button>
                                        <button type="button" @click="note = (note ? note + ' ' : '') + 'Tuliskan kendala dan cara Anda mengatasinya.'; showNoteError = false"
                                                class="min-h-[40px] px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-100 text-slate-800 text-sm font-semibold rounded-xl transition cursor-pointer">+ Kendala &amp; solusi</button>
                                    </span>
                                </template>
                                <template x-if="revise.length === 0">
                                    <span class="contents">
                                        <button type="button" @click="note = (note ? note + ' ' : '') + 'Kegiatan sudah sesuai, pertahankan.'"
                                                class="min-h-[40px] px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-100 text-slate-800 text-sm font-semibold rounded-xl transition cursor-pointer">+ Sudah sesuai</button>
                                        <button type="button" @click="note = (note ? note + ' ' : '') + 'Lanjutkan ke minggu berikutnya.'"
                                                class="min-h-[40px] px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-100 text-slate-800 text-sm font-semibold rounded-xl transition cursor-pointer">+ Lanjutkan</button>
                                    </span>
                                </template>
                            </div>

                            <textarea id="catatan-{{ $bundle['bundle_key'] }}" name="feedback" x-ref="note" x-model="note" rows="3"
                                      @input="if (note.trim() !== '') showNoteError = false"
                                      :aria-invalid="showNoteError.toString()"
                                      placeholder="Tulis catatan di sini..."
                                      class="w-full text-base bg-white border focus:ring-2 focus:ring-blue-600 focus:border-blue-600 rounded-xl p-3 shadow-2xs"
                                      :class="showNoteError ? 'border-rose-500' : 'border-slate-300'"></textarea>
                            <p x-show="showNoteError" x-cloak class="mt-1 text-base font-semibold text-rose-700" role="alert">
                                Catatan wajib diisi karena ada hari yang dikembalikan. Tulis apa yang harus diperbaiki agar peserta magang paham.
                            </p>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <p class="text-base text-slate-800">
                                <span x-show="revise.length === 0">Semua <strong x-text="pendingIds.length"></strong> hari akan <strong>disetujui</strong>.</span>
                                <span x-show="revise.length > 0" x-cloak>
                                    <strong x-text="approveCount"></strong> hari disetujui,
                                    <strong class="text-rose-700" x-text="revise.length"></strong> hari dikembalikan untuk diperbaiki.
                                </span>
                            </p>
                            <button type="submit"
                                    :class="revise.length > 0 ? 'bg-amber-600 hover:bg-amber-700' : 'bg-blue-600 hover:bg-blue-700'"
                                    class="min-h-[52px] inline-flex items-center justify-center px-6 py-3 text-white text-base font-bold rounded-xl shadow-xs transition cursor-pointer">
                                <span x-text="buttonText">Setujui {{ $pendingCountInBundle }} Hari</span>
                            </button>
                        </div>
                    </form>
                @elseif ($waitingStudentCount > 0)
                    <p class="mt-4 text-base text-slate-700">Tidak ada yang perlu Anda putuskan pada minggu ini. Hari yang dikembalikan akan muncul lagi di sini setelah diperbaiki peserta magang.</p>
                @else
                    <p class="mt-4 text-base text-emerald-800 font-semibold">Semua hari pada minggu ini sudah disetujui.</p>
                @endif
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl p-8 text-center text-slate-700 shadow-xs">
            <p class="text-base font-medium">{{ $emptyMessage }}</p>
        </div>
    @endforelse

    @if ($bundles instanceof \Illuminate\Pagination\AbstractPaginator && $bundles->hasPages())
        <div class="pt-2">
            {{ $bundles->links() }}
        </div>
    @endif
</div>
