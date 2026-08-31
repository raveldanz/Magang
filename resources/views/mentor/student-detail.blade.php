<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('mentor.dashboard') }}" class="p-2 bg-white hover:bg-gray-100 border border-gray-200 text-gray-700 rounded-xl transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                        {{ $placement->application?->user?->name ?? '-' }}
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                        {{ $placement->application?->user?->studentProfile?->nim ?? '-' }} &bull; {{ $placement->application?->user?->studentProfile?->universitas ?? '-' }} ({{ $placement->application?->user?->studentProfile?->jurusan ?? '-' }})
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('mentor.evaluations.create', $placement->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    {{ $placement->evaluation ? 'Edit Penilaian Akhir' : 'Input Penilaian Akhir' }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Success Message -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-lg shadow-sm flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Grid Kartu Status: Penilaian & Laporan Akhir -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- 1. Kartu Status Evaluasi Nilai -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between border-b pb-3 mb-4">
                            <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                </svg>
                                Status Penilaian & E-Sertifikat
                            </h3>
                            @if ($placement->evaluation)
                                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-full">
                                    Sudah Dinilai
                                </span>
                            @else
                                <span class="px-2.5 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-full">
                                    Belum Dinilai
                                </span>
                            @endif
                        </div>

                        @if ($placement->evaluation)
                            @php
                                $eval = $placement->evaluation;
                                $rataRata = round((($eval?->nilai_disiplin ?? 0) + ($eval?->nilai_kinerja ?? 0) + ($eval?->nilai_laporan ?? 0)) / 3, 2);
                                $grade = 'C';
                                if ($rataRata >= 85) $grade = 'A (Sangat Memuaskan)';
                                elseif ($rataRata >= 70) $grade = 'B (Memuaskan)';
                            @endphp
                            <div class="grid grid-cols-3 gap-3 mb-4 text-center">
                                <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                                    <span class="text-[11px] font-bold text-gray-500 uppercase">Disiplin</span>
                                    <p class="text-xl font-black text-gray-800 mt-1">{{ $eval?->nilai_disiplin ?? '-' }}</p>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                                    <span class="text-[11px] font-bold text-gray-500 uppercase">Kinerja</span>
                                    <p class="text-xl font-black text-gray-800 mt-1">{{ $eval?->nilai_kinerja ?? '-' }}</p>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                                    <span class="text-[11px] font-bold text-gray-500 uppercase">Laporan</span>
                                    <p class="text-xl font-black text-gray-800 mt-1">{{ $eval?->nilai_laporan ?? '-' }}</p>
                                </div>
                            </div>

                            <div class="bg-blue-50/75 p-3.5 rounded-xl border border-blue-100 space-y-1 mb-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-semibold text-blue-900">Nilai Akhir Rata-Rata:</span>
                                    <span class="text-lg font-black text-blue-700">{{ $rataRata }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-semibold text-blue-900">Predikat Kelulusan:</span>
                                    <span class="text-xs font-extrabold text-blue-800">{{ $grade }}</span>
                                </div>
                                @if ($eval?->catatan)
                                    <div class="pt-2 border-t border-blue-100 text-xs text-blue-950">
                                        <strong>Catatan:</strong> <em>"{{ $eval->catatan }}"</em>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="p-4 bg-gray-50 rounded-xl text-center text-gray-500 text-sm mb-4">
                                <p>Mahasiswa belum memiliki evaluasi nilai dari Pembimbing Lapangan.</p>
                                <p class="text-xs text-gray-400 mt-1">Silakan klik tombol di bawah untuk memasukkan nilai evaluasi akhir.</p>
                            </div>
                        @endif
                    </div>

                    <a href="{{ route('mentor.evaluations.create', $placement->id) }}" class="w-full text-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-sm cursor-pointer">
                        {{ $placement->evaluation ? 'Ubah Nilai Evaluasi' : 'Isi Form Penilaian Evaluasi' }}
                    </a>
                </div>

                <!-- 2. Kartu Status Laporan Akhir -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between border-b pb-3 mb-4">
                            <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Laporan Akhir Magang
                            </h3>
                            @if ($placement->finalreport)
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full 
                                    {{ $placement->finalreport->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                    {{ $placement->finalreport->status === 'revision' ? 'bg-rose-100 text-rose-800' : '' }}
                                    {{ $placement->finalreport->status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}">
                                    {{ strtoupper($placement->finalreport->status) }}
                                </span>
                            @else
                                <span class="px-2.5 py-1 bg-gray-100 text-gray-500 text-xs font-medium rounded-full">
                                    Belum Diunggah
                                </span>
                            @endif
                        </div>

                        @if ($placement->finalreport && $placement->finalreport->file_path)
                            <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-100 space-y-2 mb-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-600 font-medium">Berkas Dokumen Laporan:</span>
                                    <a href="{{ asset('storage/' . $placement->finalreport->file_path) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 font-bold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Buka PDF Laporan
                                    </a>
                                </div>
                                @if ($placement->finalreport->feedback)
                                    <p class="text-xs text-gray-500 border-t pt-2 mt-2">
                                        <strong>Feedback Pembimbing:</strong> "{{ $placement->finalreport->feedback }}"
                                    </p>
                                @endif
                            </div>

                            <!-- Form Review Laporan Akhir -->
                            <form action="{{ route('mentor.final_report.updateStatus', $placement->finalreport->id) }}" method="POST" class="space-y-3 pt-2">
                                @csrf
                                @method('PUT')
                                <input type="text" name="feedback" value="{{ $placement->finalreport->feedback }}" placeholder="Catatan/revisi untuk mahasiswa..." class="w-full text-xs border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                                <div class="flex gap-2">
                                    <button type="submit" name="status" value="approved" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition cursor-pointer">
                                        Setujui Laporan
                                    </button>
                                    <button type="submit" name="status" value="revision" class="w-full py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-sm transition cursor-pointer">
                                        Minta Revisi
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="p-6 bg-gray-50 rounded-xl text-center text-gray-500 text-sm">
                                <p>Mahasiswa belum mengunggah berkas laporan akhir magang.</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Daftar Aktivitas Logbook Harian -->
            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-200 p-6 space-y-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b pb-4 gap-2">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Riwayat & Verifikasi Logbook Harian</h3>
                        <p class="text-xs text-gray-500">Tinjau deskripsi aktivitas, lampiran bukti, dan berikan status verifikasi beserta feedback</p>
                    </div>
                    <span class="px-3 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-full">
                        Total: {{ $placement->logbooks->count() }} Kegiatan
                    </span>
                </div>

                <div class="space-y-4 pt-2">
                    @forelse ($placement->logbooks as $log)
                        <div class="border border-gray-200 p-4 sm:p-5 rounded-2xl bg-slate-50/50 space-y-3 hover:border-blue-200 transition">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="p-1.5 bg-blue-100 text-blue-700 rounded-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </span>
                                    <span class="font-bold text-gray-800 text-sm">
                                        Tanggal: {{ \Carbon\Carbon::parse($log->date)->translatedFormat('l, d F Y') }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1 text-xs font-bold rounded-full 
                                        {{ $log->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                        {{ $log->status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}
                                        {{ $log->status === 'rejected' ? 'bg-rose-100 text-rose-800' : '' }}">
                                        {{ strtoupper($log->status) }}
                                    </span>
                                    <a href="{{ route('mentor.logbooks.show', $log->id) }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">
                                        Detail &rarr;
                                    </a>
                                </div>
                            </div>

                            <div class="text-sm text-gray-700 leading-relaxed bg-white p-3.5 rounded-xl border border-gray-100">
                                {{ $log->activity }}
                            </div>

                            @if ($log->attachment)
                                <div class="pt-1">
                                    <a href="{{ asset('storage/' . $log->attachment) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-blue-600 hover:text-blue-800 font-bold bg-blue-50 px-3 py-1.5 rounded-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                        </svg>
                                        Buka Lampiran Bukti Kegiatan
                                    </a>
                                </div>
                            @endif

                            <!-- Form Action Verifikasi (Approve / Reject) -->
                            <form action="{{ route('mentor.logbooks.updateStatus', $log->id) }}" method="POST" class="pt-3 border-t border-gray-200 grid grid-cols-1 sm:grid-cols-4 gap-3 items-center">
                                @csrf
                                @method('PUT')
                                
                                <div class="sm:col-span-3">
                                    <input type="text" name="feedback" value="{{ $log->feedback }}" placeholder="Tambahkan catatan/feedback pembimbing..." class="w-full text-xs border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div class="flex gap-2">
                                    <button type="submit" name="status" value="approved" class="w-full px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-sm">
                                        Approve
                                    </button>
                                    <button type="submit" name="status" value="rejected" class="w-full px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition shadow-sm">
                                        Reject
                                    </button>
                                </div>
                            </form>
                        </div>
                    @empty
                        <div class="py-8 text-center text-gray-400">
                            <p class="text-sm">Mahasiswa belum menginputkan logbook kegiatan.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
