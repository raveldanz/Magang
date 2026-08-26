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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Info Penilaian -->
                <div class="bg-white p-6 rounded-lg shadow space-y-4">
                    <h3 class="text-lg font-bold border-b pb-2">Status Penilaian</h3>
                    @if ($placement->evaluation)
                        <div class="text-sm">
<<<<<<< HEAD
                            <p><strong>Disiplin:</strong> {{ $placement->evaluation->nilai_disiplin }}</p>
                            <p><strong>Kinerja:</strong> {{ $placement->evaluation->nilai_kinerja }}</p>
                            <p><strong>Laporan:</strong> {{ $placement->evaluation->nilai_laporan }}</p>
                            <p><strong>Catatan:</strong> {{ $placement->evaluation->catatan ?? '-' }}</p>
=======
                            <p><strong>Disiplin:</strong> {{ $placement->evaluation?->nilai_disiplin ?? '-' }}</p>
                            <p><strong>Kinerja:</strong> {{ $placement->evaluation?->nilai_kinerja ?? '-' }}</p>
                            <p><strong>Laporan:</strong> {{ $placement->evaluation?->nilai_laporan ?? '-' }}</p>
                            <p><strong>Catatan:</strong> {{ $placement->evaluation?->catatan ?? '-' }}</p>
>>>>>>> main
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Belum ada penilaian.</p>
                    @endif
<<<<<<< HEAD
                    <a href="{{ route('pembimbing.evaluation.create', $placement->id) }}" class="inline-block mt-2 px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded hover:bg-indigo-700">
=======
                    <a href="{{ route('pembimbing.evaluation.create', $placement->id) }}" class="inline-block mt-2 px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700 transition shadow-xs cursor-pointer">
>>>>>>> main
                        Isi / Edit Penilaian
                    </a>
                </div>

                <!-- Info Laporan Akhir -->
                <div class="bg-white p-6 rounded-lg shadow space-y-4 flex flex-col justify-between">
                    <div>
                        <h3 class="text-lg font-bold border-b pb-2">Dokumen Laporan Akhir</h3>
                        @if ($placement->finalreport && $placement->finalreport->file_path)
                            <div class="text-sm mt-3">
<<<<<<< HEAD
                                <p><strong>Status:</strong> <span class="uppercase font-bold text-indigo-700">{{ $placement->finalreport->status }}</span></p>
=======
                                <p><strong>Status:</strong> <span class="uppercase font-bold text-blue-700">{{ $placement->finalreport->status }}</span></p>
>>>>>>> main
                            </div>
                            <a href="{{ asset('storage/' . $placement->finalreport->file_path) }}" target="_blank" class="inline-block mt-2 px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded hover:bg-blue-700">
                                📄 Unduh / Lihat Laporan
                            </a>
                        @else
                            <p class="text-sm text-gray-500 mt-3">Mahasiswa belum mengunggah laporan akhir.</p>
                        @endif
                    </div>

                    @if ($placement->finalreport && $placement->finalreport->file_path)
                    <form action="{{ route('pembimbing.final_report.updateStatus', $placement->finalreport->id) }}" method="POST" class="pt-3 border-t mt-4 space-y-3">
                        @csrf
                        @method('PUT')
                        <input type="text" name="feedback" value="{{ $placement->finalreport->feedback }}" placeholder="Catatan/Revisi untuk mahasiswa..." class="w-full text-xs border-gray-300 rounded-md">
                        <div class="flex space-x-2">
                            <button type="submit" name="status" value="approved" class="px-3 py-1.5 w-full bg-green-600 text-white text-xs font-bold rounded hover:bg-green-700">Setujui (Approve)</button>
                            <button type="submit" name="status" value="revision" class="px-3 py-1.5 w-full bg-red-600 text-white text-xs font-bold rounded hover:bg-red-700">Minta Revisi</button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow space-y-6">
                <div class="flex justify-between items-center border-b pb-2">
                    <h3 class="text-lg font-bold">Daftar Kegiatan Harian</h3>
<<<<<<< HEAD
                    <a href="{{ route('pembimbing.evaluation.create', $placement->id) }}" class="px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded hover:bg-indigo-700">
=======
                    <a href="{{ route('pembimbing.evaluation.create', $placement->id) }}" class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700 transition shadow-xs cursor-pointer">
>>>>>>> main
                        Isi / Edit Penilaian Magang
                    </a>
                </div>

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
<<<<<<< HEAD
                            <a href="{{ asset('storage/' . $log->attachment) }}" target="_blank" class="text-xs text-indigo-600 hover:underline inline-block">
=======
                            <a href="{{ asset('storage/' . $log->attachment) }}" target="_blank" class="text-xs text-blue-600 hover:underline inline-block">
>>>>>>> main
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