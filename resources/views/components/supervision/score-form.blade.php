@props([
    'role' => 'mentor',
    'placement' => null,
    'student' => null,
    'profile' => null,
    'unit' => null,
    'agencyProfile' => null,
    'evaluation' => null,
    'univ' => null,
    'submitRoute' => '',
    'cancelRoute' => '',
    'lockReason' => null,
])

@php
    $student = $student ?? $placement?->application?->user;
    $profile = $profile ?? $student?->studentProfile;
    $unit = $unit ?? $placement?->application?->unit;
    $agencyProfile = $agencyProfile ?? ($unit?->agencyProfile ?? $placement?->agencyProfile);
    $univ = $univ ?? $evaluation?->getUniversity();
    if (!$univ && $student) {
        $univ = app(\App\Services\UniversityResolver::class)->forUser($student);
    }
    $scheme = $univ->evaluation_scheme ?? 'mentor_only';
    $weightMentor = $univ ? (int) $univ->weight_mentor : 100;
    $weightLecturer = $univ ? (int) $univ->weight_lecturer : 0;
    $isMentorOnly = ($scheme === 'mentor_only' || $weightLecturer === 0);
    $dinasScore = (float) ($evaluation?->nilai_pembimbing ?? 0);

    // Persiapkan daftar aspek awal (dari old input, dari DB JSON, atau fallback ke 3 aspek standar)
    $oldAspects = old('aspects');
    if (is_array($oldAspects) && count($oldAspects) > 0) {
        $initialAspects = array_values($oldAspects);
    } else {
        $savedList = $role === 'mentor' ? $evaluation?->mentor_aspects_list : $evaluation?->dosen_aspects_list;
        if (! empty($savedList) && is_array($savedList) && count($savedList) > 0) {
            $initialAspects = array_values($savedList);
        } else {
            if ($role === 'mentor') {
                $initialAspects = [
                    ['name' => $univ ? $univ->getDinasComponent(1) : 'Kedisiplinan & Tata Tertib Kerja', 'score' => ($evaluation?->nilai_disiplin > 0 ? (string)$evaluation->nilai_disiplin : '')],
                    ['name' => $univ ? $univ->getDinasComponent(2) : 'Kinerja Teknis & Kerjasama Tim', 'score' => ($evaluation?->nilai_kinerja > 0 ? (string)$evaluation->nilai_kinerja : '')],
                    ['name' => $univ ? $univ->getDinasComponent(3) : 'Inisiatif & Laporan Kedinasan', 'score' => ($evaluation?->nilai_laporan > 0 ? (string)$evaluation->nilai_laporan : '')],
                ];
            } else {
                $initialAspects = [
                    ['name' => $univ ? $univ->getDplComponent(1) : 'Penguasaan Materi & Relevansi CPL', 'score' => ($evaluation?->score_mastery > 0 ? (string)$evaluation->score_mastery : '')],
                    ['name' => $univ ? $univ->getDplComponent(2) : 'Kualitas Laporan Akhir Magang', 'score' => ($evaluation?->score_report > 0 ? (string)$evaluation->score_report : '')],
                    ['name' => $univ ? $univ->getDplComponent(3) : 'Sikap, Komunikasi & Etika Bimbingan', 'score' => ($evaluation?->score_attitude > 0 ? (string)$evaluation->score_attitude : '')],
                ];
            }
        }
    }

    $oldFeedback = $role === 'mentor'
        ? old('catatan', $evaluation?->catatan ?? '')
        : old('feedback_dosen', $evaluation?->feedback_dosen ?? ($evaluation?->catatan_dosen ?? ''));

    $endDate = $placement?->application?->end_date ? \Carbon\Carbon::parse($placement->application->end_date) : null;
    $daysUntilEnd = $endDate ? (int) now()->startOfDay()->diffInDays($endDate->copy()->startOfDay(), false) : null;
    $isEarlyGrading = $daysUntilEnd !== null && $daysUntilEnd > 14;
@endphp

