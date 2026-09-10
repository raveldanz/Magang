<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            {{ __('Pengaturan Profil Instansi & TTD Surat Balasan') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-xl shadow-xs text-sm font-bold flex items-center gap-2">
                    <span></span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-xl shadow-xs text-sm">
                    <p class="font-bold flex items-center gap-2">
                        <span></span>
                        <span>Gagal Menyimpan Perubahan:</span>
                    </p>
                    <ul class="mt-1 list-disc list-inside text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(!empty($isSuperAdmin) && isset($allAgencies) && $allAgencies->count() > 1)
                <!-- Agency Switcher Tabs (Khusus Superadmin) -->
                <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-3">
                    @foreach($allAgencies as $agency)
                        <a href="{{ route('admin.agency_profile.edit', ['agency_id' => $agency->id]) }}"
                           class="px-4 py-2 text-xs font-bold rounded-xl transition {{ ($agencyProfile->id ?? $profile->id) === $agency->id ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200' }}">
                             {{ $agency->agency_name }}
                        </a>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('admin.agency_profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="agency_id" value="{{ $agencyProfile->id ?? $profile->id }}">

                <!-- 1. KARTU INFORMASI INSTANSI & KOP SURAT -->
                <div class="bg-white shadow-sm rounded-3xl p-6 sm:p-8 border border-slate-100 space-y-4">
                    <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center space-x-2">
                        <span></span>
                        <span>Identitas Pemerintah & Instansi Kerja</span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="government_name" value="Nama Pemerintah / Kota / Provinsi" class="text-xs font-bold text-slate-700 uppercase" />
                            <x-text-input id="government_name" name="government_name" type="text" class="mt-1 block w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm" 
                                value="{{ old('government_name', $agencyProfile->government_name ?? $profile->government_name) }}" required />
                        </div>

                        <div>
                            <x-input-label for="agency_name" value="Nama Dinas / Instansi Kerja" class="text-xs font-bold text-slate-700 uppercase" />
                            <x-text-input id="agency_name" name="agency_name" type="text" class="mt-1 block w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm" 
                                value="{{ old('agency_name', $agencyProfile->agency_name ?? $profile->agency_name) }}" required />
                        </div>

                        <div>
                            <x-input-label for="city" value="Kota Tempat Penerbitan Surat" class="text-xs font-bold text-slate-700 uppercase" />
                            <x-text-input id="city" name="city" type="text" class="mt-1 block w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm" 
                                value="{{ old('city', $agencyProfile->city ?? $profile->city ?? 'Surabaya') }}" required />
                        </div>

                        <div>
                            <x-input-label for="phone" value="No. Telepon Instansi" class="text-xs font-bold text-slate-700 uppercase" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm" 
                                value="{{ old('phone', $agencyProfile->phone ?? $profile->phone) }}" />
                        </div>

                        <div>
                            <x-input-label for="email" value="Email Resmi Instansi" class="text-xs font-bold text-slate-700 uppercase" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm" 
                                value="{{ old('email', $agencyProfile->email ?? $profile->email) }}" />
                        </div>

                        <div>
                            <x-input-label for="website" value="Website Resmi" class="text-xs font-bold text-slate-700 uppercase" />
                            <x-text-input id="website" name="website" type="text" class="mt-1 block w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm" 
                                value="{{ old('website', $agencyProfile->website ?? $profile->website) }}" />
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-input-label for="address" value="Alamat Lengkap Kantor Instansi (Kop Surat)" class="text-xs font-bold text-slate-700 uppercase" />
                        <textarea id="address" name="address" rows="2" class="mt-1 block w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 shadow-2xs text-xs sm:text-sm">{{ old('address', $agencyProfile->address ?? $profile->address) }}</textarea>
                    </div>

                    <!-- Upload Logo -->
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <x-input-label for="logo" value="Logo Resmi Instansi" class="text-xs font-bold text-slate-700 uppercase" />
                        <div class="flex items-center space-x-4 mt-2">
                            @php
                                $currentLogo = $agencyProfile->logo ?? $profile->logo ?? null;
                                $displayLogo = null;
                                if ($currentLogo && file_exists(public_path($currentLogo))) {
                                    $displayLogo = asset($currentLogo);
                                } elseif ($currentLogo && file_exists(storage_path('app/public/' . $currentLogo))) {
                                    $displayLogo = asset('storage/' . $currentLogo);
                                } else {
                                    $displayLogo = asset('images/logos/surabaya.png');
                                }
                            @endphp
                            <div class="flex flex-col sm:flex-row sm:items-center gap-3.5 p-3.5 sm:p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                                <div class="flex items-center gap-3 shrink-0">
                                    <div class="w-16 h-16 rounded-2xl bg-white border border-slate-200 shadow-2xs flex items-center justify-center p-2 shrink-0 overflow-hidden" style="width: 64px; height: 64px; min-width: 64px; min-height: 64px;">
                                        <img id="agencyLogoPreview" src="{{ $displayLogo }}" 
                                             alt="Logo {{ $agencyProfile->agency_name ?? $profile->agency_name }}" 
                                             class="w-full h-full object-contain shrink-0 transition-all duration-200">
                                    </div>
                                    <div class="sm:hidden text-xs">
                                        <span class="font-bold text-slate-800 block">Pratinjau Logo</span>
                                        <span class="text-[11px] text-slate-500">Maksimal ukuran 2MB</span>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <input type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml"
                                           onchange="handleLogoPreview(this, 'agencyLogoPreview', '{{ $displayLogo }}')"
                                           class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer shadow-2xs" />
                                    <p class="text-[11px] text-gray-500 mt-1.5 leading-relaxed">Format: PNG, JPG, WEBP, SVG. Maks 2MB. Biarkan kosong jika tidak ingin mengubah logo.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. KARTU PEJABAT PENANDATANGAN SURAT -->
                <div class="bg-white shadow-sm rounded-3xl p-6 sm:p-8 border border-slate-100 space-y-4">
                    <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center space-x-2">
                        <span></span>
                        <span>Pejabat Penandatangan Surat Balasan (TTD Official)</span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="signee_name" value="Nama Pejabat (Lengkap Gelar)" class="text-xs font-bold text-slate-700 uppercase" />
                            <x-text-input id="signee_name" name="signee_name" type="text" class="mt-1 block w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm" 
                                value="{{ old('signee_name', $agencyProfile->signee_name ?? $profile->signee_name) }}" required />
                        </div>

                        <div>
                            <x-input-label for="signee_nip" value="NIP Pejabat" class="text-xs font-bold text-slate-700 uppercase" />
                            <x-text-input id="signee_nip" name="signee_nip" type="text" class="mt-1 block w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm" 
                                value="{{ old('signee_nip', $agencyProfile->signee_nip ?? $profile->signee_nip) }}" />
                        </div>

                        <div>
                            <x-input-label for="signee_position" value="Jabatan Resmi Pejabat" class="text-xs font-bold text-slate-700 uppercase" />
                            <x-text-input id="signee_position" name="signee_position" type="text" class="mt-1 block w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm" 
                                value="{{ old('signee_position', $agencyProfile->signee_position ?? $profile->signee_position) }}" required />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="w-full sm:w-auto px-6 py-3 sm:py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer text-center justify-center flex items-center">
                             Simpan Perubahan Profil Instansi
                        </button>
                    </div>
                </div>

            </form>



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
