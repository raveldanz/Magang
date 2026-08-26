<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Logbook Harian') }}
        </h2>
    </x-slot>

<<<<<<< HEAD
    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-[0_4px_12px_rgba(100,116,139,0.08)] p-8">

                <div class="mb-6 pb-4 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800">Input Catatan Kegiatan Harian</h3>
                    <p class="text-sm text-slate-500 mt-1">Isi logbook kegiatan magang kamu untuk hari ini. Logbook akan direview oleh Admin/Pembimbing.</p>
=======
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm p-8 border border-gray-100">

                <div class="mb-6 pb-4 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">📝 Input Catatan Kegiatan Harian</h3>
                    <p class="text-sm text-gray-500 mt-1">Isi logbook kegiatan magang Anda untuk hari ini. Logbook akan direview oleh Admin/Pembimbing.</p>
>>>>>>> main
                </div>

                {{-- Alert Error jika Validasi Gagal --}}
                @if ($errors->any())
<<<<<<< HEAD
                    <div class="p-4 mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-xl text-sm">
=======
                    <div class="p-4 mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-lg text-sm">
>>>>>>> main
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
<<<<<<< HEAD
                        <label for="date" class="block font-semibold text-sm text-slate-700 mb-1">Tanggal Kegiatan</label>
                        <input type="date" id="date" name="date" value="{{ date('Y-m-d') }}" class="w-full rounded-xl border-slate-200 focus:border-slate-500 focus:ring-slate-500 shadow-sm text-sm" required>
=======
                        <label for="date" class="block font-semibold text-sm text-gray-700 mb-1">Tanggal Kegiatan</label>
                        <input type="date" id="date" name="date" value="{{ date('Y-m-d') }}" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm text-sm" required>
>>>>>>> main
                    </div>

                    {{-- Deskripsi Kegiatan --}}
                    <div>
<<<<<<< HEAD
                        <label for="activity" class="block font-semibold text-sm text-slate-700 mb-1">Deskripsi Kegiatan</label>
                        <textarea id="activity" name="activity" rows="5" class="w-full rounded-xl border-slate-200 focus:border-slate-500 focus:ring-slate-500 shadow-sm text-sm" placeholder="Tuliskan secara detail kegiatan magang kamu hari ini..." required></textarea>
                        <p class="text-xs text-slate-400 mt-1">Minimal 10 karakter</p>
=======
                        <label for="activity" class="block font-semibold text-sm text-gray-700 mb-1">Deskripsi Kegiatan</label>
                        <textarea id="activity" name="activity" rows="5" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm text-sm" placeholder="Tuliskan secara detail kegiatan magang Anda hari ini..." required></textarea>
                        <p class="text-xs text-gray-400 mt-1">Minimal 10 karakter</p>
>>>>>>> main
                    </div>

                    {{-- Lampiran --}}
                    <div>
<<<<<<< HEAD
                        <label for="attachment" class="block font-semibold text-sm text-slate-700 mb-1">Lampiran (Opsional)</label>
                        <input id="attachment" name="attachment" type="file" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer" />
                        <p class="text-xs text-slate-400 mt-1">Format: PDF, JPG, PNG. Maksimal 2MB.</p>
                    </div>

                    {{-- Tombol Submit --}}
                    <div class="flex items-center space-x-3 pt-4 border-t border-slate-100">
                        <button type="submit" class="px-6 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition-colors">
                            Simpan Logbook
                        </button>

                        <a href="{{ route('student.logbook.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 uppercase tracking-wider hover:bg-slate-50 transition-colors">
                            Batal
=======
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
>>>>>>> main
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
<<<<<<< HEAD
</x-app-layout>
=======
</x-app-layout>
>>>>>>> main
