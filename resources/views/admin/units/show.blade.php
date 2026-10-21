<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                {{-- Breadcrumb Navigasi Kedinasan --}}
                <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1.5" aria-label="Breadcrumb">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition">Dashboard</a>
                    <span class="text-slate-400">/</span>
                    @if(Auth::user()->isBidangAdmin())
                        <span class="text-slate-800 font-semibold truncate">Bidang Saya</span>
                    @else
                        <a href="{{ route('admin.units.index') }}" class="hover:text-blue-600 transition">Bidang Devisi</a>
                        <span class="text-slate-400">/</span>
                        <span class="text-slate-800 font-semibold truncate">{{ $unit->name }}</span>
                    @endif
                </nav>

                <div class="flex items-center gap-2.5">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                        {{ $unit->name }}
                    </h1>
                </div>

                <div class="text-xs text-slate-600 mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                    <span class="font-semibold text-slate-800">
                        {{ $unit->agencyProfile->agency_name ?? 'Dinas Perpustakaan dan Kearsipan' }}
                    </span>
                    <span class="text-slate-300">&bull;</span>
                    <span>{{ $unit->agencyProfile->government_name ?? 'Pemerintah Kota Surabaya' }}</span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full sm:w-auto shrink-0">
                <a href="{{ route('admin.dashboard') }}"
                   class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 shadow-2xs transition min-h-[40px]">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>{{ Auth::user()->isBidangAdmin() ? 'Dashboard Bidang' : 'Dashboard Utama' }}</span>
                </a>
                <a href="{{ route('admin.units.edit', $unit->id) }}"
                   class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-2xs transition min-h-[40px]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span>Edit Pengaturan Bidang</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- 1. LIMA KARTU METRIK RESMI BIDANG (STYLE DINAS SIMPEL & NETRAL) --}}
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3.5 sm:gap-4">
                {{-- Kuota Total --}}
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Kuota Total</div>
                        <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-2" id="stat-total-quota">{{ $stats['total_quota'] }}</div>
                    </div>
                    <div class="text-[11px] text-slate-500 mt-3 pt-2.5 border-t border-slate-100">
                        Slot Alokasi Resmi
                    </div>
                </div>

                {{-- Mahasiswa Aktif --}}
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Mahasiswa Aktif</div>
                        <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-2">{{ $stats['active_students'] }}</div>
                    </div>
                    <div class="text-[11px] text-slate-500 mt-3 pt-2.5 border-t border-slate-100">
                        Sedang Berjalan
                    </div>
                </div>

                {{-- Sisa Kuota --}}
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            Sisa Kuota
                        </div>
                        <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-2" id="stat-remaining-quota">
                            {{ $stats['remaining_quota'] }}
                        </div>
                    </div>
                    <div class="text-[11px] text-slate-500 mt-3 pt-2.5 border-t border-slate-100">
                        Slot Tersedia
                    </div>
                </div>

                {{-- Menunggu ACC --}}
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Menunggu ACC</div>
                        <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-2">{{ $stats['waiting_approval'] }}</div>
                    </div>
                    <div class="text-[11px] text-slate-500 mt-3 pt-2.5 border-t border-slate-100">
                        Proses Seleksi / ACC
                    </div>
                </div>

                {{-- Mahasiswa Lulus --}}
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs col-span-2 md:col-span-1 flex flex-col justify-between">
                    <div>
                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Mahasiswa Lulus</div>
                        <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-2">{{ $stats['completed_students'] }}</div>
                    </div>
                    <div class="text-[11px] text-slate-500 mt-3 pt-2.5 border-t border-slate-100">
                        Alumni Program
                    </div>
                </div>
            </div>

            {{-- 2. BAR UTILISASI KUOTA BIDANG & PENYESUAIAN KUOTA --}}
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs space-y-4">
                <div>
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-700 mb-2">
                        <span>Tingkat Keterisian Kuota Bidang</span>
                        <span id="unit-occupancy-label" class="font-bold text-slate-900">
                            {{ $stats['active_students'] }} dari <span id="unit-total-quota-display">{{ $stats['total_quota'] }}</span> kuota terpakai (<span id="unit-occupancy-percent">{{ $stats['occupancy_rate'] }}</span>%)
                        </span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                        <div id="unit-occupancy-bar" class="bg-blue-600 h-2.5 rounded-full transition-all duration-500"
                             style="width: {{ max(min($stats['occupancy_rate'], 100), ($stats['active_students'] > 0 ? 3 : 0)) }}%">
                        </div>
                    </div>
                </div>

                {{-- Fitur Tambah / Penyesuaian Cepat Kuota Khusus Bidang Ini --}}
                <div class="pt-3.5 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-1.5 sm:gap-2">
                        <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            Penyesuaian Kuota Bidang:
                        </span>
                        <span class="text-[11px] text-slate-500">Ketik angka langsung atau klik tombol +/- untuk penyesuaian instan</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="quota-ctrl inline-flex items-center gap-1 bg-slate-50 p-1 rounded-xl border border-slate-200 shadow-2xs relative">
                            <!-- Kurang (-1) -->
                            <button type="button" 
                                    onclick="adjustUnitQuota(-1)"
                                    title="Kurangi Kuota (-1)" 
                                    class="w-8 h-8 flex items-center justify-center bg-white hover:bg-slate-100 text-slate-700 font-bold rounded-lg border border-slate-200 transition text-sm shadow-2xs active:scale-95 cursor-pointer">
                                -
                            </button>

                            <!-- Input Number Editable Langsung -->
                            <input type="number" 
                                   id="unit-quota-input"
                                   name="quota" 
                                   value="{{ $unit->quota }}" 
                                   min="{{ $stats['active_students'] }}" 
                                   max="500"
                                   class="w-16 text-center border-slate-300 rounded-md text-sm font-bold text-slate-800 focus:ring-blue-500 focus:border-blue-500 py-1 px-1 bg-white shadow-2xs"
                                   data-current-val="{{ $unit->quota }}"
                                   data-filled="{{ $stats['active_students'] }}"
                                   onchange="updateUnitQuotaValue(this.value)"
                                   onkeydown="if(event.key === 'Enter'){ this.blur(); }"
                                   aria-label="Atur Kuota {{ $unit->name }}">

                            <!-- Tambah (+1) -->
                            <button type="button" 
                                    onclick="adjustUnitQuota(1)"
                                    title="Tambah Kuota (+1)" 
                                    class="w-8 h-8 flex items-center justify-center bg-white hover:bg-slate-100 text-slate-700 font-bold rounded-lg border border-slate-200 transition text-sm shadow-2xs active:scale-95 cursor-pointer">
                                +
                            </button>

                            <!-- Save Indicator Icon -->
                            <span id="unit-save-indicator" class="hidden absolute -top-2 -right-2 bg-emerald-500 text-white rounded-full p-0.5 shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. DAFTAR PESERTA MAGANG DI BIDANG INI --}}
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-5 sm:p-6 bg-slate-50/80 border-b border-slate-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h2 class="text-base sm:text-lg font-black text-slate-900">
                            Daftar Peserta Magang Bidang
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Data seluruh pendaftar dan peserta aktif yang ditempatkan pada {{ $unit->name }}.
                        </p>
                    </div>

                    {{-- Form Pencarian & Filter Status --}}
                    <form method="GET" action="{{ route('admin.units.show', $unit->id) }}" class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2 w-full sm:w-auto">
                        <div class="relative flex-1 sm:w-60">
                            <input type="text"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Cari mahasiswa, NIM, kampus..."
                                   class="w-full text-xs pl-8 pr-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-1 focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>

                        <div class="flex items-center gap-2">
                            <select name="status"
                                    class="flex-1 sm:w-auto text-xs py-2 pl-3 pr-8 bg-white border border-slate-200 rounded-xl focus:ring-1 focus:ring-blue-500 focus:border-blue-500 shadow-2xs cursor-pointer">
                                <option value="">Semua Status</option>
                                @foreach(\App\Enums\ApplicationStatus::cases() as $statusCase)
                                    <option value="{{ $statusCase->value }}" {{ request('status') === $statusCase->value ? 'selected' : '' }}>{{ $statusCase->label() }}</option>
                                @endforeach
                            </select>

                            <button type="submit"
                                    class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition shadow-2xs cursor-pointer">
                                Filter
                            </button>

                            @if(request()->hasAny(['search', 'status']))
                                <a href="{{ route('admin.units.show', $unit->id) }}"
                                   class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- 1. Tabel Desktop & Tablet (>= 768px) --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-100/50 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                                <th class="py-3.5 px-4 text-center w-12">No</th>
                                <th class="py-3.5 px-6">Nama Peserta & NIM</th>
                                <th class="py-3.5 px-6">Perguruan Tinggi & Program Studi</th>
                                <th class="py-3.5 px-4 text-center">Periode Magang</th>
                                <th class="py-3.5 px-4">Mentor</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-4 text-center w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($applications as $app)
                                @php
                                    $student = $app->user;
                                    $profile = $student?->studentProfile;
                                    $univName = $student?->university?->name ?? $profile?->universitas ?? '-';
                                    $jurusan = $profile?->jurusan ?? '-';
                                    $nim = $profile?->nim ?? '-';
                                    $mentor = $app->placement?->mentor;
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-4 px-4 text-center text-slate-500 font-medium">
                                        {{ $loop->iteration + ($applications->currentPage() - 1) * $applications->perPage() }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-slate-900 text-sm">
                                            {{ $student?->name ?? 'Nama Tidak Terdata' }}
                                        </div>
                                        <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                                            NIM: {{ $nim }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="font-semibold text-slate-800">
                                            {{ $univName }}
                                        </div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">
                                            {{ $jurusan }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-center text-slate-600">
                                        @if($app->start_date && $app->end_date)
                                            <div class="font-medium text-[11px]">
                                                {{ \Carbon\Carbon::parse($app->start_date)->format('d M Y') }}
                                            </div>
                                            <div class="text-[10px] text-slate-400">
                                                s.d. {{ \Carbon\Carbon::parse($app->end_date)->format('d M Y') }}
                                            </div>
                                        @else
                                            <span class="text-slate-400 italic">Belum Dijadwalkan</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4">
                                        @if($mentor)
                                            <div class="font-medium text-slate-800">{{ $mentor->name }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $mentor->email }}</div>
                                        @else
                                            <span class="text-slate-400 italic">Belum Ditugaskan</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <x-status-badge :status="$app->status" />
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <a href="{{ route('admin.applications.show', $app->id) }}"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] rounded-lg transition"
                                            title="Lihat Detail Berkas & Logbook">
                                            <span>Detail</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-slate-400">
                                        Belum ada peserta magang yang terdaftar pada bidang ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- 2. Tampilan Kartu Khusus Mobile (< 768px) --}}
                <div class="md:hidden divide-y divide-slate-100 p-4 space-y-3.5 bg-slate-50/50">
                    @forelse($applications as $app)
                        @php
                            $student = $app->user;
                            $profile = $student?->studentProfile;
                            $univName = $student?->university?->name ?? $profile?->universitas ?? '-';
                            $jurusan = $profile?->jurusan ?? '-';
                            $nim = $profile?->nim ?? '-';
                            $mentor = $app->placement?->mentor;
                        @endphp
                        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-2xs space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-bold text-slate-900 text-sm leading-snug">
                                        {{ $student?->name ?? 'Nama Tidak Terdata' }}
                                    </h4>
                                    <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                                        NIM: {{ $nim }}
                                    </div>
                                </div>
                                <div class="shrink-0">
                                    <x-status-badge :status="$app->status" />
                                </div>
                            </div>

                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs space-y-1.5">
                                <div>
                                    <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Perguruan Tinggi & Prodi</span>
                                    <span class="font-semibold text-slate-800 block">{{ $univName }}</span>
                                    <span class="text-slate-500 text-[11px] block">{{ $jurusan }}</span>
                                </div>
                                <div class="pt-1.5 border-t border-slate-200/60 flex items-center justify-between text-[11px]">
                                    <div>
                                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Periode Magang</span>
                                        @if($app->start_date && $app->end_date)
                                            <span class="text-slate-700 font-medium">
                                                {{ \Carbon\Carbon::parse($app->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($app->end_date)->format('d M Y') }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 italic">Belum Dijadwalkan</span>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Mentor</span>
                                        @if($mentor)
                                            <span class="font-semibold text-slate-800">{{ $mentor->name }}</span>
                                        @else
                                            <span class="text-slate-400 italic">Belum Ditugaskan</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="pt-1">
                                <a href="{{ route('admin.applications.show', $app->id) }}"
                                   class="w-full inline-flex items-center justify-center gap-1.5 min-h-[44px] py-2.5 px-4 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold border border-blue-200 shadow-2xs transition active:scale-95">
                                    <span>Lihat Detail Berkas & Logbook</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-slate-400 bg-white rounded-2xl border border-dashed border-slate-200">
                            Belum ada peserta magang yang terdaftar pada bidang ini.
                        </div>
                    @endforelse
                </div>

                @if($applications->hasPages())
                    <div class="p-4 bg-slate-50 border-t border-slate-200">
                        {{ $applications->links() }}
                    </div>
                @endif
            </div>

            {{-- 3. DAFTAR MENTOR YANG BERTUGAS DI BIDANG INI --}}
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-slate-900">
                            Tim Mentor Bidang
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Daftar mentor yang dialokasikan untuk mendampingi peserta di {{ $unit->name }}.
                        </p>
                    </div>
                    <a href="{{ route('admin.mentors.index') }}"
                        class="self-start sm:self-auto inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3.5 py-2 rounded-xl transition border border-blue-200 shadow-2xs">
                        <span>Kelola Tim Mentor</span>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5 pt-4">
                    @forelse($mentors as $mentor)
                        <div class="p-4 rounded-2xl bg-slate-50/70 border border-slate-200 flex items-start justify-between gap-3">
                            <div>
                                <div class="font-bold text-slate-900 text-xs sm:text-sm">{{ $mentor->name }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $mentor->email }}</div>
                                @if($mentor->nip)
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">NIP: {{ $mentor->nip }}</div>
                                @endif
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold bg-blue-100 text-blue-800 shrink-0">
                                {{ $mentor->active_students_count ?? 0 }} Mhs Aktif
                            </span>
                        </div>
                    @empty
                        <div class="col-span-full py-6 text-center text-xs text-slate-400 italic">
                            Belum ada mentor khusus yang ditugaskan pada bidang ini.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    <!-- Quick Quota AJAX Handler Script -->
    @push('scripts')
    <script>
        async function adjustUnitQuota(delta) {
            const input = document.getElementById('unit-quota-input');
            if (!input) return;
            const current = parseInt(input.value) || 0;
            const filled = parseInt(input.dataset.filled || 0);
            const nextVal = current + delta;
            
            if (nextVal < filled) {
                alert(`Kuota tidak boleh kurang dari jumlah mahasiswa yang sedang aktif (${filled} orang).`);
                return;
            }
            if (nextVal > 500) {
                alert('Maksimal kuota per bidang adalah 500 slot.');
                return;
            }
            input.value = nextVal;
            await updateUnitQuotaValue(nextVal);
        }

        async function updateUnitQuotaValue(newValue) {
            const input = document.getElementById('unit-quota-input');
            const indicator = document.getElementById('unit-save-indicator');
            if (!input) return;

            const filled = parseInt(input.dataset.filled || 0);
            let val = parseInt(newValue);

            if (isNaN(val) || val < 0) {
                val = 0;
                input.value = 0;
            }

            if (val < filled) {
                alert(`Kuota tidak boleh kurang dari jumlah mahasiswa yang sedang aktif (${filled} orang).`);
                input.value = input.dataset.currentVal;
                return;
            }

            try {
                input.classList.add('opacity-50');
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                const response = await fetch('/admin/units/{{ $unit->id }}/quota', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        custom_quota: val,
                        quota: val
                    })
                });

                const data = await response.json();
                input.classList.remove('opacity-50');

                if (response.ok && data.success) {
                    input.dataset.currentVal = val;
                    input.value = val;

                    // Show visual feedback checkmark
                    if (indicator) {
                        indicator.classList.remove('hidden');
                        setTimeout(() => {
                            indicator.classList.add('hidden');
                        }, 2000);
                    }

                    // Update Kuota Total card
                    const totalCard = document.getElementById('stat-total-quota');
                    if (totalCard) totalCard.innerText = val;

                    // Update Sisa Kuota card
                    const remainingCard = document.getElementById('stat-remaining-quota');
                    const remaining = Math.max(0, val - filled);
                    if (remainingCard) remainingCard.innerText = remaining;

                    // Update Tingkat Keterisian text & progress bar
                    const totalText = document.getElementById('unit-total-quota-display');
                    if (totalText) totalText.innerText = val;

                    const percentText = document.getElementById('unit-occupancy-percent');
                    const occupancyRate = val > 0 ? (Math.round((filled / val) * 1000) / 10) : 0;
                    if (percentText) percentText.innerText = occupancyRate;

                    const bar = document.getElementById('unit-occupancy-bar');
                    if (bar) {
                        const barWidth = Math.max(Math.min(occupancyRate, 100), (filled > 0 ? 3 : 0));
                        bar.style.width = `${barWidth}%`;
                    }
                } else {
                    alert(data.message || 'Gagal memperbarui kuota.');
                    input.value = input.dataset.currentVal;
                }
            } catch (err) {
                input.classList.remove('opacity-50');
                console.error(err);
                alert('Terjadi kesalahan jaringan saat memperbarui kuota.');
                input.value = input.dataset.currentVal;
            }
        }
    </script>
    @endpush
</x-app-layout>
