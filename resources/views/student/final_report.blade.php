<x-app-layout>
    <x-slot name="header">
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

        </div>
    </div>
</x-app-layout>
