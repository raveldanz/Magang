<x-app-layout>
    <x-slot name="header">
        <x-page-header 
            title="Pengembalian Revisi Sertifikat Magang" 
            description="Pilih aspek data yang memerlukan koreksi dan berikan instruksi perbaikan kepada Admin Dinas sebelum pengesahan resmi.">
            <x-slot name="actions">
                <a href="{{ route('supervisor.certificates.index') }}" 
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
                    <div class="font-bold text-sm mb-1">Terdapat data yang belum lengkap:</div>
                    <ul class="list-disc list-inside text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- KARTU KONTEKS MAHASISWA & SERTIFIKAT --}}
            <div class="bg-white rounded-3xl border-2 border-slate-200 p-6 shadow-xs">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div class="flex items-start gap-4 min-w-0">
                        <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center font-black text-xl shrink-0">
                            {{ strtoupper(substr($student?->name ?? 'M', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-lg sm:text-xl font-black text-slate-900 truncate">
                                    {{ $student?->name }}
                                </h2>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    {{ $unit?->name ?? 'Dispusip Surabaya' }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                NIM: <span class="font-bold text-slate-800">{{ $profile?->nim ?? '-' }}</span> &bull; 
                                <span class="text-blue-700 font-semibold">{{ $profile?->universitas ?? $student?->university ?? '-' }}</span> &bull; 
                                <span class="text-slate-600">{{ $profile?->jurusan ?? '-' }}</span>
                            </p>
                            <p class="text-[11px] text-slate-400 font-mono mt-0.5">
                                Draft No: {{ $certificate->certificate_number ?? $placement->certificate_number ?? 'Nomor otomatis' }}
                            </p>
                        </div>
                    </div>

                    <div class="shrink-0 flex items-center gap-2">
                        <a href="{{ route('supervisor.certificates.show', $placement->id) }}" target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-800 font-bold text-xs border border-slate-300 shadow-2xs transition cursor-pointer">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Pratinjau Draft Sertifikat</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- FORMULIR PEMILIHAN ASPEK & CATATAN REVISI --}}
            <form method="POST" action="{{ route('supervisor.certificates.submitRevision', $placement->id) }}"
                  x-data="{
                      selectedAspects: {{ json_encode(old('aspects', ['nilai'])) }},
                      notesText: {{ json_encode(old('notes', '')) }},
                      appendNote(text) {
                          if (this.notesText.trim() === '') {
                              this.notesText = text;
                          } else if (!this.notesText.includes(text)) {
                              this.notesText = this.notesText.trim() + '\n- ' + text;
                          }
                      },
                      toggleAspect(val) {
                          if (this.selectedAspects.includes(val)) {
                              this.selectedAspects = this.selectedAspects.filter(a => a !== val);
                          } else {
                              this.selectedAspects.push(val);
                          }
                      }
                  }"
                  class="space-y-6">
                @csrf

                {{-- Header Panduan Pilihan Aspek --}}
                <div class="bg-white rounded-3xl border-2 border-slate-200 p-6 shadow-xs space-y-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-black uppercase tracking-wider text-amber-600">Pusat Kendali Pengembalian</span>
                            <h3 class="text-lg font-black text-slate-900 mt-0.5">Pilih Aspek yang Perlu Direvisi</h3>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                            Wajib Pilih Minimal 1 Aspek
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Centang aspek-aspek di bawah ini yang memerlukan perbaikan dari Admin Dinas atau Mentor Lapangan. Anda dapat melihat data aktual yang saat ini tercatat untuk setiap aspek.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    {{-- ASPEK 1: NILAI EVALUASI & TRANSKRIP --}}
                    <div class="rounded-3xl border-2 transition p-6 space-y-4"
                         :class="selectedAspects.includes('nilai') ? 'bg-amber-50/40 border-amber-400 shadow-sm' : 'bg-white border-slate-200'">
                        <div class="flex items-start justify-between gap-3 border-b pb-3"
                             :class="selectedAspects.includes('nilai') ? 'border-amber-200' : 'border-slate-100'">
                            <label class="flex items-center gap-3 cursor-pointer select-none">
                                <input type="checkbox" name="aspects[]" value="nilai" 
                                       :checked="selectedAspects.includes('nilai')"
                                       @change="toggleAspect('nilai')"
                                       class="w-5 h-5 rounded-lg border-2 text-amber-600 focus:ring-0 cursor-pointer"
                                       :class="selectedAspects.includes('nilai') ? 'border-amber-500' : 'border-slate-300'">
                                <div>
                                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-700 block">Aspek 1</span>
                                    <h4 class="text-base font-black text-slate-900">Koreksi Nilai Evaluasi & Transkrip</h4>
                                </div>
                            </label>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold"
                                  :class="selectedAspects.includes('nilai') ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-600'">
                                <span x-show="selectedAspects.includes('nilai')">Dipilih untuk Revisi</span>
                                <span x-show="!selectedAspects.includes('nilai')">Sesuai / Valid</span>
                            </span>
                        </div>

                        {{-- Ringkasan Data Nilai Terkini --}}
                        <div class="p-3.5 bg-white/90 rounded-2xl border border-slate-200/80 space-y-3 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-500 uppercase text-[10px]">Nilai Akhir Saat Ini:</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-base font-black text-slate-900">
                                        {{ $evaluation->final_score ?? '-' }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-md font-bold text-xs bg-emerald-100 text-emerald-800">
                                        Grade: {{ $evaluation->grade ?? '-' }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-2 text-center pt-2 border-t border-slate-100">
                                <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                                    <span class="text-[10px] text-slate-400 font-bold block">1. Disiplin</span>
                                    <span class="font-black text-slate-800 text-sm mt-0.5 block">{{ $evaluation->nilai_disiplin ?? '-' }}</span>
                                </div>
                                <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                                    <span class="text-[10px] text-slate-400 font-bold block">2. Kinerja</span>
                                    <span class="font-black text-slate-800 text-sm mt-0.5 block">{{ $evaluation->nilai_kinerja ?? '-' }}</span>
                                </div>
                                <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                                    <span class="text-[10px] text-slate-400 font-bold block">3. Laporan</span>
                                    <span class="font-black text-slate-800 text-sm mt-0.5 block">{{ $evaluation->nilai_laporan ?? '-' }}</span>
                                </div>
                            </div>

                            @if($evaluation?->catatan)
                                <div class="p-2 rounded-xl bg-slate-50 border border-slate-100 text-[11px] text-slate-600">
                                    <span class="font-bold text-slate-700">Catatan Mentor:</span> "{{ $evaluation->catatan }}"
                                </div>
                            @endif
                        </div>

                        {{-- Catatan Spesifik Aspek Nilai --}}
                        <div x-show="selectedAspects.includes('nilai')" x-transition class="space-y-1.5 pt-1">
                            <label class="block text-xs font-bold text-slate-800">
                                Catatan Khusus Aspek Nilai:
                            </label>
                            <textarea name="aspect_notes[nilai]" rows="2" 
                                      placeholder="Contoh: Skor kedisiplinan presensi mohon diverifikasi ulang dengan buku absensi..."
                                      class="w-full text-xs p-3 rounded-xl border border-amber-300 focus:border-amber-600 focus:ring-0 bg-white shadow-2xs">{{ old('aspect_notes.nilai') }}</textarea>
                        </div>
                    </div>

                    {{-- ASPEK 2: DATA MAHASISWA & MAGANG --}}
                    <div class="rounded-3xl border-2 transition p-6 space-y-4"
                         :class="selectedAspects.includes('identitas') ? 'bg-amber-50/40 border-amber-400 shadow-sm' : 'bg-white border-slate-200'">
                        <div class="flex items-start justify-between gap-3 border-b pb-3"
                             :class="selectedAspects.includes('identitas') ? 'border-amber-200' : 'border-slate-100'">
                            <label class="flex items-center gap-3 cursor-pointer select-none">
                                <input type="checkbox" name="aspects[]" value="identitas" 
                                       :checked="selectedAspects.includes('identitas')"
                                       @change="toggleAspect('identitas')"
                                       class="w-5 h-5 rounded-lg border-2 text-amber-600 focus:ring-0 cursor-pointer"
                                       :class="selectedAspects.includes('identitas') ? 'border-amber-500' : 'border-slate-300'">
                                <div>
                                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-700 block">Aspek 2</span>
                                    <h4 class="text-base font-black text-slate-900">Data Mahasiswa & Magang</h4>
                                </div>
                            </label>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold"
                                  :class="selectedAspects.includes('identitas') ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-600'">
                                <span x-show="selectedAspects.includes('identitas')">Dipilih untuk Revisi</span>
                                <span x-show="!selectedAspects.includes('identitas')">Sesuai / Valid</span>
                            </span>
                        </div>

                        {{-- Ringkasan Biodata & Periode Magang --}}
                        <div class="p-3.5 bg-white/90 rounded-2xl border border-slate-200/80 space-y-2 text-xs">
                            <div class="flex justify-between items-center py-1 border-b border-slate-100">
                                <span class="text-slate-500">Nama Mahasiswa:</span>
                                <span class="font-bold text-slate-900 text-right">{{ $student?->name }}</span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-100">
                                <span class="text-slate-500">Nomor Induk Mahasiswa:</span>
                                <span class="font-mono font-bold text-slate-800">{{ $profile?->nim ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-100">
                                <span class="text-slate-500">Perguruan Tinggi:</span>
                                <span class="font-semibold text-blue-700 text-right">{{ $profile?->universitas ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-100">
                                <span class="text-slate-500">Program Studi / Jurusan:</span>
                                <span class="font-semibold text-slate-800 text-right">{{ $profile?->jurusan ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-1">
                                <span class="text-slate-500">Periode Magang:</span>
                                <span class="font-medium text-slate-700 text-right">
                                    {{ $application->start_date ? \Carbon\Carbon::parse($application->start_date)->isoFormat('D MMM Y') : '-' }} s/d {{ $application->end_date ? \Carbon\Carbon::parse($application->end_date)->isoFormat('D MMM Y') : '-' }}
                                </span>
                            </div>
                        </div>

                        {{-- Catatan Spesifik Aspek Identitas --}}
                        <div x-show="selectedAspects.includes('identitas')" x-transition class="space-y-1.5 pt-1">
                            <label class="block text-xs font-bold text-slate-800">
                                Catatan Khusus Aspek Identitas:
                            </label>
                            <textarea name="aspect_notes[identitas]" rows="2" 
                                      placeholder="Contoh: Ejaan nama mahasiswa mohon disesuaikan dengan KTP atau format penulisan NIM..."
                                      class="w-full text-xs p-3 rounded-xl border border-amber-300 focus:border-amber-600 focus:ring-0 bg-white shadow-2xs">{{ old('aspect_notes.identitas') }}</textarea>
                        </div>
                    </div>

                    {{-- ASPEK 3: VALIDASI LOGBOOK & NASKAH LAPORAN --}}
                    <div class="rounded-3xl border-2 transition p-6 space-y-4"
                         :class="selectedAspects.includes('laporan') ? 'bg-amber-50/40 border-amber-400 shadow-sm' : 'bg-white border-slate-200'">
                        <div class="flex items-start justify-between gap-3 border-b pb-3"
                             :class="selectedAspects.includes('laporan') ? 'border-amber-200' : 'border-slate-100'">
                            <label class="flex items-center gap-3 cursor-pointer select-none">
                                <input type="checkbox" name="aspects[]" value="laporan" 
                                       :checked="selectedAspects.includes('laporan')"
                                       @change="toggleAspect('laporan')"
                                       class="w-5 h-5 rounded-lg border-2 text-amber-600 focus:ring-0 cursor-pointer"
                                       :class="selectedAspects.includes('laporan') ? 'border-amber-500' : 'border-slate-300'">
                                <div>
                                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-700 block">Aspek 3</span>
                                    <h4 class="text-base font-black text-slate-900">Validasi Logbook & Laporan Akhir</h4>
                                </div>
                            </label>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold"
                                  :class="selectedAspects.includes('laporan') ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-600'">
                                <span x-show="selectedAspects.includes('laporan')">Dipilih untuk Revisi</span>
                                <span x-show="!selectedAspects.includes('laporan')">Sesuai / Valid</span>
                            </span>
                        </div>

                        {{-- Ringkasan Status Logbook & Naskah Laporan --}}
                        <div class="p-3.5 bg-white/90 rounded-2xl border border-slate-200/80 space-y-2.5 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Presensi & Logbook Aktivitas:</span>
                                <span class="font-bold text-slate-900">
                                    {{ $logbookApproved }} disetujui dari {{ $logbookTotal }} entri
                                </span>
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                                <span class="text-slate-500">Naskah Laporan Akhir:</span>
                                @if($finalReport && $finalReport->file_path)
                                    <a href="{{ route('final_reports.show', $finalReport->id) }}" target="_blank"
                                       class="inline-flex items-center gap-1 font-bold text-blue-600 hover:text-blue-800">
                                        <span>Buka Dokumen PDF</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                @else
                                    <span class="text-rose-600 font-bold">Belum Tersedia / Belum Diunggah</span>
                                @endif
                            </div>
                        </div>

                        {{-- Catatan Spesifik Aspek Laporan --}}
                        <div x-show="selectedAspects.includes('laporan')" x-transition class="space-y-1.5 pt-1">
                            <label class="block text-xs font-bold text-slate-800">
                                Catatan Khusus Logbook & Laporan:
                            </label>
                            <textarea name="aspect_notes[laporan]" rows="2" 
                                      placeholder="Contoh: Naskah laporan akhir belum dilengkapi lembar persetujuan pembimbing..."
                                      class="w-full text-xs p-3 rounded-xl border border-amber-300 focus:border-amber-600 focus:ring-0 bg-white shadow-2xs">{{ old('aspect_notes.laporan') }}</textarea>
                        </div>
                    </div>

                    {{-- ASPEK 4: LAINNYA / PENOMORAN DOKUMEN --}}
                    <div class="rounded-3xl border-2 transition p-6 space-y-4"
                         :class="selectedAspects.includes('lainnya') ? 'bg-amber-50/40 border-amber-400 shadow-sm' : 'bg-white border-slate-200'">
                        <div class="flex items-start justify-between gap-3 border-b pb-3"
                             :class="selectedAspects.includes('lainnya') ? 'border-amber-200' : 'border-slate-100'">
                            <label class="flex items-center gap-3 cursor-pointer select-none">
                                <input type="checkbox" name="aspects[]" value="lainnya" 
                                       :checked="selectedAspects.includes('lainnya')"
                                       @change="toggleAspect('lainnya')"
                                       class="w-5 h-5 rounded-lg border-2 text-amber-600 focus:ring-0 cursor-pointer"
                                       :class="selectedAspects.includes('lainnya') ? 'border-amber-500' : 'border-slate-300'">
                                <div>
                                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-700 block">Aspek 4</span>
                                    <h4 class="text-base font-black text-slate-900">Aspek Lainnya & Tata Persuratan</h4>
                                </div>
                            </label>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold"
                                  :class="selectedAspects.includes('lainnya') ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-600'">
                                <span x-show="selectedAspects.includes('lainnya')">Dipilih untuk Revisi</span>
                                <span x-show="!selectedAspects.includes('lainnya')">Sesuai / Valid</span>
                            </span>
                        </div>

                        <div class="p-3.5 bg-white/90 rounded-2xl border border-slate-200/80 text-xs text-slate-600">
                            Pilih aspek ini jika terdapat penyesuaian khusus pada penomoran sertifikat, klausul redaksional, nama penandatangan, atau dokumen kelengkapan tambahan.
                        </div>

                        {{-- Catatan Spesifik Aspek Lainnya --}}
                        <div x-show="selectedAspects.includes('lainnya')" x-transition class="space-y-1.5 pt-1">
                            <label class="block text-xs font-bold text-slate-800">
                                Catatan Khusus Aspek Lainnya:
                            </label>
                            <textarea name="aspect_notes[lainnya]" rows="2" 
                                      placeholder="Tuliskan catatan detail terkait tata persuratan atau nomor dokumen..."
                                      class="w-full text-xs p-3 rounded-xl border border-amber-300 focus:border-amber-600 focus:ring-0 bg-white shadow-2xs">{{ old('aspect_notes.lainnya') }}</textarea>
                        </div>
                    </div>

                </div>

                {{-- INSTRUKSI & CATATAN UTAMA (WAJIB) --}}
                <div class="bg-white rounded-3xl border-2 border-slate-200 p-6 shadow-xs space-y-4">
                    <div>
                        <span class="text-xs font-black uppercase tracking-wider text-amber-600">Instruksi Eksekutif</span>
                        <h3 class="text-base sm:text-lg font-black text-slate-900 mt-0.5">
                            Catatan Resmi Perbaikan untuk Admin Dinas
                        </h3>
                        <p class="text-xs text-slate-600 mt-1">
                            Tuliskan arahan spesifik yang wajib ditindaklanjuti Admin Dinas sebelum sertifikat ini diajukan kembali ke meja Atasan.
                        </p>
                    </div>

                    {{-- Quick Template Chips --}}
                    <div>
                        <span class="text-[11px] font-bold text-slate-500 block mb-2">Rekomendasi Template Catatan Cepat:</span>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" @click="appendNote('Mohon sesuaikan ejaan nama lengkap mahasiswa dengan KTP resmi.')"
                                    class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-amber-100 hover:text-amber-900 text-slate-700 text-xs font-semibold transition cursor-pointer">
                                + Ejaan nama sesuai KTP
                            </button>
                            <button type="button" @click="appendNote('Nilai disiplin presensi perlu diperiksa ulang dengan logbook kehadiran.')"
                                    class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-amber-100 hover:text-amber-900 text-slate-700 text-xs font-semibold transition cursor-pointer">
                                + Sinkronisasi nilai presensi
                            </button>
                            <button type="button" @click="appendNote('Periksa kembali tanggal periode pelaksanaan magang pada sistem.')"
                                    class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-amber-100 hover:text-amber-900 text-slate-700 text-xs font-semibold transition cursor-pointer">
                                + Koreksi periode magang
                            </button>
                            <button type="button" @click="appendNote('Naskah laporan akhir mohon dilengkapi lembar pengesahan bertanda tangan.')"
                                    class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-amber-100 hover:text-amber-900 text-slate-700 text-xs font-semibold transition cursor-pointer">
                                + Pengesahan naskah laporan
                            </button>
                            <button type="button" @click="appendNote('Format penulisan NIM dan Program Studi perlu disesuaikan.')"
                                    class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-amber-100 hover:text-amber-900 text-slate-700 text-xs font-semibold transition cursor-pointer">
                                + Format NIM dan Prodi
                            </button>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <textarea name="notes" rows="4" required minlength="5" x-model="notesText"
                                  placeholder="Tuliskan rangkuman instruksi revisi yang jelas untuk Admin Dinas..."
                                  class="w-full text-sm p-4 rounded-2xl border-2 border-slate-300 focus:border-amber-600 focus:ring-0 text-slate-800 bg-slate-50/50 shadow-inner leading-relaxed"></textarea>
                        <div class="flex items-center justify-between text-[11px] text-slate-400">
                            <span>Minimal 5 karakter instruksi</span>
                            <span x-text="notesText.length + ' karakter'"></span>
                        </div>
                    </div>
                </div>

                {{-- PANEL TOMBOL AKSI BAWAH --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('supervisor.certificates.index') }}" 
                       class="px-6 py-3 rounded-2xl bg-white hover:bg-slate-100 text-slate-700 font-bold text-sm border-2 border-slate-200 transition">
                        Batal
                    </a>
                    <button type="submit" 
                            class="px-8 py-3.5 rounded-2xl bg-amber-600 hover:bg-amber-700 active:scale-95 text-white font-black text-sm shadow-lg shadow-amber-600/30 transition flex items-center gap-2 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>Kirim Catatan Revisi</span>
                    </button>
                </div>

            </form>

        </div>
    </div>
</x-app-layout>
