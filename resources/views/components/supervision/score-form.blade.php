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

    $oldScore1 = $role === 'mentor'
        ? old('nilai_disiplin', $evaluation?->nilai_disiplin ?? '')
        : old('score_mastery', $evaluation?->dosenAspectScore('score_mastery') ?? '');
    $oldScore2 = $role === 'mentor'
        ? old('nilai_kinerja', $evaluation?->nilai_kinerja ?? '')
        : old('score_report', $evaluation?->dosenAspectScore('score_report') ?? '');
    $oldScore3 = $role === 'mentor'
        ? old('nilai_laporan', $evaluation?->nilai_laporan ?? '')
        : old('score_attitude', $evaluation?->dosenAspectScore('score_attitude') ?? '');
    $oldFeedback = $role === 'mentor'
        ? old('catatan', $evaluation?->catatan ?? '')
        : old('feedback_dosen', $evaluation?->feedback_dosen ?? ($evaluation?->catatan_dosen ?? ''));
@endphp

<div class="space-y-6" x-data="{
    role: '{{ $role }}',
    isMentorOnly: {{ $isMentorOnly && $role === 'lecturer' ? 'true' : 'false' }},
    score1: '{{ $oldScore1 }}',
    score2: '{{ $oldScore2 }}',
    score3: '{{ $oldScore3 }}',
    dinasScore: {{ $dinasScore }},
    weightMentor: {{ $weightMentor }},
    weightLecturer: {{ $weightLecturer }},
    get avg() {
        if (this.isMentorOnly) {
            return this.dinasScore > 0 ? this.dinasScore : null;
        }
        let v1 = parseFloat(this.score1);
        let v2 = parseFloat(this.score2);
        let v3 = parseFloat(this.score3);
        if (isNaN(v1) || isNaN(v2) || isNaN(v3)) return null;
        return Math.round(((v1 + v2 + v3) / 3) * 100) / 100;
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

    <!-- Formulir Penilaian -->
    <div class="bg-white rounded-2xl p-6 sm:p-7 border border-slate-200 shadow-xs">
        <form action="{{ $submitRoute }}" method="POST" class="space-y-6">
            @csrf
            {{-- fieldset disabled: semua input ikut terkunci saat penilaian belum/tidak boleh diubah --}}
            <fieldset @disabled($lockReason) class="space-y-6">

            <!-- 2. Tiga Input Angka Numerik (0 - 100) -->
            @if ($role === 'mentor')
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">
                        Komponen Penilaian Mentor Lapangan
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Masukkan nilai angka 0 sampai 100. Rata-rata dan predikat mutu akan dihitung secara otomatis.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Disiplin -->
                    <div class="bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-2xs flex flex-col justify-between">
                        <div class="min-h-[52px]">
                            <label class="block text-xs font-bold text-slate-800 tracking-wide">
                                1. Nilai Disiplin <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <p class="text-xs text-slate-500 mt-1 leading-normal">Ketepatan kehadiran dan kepatuhan aturan kerja</p>
                        </div>
                        <div class="mt-3">
                            <input type="number" name="nilai_disiplin" min="0" max="100" step="1" x-model="score1" required placeholder="0"
                                   class="w-full text-2xl font-black text-center py-2.5 bg-white border border-slate-200 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition text-slate-900">
                            @error('nilai_disiplin')
                                <span class="text-rose-600 text-xs block mt-1 font-medium">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Kinerja -->
                    <div class="bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-2xs flex flex-col justify-between">
                        <div class="min-h-[52px]">
                            <label class="block text-xs font-bold text-slate-800 tracking-wide">
                                2. Nilai Kinerja <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <p class="text-xs text-slate-500 mt-1 leading-normal">Kualitas hasil tugas dan keaktifan tim</p>
                        </div>
                        <div class="mt-3">
                            <input type="number" name="nilai_kinerja" min="0" max="100" step="1" x-model="score2" required placeholder="0"
                                   class="w-full text-2xl font-black text-center py-2.5 bg-white border border-slate-200 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition text-slate-900">
                            @error('nilai_kinerja')
                                <span class="text-rose-600 text-xs block mt-1 font-medium">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Laporan -->
                    <div class="bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-2xs flex flex-col justify-between">
                        <div class="min-h-[52px]">
                            <label class="block text-xs font-bold text-slate-800 tracking-wide">
                                3. Nilai Laporan <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <p class="text-xs text-slate-500 mt-1 leading-normal">Kelengkapan dan sistematika laporan dinas</p>
                        </div>
                        <div class="mt-3">
                            <input type="number" name="nilai_laporan" min="0" max="100" step="1" x-model="score3" required placeholder="0"
                                   class="w-full text-2xl font-black text-center py-2.5 bg-white border border-slate-200 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition text-slate-900">
                            @error('nilai_laporan')
                                <span class="text-rose-600 text-xs block mt-1 font-medium">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

            @else
                {{-- Role: Lecturer --}}
                @if ($isMentorOnly)
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-slate-900">
                            Nilai Evaluasi Mentor Lapangan
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Rincian nilai yang telah dimasukkan oleh Mentor Lapangan di instansi magang.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-200 text-center space-y-1 shadow-2xs">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Kedisiplinan</span>
                            <div class="text-2xl font-black text-slate-900">{{ $evaluation->nilai_disiplin ?? '-' }}</div>
                        </div>
                        <div class="bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-200 text-center space-y-1 shadow-2xs">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Kinerja & Tugas</span>
                            <div class="text-2xl font-black text-slate-900">{{ $evaluation->nilai_kinerja ?? '-' }}</div>
                        </div>
                        <div class="bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-200 text-center space-y-1 shadow-2xs">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Laporan Dinas</span>
                            <div class="text-2xl font-black text-slate-900">{{ $evaluation->nilai_laporan ?? '-' }}</div>
                        </div>
                    </div>
                @else
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-slate-900">
                            Komponen Penilaian Dosen Pembimbing
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Masukkan nilai angka 0 sampai 100 pada ketiga aspek kompetensi akademik.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Penguasaan Materi -->
                        <div class="bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-2xs flex flex-col justify-between">
                            <div class="min-h-[52px]">
                                <label class="block text-xs font-bold text-slate-800 tracking-wide">
                                    1. Penguasaan Materi & Teknis <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <p class="text-xs text-slate-500 mt-1 leading-normal">Pemahaman teori dan implementasi di instansi</p>
                            </div>
                            <div class="mt-3">
                                <input type="number" name="score_mastery" min="0" max="100" step="1" x-model="score1" required placeholder="0"
                                       class="w-full text-2xl font-black text-center py-2.5 bg-white border border-slate-200 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition text-slate-900">
                                @error('score_mastery')
                                    <span class="text-rose-600 text-xs block mt-1 font-medium">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Kualitas Laporan -->
                        <div class="bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-2xs flex flex-col justify-between">
                            <div class="min-h-[52px]">
                                <label class="block text-xs font-bold text-slate-800 tracking-wide">
                                    2. Kualitas Laporan Ilmiah <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <p class="text-xs text-slate-500 mt-1 leading-normal">Struktur penulisan ilmiah dan analisis hasil</p>
                            </div>
                            <div class="mt-3">
                                <input type="number" name="score_report" min="0" max="100" step="1" x-model="score2" required placeholder="0"
                                       class="w-full text-2xl font-black text-center py-2.5 bg-white border border-slate-200 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition text-slate-900">
                                @error('score_report')
                                    <span class="text-rose-600 text-xs block mt-1 font-medium">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Sikap & Komunikasi -->
                        <div class="bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-2xs flex flex-col justify-between">
                            <div class="min-h-[52px]">
                                <label class="block text-xs font-bold text-slate-800 tracking-wide">
                                    3. Sikap & Komunikasi <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <p class="text-xs text-slate-500 mt-1 leading-normal">Etika, konsultasi dan responsivitas bimbingan</p>
                            </div>
                            <div class="mt-3">
                                <input type="number" name="score_attitude" min="0" max="100" step="1" x-model="score3" required placeholder="0"
                                       class="w-full text-2xl font-black text-center py-2.5 bg-white border border-slate-200 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition text-slate-900">
                                @error('score_attitude')
                                    <span class="text-rose-600 text-xs block mt-1 font-medium">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <!-- 3. Kotak Hasil Polos (Rata-rata, Predikat, dan Nilai Akhir) -->
            <div class="bg-slate-50/70 rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-2xs">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-3">
                    Kalkulasi Hasil Nilai
                </span>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">
                    <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-2xs">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Rata-rata</span>
                        <div class="text-2xl font-black text-slate-900 mt-1" x-text="avg !== null ? avg : '-'">-</div>
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
                            class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition cursor-pointer">
                        Simpan Nilai
                    </button>
                @endunless
            </div>

        </form>
    </div>

</div>
