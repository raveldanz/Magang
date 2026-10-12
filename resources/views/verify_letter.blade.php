<x-guest-layout>
    <x-verify-detail-style />
    @php
        $agency = $application->unit?->agencyProfile ?? \App\Models\AgencyProfile::first();
        $govName = $agency->government_name ?? 'Pemerintah Kota Surabaya';
        $agencyName = $agency->agency_name ?? 'Dinas Komunikasi Dan Informatika';
    @endphp


    <div class="py-10 max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl rounded-2xl p-6 border-t-8 border-green-500">
            
            <!-- Header Logo / Instansi -->
            <div class="text-center pb-4 border-b">
                @if($agency)
                    <img src="{{ $agency->logo_url }}" alt="Logo {{ $agencyName }}" class="w-16 h-16 mx-auto mb-2 object-contain">
                @endif
                <h2 class="text-lg font-bold text-gray-800 uppercase tracking-wide">{{ $govName }}</h2>
                <h1 class="text-xl font-extrabold text-blue-900 uppercase">{{ $agencyName }}</h1>
                <p class="text-xs text-gray-500 mt-1">Sistem Informasi Pendaftaran & Validasi Magang Resmi</p>
            </div>

            <!-- Badge Status Verifikasi Sah -->
            <div class="vstatus mt-6 flex items-center space-x-4 bg-green-50 p-4 rounded-xl border border-green-200 shadow-sm">
                <div class="vstatus-icon flex-shrink-0 bg-green-500 text-white p-3 rounded-full shadow-md">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div>
                    <span class="vbadge inline-block px-2.5 py-0.5 text-xs font-bold bg-green-200 text-green-800 rounded-full mb-1">
                        TERVERIFIKASI SAH
                    </span>
                    <h3 class="font-extrabold text-green-900 text-base leading-tight">DOKUMEN SURAT BALASAN RESMI</h3>
                    <p class="text-xs text-green-700 mt-0.5">Surat Keterangan Diterima Magang ini terdaftar dan tercatat valid pada database sistem.</p>
                </div>
            </div>

            <!-- Detail Informasi Dokumen -->
            <div class="mt-6">
                <h4 class="font-bold text-gray-800 text-sm uppercase tracking-wider border-b pb-2 mb-3">Detail Informasi Surat Penerimaan</h4>
                
                
                @php
                    $fmtDate = fn ($d) => $d ? \Carbon\Carbon::parse($d)->locale('id')->translatedFormat('d F Y') : '-';
                    $sp = $application->user?->studentProfile;
                @endphp
                <dl class="vlist">
                    <div class="vrow">
                        <dt class="vlabel">Nomor Surat Balasan</dt>
                        <dd class="vvalue is-accent is-mono">{{ $application->letter_number ?? '-' }}</dd>
                    </div>
                    @if($application->letter_token)
                        <div class="vrow">
                            <dt class="vlabel">Kode Verifikasi</dt>
                            <dd class="vvalue"><span class="vcode">@foreach (str_split($application->letter_token, 4) as $chunk)<span>{{ $chunk }}</span>@endforeach</span></dd>
                        </div>
                    @endif
                    <div class="vrow">
                        <dt class="vlabel">Tanggal Terbit Surat</dt>
                        <dd class="vvalue">{{ $fmtDate($application->letter_date) }}</dd>
                    </div>
                    <div class="vrow">
                        <dt class="vlabel">Nama Mahasiswa</dt>
                        <dd class="vvalue">{{ $application->user->name }}</dd>
                    </div>
                    <div class="vrow">
                        <dt class="vlabel">NIM / NPM</dt>
                        <dd class="vvalue is-mono">{{ $sp?->nim ?? '-' }}</dd>
                    </div>
                    <div class="vrow">
                        <dt class="vlabel">Universitas / Jurusan</dt>
                        <dd class="vvalue">{{ $sp?->universitas ?? '-' }}<span class="vsub">{{ $sp?->jurusan ?? '-' }}</span></dd>
                    </div>
                    <div class="vrow">
                        <dt class="vlabel">Unit Kerja Tujuan</dt>
                        <dd class="vvalue is-green">{{ $application->unit->name ?? '-' }}</dd>
                    </div>
                    <div class="vrow">
                        <dt class="vlabel">Periode Magang</dt>
                        <dd class="vvalue">{{ $fmtDate($application->start_date) }} s/d {{ $fmtDate($application->end_date) }}</dd>
                    </div>
                    <div class="vrow">
                        <dt class="vlabel">Pembimbing Lapangan</dt>
                        <dd class="vvalue">{{ $application->placement?->pembimbing?->name ?? $application->placement?->mentor?->name ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Footer Keamanan -->
            <div class="mt-8 pt-4 border-t text-center text-xs text-gray-400">
                <p>Halaman ini diterbitkan secara otomatis oleh Sistem Informasi Magang sebagai bentuk keaslian dokumen digital.</p>
                <p class="mt-1 font-semibold text-gray-500">© {{ date('Y') }} {{ $agencyName }} {{ $govName }}</p>
            </div>

        </div>
    </div>
</x-guest-layout>
