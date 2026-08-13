<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail & Review Logbook') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 bg-green-100 text-green-800 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Info Mahasiswa --}}
            <div class="bg-white p-6 shadow sm:rounded-lg">
                <h3 class="text-lg font-bold mb-4 text-gray-800">👤 Informasi Mahasiswa</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <p><strong>Nama:</strong> {{ $logbook->placement->application->user->name ?? '-' }}</p>
                    <p><strong>NIM:</strong> {{ $logbook->placement->application->user->studentProfile->nim ?? '-' }}</p>
                    <p><strong>Unit:</strong> {{ $logbook->placement->application->unit->name ?? '-' }}</p>
                    <p><strong>Pembimbing:</strong> {{ $logbook->placement->pembimbing->name ?? 'Belum Diplot' }}</p>
                </div>
            </div>

            {{-- Detail Logbook --}}
            <div class="bg-white p-6 shadow sm:rounded-lg">
                <h3 class="text-lg font-bold mb-4 text-gray-800">📒 Detail Logbook</h3>
                <div class="space-y-4 text-sm">
                    <div>
                        <p class="text-gray-500 font-medium">Tanggal Kegiatan</p>
                        <p class="text-gray-800 font-bold text-base">{{ \Carbon\Carbon::parse($logbook->date)->format('d F Y') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 font-medium">Deskripsi Kegiatan</p>
                        <div class="mt-1 p-4 bg-gray-50 rounded-lg text-gray-800 whitespace-pre-wrap">{{ $logbook->activity }}</div>
                    </div>
                    <div>
                        <p class="text-gray-500 font-medium">Lampiran</p>
                        @if ($logbook->attachment)
                            <a href="{{ asset('storage/' . $logbook->attachment) }}" target="_blank" class="inline-flex items-center mt-1 px-3 py-1.5 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                                📎 Lihat / Download File
                            </a>
                        @else
                            <p class="text-gray-400 mt-1">Tidak ada lampiran</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-gray-500 font-medium">Status Saat Ini</p>
                        <span class="mt-1 inline-block px-3 py-1 text-xs font-bold rounded
                            {{ $logbook->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $logbook->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $logbook->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ strtoupper($logbook->status) }}
                        </span>
                    </div>
                    @if ($logbook->feedback)
                        <div>
                            <p class="text-gray-500 font-medium">Feedback Sebelumnya</p>
                            <div class="mt-1 p-4 bg-yellow-50 rounded-lg text-gray-800">{{ $logbook->feedback }}</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Form Review --}}
            <div class="bg-white p-6 shadow sm:rounded-lg">
                <h3 class="text-lg font-bold mb-4 text-gray-800">✅ Aksi Review</h3>
                <form action="{{ route('admin.logbooks.review', $logbook->id) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <x-input-label for="status" value="Keputusan Review" />
                        <select id="status" name="status"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="approved" {{ $logbook->status === 'approved' ? 'selected' : '' }}>✅ APPROVED (Disetujui)</option>
                            <option value="rejected" {{ $logbook->status === 'rejected' ? 'selected' : '' }}>❌ REJECTED (Ditolak / Revisi)</option>
                        </select>
                    </div>

                    <div>
                        <x-input-label for="feedback" value="Feedback / Catatan untuk Mahasiswa (Opsional)" />
                        <textarea id="feedback" name="feedback" rows="4"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            placeholder="Berikan catatan, saran, atau alasan penolakan...">{{ $logbook->feedback }}</textarea>
                    </div>

                    <div class="flex items-center gap-4 pt-4 border-t">
                        <x-primary-button>
                            {{ __('Simpan Review') }}
                        </x-primary-button>
                        <a href="{{ route('admin.logbooks.index') }}">
                            <x-secondary-button type="button">
                                {{ __('Kembali') }}
                            </x-secondary-button>
                        </a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>