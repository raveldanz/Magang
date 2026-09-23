<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                    <span>Pengunggahan Laporan Akhir Magang MBKM</span>
                </h2>
                
            </div>
        </div>
    </x-slot>

    <div class="py-5 sm:py-8 lg:py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Success / Error Messages -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-xs flex items-center justify-between text-rose-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-xs text-rose-900 text-xs space-y-1">
                    <p class="font-bold">Terjadi kesalahan validasi berkas:</p>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Informational Banner for Early & Parallel Upload -->
            <div class="p-4 bg-blue-50/60 border border-blue-200/80 rounded-2xl flex items-start gap-3 text-blue-900 text-xs sm:text-sm font-medium leading-relaxed shadow-2xs">
                <div class="w-7 h-7 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <span class="font-bold block text-blue-950 text-sm mb-0.5">Informasi Pengunggahan Laporan Akhir</span>
                    <span>Anda dapat mengunggah atau memperbarui draf naskah laporan ilmiah & repositori proyek magang Anda <strong>kapan saja</strong> selama periode magang berlangsung. Pastikan juga untuk tetap mengisi logbook kegiatan harian secara teratur.</span>
                </div>
            </div>

            <!-- 1. STATUS LAPORAN SAAT INI -->
            @if ($finalReport)
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-100">
                        <div>
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Status Verifikasi Naskah</span>
                            <h3 class="font-black text-lg text-slate-900 mt-1 flex items-center gap-2">
                                <span>{{ $finalReport->title ?? 'Naskah Laporan Akhir Magang' }}</span>
                            </h3>
                            @if($finalReport->repository_url)
                                <a href="{{ $finalReport->repository_url }}" target="_blank" class="text-xs text-blue-600 hover:text-blue-800 font-semibold mt-1 inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                    <span>Tautan Proyek: {{ $finalReport->repository_url }}</span>
                                </a>
                            @endif
                        </div>

                        <div>
                            @if($finalReport->status === 'approved')
                                <span class="px-4 py-2 rounded-2xl text-xs font-black bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center gap-1.5 shadow-2xs">
                                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span>Laporan Disetujui (ACC)</span>
                                </span>
                            @elseif($finalReport->status === 'revision')
                                <span class="px-4 py-2 rounded-2xl text-xs font-black bg-rose-50 text-rose-800 border border-rose-200 flex items-center gap-1.5 shadow-2xs">
                                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>Perlu Perbaikan (Revisi)</span>
                                </span>
                            @else
                                <span class="px-4 py-2 rounded-2xl text-xs font-black bg-amber-50 text-amber-800 border border-amber-200 flex items-center gap-1.5 shadow-2xs">
                                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Menunggu Verifikasi DPL / Mentor</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Detail & Unduh File -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="text-xs text-slate-600">
                            Terakhir diunggah: <strong>{{ $finalReport->updated_at ? $finalReport->updated_at->format('d F Y, H:i') : '-' }}</strong>
                        </div>
                        <a href="{{ route('final_reports.show', $finalReport->id) }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Buka / Unduh Berkas Laporan</span>
                        </a>
                    </div>

                    <!-- Feedback / Catatan Revisi jika ada -->
                    @if ($finalReport->feedback)
                        <div class="p-4 rounded-2xl {{ $finalReport->status === 'revision' ? 'bg-rose-50/80 border-rose-200 text-rose-900' : 'bg-emerald-50/80 border-emerald-200 text-emerald-900' }} border text-xs space-y-1">
                            <span class="font-bold uppercase tracking-wider text-[11px] block flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                {{ $finalReport->status === 'revision' ? 'Catatan Perbaikan / Masukan Revisi:' : 'Catatan Pembimbing:' }}
                            </span>
                            <p class="whitespace-pre-line leading-relaxed italic">
                                "{{ $finalReport->feedback }}"
                            </p>
                        </div>
                    @endif
                </div>
            @endif

            <!-- 2. FORM UNGGAH / PERBAHARUI LAPORAN -->
            @if (!$finalReport || $finalReport->status !== 'approved')
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
                    <div class="border-b border-slate-100 pb-3">
                        <h3 class="font-black text-base text-slate-900">
                            {{ $finalReport ? 'Perbarui / Unggah Ulang Laporan Revisi' : 'Formulir Pengunggahan Laporan Akhir' }}
                        </h3>
                    </div>

                    <form action="{{ route('student.final_report.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                        @csrf

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Judul Naskah Laporan Akhir Magang
                            </label>
                            <input type="text" name="title" value="{{ old('title', $finalReport->title ?? 'Laporan Akhir Praktik Kerja Lapangan (PKL) / Magang MBKM') }}" placeholder="Contoh: Rancang Bangun Sistem Monitoring Layanan Publik..." class="w-full text-xs sm:text-sm border-slate-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    Tautan Repositori Proyek / Luaran Kerja (Opsional)
                                </label>
                                <span id="repoUrlBadge" class="hidden text-[11px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200">
                                    ✓ Format Tautan Valid
                                </span>
                            </div>
                            <div class="relative flex items-center">
                                <input type="url" name="repository_url" id="repository_url" value="{{ old('repository_url', $finalReport->repository_url ?? '') }}" 
                                       oninput="handleRepoUrlChange(this)"
                                       placeholder="https://github.com/username/project atau link Google Drive" 
                                       class="w-full text-xs sm:text-sm border-slate-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono pr-28">
                                <a id="repoTestBtn" href="#" target="_blank" class="hidden absolute right-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition items-center gap-1 shadow-xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    <span>Uji Link</span>
                                </a>
                            </div>
                        </div>

                        <!-- Himbauan Format Penamaan Berkas -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-start gap-3.5 shadow-2xs">
                            <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="text-xs space-y-1.5 flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-900 text-xs sm:text-sm">Himbauan Format Penamaan Berkas</span>
                                    <span class="px-2 py-0.5 rounded-md bg-slate-200 text-slate-700 text-[10px] font-bold uppercase tracking-wider">Format Baku</span>
                                </div>
                                <p class="text-slate-600 leading-relaxed text-xs">
                                    Agar memudahkan Dosen Pembimbing Lapangan (DPL) dan Pembimbing Dinas dalam memeriksa serta mengarsipkan naskah magang Anda, pastikan nama file telah berformat standar sebelum diunggah:
                                </p>
                                <div class="p-2.5 bg-white border border-slate-200 rounded-xl space-y-1">
                                    <div class="flex items-center gap-2 font-mono text-xs font-bold text-blue-700">
                                        <svg class="w-3.5 h-3.5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>Format: Laporan_Akhir_[NIM]_[Nama_Lengkap].[pdf/docx]</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500">
                                        Contoh Anda: <strong class="text-slate-800 font-mono">Laporan_Akhir_{{ preg_replace('/[^A-Za-z0-9]/', '', Auth::user()->studentProfile->nim ?? '22051204001') }}_{{ \Illuminate\Support\Str::slug(Auth::user()->name, '_') }}.pdf</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. INTERACTIVE DRAG & DROP DROPZONE + HARMONIZED LIVE PREVIEW -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    Unggah File Naskah Laporan (PDF / DOCX) 
                                    @if(!$finalReport || !$finalReport->file_path)
                                        <span class="text-rose-500">*</span>
                                    @endif
                                </label>
                                @if($finalReport && $finalReport->file_path)
                                    <span class="text-[11px] text-blue-600 font-medium">
                                        Berkas tersimpan tersedia (Opsional ganti berkas)
                                    </span>
                                @endif
                            </div>

                            <!-- Drag & Drop Zone Box -->
                            <div id="dropzoneArea" 
                                 onclick="document.getElementById('file_laporan').click()"
                                 ondragover="handleDragOver(event)" 
                                 ondragleave="handleDragLeave(event)" 
                                 ondrop="handleFileDrop(event)"
                                 class="relative border-2 border-dashed border-slate-300 hover:border-blue-600 rounded-3xl p-6 sm:p-8 bg-slate-50/50 hover:bg-blue-50/40 transition cursor-pointer text-center group space-y-3">
                                <input type="file" name="file_laporan" id="file_laporan" accept=".pdf,.doc,.docx" 
                                       {{ $finalReport && $finalReport->file_path ? '' : 'required' }}
                                       onchange="handleFinalReportPreview(this)"
                                       class="hidden">
                                
                                <div class="w-12 h-12 mx-auto rounded-2xl bg-blue-50 group-hover:bg-blue-600 text-blue-600 group-hover:text-white border border-blue-100 flex items-center justify-center transition shadow-2xs">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-xs sm:text-sm font-bold text-slate-800">
                                        <span class="text-blue-600 underline">Pilih berkas dari komputer</span> atau tarik & lepas (drag & drop) di sini
                                    </p>
                                    <p class="text-[11px] text-slate-400">
                                        Format didukung: <strong>PDF, DOC, DOCX</strong> (Maksimal 10 MB)
                                    </p>
                                </div>
                            </div>

                            <!-- File Info & Quick Actions Box -->
                            <div id="reportFileBox" class="hidden mt-4 p-4 bg-slate-50 border border-slate-200 rounded-2xl items-center justify-between gap-3 shadow-2xs">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div id="reportFileIcon" class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                                        PDF
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p id="reportFileName" class="text-xs font-bold text-slate-900 truncate"></p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span id="reportFileSize" class="text-[11px] text-slate-500 font-medium"></span>
                                            <span id="pdfBadge" class="hidden text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200">Pratinjau PDF Siap</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <a id="reportViewFileBtn" href="#" target="_blank" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white border border-blue-600 rounded-xl text-xs font-bold transition shadow-2xs inline-flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Lihat Berkas</span>
                                    </a>
                                    <button type="button" onclick="document.getElementById('file_laporan').click()" class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 rounded-xl text-xs font-bold transition shadow-2xs">
                                        Ganti Berkas
                                    </button>
                                </div>
                            </div>

                        </div>

                        <div class="pt-3 border-t border-gray-100 flex flex-col-reverse sm:flex-row items-stretch sm:items-center sm:justify-end gap-2.5 sm:gap-3">
                            <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-5 py-3 sm:py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition text-center justify-center flex items-center">
                                Batal
                            </a>
                            <button type="submit" class="w-full sm:w-auto px-7 py-3 sm:py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer text-center justify-center flex items-center">
                                {{ $finalReport ? 'Unggah Ulang Naskah Revisi' : 'Kirim Laporan Akhir' }}
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-emerald-200 shadow-xs space-y-6">
                    <div class="p-6 bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50 border border-emerald-200/80 rounded-2xl text-center space-y-3 shadow-2xs">
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-600 text-white flex items-center justify-center shadow-md shadow-emerald-600/30">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <h3 class="font-black text-lg sm:text-xl text-emerald-950">Laporan Akhir Anda Telah Disetujui Secara Resmi (ACC)</h3>
                        <p class="text-xs sm:text-sm text-emerald-800 max-w-xl mx-auto leading-relaxed">
                            Selamat! Naskah laporan ilmiah dan luaran magang Anda telah diverifikasi dan disetujui oleh Dosen Pembimbing Lapangan (DPL) dan Mentor Instansi Pemerintah Kota Surabaya.
                        </p>
                    </div>

                    @php
                        $univ = $evaluation?->getUniversity();
                        $scheme = $evaluation?->evaluation_scheme ?? ($univ->evaluation_scheme ?? 'dual_evaluation');
                        $isMentorOnly = ($scheme === 'mentor_only');

                        $hasMentorScore = $evaluation && ($evaluation->nilai_pembimbing ?? 0) > 0;
                        $hasDosenScore = $evaluation && (($evaluation->nilai_dosen_calculated ?? 0) > 0 || ($evaluation->nilai_dosen ?? 0) > 0 || ($evaluation->nilai_akademik ?? 0) > 0);

                        $isEvalComplete = $evaluation && $evaluation->is_complete;
                    @endphp

                    @if ($isEvalComplete)
                        <!-- Rekapitulasi Nilai Kelulusan Mahasiswa -->
                        <div class="space-y-4 pt-2">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                                <div>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600 block">Hasil Evaluasi Magang MBKM</span>
                                    <h4 class="font-black text-base text-gray-900">Rekapitulasi Nilai Kelulusan & Prestasi</h4>
                                </div>
                                <span class="px-3 py-1 bg-emerald-100 text-emerald-800 font-bold text-xs rounded-full border border-emerald-200">
                                    Lulus Magang
                                </span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-center">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Nilai Disiplin</span>
                                    <span class="text-xl font-black text-slate-800 mt-1 block">{{ number_format($evaluation->nilai_disiplin ?? 0, 1) }}</span>
                                    <span class="text-[10px] text-slate-400">Kehadiran & Tata Tertib</span>
                                </div>
                                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-center">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Nilai Kinerja</span>
                                    <span class="text-xl font-black text-slate-800 mt-1 block">{{ number_format($evaluation->nilai_kinerja ?? 0, 1) }}</span>
                                    <span class="text-[10px] text-slate-400">Keahlian & Inisiatif</span>
                                </div>
                                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-center">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Nilai Laporan</span>
                                    <span class="text-xl font-black text-slate-800 mt-1 block">{{ number_format($evaluation->nilai_laporan ?? 0, 1) }}</span>
                                    <span class="text-[10px] text-slate-400">Kualitas Naskah</span>
                                </div>
                                <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-100 text-center">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700 block">Nilai Akhir</span>
                                    <span class="text-xl font-black text-indigo-950 mt-1 block">
                                        {{ number_format($evaluation->nilai_akhir > 0 ? $evaluation->nilai_akhir : ($evaluation->final_score ?? 0), 1) }}
                                        <span class="text-xs font-bold text-indigo-600 font-mono">({{ $evaluation->grade ?? 'A' }})</span>
                                    </span>
                                    <span class="text-[10px] text-indigo-600 font-semibold">Predikat Kelulusan</span>
                                </div>
                            </div>

                            @if($evaluation->catatan || $evaluation->feedback_dosen)
                                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-2 text-xs">
                                    @if($evaluation->catatan)
                                        <p class="text-slate-700"><strong class="text-slate-900">Ulasan Pembimbing Lapangan:</strong> <span class="italic text-slate-600">"{{ $evaluation->catatan }}"</span></p>
                                    @endif
                                    @if($evaluation->feedback_dosen)
                                        <p class="text-slate-700"><strong class="text-slate-900">Ulasan Dosen Pembimbing Lapangan:</strong> <span class="italic text-slate-600">"{{ $evaluation->feedback_dosen }}"</span></p>
                                    @endif
                                </div>
                            @endif

                            <!-- Action Button: Cetak & Download Sertifikat -->
                            <div class="pt-3 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-end gap-3">
                                <a href="{{ route('student.certificate.show', $placement->id) }}" target="_blank"
                                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl text-xs transition shadow-md shadow-blue-600/20 active:scale-95 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Pratinjau E-Sertifikat Resmi</span>
                                </a>

                                <a href="{{ route('student.certificate.download', $placement->id) }}" target="_blank"
                                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl text-xs transition shadow-md shadow-emerald-600/20 active:scale-95 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span>Unduh E-Sertifikat & Transkrip</span>
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="p-5 rounded-2xl bg-amber-50/90 border border-amber-200 space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <span class="text-xs sm:text-sm font-bold text-amber-950 block">Menunggu Kelengkapan Lembar Penilaian & Verifikasi</span>
                                    <p class="text-xs text-amber-800 leading-relaxed">
                                        E-Sertifikat resmi akan otomatis terbit begitu seluruh 3 indikator kelengkapan di bawah terpenuhi.
                                    </p>
                                </div>
                            </div>

                            <!-- 3-Item Progress Checklist -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs pt-1">
                                <div class="p-3 rounded-xl border {{ $hasMentorScore ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-white border-amber-200 text-slate-700' }} space-y-1">
                                    <div class="flex items-center gap-1.5 font-bold">
                                        @if($hasMentorScore)
                                            <span class="text-emerald-600 font-bold">✓</span>
                                            <span>1. Pembimbing Lapangan</span>
                                        @else
                                            <span class="text-amber-500 font-bold">⏳</span>
                                            <span>1. Pembimbing Lapangan</span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] {{ $hasMentorScore ? 'text-emerald-700' : 'text-slate-500' }}">
                                        {{ $hasMentorScore ? 'Selesai Menilai' : 'Menunggu Penilaian Dinas' }}
                                    </p>
                                </div>

                                <div class="p-3 rounded-xl border {{ ($hasDosenScore || $isMentorOnly) ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-white border-amber-200 text-slate-700' }} space-y-1">
                                    <div class="flex items-center gap-1.5 font-bold">
                                        @if($hasDosenScore || $isMentorOnly)
                                            <span class="text-emerald-600 font-bold">✓</span>
                                            <span>2. DPL Kampus</span>
                                        @else
                                            <span class="text-amber-500 font-bold">⏳</span>
                                            <span>2. DPL Kampus</span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] {{ ($hasDosenScore || $isMentorOnly) ? 'text-emerald-700' : 'text-slate-500' }}">
                                        {{ $isMentorOnly ? 'Skema Kampus 100% Dinas' : ($hasDosenScore ? 'Selesai Menilai' : 'Menunggu Nilai Akademik DPL') }}
                                    </p>
                                </div>

                                <div class="p-3 rounded-xl border {{ ($finalReport && $finalReport->status === 'approved') ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-white border-amber-200 text-slate-700' }} space-y-1">
                                    <div class="flex items-center gap-1.5 font-bold">
                                        @if($finalReport && $finalReport->status === 'approved')
                                            <span class="text-emerald-600 font-bold">✓</span>
                                            <span>3. Naskah Laporan Akhir</span>
                                        @else
                                            <span class="text-amber-500 font-bold">⏳</span>
                                            <span>3. Naskah Laporan Akhir</span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] {{ ($finalReport && $finalReport->status === 'approved') ? 'text-emerald-700' : 'text-slate-500' }}">
                                        {{ ($finalReport && $finalReport->status === 'approved') ? 'Disetujui (ACC)' : 'Menunggu Persetujuan Naskah' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/mammoth@1.6.0/mammoth.browser.min.js"></script>
    <script>
        let currentObjectUrl = null;

        function handleRepoUrlChange(input) {
            const badge = document.getElementById('repoUrlBadge');
            const testBtn = document.getElementById('repoTestBtn');
            const val = input.value.trim();

            if (val.length > 5 && (val.startsWith('http://') || val.startsWith('https://'))) {
                badge.classList.remove('hidden');
                testBtn.classList.remove('hidden');
                testBtn.classList.add('inline-flex');
                testBtn.href = val;
            } else {
                badge.classList.add('hidden');
                testBtn.classList.add('hidden');
                testBtn.classList.remove('inline-flex');
                testBtn.href = '#';
            }
        }

        function handleDragOver(e) {
            e.preventDefault();
            e.stopPropagation();
            const dz = document.getElementById('dropzoneArea');
            dz.classList.add('border-blue-600', 'bg-blue-100/50');
        }

        function handleDragLeave(e) {
            e.preventDefault();
            e.stopPropagation();
            const dz = document.getElementById('dropzoneArea');
            dz.classList.remove('border-blue-600', 'bg-blue-100/50');
        }

        function handleFileDrop(e) {
            e.preventDefault();
            e.stopPropagation();
            const dz = document.getElementById('dropzoneArea');
            dz.classList.remove('border-blue-600', 'bg-blue-100/50');

            if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                const fileInput = document.getElementById('file_laporan');
                fileInput.files = e.dataTransfer.files;
                handleFinalReportPreview(fileInput);
            }
        }

        function handleFinalReportPreview(input) {
            const box = document.getElementById('reportFileBox');
            const nameEl = document.getElementById('reportFileName');
            const sizeEl = document.getElementById('reportFileSize');
            const iconEl = document.getElementById('reportFileIcon');
            const viewFileBtn = document.getElementById('reportViewFileBtn');

            if (currentObjectUrl) {
                URL.revokeObjectURL(currentObjectUrl);
                currentObjectUrl = null;
            }

            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (file.size > 10 * 1024 * 1024) {
                    alert('Ukuran berkas naskah laporan melebihi batas maksimal 10MB.');
                    input.value = '';
                    box.classList.add('hidden');
                    box.classList.remove('flex');
                    return;
                }

                const ext = file.name.split('.').pop().toLowerCase();
                nameEl.textContent = file.name;
                sizeEl.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                iconEl.textContent = ext.toUpperCase();

                currentObjectUrl = URL.createObjectURL(file);
                if (viewFileBtn) {
                    viewFileBtn.href = currentObjectUrl;
                }

                box.classList.remove('hidden');
                box.classList.add('flex');
            } else {
                box.classList.add('hidden');
                box.classList.remove('flex');
            }
        }

        // Initialize repository URL listener on page load
        document.addEventListener('DOMContentLoaded', () => {
            const repoInput = document.getElementById('repository_url');
            if (repoInput && repoInput.value) {
                handleRepoUrlChange(repoInput);
            }
        });
    </script>
    @endpush
</x-app-layout>
