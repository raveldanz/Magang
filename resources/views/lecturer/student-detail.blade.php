<x-app-layout>
    <div class="py-8 bg-[#F5F8FC] min-h-screen text-slate-900 font-sans">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-3">
                    <a href="{{ route('lecturer.dashboard') }}" class="p-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-600 rounded-xl transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    </a>
                    <div>
                        <h2 class="text-xl font-bold tracking-tight text-slate-900">
                            Monitoring Mahasiswa Bimbingan
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Pantau aktivitas lapangan, logbook harian, laporan akhir, dan evaluasi akademik</p>
                    </div>
                </div>

                <a href="{{ route('lecturer.evaluations.create', $placement->id) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-semibold uppercase tracking-wider rounded-xl shadow-sm shadow-blue-200 transition-all duration-200 hover:scale-[1.01]">
                    <span>{{ $evaluation?->nilai_akademik > 0 ? 'Edit Nilai Akademik DPL' : 'Input Nilai Akademik DPL' }}</span>
                </a>
            </div>

            <!-- Ringkasan Profil & Status Penempatan -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                
                <!-- Kartu Mahasiswa -->
                <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm shadow-slate-200/50 space-y-4">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 font-bold text-base flex items-center justify-center shrink-0">
                            {{ strtoupper(substr($student->name ?? 'M', 0, 2)) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 leading-snug">{{ $student->name }}</h3>
                            <span class="text-xs text-slate-400 font-mono">NIM: {{ $profile->nim ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-400">Perguruan Tinggi:</span>
                            <span class="font-semibold text-slate-800">{{ $profile->universitas ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-400">Program Studi:</span>
                            <span class="font-semibold text-slate-800">{{ $profile->jurusan ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-slate-400">Email:</span>
                            <span class="font-semibold text-slate-800">{{ $student->email }}</span>
                        </div>
                    </div>
                </div>

                <!-- Kartu Instansi & Pembimbing Dinas -->
                <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm shadow-slate-200/50 space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 block">Penempatan Magang</span>
                        <h3 class="font-bold text-slate-900 mt-1 text-sm">🏛️ {{ $agencyProfile->agency_name ?? '-' }}</h3>
                        <p class="text-xs text-blue-600 font-semibold mt-0.5">{{ $unit->name ?? '-' }}</p>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-400">Pembimbing Dinas:</span>
                            <span class="font-semibold text-slate-800">{{ $mentor->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-400">Email Mentor:</span>
                            <span class="font-medium text-slate-600">{{ $mentor->email ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-slate-400">Periode:</span>
                            <span class="font-semibold text-slate-800">{{ $placement->application->start_date }} s/d {{ $placement->application->end_date }}</span>
                        </div>
                    </div>
                </div>

                <!-- Kartu Nilai & Aksi -->
                <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm shadow-slate-200/50 flex flex-col justify-between space-y-4">
                    <div>
                        <div class="border-b border-slate-100 pb-3">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 block">Rekapitulasi Penilaian</span>
                            <h3 class="font-bold text-slate-900 mt-1 text-sm">Nilai Lapangan & Akademik</h3>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mt-3">
                            <div class="bg-blue-50 border border-blue-100 p-3 rounded-xl text-center">
                                <span class="text-[10px] font-bold text-blue-600 uppercase">Nilai DPL</span>
                                <p class="text-xl font-extrabold text-blue-700 mt-0.5">
                                    {{ $evaluation?->nilai_akademik > 0 ? $evaluation->nilai_akademik : '-' }}
                                </p>
                            </div>
                            <div class="bg-emerald-50 border border-emerald-100 p-3 rounded-xl text-center">
                                <span class="text-[10px] font-bold text-emerald-600 uppercase">Nilai Dinas</span>
                                <p class="text-xl font-extrabold text-emerald-700 mt-0.5">
                                    {{ $evaluation?->nilai_kinerja > 0 ? round(($evaluation->nilai_disiplin + $evaluation->nilai_kinerja + $evaluation->nilai_laporan) / 3, 1) : '-' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('lecturer.evaluations.create', $placement->id) }}" class="w-full py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-semibold uppercase tracking-wider rounded-xl text-center shadow-sm transition">
                        {{ $evaluation?->nilai_akademik > 0 ? 'Edit Nilai Akademik' : 'Input Nilai Akademik' }}
                    </a>
                </div>

            </div>

            <!-- Bagian Laporan Akhir & Dokumen -->
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm shadow-slate-200/50">
                <h3 class="text-base font-bold text-slate-900 mb-3">Laporan Akhir Magang Mahasiswa</h3>
                @if ($finalReport)
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 bg-slate-50/70 rounded-xl border border-slate-100 gap-3">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900">Berkas Laporan Akhir Magang (PDF)</p>
                                <span class="text-[11px] text-slate-500">Status: <strong>{{ strtoupper($finalReport->status) }}</strong> &bull; Feedback: "{{ $finalReport->feedback ?? 'Telah diperiksa' }}"</span>
                            </div>
                        </div>

                        <a href="{{ asset('storage/' . $finalReport->file_path) }}" target="_blank" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold uppercase tracking-wider rounded-xl transition shadow-sm flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            Buka Laporan
                        </a>
                    </div>
                @else
                    <div class="p-4 bg-amber-50 text-amber-800 text-xs font-medium rounded-xl border border-amber-200">
                        Mahasiswa belum mengunggah laporan akhir magang.
                    </div>
                @endif
            </div>

            <!-- Tabel Logbook Kegiatan Harian Mahasiswa -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm shadow-slate-200/50 overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Rekapitulasi Logbook Harian Mahasiswa</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Catatan aktivitas lapangan dan status verifikasi pembimbing dinas</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/75 border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                <th class="py-3 px-4">Tanggal</th>
                                <th class="py-3 px-4">Uraian Kegiatan Lapangan</th>
                                <th class="py-3 px-4 text-center">Status Verifikasi Dinas</th>
                                <th class="py-3 px-4">Catatan Feedback Mentor</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse ($logbooks as $logbook)
                                <tr class="hover:bg-slate-50/70 transition-colors duration-150">
                                    <td class="py-4 px-5 text-xs text-slate-600 whitespace-nowrap">
                                        📅 {{ \Carbon\Carbon::parse($logbook->date)->translatedFormat('d F Y') }}
                                    </td>
                                    <td class="py-4 px-5 text-xs text-slate-800 leading-relaxed">
                                        {{ $logbook->activity }}
                                    </td>
                                    <td class="py-4 px-5 text-center whitespace-nowrap">
                                        @php $st = strtolower($logbook->status ?? ''); @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-bold rounded-full border
                                            {{ $st === 'approved' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}
                                            {{ $st === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}
                                            {{ $st === 'rejected' ? 'bg-red-50 text-red-700 border-red-200' : '' }}">
                                            {{ strtoupper($logbook->status) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-5 text-xs text-slate-500 italic">
                                        {{ $logbook->feedback ?? '-' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <a href="{{ route('lecturer.logbooks.show', $logbook->id) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                                            Detail &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400 text-xs">
                                        Belum ada catatan logbook yang diunggah mahasiswa.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>