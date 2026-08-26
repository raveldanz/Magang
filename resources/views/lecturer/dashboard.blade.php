<<<<<<< HEAD
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
                        {{ $lecturer->university ?? 'Perguruan Tinggi Mitra' }}
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
=======
﻿<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                    <span>Portal Dosen Pembimbing Lapangan (DPL Kampus)</span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Monitoring bimbingan akademik, verifikasi logbook, review laporan akhir, dan evaluasi 60% mahasiswa magang
                </p>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="px-3.5 py-1.5 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl text-xs font-bold shadow-2xs">
                   {{ is_string($lecturer->university) ? $lecturer->university : ($lecturer->universityRelation?->name ?? $lecturer->university?->name ?? 'Perguruan Tinggi Mitra') }}
                </span>
            </div>
>>>>>>> main
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

<<<<<<< HEAD
            <!-- Flash Alert -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-lg shadow-sm flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
=======
            <!-- Flash Alert Messages -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
>>>>>>> main
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif
<<<<<<< HEAD

            <!-- Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-white rounded-2xl p-5 shadow-sm">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Mahasiswa Bimbingan</p>
                    <h3 class="text-2xl font-black text-gray-900 mt-1">{{ $stats['total_students'] }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Mahasiswa asal {{ $lecturer->university ?? 'kampus' }}</p>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Nilai Akademik Terisi</p>
                    <h3 class="text-2xl font-black text-gray-900 mt-1">{{ $stats['total_evaluated'] }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Telah dinilai oleh DPL</p>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Menunggu Penilaian</p>
                    <h3 class="text-2xl font-black text-gray-900 mt-1">{{ $stats['total_pending_eval'] }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Perlu input nilai bimbingan</p>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Laporan Akhir Disetujui</p>
                    <h3 class="text-2xl font-black text-gray-900 mt-1">{{ $stats['total_reports_approved'] }}</h3>
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
                                        <div class="font-semibold text-gray-800 text-xs">{{ $agency->agency_name ?? '-' }}</div>
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
                                            {{ $approvedLogbooks }} / {{ $logbookCount }}
                                        </span>
                                    </td>

                                    <!-- Nilai Akademik DPL -->
                                    <td class="py-4 px-4 text-center">
                                        @if ($hasAcademicEval)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-100 text-emerald-800 text-xs font-black rounded-full">
                                                {{ $placement->evaluation->nilai_akademik }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 text-amber-800 text-xs font-bold rounded-md">
=======
                        
            <!-- 1. STATS WIDGETS -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Total Mahasiswa Bimbingan -->
                <div class="bg-white p-5 rounded-2xl shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-900 uppercase tracking-wider">Total Bimbingan</span>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-black text-gray-900">{{ $stats['total_students'] }}</span>
                        <span class="text-[11px] text-gray-500 block mt-0.5">Mahasiswa magang aktif</span>
                    </div>
                </div>

                <!-- Evaluasi Selesai (60%) -->
                <div class="bg-white p-5 rounded-2xl shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-900 uppercase tracking-wider">Sudah Dinilai</span>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-black text-gray-900">{{ $stats['total_evaluated'] }}</span>
                        <span class="text-[11px] text-gray-500 block mt-0.5">Nilai DPL 60% tersimpan</span>
                    </div>
                </div>

                <!-- Laporan Akhir Masuk / Pending -->
                <div class="bg-white p-5 rounded-2xl shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-900 uppercase tracking-wider">Laporan Akhir</span>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-black text-gray-900">{{ $stats['total_reports_approved'] }}</span>
                        <span class="text-[11px] text-gray-500 block mt-0.5">Telah di-ACC DPL</span>
                    </div>
                </div>

                <!-- Belum Dinilai -->
                <div class="bg-white p-5 rounded-2xl shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-900 uppercase tracking-wider">Belum Dinilai</span>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-black text-gray-900">{{ $stats['total_pending_eval'] }}</span>
                        <span class="text-[11px] text-gray-500 block mt-0.5">Menunggu penilaian DPL</span>
                    </div>
                </div>

            </div>

            <!-- 2. FILTER & SEARCH CARD -->
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs">
                <form method="GET" action="{{ route('lecturer.dashboard') }}" class="flex flex-col sm:flex-row items-center gap-3">
                    <div class="relative flex-1 w-full">
                        <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none text-slate-400" style="padding-left: 1rem !important;">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama mahasiswa, NIM, atau program studi..." 
                               class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                               style="padding-left: 2.75rem !important; padding-right: 1rem !important; padding-top: 0.6rem !important; padding-bottom: 0.6rem !important;">
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <select name="agency_id" class="py-2 text-xs border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-medium w-full sm:w-auto">
                            <option value="">Semua Instansi Dinas</option>
                            @foreach($agencies as $ag)
                                <option value="{{ $ag->id }}" {{ request('agency_id') == $ag->id ? 'selected' : '' }}>
                                    {{ $ag->agency_name }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition shrink-0 cursor-pointer">
                            Cari
                        </button>

                        @if(request()->hasAny(['search', 'agency_id']))
                            <a href="{{ route('lecturer.dashboard') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold rounded-xl transition shrink-0">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- 3. TABEL MAHASISWA BIMBINGAN DPL -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-sm text-gray-900">Daftar Mahasiswa Bimbingan Magang</h3>
                    </div>
                    <span class="text-xs text-gray-400 font-mono">{{ $placements->count() }} Mahasiswa</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-left text-xs">
                        <thead class="bg-gray-50/75 text-gray-500 font-bold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="py-3.5 px-4">Mahasiswa</th>
                                <th class="py-3.5 px-4">Instansi & Divisi Magang</th>
                                <th class="py-3.5 px-4">Pembimbing Dinas</th>
                                <th class="py-3.5 px-4 text-center">Logbook</th>
                                <th class="py-3.5 px-4 text-center">Laporan Akhir</th>
                                <th class="py-3.5 px-4 text-center">Nilai DPL (60%)</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($placements as $p)
                                @php
                                    $student = $p->application?->user;
                                    $profile = $student?->studentProfile;
                                    $unit = $p->application?->unit;
                                    $agency = $unit?->agencyProfile;
                                    $mentor = $p->mentor ?? $p->pembimbing;
                                    $eval = $p->evaluation;
                                    $hasEval = $eval && (($eval->nilai_akademik ?? 0) > 0 || ($eval->nilai_dosen ?? 0) > 0);
                                    $finalReport = $p->finalreport;
                                    $logbooksCount = $p->logbooks->count();
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition">
                                    
                                    <!-- Student Info -->
                                    <td class="py-4 px-4">
                                        <div class="font-bold text-gray-900 text-xs sm:text-sm">{{ $student->name ?? '-' }}</div>
                                        <div class="text-[11px] text-gray-500 font-mono">NIM: {{ $profile?->nim ?? '-' }}</div>
                                        <div class="text-[10px] text-blue-600 font-semibold">{{ $profile?->jurusan ?? '-' }}</div>
                                    </td>

                                    <!-- Placement Location -->
                                    <td class="py-4 px-4">
                                        <div class="font-bold text-gray-800 text-xs">{{ $agency->agency_name ?? '-' }}</div>
                                        <div class="text-[11px] text-gray-500">{{ $unit->name ?? '-' }}</div>
                                    </td>

                                    <!-- Mentor Info -->
                                    <td class="py-4 px-4">
                                        <div class="font-semibold text-gray-800 text-xs">{{ $mentor->name ?? 'Belum Ditugaskan' }}</div>
                                        <div class="text-[10px] text-emerald-600 font-semibold">
                                            @if(($eval?->nilai_pembimbing ?? 0) > 0)
                                                Skor Dinas: {{ $eval->nilai_pembimbing }}/100 (40%)
                                            @else
                                                <span class="text-gray-400">Belum dinilai dinas</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Logbook Counter -->
                                    <td class="py-4 px-4 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            {{ $logbooksCount }} Entri
                                        </span>
                                    </td>

                                    <!-- Final Report Status -->
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        @if(!$finalReport)
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500">
                                                Belum Unggah
                                            </span>
                                        @elseif($finalReport->status === 'approved')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                Disetujui
                                            </span>
                                        @elseif($finalReport->status === 'revision')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                                Perlu Revisi
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                Menunggu Review
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Nilai DPL Status -->
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        @if($hasEval)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                {{ $eval->nilai_dosen_calculated }}/100
                                                @if($eval->grade)
                                                    ({{ $eval->grade }})
                                                @endif
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
>>>>>>> main
                                                Belum Dinilai
                                            </span>
                                        @endif
                                    </td>

<<<<<<< HEAD
                                    <!-- Aksi -->
                                    <td class="py-4 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('lecturer.students.show', $placement->id) }}" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-lg transition shadow-sm">
                                                Monitoring
                                            </a>

                                            <a href="{{ route('lecturer.evaluations.create', $placement->id) }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition shadow-sm">
                                                {{ $hasAcademicEval ? 'Ubah Nilai' : 'Input Nilai' }}
=======
                                    <!-- Action Buttons -->
                                    <td class="py-4 px-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('lecturer.students.show', $p->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl text-xs font-bold transition cursor-pointer">
                                                <span>Detail</span>
                                                <span>→</span>
>>>>>>> main
                                            </a>
                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
<<<<<<< HEAD
                                    <td colspan="6" class="py-12 text-center text-gray-400">
                                        <p class="font-medium text-gray-600">Belum Ada Mahasiswa Bimbingan</p>
                                        <p class="text-xs text-gray-400 mt-1">Mahasiswa bimbingan dari kampus {{ $lecturer->university }} akan otomatis tampil di sini.</p>
=======
                                    <td colspan="7" class="py-12 text-center text-gray-400">
                                        Belum ada mahasiswa bimbingan magang yang ditugaskan kepada Anda.
>>>>>>> main
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
<<<<<<< HEAD
</x-app-layout>
=======
</x-app-layout>
>>>>>>> main
