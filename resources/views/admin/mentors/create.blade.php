<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.mentors.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-600 hover:text-gray-900 transition">
                ←
            </a>
            <div>
                <h2 class="font-black text-xl text-gray-900 tracking-tight">
                    Tambah Mentor Lapangan Baru
                </h2>
                <p class="text-xs text-gray-500">Daftarkan akun pembimbing teknis dinas untuk membimbing mahasiswa magang</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 sm:p-8">

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="p-4 mb-6 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-xs text-rose-900 text-sm">
                        <div class="font-bold flex items-center gap-1.5 mb-1 text-rose-800">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            Terdapat kesalahan pengisian formulir:
                        </div>
                        <ul class="list-disc list-inside text-xs space-y-0.5 text-rose-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.mentors.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Nama Lengkap & Gelar <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Ir. Siti Aminah, M.Kom" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Email Kedinasan / Akun Login <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="mentor.kominfo@surabaya.go.id" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono">
                        <p class="text-[11px] text-gray-400 mt-1">Password bawaan akun: <code class="bg-gray-100 px-1.5 py-0.5 rounded text-blue-600 font-bold">password</code></p>
                    </div>

                    @if($isSuperAdmin)
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Instansi Dinas <span class="text-rose-500">*</span>
                            </label>
                            <select name="agency_profile_id" required class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                                <option value="">-- Pilih Dinas --</option>
                                @foreach($agencies as $ag)
                                    <option value="{{ $ag->id }}" {{ old('agency_profile_id') == $ag->id ? 'selected' : '' }}>{{ $ag->agency_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Status Keaktifan
                        </label>
                        <select name="status" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-medium">
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>🟢 Aktif (Tersedia Membimbing)</option>
                            <option value="on_leave" {{ old('status') === 'on_leave' ? 'selected' : '' }}>🟡 Cuti (Tidak Menerima Bimbingan)</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>🔴 Non-Aktif</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('admin.mentors.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                            Batal
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                            Simpan Mentor
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
