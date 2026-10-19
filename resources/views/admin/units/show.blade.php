<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                {{-- Breadcrumb Navigasi Kedinasan --}}
                <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1.5" aria-label="Breadcrumb">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition">Dashboard</a>
                    <span class="text-slate-400">/</span>
                    <a href="{{ route('admin.units.index') }}" class="hover:text-blue-600 transition">Bidang Devisi</a>
                    <span class="text-slate-400">/</span>
                    <span class="text-slate-800 font-semibold truncate">{{ $unit->name }}</span>
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

            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('admin.dashboard') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 shadow-2xs transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Dashboard Utama</span>
                </a>
                <a href="{{ route('admin.units.edit', $unit->id) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-2xs transition">
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

            {{-- 1. INFORMASI DESKRIPSI & TUGAS POKOK BIDANG --}}
            @if($unit->description)
                <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-2xs">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                        Deskripsi & Lingkup Kerja Bidang
                    </div>
                    <p class="text-xs sm:text-sm text-slate-700 leading-relaxed">
                        {{ $unit->description }}
                    </p>
                </div>
            @endif

            {{-- 2. LIMA KARTU METRIK RESMI BIDANG (STYLE DINAS SIMPEL) --}}
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3.5 sm:gap-4">
                {{-- Kuota Total --}}
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Kuota Total</div>
                        <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-2">{{ $stats['total_quota'] }}</div>
                    </div>
                    <div class="text-[11px] text-slate-500 mt-3 pt-2.5 border-t border-slate-100">
                        Slot Alokasi Resmi
                    </div>
                </div>

                {{-- Mahasiswa Aktif --}}
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Mahasiswa Aktif</div>
                        <div class="text-2xl sm:text-3xl font-black text-emerald-700 mt-2">{{ $stats['active_students'] }}</div>
                    </div>
                    <div class="text-[11px] text-slate-500 mt-3 pt-2.5 border-t border-slate-100">
                        Sedang Berjalan
                    </div>
                </div>

                {{-- Sisa Kuota --}}
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="text-[11px] font-bold {{ $stats['remaining_quota'] > 0 ? 'text-blue-700' : 'text-rose-700' }} uppercase tracking-wider">
                            Sisa Kuota
                        </div>
                        <div class="text-2xl sm:text-3xl font-black {{ $stats['remaining_quota'] > 0 ? 'text-blue-700' : 'text-rose-700' }} mt-2">
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
                        <div class="text-[11px] font-bold text-purple-700 uppercase tracking-wider">Menunggu ACC</div>
                        <div class="text-2xl sm:text-3xl font-black text-purple-700 mt-2">{{ $stats['waiting_approval'] }}</div>
                    </div>
                    <div class="text-[11px] text-slate-500 mt-3 pt-2.5 border-t border-slate-100">
                        Proses Seleksi / ACC
                    </div>
                </div>

                {{-- Mahasiswa Lulus --}}
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs col-span-2 md:col-span-1 flex flex-col justify-between">
                    <div>
                        <div class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Mahasiswa Lulus</div>
                        <div class="text-2xl sm:text-3xl font-black text-slate-800 mt-2">{{ $stats['completed_students'] }}</div>
                    </div>
                    <div class="text-[11px] text-slate-500 mt-3 pt-2.5 border-t border-slate-100">
                        Alumni Program
                    </div>
                </div>
            </div>

            {{-- 3. BAR UTILISASI KUOTA BIDANG --}}
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
                <div class="flex items-center justify-between text-xs font-semibold text-slate-700 mb-2">
                    <span>Tingkat Keterisian Kuota Bidang</span>
                    <span class="font-bold text-slate-900">
                        {{ $stats['active_students'] }} dari {{ $stats['total_quota'] }} kuota terpakai ({{ $stats['occupancy_rate'] }}%)
                    </span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500"
                         style="width: {{ max(min($stats['occupancy_rate'], 100), ($stats['active_students'] > 0 ? 3 : 0)) }}%">
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
                    <form method="GET" action="{{ route('admin.units.show', $unit->id) }}" class="flex flex-wrap items-center gap-2">
                        <div class="relative">
                            <input type="text"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Cari mahasiswa, NIM, kampus..."
                                   class="text-xs pl-8 pr-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-1 focus:ring-blue-500 focus:border-blue-500 w-48 sm:w-60 shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>

                        <select name="status"
                                class="text-xs py-2 pl-3 pr-8 bg-white border border-slate-200 rounded-xl focus:ring-1 focus:ring-blue-500 focus:border-blue-500 shadow-2xs cursor-pointer">
                            <option value="">Semua Status</option>
                            @foreach(\App\Enums\ApplicationStatus::cases() as $statusCase)
                                <option value="{{ $statusCase->value }}" {{ request('status') === $statusCase->value ? 'selected' : '' }}>{{ $statusCase->label() }}</option>
                            @endforeach
                        </select>

                        <button type="submit"
                                class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition shadow-2xs cursor-pointer">
                            Filter
                        </button>

                        @if(request()->hasAny(['search', 'status']))
                            <a href="{{ route('admin.units.show', $unit->id) }}"
                               class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition">
                                Reset
                            </a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-100/50 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                                <th class="py-3.5 px-4 text-center w-12">No</th>
                                <th class="py-3.5 px-6">Nama Peserta & NIM</th>
                                <th class="py-3.5 px-6">Perguruan Tinggi & Program Studi</th>
                                <th class="py-3.5 px-4 text-center">Periode Magang</th>
                                <th class="py-3.5 px-4">Pembimbing (Mentor)</th>
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

                @if($applications->hasPages())
                    <div class="p-4 bg-slate-50 border-t border-slate-200">
                        {{ $applications->links() }}
                    </div>
                @endif
            </div>

            {{-- 5. DAFTAR PEMBIMBING (MENTOR) YANG BERTUGAS DI BIDANG INI --}}
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-slate-900">
                            Tim Pembimbing Lapangan (Mentor)
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Daftar pegawai pembimbing yang dialokasikan untuk mendampingi peserta di {{ $unit->name }}.
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
                            Belum ada pembimbing (mentor) khusus yang ditugaskan pada bidang ini.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
