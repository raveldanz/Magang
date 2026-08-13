<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Akhir Magang') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 bg-green-100 text-green-800 rounded-lg text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-bold border-b pb-2 mb-4">Upload Dokumen Laporan</h3>

                @if ($placement->finalreport)
                    <div class="mb-6 p-4 rounded-lg bg-gray-50 border border-gray-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-600">Status Laporan Saat Ini:</p>
                                <span class="px-3 py-1 inline-block mt-1 text-xs font-bold rounded-full 
                                    {{ $placement->finalreport->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $placement->finalreport->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $placement->finalreport->status === 'revision' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ strtoupper($placement->finalreport->status) }}
                                </span>
                            </div>
                            <a href="{{ asset('storage/' . $placement->finalreport->file_path) }}" target="_blank" class="px-4 py-2 bg-blue-600 text-white rounded text-sm font-semibold hover:bg-blue-700">
                                📄 Unduh / Lihat File
                            </a>
                        </div>
                        
                        @if ($placement->finalreport->status === 'revision')
                            <div class="mt-4 p-3 bg-red-50 text-red-700 rounded text-sm">
                                <strong>Catatan Revisi dari Pembimbing:</strong> <br>
                                {{ $placement->finalreport->feedback ?? 'Tidak ada catatan khusus.' }}
                            </div>
                        @endif
                    </div>
                @endif

                @if (!$placement->finalreport || $placement->finalreport->status !== 'approved')
                    <form action="{{ route('student.final_report.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 border-t pt-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">File Laporan (PDF / DOC / DOCX)</label>
                            <input type="file" name="file_laporan" accept=".pdf,.doc,.docx" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-md cursor-pointer bg-gray-50 focus:outline-none" required>
                            <p class="text-xs text-gray-500 mt-1">Ukuran maksimal file: 5MB.</p>
                            @error('file_laporan') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full md:w-auto px-6 py-2 bg-indigo-600 text-white font-bold rounded-md hover:bg-indigo-700">
                                Unggah Laporan
                            </button>
                        </div>
                    </form>
                @else
                    <div class="p-4 bg-green-50 text-green-700 rounded-lg text-sm font-semibold text-center mt-4">
                        🎉 Laporan Akhir Anda telah disetujui. Anda tidak perlu mengunggah ulang.
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
