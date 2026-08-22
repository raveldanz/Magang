<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('mentor.students.show', $placement->id) }}" class="p-2 bg-white hover:bg-gray-100 border border-gray-200 text-gray-700 rounded-xl transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                        Formulir Penilaian Evaluasi Akhir
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                        Mahasiswa: <strong class="text-gray-700">{{ $placement->application->user->name }}</strong> ({{ $placement->application->user->studentProfile->nim ?? '-' }})
                    </p>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-100 text-blue-800 text-xs font-semibold px-3.5 py-1.5 rounded-full">
                {{ $placement->application->unit->name ?? '-' }}
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{
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

            <!-- Card Informasi Mahasiswa -->
            <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white font-black text-base flex items-center justify-center shadow-md">
                        {{ strtoupper(substr($placement->application->user->name, 0, 2)) }}
                    </div>
                    <div>
                        <h3 class="font-extrabold text-gray-900 text-lg">{{ $placement->application->user->name }}</h3>
                        <p class="text-xs text-gray-500">
                            {{ $placement->application->user->studentProfile->universitas ?? '-' }} &bull; Jurusan {{ $placement->application->user->studentProfile->jurusan ?? '-' }}
                        </p>
                    </div>
                </div>
                <div class="text-right sm:text-right">
                    <span class="text-xs text-gray-400 block">Periode Pelaksanaan</span>
                    <span class="text-xs font-bold text-gray-700">
                        {{ \Carbon\Carbon::parse($placement->application->start_date)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($placement->application->end_date)->translatedFormat('d M Y') }}
                    </span>
                </div>
            </div>

            <!-- Form Penilaian -->
            <div class="bg-white rounded-2xl p-6 sm:p-8 border border-gray-200 shadow-sm space-y-6">
                
                <form action="{{ route('mentor.evaluations.store', $placement->id) }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <h4 class="text-base font-bold text-gray-900 pb-2 border-b">Komponen Penilaian Numerik (Skala 0 - 100)</h4>
                        <p class="text-xs text-gray-500 mt-1">Masukkan nilai angka murni. Nilai rata-rata dan predikat kelulusan akan dihitung secara otomatis untuk transkrip E-Sertifikat.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        
                        <!-- 1. Nilai Disiplin -->
                        <div class="bg-slate-50/75 p-4 rounded-2xl border border-gray-200 focus-within:border-blue-500 transition">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                1. Nilai Disiplin
                            </label>
                            <p class="text-[11px] text-gray-500 mb-2">Ketepatan kehadiran & kepatuhan aturan</p>
                            <input 
                                type="number" 
                                name="nilai_disiplin" 
                                min="0" 
                                max="100" 
                                step="1" 
                                x-model="disiplin" 
                                class="w-full text-lg font-bold text-gray-900 border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                                required
                            >
                            @error('nilai_disiplin')
                                <span class="text-rose-600 text-xs font-medium block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- 2. Nilai Kinerja -->
                        <div class="bg-slate-50/75 p-4 rounded-2xl border border-gray-200 focus-within:border-blue-500 transition">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                2. Nilai Kinerja
                            </label>
                            <p class="text-[11px] text-gray-500 mb-2">Kualitas hasil tugas & keaktifan tim</p>
                            <input 
                                type="number" 
                                name="nilai_kinerja" 
                                min="0" 
                                max="100" 
                                step="1" 
                                x-model="kinerja" 
                                class="w-full text-lg font-bold text-gray-900 border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                                required
                            >
                            @error('nilai_kinerja')
                                <span class="text-rose-600 text-xs font-medium block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- 3. Nilai Laporan -->
                        <div class="bg-slate-50/75 p-4 rounded-2xl border border-gray-200 focus-within:border-blue-500 transition">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                3. Nilai Laporan
                            </label>
                            <p class="text-[11px] text-gray-500 mb-2">Kelengkapan & sistematika laporan</p>
                            <input 
                                type="number" 
                                name="nilai_laporan" 
                                min="0" 
                                max="100" 
                                step="1" 
                                x-model="laporan" 
                                class="w-full text-lg font-bold text-gray-900 border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                                required
                            >
                            @error('nilai_laporan')
                                <span class="text-rose-600 text-xs font-medium block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>

                    <!-- Live Calculation Preview Card -->
                    <div class="bg-blue-900 text-white rounded-2xl p-5 shadow-inner flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-white/10 rounded-xl text-blue-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h5 class="text-xs font-bold text-blue-200 uppercase tracking-wider">Kalkulasi Otomatis Transkrip Nilai</h5>
                                <p class="text-xs text-blue-300">Nilai kumulatif yang akan dicetak di E-Sertifikat resmi</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-6">
                            <div class="text-center">
                                <span class="text-[11px] font-medium text-blue-300 uppercase">Nilai Rata-rata</span>
                                <div class="text-2xl font-black text-white" x-text="average">0.00</div>
                            </div>
                            <div class="text-center border-l border-blue-700/60 pl-6">
                                <span class="text-[11px] font-medium text-blue-300 uppercase">Predikat</span>
                                <div class="text-sm font-extrabold text-amber-300" x-text="grade">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan Evaluasi -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                            Catatan / Rekomendasi Mentor
                        </label>
                        <textarea 
                            name="catatan" 
                            rows="4" 
                            placeholder="Tuliskan evaluasi performa, dedikasi, inisiatif, atau catatan kelulusan mahasiswa selama magang..."
                            class="w-full text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                        >{{ old('catatan', $placement->evaluation->catatan ?? '') }}</textarea>
                        @error('catatan')
                            <span class="text-rose-600 text-xs font-medium block mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Submit Actions -->
                    <div class="pt-4 border-t border-gray-200 flex flex-col sm:flex-row justify-end items-center gap-3">
                        <a href="{{ route('mentor.students.show', $placement->id) }}" class="w-full sm:w-auto px-5 py-2.5 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                            Batal
                        </a>
                        <button type="submit" class="w-full sm:w-auto px-7 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition cursor-pointer">
                            Simpan & Terbitkan Penilaian
                        </button>
                    </div>

                </form>

            </div>

        </div>
    </div>
</x-app-layout>
