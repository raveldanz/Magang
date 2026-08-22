<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Penilaian Magang: {{ $placement->application->user->name }}
            </h2>
            <a href="{{ route('pembimbing.student.detail', $placement->id) }}" class="px-4 py-2 bg-gray-200 text-gray-800 text-xs font-semibold rounded-md">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white p-6 rounded-lg shadow space-y-6">
                <h3 class="text-lg font-bold border-b pb-2">Formulir Penilaian</h3>
                
                <form action="{{ route('pembimbing.evaluation.store', $placement->id) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700">Nilai Disiplin (0-100)</label>
                            <input type="number" name="nilai_disiplin" min="0" max="100" value="{{ old('nilai_disiplin', $placement->evaluation->nilai_disiplin ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            @error('nilai_disiplin') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700">Nilai Kinerja (0-100)</label>
                            <input type="number" name="nilai_kinerja" min="0" max="100" value="{{ old('nilai_kinerja', $placement->evaluation->nilai_kinerja ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            @error('nilai_kinerja') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700">Nilai Laporan (0-100)</label>
                            <input type="number" name="nilai_laporan" min="0" max="100" value="{{ old('nilai_laporan', $placement->evaluation->nilai_laporan ?? '') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            @error('nilai_laporan') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700">Catatan / Evaluasi Umum</label>
                        <textarea name="catatan" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('catatan', $placement->evaluation->catatan ?? '') }}</textarea>
                        @error('catatan') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700 transition shadow-md cursor-pointer">
                            Simpan Penilaian
                        </button>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
