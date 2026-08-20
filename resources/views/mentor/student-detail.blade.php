<x-app-layout>
    <div class="py-8 bg-[#F5F8FC] min-h-screen text-slate-900 font-sans">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-3">
                    <a href="{{ route('mentor.dashboard') }}" class="p-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-600 rounded-xl transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    </a>
                    <div>
                        <h2 class="text-xl font-bold tracking-tight text-slate-900">
                            Detail Mahasiswa Bimbingan
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Monitoring logbook harian, status laporan akhir, dan evaluasi mahasiswa
                        </p>
                    </div>
                </div>

                <a href="{{ route('mentor.evaluations.create', $placement->id) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-semibold uppercase tracking-wider rounded-xl shadow-sm shadow-blue-200 transition-all duration-200 hover:scale-[1.01]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    <span>{{ $placement->evaluation ? 'Edit Evaluasi Nilai' : 'Input Nilai Akhir' }}</span>
                </a>
            </div>

            <!-- Flash Alert -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-emerald-800 text-sm font-semibold shadow-sm">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Info Cards Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <!-- Biodata Card -->
                <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-100 shadow-sm shadow-slate-200/50 space-y-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 font-bold text-base flex items-center justify-center shrink-0">
                            {{ strtoupper(substr($placement->application->user->name ?? 'M', 0, 2)) }}
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">{{ $placement->application->user->name ?? '-' }}</h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $placement->application->user->studentProfile->universitas ?? '-' }} &bull; Jurusan {{ $placement->application->user->studentProfile->jurusan ?? '-' }}
                            </p>
                            <span class="inline-block mt-1 px-2.5 py-0.5 bg-slate-50 border border-slate-200 text-slate-700 text-[11px] font-semibold rounded-full">
                                NIM: {{ $placement->application->user->studentProfile->nim ?? '-' }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-3 border-t border-slate-100 text-xs">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-slate-400 block text-[11px]">Unit Penempatan:</span>
                            <span class="font-bold text-slate-800">{{ $placement->application->unit->name ?? '-' }}</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-slate-400 block text-[11px]">Periode Magang:</span>
                            <span class="font-bold text-slate-800">
                                {{ \Carbon\Carbon::parse($placement->application->start_date)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($placement->application->end_date)->translatedFormat('d M Y') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Status Evaluasi & Laporan -->
                <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm shadow-slate-200/50 flex flex-col justify-between space-y-4">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 block mb-2">Status Nilai Akhir</span>
                        @if ($placement->evaluation)
                            @php
                                $eval = $placement->evaluation;
                                $rataRata = round(($eval->nilai_disiplin + $eval->nilai_kinerja + $eval->nilai_laporan) / 3, 1);
                            @endphp
                            <div class="p-3.5 bg-blue-50 border border-blue-100 rounded-xl">
                                <div class="flex items-baseline justify-between">
                                    <span class="text-xs text-blue-700 font-semibold">Rata-rata:</span>
                                    <span class="text-2xl font-extrabold text-blue-700">{{ $rataRata }}</span>
                                </div>
                                <div class="text-[11px] text-blue-600 mt-1">
                                    Predikat: <strong>{{ $rataRata >= 85 ? 'A (Sangat Memuaskan)' : ($rataRata >= 70 ? 'B (Memuaskan)' : 'C (Cukup)') }}</strong>
                                </div>
                            </div>
                        @else
                            <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-center">
                                <span class="text-xs text-slate-400 italic">Belum dinilai oleh mentor</span>
                            </div>
                        @endif
                    </div>

                    <div class="pt-3 border-t border-slate-100">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 block mb-1.5">Laporan Akhir</span>
                        @if ($placement->finalreport)
                            <div class="flex items-center justify-between">
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full border
                                    {{ $placement->finalreport->status === 'approved' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}
                                    {{ $placement->finalreport->status === 'revision' ? 'bg-red-50 text-red-700 border-red-200' : '' }}
                                    {{ $placement->finalreport->status === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}">
                                    {{ strtoupper($placement->finalreport->status) }}
                                </span>
                                @if ($placement->finalreport->file_path)
                                    <a href="{{ asset('storage/' . $placement->finalreport->file_path) }}" target="_blank" class="text-xs font-semibold text-blue-600 hover:text-blue-700 inline-flex items-center gap-1">
                                        Unduh PDF <span>&rarr;</span>
                                    </a>
                                @endif
                            </div>
                        @else
                            <span class="text-xs text-slate-400 italic">Belum ada unggahan laporan</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- List Logbook Mahasiswa Tersebut -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm shadow-slate-200/50 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Riwayat Logbook Harian</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Daftar seluruh aktivitas harian yang dikerjakan mahasiswa</p>
                    </div>
                    <span class="text-xs font-semibold px-3 py-1 bg-blue-50 text-blue-700 rounded-full border border-blue-100">
                        {{ $placement->logbooks->count() }} Logbook
                    </span>
                </div>

                <div class="divide-y divide-slate-100 p-6 space-y-4">
                    @forelse ($placement->logbooks->sortByDesc('date') as $log)
                        <div class="p-5 bg-slate-50/70 rounded-2xl border border-slate-200/70 space-y-3">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="p-1.5 bg-indigo-100 text-indigo-700 rounded-lg">
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
                                    <a href="{{ route('mentor.logbooks.show', $log->id) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                                        Detail &rarr;
                                    </a>
                                </div>
                            </div>

                            <p class="text-xs sm:text-sm text-slate-700 leading-relaxed bg-white p-3.5 rounded-xl border border-slate-100">
                                {{ $log->activity }}
                            </p>

                            @if ($log->attachment)
                                <div>
                                    <a href="{{ asset('storage/' . $log->attachment) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-blue-600 hover:text-blue-700 font-semibold bg-blue-50 border border-blue-100 px-3 py-1.5 rounded-xl">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                        Buka Lampiran Bukti
                                    </a>
                                </div>
                            @endif

                            <!-- Quick Form Approve / Reject Inline -->
                            <form action="{{ route('mentor.logbooks.updateStatus', $log->id) }}" method="POST" class="pt-3 border-t border-slate-200/70 grid grid-cols-1 sm:grid-cols-4 gap-2 items-center">
                                @csrf
                                @method('PUT')
                                <div class="sm:col-span-3">
                                    <input type="text" name="feedback" value="{{ $log->feedback }}" placeholder="Catatan/feedback perbaikan logbook..." class="w-full text-xs rounded-xl border border-slate-200 bg-white text-slate-900 px-3 py-2 focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400">
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit" name="status" value="approved" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold uppercase rounded-xl transition shadow-sm">
                                        Approve
                                    </button>
                                    <button type="submit" name="status" value="rejected" class="w-full py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold uppercase rounded-xl transition shadow-sm">
                                        Reject
                                    </button>
                                </div>
                            </form>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400 text-xs">
                            Belum ada logbook kegiatan yang diunggah oleh mahasiswa ini.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>