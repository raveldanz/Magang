<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Jurnal Kegiatan Harian (Logbook)') }}
            </h2>
            <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 text-xs font-semibold rounded-md">
                &larr; Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 bg-green-100 text-green-800 rounded-lg text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Form Isi Logbook -->
            <div class="bg-white p-6 shadow sm:rounded-lg">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Tambah Kegiatan Harian</h3>
                <form action="{{ route('student.logbook.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="date" value="Tanggal Kegiatan" />
                        <x-text-input id="date" name="date" type="date" class="mt-1 block w-full" required />
                    </div>

                    <div>
                        <x-input-label for="activity" value="Deskripsi Kegiatan Magang" />
                        <textarea id="activity" name="activity" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required placeholder="Jelaskan pekerjaan/kegiatan yang dilakukan hari ini..."></textarea>
                    </div>

                    <div>
                        <x-input-label for="attachment" value="Lampiran Bukti / Dokumentasi (Opsional: PDF, Gambar max 2MB)" />
                        <x-text-input id="attachment" name="attachment" type="file" class="mt-1 block w-full border p-2 rounded-md" />
                    </div>

                    <x-primary-button>
                        {{ __('Simpan Logbook') }}
                    </x-primary-button>
                </form>
            </div>

            <!-- Tabel Daftar Logbook -->
            <div class="bg-white p-6 shadow sm:rounded-lg">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Riwayat Logbook</h3>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b bg-gray-50 text-xs font-semibold text-gray-600 uppercase">
                            <th class="p-3">Tanggal</th>
                            <th class="p-3">Kegiatan</th>
                            <th class="p-3">Lampiran</th>
                            <th class="p-3">Status Verifikasi</th>
                            <th class="p-3">Catatan Pembimbing</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y">
                        @forelse ($logbooks as $log)
                            <tr>
                                <td class="p-3 whitespace-nowrap font-medium">{{ $log->date }}</td>
                                <td class="p-3">{{ $log->activity }}</td>
                                <td class="p-3">
                                    @if ($log->attachment)
                                        <a href="{{ asset('storage/' . $log->attachment) }}" target="_blank" class="text-indigo-600 hover:underline">
                                            Lihat File
                                        </a>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="p-3">
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full 
                                        {{ $log->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $log->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $log->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ strtoupper($log->status) }}
                                    </span>
                                </td>
                                <td class="p-3 text-gray-600">{{ $log->feedback ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-4 text-center text-gray-500">Belum ada kegiatan logbook yang dicatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>