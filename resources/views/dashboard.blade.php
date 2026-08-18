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
                $isPassed = $application && $application->status === 'accepted' && 
                            optional($application->placement)->evaluation && 
                            optional(optional($application->placement)->finalreport)->status === 'approved';
            @endphp

            <!-- Banner Kelulusan & Unduh E-Sertifikat -->
            @if ($isPassed)
            <div class="bg-gradient-to-r from-green-500 to-emerald-700 rounded-xl p-6 text-white shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <div class="p-3 bg-white bg-opacity-20 rounded-full shadow-inner">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">🎉 Selamat! Anda telah menyelesaikan seluruh rangkaian magang.</h3>
                        <p class="text-green-100 mt-1 text-sm">Laporan akhir Anda telah disetujui dan penilaian telah lengkap. Anda dapat mengunduh E-Sertifikat resmi.</p>
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <a href="{{ route('student.certificate.download', $application->placement->id) }}" 
                       class="inline-flex items-center space-x-2 px-5 py-2.5 bg-white text-emerald-800 font-extrabold text-sm rounded-lg shadow-md hover:bg-emerald-50 hover:shadow-lg transition transform hover:-translate-y-0.5">
                        <span>📜</span>
                        <span>Unduh E-Sertifikat</span>
                    </a>
                </div>
            </div>
            @endif

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

                <!-- Card 4: Laporan Akhir (New) -->
                @if ($application && $application->status === 'accepted')
                <div class="bg-white p-6 rounded-lg shadow border-l-4 border-indigo-500">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Laporan Akhir</p>
                            <p class="text-base font-bold mt-1 text-gray-800">
                                @if(optional($application->placement)->finalreport)
                                    {{ strtoupper($application->placement->finalreport->status) }}
                                @else
                                    Belum Upload
                                @endif
                            </p>
                        </div>
                        <div class="p-3 bg-indigo-100 rounded-full">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                    </div>
                    <a href="{{ route('student.final_report.index') }}" class="inline-block mt-4 text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                        Kelola Laporan Akhir →
                    </a>
                </div>
                @endif

                <!-- Card 5: Penilaian Pembimbing (New) -->
                @if ($application && $application->status === 'accepted')
                <div class="bg-white p-6 rounded-lg shadow border-l-4 border-purple-500">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Penilaian Pembimbing</p>
                            @if(optional($application->placement)->evaluation)
                                @php
                                    $eval = $application->placement->evaluation;
                                    $rataRata = round(($eval->nilai_disiplin + $eval->nilai_kinerja + $eval->nilai_laporan) / 3, 2);
                                @endphp
                                <p class="text-2xl font-bold mt-1 text-gray-800">{{ $rataRata }}</p>
                                <p class="text-xs text-gray-500">Disiplin: {{ $eval->nilai_disiplin }}, Kinerja: {{ $eval->nilai_kinerja }}, Laporan: {{ $eval->nilai_laporan }}</p>
                            @else
                                <p class="text-base font-bold mt-1 text-gray-800">Belum Dinilai</p>
                            @endif
                        </div>
                        <div class="p-3 bg-purple-100 rounded-full">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                        </div>
                    </div>
                </div>
                @endif

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