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
    $scheme = $univ->evaluation_scheme ?? 'dual_evaluation';
    $weightMentor = $univ ? (int) $univ->weight_mentor : 40;
    $weightLecturer = $univ ? (int) $univ->weight_lecturer : 60;
    $isMentorOnly = ($scheme === 'mentor_only');
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
                    ['name' => 'Kedisiplinan & Tata Tertib Kerja', 'score' => ($evaluation?->nilai_disiplin > 0 ? (string)$evaluation->nilai_disiplin : '')],
                    ['name' => 'Kinerja Teknis & Kerjasama Tim', 'score' => ($evaluation?->nilai_kinerja > 0 ? (string)$evaluation->nilai_kinerja : '')],
                    ['name' => 'Inisiatif & Laporan Kedinasan', 'score' => ($evaluation?->nilai_laporan > 0 ? (string)$evaluation->nilai_laporan : '')],
                ];
            } else {
                $initialAspects = [
                    ['name' => 'Penguasaan Materi & Relevansi CPL', 'score' => ($evaluation?->score_mastery > 0 ? (string)$evaluation->score_mastery : '')],
                    ['name' => 'Kualitas Laporan Akhir Magang', 'score' => ($evaluation?->score_report > 0 ? (string)$evaluation->score_report : '')],
                    ['name' => 'Sikap, Komunikasi & Etika Bimbingan', 'score' => ($evaluation?->score_attitude > 0 ? (string)$evaluation->score_attitude : '')],
                ];
            }
        }
    }

    $oldFeedback = $role === 'mentor'
        ? old('catatan', $evaluation?->catatan ?? '')
        : old('feedback_dosen', $evaluation?->feedback_dosen ?? ($evaluation?->catatan_dosen ?? ''));
@endphp

