<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.universities.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-600 hover:text-gray-900 transition flex items-center justify-center shadow-xs" title="Kembali">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h2 class="font-black text-xl text-gray-900 tracking-tight">
                    Tambah Universitas / Perguruan Tinggi Mitra
                </h2>
                <p class="text-xs text-gray-500">Daftarkan perguruan tinggi mitra baru untuk program magang MBKM</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 sm:p-8">

                <form method="POST" action="{{ route('admin.universities.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Nama Resmi Perguruan Tinggi <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Universitas Airlangga" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Kode / Singkatan <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="code" value="{{ old('code') }}" required placeholder="UNAIR" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono uppercase">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Email Resmi Kampus
                            </label>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="humas@unair.ac.id" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Nomor Telepon / Fax
                            </label>
                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="(031) 5914042" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Alamat Kampus Utama
                        </label>
                        <textarea name="address" rows="2" placeholder="Jl. Airlangga No. 4-6, Surabaya..." class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">{{ old('address') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Nama Rektor / Pimpinan (PIC)
                            </label>
                            <input type="text" name="pic_name" value="{{ old('pic_name') }}" placeholder="Prof. Dr. Moh. Nasih, SE., MT., Ak" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                NIP / NIDN Rektor
                            </label>
                            <input type="text" name="pic_nip" value="{{ old('pic_nip') }}" placeholder="19650806 199203 1 002" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Jabatan Penandatangan
                            </label>
                            <input type="text" name="pic_position" value="{{ old('pic_position', 'Rektor') }}" placeholder="Rektor..." class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        </div>
                    </div>

                    <!-- Upload Logo Resmi Kampus (Mobile Friendly Card) -->
                    <div class="pt-4 border-t border-gray-100">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                            Logo Resmi Perguruan Tinggi (Opsional)
                        </label>
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3.5 p-3.5 sm:p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                            <div class="flex items-center gap-3 shrink-0">
                                <div class="w-16 h-16 rounded-2xl bg-white border border-slate-200 shadow-2xs flex items-center justify-center p-2 shrink-0 overflow-hidden" style="width: 64px; height: 64px; min-width: 64px; min-height: 64px; max-width: 64px; max-height: 64px;">
                                    <img id="univLogoPreview" src="{{ asset('images/default-university.svg') }}" alt="Logo Default" class="w-full h-full object-contain shrink-0 transition-all duration-200">
                                </div>
                                <div class="sm:hidden text-xs">
                                    <span class="font-bold text-slate-800 block">Pratinjau Logo</span>
                                    <span class="text-[11px] text-slate-500">Maksimal ukuran 2MB</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <input type="file" id="univLogoInput" name="logo" accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml" 
                                       onchange="handleLogoPreview(this, 'univLogoPreview', '{{ asset('images/default-university.svg') }}')"
                                       class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer shadow-2xs">
                                <p class="text-[11px] text-gray-500 mt-1.5 leading-relaxed">Format didukung: PNG, JPG, WEBP, SVG. Jika dikosongkan, sistem menyediakan logo default.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center sm:justify-end gap-2.5 sm:gap-3 pt-6 border-t border-gray-100">
                        <a href="{{ route('admin.universities.index') }}" class="w-full sm:w-auto px-5 py-3 sm:py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition text-center justify-center flex items-center">
                            Batal
                        </a>
                        <button type="submit" class="w-full sm:w-auto px-6 py-3 sm:py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer text-center justify-center flex items-center">
                            Daftarkan Universitas
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function handleLogoPreview(input, previewId, defaultSrc) {
            const preview = document.getElementById(previewId);
            if (!preview) return;

            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Validasi tipe file
                if (!file.type.startsWith('image/')) {
                    alert('Berkas harus berupa gambar (PNG, JPG, WEBP, atau SVG).');
                    input.value = '';
                    preview.src = defaultSrc;
                    return;
                }

                // Validasi ukuran file (maks 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran berkas logo terlalu besar (maksimal 2MB).');
                    input.value = '';
                    preview.src = defaultSrc;
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = defaultSrc;
            }
        }
    </script>
    @endpush
</x-app-layout>
