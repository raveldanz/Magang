<x-app-layout>
    <x-slot name="header">
<<<<<<< HEAD
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Akhir Magang') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm font-semibold">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white rounded-2xl border border-slate-100 shadow-[0_4px_12px_rgba(100,116,139,0.08)] p-6">
                <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-4 mb-4">Upload Dokumen Laporan</h3>

                @if ($placement->finalreport)
                    <div class="mb-6 p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                            <div>
                                <p class="text-sm text-slate-500">Status Laporan Saat Ini:</p>
                                <span class="px-3 py-1 inline-block mt-1 text-xs font-bold rounded-full
                                    {{ $placement->finalreport->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                    {{ $placement->finalreport->status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                                    {{ $placement->finalreport->status === 'revision' ? 'bg-red-100 text-red-700' : '' }}">
                                    {{ strtoupper($placement->finalreport->status) }}
                                </span>
                            </div>
                            <a href="{{ asset('storage/' . $placement->finalreport->file_path) }}" target="_blank" class="shrink-0 px-4 py-2 bg-slate-800 text-white rounded-xl text-sm font-semibold hover:bg-slate-900 transition-colors text-center">
                                Unduh / Lihat File
                            </a>
                        </div>

                        @if ($placement->finalreport->status === 'revision')
                            <div class="mt-4 p-3 bg-red-50 text-red-700 rounded-xl text-sm">
                                <strong>Catatan Revisi dari Pembimbing:</strong> <br>
                                {{ $placement->finalreport->feedback ?? 'Tidak ada catatan khusus.' }}
                            </div>
                        @endif
                    </div>
                @endif

                @if (!$placement->finalreport || $placement->finalreport->status !== 'approved')
                    <form action="{{ route('student.final_report.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 border-t border-slate-100 pt-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-700">File Laporan (PDF / DOC / DOCX)</label>
                            <input type="file" name="file_laporan" accept=".pdf,.doc,.docx" class="mt-1 block w-full text-sm text-slate-700 border border-slate-200 rounded-xl cursor-pointer bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-500/50" required>
                            <p class="text-xs text-slate-400 mt-1">Ukuran maksimal file: 5MB.</p>
                            @error('file_laporan') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full md:w-auto px-6 py-2.5 bg-slate-800 text-white font-bold rounded-xl hover:bg-slate-900 transition-colors">
                                Unggah Laporan
                            </button>
                        </div>
                    </form>
                @else
                    <div class="p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-semibold text-center mt-4">
                        🎉 Laporan Akhir kamu telah disetujui. Tidak perlu mengunggah ulang.
                    </div>
                @endif
            </div>
=======
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="p-2 bg-white hover:bg-gray-100 border border-gray-200 text-gray-700 rounded-xl transition shadow-xs">
                ←
            </a>
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                    <span>📂</span>
                    <span>Pengunggahan Laporan Akhir Magang MBKM</span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                    Kirimkan dokumen naskah laporan ilmiah dan tautan luaran proyek magang Anda
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Success / Error Messages -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <span>✅</span>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-xs flex items-center justify-between text-rose-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <span>⚠️</span>
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

            <!-- 1. STATUS LAPORAN SAAT INI -->
            @if ($finalReport)
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-100">
                        <div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Status Verifikasi Naskah</span>
                            <h3 class="font-black text-lg text-gray-900 mt-1 flex items-center gap-2">
                                <span>📄</span>
                                <span>{{ $finalReport->title ?? 'Naskah Laporan Akhir Magang' }}</span>
                            </h3>
                            @if($finalReport->repository_url)
                                <a href="{{ $finalReport->repository_url }}" target="_blank" class="text-xs text-blue-600 hover:text-blue-800 font-semibold mt-1 block">
                                    🔗 Tautan Proyek: {{ $finalReport->repository_url }}
                                </a>
                            @endif
                        </div>

                        <div>
                            @if($finalReport->status === 'approved')
                                <span class="px-4 py-2 rounded-2xl text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1.5 shadow-2xs">
                                    <span>✅</span>
                                    <span>Laporan Disetujui (ACC)</span>
                                </span>
                            @elseif($finalReport->status === 'revision')
                                <span class="px-4 py-2 rounded-2xl text-xs font-black bg-rose-100 text-rose-800 border border-rose-300 flex items-center gap-1.5 shadow-2xs">
                                    <span>⚠️</span>
                                    <span>Perlu Perbaikan (Revisi)</span>
                                </span>
                            @else
                                <span class="px-4 py-2 rounded-2xl text-xs font-black bg-amber-100 text-amber-800 border border-amber-300 flex items-center gap-1.5 shadow-2xs">
                                    <span>⏳</span>
                                    <span>Menunggu Verifikasi DPL / Mentor</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Detail & Unduh File -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="text-xs text-gray-600">
                            Terakhir diunggah: <strong>{{ $finalReport->updated_at ? $finalReport->updated_at->format('d F Y, H:i') : '-' }}</strong>
                        </div>
                        <a href="{{ asset('storage/' . ($finalReport->file_path ?? $finalReport->final_report_path)) }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                            <span>📥</span>
                            <span>Buka / Unduh Berkas PDF</span>
                        </a>
                    </div>

                    <!-- Feedback / Catatan Revisi jika ada -->
                    @if ($finalReport->feedback)
                        <div class="p-4 rounded-2xl {{ $finalReport->status === 'revision' ? 'bg-rose-50/80 border-rose-200 text-rose-900' : 'bg-emerald-50/80 border-emerald-200 text-emerald-900' }} border text-xs space-y-1">
                            <span class="font-bold uppercase tracking-wider text-[11px] block">
                                💬 Catatan dari Pembimbing / DPL:
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
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-xs space-y-6">
                    <div class="border-b border-gray-100 pb-3">
                        <h3 class="font-black text-base text-gray-900">
                            {{ $finalReport ? 'Perbarui / Unggah Ulang Laporan Revisi' : 'Formulir Pengunggahan Laporan Akhir' }}
                        </h3>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Pastikan format naskah laporan telah mengikuti pedoman tata tulis magang MBKM
                        </p>
                    </div>

                    <form action="{{ route('student.final_report.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                        @csrf

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Judul Naskah Laporan Akhir Magang
                            </label>
                            <input type="text" name="title" value="{{ old('title', $finalReport->title ?? 'Laporan Akhir Praktik Kerja Lapangan (PKL) / Magang MBKM') }}" placeholder="Contoh: Rancang Bangun Sistem Monitoring Layanan Publik..." class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Tautan Repositori Proyek / Luaran Kerja (Opsional)
                            </label>
                            <input type="url" name="repository_url" value="{{ old('repository_url', $finalReport->repository_url ?? '') }}" placeholder="https://github.com/username/project atau link Google Drive" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Unggah File Laporan (PDF / DOCX) <span class="text-rose-500">*</span>
                            </label>
                            <input type="file" name="file_laporan" accept=".pdf,.doc,.docx" required class="block w-full text-xs text-gray-900 border border-gray-300 rounded-xl cursor-pointer bg-slate-50 focus:outline-none file:mr-4 file:py-2.5 file:px-4 file:rounded-l-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-[11px] text-gray-400 mt-1">Ukuran maksimal file: 10 MB. Format yang didukung: PDF, DOC, DOCX.</p>
                        </div>

                        <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-3">
                            <a href="{{ route('dashboard') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                                Batal
                            </a>
                            <button type="submit" class="px-7 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                                {{ $finalReport ? 'Unggah Ulang Naskah Revisi' : 'Kirim Laporan Akhir' }}
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="p-6 bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 rounded-3xl text-center space-y-2 shadow-xs">
                    <span class="text-3xl">🎉</span>
                    <h3 class="font-black text-base text-emerald-900">Laporan Akhir Anda Telah Disetujui Secara Resmi</h3>
                    <p class="text-xs text-emerald-700 max-w-xl mx-auto">
                        Naskah laporan akhir Anda telah di-ACC oleh DPL dan pembimbing lapangan. Nilai kelulusan dan sertifikat resmi dapat dilihat di Dashboard.
                    </p>
                </div>
            @endif
>>>>>>> main

        </div>
    </div>
</x-app-layout>
