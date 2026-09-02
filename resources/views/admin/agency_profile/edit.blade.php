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
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-xl shadow-xs text-sm">
                    <p class="font-bold flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
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
                           class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl transition {{ ($agencyProfile->id ?? $profile->id) === $agency->id ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>{{ $agency->agency_name }}</span>
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
                        <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
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
                            <img src="{{ $displayLogo }}" 
                                 alt="Logo {{ $agencyProfile->agency_name ?? $profile->agency_name }}" 
                                 class="w-16 h-16 object-contain border border-slate-200 p-1.5 rounded-2xl bg-white shadow-2xs">
                            <input type="file" id="logo" name="logo" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer" />
                        </div>
                    </div>
                </div>

                <!-- 2. KARTU PEJABAT PENANDATANGAN SURAT -->
                <div class="bg-white shadow-sm rounded-3xl p-6 sm:p-8 border border-slate-100 space-y-4">
                    <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center space-x-2">
                        <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
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
                        <x-primary-button class="px-6 py-2.5 inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Simpan Perubahan Profil Instansi</span>
                        </x-primary-button>
                    </div>
                </div>

            </form>



        </div>
    </div>
</x-app-layout>
