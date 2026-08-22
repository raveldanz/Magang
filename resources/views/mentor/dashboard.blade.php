<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    {{ __('Portal Pembimbing Lapangan (Mentor)') }}
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    {{ Auth::user()->agencyProfile->agency_name ?? 'Pemerintah Kota Surabaya' }} &bull; Monitoring & Evaluasi Mahasiswa
                </p>
            </div>
            <div class="flex items-center gap-2 bg-blue-50 border border-blue-100 text-blue-800 text-xs font-semibold px-3 py-1.5 rounded-full shadow-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Mentor: {{ Auth::user()->name }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Alert Flash Message -->
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

            <!-- Summary Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Bimbingan Aktif -->
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Bimbingan Aktif</p>
                            <h3 class="text-2xl font-black text-blue-600 mt-1">{{ $stats['active_students'] }}</h3>
                            <p class="text-xs text-gray-500 mt-1">Sedang aktif magang</p>
                        </div>
                        <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Logbook Pending -->
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Logbook Pending</p>
                            <h3 class="text-2xl font-black text-amber-600 mt-1">{{ $stats['pending_logbooks'] }}</h3>
                            <p class="text-xs text-gray-500 mt-1">Perlu diverifikasi</p>
                        </div>
                        <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Mahasiswa Sudah Dinilai / Selesai -->
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Alumni Selesai</p>
                            <h3 class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['completed_students'] }}</h3>
                            <p class="text-xs text-gray-500 mt-1">Telah dinilai & lulus</p>
                        </div>
                        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Calon Peserta -->
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Calon Peserta</p>
                            <h3 class="text-2xl font-black text-blue-600 mt-1">{{ $stats['upcoming_students'] }}</h3>
                            <p class="text-xs text-gray-500 mt-1">Menunggu jadwal mulai</p>
                        </div>
                        <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Segregated Tab Navigation -->
            <div class="flex flex-wrap items-center gap-2 border-b border-gray-200 pb-2">
                <a href="{{ route('mentor.dashboard', ['tab' => 'active']) }}" 
                    class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2 {{ ($tab ?? 'active') === 'active' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
                    <span>⚡ Bimbingan Aktif</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] {{ ($tab ?? 'active') === 'active' ? 'bg-blue-700 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $stats['active_students'] }}</span>
                </a>
                <a href="{{ route('mentor.dashboard', ['tab' => 'upcoming']) }}" 
                    class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2 {{ ($tab ?? '') === 'upcoming' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
                    <span>📅 Calon Peserta Magang</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] {{ ($tab ?? '') === 'upcoming' ? 'bg-blue-700 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $stats['upcoming_students'] }}</span>
                </a>
                <a href="{{ route('mentor.dashboard', ['tab' => 'completed']) }}" 
                    class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2 {{ ($tab ?? '') === 'completed' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
                    <span>🎓 Alumni Selesai</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] {{ ($tab ?? '') === 'completed' ? 'bg-blue-700 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $stats['completed_students'] }}</span>
                </a>
                <a href="{{ route('mentor.dashboard', ['tab' => 'all']) }}" 
                    class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2 {{ ($tab ?? '') === 'all' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
                    <span>📁 Semua Mahasiswa</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] {{ ($tab ?? '') === 'all' ? 'bg-blue-700 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $stats['total_students'] }}</span>
                </a>
            </div>

            <!-- Table Mahasiswa Bimbingan -->
            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-200">
                <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">
                            @if (($tab ?? 'active') === 'active')
                                Daftar Mahasiswa Bimbingan Aktif
                            @elseif ($tab === 'upcoming')
                                Daftar Calon Peserta Magang (Mendatang)
                            @elseif ($tab === 'completed')
                                Arsip Alumni Mahasiswa Selesai
                            @else
                                Seluruh Daftar Mahasiswa Bimbingan
                            @endif
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Kelola verifikasi logbook harian dan berikan penilaian evaluasi akhir</p>
                    </div>
                    <a href="{{ route('mentor.logbooks.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 text-xs font-semibold rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                        Feed Logbook Masuk
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/75 border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                <th class="py-3.5 px-4">Mahasiswa</th>
                                <th class="py-3.5 px-4">Unit & Periode</th>
                                <th class="py-3.5 px-4 text-center">STATUS</th>
                                <th class="py-3.5 px-4 text-center">LOGBOOK</th>
                                <th class="py-3.5 px-4 text-center">Laporan Akhir</th>
                                <th class="py-3.5 px-4 text-center">Nilai Akhir</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($placements as $place)
                                @php
                                    $student = $place->application->user ?? null;
                                    $profile = $student?->studentProfile;
                                    $unit = $place->application->unit;
                                    $eval = $place->evaluation;
                                    $report = $place->finalreport;
                                    $totalLog = $place->logbooks->count();
                                    $pendingLog = $place->logbooks->where('status', 'pending')->count();
                                    $lifecycle = $place->application?->lifecycle_status ?? 'ACCEPTED';
                                    
                                    $rataRata = $eval ? round(($eval->nilai_disiplin + $eval->nilai_kinerja + $eval->nilai_laporan) / 3, 1) : null;
                                @endphp
                                <tr class="hover:bg-slate-50/75 transition-colors">
                                    <!-- Mahasiswa -->
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-xs shrink-0">
                                                {{ strtoupper(substr($student->name ?? 'M', 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900 leading-snug">{{ $student->name ?? '-' }}</div>
                                                <div class="text-xs text-gray-500 mt-0.5">
                                                    {{ $profile->nim ?? '-' }} &bull; {{ $profile->universitas ?? '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Unit Penempatan -->
                                    <td class="py-4 px-4">
                                        <div class="text-xs font-semibold text-gray-800 leading-tight">{{ $unit->name ?? '-' }}</div>
                                        <div class="text-[11px] text-gray-400 mt-0.5">
                                            {{ \Carbon\Carbon::parse($place->application->start_date)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($place->application->end_date)->translatedFormat('d M Y') }}
                                        </div>
                                    </td>

                                    <!-- Status Lifecycle -->
                                    <td class="py-4 px-4 text-center">
                                        <span class="px-2.5 py-1 text-[11px] font-bold rounded-full 
                                            {{ $lifecycle === 'ACTIVE' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : '' }}
                                            {{ $lifecycle === 'ACCEPTED' ? 'bg-blue-100 text-blue-800 border border-blue-300' : '' }}
                                            {{ $lifecycle === 'COMPLETED' ? 'bg-purple-100 text-purple-800 border border-purple-300' : '' }}">
                                            {{ $lifecycle }}
                                        </span>
                                    </td>

                                    <!-- Status Logbook -->
                                    <td class="py-4 px-4 text-center">
                                        <div class="inline-flex flex-col items-center gap-1">
                                            <span class="text-xs font-bold text-gray-700">{{ $totalLog }} Kegiatan</span>
                                            @if ($pendingLog > 0)
                                                <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-[10px] font-bold rounded-full animate-pulse">
                                                    {{ $pendingLog }} Pending
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-medium rounded-full">
                                                    Terverifikasi
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Laporan Akhir -->
                                    <td class="py-4 px-4 text-center">
                                        @if ($report && $report->status === 'approved')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-full border border-emerald-200">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Disetujui
                                            </span>
                                        @elseif ($report && $report->status === 'revision')
                                            <span class="px-2.5 py-1 bg-rose-50 text-rose-700 text-xs font-bold rounded-full border border-rose-200">
                                                Revisi
                                            </span>
                                        @elseif ($report)
                                            <span class="px-2.5 py-1 bg-yellow-50 text-yellow-800 text-xs font-medium rounded-full border border-yellow-200">
                                                Terkirim
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Belum Ada</span>
                                        @endif
                                    </td>

                                    <!-- Nilai Akhir -->
                                    <td class="py-4 px-4 text-center">
                                        @if ($eval)
                                            <div class="inline-block">
                                                <span class="text-base font-black text-blue-600">{{ $rataRata }}</span>
                                                <span class="text-xs font-bold px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 ml-1">
                                                    {{ $rataRata >= 85 ? 'A' : ($rataRata >= 70 ? 'B' : 'C') }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="px-2.5 py-1 bg-gray-100 text-gray-500 text-xs font-medium rounded-full">
                                                Belum Dinilai
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-4 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('mentor.students.show', $place->id) }}" 
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition shadow-xs border border-gray-200">
                                                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                <span>Detail</span>
                                            </a>
                                            <a href="{{ route('mentor.evaluations.create', $place->id) }}" 
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 {{ $eval ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'bg-blue-600 hover:bg-blue-700 text-white' }} text-xs font-bold rounded-xl transition shadow-xs cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                <span>{{ $eval ? 'Edit Nilai' : 'Input Nilai' }}</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-gray-400">
                                        <div class="max-w-sm mx-auto space-y-2">
                                            <svg class="w-12 h-12 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            <p class="font-medium text-gray-600">Tidak ada data mahasiswa pada tab ini</p>
                                            <p class="text-xs text-gray-400">Data mahasiswa bimbingan akan diperbarui sesuai status lifecycle aktif.</p>
                                        </div>
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
