<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Portal Monitoring Magang — ') . ($university->name ?? $user->name) }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Welcome Banner Resmi Universitas -->
            <div class="bg-gradient-to-r from-blue-700 via-indigo-800 to-indigo-950 rounded-2xl p-6 text-white shadow-lg flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2.5 py-0.5 text-xs font-black bg-blue-400/30 border border-blue-300/40 rounded-full tracking-wider uppercase">
                            🏛️ Akun Resmi Perguruan Tinggi
                        </span>
                        @if($university?->code)
                            <span class="px-2 py-0.5 text-xs font-mono font-bold bg-white/20 rounded-md">
                                {{ $university->code }}
                            </span>
                        @endif
                    </div>
                    <h3 class="text-2xl font-black">{{ $university->name ?? $user->name }}</h3>
                    <p class="text-blue-100 text-xs sm:text-sm mt-1">
                        Pemantauan Partisipasi, Distribusi Penempatan Dinas, & Evaluasi Mahasiswa Magang di Pemkot Surabaya
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right hidden sm:block">
                        <p class="text-xs text-blue-200">Email Resmi</p>
                        <p class="text-xs font-mono font-bold text-white">{{ $user->email }}</p>
                    </div>
                </div>
            </div>

            <!-- Metrik Statistik Utama Kampus -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Card 1: Total Mahasiswa -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 border-l-4 border-l-blue-600 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Pendaftar</p>
                        <p class="text-2xl font-black text-gray-800 mt-1">{{ $stats['total_students'] }}</p>
                        <p class="text-[11px] text-gray-500 mt-0.5">Mahasiswa mendaftar</p>
                    </div>
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Card 2: Diterima / Aktif -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 border-l-4 border-l-emerald-500 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Diterima / Aktif</p>
                        <p class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['total_accepted'] }}</p>
                        <p class="text-[11px] text-gray-500 mt-0.5">Sedang menjalani magang</p>
                    </div>
                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Card 3: Lulus & Lengkap -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 border-l-4 border-l-teal-600 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Selesai Magang</p>
                        <p class="text-2xl font-black text-teal-700 mt-1">{{ $stats['total_completed'] }}</p>
                        <p class="text-[11px] text-gray-500 mt-0.5">Laporan & nilai tuntas</p>
                    </div>
                    <div class="p-3 bg-teal-50 text-teal-700 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                </div>

                <!-- Card 4: Menunggu Seleksi -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 border-l-4 border-l-amber-500 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Menunggu Seleksi</p>
                        <p class="text-2xl font-black text-amber-600 mt-1">{{ $stats['total_pending'] }}</p>
                        <p class="text-[11px] text-gray-500 mt-0.5">Dalam proses verifikasi</p>
                    </div>
                    <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

            </div>

            <!-- Card Sebaran Mahasiswa per Dinas Penempatan Pemkot Surabaya -->
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h4 class="font-bold text-gray-900 text-base flex items-center gap-2">
                            <span>🏢 Sebaran Penempatan Mahasiswa di Instansi Pemkot Surabaya</span>
                        </h4>
                        <p class="text-xs text-gray-500 mt-0.5">Distribusi mahasiswa magang asal kampus pada masing-masing dinas pemerintah kota</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach ($agencyDistribution as $dist)
                        <div class="p-4 rounded-xl border border-gray-100 bg-slate-50 hover:bg-white hover:shadow-sm transition">
                            <div class="flex justify-between items-start mb-2">
                                <h5 class="font-bold text-sm text-gray-800 line-clamp-1">{{ $dist['name'] }}</h5>
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-800 text-xs font-extrabold rounded-md">
                                    {{ $dist['count'] }} Mahasiswa
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-3 overflow-hidden">
                                <div class="bg-indigo-600 h-2 rounded-full transition-all duration-500" style="width: {{ $dist['percentage'] }}%"></div>
                            </div>
                            <div class="flex justify-between items-center text-[11px] text-gray-500 mt-1.5">
                                <span>Porsi Penempatan</span>
                                <span class="font-bold text-gray-700">{{ $dist['percentage'] }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Filter & Search Panel -->
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
                <form method="GET" action="{{ route('university.dashboard') }}" class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1">
                        <input type="text" name="search" value="{{ request('search') }}" 
                            placeholder="Cari nama mahasiswa, NIM, atau jurusan..." 
                            class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-xs">
                    </div>
                    <div class="w-full sm:w-64">
                        <select name="agency_id" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-xs">
                            <option value="">-- Semua Dinas / Instansi --</option>
                            @foreach ($agencies as $ag)
                                <option value="{{ $ag->id }}" {{ request('agency_id') == $ag->id ? 'selected' : '' }}>
                                    {{ $ag->agency_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-primary-button type="submit" class="text-xs">
                            {{ __('Filter') }}
                        </x-primary-button>
                        @if (request()->hasAny(['search', 'agency_id']))
                            <a href="{{ route('university.dashboard') }}" class="px-3 py-2 text-xs font-bold text-gray-600 hover:text-gray-900">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Tabel Rekapitulasi Mahasiswa Kampus -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <div>
                        <h4 class="font-bold text-gray-900 text-base">Daftar Mahasiswa Magang ({{ $allApplications->count() }})</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Seluruh mahasiswa terdaftar asal {{ $university->name ?? $user->name }}</p>
                    </div>

                    <a href="{{ route('university.students.export') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>📥 Export Data Magang (Excel/CSV)</span>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 uppercase text-[11px] font-bold tracking-wider">
                            <tr>
                                <th class="px-6 py-3.5">Mahasiswa</th>
                                <th class="px-6 py-3.5">Jurusan / NIM</th>
                                <th class="px-6 py-3.5">Instansi & Unit Kerja</th>
                                <th class="px-6 py-3.5">Status Pengajuan</th>
                                <th class="px-6 py-3.5">Dosen DPL</th>
                                <th class="px-6 py-3.5">Mentor Dinas</th>
                                <th class="px-6 py-3.5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($allApplications as $app)
                                @php
                                    $student = $app->user;
                                    $placement = $app->placement;
                                    $dosen = $placement?->academicAdvisor;
                                    $mentor = $placement?->mentor ?? $placement?->pembimbing;
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">{{ $student->name }}</div>
                                        <div class="text-xs text-gray-500 font-mono">{{ $student->email }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-800">{{ $student->studentProfile->jurusan ?? '-' }}</div>
                                        <div class="text-xs text-gray-500 font-mono">NIM: {{ $student->studentProfile->nim ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-indigo-900">{{ $app->unit->agencyProfile->agency_name ?? '-' }}</div>
                                        <div class="text-xs text-gray-600">{{ $app->unit->name ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full 
                                            {{ $app->status === 'accepted' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                            {{ $app->status === 'verified' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $app->status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}
                                            {{ $app->status === 'rejected' ? 'bg-rose-100 text-rose-800' : '' }}">
                                            {{ strtoupper($app->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($dosen)
                                            <div class="font-semibold text-gray-900 text-xs">👨‍🏫 {{ $dosen->name }}</div>
                                            <div class="text-[11px] text-gray-500 font-mono">{{ $dosen->email }}</div>
                                        @else
                                            <span class="text-xs text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md font-semibold">
                                                Belum Ditentukan
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($mentor)
                                            <div class="font-semibold text-gray-900 text-xs">👔 {{ $mentor->name }}</div>
                                            <div class="text-[11px] text-gray-500 font-mono">{{ $mentor->email }}</div>
                                        @else
                                            <span class="text-xs text-gray-400">Belum Diplot</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            @if (in_array(strtolower($app->lifecycle_status ?? $app->status), ['accepted', 'active', 'completed']))
                                                <a href="{{ route('university.students.letter', $app->id) }}" 
                                                   target="_blank"
                                                   title="Cetak Surat Tugas / Pengantar Magang Resmi A4"
                                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-bold rounded-lg transition shadow-2xs">
                                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    <span>Surat Tugas</span>
                                                </a>
                                            @endif

                                            @if ($placement)
                                                <a href="{{ route('university.students.show', $placement->id) }}" 
                                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-bold rounded-lg transition shadow-2xs">
                                                    <span>Detail</span>
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500 text-xs">
                                        Belum ada data pengajuan magang dari mahasiswa kampus ini.
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
