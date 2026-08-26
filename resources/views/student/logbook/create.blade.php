<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Logbook Harian') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm p-8 border border-gray-100">

                <div class="mb-6 pb-4 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">Input Catatan Kegiatan Harian</h3>
                    <p class="text-sm text-gray-500 mt-1">Isi logbook kegiatan magang Anda untuk hari ini. Logbook akan direview oleh Admin/Pembimbing.</p>
                </div>

                {{-- Alert Error jika Validasi Gagal --}}
                @if ($errors->any())
                    <div class="p-4 mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-lg text-sm">
                        <p class="font-bold mb-1">Gagal Menyimpan Logbook:</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- FORM CREATE (Mengirim POST ke route store) --}}
                <form action="{{ route('student.logbook.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    {{-- Tanggal --}}
                    <div>
                        <label for="date" class="block font-semibold text-sm text-gray-700 mb-1">Tanggal Kegiatan</label>
                        <input type="date" id="date" name="date" value="{{ date('Y-m-d') }}" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm text-sm" required>
                    </div>

                    {{-- Deskripsi Kegiatan --}}
                    <div>
                        <label for="activity" class="block font-semibold text-sm text-gray-700 mb-1">Deskripsi Kegiatan</label>
                        <textarea id="activity" name="activity" rows="5" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm text-sm" placeholder="Tuliskan secara detail kegiatan magang Anda hari ini..." required></textarea>
                        <p class="text-xs text-gray-400 mt-1">Minimal 10 karakter</p>
                    </div>

                    {{-- Lampiran --}}
                    <div>
                        <label for="attachment" class="block font-semibold text-sm text-gray-700 mb-1">Lampiran (Opsional)</label>
                        <input id="attachment" name="attachment" type="file" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer" />
                        <p class="text-xs text-gray-400 mt-1">Format: PDF, JPG, PNG. Maksimal 2MB.</p>
                    </div>

                    {{-- Tombol Submit --}}
                    <div class="flex items-center space-x-3 pt-4 border-t border-gray-100">
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition cursor-pointer shadow-md">
                            SIMPAN LOGBOOK
                        </button>

                        <a href="{{ route('student.logbook.index') }}" class="px-5 py-2.5 bg-gray-100 border border-gray-300 rounded-xl text-xs font-bold text-gray-700 uppercase tracking-wider hover:bg-gray-200 transition">
                            BATAL
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>