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
                        <input id="attachment" name="attachment" type="file" accept=".pdf,.jpg,.jpeg,.png"
                               onchange="handleAttachmentPreview(this)"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer" />
                        <p class="text-xs text-gray-400 mt-1">Format: PDF, JPG, PNG. Maksimal 3MB.</p>

                        <!-- Preview Container -->
                        <div id="attachmentPreviewBox" class="hidden mt-3 p-3 bg-slate-50 border border-slate-200 rounded-xl items-center gap-3">
                            <div id="previewImageWrapper" class="hidden w-16 h-16 rounded-lg overflow-hidden border border-slate-200 shrink-0 bg-white">
                                <img id="previewImage" src="" alt="Pratinjau Berkas" class="w-full h-full object-cover">
                            </div>
                            <div id="previewDocWrapper" class="hidden w-12 h-12 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p id="previewFileName" class="text-xs font-bold text-slate-800 truncate"></p>
                                <p id="previewFileSize" class="text-[11px] text-slate-500"></p>
                            </div>
                        </div>
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

    @push('scripts')
    <script>
        function handleAttachmentPreview(input) {
            const box = document.getElementById('attachmentPreviewBox');
            const imgWrapper = document.getElementById('previewImageWrapper');
            const img = document.getElementById('previewImage');
            const docWrapper = document.getElementById('previewDocWrapper');
            const nameEl = document.getElementById('previewFileName');
            const sizeEl = document.getElementById('previewFileSize');

            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (file.size > 3 * 1024 * 1024) {
                    alert('Ukuran berkas melebihi batas maksimal 3MB.');
                    input.value = '';
                    box.classList.add('hidden');
                    box.classList.remove('flex');
                    return;
                }

                nameEl.textContent = file.name;
                sizeEl.textContent = (file.size / 1024).toFixed(1) + ' KB';
                box.classList.remove('hidden');
                box.classList.add('flex');

                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        img.src = e.target.result;
                        imgWrapper.classList.remove('hidden');
                        docWrapper.classList.add('hidden');
                    };
                    reader.readAsDataURL(file);
                } else {
                    imgWrapper.classList.add('hidden');
                    docWrapper.classList.remove('hidden');
                }
            } else {
                box.classList.add('hidden');
                box.classList.remove('flex');
            }
        }
    </script>
    @endpush
</x-app-layout>
