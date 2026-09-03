<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    {{ __('Rekapitulasi & Feed Logbook Bimbingan') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Monitoring & Verifikasi Aktivitas Harian Mahasiswa Bimbingan &bull; <strong>{{ $user->university ?? 'Perguruan Tinggi' }}</strong>
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('lecturer.monitoring.index') }}" class="px-3.5 py-2 bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 text-xs font-bold rounded-xl transition shadow-xs">
                    Mahasiswa Bimbingan
                </a>
                <a href="{{ route('lecturer.dashboard') }}" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-xs cursor-pointer">
                    Dashboard Dosen
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Success Message -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Statistik Metrik Logbook -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Total Logbook</span>
                    <div class="text-2xl font-black text-gray-900 mt-1">{{ $totalLogs }}</div>
                    <p class="text-[11px] text-gray-400 mt-0.5">Semua entri aktivitas</p>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Menunggu Persetujuan Dosen</span>
                    <div class="text-2xl font-black text-gray-900 mt-1">{{ $pendingDosenLogs }}</div>
                    <p class="text-[11px] text-gray-400 mt-0.5">Perlu diverifikasi</p>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Disetujui Dosen</span>
                    <div class="text-2xl font-black text-gray-900 mt-1">{{ $approvedDosenLogs }}</div>
                    <p class="text-[11px] text-gray-400 mt-0.5">Terverifikasi disetujui</p>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Minta Revisi</span>
                    <div class="text-2xl font-black text-gray-900 mt-1">{{ $rejectedDosenLogs }}</div>
                    <p class="text-[11px] text-gray-400 mt-0.5">Perlu perbaikan mhs</p>
                </div>
            </div>

            <!-- Filter & Search Bar -->
            <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm">
                <form method="GET" action="{{ route('lecturer.logbooks.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    
                    <!-- Filter Mahasiswa -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Mahasiswa Bimbingan:</label>
                        <select name="placement_id" onchange="this.form.submit()" class="w-full text-xs border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Semua Mahasiswa --</option>
                            @foreach ($supervisedPlacements as $pl)
                                <option value="{{ $pl->id }}" {{ request('placement_id') == $pl->id ? 'selected' : '' }}>
                                    {{ $pl->application->user->name }} ({{ $pl->application->user->studentProfile->nim ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Status Verifikasi Dosen -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Status Verifikasi Dosen:</label>
                        <select name="lecturer_status" onchange="this.form.submit()" class="w-full text-xs border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Semua Status Dosen --</option>
                            <option value="pending" {{ request('lecturer_status') === 'pending' ? 'selected' : '' }}>Menunggu (Pending)</option>
                            <option value="approved" {{ request('lecturer_status') === 'approved' ? 'selected' : '' }}>Disetujui (Approved)</option>
                            <option value="rejected" {{ request('lecturer_status') === 'rejected' ? 'selected' : '' }}>Minta Revisi (Rejected)</option>
                        </select>
                    </div>

                    <!-- Filter Status Mentor Dinas -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Status Mentor Dinas:</label>
                        <select name="mentor_status" onchange="this.form.submit()" class="w-full text-xs border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Semua Status Mentor --</option>
                            <option value="pending" {{ request('mentor_status') === 'pending' ? 'selected' : '' }}>Pending Mentor</option>
                            <option value="approved" {{ request('mentor_status') === 'approved' ? 'selected' : '' }}>Approved Mentor</option>
                            <option value="rejected" {{ request('mentor_status') === 'rejected' ? 'selected' : '' }}>Rejected Mentor</option>
                        </select>
                    </div>

                    <!-- Search Box -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Cari Keyword:</label>
                        <div class="flex items-center gap-1.5">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, NIM, kegiatan..." class="w-full text-xs border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-xs">
                            <button type="submit" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-xs shrink-0 cursor-pointer">
                                Cari
                            </button>
                        </div>
                    </div>

                </form>
            </div>

            <!-- Alpine Modal & Bundle State Container (SATU PINTU: FOKUS PADA PAKET RANGKUMAN MINGGUAN) -->
            <div x-data="{
                activeBundle: null,
                showModal: false,
                openModal(bundleData) {
                    this.activeBundle = bundleData;
                    this.showModal = true;
                }
            }" class="space-y-4">
                
                <!-- Banner Pengantar Rangkuman -->
                <div class="bg-blue-50/60 border border-blue-200 rounded-xl p-4 text-xs text-blue-900 flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <div class="space-y-0.5">
                        <strong class="font-bold text-blue-900">Rangkuman Aktivitas Berkala (Paket 7 Hari):</strong>
                        <p class="text-blue-800 leading-relaxed">
                            Seluruh catatan harian mahasiswa bimbingan telah dirangkum ke dalam paket mingguan. Klik <strong>"Lihat Detail & Evaluasi"</strong> untuk memeriksa rekapitulasi kegiatan 1 pekan dan memberikan pengesahan akademik.
                        </p>
                    </div>
                </div>

                <!-- TABEL UTAMA: PAKET RANGKUMAN BERKALA (TANPA TAB DOBEL) -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse divide-y divide-gray-200">
                            <thead class="bg-gray-50 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                <tr>
                                    <th class="py-3.5 px-4 w-52">Mahasiswa Bimbingan</th>
                                    <th class="py-3.5 px-4">Instansi & Unit Penempatan</th>
                                    <th class="py-3.5 px-4 w-48">Periode Rangkuman</th>
                                    <th class="py-3.5 px-4 w-28 text-center">Jumlah Hari</th>
                                    <th class="py-3.5 px-4 w-36 text-center">Status Verifikasi</th>
                                    <th class="py-3.5 px-4 w-44 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-xs">
                                @forelse ($weeklyBundles as $bIndex => $bundle)
                                    @php
                                        $bStudent = $bundle['student'];
                                        $bProfile = $bStudent?->studentProfile;
                                        $bPlacement = $bundle['placement'];
                                        $bUnit = $bPlacement?->application?->unit;
                                        $bAgency = $bUnit?->agencyProfile ?? $bPlacement?->agencyProfile;
                                    @endphp
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <!-- Mahasiswa -->
                                        <td class="py-4 px-4 align-top">
                                            <strong class="text-gray-900 block text-xs">{{ $bStudent->name ?? 'Mahasiswa' }}</strong>
                                            <span class="text-gray-500 font-mono text-[11px]">NIM: {{ $bProfile->nim ?? '-' }}</span>
                                            <span class="text-blue-600 block text-[11px] mt-0.5">{{ $bProfile->jurusan ?? '-' }}</span>
                                        </td>

                                        <!-- Instansi -->
                                        <td class="py-4 px-4 align-top">
                                            <strong class="text-gray-800 block">{{ $bAgency->agency_name ?? '-' }}</strong>
                                            <span class="text-gray-500 text-[11px]">{{ $bUnit->name ?? '-' }}</span>
                                        </td>

                                        <!-- Periode Rangkuman -->
                                        <td class="py-4 px-4 align-top">
                                            <div class="font-bold text-gray-800">
                                                {{ \Carbon\Carbon::parse($bundle['min_date'])->translatedFormat('d M Y') }}
                                            </div>
                                            <div class="text-gray-400 text-[11px]">
                                                s/d {{ \Carbon\Carbon::parse($bundle['max_date'])->translatedFormat('d M Y') }}
                                            </div>
                                        </td>

                                        <!-- Jumlah Hari -->
                                        <td class="py-4 px-4 align-top text-center">
                                            <span class="px-2.5 py-1 rounded-md font-bold text-[11px] bg-blue-50 text-blue-700 border border-blue-200 inline-block">
                                                {{ $bundle['entries_count'] }} Hari Terisi
                                            </span>
                                        </td>

                                        <!-- Status DPL -->
                                        <td class="py-4 px-4 align-top text-center">
                                            @if ($bundle['status'] === 'approved')
                                                <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 inline-block">
                                                    Disetujui DPL
                                                </span>
                                            @elseif ($bundle['status'] === 'rejected')
                                                <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200 inline-block">
                                                    Minta Revisi
                                                </span>
                                            @else
                                                <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200 inline-block">
                                                    Menunggu Review
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Tombol Aksi Evaluasi -->
                                        <td class="py-4 px-4 align-top text-right">
                                            <button type="button" 
                                                    @click="openModal({{ json_encode($bundle) }})"
                                                    class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-xs transition active:scale-95 cursor-pointer inline-flex items-center gap-1">
                                                <span>Lihat Detail & Evaluasi</span>
                                                <span>&rarr;</span>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-12 text-center text-gray-400 text-xs">
                                            Belum ada paket logbook mingguan dari mahasiswa bimbingan Anda.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- MODAL RINCIAN 7 HARI & FORM EVALUASI RESMI DPL -->
                <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <!-- Backdrop -->
                        <div x-show="showModal" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                             @click="showModal = false" class="fixed inset-0 bg-slate-900/60 transition-opacity"></div>

                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                        <div x-show="showModal" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                             class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-gray-200">
                            
                            <!-- Header Modal -->
                            <div class="px-6 py-4 bg-slate-50 border-b border-gray-200 flex items-center justify-between">
                                <div>
                                    <h3 class="text-sm font-bold text-gray-900">
                                        Rincian Logbook 7 Hari Kerja: <span x-text="activeBundle?.student?.name"></span>
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        Periode: <strong x-text="activeBundle?.min_date"></strong> s/d <strong x-text="activeBundle?.max_date"></strong>
                                        &bull; <span x-text="activeBundle?.entries_count"></span> Hari Aktivitas
                                    </p>
                                </div>
                                <button type="button" @click="showModal = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg border border-gray-200 hover:bg-white transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <!-- Isi Kronologi 7 Hari (Scrollable) -->
                            <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto text-xs">
                                
                                <div class="border border-gray-200 rounded-xl overflow-hidden">
                                    <table class="w-full text-left border-collapse divide-y divide-gray-100">
                                        <thead class="bg-gray-50 text-gray-600 font-bold">
                                            <tr>
                                                <th class="py-2.5 px-3 w-16 text-center">Urutan</th>
                                                <th class="py-2.5 px-3 w-28">Tanggal</th>
                                                <th class="py-2.5 px-4">Deskripsi Aktivitas Mahasiswa</th>
                                                <th class="py-2.5 px-3 w-24 text-center">Mentor</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            <template x-for="(entry, index) in (activeBundle?.entries || [])" :key="entry.id">
                                                <tr class="hover:bg-slate-50">
                                                    <td class="py-3 px-3 text-center font-bold text-blue-700 bg-slate-50/50 align-top" x-text="'Hari ' + (index + 1)"></td>
                                                    <td class="py-3 px-3 font-mono font-bold text-gray-800 align-top" x-text="entry.date"></td>
                                                    <td class="py-3 px-4 text-gray-700 leading-relaxed align-top">
                                                        <div x-text="entry.activity"></div>
                                                        <template x-if="entry.attachment">
                                                            <div class="mt-1">
                                                                <a :href="'/storage/' + entry.attachment" target="_blank" class="text-[11px] text-blue-600 font-semibold hover:underline">
                                                                    &rarr; Unduh Dokumen Bukti Lampiran
                                                                </a>
                                                            </div>
                                                        </template>
                                                    </td>
                                                    <td class="py-3 px-3 text-center align-top">
                                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800" x-text="entry.status"></span>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Form Evaluasi DPL Langsung Di Modal -->
                                <form action="{{ route('lecturer.logbooks.bulk_approve') }}" method="POST" class="p-4 bg-slate-50 rounded-xl border border-gray-200 space-y-3">
                                    @csrf
                                    <template x-for="id in (activeBundle?.logbook_ids || [])" :key="id">
                                        <input type="hidden" name="logbook_ids[]" :value="id">
                                    </template>

                                    <div>
                                        <label class="block text-xs font-bold text-gray-800 mb-1">
                                            Catatan Evaluasi / Bimbingan DPL untuk Paket 7 Hari Ini:
                                        </label>
                                        <input type="text" name="bulk_feedback" :value="activeBundle?.feedback"
                                               placeholder="Tuliskan arahan capaian kompetensi mingguan mahasiswa..."
                                               class="w-full text-xs rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600 shadow-2xs">
                                    </div>

                                    <div class="flex items-center justify-end gap-2 pt-1">
                                        <button type="submit" name="action" value="rejected" onclick="return confirm('Minta revisi untuk paket logbook ini?')"
                                                class="px-4 py-2 bg-white border border-rose-300 text-rose-700 hover:bg-rose-50 text-xs font-bold rounded-lg transition shadow-2xs cursor-pointer">
                                            Minta Revisi Paket
                                        </button>

                                        <button type="submit" name="action" value="approved"
                                                class="px-5 py-2 bg-blue-700 hover:bg-blue-800 text-white text-xs font-bold rounded-lg transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            <span>Verifikasi & ACC Paket 7 Hari</span>
                                        </button>
                                    </div>
                                </form>

                            </div>

                            <!-- Footer Modal -->
                            <div class="px-6 py-3 bg-slate-50 border-t border-gray-200 flex justify-end">
                                <button type="button" @click="showModal = false" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-xs font-bold rounded-lg hover:bg-gray-100 transition cursor-pointer">
                                    Tutup Rincian
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
