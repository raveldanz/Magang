<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.universities.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-600 hover:text-gray-900 transition">
                ←
            </a>
            <div>
                <h2 class="font-black text-xl text-gray-900 tracking-tight">
                    Edit Data Universitas: {{ $university->name }}
                </h2>
                <p class="text-xs text-gray-500">Perbarui identitas kampus, kode, dan pejabat penandatangan</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 sm:p-8">

                <form method="POST" action="{{ route('admin.universities.update', $university->id) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Nama Resmi Perguruan Tinggi <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $university->name) }}" required class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Kode / Singkatan <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="code" value="{{ old('code', $university->code) }}" required class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono uppercase">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Email Resmi Kampus
                            </label>
                            <input type="email" name="email" value="{{ old('email', $university->email) }}" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Nomor Telepon / Fax
                            </label>
                            <input type="text" name="phone" value="{{ old('phone', $university->phone) }}" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Alamat Kampus Utama
                        </label>
                        <textarea name="address" rows="2" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">{{ old('address', $university->address) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Nama Rektor / Pimpinan (PIC)
                            </label>
                            <input type="text" name="pic_name" value="{{ old('pic_name', $university->pic_name) }}" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                NIP / NIDN Rektor
                            </label>
                            <input type="text" name="pic_nip" value="{{ old('pic_nip', $university->pic_nip) }}" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Jabatan Penandatangan
                            </label>
                            <input type="text" name="pic_position" value="{{ old('pic_position', $university->pic_position) }}" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                        <a href="{{ route('admin.universities.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                            Batal
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
