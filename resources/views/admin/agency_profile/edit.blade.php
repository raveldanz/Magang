<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengaturan Profil Instansi & TTD Surat Balasan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 bg-green-100 border-l-4 border-green-500 text-green-800 rounded-lg shadow-sm text-sm font-bold">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-lg shadow-sm text-sm">
                    <p class="font-bold">Gagal Menyimpan Perubahan:</p>
                    <ul class="mt-1 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(!empty($isSuperAdmin) && isset($allAgencies) && $allAgencies->count() > 1)
                <!-- Agency Switcher Tabs (Khusus Superadmin) -->
                <div class="flex flex-wrap gap-2 border-b pb-3">
                    @foreach($allAgencies as $agency)
                        <a href="{{ route('admin.agency_profile.edit', ['agency_id' => $agency->id]) }}"
                           class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors {{ ($agencyProfile->id ?? $profile->id) === $agency->id ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
                            🏛️ {{ $agency->agency_name }}
                        </a>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('admin.agency_profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="agency_id" value="{{ $agencyProfile->id ?? $profile->id }}">

                <!-- 1. KARTU INFORMASI INSTANSI & KOP SURAT -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center space-x-2">
                        <span>🏛️</span>
                        <span>Identitas Pemerintah & Instansi Kerja</span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="government_name" value="Nama Pemerintah / Kota / Provinsi" />
                            <x-text-input id="government_name" name="government_name" type="text" class="mt-1 block w-full" 
                                value="{{ old('government_name', $agencyProfile->government_name ?? $profile->government_name) }}" required />
                        </div>

                        <div>
                            <x-input-label for="agency_name" value="Nama Dinas / Instansi Kerja" />
                            <x-text-input id="agency_name" name="agency_name" type="text" class="mt-1 block w-full" 
                                value="{{ old('agency_name', $agencyProfile->agency_name ?? $profile->agency_name) }}" required />
                        </div>

                        <div>
                            <x-input-label for="city" value="Kota Tempat Penerbitan Surat" />
                            <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" 
                                value="{{ old('city', $agencyProfile->city ?? $profile->city) }}" required />
                        </div>

                        <div>
                            <x-input-label for="phone" value="No. Telepon Instansi" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" 
                                value="{{ old('phone', $agencyProfile->phone ?? $profile->phone) }}" />
                        </div>

                        <div>
                            <x-input-label for="email" value="Email Resmi Instansi" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" 
                                value="{{ old('email', $agencyProfile->email ?? $profile->email) }}" />
                        </div>

                        <div>
                            <x-input-label for="website" value="Website Resmi" />
                            <x-text-input id="website" name="website" type="text" class="mt-1 block w-full" 
                                value="{{ old('website', $agencyProfile->website ?? $profile->website) }}" />
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-input-label for="address" value="Alamat Lengkap Kantor Instansi (Kop Surat)" />
                        <textarea id="address" name="address" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">{{ old('address', $agencyProfile->address ?? $profile->address) }}</textarea>
                    </div>

                    <!-- Upload Logo -->
                    <div class="mt-4 pt-4 border-t">
                        <x-input-label for="logo" value="Logo Resmi Instansi (Untuk Kop Surat)" />
                        <div class="flex items-center space-x-4 mt-2">
                            <img src="{{ ($agencyProfile->logo ?? $profile->logo) ? asset('storage/' . ($agencyProfile->logo ?? $profile->logo)) : asset('images/logo-surabaya.png') }}" 
                                 alt="Logo {{ $agencyProfile->agency_name ?? $profile->agency_name }}" 
                                 class="w-16 h-16 object-contain border p-1 rounded-lg bg-white shadow-sm">
                            <input type="file" id="logo" name="logo" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                        </div>
                    </div>
                </div>

                <!-- 2. KARTU PEJABAT PENANDATANGAN SURAT -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center space-x-2">
                        <span>✍️</span>
                        <span>Pejabat Penandatangan Surat Balasan (TTD Official)</span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="signee_name" value="Nama Pejabat (Lengkap Gelar)" />
                            <x-text-input id="signee_name" name="signee_name" type="text" class="mt-1 block w-full" 
                                value="{{ old('signee_name', $agencyProfile->signee_name ?? $profile->signee_name) }}" required />
                        </div>

                        <div>
                            <x-input-label for="signee_nip" value="NIP Pejabat" />
                            <x-text-input id="signee_nip" name="signee_nip" type="text" class="mt-1 block w-full" 
                                value="{{ old('signee_nip', $agencyProfile->signee_nip ?? $profile->signee_nip) }}" />
                        </div>

                        <div>
                            <x-input-label for="signee_position" value="Jabatan Resmi Pejabat" />
                            <x-text-input id="signee_position" name="signee_position" type="text" class="mt-1 block w-full" 
                                value="{{ old('signee_position', $agencyProfile->signee_position ?? $profile->signee_position) }}" required />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <x-primary-button class="px-6 py-2">
                            💾 Simpan Perubahan Profil Instansi
                        </x-primary-button>
                    </div>
                </div>

            </form>


        </div>
    </div>
</x-app-layout>
