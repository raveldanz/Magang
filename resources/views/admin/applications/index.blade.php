<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Pengajuan Magang (Admin)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <!-- Form Filter & Search -->
                <form method="GET" action="{{ route('admin.applications.index') }}" class="mb-6 flex flex-col md:flex-row items-center justify-between gap-4 bg-gray-50 p-4 rounded-xl border border-gray-200">
                    <div class="w-full md:w-1/2 flex items-center space-x-2">
                        <x-text-input type="text" name="search" value="{{ request('search') }}" 
                            placeholder="Cari nama mahasiswa, NIM, atau universitas..." class="w-full text-sm" />
                    </div>

                    <div class="w-full md:w-auto flex items-center space-x-3">
                        <select name="status" class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">-- Semua Status --</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>PENDING</option>
                            <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>VERIFIED</option>
                            <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>ACCEPTED</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>REJECTED</option>
                        </select>

                        <x-primary-button type="submit" class="text-xs">
                            🔍 Filter
                        </x-primary-button>

                        @if(request('search') || request('status'))
                            <a href="{{ route('admin.applications.index') }}">
                                <x-secondary-button type="button" class="text-xs">
                                    Reset
                                </x-secondary-button>
                            </a>
                        @endif
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
                                        {{ $app->user->name }}
                                        @if ($app->status === 'accepted' && optional($app->placement)->evaluation && optional(optional($app->placement)->finalreport)->status === 'approved')
                                            <br><span class="px-2 py-0.5 mt-1 inline-block text-[10px] font-bold bg-purple-100 text-purple-800 rounded-full border border-purple-300">🎉 SIAP CETAK SERTIFIKAT</span>
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
                                        <a href="{{ route('admin.applications.show', $app->id) }}" class="text-indigo-600 hover:text-indigo-900 font-bold text-xs inline-flex items-center space-x-1">
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