<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    {{ __('Manajemen Divisi & Kuota Magang') }}
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    {{ Auth::user()->agencyProfile->agency_name ?? 'Super Administrator (Semua Instansi Pemkot Surabaya)' }}
                </p>
            </div>

            <a href="{{ route('admin.units.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Divisi / Lowongan Baru
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Message -->
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

            @if (session('error'))
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-lg shadow-sm flex items-center justify-between text-rose-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Divisi / Bidang</p>
                    <h3 class="text-2xl font-black text-gray-900 mt-1">{{ $stats['total_units'] }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Unit kerja aktif</p>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Kuota Magang</p>
                    <h3 class="text-2xl font-black text-indigo-600 mt-1">{{ $stats['total_quota'] }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Kapasitas maksimal</p>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Kuota Terisi (Diterima)</p>
                    <h3 class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['total_filled'] }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Mahasiswa aktif magang</p>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Sisa Kuota Tersedia</p>
                    <h3 class="text-2xl font-black text-amber-600 mt-1">{{ $stats['total_remaining'] }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Slot mahasiswa baru</p>
                </div>
            </div>

            <!-- Filter & Search Bar -->
            <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="w-full md:w-auto flex flex-wrap items-center gap-3">
                    @if (Auth::user()->agency_profile_id === null && count($agencies) > 1)
                        <form method="GET" action="{{ route('admin.units.index') }}" class="flex items-center gap-2">
                            <select name="agency_id" onchange="this.form.submit()" class="text-xs border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500">
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

                <form method="GET" action="{{ route('admin.units.index') }}" class="w-full md:w-72 flex items-center gap-2">
                    @if (request('agency_id'))
                        <input type="hidden" name="agency_id" value="{{ request('agency_id') }}">
                    @endif
                    <div class="relative w-full">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama divisi..." class="w-full text-xs border-gray-300 rounded-xl pl-9 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <button type="submit" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                        Cari
                    </button>
                </form>
            </div>

            <!-- Table Divisi / Unit Kerja -->
            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-200">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900">Daftar Divisi & Penyesuaian Kuota</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Atur kuota, tambah divisi baru, atau sesuaikan kapasitas secara instan</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/75 border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                <th class="py-3.5 px-4">Nama Divisi / Bidang</th>
                                <th class="py-3.5 px-4">Instansi Induk</th>
                                <th class="py-3.5 px-4 text-center">Status Kuota</th>
                                <th class="py-3.5 px-4 text-center">Sisa Kuota</th>
                                <th class="py-3.5 px-4 text-center">Aksi Cepat Kuota</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($units as $unit)
                                @php
                                    $acceptedCount = $unit->applications->where('status', 'accepted')->count();
                                    $remaining = max(0, $unit->quota - $acceptedCount);
                                    $percent = $unit->quota > 0 ? min(100, round(($acceptedCount / $unit->quota) * 100)) : 100;
                                @endphp
                                <tr class="hover:bg-slate-50/75 transition-colors">
                                    
                                    <!-- Nama Divisi & Deskripsi -->
                                    <td class="py-4 px-4 max-w-xs">
                                        <div class="font-bold text-gray-900 leading-snug">{{ $unit->name }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $unit->description ?? 'Tidak ada deskripsi' }}</div>
                                    </td>

                                    <!-- Instansi Induk -->
                                    <td class="py-4 px-4">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-indigo-50 text-indigo-700 text-xs font-semibold rounded-lg">
                                            🏛️ {{ $unit->agencyProfile->agency_name ?? '-' }}
                                        </span>
                                    </td>

                                    <!-- Progress Kuota -->
                                    <td class="py-4 px-4 text-center min-w-[140px]">
                                        <div class="space-y-1">
                                            <div class="flex justify-between text-xs font-bold text-gray-700">
                                                <span>{{ $acceptedCount }} Terisi</span>
                                                <span>{{ $unit->quota }} Total</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                                <div class="h-2 rounded-full {{ $percent >= 100 ? 'bg-rose-500' : ($percent >= 75 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ $percent }}%"></div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Sisa Kuota & Status -->
                                    <td class="py-4 px-4 text-center">
                                        @if ($remaining > 0)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-100 text-emerald-800 text-xs font-black rounded-full">
                                                {{ $remaining }} Slot Tersedia
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-rose-100 text-rose-800 text-xs font-black rounded-full">
                                                PENUH
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Aksi Cepat Kuota (+ / -) -->
                                    <td class="py-4 px-4 text-center">
                                        <div class="inline-flex items-center gap-1 bg-gray-50 p-1 rounded-xl border border-gray-200 shadow-sm">
                                            <!-- Kurang (-1) -->
                                            <form action="{{ route('admin.units.updateQuota', $unit->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="action" value="decrement">
                                                <button type="submit" title="Kurangi Kuota (-1)" class="w-7 h-7 flex items-center justify-center bg-white hover:bg-rose-50 text-rose-600 font-black rounded-lg border border-gray-200 transition text-sm">
                                                    -
                                                </button>
                                            </form>

                                            <span class="px-2 font-black text-gray-800 text-xs">{{ $unit->quota }}</span>

                                            <!-- Tambah (+1) -->
                                            <form action="{{ route('admin.units.updateQuota', $unit->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="action" value="increment">
                                                <button type="submit" title="Tambah Kuota (+1)" class="w-7 h-7 flex items-center justify-center bg-white hover:bg-emerald-50 text-emerald-600 font-black rounded-lg border border-gray-200 transition text-sm">
                                                    +
                                                </button>
                                            </form>
                                        </div>
                                    </td>

                                    <!-- Aksi Edit & Hapus -->
                                    <td class="py-4 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.units.edit', $unit->id) }}" class="p-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-bold transition shadow-sm" title="Edit Divisi">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>

                                            <form action="{{ route('admin.units.destroy', $unit->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus divisi {{ $unit->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg text-xs font-bold transition shadow-sm" title="Hapus Divisi">
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
                                    <td colspan="6" class="py-12 text-center text-gray-400">
                                        <p class="font-medium text-gray-600">Belum Ada Divisi / Lowongan Magang</p>
                                        <p class="text-xs text-gray-400 mt-1">Silakan klik tombol "Tambah Divisi Baru" untuk membuka lowongan.</p>
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
