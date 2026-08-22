<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.agencies.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-600 hover:text-gray-900 transition">
                ←
            </a>
            <div>
                <h2 class="font-black text-xl text-gray-900 tracking-tight">
                    Edit Data Instansi Dinas: {{ $agency->agency_name }}
                </h2>
                <p class="text-xs text-gray-500">Perbarui identitas, kontak, dan pejabat penandatangan instansi</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 sm:p-8">

                <form method="POST" action="{{ route('admin.agencies.update', $agency->id) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Nama Entitas Pemerintah <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="government_name" value="{{ old('government_name', $agency->government_name) }}" required class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-2xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Nama Resmi Dinas / OPD <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="agency_name" value="{{ old('agency_name', $agency->agency_name) }}" required class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-2xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Email Resmi Dinas <span class="text-rose-500">*</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email', $agency->email) }}" required class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-2xs font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Nomor Telepon / Fax
                            </label>
                            <input type="text" name="phone" value="{{ old('phone', $agency->phone) }}" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-2xs">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Alamat Kantor Dinas Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="address" rows="3" required class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-2xs">{{ old('address', $agency->address) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Nama Kepala Dinas
                            </label>
                            <input type="text" name="signee_name" value="{{ old('signee_name', $agency->signee_name) }}" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-2xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                NIP Kepala Dinas
                            </label>
                            <input type="text" name="signee_nip" value="{{ old('signee_nip', $agency->signee_nip) }}" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-2xs font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Jabatan Resmi
                            </label>
                            <input type="text" name="signee_position" value="{{ old('signee_position', $agency->signee_position) }}" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-2xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Website Dinas
                            </label>
                            <input type="url" name="website" value="{{ old('website', $agency->website) }}" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-2xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Kota <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="city" value="{{ old('city', $agency->city) }}" required class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-2xs">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                        <a href="{{ route('admin.agencies.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                            Batal
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
