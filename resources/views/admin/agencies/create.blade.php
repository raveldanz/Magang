<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.agencies.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-600 hover:text-gray-900 transition flex items-center justify-center shadow-xs" title="Kembali">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h2 class="font-black text-xl text-gray-900 tracking-tight">
                    Tambah Instansi Dinas Baru
                </h2>
                <p class="text-xs text-gray-500">Daftarkan entitas instansi dinas pemerintah kota baru</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 sm:p-8">

                <form method="POST" action="{{ route('admin.agencies.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Nama Entitas Pemerintah <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="government_name" value="{{ old('government_name', 'Pemerintah Kota Surabaya') }}" required class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Nama Resmi Dinas / OPD <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="agency_name" value="{{ old('agency_name') }}" required placeholder="Contoh: Dinas Kesehatan" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Email Resmi Dinas <span class="text-rose-500">*</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="dinkes@surabaya.go.id" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Nomor Telepon / Fax
                            </label>
                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="(031) 5312144" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Alamat Kantor Dinas Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="address" rows="3" required placeholder="Jl. Jimerto No. 25-27, Ketabang, Genteng, Kota Surabaya..." class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">{{ old('address') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Nama Kepala Dinas (Penandatangan)
                            </label>
                            <input type="text" name="signee_name" value="{{ old('signee_name') }}" placeholder="Dr. H. Budi, M.Kes" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                NIP Kepala Dinas
                            </label>
                            <input type="text" name="signee_nip" value="{{ old('signee_nip') }}" placeholder="19720101 199803 1 003" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Jabatan Resmi
                            </label>
                            <input type="text" name="signee_position" value="{{ old('signee_position', 'Kepala Dinas') }}" placeholder="Kepala Dinas..." class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Website Dinas
                            </label>
                            <input type="url" name="website" value="{{ old('website') }}" placeholder="https://dinkes.surabaya.go.id" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Kota <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="city" value="{{ old('city', 'Surabaya') }}" required class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        </div>
                    </div>

                    <!-- Upload Logo Resmi Dinas (Mobile Friendly Card) -->
                    <div class="pt-4 border-t border-gray-100">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                            Logo Resmi Instansi Dinas (Opsional)
                        </label>
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3.5 p-3.5 sm:p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                            <div class="flex items-center gap-3 shrink-0">
                                <div class="w-16 h-16 rounded-2xl bg-white border border-slate-200 shadow-2xs flex items-center justify-center p-2 shrink-0 overflow-hidden" style="width: 64px; height: 64px; min-width: 64px; min-height: 64px; max-width: 64px; max-height: 64px;">
                                    <img id="agencyLogoPreview" src="{{ asset('images/default-agency.svg') }}" alt="Logo Default" class="w-full h-full object-contain shrink-0 transition-all duration-200">
                                </div>
                                <div class="sm:hidden text-xs">
                                    <span class="font-bold text-slate-800 block">Pratinjau Logo</span>
                                    <span class="text-[11px] text-slate-500">Maksimal ukuran 2MB</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <input type="file" id="agencyLogoInput" name="logo" accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml"
                                       onchange="handleLogoPreview(this, 'agencyLogoPreview', '{{ asset('images/default-agency.svg') }}')"
                                       class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer shadow-2xs">
                                <p class="text-[11px] text-gray-500 mt-1.5 leading-relaxed">Format: PNG, JPG, WEBP, SVG. Ukuran maksimal 2MB. Jika dikosongkan, menggunakan logo default dinas.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center sm:justify-end gap-2.5 sm:gap-3 pt-6 border-t border-gray-100">
                        <a href="{{ route('admin.agencies.index') }}" class="w-full sm:w-auto px-5 py-3 sm:py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition text-center justify-center flex items-center">
                            Batal
                        </a>
                        <button type="submit" class="w-full sm:w-auto px-6 py-3 sm:py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer text-center justify-center flex items-center">
                            Simpan Instansi Dinas
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
                
                if (!file.type.startsWith('image/')) {
                    alert('Berkas harus berupa gambar (PNG, JPG, WEBP, atau SVG).');
                    input.value = '';
                    preview.src = defaultSrc;
                    return;
                }

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
