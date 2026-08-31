<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-600 hover:text-gray-900 transition">
                
            </a>
            <div>
                <h2 class="font-black text-xl text-gray-900 tracking-tight">
                    Tambah Pengguna Sistem Baru
                </h2>
                <p class="text-xs text-gray-500">Registrasi akun baru untuk admin, mentor, dosen, universitas, atau mahasiswa</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ selectedRole: '{{ old('role', 'mahasiswa') }}' }">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 sm:p-8">

                <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Nama Lengkap & Gelar <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Dr. Budi Santoso, M.Kom." class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Email Resmi / Akun Login <span class="text-rose-500">*</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="budi@surabaya.go.id" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Password Awal (Opsional)
                            </label>
                            <input type="text" name="password" placeholder="Default: password" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Role Pengguna <span class="text-rose-500">*</span>
                            </label>
                            <select name="role" x-model="selectedRole" required class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-semibold">
                                <option value="mahasiswa">Mahasiswa Pendaftar</option>
                                <option value="admin">Admin Instansi Dinas</option>
                                <option value="mentor">Mentor Lapangan Dinas</option>
                                <option value="dosen">Dosen Pembimbing (DPL)</option>
                                <option value="universitas">Admin Universitas</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Status Akun
                            </label>
                            <select name="status" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-medium">
                                <option value="active">Aktif</option>
                                <option value="on_leave">Cuti</option>
                                <option value="inactive">Non-Aktif</option>
                            </select>
                        </div>
                    </div>

                    <!-- Input Instansi Dinas jika Role Admin / Mentor -->
                    <div x-show="selectedRole === 'admin' || selectedRole === 'mentor'" x-cloak class="p-4 rounded-xl bg-blue-50/60 border border-blue-100 space-y-2">
                        <label class="block text-xs font-bold text-blue-900 uppercase tracking-wider">
                            Pilih Instansi Dinas Terkait
                        </label>
                        <select name="agency_profile_id" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                            <option value="">-- Tanpa Instansi / Global Superadmin --</option>
                            @foreach($agencies as $ag)
                                <option value="{{ $ag->id }}" {{ old('agency_profile_id') == $ag->id ? 'selected' : '' }}>
                                    {{ $ag->agency_name }} ({{ $ag->city }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Input Universitas jika Role Universitas / Dosen / Mahasiswa -->
                    <div x-show="selectedRole === 'universitas' || selectedRole === 'dosen' || selectedRole === 'mahasiswa'" x-cloak class="p-4 rounded-xl bg-sky-50/60 border border-sky-100 space-y-2">
                        <label class="block text-xs font-bold text-sky-900 uppercase tracking-wider">
                            Pilih Perguruan Tinggi Terkait
                        </label>
                        <select name="university_id" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                            <option value="">-- Pilih Universitas --</option>
                            @foreach($universities as $un)
                                <option value="{{ $un->id }}" {{ old('university_id') == $un->id ? 'selected' : '' }}>
                                    {{ $un->name }} ({{ $un->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                        <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                            Batal
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                            Daftarkan Pengguna
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
