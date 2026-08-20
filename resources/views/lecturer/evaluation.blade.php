<x-app-layout>
    <div class="py-8 bg-[#F5F8FC] min-h-screen text-slate-900 font-sans"
        x-data="{
            nilaiAkademik: {{ old('nilai_akademik', $evaluation->nilai_akademik ?? 85) }},
            get letterGrade() {
                let score = Number(this.nilaiAkademik);
                if (score >= 85) return 'A (Sangat Baik / Unggul)';
                if (score >= 75) return 'B (Baik / Memuaskan)';
                if (score >= 60) return 'C (Cukup)';
                return 'D (Kurang)';
            }
        }">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Page Header -->
            <div class="flex items-center gap-3">
                <a href="{{ route('lecturer.dashboard') }}" class="p-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-600 rounded-xl transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </a>
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-slate-900">
                        Input Penilaian Akademik DPL Kampus
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Berikan penilaian bimbingan akademik dan mutu laporan ilmiah mahasiswa magang</p>
                </div>
            </div>

            <!-- Flash Error -->
            @if ($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-xs">
                    <p class="font-bold">Gagal Menyimpan Nilai:</p>
                    <ul class="mt-1 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Info Mahasiswa & Penempatan -->
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm shadow-slate-200/50 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 block">Mahasiswa Bimbingan</span>
                    <h3 class="font-bold text-slate-900 text-base mt-1">{{ $student->name }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">NIM: {{ $profile->nim ?? '-' }} &bull; {{ $profile->jurusan ?? 'Informatika' }}</p>
                    <span class="inline-block mt-2 px-2.5 py-0.5 bg-blue-50 text-blue-700 text-xs font-semibold rounded-full border border-blue-100">
                        🎓 {{ $profile->universitas ?? '-' }}
                    </span>
                </div>

                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 block">Lokasi Magang Dinas</span>
                    <h4 class="font-bold text-slate-800 text-sm mt-1">🏛️ {{ $agencyProfile->agency_name ?? '-' }}</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Divisi: {{ $unit->name ?? '-' }}</p>
                    <p class="text-xs text-emerald-700 font-semibold mt-1">Pembimbing Dinas: {{ $mentor->name ?? '-' }}</p>
                </div>
            </div>

            <!-- Nilai Lapangan Dinas Preview -->
            @if ($evaluation && ($evaluation->nilai_disiplin > 0 || $evaluation->nilai_kinerja > 0))
                <div class="bg-emerald-50/50 rounded-2xl p-5 border border-emerald-100 shadow-sm">
                    <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider block">💡 Referensi Penilaian Pembimbing Dinas</span>
                    <div class="grid grid-cols-3 gap-3 mt-3">
                        <div class="bg-white p-3 rounded-xl border border-emerald-100 text-center">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Nilai Disiplin</span>
                            <p class="text-xl font-extrabold text-emerald-700 mt-0.5">{{ $evaluation->nilai_disiplin }}</p>
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-emerald-100 text-center">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Nilai Kinerja</span>
                            <p class="text-xl font-extrabold text-emerald-700 mt-0.5">{{ $evaluation->nilai_kinerja }}</p>
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-emerald-100 text-center">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Nilai Laporan Dinas</span>
                            <p class="text-xl font-extrabold text-emerald-700 mt-0.5">{{ $evaluation->nilai_laporan }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Formulir Input Nilai Akademik -->
            <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-100 shadow-sm shadow-slate-200/50">
                <form action="{{ route('lecturer.evaluations.store', $placement->id) }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Input Nilai Akademik -->
                    <div>
                        <label for="nilai_akademik" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                            Nilai Bimbingan & Laporan Akademik Kampus (Skala 0 - 100)
                        </label>
                        <div class="mt-2 flex items-center gap-4">
                            <input 
                                type="range" 
                                min="0" 
                                max="100" 
                                x-model="nilaiAkademik" 
                                class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-blue-600"
                            >
                            <input 
                                type="number" 
                                id="nilai_akademik" 
                                name="nilai_akademik" 
                                min="0" 
                                max="100" 
                                x-model="nilaiAkademik" 
                                class="w-24 text-center font-extrabold text-lg rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 shadow-sm"
                                required
                            >
                        </div>
                    </div>

                    <!-- Live Grade Preview Card -->
                    <div class="p-4 bg-blue-50 rounded-xl border border-blue-100 flex items-center justify-between">
                        <div>
                            <span class="text-xs text-blue-700 font-semibold">Predikat Kelulusan Akademik:</span>
                            <h4 class="text-base font-extrabold text-blue-900 mt-0.5" x-text="letterGrade"></h4>
                        </div>
                        <div class="text-right">
                            <span class="text-[11px] text-blue-600 font-medium">Skor DPL:</span>
                            <p class="text-2xl font-black text-blue-700" x-text="nilaiAkademik"></p>
                        </div>
                    </div>

                    <!-- Catatan Bimbingan Dosen -->
                    <div>
                        <label for="catatan_dosen" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                            Catatan / Rekomendasi Dosen Pembimbing Lapangan
                        </label>
                        <textarea 
                            id="catatan_dosen" 
                            name="catatan_dosen" 
                            rows="4" 
                            placeholder="Tuliskan evaluasi komprehensif, masukan penyempurnaan karya ilmiah, atau rekomendasi..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm p-3.5 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 transition-all duration-200"
                        >{{ old('catatan_dosen', $evaluation->catatan_dosen ?? '') }}</textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                        <a href="{{ route('lecturer.dashboard') }}" class="px-4 py-2.5 bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200 text-xs font-medium rounded-xl transition">
                            Kembali
                        </a>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-semibold uppercase tracking-wider rounded-xl shadow-sm shadow-blue-200 hover:scale-[1.01] active:scale-[0.99] transition-all duration-200">
                            Simpan Nilai Akademik
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</x-app-layout>