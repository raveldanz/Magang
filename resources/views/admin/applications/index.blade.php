<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Pengajuan Magang') }}
        </h2>
    </x-slot>

    <div class="py-5 sm:py-8 lg:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-2xl sm:rounded-3xl p-4 sm:p-6 border border-slate-100">
                
                <!-- Form Filter & Search (Responsive Single-Row Flex Layout) -->
                <form method="GET" action="{{ route('admin.applications.index') }}" class="mb-6 bg-gray-50 p-4 rounded-xl border border-gray-200">
                    <div class="flex flex-col lg:flex-row items-center gap-3">
                        <!-- Input Search (flex-1) -->
                        <div class="flex-1 w-full">
                            <x-text-input type="text" name="search" value="{{ request('search') }}" 
                                placeholder="Cari nama mahasiswa, NIM, atau universitas..." class="w-full text-sm h-10" />
                        </div>

                        <!-- Filter Unit / Divisi (w-64) -->
                        <div class="w-full lg:w-64">
                            <select name="unit_id" class="w-full h-10 text-xs border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                                <option value="">-- Semua Unit/Divisi --</option>
                                @if (isset($groupedUnits) && $groupedUnits !== null)
                                    @foreach ($groupedUnits as $agencyName => $agencyUnits)
                                        <optgroup label=" {{ $agencyName }}">
                                            @foreach ($agencyUnits as $u)
                                                <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>
                                                    {{ $u->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                @elseif(isset($units))
                                    @foreach ($units as $u)
                                        <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Filter Status (w-44) -->
                        <div class="w-full lg:w-44">
                            <select name="status" class="w-full h-10 text-xs border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                                <option value="">-- Semua Status --</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>PENDING</option>
                                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>VERIFIED</option>
                                <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>ACCEPTED</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>REJECTED</option>
                            </select>
                        </div>

                        <!-- Action Buttons (Height 10 uniform) -->
                        <div class="flex items-center gap-2 w-full lg:w-auto shrink-0">
                            <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 h-10 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg shadow-sm transition w-full lg:w-auto cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <span>Filter</span>
                            </button>

                            @if(request('search') || request('status') || request('unit_id'))
                                <a href="{{ route('admin.applications.index') }}" class="inline-flex items-center justify-center px-4 h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-bold rounded-lg shadow-sm transition">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                <!-- Tabel Data Pengajuan (Desktop & Tablet) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b bg-gray-50 text-xs font-semibold text-gray-600 uppercase">
                                <th class="p-3">Tanggal Pengajuan</th>
                                <th class="p-3">Nama Mahasiswa</th>
                                <th class="p-3">Universitas / Jurusan</th>
                                <th class="p-3">Unit Tujuan</th>
                                <th class="p-3">Status</th>
                                <th class="p-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y">
                            @forelse ($applications as $app)
                                <tr class="border-b hover:bg-gray-50/50">
                                    <td class="p-3 text-xs text-gray-500 font-mono">
                                        {{ $app->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="p-3 font-semibold text-gray-900">
                                        <div class="leading-snug">{{ $app->user->name }}</div>
                                        @if ($app->status === 'accepted' && optional($app->placement)->evaluation && optional(optional($app->placement)->finalreport)->status === 'approved')
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full mt-1 bg-blue-100 text-blue-800 border border-blue-200">
                                                 SIAP CETAK SERTIFIKAT
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-gray-600">{{ $app->user->studentProfile->universitas ?? '-' }} <br><span class="text-xs text-gray-400">({{ $app->user->studentProfile->jurusan ?? '-' }})</span></td>
                                    <td class="p-3 font-medium text-gray-800">{{ $app->unit->name ?? '-' }}</td>
                                    <td class="p-3">
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full border shadow-sm
                                            {{ $app->status === 'accepted' ? 'bg-green-100 text-green-800 border-green-300' : '' }}
                                            {{ $app->status === 'pending' ? 'bg-amber-100 text-amber-800 border-amber-300 font-black' : '' }}
                                            {{ $app->status === 'rejected' ? 'bg-red-100 text-red-800 border-red-300' : '' }}
                                            {{ $app->status === 'verified' ? 'bg-blue-100 text-blue-800 border-blue-300' : '' }}">
                                            {{ strtoupper($app->status) }}
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        <a href="{{ route('admin.applications.show', $app->id) }}" class="text-blue-600 hover:text-blue-900 font-bold text-xs inline-flex items-center space-x-1">
                                            <span>Detail & Verifikasi</span>
                                            <span>&rarr;</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-6 text-center text-gray-500">Tidak ada pengajuan magang yang sesuai kriteria pencarian.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Kartu Data Pengajuan Khusus Mobile (< 768px) -->
                <div class="md:hidden space-y-3">
                    @forelse ($applications as $app)
                        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-2xs space-y-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <h4 class="font-bold text-sm text-slate-900 leading-snug truncate">{{ $app->user->name }}</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $app->user->studentProfile->universitas ?? '-' }} <span class="text-slate-400">({{ $app->user->studentProfile->jurusan ?? '-' }})</span></p>
                                </div>
                                <span class="px-2.5 py-1 text-[11px] font-bold rounded-full border shadow-2xs shrink-0
                                    {{ $app->status === 'accepted' ? 'bg-green-100 text-green-800 border-green-300' : '' }}
                                    {{ $app->status === 'pending' ? 'bg-amber-100 text-amber-800 border-amber-300 font-black' : '' }}
                                    {{ $app->status === 'rejected' ? 'bg-red-100 text-red-800 border-red-300' : '' }}
                                    {{ $app->status === 'verified' ? 'bg-blue-100 text-blue-800 border-blue-300' : '' }}">
                                    {{ strtoupper($app->status) }}
                                </span>
                            </div>

                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs space-y-1">
                                <div class="flex items-center justify-between text-slate-600">
                                    <span class="text-slate-400">Unit Tujuan:</span>
                                    <span class="font-semibold text-slate-800 text-right">{{ $app->unit->name ?? '-' }}</span>
                                </div>
                                <div class="flex items-center justify-between text-slate-600">
                                    <span class="text-slate-400">Diajukan:</span>
                                    <span class="font-mono text-slate-500">{{ $app->created_at->format('d M Y, H:i') }}</span>
                                </div>
                            </div>

                            @if ($app->status === 'accepted' && optional($app->placement)->evaluation && optional(optional($app->placement)->finalreport)->status === 'approved')
                                <div class="inline-flex items-center gap-1 text-[10px] font-bold px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 w-full justify-center">
                                     SIAP CETAK SERTIFIKAT
                                </div>
                            @endif

                            <a href="{{ route('admin.applications.show', $app->id) }}" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-xl transition cursor-pointer">
                                <span>Detail & Verifikasi Berkas</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    @empty
                        <div class="p-6 bg-white rounded-2xl border border-slate-200 text-center text-xs text-slate-500">
                            Tidak ada pengajuan magang yang sesuai kriteria pencarian.
                        </div>
                    @endforelse
                </div>

                <!-- Paginasi -->
                <div class="mt-6">
                    {{ $applications->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>