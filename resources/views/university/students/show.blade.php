<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3">
            <!-- Baris 1: Judul Halaman -->
            <div>
                <h2 class="font-bold text-lg sm:text-2xl text-slate-800 leading-tight">
                    {{ __('Detail Monitoring Mahasiswa') }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5 truncate">
                    {{ $student->name }} &bull; <span class="font-mono">{{ $profile?->nim ?? '-' }}</span>
                </p>
            </div>

            <!-- Baris 2: Tombol Aksi (Mobile: Full Width Stack, Desktop: Baris Kanan) -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:self-end">
                @if (in_array(strtoupper($application->lifecycle_status ?? $application->status), ['ACCEPTED', 'ACTIVE', 'COMPLETED', 'VERIFIED']))
                    <a href="{{ route('university.students.letter', $application->id) }}" target="_blank"
                       class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow-xs transition">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Cetak Surat Tugas (PDF)</span>
                    </a>
                @endif

                <a href="{{ route('university.dashboard') }}" 
                   class="inline-flex items-center justify-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition text-center">
                    &larr; Kembali ke Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8 bg-[#F5F8FC] min-h-screen text-slate-900 font-sans overflow-x-hidden" x-data="{ showAssignModal: false }">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Alerts -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-2xl shadow-xs flex items-center justify-between text-emerald-900 text-xs sm:text-sm font-medium">
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-2xl shadow-xs text-rose-900 text-xs sm:text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <!-- 1. Header Ringkasan Profil & Penempatan -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
                <!-- Card Mahasiswa -->
                <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 border border-slate-100 shadow-xs space-y-3">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                        <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm border border-blue-100 shrink-0">
                            {{ substr($student->name, 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-bold text-sm text-slate-900 truncate leading-tight">{{ $student->name }}</h3>
                            <p class="text-[11px] font-mono text-slate-400 mt-0.5">NIM: {{ $profile?->nim ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Prodi:</span>
                            <span class="font-semibold text-slate-700">{{ $profile?->jurusan ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Fakultas:</span>
                            <span class="font-semibold text-slate-700">{{ $profile?->fakultas ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">WhatsApp:</span>
                            <span class="font-mono text-slate-700">{{ $profile?->no_hp ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Card Instansi Penempatan -->
                <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 border border-slate-100 shadow-xs space-y-3">
                    <div class="border-b border-slate-100 pb-3">
                        <h3 class="font-bold text-sm text-slate-900 leading-tight">Penempatan Magang</h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Instansi Pemkot Surabaya</p>
                    </div>

                    <div class="space-y-1.5 text-xs">
                        <div class="flex justify-between items-start gap-2">
                            <span class="text-slate-400 shrink-0">Dinas:</span>
                            <span class="font-bold text-blue-900 text-right truncate">{{ $agencyProfile?->agency_name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-start gap-2">
                            <span class="text-slate-400 shrink-0">Unit:</span>
                            <span class="font-semibold text-slate-700 text-right truncate">{{ $unit?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-1">
                            <span class="text-slate-400">Status:</span>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase">
                                {{ $application->lifecycle_status ?? $application->status }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Card Pembimbing & Plotting DPL -->
                <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 border border-slate-100 shadow-xs space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="font-bold text-sm text-slate-900 leading-tight">Pembimbing</h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">DPL Kampus & Mentor Dinas</p>
                        </div>
                        <button type="button" 
                                @click="showAssignModal = true" 
                                class="px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-lg transition shrink-0">
                            {{ $dosen ? 'Ganti' : '+ DPL' }}
                        </button>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div>
                            <span class="text-slate-400 block text-[10px]">Dosen Pembimbing (DPL):</span>
                            @if ($dosen)
                                <div class="font-bold text-slate-800 truncate mt-0.5">{{ $dosen->name }}</div>
                            @else
                                <span class="inline-block mt-0.5 text-amber-700 bg-amber-50 px-2 py-0.5 rounded text-[10px] font-bold">
                                    Belum Ditentukan
                                </span>
                            @endif
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">Mentor Dinas:</span>
                            <div class="font-bold text-slate-800 truncate mt-0.5">{{ $mentor->name ?? 'Belum Diplot' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Ringkasan Nilai & Evaluasi Akademik (Mobile 2 Kolom) -->
            <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 border border-slate-100 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h4 class="font-bold text-slate-900 text-sm sm:text-base">Rekapitulasi Nilai Magang</h4>
                    <p class="text-[11px] sm:text-xs text-slate-400 mt-0.5">Evaluasi dari mentor dinas dan dosen pembimbing lapangan</p>
                </div>

                @php
                    $evalObj = $student->placement?->evaluation ?? $evaluation ?? null;
                    $mentorVal = $evalObj?->nilai_pembimbing;
                    $dosenVal = $evalObj?->nilai_dosen_calculated ?? ($evalObj?->nilai_akademik);
                    $finalVal = $evalObj?->nilai_akhir;
                    $gradeLetter = $evalObj?->grade_calculated ?? ($evalObj?->grade ?? '-');
                @endphp

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-center">
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Nilai Mentor</p>
                        <p class="text-xl sm:text-2xl font-black text-slate-800 mt-1">{{ $mentorVal ? number_format($mentorVal, 2) : '-' }}</p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-center">
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Nilai Dosen</p>
                        <p class="text-xl sm:text-2xl font-black text-blue-600 mt-1">{{ $dosenVal ? number_format($dosenVal, 2) : '-' }}</p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-blue-50/70 border border-blue-100 text-center">
                        <p class="text-[10px] text-blue-900 font-bold uppercase tracking-wider">Nilai Akhir</p>
                        <p class="text-xl sm:text-2xl font-black text-blue-700 mt-1">{{ $finalVal !== null && $finalVal > 0 ? number_format($finalVal, 2) : '-' }}</p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-emerald-50/70 border border-emerald-100 text-center">
                        <p class="text-[10px] text-emerald-900 font-bold uppercase tracking-wider">Predikat Huruf</p>
                        <p class="text-xl sm:text-2xl font-black text-emerald-700 mt-1">{{ $gradeLetter }}</p>
                    </div>
                </div>
            </div>

            <!-- 3. Laporan Akhir Magang -->
            <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 border border-slate-100 shadow-xs space-y-3">
                <div class="border-b border-slate-100 pb-3">
                    <h4 class="font-bold text-slate-900 text-sm sm:text-base">Laporan Akhir Magang</h4>
                    <p class="text-[11px] sm:text-xs text-slate-400 mt-0.5">Dokumen laporan tugas magang mahasiswa</p>
                </div>

                @if ($finalReport && $finalReport->file_path)
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                        <div class="min-w-0">
                            <h5 class="font-bold text-xs sm:text-sm text-slate-900 truncate">{{ $finalReport->title ?? 'Laporan Akhir Magang' }}</h5>
                            <p class="text-[11px] text-slate-400 mt-0.5">Status: <span class="font-bold uppercase text-emerald-700">{{ $finalReport->status ?? 'Submitted' }}</span></p>
                        </div>
                        <a href="{{ asset('storage/' . $finalReport->file_path) }}" target="_blank" class="w-full sm:w-auto text-center px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition">
                            Unduh Laporan (PDF)
                        </a>
                    </div>
                @else
                    <div class="p-4 text-center text-slate-400 bg-slate-50 rounded-xl text-xs">
                        Mahasiswa belum mengunggah laporan akhir magang.
                    </div>
                @endif
            </div>

            <!-- 4. Riwayat Logbook Harian Mahasiswa -->
            <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-100 shadow-xs overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-slate-100">
                    <h4 class="font-bold text-slate-900 text-sm sm:text-base">
                        Riwayat Logbook Aktivitas Harian ({{ $logbooks->count() }})
                    </h4>
                    <p class="text-[11px] sm:text-xs text-slate-400 mt-0.5">
                        Catatan kegiatan harian beserta review mentor dan dosen
                    </p>
                </div>

                <!-- TAMPILAN MOBILE: Card Stack Logbook (Anti Melebar / Tanpa Scroll Horizontal) -->
                <div class="block sm:hidden divide-y divide-slate-100">
                    @forelse ($logbooks as $idx => $log)
                        @php
                            $mSt = strtolower($log->status ?? 'pending');
                            $lSt = strtolower($log->lecturer_status ?? 'pending');
                        @endphp
                        <div class="p-4 space-y-2.5">
                            <!-- Tanggal & No Log -->
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-800">
                                    {{ date('d M Y', strtotime($log->date)) }}
                                    <span class="text-[11px] text-slate-400 font-normal">({{ date('l', strtotime($log->date)) }})</span>
                                </span>
                                <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">
                                    #{{ $idx + 1 }}
                                </span>
                            </div>

                            <!-- Isi Aktivitas -->
                            <div class="text-xs text-slate-700 leading-relaxed bg-slate-50/70 p-3 rounded-xl border border-slate-100 whitespace-pre-line">
                                {{ $log->activity }}
                            </div>

                            <!-- Feedback jika ada -->
                            @if ($log->mentor_feedback)
                                <div class="text-[11px] bg-amber-50/80 border border-amber-200/70 p-2.5 rounded-xl text-amber-900 space-y-0.5">
                                    <strong class="block text-[10px] uppercase tracking-wider text-amber-700">Catatan Mentor:</strong>
                                    <p class="italic">"{{ $log->mentor_feedback }}"</p>
                                </div>
                            @endif

                            @if ($log->lecturer_feedback)
                                <div class="text-[11px] bg-blue-50/80 border border-blue-200/70 p-2.5 rounded-xl text-blue-900 space-y-0.5">
                                    <strong class="block text-[10px] uppercase tracking-wider text-blue-700">Catatan Dosen:</strong>
                                    <p class="italic">"{{ $log->lecturer_feedback }}"</p>
                                </div>
                            @endif

                            <!-- Status Mentor & Dosen dalam 1 Baris Grid -->
                            <div class="grid grid-cols-2 gap-2 pt-1">
                                <div class="p-2 rounded-lg bg-slate-50 border border-slate-100 text-center">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Mentor Dinas</span>
                                    <span class="inline-block mt-0.5 text-[10px] font-bold px-2 py-0.5 rounded-full border
                                        {{ $mSt === 'approved' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}
                                        {{ $mSt === 'rejected' ? 'bg-rose-50 text-rose-700 border-rose-200' : '' }}
                                        {{ $mSt === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}">
                                        {{ strtoupper($log->status ?? 'PENDING') }}
                                    </span>
                                </div>

                                <div class="p-2 rounded-lg bg-slate-50 border border-slate-100 text-center">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Dosen DPL</span>
                                    <span class="inline-block mt-0.5 text-[10px] font-bold px-2 py-0.5 rounded-full border
                                        {{ $lSt === 'approved' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}
                                        {{ $lSt === 'rejected' ? 'bg-rose-50 text-rose-700 border-rose-200' : '' }}
                                        {{ $lSt === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}">
                                        {{ strtoupper($log->lecturer_status ?? 'PENDING') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400 text-xs">
                            Belum ada catatan logbook yang diisi oleh mahasiswa ini.
                        </div>
                    @endforelse
                </div>

                <!-- TAMPILAN DESKTOP: Tabel Standar -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase text-[11px] font-bold tracking-wider">
                            <tr>
                                <th class="px-6 py-3.5 w-12 text-center">No</th>
                                <th class="px-6 py-3.5 w-32">Tanggal</th>
                                <th class="px-6 py-3.5">Deskripsi Aktivitas</th>
                                <th class="px-6 py-3.5 text-center w-36">Status Mentor</th>
                                <th class="px-6 py-3.5 text-center w-36">Status Dosen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($logbooks as $idx => $log)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-6 py-4 text-center font-bold text-slate-400 text-xs">
                                        {{ $idx + 1 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-800">{{ date('d M Y', strtotime($log->date)) }}</div>
                                        <div class="text-[11px] text-slate-400">{{ date('l', strtotime($log->date)) }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-slate-800 leading-relaxed">{{ $log->activity }}</div>
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
                                    <td colspan="5" class="px-6 py-8 text-center text-slate-400 text-xs">
                                        Belum ada catatan logbook yang diisi oleh mahasiswa ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- MODAL: TUGASKAN / GANTI DOSEN PEMBIMBING -->
        <div x-show="showAssignModal" 
             x-cloak 
             style="display: none;"
             class="fixed inset-0 z-[9999] overflow-y-auto p-4 sm:p-6 flex items-center justify-center bg-slate-900/70 backdrop-blur-xs transition-opacity"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 border border-slate-100 relative my-auto max-h-[90vh] overflow-y-auto"
                 @click.outside="showAssignModal = false">
                
                <div class="flex justify-between items-center border-b border-slate-100 pb-3 mb-4">
                    <div>
                        <h3 class="font-bold text-base text-slate-900">Plotting Dosen Pembimbing Lapangan</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Untuk Mahasiswa: <strong class="text-slate-700">{{ $student->name }}</strong></p>
                    </div>
                    <button type="button" @click="showAssignModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
                </div>

                <form method="POST" action="{{ route('university.students.assign_advisor', $application->id) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Pilih Dosen Pembimbing (DPL) <span class="text-rose-500">*</span>
                        </label>
                        <select name="academic_advisor_id" required class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                            <option value="">-- Pilih Dosen Pembimbing Kampus --</option>
                            @foreach ($availableDosens as $d)
                                <option value="{{ $d->id }}" {{ $dosen?->id == $d->id ? 'selected' : '' }}>
                                    {{ $d->name }} ({{ $d->email }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Daftar memuat seluruh dosen yang terdaftar di kampus Anda.</p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="showAssignModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer">
                            Simpan Penugasan DPL
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>