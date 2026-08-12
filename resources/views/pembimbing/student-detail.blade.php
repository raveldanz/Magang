<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Logbook: {{ $placement->application->user->name }}
            </h2>
            <a href="{{ route('pembimbing.dashboard') }}" class="px-4 py-2 bg-gray-200 text-gray-800 text-xs font-semibold rounded-md">
                &larr; Kembali
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

            <div class="bg-white p-6 rounded-lg shadow space-y-6">
                <h3 class="text-lg font-bold border-b pb-2">Daftar Kegiatan Harian</h3>

                @forelse ($placement->logbooks as $log)
                    <div class="border p-4 rounded-lg bg-gray-50 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gray-700">Tanggal: {{ $log->date }}</span>
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full 
                                {{ $log->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $log->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $log->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ strtoupper($log->status) }}
                            </span>
                        </div>

                        <p class="text-sm text-gray-800">{{ $log->activity }}</p>

                        @if ($log->attachment)
                            <a href="{{ asset('storage/' . $log->attachment) }}" target="_blank" class="text-xs text-indigo-600 hover:underline inline-block">
                                📄 Lihat Bukti Lampiran
                            </a>
                        @endif

                        <!-- Form Action Approve/Reject -->
                        <form action="{{ route('pembimbing.logbook.updateStatus', $log->id) }}" method="POST" class="pt-3 border-t grid grid-cols-1 md:grid-cols-3 gap-3 items-center">
                            @csrf
                            @method('PUT')
                            
                            <div class="md:col-span-2">
                                <input type="text" name="feedback" value="{{ $log->feedback }}" placeholder="Tambah catatan/feedback pembimbing (opsional)..." class="w-full text-xs border-gray-300 rounded-md">
                            </div>

                            <div class="flex space-x-2">
                                <button type="submit" name="status" value="approved" class="px-3 py-1.5 bg-green-600 text-white text-xs font-bold rounded hover:bg-green-700">
                                    Approve
                                </button>
                                <button type="submit" name="status" value="rejected" class="px-3 py-1.5 bg-red-600 text-white text-xs font-bold rounded hover:bg-red-700">
                                    Reject
                                </button>
                            </div>
                        </form>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">Mahasiswa belum menginputkan logbook.</p>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>