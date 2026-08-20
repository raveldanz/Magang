<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-gray-800 leading-tight">
                {{ __('Pengisian Profil Mahasiswa') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

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

            <div class="bg-white rounded-2xl p-6 sm:p-8 border border-gray-200 shadow-sm space-y-6">
                
                <div class="border-b border-gray-100 pb-4">
                    <h3 class="text-lg font-bold text-gray-900">Data Akademik & Pribadi Mahasiswa</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Lengkapi informasi perguruan tinggi dan kontak aktif untuk keperluan penempatan magang</p>
                </div>

                <form action="{{ route('student.profile.update') }}" method="POST" class="space-y-5">
                    @csrf

                    <!-- Grid NIM & Nama -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="name" value="Nama Lengkap (Akun)" class="text-xs font-bold uppercase tracking-wider" />
                            <x-text-input id="name" type="text" class="mt-1 block w-full bg-gray-50 text-gray-500 cursor-not-allowed text-xs sm:text-sm" :value="Auth::user()->name" disabled />
                        </div>

                        <div>
                            <x-input-label for="nim" value="Nomor Induk Mahasiswa (NIM)" class="text-xs font-bold uppercase tracking-wider" />
                            <x-text-input id="nim" name="nim" type="text" class="mt-1 block w-full text-xs sm:text-sm" :value="old('nim', $profile->nim ?? '')" placeholder="Contoh: 22081010001" required />
                            <x-input-error :messages="$errors->get('nim')" class="mt-1" />
                        </div>
                    </div>

                    <!-- Input Universitas dengan Datalist Searchable -->
                    <div>
                        <x-input-label for="universitas" value="Universitas / Perguruan Tinggi" class="text-xs font-bold uppercase tracking-wider" />
                        <div class="relative mt-1">
                            <x-text-input 
                                id="universitas" 
                                name="universitas" 
                                list="universities_list" 
                                type="text" 
                                class="block w-full text-xs sm:text-sm pr-10" 
                                :value="old('universitas', $profile->universitas ?? Auth::user()->university ?? '')" 
                                placeholder="Ketik atau pilih nama perguruan tinggi..." 
                                required 
                                autocomplete="off" />
                            
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        <!-- Daftar Saran Universitas -->
                        <datalist id="universities_list">
                            @foreach ($universities as $univ)
                                <option value="{{ $univ->name }}">{{ $univ->code ? '(' . $univ->code . ')' : '' }}</option>
                            @endforeach
                        </datalist>

                        <p class="text-[11px] text-gray-500 mt-1.5 flex items-center gap-1">
                            <span>💡</span>
                            <span>Pilih universitas dari daftar yang tersedia, atau ketik nama universitas baru jika belum terdaftar.</span>
                        </p>
                        <x-input-error :messages="$errors->get('universitas')" class="mt-1" />
                    </div>

                    <!-- Jurusan & No HP -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="jurusan" value="Jurusan / Program Studi" class="text-xs font-bold uppercase tracking-wider" />
                            <x-text-input id="jurusan" name="jurusan" type="text" class="mt-1 block w-full text-xs sm:text-sm" :value="old('jurusan', $profile->jurusan ?? '')" placeholder="Contoh: Informatika / Sains Data" required />
                            <x-input-error :messages="$errors->get('jurusan')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="phone" value="No. WhatsApp / HP Aktif" class="text-xs font-bold uppercase tracking-wider" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full text-xs sm:text-sm" :value="old('phone', $profile->phone ?? '')" placeholder="Contoh: 081234567890" required />
                            <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                        </div>
                    </div>

                    <!-- Alamat Domisili -->
                    <div>
                        <x-input-label for="alamat" value="Alamat Domisili / Tempat Tinggal di Surabaya" class="text-xs font-bold uppercase tracking-wider" />
                        <textarea id="alamat" name="alamat" rows="3" class="mt-1 block w-full text-xs sm:text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-xs" placeholder="Tuliskan alamat lengkap domisili saat ini...">{{ old('alamat', $profile->alamat ?? '') }}</textarea>
                        <x-input-error :messages="$errors->get('alamat')" class="mt-1" />
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                            &larr; Kembali ke Dashboard
                        </a>

                        <x-primary-button class="text-xs px-5 py-2.5">
                            {{ __('Simpan Perubahan Profil') }}
                        </x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
