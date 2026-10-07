<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('lecturer.dashboard') }}" class="p-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 rounded-lg transition" title="Kembali ke Dashboard">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h2 class="font-bold text-xl sm:text-2xl text-slate-900 leading-tight">
                    Logbook Mahasiswa Bimbingan
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Tinjau dan verifikasi paket aktivitas mingguan mahasiswa magang bimbingan akademik Anda
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Success Message -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-lg flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- 1. Tiga Angka Polos (Menunggu, Disetujui, Perlu Revisi) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-xl border border-slate-200">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Menunggu</span>
                    <div class="text-2xl font-bold text-slate-900 mt-1">{{ $pendingCount }}</div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Disetujui</span>{{-- status-guard:ignore --}}
                    <div class="text-2xl font-bold text-slate-900 mt-1">{{ $approvedCount }}</div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Perlu Revisi</span>
                    <div class="text-2xl font-bold text-slate-900 mt-1">{{ $rejectedCount }}</div>
                </div>
            </div>

            <!-- 2. Filter Bar: Dropdown Mahasiswa, Dropdown Status, Kolom Cari, Tombol Cari -->
            <div class="bg-white rounded-xl p-4 border border-slate-200">
                <form method="GET" action="{{ route('lecturer.logbooks.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <!-- Dropdown Mahasiswa -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Mahasiswa:</label>
                        <select name="placement_id" onchange="this.form.submit()" class="w-full text-sm border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 py-2">
                            <option value="">Semua Mahasiswa</option>
                            @foreach ($supervisedPlacements as $pl)
                                @php
                                    $st = $pl->application?->user;
                                    $nim = $st?->studentProfile?->nim ?? '-';
                                @endphp
                                <option value="{{ $pl->id }}" {{ request('placement_id') == $pl->id ? 'selected' : '' }}>
                                    {{ $st?->name ?? 'Mahasiswa' }} ({{ $nim }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Dropdown Status -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Status:</label>
                        <select name="status" onchange="this.form.submit()" class="w-full text-sm border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 py-2">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') === 'pending' || request('lecturer_status') === 'pending' ? 'selected' : '' }}>Menunggu</option>
                            <option value="approved" {{ request('status') === 'approved' || request('lecturer_status') === 'approved' ? 'selected' : '' }}>Disetujui</option>{{-- status-guard:ignore --}}
                            <option value="rejected" {{ request('status') === 'rejected' || request('lecturer_status') === 'rejected' ? 'selected' : '' }}>Perlu Revisi</option>
                        </select>
                    </div>

                    <!-- Kolom Cari & Tombol Cari -->
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Cari:</label>
                        <div class="flex items-center gap-2">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama mahasiswa, NIM, atau kegiatan..."
                                   class="w-full text-sm border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 py-2">
                            <button type="submit" class="inline-flex items-center justify-center px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg transition shrink-0 cursor-pointer">
                                Cari
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- 3. Daftar Paket Mingguan (Komponen Bersama) -->
            <x-supervision.logbook-weekly
                :bundles="$weeklyBundles"
                :reviewRoute="route('lecturer.logbooks.bulk_approve')"
                role="lecturer"
                emptyMessage="Tidak ada paket logbook mingguan yang ditemukan sesuai filter yang dipilih."
            />

        </div>
    </div>
</x-app-layout>