<div class="space-y-6" x-data="{
    role: '{{ $role }}',
    isMentorOnly: {{ $isMentorOnly && $role === 'lecturer' ? 'true' : 'false' }},
    aspects: @js($initialAspects),
    dinasScore: {{ $dinasScore }},
    weightMentor: {{ $weightMentor }},
    weightLecturer: {{ $weightLecturer }},
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

    <!-- 1. Info Singkat Mahasiswa -->
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-2xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Mahasiswa</span>
                <span class="text-sm font-bold text-slate-900 block mt-0.5">{{ $student->name ?? '-' }}</span>
                <span class="text-xs text-slate-500 font-mono">NIM: {{ $profile->nim ?? '-' }}</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Perguruan Tinggi & Prodi</span>
                <span class="text-sm font-medium text-slate-800 block mt-0.5">{{ $profile->universitas ?? ($univ->name ?? '-') }}</span>
                <span class="text-xs text-slate-500">{{ $profile->jurusan ?? '-' }}</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Lokasi & Unit Kerja</span>
                <span class="text-sm font-medium text-slate-800 block mt-0.5">{{ $agencyProfile->agency_name ?? '-' }}</span>
                <span class="text-xs text-slate-500">{{ $unit->name ?? '-' }}</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Periode Magang</span>
                <span class="text-sm font-medium text-slate-800 block mt-0.5">
                    {{ $placement->application?->start_date ? \Carbon\Carbon::parse($placement->application->start_date)->translatedFormat('d M Y') : '-' }} s/d {{ $placement->application?->end_date ? \Carbon\Carbon::parse($placement->application->end_date)->translatedFormat('d M Y') : '-' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Notifikasi Khusus jika Kampus Memberlakukan Mentor Only bagi Dosen -->
    @if ($role === 'lecturer' && $isMentorOnly)
        <div class="bg-blue-50/60 rounded-2xl p-4 sm:p-5 border border-blue-200 text-sm text-blue-900 shadow-2xs">
            <div class="font-bold mb-1">Kebijakan Evaluasi Kampus: 100% Mentor Lapangan</div>
            <p class="text-xs text-blue-800 leading-relaxed">
                Perguruan tinggi <strong>{{ $profile->universitas ?? ($univ->name ?? 'Mahasiswa') }}</strong> memberlakukan penilaian magang penuh dari Mentor Lapangan. Anda dapat meninjau nilai di bawah dan melengkapi catatan evaluasi akhir bimbingan.
            </p>
        </div>
    @endif

    @if ($lockReason)
        <div class="bg-slate-50 rounded-2xl p-4 sm:p-5 border border-slate-300 text-sm text-slate-700">
            <div class="font-bold text-slate-900 mb-1">Form penilaian terkunci</div>
            <p>{{ $lockReason }}</p>
        </div>
    @endif

    <!-- Formulir Penilaian Dinamis -->
    <div class="bg-white rounded-2xl p-6 sm:p-7 border border-slate-200 shadow-xs">
        <form action="{{ $submitRoute }}" method="POST" class="space-y-6">
            @csrf
            {{-- fieldset disabled: semua input ikut terkunci saat penilaian belum/tidak boleh diubah --}}
            <fieldset @disabled($lockReason) class="space-y-6">

            @if ($role === 'lecturer' && $isMentorOnly)
                <!-- Tampilan Read-Only Nilai Mentor untuk DPL di Kampus Mentor Only -->
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">
                        Nilai Evaluasi Mentor Lapangan
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Rincian nilai yang telah dimasukkan oleh Mentor Lapangan di instansi magang.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @forelse ($evaluation?->mentor_aspects_list ?? [] as $mAspect)
                        <div class="bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-200 text-center space-y-1 shadow-2xs">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">{{ $mAspect['name'] }}</span>
                            <div class="text-2xl font-black text-slate-900">{{ $mAspect['score'] }}</div>
                        </div>
                    @empty
                        <div class="col-span-3 p-4 bg-slate-50 rounded-xl text-center text-slate-400 text-xs">
                            Belum ada nilai yang dimasukkan oleh Mentor Lapangan.
                        </div>
                    @endforelse
                </div>
            @else
                <!-- Header Aspek Penilaian Dinamis & Petunjuk -->
                <div class="space-y-2">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h3 class="text-sm sm:text-base font-bold text-slate-900">
                                {{ $role === 'mentor' ? 'Aspek & Kriteria Penilaian Mentor Lapangan' : 'Aspek & Kriteria Penilaian Akademik DPL' }}
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Sesuaikan nama kriteria dan skor penilaian di bawah agar cocok dengan rubrik perguruan tinggi mahasiswa.
                            </p>
                        </div>
                        <button type="button" @click="addAspect()"
                                class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-xl transition cursor-pointer self-start sm:self-auto shadow-2xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <span>Tambah Aspek</span>
                        </button>
                    </div>

                    <!-- Preset Chips Bantuan Cepat -->
                    <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-100 flex flex-wrap items-center gap-1.5 text-xs">
                        <span class="text-[11px] font-semibold text-slate-500 mr-1">Rekomendasi Rubrik Kampus:</span>
                        @if ($role === 'mentor')
                            <button type="button" @click="addAspect('Kedisiplinan & Presensi Kehadiran')" class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 text-[11px] font-medium rounded-lg border border-slate-200 transition cursor-pointer shadow-2xs">+ Kehadiran</button>
                            <button type="button" @click="addAspect('Kualitas Output & Ketepatan Tugas')" class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 text-[11px] font-medium rounded-lg border border-slate-200 transition cursor-pointer shadow-2xs">+ Kualitas Tugas</button>
                            <button type="button" @click="addAspect('Inisiatif, Problem Solving & Kerja Tim')" class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 text-[11px] font-medium rounded-lg border border-slate-200 transition cursor-pointer shadow-2xs">+ Kerjasama Tim</button>
                            <button type="button" @click="addAspect('Etika Kerja & Komunikasi Kedinasan')" class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 text-[11px] font-medium rounded-lg border border-slate-200 transition cursor-pointer shadow-2xs">+ Etika Kerja</button>
                            <button type="button" @click="addAspect('Sistematika Laporan Magang Instansi')" class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 text-[11px] font-medium rounded-lg border border-slate-200 transition cursor-pointer shadow-2xs">+ Laporan Dinas</button>
                        @else
                            <button type="button" @click="addAspect('Penguasaan Teori & Relevansi CPL')" class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 text-[11px] font-medium rounded-lg border border-slate-200 transition cursor-pointer shadow-2xs">+ Relevansi CPL</button>
                            <button type="button" @click="addAspect('Sistematika & Mutu Laporan Akhir')" class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 text-[11px] font-medium rounded-lg border border-slate-200 transition cursor-pointer shadow-2xs">+ Mutu Laporan</button>
                            <button type="button" @click="addAspect('Ujian Presentasi / Sidang Magang')" class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 text-[11px] font-medium rounded-lg border border-slate-200 transition cursor-pointer shadow-2xs">+ Ujian Presentasi</button>
                            <button type="button" @click="addAspect('Sikap, Etika & Responsivitas Bimbingan')" class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 text-[11px] font-medium rounded-lg border border-slate-200 transition cursor-pointer shadow-2xs">+ Etika Bimbingan</button>
                            <button type="button" @click="addAspect('Inovasi & Penyelesaian Kasus Industri')" class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 text-[11px] font-medium rounded-lg border border-slate-200 transition cursor-pointer shadow-2xs">+ Inovasi Kasus</button>
                        @endif
                    </div>
                </div>

                <!-- Daftar Baris Aspek Input Dinamis (Editable Aspects Repeater) -->
                <div class="space-y-3">
                    <template x-for="(aspect, index) in aspects" :key="index">
                        <div class="p-4 sm:p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 transition hover:border-slate-300">
                            
                            <!-- 1. Nomor & Input Nama Aspek Kriteria -->
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="w-8 h-8 rounded-xl bg-white border border-slate-200 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs"
                                     x-text="index + 1">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                                        Nama Aspek / Kriteria <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" :name="'aspects[' + index + '][name]'" x-model="aspect.name" required
                                           placeholder="Contoh: Penguasaan Materi / Sikap & Komunikasi"
                                           class="w-full text-xs sm:text-sm font-semibold bg-white border border-slate-200 rounded-xl px-3.5 py-2 shadow-2xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition text-slate-900">
                                </div>
                            </div>

                            <!-- 2. Input Skor Nilai Numerik (0 - 100) & Tombol Hapus -->
                            <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1 text-center sm:text-right">
                                        Nilai (0 - 100) <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="w-28 sm:w-32">
                                        <input type="number" :name="'aspects[' + index + '][score]'" min="0" max="100" step="1" x-model="aspect.score" required placeholder="0"
                                               class="w-full text-xl sm:text-2xl font-black text-center py-1.5 bg-white border border-slate-200 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition text-slate-900">
                                    </div>
                                </div>

                                <div class="pt-5">
                                    <button type="button" @click="removeAspect(index)"
                                            :disabled="aspects.length <= 1"
                                            :class="aspects.length <= 1 ? 'opacity-30 cursor-not-allowed text-slate-400' : 'text-slate-400 hover:text-rose-600 hover:bg-rose-50 cursor-pointer'"
                                            class="p-2.5 rounded-xl border border-slate-200 bg-white transition shadow-2xs"
                                            title="Hapus kriteria ini">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Tombol Tambah Baris di Bawah List -->
                <div class="flex items-center justify-between pt-1">
                    <button type="button" @click="addAspect()"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Tambah Aspek Penilaian Lainnya</span>
                    </button>
                    <span class="text-xs text-slate-400 font-medium" x-text="aspects.length + ' aspek kriteria aktif'"></span>
                </div>
            @endif

            <!-- 3. Kotak Hasil Kalkulasi Real-time (Rata-rata, Predikat Mutu, Nilai Akhir) -->
            <div class="bg-slate-50/70 rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-2xs">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-3">
                    Kalkulasi Hasil Nilai
                </span>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">
                    <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-2xs">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Rata-rata</span>
                        <div class="text-2xl font-black text-slate-900 mt-1" x-text="avg !== null ? avg : '-'">-</div>
                        <span class="text-[11px] text-slate-400 block mt-1" x-text="aspects.length + ' kriteria'"></span>
                    </div>
                    <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-2xs">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Predikat Mutu</span>
                        <div class="text-2xl font-black text-blue-700 mt-1" x-text="letterGrade">-</div>
                    </div>
                    <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-2xs">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Nilai Akhir</span>
                        <div class="text-2xl font-black text-slate-900 mt-1" x-text="finalScore !== null ? finalScore : (avg !== null ? avg : '-')">-</div>
                        @if ($role === 'lecturer' && !$isMentorOnly && $dinasScore > 0)
                            <span class="text-[11px] text-slate-500 block mt-1 font-medium">
                                Bobot: {{ $weightMentor }}% Dinas, {{ $weightLecturer }}% Kampus
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 4. Textarea Catatan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    {{ $role === 'mentor' ? 'Catatan Evaluasi Mentor Lapangan:' : 'Catatan Evaluasi Dosen Pembimbing:' }}
                </label>
                <textarea name="{{ $role === 'mentor' ? 'catatan' : 'feedback_dosen' }}" rows="3"
                          placeholder="Tuliskan catatan evaluasi, rekomendasi karir, atau apresiasi untuk mahasiswa..."
                          class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 p-3 bg-white shadow-2xs">{{ $oldFeedback }}</textarea>
                @error($role === 'mentor' ? 'catatan' : 'feedback_dosen')
                    <span class="text-rose-600 text-xs block mt-1 font-medium">{{ $message }}</span>
                @enderror
            </div>

            </fieldset>

            <!-- 5. Tombol Aksi di Kanan Bawah: Batal + Simpan Nilai -->
            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <a href="{{ $cancelRoute }}" 
                   class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 hover:border-slate-300 shadow-2xs transition active:scale-95 cursor-pointer">
                    {{ $lockReason ? 'Kembali' : 'Batal' }}
                </a>
                @unless ($lockReason)
                    <button type="submit"
                            class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition cursor-pointer shadow-xs">
                        Simpan Nilai
                    </button>
                @endunless
            </div>

        </form>
    </div>

</div>
