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

    // Initial dynamic components
    $initialComponents = [];
    if ($role === 'mentor') {
        if ($evaluation && !empty($evaluation->getMentorComponents())) {
            $initialComponents = $evaluation->getMentorComponents();
        } else {
            $rawComps = $univ ? $univ->getDinasComponents() : \App\Models\University::DEFAULT_DINAS_COMPONENTS;
            foreach ($rawComps as $c) {
                $initialComponents[] = ['name' => is_array($c) ? ($c['name'] ?? '') : (string) $c, 'score' => ''];
            }
        }
    } else {
        if ($evaluation && !empty($evaluation->getLecturerComponents())) {
            $initialComponents = $evaluation->getLecturerComponents();
        } else {
            $rawComps = $univ ? $univ->getDplComponents() : \App\Models\University::DEFAULT_DPL_COMPONENTS;
            foreach ($rawComps as $c) {
                $initialComponents[] = ['name' => is_array($c) ? ($c['name'] ?? '') : (string) $c, 'score' => ''];
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
    components: @js($initialComponents),
    dinasScore: {{ $dinasScore }},
    weightMentor: {{ $weightMentor }},
    weightLecturer: {{ $weightLecturer }},
    addComponent() {
        this.components.push({
            name: 'Aspek Penilaian ' + (this.components.length + 1),
            score: ''
        });
    },
    removeComponent(index) {
        if (this.components.length > 1) {
            this.components.splice(index, 1);
        }
    },
    get avg() {
        if (this.isMentorOnly) {
            return this.dinasScore > 0 ? this.dinasScore : null;
        }
        let valid = this.components
            .map(c => parseFloat(c.score))
            .filter(s => !isNaN(s) && s >= 0 && s <= 100);
        if (valid.length === 0) return null;
        let sum = valid.reduce((a, b) => a + b, 0);
        return Math.round((sum / valid.length) * 100) / 100;
    },
    get finalScore() {
        if (this.role === 'mentor') {
            return this.avg;
        }
        if (this.isMentorOnly) {
            return this.dinasScore > 0 ? this.dinasScore : null;
        }
        if (this.weightMentor === 0) {
            return this.avg;
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
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Instansi & Bidang</span>
                <span class="text-sm font-medium text-slate-800 block mt-0.5">{{ $agencyProfile->agency_name ?? 'Dinas Perpustakaan dan Kearsipan' }}</span>
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
            <div class="font-bold mb-1">Kebijakan Evaluasi Kampus: 100% Mentor Lapangan Dinas</div>
            <p class="text-xs text-blue-800 leading-relaxed">
                Perguruan tinggi <strong>{{ $profile->universitas ?? ($univ->name ?? 'Mahasiswa') }}</strong> memberlakukan penilaian mandiri instansi. Anda dapat meninjau nilai pembimbing di bawah dan melengkapi catatan evaluasi bimbingan.
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
            <fieldset @disabled($lockReason) class="space-y-6">

            <!-- Hidden Inputs untuk Backward Compatibility Controller/Validator -->
            @if ($role === 'mentor')
                <input type="hidden" name="nilai_disiplin" :value="components[0] ? (components[0].score || 0) : 0">
                <input type="hidden" name="nilai_kinerja" :value="components[1] ? (components[1].score || components[0]?.score || 0) : 0">
                <input type="hidden" name="nilai_laporan" :value="components[2] ? (components[2].score || components[0]?.score || 0) : 0">
            @else
                <input type="hidden" name="score_mastery" :value="components[0] ? (components[0].score || 0) : 0">
                <input type="hidden" name="score_report" :value="components[1] ? (components[1].score || components[0]?.score || 0) : 0">
                <input type="hidden" name="score_attitude" :value="components[2] ? (components[2].score || components[0]?.score || 0) : 0">
            @endif

            @if ($role === 'mentor' || !$isMentorOnly)
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-200 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">
                            {{ $role === 'mentor' ? 'Komponen Penilaian Pembimbing Lapangan Dinas' : 'Komponen Penilaian Dosen Pembimbing Lapangan' }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Setiap baris aspek dapat disesuaikan teksnya, ditambah komponen baru, atau dihapus. Masukkan nilai skala 0 - 100.
                        </p>
                    </div>
                    <button type="button" @click="addComponent()" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl font-bold text-xs shadow-2xs transition shrink-0 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>+ Tambah Komponen</span>
                    </button>
                </div>

                {{-- Dynamic Components Rows (Alpine.js) --}}
                <div class="space-y-3">
                    <template x-for="(comp, index) in components" :key="index">
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 flex flex-col sm:flex-row sm:items-center gap-3 transition">
                            <div class="flex items-center gap-2 text-xs font-bold text-slate-400 w-8 shrink-0">
                                <span x-text="(index + 1) + '.'"></span>
                            </div>

                            {{-- Input Nama Aspek Kompetensi (Bisa diedit sendiri) --}}
                            <div class="flex-1">
                                <input type="text" 
                                       :name="'custom_components[' + index + '][name]'" 
                                       x-model="comp.name" 
                                       required 
                                       placeholder="Nama aspek kompetensi..."
                                       class="w-full text-xs sm:text-sm font-semibold text-slate-900 bg-white border border-slate-300 rounded-xl px-3 py-2 shadow-2xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
                            </div>

                            {{-- Input Skor Numerik (0-100) --}}
                            <div class="w-full sm:w-36 shrink-0 flex items-center gap-2">
                                <input type="number" 
                                       :name="'custom_components[' + index + '][score]'" 
                                       min="0" max="100" step="1" 
                                       x-model="comp.score" 
                                       required 
                                       placeholder="0 - 100"
                                       class="w-full text-base font-black text-center py-2 bg-white border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition text-slate-900">
                                
                                {{-- Tombol Hapus Baris Komponen --}}
                                <button type="button" 
                                        @click="removeComponent(index)" 
                                        x-show="components.length > 1"
                                        title="Hapus Aspek Ini"
                                        class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            @else
                {{-- Role: Lecturer & isMentorOnly (Tampilkan Nilai Dinas yang Sudah Masuk) --}}
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">
                        Nilai Evaluasi Resmi Mentor Lapangan Dinas
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Rincian aspek kompetensi dan skor yang telah dinilai oleh Mentor Lapangan.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    @forelse($evaluation?->getMentorComponents() ?? [] as $mComp)
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 text-center space-y-1">
                            <span class="text-xs font-bold text-slate-500 block truncate">{{ $mComp['name'] }}</span>
                            <div class="text-2xl font-black text-slate-900">{{ $mComp['score'] ?? '-' }}</div>
                        </div>
                    @empty
                        <div class="col-span-3 text-center py-4 text-xs text-slate-400">
                            Mentor Lapangan belum menginputkan nilai evaluasi.
                        </div>
                    @endforelse
                </div>
            @endif

            <!-- 3. Kotak Hasil Polos (Rata-rata, Predikat, dan Nilai Akhir) -->
            <div class="bg-slate-50/70 rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-2xs">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-3">
                    Kalkulasi Otomatis Rata-rata & Nilai Akhir
                </span>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">
                    <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-2xs">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Rata-rata Komponen</span>
                        <div class="text-2xl font-black text-slate-900 mt-1" x-text="avg !== null ? avg : '-'">-</div>
                    </div>
                    <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-2xs">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Predikat Mutu</span>
                        <div class="text-2xl font-black text-blue-700 mt-1" x-text="letterGrade">-</div>
                    </div>
                    <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-2xs">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Nilai Akhir Magang</span>
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
                    {{ $role === 'mentor' ? 'Catatan Evaluasi Pembimbing Lapangan Dinas:' : 'Catatan Evaluasi Dosen Pembimbing Lapangan:' }}
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
                            class="inline-flex items-center justify-center px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-sm transition cursor-pointer">
                        Simpan Nilai Evaluasi
                    </button>
                @endunless
            </div>

        </form>
    </div>

</div>
