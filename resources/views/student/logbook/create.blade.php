<x-app-layout>
    <div class="py-8 bg-[#F5F8FC] min-h-screen text-slate-900 font-sans">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Card Form Surface -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm shadow-slate-200/50 p-6 sm:p-8">

                <!-- Header Form -->
                <div class="mb-6 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">
                            📝
                        </div>
                        <div>
                            <h3 class="text-lg font-bold tracking-tight text-slate-900">Input Catatan Kegiatan Harian</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Isi logbook kegiatan magang Anda untuk hari ini. Logbook akan direview oleh Admin/Pembimbing.</p>
                        </div>
                    </div>
                </div>

                {{-- Alert Error jika Validasi Gagal --}}
                @if ($errors->any())
                    <div class="p-4 mb-6 bg-red-50 border border-red-200 text-red-700 rounded-xl text-xs">
                        <p class="font-bold mb-1">Gagal Menyimpan Logbook:</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- FORM CREATE --}}
                <form action="{{ route('student.logbook.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    {{-- Tanggal Kegiatan --}}
                    <div>
                        <label for="date" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                            Tanggal Kegiatan
                        </label>
                        <input type="date" 
                               id="date" 
                               name="date" 
                               value="{{ old('date', date('Y-m-d')) }}" 
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm px-3.5 py-2.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 transition-all duration-200" 
                               required>
                    </div>

                    {{-- Deskripsi Kegiatan --}}
                    <div>
                        <label for="activity" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                            Deskripsi Kegiatan
                        </label>
                        <textarea id="activity" 
                                  name="activity" 
                                  rows="5" 
                                  class="w-full rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm p-3.5 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 transition-all duration-200" 
                                  placeholder="Tuliskan secara detail kegiatan magang Anda hari ini..." 
                                  required>{{ old('activity') }}</textarea>
                        <p class="text-[11px] text-slate-400 mt-1">Minimal 10 karakter</p>
                    </div>

                    {{-- Lampiran Bukti --}}
                    <div>
                        <label for="attachment" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                            Lampiran (Opsional)
                        </label>
                        <input id="attachment" 
                               name="attachment" 
                               type="file" 
                               accept=".pdf,.jpg,.jpeg,.png" 
                               class="block w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100 cursor-pointer border border-slate-200 rounded-xl bg-slate-50" />
                        <p class="text-[11px] text-slate-400 mt-1.5">Format: PDF, JPG, PNG. Maksimal 2MB.</p>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-xl text-xs font-semibold uppercase tracking-wider shadow-sm shadow-blue-200 hover:scale-[1.01] active:scale-[0.99] transition-all duration-200">
                            Simpan Logbook
                        </button>

                        <a href="{{ route('student.logbook.index') }}" class="px-4 py-2.5 bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200 rounded-xl text-xs font-medium uppercase tracking-wider transition-all duration-200">
                            Batal
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>