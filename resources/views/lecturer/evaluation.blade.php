<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('lecturer.dashboard') }}" class="p-2 bg-white hover:bg-gray-100 border border-gray-200 text-gray-700 rounded-xl transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    Input Penilaian Akademik DPL Kampus
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Berikan penilaian bimbingan akademik dan mutu laporan ilmiah mahasiswa magang</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
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

            <!-- Flash Error -->
            @if ($errors->any())
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-lg shadow-sm text-rose-900 text-xs">
                    <p class="font-bold">Gagal Menyimpan Nilai:</p>
                    <ul class="mt-1 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Info Mahasiswa & Penempatan -->
            <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Mahasiswa Bimbingan</span>
                    <h3 class="font-bold text-gray-900 text-base mt-1">{{ $student->name }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">NIM: {{ $profile->nim ?? '-' }} &bull; {{ $profile->jurusan ?? 'Informatika' }}</p>
                    <span class="inline-block mt-2 px-2 py-0.5 bg-indigo-50 text-indigo-700 text-xs font-semibold rounded-md">
                        🎓 {{ $profile->universitas ?? '-' }}
                    </span>
                </div>

                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Lokasi Magang Dinas</span>
                    <h4 class="font-bold text-gray-800 text-sm mt-1">🏛️ {{ $agencyProfile->agency_name ?? '-' }}</h4>
                    <p class="text-xs text-gray-500 mt-0.5">Divisi: {{ $unit->name ?? '-' }}</p>
                    <p class="text-xs text-emerald-700 font-medium mt-1">Pembimbing Dinas: {{ $mentor->name ?? '-' }}</p>
                </div>
            </div>

            <!-- Nilai Lapangan Dinas Preview -->
            @if ($evaluation && ($evaluation->nilai_disiplin > 0 || $evaluation->nilai_kinerja > 0))
                <div class="bg-emerald-50/50 rounded-2xl p-5 border border-emerald-200 shadow-sm">
                    <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider block">💡 Referensi Penilaian Pembimbing Dinas</span>
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
                        <p class="text-xs text-gray-600 mt-3 italic bg-white p-2.5 rounded-lg border border-emerald-100">
                            "{{ $evaluation->catatan }}"
                        </p>
                    @endif
                </div>
            @endif

            <!-- Formulir Input Nilai Akademik -->
            <div class="bg-white rounded-2xl p-6 sm:p-8 border border-gray-200 shadow-sm">
                <form action="{{ route('lecturer.evaluations.store', $placement->id) }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Input Nilai Akademik -->
                    <div>
                        <x-input-label for="nilai_akademik" value="Nilai Bimbingan & Laporan Akademik Kampus (Skala 0 - 100)" />
                        <div class="mt-2 flex items-center gap-4">
                            <input 
                                type="range" 
                                min="0" 
                                max="100" 
                                x-model="nilaiAkademik" 
                                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600"
                            >
                            <input 
                                type="number" 
                                id="nilai_akademik" 
                                name="nilai_akademik" 
                                min="0" 
                                max="100" 
                                x-model="nilaiAkademik" 
                                class="w-24 text-center font-black text-lg border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                                required
                            >
                        </div>
                        <p class="text-xs text-gray-500 mt-1.5">Mencakup ketepatan metodologi ilmiah, substansi analisis magang, dan keaktifan konsultasi bimbingan.</p>
                    </div>

                    <!-- Live Grade Preview Card -->
                    <div class="p-4 bg-indigo-50/75 rounded-xl border border-indigo-100 flex items-center justify-between">
                        <div>
                            <span class="text-xs text-indigo-700 font-semibold">Predikat Kelulusan Akademik:</span>
                            <h4 class="text-base font-black text-indigo-900 mt-0.5" x-text="letterGrade"></h4>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-indigo-500 font-medium">Skor DPL:</span>
                            <p class="text-2xl font-black text-indigo-700" x-text="nilaiAkademik"></p>
                        </div>
                    </div>

                    <!-- Catatan Bimbingan Dosen -->
                    <div>
                        <x-input-label for="catatan_dosen" value="Catatan / Rekomendasi Dosen Pembimbing Lapangan" />
                        <textarea 
                            id="catatan_dosen" 
                            name="catatan_dosen" 
                            rows="4" 
                            placeholder="Tuliskan evaluasi komprehensif, masukan penyempurnaan karya ilmiah, atau rekomendasi karir untuk mahasiswa..."
                            class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                        >{{ old('catatan_dosen', $evaluation->catatan_dosen ?? '') }}</textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                        <a href="{{ route('lecturer.dashboard') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                            Kembali
                        </a>
                        <button type="submit" class="px-7 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md transition">
                            Simpan Nilai Akademik
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</x-app-layout>
