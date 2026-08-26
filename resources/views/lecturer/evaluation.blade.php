<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('lecturer.dashboard') }}" class="p-2 bg-white hover:bg-gray-100 border border-gray-200 text-gray-700 rounded-xl transition shadow-xs">
                ←
            </a>
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                    <span></span>
                    <span>Formulir Penilaian Akademik DPL (Bobot 60%)</span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                    Evaluasi bimbingan akademik, mutu laporan ilmiah, dan keaktifan mahasiswa magang
                </p>
            </div>
        </div>
    </x-slot>

    @php
        $nilaiDinas = $evaluation?->nilai_pembimbing ?? 0;
        $evalMastery = old('score_mastery', $evaluation?->score_mastery ?? ($evaluation?->nilai_akademik ?? 85));
        $evalReport = old('score_report', $evaluation?->score_report ?? ($evaluation?->nilai_akademik ?? 85));
        $evalAttitude = old('score_attitude', $evaluation?->score_attitude ?? ($evaluation?->nilai_akademik ?? 85));
    @endphp

    <div class="py-8" x-data="{
        scoreMastery: {{ $evalMastery }},
        scoreReport: {{ $evalReport }},
        scoreAttitude: {{ $evalAttitude }},
        scoreDinas: {{ $nilaiDinas }},
        get calculatedDosen() {
            let m = Number(this.scoreMastery) || 0;
            let r = Number(this.scoreReport) || 0;
            let a = Number(this.scoreAttitude) || 0;
            return Math.round(((m + r + a) / 3) * 100) / 100;
        },
        get calculatedFinal() {
            let d = Number(this.scoreDinas) || 0;
            let l = this.calculatedDosen;
            if (d > 0) {
                return Math.round(((0.40 * d) + (0.60 * l)) * 100) / 100;
            }
            return l;
        },
        get letterGrade() {
            let f = this.calculatedFinal;
            if (f >= 85) return 'A (Sangat Baik / Unggul)';
            if (f >= 75) return 'AB (Baik Sekali)';
            if (f >= 65) return 'B (Baik)';
            if (f >= 55) return 'BC (Cukup Baik)';
            if (f >= 40) return 'C (Cukup)';
            return 'E (Tidak Lulus)';
        }
    }">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Error -->
            @if ($errors->any())
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-xs text-rose-900 text-xs">
                    <p class="font-bold">Gagal Menyimpan Nilai:</p>
                    <ul class="mt-1 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Info Mahasiswa & Penempatan -->
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-xs grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Mahasiswa Bimbingan</span>
                    <h3 class="font-black text-gray-900 text-base mt-1">{{ $student->name }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">NIM: {{ $profile->nim ?? '-' }} &bull; {{ $profile->jurusan ?? 'Informatika' }}</p>
                    <span class="inline-block mt-2 px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-xl border border-blue-200">
                         {{ $profile->universitas ?? '-' }}
                    </span>
                </div>

                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Lokasi Magang Dinas</span>
                    <h4 class="font-bold text-gray-800 text-sm mt-1"> {{ $agencyProfile->agency_name ?? '-' }}</h4>
                    <p class="text-xs text-gray-500 mt-0.5">Divisi: {{ $unit->name ?? '-' }}</p>
                    <p class="text-xs text-emerald-700 font-semibold mt-1">Pembimbing Dinas: {{ $mentor->name ?? '-' }}</p>
                </div>
            </div>

            <!-- Nilai Lapangan Dinas Preview (40%) -->
            @if ($evaluation && ($evaluation->nilai_disiplin > 0 || $evaluation->nilai_kinerja > 0))
                <div class="bg-emerald-50/60 rounded-3xl p-5 border border-emerald-100 shadow-xs">
                    <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider block"> Nilai Evaluasi Pembimbing Dinas (Bobot 40%)</span>
                    <div class="grid grid-cols-3 gap-3 mt-3">
                        <div class="bg-white p-3 rounded-xl border border-emerald-100 text-center">
                            <span class="text-[10px] font-bold text-gray-400 uppercase">Nilai Disiplin</span>
                            <p class="text-xl font-black text-emerald-700 mt-0.5">{{ $evaluation->nilai_disiplin }}</p>
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-emerald-100 text-center">
                            <span class="text-[10px] font-bold text-gray-400 uppercase">Nilai Kinerja</span>
                            <p class="text-xl font-black text-emerald-700 mt-0.5">{{ $evaluation->nilai_kinerja }}</p>
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-emerald-100 text-center">
                            <span class="text-[10px] font-bold text-gray-400 uppercase">Nilai Laporan Dinas</span>
                            <p class="text-xl font-black text-emerald-700 mt-0.5">{{ $evaluation->nilai_laporan }}</p>
                        </div>
                    </div>
                    @if ($evaluation->catatan)
                        <p class="text-xs text-gray-600 mt-3 italic bg-white p-2.5 rounded-xl border border-emerald-100">
                            "{{ $evaluation->catatan }}"
                        </p>
                    @endif
                </div>
            @endif

            <!-- Formulir Input Nilai Akademik 60% -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-xs">
                <form action="{{ route('lecturer.evaluations.store', $placement->id) }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- 3 Aspek Grid -->
                    <div class="space-y-4">
                        
                        <!-- 1. Penguasaan Materi -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider">
                                    1. Penguasaan Materi & Teknis Magang <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-xs font-mono font-bold text-blue-700" x-text="scoreMastery + '/100'"></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <input type="range" min="0" max="100" x-model="scoreMastery" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                                <input type="number" name="score_mastery" min="0" max="100" x-model="scoreMastery" required class="w-20 text-center font-black text-base border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono">
                            </div>
                        </div>

                        <!-- 2. Kualitas Laporan -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider">
                                    2. Kualitas & Sistematika Penulisan Laporan Akhir <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-xs font-mono font-bold text-blue-700" x-text="scoreReport + '/100'"></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <input type="range" min="0" max="100" x-model="scoreReport" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                                <input type="number" name="score_report" min="0" max="100" x-model="scoreReport" required class="w-20 text-center font-black text-base border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono">
                            </div>
                        </div>

                        <!-- 3. Sikap & Komunikasi -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider">
                                    3. Sikap, Komunikasi & Keaktifan Bimbingan <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-xs font-mono font-bold text-blue-700" x-text="scoreAttitude + '/100'"></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <input type="range" min="0" max="100" x-model="scoreAttitude" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                                <input type="number" name="score_attitude" min="0" max="100" x-model="scoreAttitude" required class="w-20 text-center font-black text-base border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono">
                            </div>
                        </div>

                    </div>

                    <!-- Live Grade Preview Card -->
                    <div class="p-5 bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 rounded-2xl text-white flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div>
                            <span class="text-xs text-blue-200 font-semibold uppercase tracking-wider block">Kalkulasi Nilai DPL (60%)</span>
                            <div class="text-2xl font-black text-emerald-400 mt-0.5" x-text="calculatedDosen + '/100'"></div>
                            <div class="text-xs text-slate-300 mt-1">
                                Predikat Akhir: <strong class="text-white" x-text="letterGrade"></strong>
                            </div>
                        </div>
                        <div class="text-center sm:text-right bg-white/10 p-3.5 rounded-xl border border-white/10 min-w-[170px]">
                            <span class="text-[11px] uppercase tracking-wider text-slate-300 font-bold block">Nilai Gabungan</span>
                            <p class="text-2xl font-black text-emerald-400 mt-0.5" x-text="calculatedFinal"></p>
                        </div>
                    </div>

                    <!-- Catatan Bimbingan Dosen -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Catatan / Rekomendasi Dosen Pembimbing Lapangan
                        </label>
                        <textarea 
                            name="feedback_dosen" 
                            rows="4" 
                            placeholder="Tuliskan evaluasi komprehensif, masukan penyempurnaan karya ilmiah, atau rekomendasi karir untuk mahasiswa..."
                            class="w-full text-xs sm:text-sm border-gray-200 rounded-2xl shadow-2xs focus:ring-blue-500 focus:border-blue-500"
                        >{{ old('feedback_dosen', $evaluation?->feedback_dosen ?? ($evaluation?->catatan_dosen ?? '')) }}</textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                        <a href="{{ route('lecturer.dashboard') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                            Batal
                        </a>
                        <button type="submit" class="px-7 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                            Simpan Nilai Akademik DPL (60%)
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</x-app-layout>
