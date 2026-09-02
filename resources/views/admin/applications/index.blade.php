<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Pengajuan Magang') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
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
                                        <optgroup label="{{ $agencyName }}">
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

                <!-- Tabel Data Pengajuan -->
                <div class="overflow-x-auto">
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
                                            <span class="inline-flex items-center gap-1.5 text-[10px] font-bold px-2 py-0.5 rounded-full mt-1 bg-blue-100 text-blue-800 border border-blue-200">
                                                <svg class="w-3 h-3 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                                <span>SIAP CETAK SERTIFIKAT</span>
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

                <!-- Paginasi -->
                <div class="mt-6">
                    {{ $applications->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>