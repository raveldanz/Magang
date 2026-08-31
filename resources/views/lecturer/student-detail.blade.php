<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('lecturer.dashboard') }}" class="p-2.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 rounded-xl transition shadow-xs flex items-center justify-center" title="Kembali ke Dashboard">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                    <span>Monitoring & Penilaian: {{ $student->name }}</span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                    NIM: {{ $profile->nim ?? '-' }} &bull; {{ $profile->universitas ?? '-' }} &bull; Penempatan: {{ $agencyProfile->agency_name ?? '-' }}
                </p>
            </div>
        </div>
    </x-slot>

    @php
        $nilaiDinas = $evaluation?->nilai_pembimbing ?? 0;
        $evalMastery = old('score_mastery', $evaluation?->score_mastery ?? ($evaluation?->nilai_akademik ?? 85));
        $evalReport = old('score_report', $evaluation?->score_report ?? ($evaluation?->nilai_akademik ?? 85));
        $evalAttitude = old('score_attitude', $evaluation?->score_attitude ?? ($evaluation?->nilai_akademik ?? 85));

        $univ = $evaluation?->getUniversity();
        if (!$univ && isset($student)) {
            if ($student->university_id) {
                $univ = \App\Models\University::find($student->university_id);
            } else {
                $name = $student->university ?? ($profile->universitas ?? null);
                if ($name) {
                    $univ = \App\Models\University::where('name', 'like', "%{$name}%")->orWhere('code', 'like', "%{$name}%")->first();
                }
            }
        }
        $scheme = $univ->evaluation_scheme ?? 'dual_evaluation';
        $weightMentor = $univ ? (int)$univ->weight_mentor : 40;
        $weightLecturer = $univ ? (int)$univ->weight_lecturer : 60;
        $isMentorOnly = $scheme === 'mentor_only';
    @endphp

    <div class="py-8" x-data="{
        scoreMastery: {{ $evalMastery }},
        scoreReport: {{ $evalReport }},
        scoreAttitude: {{ $evalAttitude }},
        scoreDinas: {{ $nilaiDinas }},
        scheme: '{{ $scheme }}',
        weightMentor: {{ $weightMentor }},
        weightLecturer: {{ $weightLecturer }},
        get calculatedDosen() {
            let m = Number(this.scoreMastery) || 0;
            let r = Number(this.scoreReport) || 0;
            let a = Number(this.scoreAttitude) || 0;
            return Math.round(((m + r + a) / 3) * 100) / 100;
        },
        get calculatedFinal() {
            let d = Number(this.scoreDinas) || 0;
            let l = this.calculatedDosen;
            if (this.scheme === 'mentor_only') {
                return d > 0 ? d : l;
            }
            if (d > 0 && l > 0) {
                return Math.round((((this.weightMentor / 100) * d) + ((this.weightLecturer / 100) * l)) * 100) / 100;
            }
            return d > 0 ? d : l;
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
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
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

            <!-- Banner Info Aturan Penilaian 100% Mentor Dinas -->
            @if($isMentorOnly)
                <div class="p-5 sm:p-6 rounded-3xl bg-blue-50/90 border border-blue-200 shadow-xs flex items-start gap-4">
                    <div class="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-sm font-bold text-blue-950">Kebijakan Evaluasi Kampus: 100% Pembimbing Lapangan Dinas</h3>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-200/80 text-blue-900 border border-blue-300">Aturan Universitas</span>
                        </div>
                        <p class="text-xs text-blue-900 leading-relaxed">
                            Perguruan tinggi <strong>{{ $profile->universitas ?? $univ?->name ?? 'Mahasiswa' }}</strong> memberlakukan peraturan penilaian magang <strong>100% penuh dari Pembimbing Lapangan Dinas</strong>. Dosen Pembimbing Lapangan (DPL) berfokus mendampingi bimbingan akademik, memonitor logbook aktivitas harian, dan memverifikasi laporan akhir. <strong>Dosen tidak diwajibkan menginput formulir nilai angka</strong>.
                        </p>
                    </div>
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
                             {{ $profile->universitas ?? '-' }}
                        </span>
                    </div>
                </div>

                <!-- Lokasi Magang & Pembimbing Dinas -->
                <div class="md:col-span-1 border-b md:border-b-0 md:border-r border-gray-100 pb-4 md:pb-0 md:pr-4">
                    <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Penempatan Instansi</span>
                    <h4 class="font-bold text-base text-gray-900 mt-1"> {{ $agencyProfile->agency_name ?? '-' }}</h4>
                    <p class="text-xs text-gray-500 mt-0.5">Divisi: <span class="font-semibold text-gray-700">{{ $unit->name ?? '-' }}</span></p>
                    <div class="mt-2 text-xs text-gray-600">
                         Mentor: <strong>{{ $mentor->name ?? 'Belum Ditugaskan' }}</strong>
                    </div>
                </div>

                <!-- Status Evaluasi & Nilai Gabungan -->
                <div class="md:col-span-1">
                    <span class="text-xs font-bold text-amber-600 uppercase tracking-wider">Status Kelulusan</span>
                    <div class="mt-2 space-y-1.5">
                        @if($isMentorOnly)
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-500">Nilai Mentor Dinas:</span>
                                <span class="font-bold {{ $nilaiDinas > 0 ? 'text-emerald-700' : 'text-amber-600' }}">
                                    {{ $nilaiDinas > 0 ? $nilaiDinas . '/100' : 'Belum Dinilai Dinas' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-500">Skema Penilaian:</span>
                                <span class="font-bold text-blue-700">100% Mentor Dinas</span>
                            </div>
                            <div class="flex items-center justify-between text-xs pt-2 border-t border-gray-100">
                                <span class="font-bold text-gray-900">Nilai Akhir Magang:</span>
                                <span class="font-black text-emerald-700 text-sm" x-text="calculatedFinal + ' (' + letterGrade.split(' ')[0] + ')'"></span>
                            </div>
                        @else
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-500">Skor Dinas:</span>
                                <span class="font-bold text-gray-800">{{ $nilaiDinas > 0 ? $nilaiDinas . '/100' : 'Belum Ada' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-500">Skor DPL:</span>
                                <span class="font-bold text-blue-700" x-text="calculatedDosen + '/100'"></span>
                            </div>
                            <div class="flex items-center justify-between text-xs pt-2 border-t border-gray-100">
                                <span class="font-bold text-gray-900">Nilai Akhir:</span>
                                <span class="font-black text-emerald-700 text-sm" x-text="calculatedFinal + ' (' + letterGrade.split(' ')[0] + ')'"></span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 2. SECTION LAPORAN AKHIR MAGANG (APPROVAL / REVISION) -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-xs space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-black text-base text-gray-900">Verifikasi Dokumen Laporan Akhir Magang</h3>
                            <p class="text-xs text-gray-400">Tinjau naskah laporan ilmiah akhir mahasiswa dan berikan persetujuan atau catatan revisi</p>
                        </div>
                    </div>

                    <div>
                        @if(!$finalReport)
                            <span class="px-4 py-2 rounded-2xl text-xs font-black bg-gray-100 text-gray-500">Belum Mengunggah Laporan</span>
                        @elseif($finalReport->status === 'approved')
                            <span class="px-4 py-2 rounded-2xl text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300">Laporan Disetujui (ACC)</span>
                        @elseif($finalReport->status === 'revision')
                            <span class="px-4 py-2 rounded-2xl text-xs font-black bg-rose-100 text-rose-800 border border-rose-300">Perlu Perbaikan (Revisi)</span>
                        @else
                            <span class="px-4 py-2 rounded-2xl text-xs font-black bg-amber-100 text-amber-800 border border-amber-300">Menunggu Review DPL</span>
                        @endif
                    </div>
                </div>

                @if($finalReport)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="space-y-1">
                            <h4 class="font-bold text-xs sm:text-sm text-gray-900">{{ $finalReport->title ?? 'Laporan Akhir Praktik Kerja Lapangan (PKL) / Magang MBKM' }}</h4>
                            <p class="text-[11px] text-gray-400">Diunggah: {{ $finalReport->updated_at ? $finalReport->updated_at->format('d M Y, H:i') : '-' }}</p>
                            @if($finalReport->repository_url)
                                <a href="{{ $finalReport->repository_url }}" target="_blank" class="text-xs text-blue-600 hover:text-blue-800 font-semibold inline-flex items-center gap-1">
                                    <span>🔗 Tautan Repository / Luaran: {{ $finalReport->repository_url }}</span>
                                </a>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ asset('storage/' . ($finalReport->file_path ?? $finalReport->final_report_path)) }}" target="_blank" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                                Buka / Unduh PDF
                            </a>
                        </div>
                    </div>

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
                                 Minta Revisi Laporan
                            </button>
                            <button type="submit" name="status" value="approved" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                                 Setujui Laporan Akhir (ACC)
                            </button>
                        </div>
                    </form>
                @endif
            </div>

            <!-- 3. SECTION EVALUASI & PENILAIAN -->
            @if($isMentorOnly)
                <!-- HASIL REKAPITULASI EVALUASI PEMBIMBING DINAS (100%) -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-xs space-y-6">
                    <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-black text-base text-gray-900">Hasil Rekapitulasi Penilaian Mentor Dinas (100%)</h3>
                            <p class="text-xs text-gray-400">Sesuai kebijakan kampus, nilai akhir mahasiswa diperoleh 100% dari evaluasi pembimbing dinas</p>
                        </div>
                    </div>

                    @if($nilaiDinas > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-center">
                                <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider block">Kedisiplinan & Sikap</span>
                                <p class="text-2xl font-black text-gray-900 mt-1">{{ $evaluation->nilai_disiplin ?? '-' }}/100</p>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-center">
                                <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider block">Kinerja & Tugas</span>
                                <p class="text-2xl font-black text-gray-900 mt-1">{{ $evaluation->nilai_kinerja ?? '-' }}/100</p>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-center">
                                <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider block">Laporan & Output</span>
                                <p class="text-2xl font-black text-gray-900 mt-1">{{ $evaluation->nilai_laporan ?? '-' }}/100</p>
                            </div>
                        </div>

                        <div class="p-6 rounded-3xl bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
                            <div class="space-y-1 text-center md:text-left">
                                <span class="px-3 py-1 bg-white/10 rounded-full text-[11px] font-bold uppercase tracking-wider text-blue-200 border border-white/10">
                                    Penilaian Resmi Terverifikasi
                                </span>
                                <h4 class="text-lg font-black mt-2">
                                    Nilai Akhir Magang: <span class="text-emerald-400">{{ $nilaiDinas }}/100</span>
                                </h4>
                                <p class="text-xs text-slate-300">
                                    Dinilai oleh Mentor: <strong>{{ $mentor->name ?? 'Pembimbing Dinas' }}</strong> ({{ $agencyProfile->agency_name ?? 'Dinas' }})
                                </p>
                            </div>

                            <div class="text-center md:text-right bg-white/10 p-4 rounded-2xl border border-white/10 min-w-[200px]">
                                <span class="text-[11px] uppercase tracking-wider text-slate-300 font-bold block">Indeks Mutu</span>
                                <div class="text-2xl font-black text-emerald-400 mt-1" x-text="letterGrade.split(' ')[0]"></div>
                                <div class="text-xs font-bold text-blue-200 mt-0.5" x-text="letterGrade"></div>
                            </div>
                        </div>

                        @if($evaluation->catatan_pembimbing ?? $evaluation->feedback_pembimbing)
                            <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200 text-xs text-emerald-950">
                                <span class="font-bold uppercase tracking-wider block text-[11px]">Catatan / Evaluasi dari Mentor Lapangan:</span>
                                <p class="mt-1 italic">"{{ $evaluation->catatan_pembimbing ?? $evaluation->feedback_pembimbing }}"</p>
                            </div>
                        @endif
                    @else
                        <div class="p-6 rounded-2xl bg-amber-50 border border-amber-200 text-center space-y-2">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-amber-100 text-amber-700 text-lg font-bold">⏳</span>
                            <h4 class="text-sm font-bold text-amber-900">Menunggu Penilaian dari Pembimbing Lapangan Dinas</h4>
                            <p class="text-xs text-amber-800 max-w-lg mx-auto">
                                Pembimbing lapangan instansi ({{ $mentor->name ?? 'Mentor Dinas' }}) saat ini belum menginput nilai evaluasi magang. Begitu dinilai, nilai akhir akan langsung tampil di sini secara otomatis.
                            </p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('lecturer.evaluations.store', $placement->id) }}" class="space-y-4 pt-4 border-t border-gray-100">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Catatan / Rekomendasi Bimbingan DPL untuk Mahasiswa (Opsional)
                            </label>
                            <textarea name="feedback_dosen" rows="3" placeholder="Tuliskan saran pengembangan diri, apresiasi, atau rekomendasi karir untuk mahasiswa bimbingan Anda..." class="w-full text-xs sm:text-sm border-gray-200 rounded-2xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">{{ old('feedback_dosen', $evaluation?->feedback_dosen ?? ($evaluation?->catatan_dosen ?? '')) }}</textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('lecturer.dashboard') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                                Kembali ke Dashboard
                            </a>
                            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                                Simpan Catatan DPL
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <!-- FORM PENILAIAN AKADEMIK DPL (DUAL EVALUATION) -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-xs space-y-6">
                    <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-black text-base text-gray-900">Form Evaluasi & Penilaian Akademik DPL</h3>
                            <p class="text-xs text-gray-400">Masukkan nilai 3 aspek kompetensi akademik magang (skala 0 - 100)</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('lecturer.evaluations.store', $placement->id) }}" class="space-y-6">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                        <div class="p-6 rounded-3xl bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
                            <div class="space-y-1 text-center md:text-left">
                                <span class="px-3 py-1 bg-white/10 rounded-full text-[11px] font-bold uppercase tracking-wider text-blue-200 border border-white/10">
                                     Kalkulasi Nilai Akhir Otomatis
                                </span>
                                <h4 class="text-lg font-black mt-2">
                                    Nilai DPL: <span class="text-emerald-400" x-text="calculatedDosen + '/100'"></span>
                                    @if($nilaiDinas > 0)
                                        &bull; Dinas: <span class="text-teal-300">{{ $nilaiDinas }}/100</span>
                                    @endif
                                </h4>
                                <p class="text-xs text-slate-300">
                                    Rumus Aturan Kampus: ({{ $weightMentor }}% &times; Nilai Dinas) + ({{ $weightLecturer }}% &times; Nilai DPL)
                                </p>
                            </div>
                            <div class="text-center md:text-right bg-white/10 p-4 rounded-2xl border border-white/10 min-w-[200px]">
                                <span class="text-[11px] uppercase tracking-wider text-slate-300 font-bold block">Nilai Akhir & Mutu</span>
                                <div class="text-2xl font-black text-emerald-400 mt-1" x-text="calculatedFinal"></div>
                                <div class="text-xs font-bold text-blue-200 mt-0.5" x-text="letterGrade"></div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Catatan / Rekomendasi DPL untuk Mahasiswa
                            </label>
                            <textarea name="feedback_dosen" rows="3" placeholder="Tuliskan evaluasi komprehensif atau saran pengembangan karir bagi mahasiswa..." class="w-full text-xs sm:text-sm border-gray-200 rounded-2xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">{{ old('feedback_dosen', $evaluation?->feedback_dosen ?? ($evaluation?->catatan_dosen ?? '')) }}</textarea>
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
            @endif

            <!-- 4. SECTION RIWAYAT LOGBOOK MAHASISWA -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-xs space-y-6">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold">
                            
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
                                    <span class="text-xs font-bold text-gray-900 font-mono"> {{ \Carbon\Carbon::parse($lb->date)->format('d F Y') }}</span>
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
                                     <strong>Catatan Mentor Lapangan:</strong> "{{ $lb->feedback }}"
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
