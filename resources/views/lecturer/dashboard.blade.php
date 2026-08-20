<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                    </svg>
                    {{ __('Portal Dosen Pembimbing Lapangan (DPL Kampus)') }}
                </h2>
                <div class="flex items-center gap-2 mt-1">
                    <span class="px-2.5 py-0.5 bg-indigo-100 text-indigo-800 text-xs font-bold rounded-md">
                        🎓 {{ $lecturer->university ?? 'Perguruan Tinggi Mitra' }}
                    </span>
                    <span class="text-xs text-gray-500 font-medium">
                        Dosen: {{ $lecturer->name }}
                    </span>
                </div>
            </div>

            <a href="{{ route('lecturer.monitoring.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                Monitoring Logbook & Laporan
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Alert -->
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

            <!-- Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Mahasiswa Bimbingan</p>
                    <h3 class="text-2xl font-black text-gray-900 mt-1">{{ $stats['total_students'] }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Mahasiswa asal {{ $lecturer->university ?? 'kampus' }}</p>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Nilai Akademik Terisi</p>
                    <h3 class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['total_evaluated'] }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Telah dinilai oleh DPL</p>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Menunggu Penilaian</p>
                    <h3 class="text-2xl font-black text-amber-600 mt-1">{{ $stats['total_pending_eval'] }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Perlu input nilai bimbingan</p>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Laporan Akhir Disetujui</p>
                    <h3 class="text-2xl font-black text-indigo-600 mt-1">{{ $stats['total_reports_approved'] }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Laporan magang tervalidasi</p>
                </div>
            </div>

            <!-- Table Mahasiswa Bimbingan -->
            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-200">
                <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Daftar Mahasiswa Bimbingan Magang</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Pantau mahasiswa bimbingan kampus Anda di seluruh dinas Pemerintah Kota Surabaya</p>
                    </div>

                    <!-- Search Form -->
                    <form method="GET" action="{{ route('lecturer.dashboard') }}" class="flex items-center gap-2 w-full md:w-auto">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIM..." class="text-xs border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 w-full md:w-60 shadow-sm">
                        <button type="submit" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition shadow-sm">
                            Cari
                        </button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/75 border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                <th class="py-3.5 px-4">Mahasiswa</th>
                                <th class="py-3.5 px-4">Dinas & Unit Penempatan</th>
                                <th class="py-3.5 px-4">Pembimbing Dinas</th>
                                <th class="py-3.5 px-4 text-center">Logbook</th>
                                <th class="py-3.5 px-4 text-center">Nilai Akademik DPL</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($placements as $placement)
                                @php
                                    $student = $placement->application->user;
                                    $profile = $student->studentProfile;
                                    $unit = $placement->application->unit;
                                    $agency = $unit?->agencyProfile ?? $placement->agencyProfile;
                                    $mentor = $placement->mentor ?? $placement->pembimbing;
                                    $logbookCount = $placement->logbooks->count();
                                    $approvedLogbooks = $placement->logbooks->where('status', 'approved')->count();
                                    $hasAcademicEval = $placement->evaluation && $placement->evaluation->nilai_akademik > 0;
                                @endphp
                                <tr class="hover:bg-slate-50/75 transition-colors">
                                    
                                    <!-- Mahasiswa -->
                                    <td class="py-4 px-4">
                                        <div class="font-bold text-gray-900 leading-snug">{{ $student->name }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5">NIM: {{ $profile->nim ?? '-' }} &bull; {{ $profile->jurusan ?? 'Informatika' }}</div>
                                    </td>

                                    <!-- Dinas & Unit Penempatan -->
                                    <td class="py-4 px-4">
                                        <div class="font-semibold text-gray-800 text-xs">🏛️ {{ $agency->agency_name ?? '-' }}</div>
                                        <div class="text-[11px] text-gray-500 mt-0.5">{{ $unit->name ?? '-' }}</div>
                                    </td>

                                    <!-- Pembimbing Dinas -->
                                    <td class="py-4 px-4">
                                        <div class="text-xs font-medium text-gray-800">{{ $mentor->name ?? '-' }}</div>
                                        <div class="text-[10px] text-gray-400">{{ $mentor->email ?? '-' }}</div>
                                    </td>

                                    <!-- Logbook Stats -->
                                    <td class="py-4 px-4 text-center">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-700 text-xs font-semibold rounded-md">
                                            📝 {{ $approvedLogbooks }} / {{ $logbookCount }}
                                        </span>
                                    </td>

                                    <!-- Nilai Akademik DPL -->
                                    <td class="py-4 px-4 text-center">
                                        @if ($hasAcademicEval)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-100 text-emerald-800 text-xs font-black rounded-full">
                                                ⭐ {{ $placement->evaluation->nilai_akademik }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 text-amber-800 text-xs font-bold rounded-md">
                                                Belum Dinilai
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-4 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('lecturer.students.show', $placement->id) }}" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-lg transition shadow-sm">
                                                Monitoring
                                            </a>

                                            <a href="{{ route('lecturer.evaluations.create', $placement->id) }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition shadow-sm">
                                                {{ $hasAcademicEval ? 'Ubah Nilai' : 'Input Nilai' }}
                                            </a>
                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-gray-400">
                                        <p class="font-medium text-gray-600">Belum Ada Mahasiswa Bimbingan</p>
                                        <p class="text-xs text-gray-400 mt-1">Mahasiswa bimbingan dari kampus {{ $lecturer->university }} akan otomatis tampil di sini.</p>
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
