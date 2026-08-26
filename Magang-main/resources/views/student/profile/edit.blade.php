<x-app-layout>
    <x-slot name="header">
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengisian Profil Mahasiswa') }}
        </h2>
    </div>
</x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if (session('success'))
                    <div class="mb-4 text-sm font-medium text-green-600">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('student.profile.update') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="nim" value="NIM" />
                        <x-text-input id="nim" name="nim" type="text" class="mt-1 block w-full" :value="old('nim', $profile->nim ?? '')" required />
                    </div>

                    <div>
                        <x-input-label for="universitas" value="Universitas / Perguruan Tinggi" />
                        <x-text-input id="universitas" name="universitas" type="text" class="mt-1 block w-full" :value="old('universitas', $profile->universitas ?? '')" required />
                    </div>

                    <div>
                        <x-input-label for="jurusan" value="Jurusan / Program Studi" />
                        <x-text-input id="jurusan" name="jurusan" type="text" class="mt-1 block w-full" :value="old('jurusan', $profile->jurusan ?? '')" required />
                    </div>

                    <div>
                        <x-input-label for="phone" value="No. WhatsApp / HP" />
                        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $profile->phone ?? '')" required />
                    </div>

                    <div>
                        <x-input-label for="alamat" value="Alamat Domisili" />
                        <textarea id="alamat" name="alamat" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('alamat', $profile->alamat ?? '') }}</textarea>
                    </div>

                    <x-primary-button>
                        {{ __('Simpan Profil') }}
                    </x-primary-button>

                    <a href="{{ route('dashboard') }}">
                            <x-secondary-button>
                                {{ __('Kembali') }}
                            </x-secondary-button>
                        </a>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
