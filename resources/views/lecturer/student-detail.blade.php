<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('lecturer.dashboard') }}" 
                   class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition shadow-2xs" 
                   title="Kembali ke Dashboard">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl sm:text-2xl text-slate-900 leading-tight">
                        {{ $student->name }}
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        NIM: {{ $profile->nim ?? '-' }} &bull; {{ $profile->universitas ?? '-' }} ({{ $profile->jurusan ?? '-' }})
                    </p>
                </div>
            </div>

            <!-- Tombol Aksi & Chat -->
            <div class="w-full sm:w-auto grid grid-cols-2 sm:flex sm:items-center gap-2">
                <x-chat-button :user="$student" label="Chat Mahasiswa" />
                @if ($placement->mentor ?? $placement->pembimbing)
                    <x-chat-button :user="$placement->mentor ?? $placement->pembimbing" label="Chat Mentor" />
                @endif
                <x-chat-group-button :placement="$placement" />
                <a href="{{ route('lecturer.students.grade_sheet', $placement->id) }}" target="_blank" class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-xs cursor-pointer text-center" title="Buka Dokumen Resmi Berita Acara & Nilai A4">
                    <span>Cetak Berita Acara (BAP)</span>
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $mentor = $placement->mentor ?? $placement->pembimbing;
        $academicAdvisor = $placement->academicAdvisor;
        $agency = $unit?->agencyProfile ?? $placement->agencyProfile;
        $eval = $evaluation;
        $hasEval = $eval && (($eval->score_mastery ?? 0) > 0 || ($eval->nilai_akademik ?? 0) > 0 || ($eval->nilai_dosen ?? 0) > 0);
        $univ = $eval?->getUniversity();
        if (!$univ && $student) {
            $univ = app(\App\Services\UniversityResolver::class)->forUser($student);
        }
        $scheme = $univ->evaluation_scheme ?? 'dual_evaluation';
        $isMentorOnly = ($scheme === 'mentor_only');
    @endphp

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Message -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-lg flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-lg text-rose-900 text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <!-- 2. Card: Data Magang -->
            <div class="bg-white rounded-xl p-6 border border-slate-200 space-y-4">
                <h3 class="font-bold text-base text-slate-900 pb-3 border-b border-slate-200">
                    Data Magang
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-xs text-slate-500 block">Perguruan Tinggi</span>
                        <span class="font-medium text-slate-800">{{ $profile->universitas ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Program Studi / Jurusan</span>
                        <span class="font-medium text-slate-800 text-sm block mt-0.5">{{ $profile->jurusan ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Instansi & Unit Kerja</span>
                        <span class="font-medium text-slate-800 text-sm block mt-0.5">{{ $agency->agency_name ?? '-' }} &mdash; {{ $unit->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Mentor Lapangan</span>
                        <span class="font-medium text-slate-800 text-sm block mt-0.5">{{ $mentor->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Dosen Pembimbing</span>
                        <span class="font-medium text-slate-800 text-sm block mt-0.5">{{ $academicAdvisor->name ?? 'Belum Ditugaskan' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Periode Pelaksanaan</span>
                        <span class="font-medium text-slate-800 text-sm block mt-0.5">
                            {{ $placement->application?->start_date ? \Carbon\Carbon::parse($placement->application->start_date)->translatedFormat('d M Y') : '-' }} s/d {{ $placement->application?->end_date ? \Carbon\Carbon::parse($placement->application->end_date)->translatedFormat('d M Y') : '-' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- 3. Card: Penilaian -->
            <div class="bg-white rounded-2xl p-6 sm:p-7 border border-slate-200 shadow-xs space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="font-bold text-base text-slate-900">
                            Penilaian
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Ringkasan nilai evaluasi akhir magang mahasiswa
                        </p>
                    </div>
                    <div class="text-right">
                        @if (! empty($evaluationLockReason))
                            <a href="{{ route('lecturer.evaluations.create', $placement->id) }}"
                               class="inline-flex items-center px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 text-sm font-bold rounded-xl border border-slate-300 transition">
                                Lihat Nilai
                            </a>
                            <p class="text-xs text-slate-500 mt-1 max-w-xs">{{ $evaluationLockReason }}</p>
                        @else
                            <a href="{{ route('lecturer.evaluations.create', $placement->id) }}"
                               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition">
                                {{ $hasEval ? 'Ubah Nilai' : 'Isi Nilai' }}
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Grid Komparasi Nilai -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Nilai Dosen Pembimbing -->
                    <div class="p-4 sm:p-5 bg-slate-50/80 rounded-2xl border border-slate-200 space-y-3 shadow-2xs">
                        <span class="text-xs font-bold text-slate-600 uppercase tracking-wider block">Nilai Dosen Pembimbing</span>
                        @if ($isMentorOnly)
                            <p class="text-xs text-slate-500 italic">
                                Kebijakan kampus memberlakukan 100% penilaian dari Mentor Lapangan.
                            </p>
                        @else
                            <div class="grid grid-cols-3 gap-2 text-center">
                                <div class="p-2.5 bg-white rounded-xl border border-slate-200 shadow-2xs">
                                    <span class="text-xs text-slate-400 font-semibold block">Materi</span>
                                    <span class="text-base font-bold text-slate-900">{{ $eval?->score_mastery ?? '-' }}</span>
                                </div>
                                <div class="p-2.5 bg-white rounded-xl border border-slate-200 shadow-2xs">
                                    <span class="text-xs text-slate-400 font-semibold block">Laporan</span>
                                    <span class="text-base font-bold text-slate-900">{{ $eval?->score_report ?? '-' }}</span>
                                </div>
                                <div class="p-2.5 bg-white rounded-xl border border-slate-200 shadow-2xs">
                                    <span class="text-xs text-slate-400 font-semibold block">Sikap</span>
                                    <span class="text-base font-bold text-slate-900">{{ $eval?->score_attitude ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="text-xs text-slate-600 pt-1">
                                Rata-rata Dosen: <strong class="text-slate-900">{{ $eval?->nilai_dosen_calculated > 0 ? $eval->nilai_dosen_calculated : '-' }}</strong>
                            </div>
                        @endif
                    </div>

                    <!-- Nilai Mentor Lapangan -->
                    <div class="p-4 sm:p-5 bg-slate-50/80 rounded-2xl border border-slate-200 space-y-3 shadow-2xs">
                        <span class="text-xs font-bold text-slate-600 uppercase tracking-wider block">Nilai Mentor Lapangan</span>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="p-2.5 bg-white rounded-xl border border-slate-200 shadow-2xs">
                                <span class="text-xs text-slate-400 font-semibold block">Disiplin</span>
                                <span class="text-base font-bold text-slate-900">{{ $eval?->nilai_disiplin ?? '-' }}</span>
                            </div>
                            <div class="p-2.5 bg-white rounded-xl border border-slate-200 shadow-2xs">
                                <span class="text-xs text-slate-400 font-semibold block">Kinerja</span>
                                <span class="text-base font-bold text-slate-900">{{ $eval?->nilai_kinerja ?? '-' }}</span>
                            </div>
                            <div class="p-2.5 bg-white rounded-xl border border-slate-200 shadow-2xs">
                                <span class="text-xs text-slate-400 font-semibold block">Laporan</span>
                                <span class="text-base font-bold text-slate-900">{{ $eval?->nilai_laporan ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="text-xs text-slate-600 pt-1">
                            Rata-rata Mentor: <strong class="text-slate-900">{{ $eval?->nilai_pembimbing > 0 ? $eval->nilai_pembimbing : '-' }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Nilai Akhir & Predikat -->
                <div class="p-4 sm:p-5 bg-white rounded-2xl border border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-4 shadow-2xs">
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Nilai Akhir Magang</span>
                        <div class="text-2xl font-black text-slate-900 mt-0.5">
                            {{ $eval?->final_score > 0 ? $eval->final_score : ($eval?->nilai_akhir > 0 ? $eval->nilai_akhir : '-') }}
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Predikat Mutu</span>
                        <div class="text-2xl font-black text-blue-700 mt-0.5">
                            {{ $eval?->grade_calculated ?? '-' }}
                        </div>
                    </div>
                </div>

                @if ($eval?->feedback_dosen ?? $eval?->catatan_dosen)
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-700">
                        <strong>Catatan Dosen:</strong> "{{ $eval->feedback_dosen ?? $eval->catatan_dosen }}"
                    </div>
                @endif
            </div>

            <!-- 4. Card: Laporan Akhir (Komponen Bersama) -->
            <x-supervision.report-review
                :finalReport="$finalReport"
                :submitRoute="route('lecturer.final_report.updateStatus', $placement->id)"
                method="POST"
                role="lecturer"
            />

            <!-- 5. Card: Logbook (Komponen Bersama) -->
            <div class="bg-white rounded-xl p-6 border border-slate-200 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div>
                        <h3 class="font-bold text-base text-slate-900">
                            Logbook
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Daftar riwayat aktivitas harian per paket mingguan mahasiswa bimbingan Anda
                        </p>
                    </div>
                    <span class="px-3 py-1 bg-slate-100 text-slate-700 text-xs font-medium rounded-full">
                        Verifikasi Dosen
                    </span>
                </div>

                <x-supervision.logbook-weekly
                    :bundles="$weeklyBundles"
                    :reviewRoute="route('lecturer.logbooks.bulk_approve')"
                    role="lecturer"
                    emptyMessage="Mahasiswa belum menginputkan entri logbook kegiatan."
                />
            </div>

            <!-- 5. LEMBAR RIWAYAT KONSULTASI BIMBINGAN DPL (SUPERVISION LOG) -->
            <div id="supervision-log" class="bg-white rounded-xl border border-slate-200 p-6 sm:p-7 shadow-xs space-y-6" x-data="{ openForm: {{ $consultations->isEmpty() ? 'true' : 'false' }} }">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-slate-200 pb-4">
                    <div>
                        <h3 class="font-bold text-base text-slate-900">Riwayat Konsultasi & Bimbingan Dosen</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Catat sesi konsultasi naskah laporan, telaah metodologi, dan arahan revisi bersama mahasiswa</p>
                    </div>
                    <div class="w-full sm:w-auto flex flex-wrap items-center justify-between sm:justify-end gap-2">
                        <span class="px-3 py-1 bg-slate-100 text-slate-700 text-xs font-medium rounded-full">
                            {{ $consultations->count() }} Sesi Bimbingan
                        </span>
                        <button type="button" @click="openForm = !openForm" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition shadow-xs cursor-pointer">
                            <span x-text="openForm ? 'Sembunyikan Form' : 'Tambah Sesi Bimbingan'">Tambah Sesi Bimbingan</span>
                        </button>
                    </div>
                </div>

                <!-- Form Tambah Catatan Sesi Bimbingan -->
                <div x-show="openForm" x-collapse x-cloak class="p-5 bg-slate-50/80 rounded-xl border border-slate-200 space-y-4">
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                        Input Catatan Sesi Konsultasi Dosen
                    </h4>
                    <form method="POST" action="{{ route('lecturer.consultations.store', $placement->id) }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Pertemuan / Konsultasi <span class="text-rose-500">*</span></label>
                                <input type="date" name="consultation_date" value="{{ date('Y-m-d') }}" required
                                       class="w-full text-xs border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Tahapan Bimbingan Laporan <span class="text-rose-500">*</span></label>
                                <select name="stage" required class="w-full text-xs border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 font-medium">
                                    <option value="Bab 1-3 (Pendahuluan & Metodologi)">Bab 1-3 (Pendahuluan & Metodologi)</option>
                                    <option value="Analisis Sistem & Pembahasan Masalah">Analisis Sistem & Pembahasan Masalah</option>
                                    <option value="Penyusunan Draf Laporan Akhir">Penyusunan Draf Laporan Akhir</option>
                                    <option value="Revisi & Penyempurnaan Bab Akhir">Revisi & Penyempurnaan Bab Akhir</option>
                                    <option value="Persiapan Ujian / Pasca-Magang">Persiapan Ujian / Pasca-Magang</option>
                                    <option value="Konsultasi Khusus & Evaluasi Kinerja">Konsultasi Khusus & Evaluasi Kinerja</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Pokok Bahasan / Topik Konsultasi <span class="text-rose-500">*</span></label>
                            <input type="text" name="topic" placeholder="Contoh: Review Bab 2 Landasan Teori dan Kerangka Kerja Scrum di Diskominfo" required
                                   class="w-full text-xs border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan, Masukan, & Arahan Dosen <span class="text-rose-500">*</span></label>
                            <textarea name="notes" rows="3" placeholder="Tuliskan arahan perbaikan, format referensi, atau instruksi langkah selanjutnya..." required
                                      class="w-full text-xs border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" @click="openForm = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition shadow-xs cursor-pointer">
                                Simpan Catatan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Timeline Daftar Riwayat Konsultasi -->
                <div class="space-y-3">
                    @forelse ($consultations as $session)
                        <div class="p-4 rounded-xl bg-white border border-slate-200 space-y-2.5">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 border-b border-slate-100 pb-2.5">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2.5 py-0.5 bg-slate-100 text-slate-800 rounded-md text-xs font-semibold">
                                        Sesi #{{ $loop->iteration }}
                                    </span>
                                    <span class="text-xs font-semibold text-slate-900">
                                        {{ \Carbon\Carbon::parse($session->consultation_date)->translatedFormat('l, d F Y') }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                        {{ $session->stage }}
                                    </span>
                                </div>

                                <form method="POST" action="{{ route('lecturer.consultations.destroy', $session->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan sesi bimbingan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-medium text-slate-400 hover:text-rose-600 px-2 py-1 transition" title="Hapus catatan sesi">
                                        Hapus
                                    </button>
                                </form>
                            </div>

                            <div>
                                <h5 class="text-xs font-bold text-slate-900 leading-snug">
                                    Topik: <span class="font-normal text-slate-700">{{ $session->topic }}</span>
                                </h5>
                                <div class="mt-1.5 p-3 rounded-lg bg-slate-50 border border-slate-100 text-xs text-slate-700 leading-relaxed italic">
                                    "{{ $session->notes }}"
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400 text-xs bg-slate-50/50 rounded-xl border border-dashed border-slate-200 p-6">
                            <p class="font-semibold text-slate-700 text-sm">Belum Ada Sesi Bimbingan</p>
                            <p class="text-xs text-slate-400 mt-1">Gunakan tombol "Tambah Sesi Bimbingan" di atas untuk mencatat konsultasi laporan akademik.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
