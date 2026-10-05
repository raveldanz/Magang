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

            <!-- Metrik Statistik Utama Kampus (Putih Polos) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between"
                     style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Total Pendaftar</span>
                    <div class="text-2xl font-black text-slate-800">{{ $stats['total_students'] ?? 0 }}</div>
                    <div class="text-[11px] text-slate-500 mt-1">Mahasiswa terdaftar</div>
                </div>
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between"
                     style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Total Diterima</span>
                    <div class="text-2xl font-black text-slate-800">{{ $stats['total_accepted'] ?? 0 }}</div>
                    <div class="text-[11px] text-slate-500 mt-1">ACCEPTED, ACTIVE, dan COMPLETED</div>
                </div>
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between"
                     style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">{{ \App\Enums\ApplicationStatus::COMPLETED->label() }}</span>
                    <div class="text-2xl font-black text-slate-800">{{ $stats['total_completed'] ?? 0 }}</div>
                    <div class="text-[11px] text-slate-500 mt-1">{{ \App\Enums\ApplicationStatus::COMPLETED->description() }}</div>
                </div>
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between"
                     style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Menunggu Seleksi</span>
                    <div class="text-2xl font-black text-slate-800">{{ $stats['total_pending'] ?? 0 }}</div>
                    <div class="text-[11px] text-slate-500 mt-1">PENDING dan VERIFIED</div>
                </div>
            </div>

            <!-- Card Sebaran Mahasiswa per Dinas (Simpel, seperti dashboard admin) -->
            <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-100 shadow-sm space-y-4"
                 style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;"
                 x-data="{ showAll: {{ $activeAgenciesCount > 0 ? 'false' : 'true' }} }">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-bold text-slate-800 leading-snug">Sebaran Penempatan Mahasiswa</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Sebaran mahasiswa magang di dinas Pemkot Surabaya</p>
                    </div>
                    @if($totalAgenciesCount > $activeAgenciesCount)
                        <button type="button" @click="showAll = !showAll"
                                class="self-start sm:self-auto shrink-0 whitespace-nowrap inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-xl transition border border-blue-200 cursor-pointer">
                            <span x-text="showAll ? 'Hanya Dinas Terisi' : 'Semua Dinas'"></span>
                        </button>
                    @endif
                </div>

                <div class="space-y-4 pt-1">
                    @if($activeAgenciesCount === 0)
                        <div x-show="!showAll" class="text-center py-6 text-xs text-slate-400">Belum ada mahasiswa yang ditempatkan di dinas.</div>
                    @endif
                    @foreach ($agencyDistribution as $dist)
                        @php $hasStudents = $dist['count'] > 0; @endphp
                        <div @if(!$hasStudents) x-show="showAll" x-cloak @endif>
                            <div class="flex items-center justify-between text-xs font-semibold mb-1.5 gap-2">
                                <span class="text-slate-700 font-medium truncate flex-1 min-w-0">{{ $dist['name'] }}</span>
                                <span class="text-blue-700 font-bold shrink-0">
                                    {{ $dist['count'] }} Mahasiswa
                                    <span class="text-slate-400 font-normal">({{ $dist['percentage'] }}%)</span>
                                </span>
                            </div>
                            <div class="w-full rounded-full overflow-hidden mt-1.5"
                                 style="background-color: #f1f5f9; height: 8px; border-radius: 9999px; overflow: hidden;">
                                <div class="h-2 rounded-full transition-all duration-500"
                                     style="background-color: #2563eb; height: 8px; border-radius: 9999px; width: {{ max((float) $dist['percentage'], 2) }}%"></div>
                            </div>
                        </div>
                    @endforeach
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
                Daftar Mahasiswa Magang
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
                        Detail
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
                    <th class="px-4 py-3.5 whitespace-nowrap">Mahasiswa</th>
                    <th class="px-4 py-3.5 whitespace-nowrap">Jurusan / NIM</th>
                    <th class="px-4 py-3.5 whitespace-nowrap">Instansi & Unit Kerja</th>
                    <th class="px-4 py-3.5 whitespace-nowrap text-center">Status Magang</th>
                    <th class="px-4 py-3.5 whitespace-nowrap">Dosen DPL</th>
                    <th class="px-4 py-3.5 whitespace-nowrap">Mentor Dinas</th>
                    <th class="py-3.5 whitespace-nowrap text-center" style="padding-left: 12px; padding-right: 32px;">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($allApplications as $app)
                    @php
                        $student = $app->user;
                        $placement = $app->placement;
                        $dosen = $placement?->academicAdvisor;
                        $mentor = $placement?->mentor ?? $placement?->pembimbing;

                        $appStatus = $app->status;
                        $rawStatus = $appStatus instanceof \App\Enums\ApplicationStatus ? $appStatus->value : strtolower((string)$appStatus);
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-4 py-4">
                            <div class="font-bold text-slate-900">{{ $student->name }}</div>
                            <div class="text-xs text-slate-400 font-mono">{{ $student->email }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-medium text-slate-800">{{ $student->studentProfile->jurusan ?? '-' }}</div>
                            <div class="text-xs text-slate-400 font-mono">NIM: {{ $student->studentProfile->nim ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-bold text-blue-900">{{ $app->unit->agencyProfile->agency_name ?? '-' }}</div>
                            <div class="text-xs text-slate-500">{{ $app->unit->name ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <x-status-badge :status="$app->status" />
                        </td>
                        <td class="px-4 py-4">
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
                        <td class="px-4 py-4">
                            @if ($mentor)
                                <div class="font-semibold text-slate-900 text-xs">{{ $mentor->name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $mentor->email }}</div>
                            @else
                                <span class="text-xs text-slate-400">Belum Diplot</span>
                            @endif
                        </td>
                        <td class="py-4 whitespace-nowrap text-center" style="padding-left: 12px; padding-right: 32px;">
                            <a href="{{ route('university.students.show', $app->id) }}"
                               class="inline-flex items-center justify-center px-4 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg text-xs font-semibold transition">
                                Detail
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
                            Pilih Dosen Pembimbing <span class="text-rose-500">*</span>
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