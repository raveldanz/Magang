<x-app-layout>
    <x-slot name="header">
        <x-page-header 
            title="Pusat Perbaikan & Revisi Sertifikat Magang" 
            description="Lakukan koreksi terpadu pada nilai evaluasi, biodata mahasiswa, dan periode magang sesuai catatan dari Atasan.">
            <x-slot name="actions">
                <a href="{{ route('admin.certificates.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-bold text-sm border border-slate-200 shadow-xs transition">
                    &larr; Kembali ke Daftar Sertifikat
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-50 border-2 border-emerald-300 text-emerald-900 flex items-center justify-between gap-3 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold shrink-0">
                            ✓
                        </div>
                        <div class="text-sm font-bold">{{ session('success') }}</div>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 rounded-2xl bg-rose-50 border-2 border-rose-300 text-rose-900 shadow-sm">
                    <div class="font-bold text-sm mb-1">Terdapat kesalahan dalam pengisian formulir perbaikan:</div>
                    <ul class="list-disc list-inside text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- BANNER HERO: CATATAN RESMI ATASAN (REVISI) --}}
            <div class="bg-gradient-to-r from-amber-50 to-orange-50 border-2 border-amber-300 rounded-3xl p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center font-black text-xl shrink-0 shadow-md shadow-amber-500/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black uppercase tracking-wider bg-amber-200 text-amber-900">
                                    Catatan Revisi dari Atasan Kepegawaian
                                </span>
                                @if($certificate->updated_at)
                                    <span class="text-xs text-amber-800 font-medium">
                                        Dikembalikan: {{ $certificate->updated_at->isoFormat('D MMMM Y, HH:mm') }} WIB
                                    </span>
                                @endif
                            </div>
                            <h3 class="text-base sm:text-lg font-black text-slate-900 mt-2 leading-snug">
                                {{ $certificate->notes ?: 'Terdapat aspek nilai atau data kelulusan yang perlu disesuaikan sebelum sertifikat dapat disahkan resmi.' }}
                            </h3>
                            <p class="text-xs text-slate-600 mt-1">
                                Perbaiki poin-poin di bawah ini. Anda dapat mengoreksi nilai evaluasi, biodata mahasiswa, periode magang, atau berkas pendukung, lalu kirim ulang ke Atasan.
                            </p>
                        </div>
                    </div>

                    <div class="shrink-0 flex items-center gap-2">
                        <a href="{{ route('admin.certificates.show', $placement->id) }}" target="_blank" 
                           class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-800 font-bold text-xs border border-amber-300 shadow-2xs transition">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Pratinjau Draft Sertifikat</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- FORM PERBAIKAN MULTI-ASPEK TERINTEGRASI --}}
            @php
                $initialDisiplin = old('nilai_disiplin', $evaluation->nilai_disiplin ?? 80);
                $initialKinerja = old('nilai_kinerja', $evaluation->nilai_kinerja ?? 85);
                $initialLaporan = old('nilai_laporan', $evaluation->nilai_laporan ?? 85);
                $initialAkademik = old('nilai_akademik', $evaluation->nilai_akademik ?? 0);
                
                $univ = $evaluation?->getUniversity();
                $scheme = $univ->evaluation_scheme ?? 'mentor_only';
                $weightMentor = $univ ? (int) $univ->weight_mentor : 100;
                $weightLecturer = $univ ? (int) $univ->weight_lecturer : 0;
            @endphp

            <form method="POST" action="{{ route('admin.certificates.updateRevision', $placement->id) }}" 
                  x-data="revisionCalculator({
                      disiplin: {{ $initialDisiplin }},
                      kinerja: {{ $initialKinerja }},
                      laporan: {{ $initialLaporan }},
                      akademik: {{ $initialAkademik }},
                      weightMentor: {{ $weightMentor }},
                      weightLecturer: {{ $weightLecturer }},
                      scheme: '{{ $scheme }}'
                  })"
                  class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                    {{-- ASPEK 1 (KIRI - 7 KOLOM): NILAI EVALUASI & KALKULATOR TRANSKRIP REAL-TIME --}}
                    <div class="lg:col-span-7 space-y-6">

                        {{-- Card 1: Nilai Evaluasi Lapangan (Dinas) --}}
                        <div class="bg-white rounded-3xl border-2 border-slate-200 p-6 shadow-xs space-y-5">
                            <div class="border-b border-slate-200 pb-3 flex items-center justify-between">
                                <div>
                                    <span class="text-[11px] font-black uppercase tracking-wider text-blue-600">Aspek 1</span>
                                    <h3 class="text-base sm:text-lg font-black text-slate-900 mt-0.5">
                                        Koreksi Nilai Evaluasi & Transkrip
                                    </h3>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    Skema: {{ $scheme === 'dual_evaluation' ? 'Penilaian Ganda' : '100% Dinas' }}
                                </span>
                            </div>

                            {{-- Live Metric Counter Box --}}
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-4">
                                <div>
                                    <div class="text-xs text-slate-500 font-bold uppercase">Simulasi Nilai Akhir</div>
                                    <div class="text-3xl font-black text-slate-900 mt-0.5" x-text="finalScore">
                                        {{ $evaluation->final_score ?? '-' }}
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        Rata-rata Pembimbing Dinas: <strong class="text-slate-700" x-text="dinasAvg"></strong>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-slate-500 font-bold uppercase">Mutu Grade</div>
                                    <div class="inline-block mt-1 px-4 py-1.5 rounded-xl font-black text-lg bg-emerald-100 text-emerald-800 border border-emerald-300"
                                         x-text="gradeLetter">
                                        {{ $evaluation->grade ?? '-' }}
                                    </div>
                                </div>
                            </div>

                            {{-- 3 Aspek Kriteria Penilaian Lapangan --}}
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    {{-- Disiplin / Presensi --}}
                                    <div class="p-4 rounded-2xl bg-white border-2 border-slate-200 focus-within:border-blue-600 transition">
                                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">
                                            1. Disiplin & Presensi
                                        </label>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Skor kehadiran & ketepatan waktu</p>
                                        <input type="number" step="0.5" min="0" max="100" name="nilai_disiplin" required
                                               x-model.number="disiplin" @input="calculate()"
                                               class="w-full mt-2 text-xl font-black text-slate-900 border-0 border-b-2 border-slate-200 focus:border-blue-600 focus:ring-0 p-1">
                                    </div>

                                    {{-- Kinerja / Penguasaan Tugas --}}
                                    <div class="p-4 rounded-2xl bg-white border-2 border-slate-200 focus-within:border-blue-600 transition">
                                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">
                                            2. Kinerja & Tugas
                                        </label>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Kualitas output & inisiatif kerja</p>
                                        <input type="number" step="0.5" min="0" max="100" name="nilai_kinerja" required
                                               x-model.number="kinerja" @input="calculate()"
                                               class="w-full mt-2 text-xl font-black text-slate-900 border-0 border-b-2 border-slate-200 focus:border-blue-600 focus:ring-0 p-1">
                                    </div>

                                    {{-- Laporan Akhir Magang --}}
                                    <div class="p-4 rounded-2xl bg-white border-2 border-slate-200 focus-within:border-blue-600 transition">
                                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">
                                            3. Laporan Magang
                                        </label>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Kelengkapan naskah laporan</p>
                                        <input type="number" step="0.5" min="0" max="100" name="nilai_laporan" required
                                               x-model.number="laporan" @input="calculate()"
                                               class="w-full mt-2 text-xl font-black text-slate-900 border-0 border-b-2 border-slate-200 focus:border-blue-600 focus:ring-0 p-1">
                                    </div>
                                </div>

                                {{-- Nilai Akademik Dosen (Jika Dual Evaluation) --}}
                                @if($weightLecturer > 0 || $scheme === 'dual_evaluation')
                                    <div class="p-4 rounded-2xl bg-indigo-50/60 border-2 border-indigo-200">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <label class="block text-xs font-black text-indigo-900 uppercase tracking-wider">
                                                    Nilai Dosen Pembimbing Lapangan (DPL)
                                                </label>
                                                <p class="text-[11px] text-indigo-700 mt-0.5">Bobot Kampus: {{ $weightLecturer }}% (Bobot Dinas: {{ $weightMentor }}%)</p>
                                            </div>
                                            <input type="number" step="0.5" min="0" max="100" name="nilai_akademik"
                                                   x-model.number="akademik" @input="calculate()"
                                                   class="w-28 text-center text-lg font-black text-slate-900 rounded-xl border-2 border-indigo-300 focus:border-indigo-600 focus:ring-0 py-1.5">
                                        </div>
                                    </div>
                                @endif

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">
                                        Catatan Evaluasi Lapangan (Opsional):
                                    </label>
                                    <textarea name="catatan_evaluasi" rows="2" 
                                              placeholder="Tuliskan catatan apresiasi atau evaluasi kinerja magang mahasiswa..."
                                              class="w-full text-xs p-3 rounded-xl border-2 border-slate-200 focus:border-blue-600 focus:ring-0">{{ old('catatan_evaluasi', $evaluation->catatan ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Card 2: Monitoring Logbook Presensi & Laporan Akhir --}}
                        <div class="bg-white rounded-3xl border-2 border-slate-200 p-6 shadow-xs space-y-4">
                            <div class="border-b border-slate-200 pb-3 flex items-center justify-between">
                                <div>
                                    <span class="text-[11px] font-black uppercase tracking-wider text-emerald-600">Aspek 3</span>
                                    <h3 class="text-base sm:text-lg font-black text-slate-900 mt-0.5">
                                        Validasi Logbook & Naskah Laporan
                                    </h3>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $verifiedLogbookCount > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $verifiedLogbookCount }} / {{ $logbookCount }} Entri Terverifikasi
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                                    <div class="font-bold text-slate-800 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>Presensi & Logbook Aktivitas</span>
                                    </div>
                                    <p class="text-slate-600">Total entri logbook yang telah diunggah: <strong>{{ $logbookCount }} hari kegiatan</strong>.</p>
                                    <a href="{{ route('admin.logbooks.index', ['search' => $student->name]) }}" target="_blank"
                                       class="inline-flex items-center gap-1 font-bold text-blue-700 hover:text-blue-800 mt-1">
                                        Lihat Lembar Logbook & Presensi &rarr;
                                    </a>
                                </div>

                                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                                    <div class="font-bold text-slate-800 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
                                        <span>Naskah Laporan Akhir Magang</span>
                                    </div>
                                    @php
                                        $finalReport = $placement->finalreport;
                                    @endphp
                                    @if($finalReport && ($finalReport->file_path || $finalReport->file_laporan))
                                        <p class="text-slate-600">Status Laporan: <strong class="text-emerald-700">Telah Diunggah & Disetujui</strong></p>
                                        <a href="{{ asset('storage/' . ($finalReport->file_path ?: $finalReport->file_laporan)) }}" target="_blank"
                                           class="inline-flex items-center gap-1 font-bold text-blue-700 hover:text-blue-800 mt-1">
                                            Unduh Naskah Laporan (PDF) &rarr;
                                        </a>
                                    @else
                                        <p class="text-amber-700 font-medium">Belum ada naskah fisik yang diunggah.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- ASPEK 2 (KANAN - 5 KOLOM): BIODATA MAHASISWA & PERIODE MAGANG --}}
                    <div class="lg:col-span-5 space-y-6">

                        {{-- Card 3: Biodata Mahasiswa & Kampus --}}
                        <div class="bg-white rounded-3xl border-2 border-slate-200 p-6 shadow-xs space-y-4">
                            <div class="border-b border-slate-200 pb-3">
                                <span class="text-[11px] font-black uppercase tracking-wider text-purple-600">Aspek 2</span>
                                <h3 class="text-base sm:text-lg font-black text-slate-900 mt-0.5">
                                    Data Mahasiswa & Magang
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Data ini tercetak langsung pada lembar depan Sertifikat resmi.
                                </p>
                            </div>

                            <div class="space-y-3.5">
                                {{-- Nama Mahasiswa --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">
                                        Nama Lengkap Mahasiswa:
                                    </label>
                                    <input type="text" name="student_name" required
                                           value="{{ old('student_name', $student->name) }}"
                                           class="w-full text-xs font-bold text-slate-900 rounded-xl border-2 border-slate-200 focus:border-blue-600 focus:ring-0 p-2.5">
                                </div>

                                {{-- NIM --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">
                                        Nomor Induk Mahasiswa (NIM):
                                    </label>
                                    <input type="text" name="nim" required
                                           value="{{ old('nim', $profile->nim ?? '') }}"
                                           class="w-full text-xs font-bold font-mono text-slate-900 rounded-xl border-2 border-slate-200 focus:border-blue-600 focus:ring-0 p-2.5">
                                </div>

                                {{-- Universitas --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">
                                        Asal Perguruan Tinggi:
                                    </label>
                                    <input type="text" name="universitas" required
                                           value="{{ old('universitas', $profile->universitas ?? $student->university ?? '') }}"
                                           class="w-full text-xs font-semibold text-slate-900 rounded-xl border-2 border-slate-200 focus:border-blue-600 focus:ring-0 p-2.5">
                                </div>

                                {{-- Program Studi / Jurusan --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">
                                        Program Studi / Jurusan:
                                    </label>
                                    <input type="text" name="jurusan" required
                                           value="{{ old('jurusan', $profile->jurusan ?? '') }}"
                                           class="w-full text-xs font-semibold text-slate-900 rounded-xl border-2 border-slate-200 focus:border-blue-600 focus:ring-0 p-2.5">
                                </div>

                                {{-- Tanggal Magang --}}
                                <div class="grid grid-cols-2 gap-3 pt-1">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">
                                            Tanggal Mulai:
                                        </label>
                                        <input type="date" name="start_date" required
                                               value="{{ old('start_date', $application->start_date ? \Carbon\Carbon::parse($application->start_date)->format('Y-m-d') : '') }}"
                                               class="w-full text-xs font-semibold text-slate-900 rounded-xl border-2 border-slate-200 focus:border-blue-600 focus:ring-0 p-2.5">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">
                                            Tanggal Selesai:
                                        </label>
                                        <input type="date" name="end_date" required
                                               value="{{ old('end_date', $application->end_date ? \Carbon\Carbon::parse($application->end_date)->format('Y-m-d') : '') }}"
                                               class="w-full text-xs font-semibold text-slate-900 rounded-xl border-2 border-slate-200 focus:border-blue-600 focus:ring-0 p-2.5">
                                    </div>
                                </div>

                                {{-- Bidang Penempatan --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">
                                        Bidang Penempatan Dispusip:
                                    </label>
                                    <select name="unit_id" class="w-full text-xs font-semibold text-slate-900 rounded-xl border-2 border-slate-200 focus:border-blue-600 focus:ring-0 p-2.5">
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}" {{ $application->unit_id == $unit->id ? 'selected' : '' }}>
                                                {{ $unit->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Mentor Lapangan --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">
                                        Mentor Pembimbing Lapangan:
                                    </label>
                                    <select name="mentor_id" class="w-full text-xs font-semibold text-slate-900 rounded-xl border-2 border-slate-200 focus:border-blue-600 focus:ring-0 p-2.5">
                                        @php
                                            $currMentorId = $placement->mentor_id ?? $placement->pembimbing_id;
                                        @endphp
                                        @foreach($mentors as $mentor)
                                            <option value="{{ $mentor->id }}" {{ $currMentorId == $mentor->id ? 'selected' : '' }}>
                                                {{ $mentor->name }} ({{ $mentor->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Card 4: Konfirmasi Penjelasan Perbaikan untuk Atasan --}}
                        <div class="bg-white rounded-3xl border-2 border-slate-200 p-6 shadow-xs space-y-4">
                            <div class="border-b border-slate-200 pb-3">
                                <h4 class="text-sm font-black text-slate-900">
                                    Catatan Penjelasan Perbaikan untuk Atasan
                                </h4>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Tuliskan penjelasan singkat mengenai bagian yang telah diperbaiki agar Atasan dapat langsung memvalidasi.
                                </p>
                            </div>

                            <textarea name="resolution_note" rows="3" required
                                      placeholder="Contoh: Skor kehadiran presensi telah disinkronisasi sesuai lembar absensi (dari 80 menjadi 85). Data mahasiswa telah terverifikasi."
                                      class="w-full text-xs p-3 rounded-xl border-2 border-slate-200 focus:border-blue-600 focus:ring-0">{{ old('resolution_note', 'Perbaikan nilai dan data kelulusan telah disesuaikan sesuai arahan catatan atasan.') }}</textarea>

                            {{-- Tombol Aksi --}}
                            <div class="space-y-2 pt-2">
                                <button type="submit" name="action" value="resubmit"
                                        class="w-full py-3.5 px-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white font-black text-sm shadow-md shadow-emerald-600/30 transition flex items-center justify-center gap-2 cursor-pointer">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                    <span>Simpan & Ajukan Ulang ke Atasan</span>
                                </button>

                                <button type="submit" name="action" value="save_draft"
                                        class="w-full py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition flex items-center justify-center gap-2 cursor-pointer">
                                    <span>Simpan Draf (Belum Dikirim ke Atasan)</span>
                                </button>
                            </div>
                        </div>

                    </div>

                </div>
            </form>

        </div>
    </div>

    {{-- Script Alpine untuk Live Kalkulasi Nilai & Mutu Grade --}}
    @push('scripts')
    <script>
        function revisionCalculator(config) {
            return {
                disiplin: config.disiplin || 0,
                kinerja: config.kinerja || 0,
                laporan: config.laporan || 0,
                akademik: config.akademik || 0,
                weightMentor: config.weightMentor || 100,
                weightLecturer: config.weightLecturer || 0,
                scheme: config.scheme || 'mentor_only',
                dinasAvg: 0,
                finalScore: 0,
                gradeLetter: '-',

                init() {
                    this.calculate();
                },

                calculate() {
                    let d = parseFloat(this.disiplin) || 0;
                    let k = parseFloat(this.kinerja) || 0;
                    let l = parseFloat(this.laporan) || 0;
                    let a = parseFloat(this.akademik) || 0;

                    let avgDinas = (d + k + l) / 3;
                    this.dinasAvg = avgDinas.toFixed(2);

                    let score = 0;
                    if (this.scheme === 'mentor_only' || this.weightLecturer === 0) {
                        score = avgDinas;
                    } else if (this.weightMentor === 0) {
                        score = a > 0 ? a : avgDinas;
                    } else {
                        score = (avgDinas * (this.weightMentor / 100)) + (a * (this.weightLecturer / 100));
                    }

                    this.finalScore = score.toFixed(2);

                    // Grade Letter
                    if (score >= 85) this.gradeLetter = 'A (Sangat Baik)';
                    else if (score >= 75) this.gradeLetter = 'AB (Baik Sekali)';
                    else if (score >= 65) this.gradeLetter = 'B (Baik)';
                    else if (score >= 55) this.gradeLetter = 'BC (Cukup Baik)';
                    else if (score >= 40) this.gradeLetter = 'C (Cukup)';
                    else this.gradeLetter = 'E (Tidak Lulus)';
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
