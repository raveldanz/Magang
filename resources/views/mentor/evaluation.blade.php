<x-app-layout>
    <div class="py-8 bg-[#F5F8FC] min-h-screen text-slate-900 font-sans" x-data="{
        disiplin: {{ old('nilai_disiplin', $placement->evaluation->nilai_disiplin ?? 0) }},
        kinerja: {{ old('nilai_kinerja', $placement->evaluation->nilai_kinerja ?? 0) }},
        laporan: {{ old('nilai_laporan', $placement->evaluation->nilai_laporan ?? 0) }},
        get average() {
            let d = parseFloat(this.disiplin) || 0;
            let k = parseFloat(this.kinerja) || 0;
            let l = parseFloat(this.laporan) || 0;
            return ((d + k + l) / 3).toFixed(2);
        },
        get grade() {
            let avg = parseFloat(this.average);
            if (avg >= 85) return 'A (Sangat Memuaskan)';
            if (avg >= 70) return 'B (Memuaskan)';
            if (avg > 0) return 'C (Cukup)';
            return '-';
        }
    }">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-3">
                    <a href="{{ route('mentor.students.show', $placement->id) }}" class="p-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 rounded-xl transition shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <h2 class="text-xl font-bold tracking-tight text-slate-900">
                            Formulir Penilaian Evaluasi Akhir
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Mahasiswa: <strong class="text-slate-800">{{ $placement->application->user->name ?? '-' }}</strong> ({{ $placement->application->user->studentProfile->nim ?? '-' }})
                        </p>
                    </div>
                </div>

                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-100">
                    🏛️ {{ $placement->application->unit->name ?? '-' }}
                </span>
            </div>

            <!-- Card Informasi Mahasiswa -->
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm shadow-slate-200/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 font-bold text-base flex items-center justify-center shadow-sm shrink-0">
                        {{ strtoupper(substr($placement->application->user->name ?? 'M', 0, 2)) }}
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">{{ $placement->application->user->name ?? '-' }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ $placement->application->user->studentProfile->universitas ?? '-' }} &bull; Jurusan {{ $placement->application->user->studentProfile->jurusan ?? '-' }}
                        </p>
                    </div>
                </div>
                <div class="text-left sm:text-right">
                    <span class="text-[11px] text-slate-400 block uppercase font-semibold">Periode Magang</span>
                    <span class="text-xs font-bold text-slate-700">
                        {{ \Carbon\Carbon::parse($placement->application->start_date)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($placement->application->end_date)->translatedFormat('d M Y') }}
                    </span>
                </div>
            </div>

            <!-- Form Penilaian -->
            <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-100 shadow-sm shadow-slate-200/50 space-y-6">
                
                <form action="{{ route('mentor.evaluations.store', $placement->id) }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <h4 class="text-sm font-bold uppercase tracking-wider text-slate-800 pb-2 border-b border-slate-100">Komponen Penilaian Numerik (Skala 0 - 100)</h4>
                        <p class="text-xs text-slate-500 mt-1">Masukkan nilai angka murni. Nilai rata-rata dan predikat kelulusan akan dihitung secara otomatis untuk transkrip E-Sertifikat.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        
                        <!-- 1. Nilai Disiplin -->
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
                                1. Nilai Disiplin
                            </label>
                            <p class="text-[11px] text-slate-400 mb-2">Ketepatan kehadiran & kepatuhan aturan</p>
                            <input 
                                type="number" 
                                name="nilai_disiplin" 
                                min="0" 
                                max="100" 
                                step="1" 
                                x-model="disiplin" 
                                class="w-full text-lg font-bold text-slate-900 rounded-xl border border-slate-200 bg-white px-3.5 py-2 focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 shadow-sm transition" 
                                required
                            >
                            @error('nilai_disiplin')
                                <span class="text-red-600 text-xs block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- 2. Nilai Kinerja -->
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
                                2. Nilai Kinerja
                            </label>
                            <p class="text-[11px] text-slate-400 mb-2">Kualitas hasil tugas & keaktifan tim</p>
                            <input 
                                type="number" 
                                name="nilai_kinerja" 
                                min="0" 
                                max="100" 
                                step="1" 
                                x-model="kinerja" 
                                class="w-full text-lg font-bold text-slate-900 rounded-xl border border-slate-200 bg-white px-3.5 py-2 focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 shadow-sm transition" 
                                required
                            >
                            @error('nilai_kinerja')
                                <span class="text-red-600 text-xs block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- 3. Nilai Laporan -->
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
                                3. Nilai Laporan
                            </label>
                            <p class="text-[11px] text-slate-400 mb-2">Kelengkapan & sistematika laporan</p>
                            <input 
                                type="number" 
                                name="nilai_laporan" 
                                min="0" 
                                max="100" 
                                step="1" 
                                x-model="laporan" 
                                class="w-full text-lg font-bold text-slate-900 rounded-xl border border-slate-200 bg-white px-3.5 py-2 focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 shadow-sm transition" 
                                required
                            >
                            @error('nilai_laporan')
                                <span class="text-red-600 text-xs block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>

                    <!-- Live Calculation Preview Card -->
                    <div class="bg-blue-600 text-white rounded-2xl p-5 shadow-sm shadow-blue-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-white/20 rounded-xl text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h5 class="text-xs font-bold uppercase tracking-wider text-blue-100">Kalkulasi Otomatis Transkrip Nilai</h5>
                                <p class="text-xs text-blue-200 mt-0.5">Nilai kumulatif yang akan dicetak di E-Sertifikat resmi</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-6">
                            <div class="text-center">
                                <span class="text-[10px] font-semibold text-blue-200 uppercase tracking-wider">Nilai Rata-rata</span>
                                <div class="text-2xl font-extrabold text-white" x-text="average">0.00</div>
                            </div>
                            <div class="text-center border-l border-blue-400/50 pl-6">
                                <span class="text-[10px] font-semibold text-blue-200 uppercase tracking-wider">Predikat</span>
                                <div class="text-xs font-bold text-amber-200" x-text="grade">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan Evaluasi -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                            Catatan / Rekomendasi Mentor
                        </label>
                        <textarea 
                            name="catatan" 
                            rows="4" 
                            placeholder="Tuliskan evaluasi performa, dedikasi, inisiatif, atau catatan kelulusan mahasiswa selama magang..." 
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm p-3.5 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 transition-all duration-200"
                        >{{ old('catatan', $placement->evaluation->catatan ?? '') }}</textarea>
                        @error('catatan')
                            <span class="text-red-600 text-xs block mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Submit Actions -->
                    <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row justify-end items-center gap-3">
                        <a href="{{ route('mentor.students.show', $placement->id) }}" class="w-full sm:w-auto px-4 py-2.5 text-center bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200 text-xs font-medium rounded-xl transition">
                            Batal
                        </a>
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-semibold uppercase tracking-wider rounded-xl shadow-sm shadow-blue-200 hover:scale-[1.01] active:scale-[0.99] transition-all duration-200">
                            Simpan & Terbitkan Penilaian
                        </button>
                    </div>

                </form>

            </div>

        </div>
    </div>
</x-app-layout>