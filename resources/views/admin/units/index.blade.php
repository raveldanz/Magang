<x-app-layout>
    <div class="py-8 bg-[#F5F8FC] min-h-screen text-slate-900 font-sans">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Top Header & Action -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <span>Manajemen Divisi & Kuota Magang</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        {{ Auth::user()->agencyProfile->agency_name ?? 'Super Administrator (Semua Instansi Pemkot Surabaya)' }}
                    </p>
                </div>

                <a href="{{ route('admin.units.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-semibold uppercase tracking-wider rounded-xl shadow-sm shadow-blue-200 hover:scale-[1.01] active:scale-[0.99] transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Divisi Baru</span>
                </a>
            </div>

            <!-- Flash Messages -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-2xl flex items-center gap-3 text-emerald-800 text-sm font-semibold shadow-sm">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 bg-red-50 border border-red-100 rounded-2xl flex items-center gap-3 text-red-800 text-sm font-semibold shadow-sm">
                    <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Summary Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm shadow-slate-200/50 transition-all duration-200 hover:scale-[1.01]">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Divisi / Bidang</p>
                    <h3 class="text-2xl font-extrabold text-slate-900 mt-1.5">{{ $stats['total_units'] }}</h3>
                    <p class="text-[11px] text-slate-400 mt-1">Unit kerja aktif</p>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm shadow-slate-200/50 transition-all duration-200 hover:scale-[1.01]">
                    <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider">Total Kuota Magang</p>
                    <h3 class="text-2xl font-extrabold text-blue-600 mt-1.5">{{ $stats['total_quota'] }}</h3>
                    <p class="text-[11px] text-slate-400 mt-1">Kapasitas maksimal</p>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm shadow-slate-200/50 transition-all duration-200 hover:scale-[1.01]">
                    <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Kuota Terisi</p>
                    <h3 class="text-2xl font-extrabold text-emerald-600 mt-1.5">{{ $stats['total_filled'] }}</h3>
                    <p class="text-[11px] text-slate-400 mt-1">Mahasiswa aktif magang</p>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm shadow-slate-200/50 transition-all duration-200 hover:scale-[1.01]">
                    <p class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Sisa Kuota Tersedia</p>
                    <h3 class="text-2xl font-extrabold text-amber-600 mt-1.5">{{ $stats['total_remaining'] }}</h3>
                    <p class="text-[11px] text-slate-400 mt-1">Slot mahasiswa baru</p>
                </div>
            </div>

            <!-- Filter & Search Bar -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-100 shadow-sm shadow-slate-200/50 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="w-full md:w-auto flex flex-wrap items-center gap-3">
                    @if (Auth::user()->agency_profile_id === null && count($agencies) > 1)
                        <form method="GET" action="{{ route('admin.units.index') }}" class="flex items-center gap-2">
                            <select name="agency_id" onchange="this.form.submit()" class="text-xs border border-slate-200 bg-slate-50 text-slate-900 rounded-xl px-3 py-2 focus:bg-white focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 transition-all duration-200">
                                <option value="">-- Semua Instansi --</option>
                                @foreach ($agencies as $agency)
                                    <option value="{{ $agency->id }}" {{ request('agency_id') == $agency->id ? 'selected' : '' }}>
                                        🏛️ {{ $agency->agency_name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                </div>

                <form method="GET" action="{{ route('admin.units.index') }}" class="w-full md:w-80 flex items-center gap-2">
                    @if (request('agency_id'))
                        <input type="hidden" name="agency_id" value="{{ request('agency_id') }}">
                    @endif
                    <div class="relative w-full">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama divisi..." class="w-full text-xs border border-slate-200 bg-slate-50 text-slate-900 rounded-xl pl-9 pr-3 py-2 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 transition-all duration-200">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <button type="submit" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold uppercase tracking-wider rounded-xl shadow-sm transition">
                        Cari
                    </button>
                </form>
            </div>

            <!-- Table Divisi / Unit Kerja -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm shadow-slate-200/50 overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Daftar Divisi & Penyesuaian Kuota</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Atur kuota, tambah divisi baru, atau sesuaikan kapasitas secara instan</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/60 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="py-3.5 px-4">Nama Divisi / Bidang</th>
                                <th class="py-3.5 px-4">Instansi Induk</th>
                                <th class="py-3.5 px-4 text-center">Status Kuota</th>
                                <th class="py-3.5 px-4 text-center">Sisa Kuota</th>
                                <th class="py-3.5 px-4 text-center">Aksi Cepat Kuota</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse ($units as $unit)
                                @php
                                    $acceptedCount = $unit->applications->where('status', 'accepted')->count();
                                    $remaining = max(0, $unit->quota - $acceptedCount);
                                    $percent = $unit->quota > 0 ? min(100, round(($acceptedCount / $unit->quota) * 100)) : 100;
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition-colors duration-150">
                                    
                                    <!-- Nama Divisi & Deskripsi -->
                                    <td class="py-4 px-4 max-w-xs">
                                        <div class="font-bold text-slate-900 leading-snug">{{ $unit->name }}</div>
                                        <div class="text-xs text-slate-400 mt-0.5 line-clamp-2">{{ $unit->description ?? 'Tidak ada deskripsi' }}</div>
                                    </td>

                                    <!-- Instansi Induk -->
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-semibold rounded-full border border-blue-100">
                                            🏛️ {{ $unit->agencyProfile->agency_name ?? '-' }}
                                        </span>
                                    </td>

                                    <!-- Progress Kuota -->
                                    <td class="py-4 px-4 text-center min-w-[140px]">
                                        <div class="space-y-1.5">
                                            <div class="flex justify-between text-xs font-bold text-slate-700">
                                                <span>{{ $acceptedCount }} Terisi</span>
                                                <span class="text-slate-400">{{ $unit->quota }} Total</span>
                                            </div>
                                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                                <div class="h-2 rounded-full transition-all duration-300 {{ $percent >= 100 ? 'bg-red-500' : ($percent >= 75 ? 'bg-amber-500' : 'bg-blue-600') }}" style="width: {{ $percent }}%"></div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Sisa Kuota & Status -->
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        @if ($remaining > 0)
                                            <span class="inline-flex items-center px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-full border border-emerald-200">
                                                {{ $remaining }} Slot Tersedia
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 bg-red-50 text-red-700 text-xs font-bold rounded-full border border-red-200">
                                                PENUH
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Aksi Cepat Kuota (+ / -) -->
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1 bg-slate-50 p-1 rounded-xl border border-slate-200">
                                            <!-- Kurang (-1) -->
                                            <form action="{{ route('admin.units.updateQuota', $unit->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="action" value="decrement">
                                                <button type="submit" title="Kurangi Kuota (-1)" class="w-7 h-7 flex items-center justify-center bg-white hover:bg-red-50 text-red-600 font-bold rounded-lg border border-slate-200 transition text-sm">
                                                    -
                                                </button>
                                            </form>

                                            <span class="px-2.5 font-bold text-slate-800 text-xs">{{ $unit->quota }}</span>

                                            <!-- Tambah (+1) -->
                                            <form action="{{ route('admin.units.updateQuota', $unit->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="action" value="increment">
                                                <button type="submit" title="Tambah Kuota (+1)" class="w-7 h-7 flex items-center justify-center bg-white hover:bg-emerald-50 text-emerald-600 font-bold rounded-lg border border-slate-200 transition text-sm">
                                                    +
                                                </button>
                                            </form>
                                        </div>
                                    </td>

                                    <!-- Aksi Edit & Hapus -->
                                    <td class="py-4 px-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('admin.units.edit', $unit->id) }}" class="p-2 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-xl transition" title="Edit Divisi">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>

                                            <form action="{{ route('admin.units.destroy', $unit->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus divisi {{ $unit->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl transition" title="Hapus Divisi">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-400">
                                        <p class="font-medium text-slate-600">Belum Ada Divisi / Lowongan Magang</p>
                                        <p class="text-xs text-slate-400 mt-1">Silakan klik tombol "Tambah Divisi Baru" untuk membuka lowongan.</p>
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