<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('lecturer.dashboard') }}" class="p-2 bg-white hover:bg-gray-100 border border-gray-200 text-gray-700 rounded-xl transition shadow-xs">
                ←
            </a>
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                    <span>👨‍🎓</span>
                    <span>Monitoring & Penilaian: {{ $student->name }}</span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                    NIM: {{ $profile->nim ?? '-' }} &bull; {{ $profile->universitas ?? '-' }} &bull; Penempatan: {{ $agencyProfile->agency_name ?? '-' }}
                </p>
            </div>
        </div>
    </x-slot>

    @php
        $nilaiDinas = $evaluation ? $evaluation->nilai_pembimbing : 0;
        $evalMastery = old('score_mastery', $evaluation->score_mastery ?? ($evaluation->nilai_akademik ?? 85));
        $evalReport = old('score_report', $evaluation->score_report ?? ($evaluation->nilai_akademik ?? 85));
        $evalAttitude = old('score_attitude', $evaluation->score_attitude ?? ($evaluation->nilai_akademik ?? 85));
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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <!-- Flash Alert -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <span>✅</span>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-xs text-rose-900 text-xs space-y-1">
                    <p class="font-bold">Terjadi kesalahan input:</p>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- 1. HERO IDENTITY CARD -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-xs grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                <!-- Info Mahasiswa -->
                <div class="md:col-span-1 border-b md:border-b-0 md:border-r border-gray-100 pb-4 md:pb-0 md:pr-4">
                    <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Identitas Mahasiswa</span>
                    <h3 class="font-black text-xl text-gray-900 mt-1">{{ $student->name }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">NIM: <span class="font-mono font-bold text-gray-700">{{ $profile->nim ?? '-' }}</span></p>
                    <p class="text-xs text-gray-500">Program Studi: <span class="font-semibold text-gray-700">{{ $profile->jurusan ?? 'Informatika' }}</span></p>
                    <div class="mt-3">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded-xl text-xs font-bold">
                            🎓 {{ $profile->universitas ?? '-' }}
                        </span>
                    </div>
                </div>

                <!-- Lokasi Magang & Pembimbing Dinas -->
                <div class="md:col-span-1 border-b md:border-b-0 md:border-r border-gray-100 pb-4 md:pb-0 md:pr-4">
                    <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Penempatan Instansi</span>
                    <h4 class="font-bold text-base text-gray-900 mt-1">🏛️ {{ $agencyProfile->agency_name ?? '-' }}</h4>
                    <p class="text-xs text-gray-500 mt-0.5">Divisi: <span class="font-semibold text-gray-700">{{ $unit->name ?? '-' }}</span></p>
                    <div class="mt-2 text-xs text-gray-600">
                        👔 Mentor: <strong>{{ $mentor->name ?? 'Belum Ditugaskan' }}</strong>
                    </div>
                </div>

                <!-- Status Evaluasi & Nilai Gabungan -->
                <div class="md:col-span-1">
                    <span class="text-xs font-bold text-amber-600 uppercase tracking-wider">Status Kelulusan</span>
                    <div class="mt-2 space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500">Skor Dinas (40%):</span>
                            <span class="font-bold text-gray-800">{{ $nilaiDinas > 0 ? $nilaiDinas . '/100' : 'Belum Ada' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500">Skor DPL (60%):</span>
                            <span class="font-bold text-blue-700" x-text="calculatedDosen + '/100'"></span>
                        </div>
                        <div class="flex items-center justify-between text-xs pt-2 border-t border-gray-100">
                            <span class="font-bold text-gray-900">Nilai Akhir:</span>
                            <span class="font-black text-emerald-700 text-sm" x-text="calculatedFinal + ' (' + letterGrade.split(' ')[0] + ')'"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. SECTION LAPORAN AKHIR MAGANG (APPROVAL / REVISION) -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-xs space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg font-bold">
                            📂
                        </div>
                        <div>
                            <h3 class="font-black text-base text-gray-900">Verifikasi Dokumen Laporan Akhir Magang</h3>
                            <p class="text-xs text-gray-400">Tinjau naskah laporan ilmiah akhir mahasiswa dan berikan persetujuan atau catatan revisi</p>
                        </div>
                    </div>

                    <div>
                        @if(!$finalReport)
                            <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600">
                                ⚪ Mahasiswa Belum Mengunggah Laporan
                            </span>
                        @elseif($finalReport->status === 'approved')
                            <span class="px-3 py-1.5 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                ✅ Laporan Disetujui (ACC)
                            </span>
                        @elseif($finalReport->status === 'revision')
                            <span class="px-3 py-1.5 rounded-full text-xs font-black bg-rose-100 text-rose-800 border border-rose-300">
                                ⚠️ Perlu Perbaikan (Revisi)
                            </span>
                        @else
                            <span class="px-3 py-1.5 rounded-full text-xs font-black bg-amber-100 text-amber-800 border border-amber-300">
                                ⏳ Menunggu Keputusan DPL
                            </span>
                        @endif
                    </div>
                </div>

                @if($finalReport)
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="font-bold text-gray-900 text-sm flex items-center gap-2">
                                <span>📄</span>
                                <span>{{ $finalReport->title ?? 'Naskah Laporan Akhir MBKM' }}</span>
                            </div>
                            @if($finalReport->repository_url)
                                <a href="{{ $finalReport->repository_url }}" target="_blank" class="text-xs text-blue-600 hover:text-blue-800 font-semibold block">
                                    🔗 Repositori Proyek / Luaran Kerja: {{ $finalReport->repository_url }}
                                </a>
                            @endif
                            <div class="text-[11px] text-gray-400 font-mono">
                                Diunggah: {{ $finalReport->updated_at ? $finalReport->updated_at->format('d M Y, H:i') : '-' }}
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="{{ asset('storage/' . ($finalReport->file_path ?? $finalReport->final_report_path)) }}" target="_blank" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                                <span>📥</span>
                                <span>Buka / Unduh PDF</span>
                            </a>
                        </div>
                    </div>

                    <!-- Form Verifikasi Laporan Akhir -->
                    <form method="POST" action="{{ route('lecturer.final_report.updateStatus', $placement->id) }}" class="space-y-4 pt-2">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Keputusan DPL & Catatan Bimbingan Laporan
                            </label>
                            <textarea name="feedback" rows="3" placeholder="Tuliskan catatan perbaikan atau feedback untuk mahasiswa..." class="w-full text-xs sm:text-sm border-gray-200 rounded-2xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">{{ old('feedback', $finalReport->feedback) }}</textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <button type="submit" name="status" value="revision" class="px-5 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold rounded-xl transition active:scale-95 cursor-pointer">
                                ⚠️ Minta Revisi Laporan
                            </button>
                            <button type="submit" name="status" value="approved" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                                ✅ Setujui Laporan Akhir (ACC)
                            </button>
                        </div>
                    </form>
                @endif
            </div>

            <!-- 3. SECTION FORM PENILAIAN AKADEMIK DPL (BOBOT 60%) -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-xs space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold">
                        📝
                    </div>
                    <div>
                        <h3 class="font-black text-base text-gray-900">Form Evaluasi & Penilaian Akademik DPL (Bobot 60%)</h3>
                        <p class="text-xs text-gray-400">Masukkan nilai 3 aspek kompetensi akademik magang (skala 0 - 100)</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('lecturer.evaluations.store', $placement->id) }}" class="space-y-6">
                    @csrf

                    <!-- 3 Aspek Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        
                        <!-- 1. Penguasaan Materi / Teknis -->
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 space-y-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider">
                                    1. Penguasaan Materi & Teknis Magang <span class="text-rose-500">*</span>
                                </label>
                                <p class="text-[11px] text-gray-400 mt-0.5">Pemahaman teori & implementasi di dinas</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <input type="range" min="0" max="100" x-model="scoreMastery" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                                <input type="number" name="score_mastery" min="0" max="100" x-model="scoreMastery" required class="w-20 text-center font-black text-base border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono">
                            </div>
                        </div>

                        <!-- 2. Kualitas Laporan Akhir -->
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 space-y-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider">
                                    2. Kualitas & Sistematika Laporan <span class="text-rose-500">*</span>
                                </label>
                                <p class="text-[11px] text-gray-400 mt-0.5">Struktur penulisan ilmiah & analisis hasil</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <input type="range" min="0" max="100" x-model="scoreReport" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                                <input type="number" name="score_report" min="0" max="100" x-model="scoreReport" required class="w-20 text-center font-black text-base border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono">
                            </div>
                        </div>

                        <!-- 3. Sikap & Komunikasi Bimbingan -->
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 space-y-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider">
                                    3. Sikap, Komunikasi & Keaktifan <span class="text-rose-500">*</span>
                                </label>
                                <p class="text-[11px] text-gray-400 mt-0.5">Etika, konsultasi & responsivitas</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <input type="range" min="0" max="100" x-model="scoreAttitude" class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                                <input type="number" name="score_attitude" min="0" max="100" x-model="scoreAttitude" required class="w-20 text-center font-black text-base border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono">
                            </div>
                        </div>

                    </div>

                    <!-- Live Calculation Card -->
                    <div class="p-6 rounded-3xl bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
                        <div class="space-y-1 text-center md:text-left">
                            <span class="px-3 py-1 bg-white/10 rounded-full text-[11px] font-bold uppercase tracking-wider text-blue-200 border border-white/10">
                                📊 Kalkulasi Nilai Akhir Otomatis
                            </span>
                            <h4 class="text-lg font-black mt-2">
                                Nilai DPL (60%): <span class="text-emerald-400" x-text="calculatedDosen + '/100'"></span>
                                @if($nilaiDinas > 0)
                                    &bull; Dinas (40%): <span class="text-teal-300">{{ $nilaiDinas }}/100</span>
                                @endif
                            </h4>
                            <p class="text-xs text-slate-300">
                                Rumus: (0.40 &times; Nilai Dinas) + (0.60 &times; Nilai DPL)
                            </p>
                        </div>

                        <div class="text-center md:text-right bg-white/10 p-4 rounded-2xl border border-white/10 min-w-[200px]">
                            <span class="text-[11px] uppercase tracking-wider text-slate-300 font-bold block">Nilai Akhir & Mutu</span>
                            <div class="text-2xl font-black text-emerald-400 mt-1" x-text="calculatedFinal"></div>
                            <div class="text-xs font-bold text-blue-200 mt-0.5" x-text="letterGrade"></div>
                        </div>
                    </div>

                    <!-- Catatan Bimbingan Dosen -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Catatan / Rekomendasi DPL untuk Mahasiswa
                        </label>
                        <textarea name="feedback_dosen" rows="3" placeholder="Tuliskan evaluasi komprehensif atau saran pengembangan karir bagi mahasiswa..." class="w-full text-xs sm:text-sm border-gray-200 rounded-2xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">{{ old('feedback_dosen', $evaluation->feedback_dosen ?? $evaluation->catatan_dosen) }}</textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                        <a href="{{ route('lecturer.dashboard') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                            Kembali
                        </a>
                        <button type="submit" class="px-7 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                            Simpan Penilaian Akademik DPL
                        </button>
                    </div>

                </form>
            </div>

            <!-- 4. SECTION RIWAYAT LOGBOOK MAHASISWA -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-xs space-y-6">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold">
                            📖
                        </div>
                        <div>
                            <h3 class="font-black text-base text-gray-900">Riwayat Logbook Aktivitas Mahasiswa</h3>
                            <p class="text-xs text-gray-400">Pantau catatan logbook harian/mingguan dan status verifikasi mentor dinas</p>
                        </div>
                    </div>
                    <span class="text-xs text-blue-600 font-bold">{{ $logbooks->count() }} Entri Logbook</span>
                </div>

                <div class="space-y-4">
                    @forelse($logbooks as $lb)
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 space-y-3">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <span class="text-xs font-bold text-gray-900 font-mono">📅 {{ \Carbon\Carbon::parse($lb->date)->format('d F Y') }}</span>
                                    <span class="text-xs text-gray-400 ml-2">({{ \Carbon\Carbon::parse($lb->date)->diffForHumans() }})</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($lb->status === 'approved')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            Mentor: ACC
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            Mentor: Pending
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <p class="text-xs text-gray-700 whitespace-pre-line leading-relaxed">
                                {{ $lb->activity }}
                            </p>

                            @if($lb->feedback)
                                <div class="p-3 rounded-xl bg-emerald-50/70 border border-emerald-100 text-[11px] text-emerald-900">
                                    👔 <strong>Catatan Mentor Lapangan:</strong> "{{ $lb->feedback }}"
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="py-8 text-center text-gray-400 text-xs">
                            Mahasiswa belum mengisi entri logbook magang.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
