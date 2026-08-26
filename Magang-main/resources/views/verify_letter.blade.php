<x-guest-layout>
    @php
        $agency = \App\Models\AgencyProfile::first();
        $govName = $agency->government_name ?? 'Pemerintah Kota Surabaya';
        $agencyName = $agency->agency_name ?? 'Dinas Komunikasi Dan Informatika';
    @endphp

    <div class="py-10 max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl rounded-2xl p-6 border-t-8 border-green-500">
            
            <!-- Header Logo / Instansi -->
            <div class="text-center pb-4 border-b">
                @if(!empty($agency->logo))
                    <img src="{{ asset('storage/' . $agency->logo) }}" alt="Logo Instansi" class="w-16 h-16 mx-auto mb-2 object-contain">
                @endif
                <h2 class="text-lg font-bold text-gray-800 uppercase tracking-wide">{{ $govName }}</h2>
                <h1 class="text-xl font-extrabold text-indigo-900 uppercase">{{ $agencyName }}</h1>
                <p class="text-xs text-gray-500 mt-1">Sistem Informasi Pendaftaran & Validasi Magang Resmi</p>
            </div>

            <!-- Badge Status Verifikasi Sah -->
            <div class="mt-6 flex items-center space-x-4 bg-green-50 p-4 rounded-xl border border-green-200 shadow-sm">
                <div class="flex-shrink-0 bg-green-500 text-white p-3 rounded-full shadow-md">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div>
                    <span class="inline-block px-2.5 py-0.5 text-xs font-bold bg-green-200 text-green-800 rounded-full mb-1">
                        STATUS: TERVERIFIKASI SAH
                    </span>
                    <h3 class="font-extrabold text-green-900 text-base leading-tight">DOKUMEN SURAT BALASAN RESMI</h3>
                    <p class="text-xs text-green-700 mt-0.5">Surat Keterangan Diterima Magang ini terdaftar dan tercatat valid pada database sistem.</p>
                </div>
            </div>

            <!-- Detail Informasi Dokumen -->
            <div class="mt-6">
                <h4 class="font-bold text-gray-800 text-sm uppercase tracking-wider border-b pb-2 mb-3">Detail Informasi Surat Penerimaan</h4>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">Nomor Surat Balasan:</span>
                        <span class="font-bold text-indigo-700 font-mono">{{ $application->letter_number ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">Tanggal Terbit Surat:</span>
                        <span class="font-semibold text-gray-800">{{ $application->letter_date ? \Carbon\Carbon::parse($application->letter_date)->translatedFormat('d F Y') : '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">Nama Mahasiswa:</span>
                        <span class="font-bold text-gray-900">{{ $application->user->name }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">NIM / NPM:</span>
                        <span class="font-semibold text-gray-800">{{ $application->user->studentProfile->nim ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">Universitas / Jurusan:</span>
                        <span class="font-semibold text-gray-800 text-right">{{ $application->user->studentProfile->universitas ?? '-' }} <br><span class="text-xs text-gray-500">({{ $application->user->studentProfile->jurusan ?? '-' }})</span></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">Unit Kerja Tujuan:</span>
                        <span class="font-bold text-green-700">{{ $application->unit->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">Periode Magang:</span>
                        <span class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($application->start_date)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($application->end_date)->translatedFormat('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-500 font-medium">Pembimbing Lapangan:</span>
                        <span class="font-semibold text-gray-800">{{ optional($application->placement)->pembimbing->name ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Footer Keamanan -->
            <div class="mt-8 pt-4 border-t text-center text-xs text-gray-400">
                <p>Halaman ini diterbitkan secara otomatis oleh Sistem Informasi Magang sebagai bentuk keaslian dokumen digital.</p>
                <p class="mt-1 font-semibold text-gray-500">© {{ date('Y') }} {{ $agencyName }} {{ $govName }}</p>
            </div>

        </div>
    </div>
</x-guest-layout>
