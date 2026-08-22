<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    {{ __('Detail Monitoring Mahasiswa Magang') }}
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    🏛️ Pemantauan Aktivitas, Logbook, DPL, dan Nilai Mahasiswa &bull; <strong>{{ $student->name }}</strong>
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('university.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition shadow-2xs">
                    ← Kembali ke Dashboard
                </a>
                @if (in_array(strtoupper($application->lifecycle_status ?? $application->status), ['ACCEPTED', 'ACTIVE', 'COMPLETED', 'VERIFIED']))
                    <a href="{{ route('university.students.letter', $application->id) }}" target="_blank"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-sm font-semibold shadow-sm transition active:scale-95 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Cetak Surat Tugas (PDF)</span>
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ showAssignModal: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Success Message -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-xs text-rose-900 text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <!-- 1. Header Ringkasan Profil & Penempatan -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Card Mahasiswa -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-xs space-y-4">
                    <div class="flex items-center gap-3 border-b border-gray-100 pb-3">
                        <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg border border-blue-100 shrink-0">
                            {{ substr($student->name, 0, 1) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-gray-900 leading-tight">{{ $student->name }}</h3>
                            <p class="text-xs font-mono text-gray-500">NIM: {{ $profile?->nim ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Program Studi:</span>
                            <span class="font-semibold text-gray-800">{{ $profile?->jurusan ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Fakultas:</span>
                            <span class="font-semibold text-gray-800">{{ $profile?->fakultas ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Email:</span>
                            <span class="font-mono text-gray-700">{{ $student->email }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">No. WhatsApp:</span>
                            <span class="font-mono text-gray-700">{{ $profile?->no_hp ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Card Instansi Penempatan -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-xs space-y-4">
                    <div class="flex items-center gap-2 border-b border-gray-100 pb-3">
                        <span class="text-xl">🏛️</span>
                        <div>
                            <h3 class="font-bold text-base text-gray-900 leading-tight">Penempatan Magang</h3>
                            <p class="text-xs text-gray-400">Instansi Pemerintah Kota Surabaya</p>
                        </div>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between items-start">
                            <span class="text-gray-400">Dinas:</span>
                            <span class="font-bold text-blue-900 text-right max-w-[180px]">{{ $agencyProfile?->agency_name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-start">
                            <span class="text-gray-400">Unit Kerja:</span>
                            <span class="font-semibold text-gray-800 text-right max-w-[180px]">{{ $unit?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Periode:</span>
                            <span class="font-semibold text-gray-700">
                                @if ($application->start_date && $application->end_date)
                                    {{ date('d/m/Y', strtotime($application->start_date)) }} - {{ date('d/m/Y', strtotime($application->end_date)) }}
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between items-center pt-1">
                            <span class="text-gray-400">Status Magang:</span>
                            <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800 uppercase">
                                {{ $application->lifecycle_status ?? $application->status }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Card Pembimbing & Plotting DPL -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">👨‍🏫</span>
                            <div>
                                <h3 class="font-bold text-base text-gray-900 leading-tight">Pembimbing</h3>
                                <p class="text-xs text-gray-400">DPL Kampus & Mentor Dinas</p>
                            </div>
                        </div>

                        <!-- Tombol Plotting DPL -->
                        <button type="button" 
                                @click="showAssignModal = true" 
                                class="px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-xs font-bold rounded-lg transition shadow-2xs cursor-pointer">
                            {{ $dosen ? 'Ganti DPL' : 'Tugaskan DPL' }}
                        </button>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-gray-400 block text-[11px]">Dosen Pembimbing Lapangan (DPL):</span>
                            @if ($dosen)
                                <div class="font-bold text-gray-900 mt-0.5 flex items-center gap-1.5">
                                    <span>👨‍🏫 {{ $dosen->name }}</span>
                                </div>
                                <span class="font-mono text-[11px] text-gray-500">{{ $dosen->email }}</span>
                            @else
                                <span class="inline-block mt-0.5 text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md font-semibold text-xs">
                                    ⚠️ Belum Ditentukan
                                </span>
                            @endif
                        </div>

                        <div class="border-t border-gray-50 pt-2">
                            <span class="text-gray-400 block text-[11px]">Mentor Lapangan Dinas:</span>
                            @if ($mentor)
                                <div class="font-bold text-gray-900 mt-0.5">👔 {{ $mentor->name }}</div>
                                <span class="font-mono text-[11px] text-gray-500">{{ $mentor->email }}</span>
                            @else
                                <span class="text-gray-400">Belum Diplot oleh Dinas</span>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

            <!-- 2. Ringkasan Nilai & Evaluasi Akademik -->
            <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-xs space-y-4">
                <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                    <div>
                        <h4 class="font-bold text-gray-900 text-base flex items-center gap-2">
                            <span>📊 Rekapitulasi Nilai Magang</span>
                        </h4>
                        <p class="text-xs text-gray-500 mt-0.5">Evaluasi nilai praktis dari mentor dinas dan nilai akademik dari dosen pembimbing</p>
                    </div>
                </div>

                @php
                    $mentorVal = $student->placement?->evaluation?->nilai_pembimbing ?? $evaluation?->nilai_pembimbing;
                    $dosenVal = $student->placement?->evaluation?->nilai_akademik ?? $evaluation?->nilai_akademik;
                    $finalVal = null;
                    if ($mentorVal > 0 && $dosenVal > 0) {
                        $finalVal = ($mentorVal * 0.4) + ($dosenVal * 0.6);
                    } elseif ($dosenVal > 0) {
                        $finalVal = $dosenVal;
                    }

                    $gradeLetter = '-';
                    if ($finalVal !== null) {
                        if ($finalVal >= 85) $gradeLetter = 'A';
                        elseif ($finalVal >= 80) $gradeLetter = 'A-';
                        elseif ($finalVal >= 75) $gradeLetter = 'B+';
                        elseif ($finalVal >= 70) $gradeLetter = 'B';
                        elseif ($finalVal >= 65) $gradeLetter = 'B-';
                        elseif ($finalVal >= 60) $gradeLetter = 'C+';
                        elseif ($finalVal >= 55) $gradeLetter = 'C';
                        else $gradeLetter = 'D';
                    }
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="p-4 rounded-xl bg-slate-50 border border-gray-100 text-center">
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">Nilai Mentor Dinas (40%)</p>
                        <p class="text-2xl font-black text-gray-800 mt-1">
                            {{ $mentorVal ? number_format($mentorVal, 2) : ($student->placement?->evaluation?->nilai_pembimbing ?? '-') }}
                        </p>
                        <p class="text-[10px] text-gray-400 mt-0.5">Kedisiplinan & Kinerja Lapangan</p>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-gray-100 text-center">
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">Nilai Dosen DPL (60%)</p>
                        <p class="text-2xl font-black text-blue-600 mt-1">
                            {{ $dosenVal ? number_format($dosenVal, 2) : ($student->placement?->evaluation?->nilai_dosen ?? '-') }}
                        </p>
                        <p class="text-[10px] text-gray-400 mt-0.5">Logbook & Laporan Akhir</p>
                    </div>

                    <div class="p-4 rounded-xl bg-blue-50/70 border border-blue-100 text-center">
                        <p class="text-xs text-blue-900 font-bold uppercase tracking-wider">Nilai Akhir Terbobot</p>
                        <p class="text-2xl font-black text-blue-700 mt-1">
                            {{ $finalVal !== null ? number_format($finalVal, 2) : ($student->placement?->evaluation?->final_score ?? '-') }}
                        </p>
                        <p class="text-[10px] text-blue-600 mt-0.5">Akumulasi Gabungan</p>
                    </div>

                    <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-100 text-center">
                        <p class="text-xs text-emerald-900 font-bold uppercase tracking-wider">Predikat Huruf</p>
                        <p class="text-2xl font-black text-emerald-700 mt-1">
                            {{ $gradeLetter }}
                        </p>
                        <p class="text-[10px] text-emerald-600 mt-0.5">Konversi Nilai SIAKAD</p>
                    </div>
                </div>
            </div>

            <!-- 3. Laporan Akhir & Dokumen Magang -->
            <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-xs space-y-4">
                <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                    <div>
                        <h4 class="font-bold text-gray-900 text-base flex items-center gap-2">
                            <span>📑 Laporan Akhir Magang</span>
                        </h4>
                        <p class="text-xs text-gray-500 mt-0.5">Dokumen laporan karya magang mahasiswa yang telah diverifikasi</p>
                    </div>
                </div>

                @if ($finalReport && $finalReport->file_path)
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-gray-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center text-xl font-bold">
                                📄
                            </div>
                            <div>
                                <h5 class="font-bold text-sm text-gray-900">{{ $finalReport->title ?? 'Laporan Akhir Praktik Kerja Lapangan' }}</h5>
                                <p class="text-xs text-gray-500">Status: <span class="font-semibold uppercase text-emerald-700">{{ $finalReport->status ?? 'Submitted' }}</span></p>
                            </div>
                        </div>

                        <a href="{{ asset('storage/' . $finalReport->file_path) }}" target="_blank" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                            Unduh Laporan (PDF)
                        </a>
                    </div>
                @else
                    <div class="p-6 text-center text-gray-400 bg-slate-50 rounded-xl border border-dashed border-gray-200 text-xs">
                        Mahasiswa belum mengunggah dokumen laporan akhir magang.
                    </div>
                @endif
            </div>

            <!-- 4. Riwayat Logbook Harian Mahasiswa (Read-Only) -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <div>
                        <h4 class="font-bold text-gray-900 text-base">Riwayat Logbook Aktivitas Harian ({{ $logbooks->count() }})</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Catatan kegiatan harian beserta catatan feedback dari pembimbing</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 uppercase text-[11px] font-bold tracking-wider">
                            <tr>
                                <th class="px-6 py-3.5 w-12 text-center">No</th>
                                <th class="px-6 py-3.5 w-32">Tanggal</th>
                                <th class="px-6 py-3.5">Deskripsi Aktivitas</th>
                                <th class="px-6 py-3.5 text-center w-36">Status Mentor</th>
                                <th class="px-6 py-3.5 text-center w-36">Status Dosen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($logbooks as $idx => $log)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-6 py-4 text-center font-bold text-gray-400 text-xs">
                                        {{ $idx + 1 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-bold text-gray-800">{{ date('d M Y', strtotime($log->date)) }}</div>
                                        <div class="text-[11px] text-gray-400">{{ date('l', strtotime($log->date)) }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-gray-800 leading-relaxed">{{ $log->activity }}</div>
                                        
                                        @if ($log->mentor_feedback)
                                            <div class="mt-2 text-xs bg-amber-50 border border-amber-200 rounded-lg p-2 text-amber-900">
                                                <span class="font-bold">Feedback Mentor:</span> {{ $log->mentor_feedback }}
                                            </div>
                                        @endif

                                        @if ($log->lecturer_feedback)
                                            <div class="mt-1 text-xs bg-blue-50 border border-blue-200 rounded-lg p-2 text-blue-900">
                                                <span class="font-bold">Feedback Dosen:</span> {{ $log->lecturer_feedback }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full 
                                            {{ $log->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                            {{ $log->status === 'rejected' ? 'bg-rose-100 text-rose-800' : '' }}
                                            {{ $log->status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}">
                                            {{ strtoupper($log->status ?? 'PENDING') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full 
                                            {{ $log->lecturer_status === 'approved' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                            {{ $log->lecturer_status === 'rejected' ? 'bg-rose-100 text-rose-800' : '' }}
                                            {{ $log->lecturer_status === 'pending' || !$log->lecturer_status ? 'bg-amber-100 text-amber-800' : '' }}">
                                            {{ strtoupper($log->lecturer_status ?? 'PENDING') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-400 text-xs">
                                        Belum ada catatan logbook yang diisi oleh mahasiswa ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- ========================================== -->
        <!-- MODAL: TUGASKAN / GANTI DOSEN PEMBIMBING -->
        <!-- ========================================== -->
        <div x-show="showAssignModal" 
             x-cloak 
             class="fixed inset-0 z-[9999] overflow-y-auto p-4 sm:p-6 flex items-center justify-center bg-slate-900/70 backdrop-blur-sm transition-opacity"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 sm:p-7 border border-slate-100 relative my-auto max-h-[90vh] overflow-y-auto"
                 @click.away="showAssignModal = false"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <div class="flex justify-between items-center border-b border-gray-100 pb-3 mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base">
                            👨‍🏫
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-gray-900">Plotting Dosen Pembimbing Lapangan</h3>
                            <p class="text-xs text-gray-400">Untuk Mahasiswa: <strong>{{ $student->name }}</strong></p>
                        </div>
                    </div>
                    <button type="button" @click="showAssignModal = false" class="text-gray-400 hover:text-gray-600 text-lg font-bold">✕</button>
                </div>

                <form method="POST" action="{{ route('university.students.assign_advisor', $application->id) }}" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Pilih Dosen Pembimbing (DPL) <span class="text-rose-500">*</span>
                        </label>
                        <select name="academic_advisor_id" required class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                            <option value="">-- Pilih Dosen Pembimbing Kampus --</option>
                            @foreach ($availableDosens as $d)
                                <option value="{{ $d->id }}" {{ $dosen?->id == $d->id ? 'selected' : '' }}>
                                    👨‍🏫 {{ $d->name }} ({{ $d->email }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-gray-400 mt-1">Daftar memuat seluruh dosen yang terdaftar di {{ $university?->name ?? 'kampus Anda' }}.</p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="showAssignModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                            Simpan Penugasan DPL
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