<div class="space-y-6" x-data="{
    role: '{{ $role }}',
    isMentorOnly: {{ $isMentorOnly && $role === 'lecturer' ? 'true' : 'false' }},
    customizeAspects: false,
    aspects: @js($initialAspects),
    dinasScore: {{ $dinasScore }},
    weightMentor: {{ $weightMentor }},
    weightLecturer: {{ $weightLecturer }},
    openSaveConfirm(buttonEl) {
        const form = buttonEl.closest('form');
        if (form && !form.reportValidity()) {
            return;
        }
        const avgVal = this.avg !== null ? this.avg : '-';
        if (this.isMentorOnly) {
            // Kampus 100% nilai mentor: dosen hanya menyimpan catatan, jadi tidak ada rincian nilai dosen untuk ditampilkan.
            this.$dispatch('open-confirm-modal', {
                form: form,
                title: 'Konfirmasi Simpan Catatan Evaluasi',
                message: 'Catatan evaluasi Anda akan disimpan. Nilai angka sepenuhnya berasal dari Mentor Lapangan sesuai ketentuan kampus.',
                label: 'Nilai dari Mentor Lapangan',
                name: this.avg !== null ? 'Rata-rata ' + avgVal + ' — Predikat ' + this.letterGrade : 'Mentor Lapangan belum mengisi nilai',
                confirmText: 'Ya, Simpan Catatan',
                tone: 'blue'
            });
            return;
        }
        this.$dispatch('open-confirm-modal', {
            form: form,
            title: 'Konfirmasi Simpan Nilai Evaluasi',
            message: 'Anda akan menyimpan penilaian dengan rata-rata ' + avgVal + ' (Predikat ' + this.letterGrade + '). Pastikan nilai sudah benar karena setelah seluruh komponen lengkap dan magang selesai, nilai akan terkunci permanen.',
            label: 'Ringkasan Penilaian',
            name: 'Rata-rata ' + avgVal + ' — Predikat ' + this.letterGrade,
            desc: this.aspects.map((a, idx) => (idx + 1) + '. ' + (a.name || 'Kriteria') + ': ' + (a.score === '' || a.score === null || a.score === undefined ? '-' : a.score)).join(' | '),
            confirmText: 'Ya, Simpan Nilai',
            tone: 'blue'
        });
    },
    addAspect(customName = '') {
        let nextIndex = this.aspects.length + 1;
        this.aspects.push({
            name: customName || ('Aspek Penilaian ' + nextIndex),
            score: ''
        });
    },
    removeAspect(index) {
        if (this.aspects.length > 1) {
            this.aspects.splice(index, 1);
        }
    },
    get avg() {
        if (this.isMentorOnly) {
            return this.dinasScore > 0 ? this.dinasScore : null;
        }
        if (!this.aspects || this.aspects.length === 0) return null;
        let validScores = [];
        for (let i = 0; i < this.aspects.length; i++) {
            let s = parseFloat(this.aspects[i].score);
            if (isNaN(s) || s < 0 || s > 100) return null;
            validScores.push(s);
        }
        if (validScores.length === 0) return null;
        let sum = validScores.reduce((acc, curr) => acc + curr, 0);
        return Math.round((sum / validScores.length) * 100) / 100;
    },
    get finalScore() {
        if (this.role === 'mentor') {
            return this.avg;
        }
        if (this.isMentorOnly) {
            return this.dinasScore > 0 ? this.dinasScore : null;
        }
        let d = this.dinasScore;
        let l = this.avg;
        if (l === null) return null;
        if (d > 0) {
            return Math.round((((this.weightMentor / 100) * d) + ((this.weightLecturer / 100) * l)) * 100) / 100;
        }
        return l;
    },
    get letterGrade() {
        let f = this.finalScore ?? this.avg;
        if (f === null) return '-';
        if (f >= 85) return 'A';
        if (f >= 75) return 'AB';
        if (f >= 65) return 'B';
        if (f >= 55) return 'BC';
        if (f >= 40) return 'C';
        return 'E';
    }
}">

    <!-- 1. Info Singkat Mahasiswa (Grid 2x2 Lapang & Terstruktur) -->
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-xs space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-slate-100">
            <h3 class="font-bold text-base text-slate-900">Data Peserta Magang &amp; Penempatan</h3>
            @if ($placement?->application?->status)
                <x-status-badge :status="$placement->application->status" />
            @endif
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 sm:gap-4">
            <div class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/70 space-y-1">
                <span class="text-xs text-slate-600 font-bold uppercase tracking-wider block">Peserta Magang</span>
                <div class="text-sm sm:text-base font-bold text-slate-900 leading-snug">{{ $student->name ?? '-' }}</div>
                <div class="text-xs sm:text-sm text-slate-700 font-mono">NIM/NIS: {{ $profile->nim ?? '-' }}</div>
            </div>
            <div class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/70 space-y-1">
                <span class="text-xs text-slate-600 font-bold uppercase tracking-wider block">Kampus/Sekolah &amp; Jurusan</span>
                <div class="text-sm sm:text-base font-bold text-slate-900 leading-snug">{{ $profile->universitas ?? ($univ->name ?? '-') }}</div>
                <div class="text-xs sm:text-sm text-slate-700">{{ $profile->jurusan ?? '-' }}</div>
            </div>
            <div class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/70 space-y-1">
                <span class="text-xs text-slate-600 font-bold uppercase tracking-wider block">Lokasi &amp; Unit Kerja</span>
                <div class="text-sm sm:text-base font-bold text-slate-900 leading-snug">{{ $agencyProfile->agency_name ?? '-' }}</div>
                <div class="text-xs sm:text-sm text-slate-700">{{ $unit->name ?? '-' }}</div>
            </div>
            <div class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/70 space-y-1">
                <span class="text-xs text-slate-600 font-bold uppercase tracking-wider block">Periode Magang</span>
                <div class="text-sm sm:text-base font-bold text-slate-900 leading-snug">
                    {{ $placement?->application?->start_date ? \Carbon\Carbon::parse($placement->application->start_date)->translatedFormat('d M Y') : '-' }}
                    &ndash;
                    {{ $placement?->application?->end_date ? \Carbon\Carbon::parse($placement->application->end_date)->translatedFormat('d M Y') : '-' }}
                </div>
                <div class="text-xs sm:text-sm text-slate-700">
                    Pembimbing Lapangan: {{ $placement?->mentor?->name ?? $placement?->pembimbing?->name ?? 'Belum Ditugaskan' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Notifikasi Khusus jika Kampus Memberlakukan Mentor Only bagi Dosen -->
    @if ($role === 'lecturer' && $isMentorOnly)
        <div class="bg-blue-50/60 rounded-2xl p-4 sm:p-5 border border-blue-200 text-sm text-blue-900 shadow-2xs">
            <div class="font-bold mb-1">Kebijakan Evaluasi Kampus: 100% Mentor Lapangan</div>
            <p class="text-xs text-blue-800 leading-relaxed">
                Perguruan tinggi <strong>{{ $profile->universitas ?? ($univ->name ?? 'Peserta Magang') }}</strong> memberlakukan penilaian magang penuh dari Mentor Lapangan. Anda dapat meninjau nilai di bawah dan melengkapi catatan evaluasi akhir bimbingan.
            </p>
        </div>
    @endif

    @if ($lockReason)
        <div class="bg-slate-50 rounded-2xl p-4 sm:p-5 border border-slate-300 text-base text-slate-800">
            <div class="font-bold text-slate-900 mb-1">Form penilaian terkunci</div>
            <p>{{ $lockReason }}</p>
        </div>
    @elseif ($isEarlyGrading)
        <div class="bg-amber-50/80 rounded-2xl p-4 sm:p-5 border border-amber-300 text-base text-amber-950">
            <div class="font-bold mb-1">Catatan Jadwal Penilaian</div>
            <p class="text-base leading-relaxed">
                Masa magang peserta berlangsung hingga <strong>{{ $endDate->translatedFormat('d M Y') }}</strong> ({{ $daysUntilEnd }} hari lagi). Penilaian akhir disarankan diisi menjelang selesai magang agar mencerminkan capaian penuh.
            </p>
        </div>
    @endif

    <!-- Formulir Penilaian Dinamis -->
    <div class="bg-white rounded-2xl p-6 sm:p-7 border border-slate-200 shadow-xs">
        <form action="{{ $submitRoute }}" method="POST" class="space-y-6">
            @csrf
            {{-- fieldset disabled: semua input ikut terkunci saat penilaian belum/tidak boleh diubah --}}
            <fieldset @disabled($lockReason) class="space-y-6">

            @if ($role === 'lecturer' && $isMentorOnly)
                <!-- Tampilan Read-Only Nilai Mentor untuk Dosen Pembimbing di Kampus Mentor Only -->
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">
                        Nilai Evaluasi Mentor Lapangan
                    </h3>
                    <p class="text-xs text-slate-600 mt-0.5">
                        Rincian nilai yang telah dimasukkan oleh Mentor Lapangan di instansi magang.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @forelse ($evaluation?->mentor_aspects_list ?? [] as $mAspect)
                        <div class="bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-200 text-center space-y-1 shadow-2xs">
                            <span class="text-xs font-bold text-slate-600 uppercase tracking-wider block">{{ $mAspect['name'] }}</span>
                            <div class="text-2xl font-black text-slate-900">{{ $mAspect['score'] }}</div>
                        </div>
                    @empty
                        <div class="col-span-3 p-4 bg-slate-50 rounded-xl text-center text-slate-600 text-xs">
                            Belum ada nilai yang dimasukkan oleh Mentor Lapangan.
                        </div>
                    @endforelse
                </div>
            @else
                <!-- Header Aspek Penilaian & Tombol Opsi Sesuaikan Kriteria -->
                <div class="space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">
                                {{ $role === 'mentor' ? 'Aspek & Kriteria Penilaian Mentor Lapangan' : 'Aspek & Kriteria Penilaian Akademik Pembimbing' }}
                            </h3>
                            <p class="text-base text-slate-700 mt-0.5">
                                Ketik nilai 0 sampai 100 pada setiap kriteria (boleh desimal, misalnya 85.5). Rata-rata dan predikat dihitung otomatis.
                            </p>
                        </div>
                        @unless ($lockReason)
                            <button type="button" @click="customizeAspects = !customizeAspects"
                                    class="inline-flex items-center justify-center gap-1.5 min-h-[44px] px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl border border-slate-300 transition cursor-pointer self-start sm:self-auto">
                                <span x-text="customizeAspects ? 'Selesai Sesuaikan Aspek' : 'Sesuaikan Aspek Penilaian'"></span>
                            </button>
                        @endunless
                    </div>

                    <!-- Preset Chips Bantuan Cepat (Hanya tampil jika Sesuaikan Aspek diaktifkan) -->
                    <div x-show="customizeAspects" style="display: none;" class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 space-y-2.5 text-xs">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="font-semibold text-slate-700">Rekomendasi Rubrik Kampus (klik untuk menambah kriteria):</span>
                            <button type="button" @click="addAspect()"
                                    class="inline-flex items-center justify-center gap-1.5 min-h-[40px] px-3.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-xl border border-blue-200 transition cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span>Tambah Aspek</span>
                            </button>
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5">
                            @if ($role === 'mentor')
                                <button type="button" @click="addAspect('Kedisiplinan & Presensi Kehadiran')" class="min-h-[36px] px-3 py-1 bg-white hover:bg-slate-100 text-slate-800 text-xs font-medium rounded-lg border border-slate-300 transition cursor-pointer">+ Kehadiran</button>
                                <button type="button" @click="addAspect('Kualitas Output & Ketepatan Tugas')" class="min-h-[36px] px-3 py-1 bg-white hover:bg-slate-100 text-slate-800 text-xs font-medium rounded-lg border border-slate-300 transition cursor-pointer">+ Kualitas Tugas</button>
                                <button type="button" @click="addAspect('Inisiatif, Problem Solving & Kerja Tim')" class="min-h-[36px] px-3 py-1 bg-white hover:bg-slate-100 text-slate-800 text-xs font-medium rounded-lg border border-slate-300 transition cursor-pointer">+ Kerjasama Tim</button>
                                <button type="button" @click="addAspect('Etika Kerja & Komunikasi Kedinasan')" class="min-h-[36px] px-3 py-1 bg-white hover:bg-slate-100 text-slate-800 text-xs font-medium rounded-lg border border-slate-300 transition cursor-pointer">+ Etika Kerja</button>
                                <button type="button" @click="addAspect('Sistematika Laporan Magang Instansi')" class="min-h-[36px] px-3 py-1 bg-white hover:bg-slate-100 text-slate-800 text-xs font-medium rounded-lg border border-slate-300 transition cursor-pointer">+ Laporan Dinas</button>
                            @else
                                <button type="button" @click="addAspect('Penguasaan Teori & Relevansi CPL')" class="min-h-[36px] px-3 py-1 bg-white hover:bg-slate-100 text-slate-800 text-xs font-medium rounded-lg border border-slate-300 transition cursor-pointer">+ Relevansi CPL</button>
                                <button type="button" @click="addAspect('Sistematika & Mutu Laporan Akhir')" class="min-h-[36px] px-3 py-1 bg-white hover:bg-slate-100 text-slate-800 text-xs font-medium rounded-lg border border-slate-300 transition cursor-pointer">+ Mutu Laporan</button>
                                <button type="button" @click="addAspect('Ujian Presentasi / Sidang Magang')" class="min-h-[36px] px-3 py-1 bg-white hover:bg-slate-100 text-slate-800 text-xs font-medium rounded-lg border border-slate-300 transition cursor-pointer">+ Ujian Presentasi</button>
                                <button type="button" @click="addAspect('Sikap, Etika & Responsivitas Bimbingan')" class="min-h-[36px] px-3 py-1 bg-white hover:bg-slate-100 text-slate-800 text-xs font-medium rounded-lg border border-slate-300 transition cursor-pointer">+ Etika Bimbingan</button>
                                <button type="button" @click="addAspect('Inovasi & Penyelesaian Kasus Industri')" class="min-h-[36px] px-3 py-1 bg-white hover:bg-slate-100 text-slate-800 text-xs font-medium rounded-lg border border-slate-300 transition cursor-pointer">+ Inovasi Kasus</button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Daftar Baris Aspek Input (Ringkas secara default, dapat diedit jika Sesuaikan Aspek aktif) -->
                <div class="space-y-3">
                    <template x-for="(aspect, index) in aspects" :key="index">
                        <div class="p-4 sm:p-5 bg-slate-50 rounded-2xl border border-slate-200 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 transition hover:border-slate-300">
                            
                            <!-- 1. Nomor & Nama Aspek Kriteria -->
                            <div class="flex items-center gap-3.5 min-w-0 flex-1">
                                <div class="w-9 h-9 rounded-xl bg-white border border-slate-300 text-slate-800 font-bold text-sm flex items-center justify-center shrink-0"
                                     x-text="index + 1">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <template x-if="!customizeAspects">
                                        <div>
                                            <input type="hidden" :name="'aspects[' + index + '][name]'" :value="aspect.name">
                                            <span class="block text-sm font-semibold text-slate-700 mb-0.5">Kriteria Penilaian</span>
                                            <p class="text-base sm:text-lg font-bold text-slate-900 leading-snug" x-text="aspect.name"></p>
                                        </div>
                                    </template>
                                    <template x-if="customizeAspects">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">
                                                Nama Aspek / Kriteria <span class="text-rose-600">*</span>
                                            </label>
                                            <input type="text" :name="'aspects[' + index + '][name]'" x-model="aspect.name" required
                                                   placeholder="Contoh: Penguasaan Materi / Sikap & Komunikasi"
                                                   class="w-full min-h-[44px] text-sm font-semibold bg-white border border-slate-300 rounded-xl px-3.5 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition text-slate-900">
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- 2. Input Skor Nilai Numerik (0 - 100) & Tombol Hapus -->
                            <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0">
                                <div>
                                    <label :for="'nilai-aspek-' + index" class="block text-sm font-bold text-slate-800 mb-1 text-left sm:text-right">
                                        Nilai 0&ndash;100 <span class="text-rose-600">*</span>
                                    </label>
                                    <div class="w-36 sm:w-40">
                                        <input type="number" :id="'nilai-aspek-' + index" :name="'aspects[' + index + '][score]'" min="0" max="100" step="0.01" x-model="aspect.score" required placeholder="mis. 85"
                                               inputmode="decimal"
                                               class="w-full min-h-[52px] text-2xl font-black text-center py-2 bg-white border-2 border-slate-400 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition text-slate-900 placeholder:text-base placeholder:font-normal placeholder:text-slate-400">
                                    </div>
                                </div>

                                <div class="pt-5" x-show="customizeAspects" style="display: none;">
                                    <button type="button" @click="removeAspect(index)"
                                            :disabled="aspects.length <= 1"
                                            :class="aspects.length <= 1 ? 'opacity-30 cursor-not-allowed text-slate-400' : 'text-slate-600 hover:text-rose-700 hover:bg-rose-50 cursor-pointer'"
                                            class="min-w-[44px] min-h-[44px] flex items-center justify-center p-2.5 rounded-xl border border-slate-300 bg-white transition"
                                            title="Hapus kriteria ini">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Tombol Tambah Baris di Bawah List (saat Sesuaikan Aspek aktif) -->
                <div x-show="customizeAspects" style="display: none;" class="flex items-center justify-between pt-1">
                    <button type="button" @click="addAspect()"
                            class="inline-flex items-center gap-2 min-h-[44px] px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl border border-slate-300 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Tambah Aspek Penilaian Lainnya</span>
                    </button>
                    <span class="text-xs text-slate-600 font-medium" x-text="aspects.length + ' aspek kriteria aktif'"></span>
                </div>
            @endif

            <!-- 3. Kotak Hasil Kalkulasi Real-time (Rata-rata, Predikat Mutu, Nilai Akhir) -->
            <div class="bg-slate-50 rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-2xs">
                <span class="text-base font-bold text-slate-900 block mb-3">
                    Kalkulasi Hasil Nilai
                </span>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">
                    <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-2xs">
                        <span class="text-sm font-semibold text-slate-700 block">Rata-rata</span>
                        <div class="text-2xl font-black text-slate-900 mt-1" x-text="avg !== null ? avg : '-'">-</div>
                        <span class="text-sm text-slate-700 block mt-1" x-text="aspects.length + ' kriteria'"></span>
                    </div>
                    <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-2xs">
                        <span class="text-sm font-semibold text-slate-700 block">Predikat Mutu</span>
                        <div class="text-2xl font-black text-blue-700 mt-1" x-text="letterGrade">-</div>
                    </div>
                    <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-2xs">
                        <span class="text-sm font-semibold text-slate-700 block">Nilai Akhir</span>
                        <div class="text-2xl font-black text-slate-900 mt-1" x-text="finalScore !== null ? finalScore : (avg !== null ? avg : '-')">-</div>
                        @if ($role === 'lecturer' && !$isMentorOnly && $dinasScore > 0)
                            <span class="text-xs text-slate-600 block mt-1 font-medium">
                                Bobot: {{ $weightMentor }}% Dinas, {{ $weightLecturer }}% Kampus
                            </span>
                        @endif
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-slate-200 flex flex-wrap items-center justify-between gap-2 text-sm text-slate-800">
                    <span class="font-bold text-slate-800">Panduan Nilai &amp; Predikat:</span>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 font-medium">
                        <span><strong>A</strong> (&ge; 85)</span>
                        <span><strong>AB</strong> (75&ndash;84,99)</span>
                        <span><strong>B</strong> (65&ndash;74,99)</span>
                        <span><strong>BC</strong> (55&ndash;64,99)</span>
                        <span><strong>C</strong> (40&ndash;54,99)</span>
                        <span><strong>E</strong> (&lt; 40)</span>
                    </div>
                </div>
                @if ($role === 'mentor')
                    <p class="mt-2.5 text-sm text-slate-700 font-medium">
                        {{ $isMentorOnly
                            ? 'Ketentuan kampus/sekolah peserta magang ini: 100% nilai akhir berasal dari Mentor Lapangan.'
                            : "Ketentuan kampus/sekolah peserta magang ini: Bobot {$weightMentor}% nilai Mentor Lapangan dan {$weightLecturer}% nilai Pembimbing." }}
                    </p>
                @endif
            </div>

            <!-- 4. Textarea Catatan -->
            <div>
                <label for="catatan-penilaian" class="block text-base font-bold text-slate-900 mb-1.5">
                    {{ $role === 'mentor' ? 'Catatan Evaluasi Mentor Lapangan' : 'Catatan Evaluasi Pembimbing' }}
                    <span class="font-normal text-slate-600">(boleh dikosongkan)</span>
                </label>
                <textarea id="catatan-penilaian" name="{{ $role === 'mentor' ? 'catatan' : 'feedback_dosen' }}" rows="3"
                          placeholder="Tuliskan catatan evaluasi, rekomendasi karir, atau apresiasi untuk peserta magang..."
                          class="w-full text-base border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 p-3 bg-white shadow-2xs">{{ $oldFeedback }}</textarea>
                @error($role === 'mentor' ? 'catatan' : 'feedback_dosen')
                    <span class="text-rose-700 text-base block mt-1 font-semibold">{{ $message }}</span>
                @enderror
            </div>

            </fieldset>

            <!-- 5. Tombol Aksi di Kanan Bawah: Batal + Simpan Nilai -->
            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <a href="{{ $cancelRoute }}"
                   class="inline-flex items-center justify-center gap-1.5 min-h-[52px] px-5 py-3 bg-white hover:bg-slate-50 text-slate-900 text-base font-bold rounded-xl border-2 border-slate-300 shadow-2xs transition active:scale-95 cursor-pointer">
                    {{ $lockReason ? 'Kembali' : 'Batal' }}
                </a>
                @unless ($lockReason)
                    <button type="button" @click="openSaveConfirm($el)"
                            class="inline-flex items-center justify-center min-h-[52px] px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-base font-bold rounded-xl transition cursor-pointer shadow-xs">
                        Simpan Nilai
                    </button>
                @endunless
            </div>

        </form>
    </div>

</div>
