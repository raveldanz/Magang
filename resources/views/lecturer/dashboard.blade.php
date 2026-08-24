<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                    <span>👨‍🏫</span>
                    <span>Portal Dosen Pembimbing Lapangan (DPL Kampus)</span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Monitoring bimbingan akademik, verifikasi logbook, review laporan akhir, dan evaluasi 60% mahasiswa magang
                </p>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="px-3.5 py-1.5 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl text-xs font-bold shadow-2xs">
                    🏛️ {{ is_string($lecturer->university) ? $lecturer->university : ($lecturer->universityRelation?->name ?? $lecturer->university?->name ?? 'Perguruan Tinggi Mitra') }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Alert Messages -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <span>✅</span>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif
                        
            <!-- 1. STATS WIDGETS -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Total Mahasiswa Bimbingan -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Bimbingan</span>
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold">
                            👨‍🎓
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-black text-gray-900">{{ $stats['total_students'] }}</span>
                        <span class="text-[11px] text-gray-400 block mt-0.5">Mahasiswa magang aktif</span>
                    </div>
                </div>

                <!-- Evaluasi Selesai (60%) -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Sudah Dinilai</span>
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">
                            📝
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-black text-emerald-600">{{ $stats['total_evaluated'] }}</span>
                        <span class="text-[11px] text-gray-400 block mt-0.5">Nilai DPL 60% tersimpan</span>
                    </div>
                </div>

                <!-- Laporan Akhir Masuk / Pending -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-amber-600 uppercase tracking-wider">Laporan Akhir</span>
                        <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-bold">
                            📂
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-black text-amber-600">{{ $stats['total_reports_approved'] }}</span>
                        <span class="text-[11px] text-gray-400 block mt-0.5">Telah di-ACC DPL</span>
                    </div>
                </div>

                <!-- Belum Dinilai -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-rose-600 uppercase tracking-wider">Belum Dinilai</span>
                        <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-sm font-bold">
                            ⏳
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-black text-rose-600">{{ $stats['total_pending_eval'] }}</span>
                        <span class="text-[11px] text-gray-400 block mt-0.5">Menunggu penilaian DPL</span>
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
                        <span class="text-lg">📋</span>
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
                                        <div class="font-bold text-gray-800 text-xs">🏛️ {{ $agency->agency_name ?? '-' }}</div>
                                        <div class="text-[11px] text-gray-500">{{ $unit->name ?? '-' }}</div>
                                    </td>

                                    <!-- Mentor Info -->
                                    <td class="py-4 px-4">
                                        <div class="font-semibold text-gray-800 text-xs">👔 {{ $mentor->name ?? 'Belum Ditugaskan' }}</div>
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
                                            📝 {{ $logbooksCount }} Entri
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
                                                ✅ Disetujui
                                            </span>
                                        @elseif($finalReport->status === 'revision')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                                ⚠️ Perlu Revisi
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                ⏳ Menunggu Review
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Nilai DPL Status -->
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        @if($hasEval)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                ⭐ {{ $eval->nilai_dosen_calculated }}/100
                                                @if($eval->grade)
                                                    ({{ $eval->grade }})
                                                @endif
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                Belum Dinilai
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Action Buttons -->
                                    <td class="py-4 px-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('lecturer.students.show', $p->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl text-xs font-bold transition cursor-pointer">
                                                <span>Detail</span>
                                                <span>→</span>
                                            </a>
                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-gray-400">
                                        Belum ada mahasiswa bimbingan magang yang ditugaskan kepada Anda.
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
