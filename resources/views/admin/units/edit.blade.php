<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.units.index') }}" class="p-2 bg-white hover:bg-gray-100 border border-gray-200 text-gray-700 rounded-xl transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    Edit Divisi / Unit Kerja
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Ubah informasi divisi, kualifikasi, atau kuota penerimaan</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white rounded-2xl p-6 sm:p-8 border border-gray-200 shadow-sm">
                
                <form action="{{ route('admin.units.update', $unit->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Instansi Induk -->
                    @if (Auth::user()->agency_profile_id === null && count($agencies) > 1)
                        <div>
                            <x-input-label for="agency_profile_id" value="Instansi Induk" />
                            <select id="agency_profile_id" name="agency_profile_id" class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" required>
                                @foreach ($agencies as $agency)
                                    <option value="{{ $agency->id }}" {{ old('agency_profile_id', $unit->agency_profile_id) == $agency->id ? 'selected' : '' }}>
                                        🏛️ {{ $agency->agency_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('agency_profile_id')
                                <span class="text-rose-600 text-xs font-medium block mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    @else
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Instansi Induk</span>
                            <p class="text-sm font-semibold text-gray-800 mt-1">🏛️ {{ $unit->agencyProfile->agency_name ?? '-' }}</p>
                        </div>
                    @endif

                    <!-- Nama Divisi / Bidang -->
                    <div>
                        <x-input-label for="name" value="Nama Bidang / Divisi / Unit Kerja" />
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            value="{{ old('name', $unit->name) }}" 
                            class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                            required
                        >
                        @error('name')
                            <span class="text-rose-600 text-xs font-medium block mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Kuota Penerimaan -->
                    <div>
                        <x-input-label for="quota" value="Kapasitas Kuota Mahasiswa Magang" />
                        <input 
                            type="number" 
                            id="quota" 
                            name="quota" 
                            min="0" 
                            max="100" 
                            value="{{ old('quota', $unit->quota) }}" 
                            class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                            required
                        >
                        <p class="text-xs text-gray-500 mt-1">Saat ini: <strong>{{ $unit->applications->where('status', 'accepted')->count() }}</strong> mahasiswa diterima.</p>
                        @error('quota')
                            <span class="text-rose-600 text-xs font-medium block mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Deskripsi & Kualifikasi Tugas -->
                    <div>
                        <x-input-label for="description" value="Deskripsi Tugas & Kualifikasi Divisi" />
                        <textarea 
                            id="description" 
                            name="description" 
                            rows="4" 
                            class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                        >{{ old('description', $unit->description) }}</textarea>
                        @error('description')
                            <span class="text-rose-600 text-xs font-medium block mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                        <a href="{{ route('admin.units.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                            Batal
                        </a>
                        <button type="submit" class="px-7 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition cursor-pointer">
                            Perbarui Data Divisi
                        </button>
                    </div>

                </form>

            </div>

        </div>
    </div>
</x-app-layout>
