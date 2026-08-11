<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Pengajuan Magang (Admin)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="p-3">Nama Mahasiswa</th>
                            <th class="p-3">Universitas / Jurusan</th>
                            <th class="p-3">Unit Tujuan</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($applications as $app)
                            <tr class="border-b">
                                <td class="p-3 font-semibold">{{ $app->user->name }}</td>
                                <td class="p-3">{{ $app->user->studentProfile->universitas ?? '-' }} ({{ $app->user->studentProfile->jurusan ?? '-' }})</td>
                                <td class="p-3">{{ $app->unit->name ?? '-' }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-1 text-xs font-bold rounded 
                                        {{ $app->status === 'accepted' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $app->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $app->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $app->status === 'verified' ? 'bg-blue-100 text-blue-800' : '' }}">
                                        {{ strtoupper($app->status) }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    <a href="{{ route('admin.applications.show', $app->id) }}" class="text-indigo-600 hover:text-indigo-900 font-bold">
                                        Detail & Verifikasi &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-4 text-center text-gray-500">Belum ada pengajuan magang masuk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</x-app-layout>