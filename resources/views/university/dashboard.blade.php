<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            {{ __('Portal Monitoring Magang — ') . ($university->name ?? $user->name) }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8 bg-[#F5F8FC] min-h-screen text-slate-900 font-sans overflow-x-hidden" 
         x-data="{ 
            assignModal: { show: false, appId: '', studentName: '', currentAdvisorId: '' } 
         }">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Message -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-2xl shadow-xs flex items-center justify-between text-emerald-900 text-xs sm:text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-2xl shadow-xs text-rose-900 text-xs sm:text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Welcome Banner Resmi Universitas -->
            <div class="bg-gradient-to-r from-blue-700 via-blue-800 to-blue-950 rounded-2xl sm:rounded-3xl p-5 sm:p-7 text-white shadow-lg flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="px-2.5 py-0.5 text-[10px] sm:text-xs font-black bg-blue-400/30 border border-blue-300/40 rounded-full tracking-wider uppercase">
                            Akun Resmi Perguruan Tinggi
                        </span>
                        @if($university?->code)
                            <span class="px-2 py-0.5 text-[10px] sm:text-xs font-mono font-bold bg-white/20 rounded-md">
                                {{ $university->code }}
                            </span>
                        @endif
                    </div>
                    <h3 class="text-xl sm:text-2xl font-black">{{ $university->name ?? $user->name }}</h3>
                    <p class="text-blue-100 text-xs sm:text-sm mt-1 max-w-2xl leading-relaxed">
                        Pemantauan partisipasi, distribusi penempatan dinas, dan evaluasi mahasiswa magang di Pemerintah Kota Surabaya.
                    </p>
                </div>
                <div class="hidden sm:block text-right shrink-0">
                    <p class="text-xs text-blue-200">Email Resmi Instansi</p>
                    <p class="text-xs font-mono font-bold text-white mt-0.5">{{ $user->email }}</p>
                </div>
            </div>

            <!-- Metrik Statistik Utama Kampus (Mobile: 2 Kolom, Desktop: 4 Kolom) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-xs border border-slate-100 border-l-4 border-l-blue-600 flex flex-col justify-between">
                    <span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider">Total Pendaftar</span>
                    <p class="text-xl sm:text-2xl font-black text-slate-800 mt-1">{{ $stats['total_students'] ?? 0 }}</p>
                    <span class="text-[10px] sm:text-[11px] text-slate-500 mt-0.5">Mahasiswa terdaftar</span>
                </div>

                <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-xs border border-slate-100 border-l-4 border-l-emerald-500 flex flex-col justify-between">
                    <span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider">Diterima / Aktif</span>
                    <p class="text-xl sm:text-2xl font-black text-emerald-600 mt-1">{{ $stats['total_accepted'] ?? 0 }}</p>
                    <span class="text-[10px] sm:text-[11px] text-slate-500 mt-0.5">Sedang magang</span>
                </div>

                <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-xs border border-slate-100 border-l-4 border-l-teal-600 flex flex-col justify-between">
                    <span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider">Selesai Magang</span>
                    <p class="text-xl sm:text-2xl font-black text-teal-700 mt-1">{{ $stats['total_completed'] ?? 0 }}</p>
                    <span class="text-[10px] sm:text-[11px] text-slate-500 mt-0.5">Lulus & dinilai</span>
                </div>

                <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-xs border border-slate-100 border-l-4 border-l-amber-500 flex flex-col justify-between">
                    <span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider">Menunggu Seleksi</span>
                    <p class="text-xl sm:text-2xl font-black text-amber-600 mt-1">{{ $stats['total_pending'] ?? 0 }}</p>
                    <span class="text-[10px] sm:text-[11px] text-slate-500 mt-0.5">Proses verifikasi</span>
                </div>
            </div>

            <!-- Card Sebaran Mahasiswa per Dinas Penempatan Pemkot Surabaya -->
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-100 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h4 class="font-bold text-slate-900 text-sm sm:text-base">
                        Sebaran Penempatan Mahasiswa di Instansi Pemkot Surabaya
                    </h4>
                    <p class="text-[11px] sm:text-xs text-slate-400 mt-0.5">
                        Distribusi mahasiswa magang asal kampus pada masing-masing dinas pemerintah kota
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4">
                    @forelse ($agencyDistribution as $dist)
                        <div class="bg-slate-50/70 p-3.5 sm:p-4 rounded-xl border border-slate-100 flex flex-col justify-between">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <h4 class="text-xs font-semibold text-slate-800 leading-snug line-clamp-2" title="{{ $dist['name'] }}">
                                    {{ $dist['name'] }}
                                </h4>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 whitespace-nowrap shrink-0">
                                    {{ $dist['count'] ?? 0 }} Mhs
                                </span>
                            </div>
                            <div class="mt-2">
                                <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-300" style="width: {{ $dist['percentage'] ?? 0 }}%"></div>
                                </div>
                                <span class="text-[10px] text-slate-400 mt-1 block">Porsi: {{ $dist['percentage'] ?? 0 }}%</span>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-3 text-center py-6 text-xs text-slate-400">
                            Belum ada data sebaran penempatan dinas.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Filter & Search Panel -->
            <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-xs">
                <form method="GET" action="{{ route('university.dashboard') }}" class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1">
                        <input type="text" name="search" value="{{ request('search') }}" 
                            placeholder="Cari nama mahasiswa, NIM, atau jurusan..." 
                            class="w-full text-xs sm:text-sm border-slate-200 bg-slate-50 focus:bg-white rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-xs">
                    </div>
                    <div class="w-full sm:w-64">
                        <select name="agency_id" class="w-full text-xs sm:text-sm border-slate-200 bg-slate-50 focus:bg-white rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-xs">
                            <option value="">-- Semua Dinas / Instansi --</option>
                            @foreach ($agencies as $ag)
                                <option value="{{ $ag->id }}" {{ request('agency_id') == $ag->id ? 'selected' : '' }}>
                                    {{ $ag->agency_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-xs">
                            Filter
                        </button>
                        @if (request()->hasAny(['search', 'agency_id']))
                            <a href="{{ route('university.dashboard') }}" class="px-3 py-2 text-xs font-bold text-slate-500 hover:text-slate-800">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Daftar Mahasiswa Magang -->
<div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-100 shadow-xs overflow-hidden">
    <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <h4 class="font-bold text-slate-900 text-sm sm:text-base">
                Daftar Mahasiswa Magang ({{ $allApplications->count() }})
            </h4>
            <p class="text-[11px] sm:text-xs text-slate-400 mt-0.5">
                Seluruh mahasiswa terdaftar asal {{ $university->name ?? $user->name }}
            </p>
        </div>

        <a href="{{ route('university.students.export') }}" 
           class="inline-flex items-center justify-center gap-2 px-3.5 py-2 sm:px-4 sm:py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold rounded-xl shadow-xs transition w-full sm:w-auto">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Export Data Magang (Excel/CSV)</span>
        </a>
    </div>

    <!-- 1. TAMPILAN KHUSUS MOBILE (Clean & Ringkas: Nama, NIM, Tombol Detail) -->
    <div class="block sm:hidden divide-y divide-slate-100">
        @forelse ($allApplications as $app)
            @php
                $student = $app->user;
            @endphp
            <div class="px-4 py-3 flex items-center justify-between gap-3 hover:bg-slate-50/60 transition">
                <!-- Info Kiri: Nama & NIM -->
                <div class="min-w-0 flex-1">
                    <p class="font-bold text-xs text-slate-800 truncate leading-snug">
                        {{ $student->name }}
                    </p>
                    <p class="text-[11px] text-slate-400 font-mono mt-0.5">
                        NIM: {{ $student->studentProfile->nim ?? '-' }}
                    </p>
                </div>

                <!-- Aksi Kanan: Tombol Detail Langsung -->
                <div class="shrink-0">
                    <a href="{{ route('university.students.show', $app->id) }}"
                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 active:bg-blue-200 text-blue-700 text-xs font-semibold rounded-xl border border-blue-100 transition">
                        <span>Detail</span>
                        <span class="text-xs">&rarr;</span>
                    </a>
                </div>
            </div>
        @empty
            <div class="py-8 text-center text-slate-400 text-xs">
                Belum ada data pengajuan magang.
            </div>
        @endforelse
    </div>

    <!-- 2. TAMPILAN KHUSUS DESKTOP (Tabel Lengkap Bawaan Tetap Dipertahankan) -->
    <div class="hidden sm:block overflow-x-auto w-full">
        <table class="min-w-full divide-y divide-slate-100 text-left text-xs sm:text-sm">
            <thead class="bg-slate-50/80 border-b border-slate-200 text-slate-600 uppercase text-[11px] font-bold tracking-wider">
                <tr>
                    <th class="px-5 py-3.5 whitespace-nowrap">Mahasiswa</th>
                    <th class="px-5 py-3.5 whitespace-nowrap">Jurusan / NIM</th>
                    <th class="px-5 py-3.5 whitespace-nowrap">Instansi & Unit Kerja</th>
                    <th class="px-5 py-3.5 whitespace-nowrap text-center">Status Magang</th>
                    <th class="px-5 py-3.5 whitespace-nowrap">Dosen DPL</th>
                    <th class="px-5 py-3.5 whitespace-nowrap">Mentor Dinas</th>
                    <th class="px-5 py-3.5 whitespace-nowrap text-center min-w-[100px]">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($allApplications as $app)
                    @php
                        $student = $app->user;
                        $placement = $app->placement;
                        $dosen = $placement?->academicAdvisor;
                        $mentor = $placement?->mentor ?? $placement?->pembimbing;

                        $status = strtoupper($app->lifecycle_status ?? $app->status);
                        $today = \Carbon\Carbon::now();
                        $start = $app->start_date ? \Carbon\Carbon::parse($app->start_date) : null;

                        if (in_array($status, ['ACCEPTED', 'VERIFIED']) && $start && $today->gte($start)) {
                            $status = 'ACTIVE';
                        }
                        if (($app->finalReport?->status === 'approved' || strtoupper($app->finalReport?->status ?? '') === 'APPROVED') || ($placement?->finalreport?->status === 'approved')) {
                            if ((($app->evaluation?->nilai_akademik ?? 0) > 0) || (($placement?->evaluation?->nilai_akademik ?? 0) > 0)) {
                                $status = 'COMPLETED';
                            }
                        }
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-4">
                            <div class="font-bold text-slate-900">{{ $student->name }}</div>
                            <div class="text-xs text-slate-400 font-mono">{{ $student->email }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-medium text-slate-800">{{ $student->studentProfile->jurusan ?? '-' }}</div>
                            <div class="text-xs text-slate-400 font-mono">NIM: {{ $student->studentProfile->nim ?? '-' }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-bold text-blue-900">{{ $app->unit->agencyProfile->agency_name ?? '-' }}</div>
                            <div class="text-xs text-slate-500">{{ $app->unit->name ?? '-' }}</div>
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($status === 'SUBMITTED' || $status === 'PENDING')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                    Menunggu Verifikasi
                                </span>
                            @elseif($status === 'ACCEPTED' || $status === 'VERIFIED')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-300">
                                    Diterima
                                </span>
                            @elseif($status === 'ACTIVE')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    <span>Sedang Magang</span>
                                </span>
                            @elseif($status === 'COMPLETED')
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-300">
                                    <span>Lulus</span>
                                </span>
                            @elseif($status === 'REJECTED')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                    Ditolak
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-300">{{ $status }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if ($dosen)
                                <div class="font-semibold text-slate-900 text-xs">{{ $dosen->name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $dosen->email }}</div>
                                <button type="button" 
                                        @click="assignModal = { show: true, appId: '{{ $app->id }}', studentName: '{{ addslashes($student->name) }}', currentAdvisorId: '{{ $dosen->id }}' }"
                                        class="mt-1 text-[11px] text-blue-600 hover:text-blue-800 font-bold underline cursor-pointer">
                                    Ganti DPL
                                </button>
                            @else
                                <div>
                                    <span class="text-xs text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md font-semibold">
                                        Belum Ditentukan
                                    </span>
                                </div>
                                <button type="button" 
                                        @click="assignModal = { show: true, appId: '{{ $app->id }}', studentName: '{{ addslashes($student->name) }}', currentAdvisorId: '' }"
                                        class="mt-1 text-[11px] text-blue-600 hover:text-blue-800 font-bold bg-blue-50 hover:bg-blue-100 border border-blue-200 px-2 py-0.5 rounded-md inline-block cursor-pointer">
                                    + Pilih DPL
                                </button>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if ($mentor)
                                <div class="font-semibold text-slate-900 text-xs">{{ $mentor->name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $mentor->email }}</div>
                            @else
                                <span class="text-xs text-slate-400">Belum Diplot</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-center">
                            <a href="{{ route('university.students.show', $app->id) }}"
                               class="inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg text-xs font-semibold transition">
                                <span>Detail</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-400 text-xs">
                            Belum ada data pengajuan magang dari mahasiswa kampus ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

        <!-- ========================================== -->
        <!-- MODAL: PLOTTING / TUGASKAN DOSEN PEMBIMBING -->
        <!-- ========================================== -->
        <div x-show="assignModal.show" 
             x-cloak 
             style="display: none;"
             class="fixed inset-0 z-[9999] overflow-y-auto p-4 sm:p-6 flex items-center justify-center bg-slate-900/70 backdrop-blur-xs transition-opacity"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 sm:p-7 border border-slate-100 relative my-auto max-h-[90vh] overflow-y-auto"
                 @click.outside="assignModal.show = false"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <div class="flex justify-between items-center border-b border-slate-100 pb-3 mb-4">
                    <div>
                        <h3 class="font-bold text-base text-slate-900">Plotting Dosen Pembimbing Lapangan</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Mahasiswa: <strong class="text-slate-700" x-text="assignModal.studentName"></strong></p>
                    </div>
                    <button type="button" @click="assignModal.show = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
                </div>

                <form method="POST" :action="'/university/students/' + assignModal.appId + '/assign-advisor'" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Pilih Dosen Pembimbing (DPL) <span class="text-rose-500">*</span>
                        </label>
                        <select name="academic_advisor_id" x-model="assignModal.currentAdvisorId" required class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                            <option value="">-- Pilih Dosen Pembimbing Kampus --</option>
                            @foreach ($availableDosens as $d)
                                <option value="{{ $d->id }}">
                                    {{ $d->name }} ({{ $d->email }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Daftar memuat seluruh dosen yang terdaftar di {{ $university?->name ?? 'kampus Anda' }}.</p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="assignModal.show = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer">
                            Simpan Penugasan DPL
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>