<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Review Logbook Mahasiswa (Admin)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 bg-green-100 text-green-800 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Filter Status --}}
            <div class="bg-white p-4 shadow sm:rounded-lg">
                <form method="GET" action="{{ route('admin.logbooks.index') }}" class="flex items-center gap-4">
                    <label for="status" class="text-sm font-medium text-gray-700">Filter Status:</label>
                    <select name="status" id="status" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                        <option value="">Semua</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>PENDING</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>APPROVED</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>REJECTED</option>
                    </select>
                    <x-primary-button class="text-xs">
                        {{ __('Filter') }}
                    </x-primary-button>
                </form>
            </div>

            {{-- Tabel Logbook --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="p-3 text-sm font-semibold text-gray-600">No</th>
                                <th class="p-3 text-sm font-semibold text-gray-600">Mahasiswa</th>
                                <th class="p-3 text-sm font-semibold text-gray-600">Unit</th>
                                <th class="p-3 text-sm font-semibold text-gray-600">Tanggal</th>
                                <th class="p-3 text-sm font-semibold text-gray-600">Kegiatan</th>
                                <th class="p-3 text-sm font-semibold text-gray-600">Status</th>
                                <th class="p-3 text-sm font-semibold text-gray-600">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logbooks as $index => $log)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="p-3 text-sm">{{ $index + 1 }}</td>
                                    <td class="p-3 text-sm font-medium">
                                        {{ $log->placement->application->user->name ?? '-' }}
                                    </td>
                                    <td class="p-3 text-sm">
                                        {{ $log->placement->application->unit->name ?? '-' }}
                                    </td>
                                    <td class="p-3 text-sm">{{ \Carbon\Carbon::parse($log->date)->format('d M Y') }}</td>
                                    <td class="p-3 text-sm max-w-xs">
                                        <p class="truncate" title="{{ $log->activity }}">{{ Str::limit($log->activity, 60) }}</p>
                                    </td>
                                    <td class="p-3 text-sm">
                                        <span class="px-2 py-1 text-xs font-bold rounded
                                            {{ $log->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $log->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $log->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ strtoupper($log->status) }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-sm">
                                        <a href="{{ route('admin.logbooks.show', $log->id) }}" class="text-indigo-600 hover:text-indigo-900 font-bold">
                                            Detail & Review &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-6 text-center text-gray-500">Belum ada logbook yang diisi oleh mahasiswa.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
