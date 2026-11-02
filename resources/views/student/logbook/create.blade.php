<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Logbook Harian') }}
        </h2>
    </x-slot>

    <div class="py-5 sm:py-8 lg:py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl sm:rounded-3xl shadow-xs p-5 sm:p-7 lg:p-8 border border-gray-100">

                <div class="mb-6 pb-4 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">Input Catatan Kegiatan Harian</h3>
                    <p class="text-sm text-gray-500 mt-1">Isi logbook kegiatan magang Anda. Logbook akan direview oleh Pembimbing.</p>
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

                    {{-- Lampiran Logbook (Konsisten dengan Gaya Profil & Pengajuan) --}}
                    <div class="space-y-2" x-data="{ selectedName: '', previewUrl: null }">
                        <label class="block font-semibold text-xs sm:text-sm text-gray-700">
                            Lampiran (Opsional)
                        </label>

                        <div class="flex items-center gap-2 w-full">
                            <input type="file" 
                                   id="attachment" 
                                   name="attachment" 
                                   accept=".pdf,.jpg,.jpeg,.png" 
                                   class="hidden"
                                   @change="if($event.target.files.length) { 
                                       const file = $event.target.files[0];
                                       if(file.size > 2 * 1024 * 1024) {
                                           alert('Ukuran berkas lampiran maksimal 2MB.');
                                           $event.target.value = '';
                                           selectedName = '';
                                           previewUrl = null;
                                           return;
                                       }
                                       selectedName = file.name; 
                                       previewUrl = URL.createObjectURL(file); 
                                   }" />

                            <!-- Tombol Choose File Mandiri -->
                            <button type="button" 
                                    @click="document.getElementById('attachment').click()"
                                    class="px-3.5 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-xl transition shrink-0 cursor-pointer shadow-2xs">
                                Choose File
                            </button>

                            <!-- Nama File Terpilih yang Bisa Diklik Langsung untuk View -->
                            <div class="min-w-0 flex-1 truncate text-xs">
                                <template x-if="selectedName">
                                    <a :href="previewUrl" 
                                       target="_blank" 
                                       class="text-blue-600 hover:text-blue-800 hover:underline font-semibold truncate block cursor-pointer" 
                                       :title="selectedName" 
                                       x-text="selectedName"></a>
                                </template>
                                <template x-if="!selectedName">
                                    <span class="text-slate-400">No file chosen</span>
                                </template>
                            </div>
                        </div>

                        <p class="text-[10px] text-slate-400">Format: PDF, JPG, PNG (Maks. 2MB)</p>
                        <x-input-error :messages="$errors->get('attachment')" class="mt-1" />
                    </div>

                    {{-- Tombol Submit --}}
                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center sm:space-x-3 gap-2.5 sm:gap-0 pt-4 border-t border-gray-100">
                        <a href="{{ route('student.logbook.index') }}" class="w-full sm:w-auto px-5 py-3 sm:py-2.5 bg-gray-100 border border-gray-300 rounded-xl text-xs font-bold text-gray-700 uppercase tracking-wider hover:bg-gray-200 transition text-center justify-center flex items-center">
                            BATAL
                        </a>

                        <button type="submit" class="w-full sm:w-auto px-6 py-3 sm:py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition cursor-pointer shadow-md text-center justify-center flex items-center">
                            SIMPAN LOGBOOK
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>