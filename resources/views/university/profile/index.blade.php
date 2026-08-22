<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    {{ __('Pengaturan Profil & Kop Surat Kampus') }}
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    🏛️ Kelola data identitas resmi, alamat, pejabat penandatangan, dan logo untuk Kop Surat Tugas Magang
                </p>
            </div>

            <div>
                <a href="{{ route('university.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                    ← Kembali ke Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Success Message -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Form Validation Errors -->
            @if ($errors->any())
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-xs text-rose-900 text-sm">
                    <div class="font-bold flex items-center gap-1.5 mb-1 text-rose-800">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        Terdapat kesalahan pada isian form:
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-0.5 text-rose-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('university.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Card 1: Identitas & Logo Perguruan Tinggi -->
                <div class="bg-white rounded-2xl shadow-xs border border-gray-200 p-6 space-y-5">
                    <div class="border-b border-gray-100 pb-3 flex items-center gap-2">
                        <span class="text-xl">🎓</span>
                        <div>
                            <h3 class="font-bold text-base text-gray-900">Identitas Resmi Perguruan Tinggi</h3>
                            <p class="text-xs text-gray-400">Data ini akan tercantum pada kop surat tugas dan dokumen resmi kampus</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                        
                        <!-- Preview & Upload Logo Kampus -->
                        <div class="flex flex-col items-center p-4 bg-slate-50 rounded-2xl border border-dashed border-gray-300 text-center">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                Logo Resmi Kampus
                            </label>
                            
                            <div class="w-32 h-32 rounded-2xl bg-white p-2 border border-gray-200 shadow-xs flex items-center justify-center overflow-hidden mb-3">
                                @if ($university->logo && file_exists(public_path($university->logo)))
                                    <img src="{{ asset($university->logo) }}" alt="Logo {{ $university->name }}" class="max-h-full max-w-full object-contain">
                                @else
                                    <span class="text-4xl text-gray-300">🏛️</span>
                                @endif
                            </div>

                            <input type="file" name="logo" id="logo" accept="image/*" class="text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                            <p class="text-[10px] text-gray-400 mt-1.5">Format: PNG, JPG, WEBP. Maks 2MB.</p>
                        </div>

                        <!-- Nama & Singkatan Kampus -->
                        <div class="md:col-span-2 space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Nama Lengkap Perguruan Tinggi <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="name" value="{{ old('name', $university->name) }}" required placeholder="Contoh: Universitas Dr. Soetomo" class="w-full text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-semibold">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Singkatan / Kode Kampus
                                    </label>
                                    <input type="text" name="code" value="{{ old('code', $university->code) }}" placeholder="Contoh: UNITOMO" class="w-full text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono uppercase">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Email Resmi Kampus / Humas
                                    </label>
                                    <input type="email" name="email" value="{{ old('email', $university->email) }}" placeholder="info@unitomo.ac.id" class="w-full text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Nomor Telepon / Hotline Kampus
                                    </label>
                                    <input type="text" name="phone" value="{{ old('phone', $university->phone) }}" placeholder="(031) 5925970" class="w-full text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Alamat Lengkap Kampus (Untuk Header Kop Surat)
                                </label>
                                <textarea name="address" rows="2" placeholder="Jl. Semolowaru No. 84, Menur Pumpungan, Kec. Sukolilo, Surabaya, Jawa Timur 60118" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">{{ old('address', $university->address) }}</textarea>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Card 2: Pejabat Penandatangan Surat Tugas (PIC / Rektor / Dekan) -->
                <div class="bg-white rounded-2xl shadow-xs border border-gray-200 p-6 space-y-5">
                    <div class="border-b border-gray-100 pb-3 flex items-center gap-2">
                        <span class="text-xl">✍️</span>
                        <div>
                            <h3 class="font-bold text-base text-gray-900">Pejabat Resmi Penandatangan Surat Tugas</h3>
                            <p class="text-xs text-gray-400">Nama dan jabatan pejabat yang otomatis tertera di bagian tanda tangan Surat Pengantar Magang</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Nama Lengkap & Gelar Pejabat
                            </label>
                            <input type="text" name="pic_name" value="{{ old('pic_name', $university->pic_name) }}" placeholder="Dr. Siti Marwiyah, S.H., M.H." class="w-full text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                NIP / NIDN Pejabat
                            </label>
                            <input type="text" name="pic_nip" value="{{ old('pic_nip', $university->pic_nip) }}" placeholder="196808281993032001" class="w-full text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Jabatan Penandatangan
                            </label>
                            <input type="text" name="pic_position" value="{{ old('pic_position', $university->pic_position) }}" placeholder="Rektor Universitas Dr. Soetomo" class="w-full text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                        💾 Simpan Perubahan Profil Kampus
                    </button>
                </div>

            </form>

        </div>
    </div>
</x-app-layout>
