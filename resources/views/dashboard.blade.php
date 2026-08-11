<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Mahasiswa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Welcome Banner -->
            <div class="bg-indigo-600 rounded-lg p-6 text-white shadow-lg flex justify-between items-center">
                <div>
                    <h3 class="text-2xl font-bold">Selamat Datang, {{ Auth::user()->name }}!</h3>
                    <p class="text-indigo-100 mt-1">Sistem Informasi Penerimaan Magang Instansi Pemerintahan Kota Surabaya</p>
                </div>
                <div class="hidden md:block">
                    <span class="bg-indigo-800 text-indigo-200 text-xs font-semibold px-3 py-1.5 rounded-full uppercase">
                        Role: {{ ucfirst(Auth::user()->role ?? 'Mahasiswa') }}
                    </span>
                </div>
            </div>

            @php
                $profile = Auth::user()->studentProfile;
                $application = Auth::user()->studentProfile ? App\Models\Application::where('user_id', Auth::id())->latest()->first() : null;
            @endphp

            <!-- Status Card Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Card 1: Profil -->
                <div class="bg-white p-6 rounded-lg shadow border-l-4 {{ $profile ? 'border-green-500' : 'border-yellow-500' }}">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Status Profil</p>
                            <p class="text-lg font-bold mt-1 text-gray-800">
                                {{ $profile ? 'Lengkap' : 'Belum Lengkap' }}
                            </p>
                        </div>
                        <div class="p-3 bg-gray-100 rounded-full">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                    </div>
                    <a href="{{ route('student.profile.edit') }}" class="inline-block mt-4 text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                        {{ $profile ? 'Edit Profil →' : 'Lengkapi Profil Sekarang →' }}
                    </a>
                </div>

                <!-- Card 2: Status Magang -->
                <div class="bg-white p-6 rounded-lg shadow border-l-4 
                    {{ optional($application)->status === 'accepted' ? 'border-green-500' : '' }}
                    {{ optional($application)->status === 'pending' ? 'border-yellow-500' : '' }}
                    {{ optional($application)->status === 'rejected' ? 'border-red-500' : '' }}
                    {{ !$application ? 'border-gray-300' : '' }}">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Status Pengajuan</p>
                            <p class="text-lg font-bold mt-1 text-gray-800">
                                {{ $application ? strtoupper($application->status) : 'Belum Mengajukan' }}
                            </p>
                        </div>
                        <div class="p-3 bg-gray-100 rounded-full">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                    </div>
                    @if(!$application)
                        <a href="{{ route('student.application.create') }}" class="inline-block mt-4 text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                            Buat Pengajuan Baru →
                        </a>
                    @else
                        <span class="inline-block mt-4 text-xs text-gray-500">Unit: {{ $application->unit->name ?? '-' }}</span>
                    @endif
                </div>

                <!-- Card 3: Pembimbing Lapangan -->
                <div class="bg-white p-6 rounded-lg shadow border-l-4 border-blue-500">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Pembimbing Lapangan</p>
                            <p class="text-base font-bold mt-1 text-gray-800">
                                {{ $application && $application->placement && $application->placement->pembimbing ? $application->placement->pembimbing->name : 'Belum Diplot' }}
                            </p>
                        </div>
                        <div class="p-3 bg-gray-100 rounded-full">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                    </div>
                    <p class="mt-4 text-xs text-gray-500">Ditentukan oleh Admin saat magang diterima.</p>
                </div>

            </div>

            <!-- Detail Banner Pengajuan (Jika Sudah Ngisi) -->
            @if ($application)
                <div class="bg-white p-6 rounded-lg shadow space-y-4">
                    <h4 class="font-bold text-gray-800 text-lg border-b pb-2">Detail Pengajuan Magang Aktif</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <p><span class="text-gray-500">Unit Instansi:</span> <strong>{{ $application->unit->name ?? '-' }}</strong></p>
                        <p><span class="text-gray-500">Tanggal Magang:</span> <strong>{{ $application->start_date }} s/d {{ $application->end_date }}</strong></p>
                        <p><span class="text-gray-500">Status Saat Ini:</span> 
                            <span class="px-2 py-1 text-xs font-bold rounded 
                                {{ $application->status === 'accepted' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $application->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $application->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ strtoupper($application->status) }}
                            </span>
                        </p>
                        @if ($application->status === 'rejected')
                            <p class="col-span-2 text-red-600"><strong>Catatan Penolakan:</strong> {{ $application->rejection_note }}</p>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>