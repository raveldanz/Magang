@php
    $activeTab = $tab ?? 'active';
    $search = $search ?? '';

    $tabs = [
        'active' => ['label' => 'Bimbingan Aktif', 'count' => $stats['active_students']],
        'upcoming' => ['label' => 'Calon Peserta', 'count' => $stats['upcoming_students']],
        'completed' => ['label' => 'Alumni Selesai', 'count' => $stats['completed_students']],
        'all' => ['label' => 'Semua Mahasiswa', 'count' => $stats['total_students']],
    ];

    $tableTitles = [
        'active' => ['Daftar Mahasiswa Bimbingan Aktif', 'Verifikasi logbook harian dan pantau progres mahasiswa yang sedang magang.'],
        'upcoming' => ['Daftar Calon Peserta Magang', 'Mahasiswa yang telah diterima dan menunggu tanggal mulai magang.'],
        'completed' => ['Arsip Alumni Magang', 'Mahasiswa yang telah menyelesaikan program magang di unit Anda.'],
        'all' => ['Seluruh Mahasiswa Bimbingan', 'Rekap seluruh mahasiswa yang pernah dan sedang Anda bimbing.'],
    ];
    [$tableTitle, $tableSubtitle] = $tableTitles[$activeTab] ?? $tableTitles['active'];

    $pendingTotal = (int) $stats['pending_logbooks'];
    $grade = fn ($score) => $score >= 85 ? 'A' : ($score >= 70 ? 'B' : 'C');

    $btnBase = 'inline-flex items-center justify-center gap-1.5 h-9 px-3 text-xs font-medium rounded-lg transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-1';
    $btnSecondary = $btnBase.' border border-slate-200 bg-white hover:bg-slate-50 text-slate-700';
    $btnPrimary = $btnBase.' bg-blue-600 hover:bg-blue-700 text-white';
    $btnPrimaryOutline = $btnBase.' border border-blue-200 bg-white hover:bg-blue-50 text-blue-700';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Portal Mentor Dinas</h2>
            <p class="text-sm text-slate-500 flex flex-wrap items-center gap-2 mt-1">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="font-medium text-slate-600">{{ Auth::user()->agencyProfile->agency_name ?? 'Pemerintah Kota Surabaya' }}</span>
                <span class="hidden sm:inline-block w-px h-3.5 bg-slate-300" aria-hidden="true"></span>
                <span class="hidden sm:inline">Monitoring dan Evaluasi Mahasiswa Magang</span>
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Pesan sukses --}}
            @if (session('success'))
                <div class="flex items-center gap-2.5 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm font-medium text-emerald-800">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Ringkasan metrik --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                {{-- Bimbingan Aktif --}}
                <a href="{{ route('mentor.dashboard', ['tab' => 'active']) }}"
                   class="group bg-white rounded-xl border border-slate-200/80 p-4 sm:p-5 shadow-sm hover:border-slate-300 transition">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Bimbingan Aktif</p>
                        <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-blue-700 tabular-nums leading-none">{{ $stats['active_students'] }}</p>
                    <p class="mt-2 text-xs text-slate-500">Mahasiswa sedang menjalani magang</p>
                </a>

                {{-- Logbook Pending --}}
                <a href="{{ route('mentor.logbooks.index') }}"
                   class="group rounded-xl border p-4 sm:p-5 shadow-sm transition {{ $pendingTotal > 0 ? 'bg-amber-50/50 border-amber-200 hover:border-amber-300' : 'bg-white border-slate-200/80 hover:border-slate-300' }}">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wide {{ $pendingTotal > 0 ? 'text-amber-700' : 'text-slate-500' }}">Logbook Pending</p>
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center {{ $pendingTotal > 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold tabular-nums leading-none {{ $pendingTotal > 0 ? 'text-amber-600' : 'text-slate-800' }}">{{ $pendingTotal }}</p>
                    <p class="mt-2 text-xs flex items-center gap-1 {{ $pendingTotal > 0 ? 'text-amber-700 font-medium' : 'text-slate-500' }}">
                        @if ($pendingTotal > 0)
                            Menunggu verifikasi Anda
                            <svg class="w-3.5 h-3.5 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        @else
                            Seluruh logbook telah diverifikasi
                        @endif
                    </p>
                </a>

                {{-- Alumni Selesai --}}
                <a href="{{ route('mentor.dashboard', ['tab' => 'completed']) }}"
                   class="group bg-white rounded-xl border border-slate-200/80 p-4 sm:p-5 shadow-sm hover:border-slate-300 transition">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Alumni Selesai</p>
                        <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-emerald-700 tabular-nums leading-none">{{ $stats['completed_students'] }}</p>
                    <p class="mt-2 text-xs text-slate-500">Telah dinilai dan menyelesaikan magang</p>
                </a>

                {{-- Calon Peserta --}}
                <a href="{{ route('mentor.dashboard', ['tab' => 'upcoming']) }}"
                   class="group bg-white rounded-xl border border-slate-200/80 p-4 sm:p-5 shadow-sm hover:border-slate-300 transition">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Calon Peserta</p>
                        <span class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-slate-800 tabular-nums leading-none">{{ $stats['upcoming_students'] }}</p>
                    <p class="mt-2 text-xs text-slate-500">Menunggu jadwal mulai magang</p>
                </a>
            </div>

            {{-- Kartu utama: tab, kontrol, dan tabel mahasiswa --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

                {{-- Navigasi tab status --}}
                <nav class="border-b border-slate-200 px-4 sm:px-6 pt-3 flex gap-6 text-sm font-medium overflow-x-auto" aria-label="Filter status mahasiswa">
                    @foreach ($tabs as $key => $item)
                        @php $isActive = $activeTab === $key; @endphp
                        <a href="{{ route('mentor.dashboard', array_filter(['tab' => $key, 'q' => $search])) }}"
                           @if ($isActive) aria-current="page" @endif
                           class="-mb-px inline-flex items-center gap-2 whitespace-nowrap pb-3 border-b-2 transition {{ $isActive ? 'text-blue-600 border-blue-600' : 'text-slate-500 border-transparent hover:text-slate-700 hover:border-slate-300' }}">
                            {{ $item['label'] }}
                            <span class="min-w-[1.5rem] px-1.5 py-0.5 rounded-md text-xs font-semibold text-center tabular-nums {{ $isActive ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-500' }}">{{ $item['count'] }}</span>
                        </a>
                    @endforeach
                </nav>

                {{-- Judul daftar dan kontrol tabel --}}
                <div class="px-4 sm:px-6 py-4 flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100">
                    <div class="min-w-0">
                        <h3 class="text-base font-semibold text-slate-800">{{ $tableTitle }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $tableSubtitle }}</p>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 w-full lg:w-auto">
                        <form method="GET" action="{{ route('mentor.dashboard') }}" class="relative w-full sm:w-72" role="search">
                            <input type="hidden" name="tab" value="{{ $activeTab }}">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                            </svg>
                            <input type="search" name="q" value="{{ $search }}" placeholder="Cari nama atau NIM mahasiswa"
                                   aria-label="Cari nama atau NIM mahasiswa"
                                   class="w-full h-9 pl-9 {{ $search !== '' ? 'pr-9' : 'pr-3' }} text-xs rounded-lg border-slate-200 text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500">
                            @if ($search !== '')
                                <a href="{{ route('mentor.dashboard', ['tab' => $activeTab]) }}"
                                   class="absolute right-2 top-1/2 -translate-y-1/2 p-1 rounded text-slate-400 hover:text-slate-600 hover:bg-slate-100"
                                   title="Hapus pencarian" aria-label="Hapus pencarian">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </a>
                            @endif
                        </form>

                        <a href="{{ route('mentor.logbooks.index') }}" class="{{ $btnSecondary }} shrink-0">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                            Feed Logbook Masuk
                            @if ($pendingTotal > 0)
                                <span class="ml-0.5 px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-800 text-[11px] font-semibold tabular-nums">{{ $pendingTotal }}</span>
                            @endif
                        </a>
                    </div>
                </div>

                @if ($search !== '')
                    <div class="px-6 py-2.5 bg-slate-50 border-b border-slate-100 text-xs text-slate-600">
                        Menampilkan <span class="font-semibold text-slate-800">{{ $placements->total() }}</span> hasil untuk kata kunci
                        <span class="font-semibold text-slate-800">&ldquo;{{ $search }}&rdquo;</span>
                    </div>
                @endif

                {{-- Tampilan mobile --}}
                <div class="block sm:hidden divide-y divide-slate-100">
                    @forelse ($placements as $place)
                        @php
                            $student = $place->application?->user;
                            $profile = $student?->studentProfile;
                            $unit = $place->application?->unit;
                            $eval = $place->evaluation;
                            $totalLog = $place->logbooks->count();
                            $pendingLog = $place->logbooks->where('status', 'pending')->count();
                            $appStatus = $place->application?->status;
                            $rataRata = $eval ? round((($eval->nilai_disiplin ?? 0) + ($eval->nilai_kinerja ?? 0) + ($eval->nilai_laporan ?? 0)) / 3, 1) : null;
                        @endphp
                        <div class="p-4 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-600 font-semibold flex items-center justify-center text-xs shrink-0">
                                        {{ strtoupper(substr($student->name ?? 'M', 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-sm text-slate-800 truncate">{{ $student->name ?? '-' }}</p>
                                        <p class="text-xs text-slate-500 mt-0.5 truncate">
                                            <span class="font-mono">{{ $profile?->nim ?? '-' }}</span> &middot; {{ $profile?->universitas ?? '-' }}
                                        </p>
                                    </div>
                                </div>
                                <x-status-badge :status="$appStatus" class="shrink-0" />
                            </div>

                            <dl class="grid grid-cols-3 gap-2 text-xs rounded-lg border border-slate-100 bg-slate-50/60 p-3">
                                <div class="col-span-3">
                                    <dt class="text-slate-400">Unit</dt>
                                    <dd class="font-medium text-slate-700 truncate">{{ $unit->name ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-slate-400">Logbook</dt>
                                    <dd class="font-semibold text-slate-700 tabular-nums">{{ $totalLog }} kegiatan</dd>
                                </div>
                                <div>
                                    <dt class="text-slate-400">Verifikasi</dt>
                                    <dd class="font-medium {{ $pendingLog > 0 ? 'text-amber-700' : 'text-slate-600' }}">
                                        {{ $pendingLog > 0 ? $pendingLog.' pending' : 'Lengkap' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-slate-400">Nilai</dt>
                                    <dd class="font-semibold {{ $eval ? 'text-slate-800' : 'text-slate-400 font-normal' }}">
                                        {{ $eval ? $rataRata.' · '.$grade($rataRata) : 'Belum ada' }}
                                    </dd>
                                </div>
                            </dl>

                            <div class="grid grid-cols-2 gap-2">
                                <a href="{{ route('mentor.students.show', $place->id) }}" class="{{ $btnSecondary }}">Detail</a>
                                <a href="{{ route('mentor.evaluations.create', $place->id) }}" class="{{ $eval ? $btnPrimaryOutline : $btnPrimary }}">
                                    {{ $eval ? 'Ubah Nilai' : 'Evaluasi Nilai' }}
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center">
                            <p class="text-sm font-medium text-slate-600">
                                {{ $search !== '' ? 'Mahasiswa tidak ditemukan' : 'Belum ada mahasiswa pada kategori ini' }}
                            </p>
                            <p class="text-xs text-slate-400 mt-1">
                                {{ $search !== '' ? 'Periksa kembali nama atau NIM yang dicari.' : 'Data akan muncul sesuai status program magang mahasiswa.' }}
                            </p>
                        </div>
                    @endforelse
                </div>

                {{-- Tampilan desktop --}}
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap">
                                <th scope="col" class="py-3 px-6 min-w-[220px]">Mahasiswa</th>
                                <th scope="col" class="py-3 px-4">Unit & Periode</th>
                                <th scope="col" class="py-3 px-4">Status</th>
                                <th scope="col" class="py-3 px-4">Logbook</th>
                                <th scope="col" class="py-3 px-4">Laporan Akhir</th>
                                <th scope="col" class="py-3 px-4">Nilai Akhir</th>
                                <th scope="col" class="py-3 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse ($placements as $place)
                                @php
                                    $student = $place->application?->user;
                                    $profile = $student?->studentProfile;
                                    $unit = $place->application?->unit;
                                    $eval = $place->evaluation;
                                    $report = $place->finalreport;
                                    $totalLog = $place->logbooks->count();
                                    $pendingLog = $place->logbooks->where('status', 'pending')->count();
                                    $appStatus = $place->application?->status;
                                    $startDate = $place->application?->start_date;
                                    $endDate = $place->application?->end_date;
                                    $rataRata = $eval ? round((($eval->nilai_disiplin ?? 0) + ($eval->nilai_kinerja ?? 0) + ($eval->nilai_laporan ?? 0)) / 3, 1) : null;
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    {{-- Mahasiswa --}}
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-600 font-semibold flex items-center justify-center text-xs shrink-0">
                                                {{ strtoupper(substr($student->name ?? 'M', 0, 2)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-semibold text-slate-800 leading-snug">{{ $student->name ?? '-' }}</div>
                                                <div class="text-xs text-slate-500 mt-0.5">
                                                    <span class="font-mono">{{ $profile?->nim ?? '-' }}</span> &middot; {{ $profile?->universitas ?? '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Unit & periode --}}
                                    <td class="py-4 px-4">
                                        <div class="text-xs font-medium text-slate-700 leading-snug max-w-[200px]">{{ $unit->name ?? '-' }}</div>
                                        <div class="text-xs text-slate-400 mt-1 whitespace-nowrap">
                                            {{ $startDate ? \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') : '-' }}
                                            &ndash;
                                            {{ $endDate ? \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') : '-' }}
                                        </div>
                                    </td>

                                    {{-- Status magang --}}
                                    <td class="py-4 px-4">
                                        <x-status-badge :status="$appStatus" />
                                    </td>

                                    {{-- Logbook --}}
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-slate-800 tabular-nums">
                                            {{ $totalLog }} <span class="text-xs font-normal text-slate-500">kegiatan</span>
                                        </div>
                                        <div class="mt-1 flex items-center gap-1.5 text-xs">
                                            @if ($pendingLog > 0)
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                <span class="font-medium text-amber-700">{{ $pendingLog }} menunggu verifikasi</span>
                                            @elseif ($totalLog > 0)
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                <span class="text-slate-500">Semua terverifikasi</span>
                                            @else
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                                <span class="text-slate-400">Belum ada entri</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Laporan akhir --}}
                                    <td class="py-4 px-4">
                                        @if ($report)
                                            <x-status-badge type="review" :status="$report->status" />
                                        @else
                                            <span class="text-xs text-slate-400">Belum diunggah</span>
                                        @endif
                                    </td>

                                    {{-- Nilai akhir --}}
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        @if ($eval)
                                            <div class="flex items-center gap-2">
                                                <span class="text-base font-bold text-slate-800 tabular-nums">{{ $rataRata }}</span>
                                                <span class="w-6 h-6 rounded-md bg-blue-50 text-blue-700 text-xs font-bold flex items-center justify-center">{{ $grade($rataRata) }}</span>
                                            </div>
                                        @else
                                            <span class="text-xs text-slate-400">Belum dinilai</span>
                                        @endif
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="py-4 px-6">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('mentor.students.show', $place->id) }}" class="{{ $btnSecondary }}">
                                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Detail
                                            </a>
                                            <a href="{{ route('mentor.evaluations.create', $place->id) }}" class="{{ $eval ? $btnPrimaryOutline : $btnPrimary }} whitespace-nowrap">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                {{ $eval ? 'Ubah Nilai' : 'Evaluasi Nilai' }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-14 px-6 text-center">
                                        <div class="max-w-sm mx-auto">
                                            <div class="w-11 h-11 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    @if ($search !== '')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    @endif
                                                </svg>
                                            </div>
                                            <p class="mt-3 text-sm font-medium text-slate-700">
                                                {{ $search !== '' ? 'Mahasiswa tidak ditemukan' : 'Belum ada mahasiswa pada kategori ini' }}
                                            </p>
                                            <p class="mt-1 text-xs text-slate-400">
                                                {{ $search !== '' ? 'Periksa kembali nama atau NIM yang dicari, atau hapus kata kunci pencarian.' : 'Data akan muncul sesuai status program magang mahasiswa.' }}
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if (method_exists($placements, 'hasPages') && $placements->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $placements->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
