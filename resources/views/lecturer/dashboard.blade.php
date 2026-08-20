<x-app-layout>
    <div class="py-8 bg-[#F5F8FC] min-h-screen text-slate-900 font-sans">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Welcome Header Card -->
            <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-6 sm:p-8 text-white shadow-sm shadow-blue-200">
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-white/20 backdrop-blur-sm border border-white/20 text-white">
                                Role: Dosen Pembimbing Lapangan (DPL)
                            </span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">
                            Portal DPL: {{ $lecturer->name }}
                        </h1>
                        <p class="text-sm text-blue-100 mt-1 leading-relaxed max-w-xl">
                            🎓 {{ $lecturer->university ?? 'Perguruan Tinggi Mitra' }} &bull; Monitoring & Penilaian Akademik Magang Pemkot Surabaya
                        </p>
                    </div>
                    <div class="shrink-0">
                        <a href="{{ route('lecturer.monitoring.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white text-blue-700 hover:bg-blue-50 text-xs font-semibold uppercase tracking-wider rounded-xl shadow-sm transition-all duration-200 hover:scale-[1.01]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                            <span>Monitoring Logbook</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Flash Alert -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-2xl flex items-center gap-3 text-emerald-800 text-sm font-semibold shadow-sm">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- 4 Metric Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm shadow-slate-200/50 transition-all duration-200 hover:scale-[1.01]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Mahasiswa Bimbingan</p>
                        <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </div>
                    </div>
                    <h3 class="text-2xl font-extrabold text-slate-900 mt-2">{{ $stats['total_students'] }}</h3>
                    <p class="text-[11px] text-slate-400 mt-1">Mahasiswa terdaftar</p>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm shadow-slate-200/50 transition-all duration-200 hover:scale-[1.01]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Nilai DPL Terisi</p>
                        <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                    </div>
                    <h3 class="text-2xl font-extrabold text-emerald-600 mt-2">{{ $stats['total_evaluated'] }}</h3>
                    <p class="text-[11px] text-slate-400 mt-1">Telah dinilai akademik</p>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm shadow-slate-200/50 transition-all duration-200 hover:scale-[1.01]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wider text-amber-600">Menunggu Penilaian</p>
                        <div class="w-8 h-8 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                    </div>
                    <h3 class="text-2xl font-extrabold text-amber-600 mt-2">{{ $stats['total_pending_eval'] }}</h3>
                    <p class="text-[11px] text-slate-400 mt-1">Perlu input nilai</p>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm shadow-slate-200/50 transition-all duration-200 hover:scale-[1.01]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wider text-blue-600">Laporan Disetujui</p>
                        <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                    </div>
                    <h3 class="text-2xl font-extrabold text-blue-600 mt-2">{{ $stats['total_reports_approved'] }}</h3>
                    <p class="text-[11px] text-slate-400 mt-1">Laporan akhir tervalidasi</p>
                </div>
            </div>

            <!-- Table Mahasiswa Bimbingan -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm shadow-slate-200/50 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Daftar Mahasiswa Bimbingan Magang</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pantau mahasiswa bimbingan kampus Anda di seluruh dinas Pemerintah Kota Surabaya</p>
                    </div>

                    <form method="GET" action="{{ route('lecturer.dashboard') }}" class="w-full md:w-72 flex items-center gap-2">
                        <div class="relative w-full">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIM..." class="w-full text-xs border border-slate-200 bg-slate-50 text-slate-900 rounded-xl pl-9 pr-3 py-2 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 transition-all duration-200">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <button type="submit" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-semibold uppercase tracking-wider rounded-xl shadow-sm transition">
                            Cari
                        </button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/60 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="py-3.5 px-5">Mahasiswa</th>
                                <th class="py-3.5 px-5">Dinas & Unit Penempatan</th>
                                <th class="py-3.5 px-5">Pembimbing Dinas</th>
                                <th class="py-3.5 px-5 text-center">Logbook</th>
                                <th class="py-3.5 px-5 text-center">Nilai Akademik DPL</th>
                                <th class="py-3.5 px-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse ($placements as $placement)
                                @php
                                    $student = $placement->application?->user;
                                    $profile = $student?->studentProfile;
                                    $unit = $placement->application?->unit;
                                    $agency = $unit?->agencyProfile ?? $placement->agencyProfile;
                                    $mentor = $placement->mentor ?? $placement->pembimbing;
                                    $logbookCount = $placement->logbooks->count();
                                    $approvedLogbooks = $placement->logbooks->where('status', 'approved')->count();
                                    $hasAcademicEval = $placement->evaluation && $placement->evaluation->nilai_akademik > 0;
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition-colors duration-150">
                                    <td class="py-4 px-5">
                                        <div class="font-bold text-slate-900 leading-snug">{{ $student->name ?? '-' }}</div>
                                        <div class="text-xs text-slate-400 mt-0.5">NIM: {{ $profile->nim ?? '-' }} &bull; {{ $profile->jurusan ?? 'Informatika' }}</div>
                                    </td>
                                    <td class="py-4 px-5 text-xs">
                                        <div class="font-semibold text-slate-800">🏛️ {{ $agency->agency_name ?? '-' }}</div>
                                        <div class="text-slate-400 mt-0.5">{{ $unit->name ?? '-' }}</div>
                                    </td>
                                    <td class="py-4 px-5 text-xs">
                                        <div class="font-medium text-slate-800">{{ $mentor->name ?? '-' }}</div>
                                        <div class="text-slate-400 text-[11px]">{{ $mentor->email ?? '-' }}</div>
                                    </td>
                                    <td class="py-4 px-5 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-700 border border-blue-100 text-xs font-semibold rounded-full">
                                            📝 {{ $approvedLogbooks }} / {{ $logbookCount }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-5 text-center whitespace-nowrap">
                                        @if ($hasAcademicEval)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-extrabold rounded-full">
                                                ⭐ {{ $placement->evaluation->nilai_akademik }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 text-[11px] font-semibold rounded-full">
                                                Belum Dinilai
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-5 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('lecturer.students.show', $placement->id) }}" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 border border-blue-100 rounded-xl text-xs font-semibold transition">
                                                Monitoring
                                            </a>
                                            <a href="{{ route('lecturer.evaluations.create', $placement->id) }}" class="px-3 py-1.5 {{ $hasAcademicEval ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-blue-600 text-white hover:bg-blue-700 shadow-sm shadow-blue-200' }} rounded-xl text-xs font-semibold uppercase tracking-wider transition">
                                                {{ $hasAcademicEval ? 'Ubah Nilai' : 'Input Nilai' }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-400">
                                        <p class="font-medium text-slate-600">Belum Ada Mahasiswa Bimbingan</p>
                                        <p class="text-xs text-slate-400 mt-1">Mahasiswa bimbingan dari kampus {{ $lecturer->university }} akan otomatis tampil di sini.</p>
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