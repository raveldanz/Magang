<x-guest-layout>
    <x-verify-detail-style />
    @php
        $agency = $application->unit?->agencyProfile ?? \App\Models\AgencyProfile::first();
        $govName = $agency->government_name ?? 'Pemerintah Kota Surabaya';
        $agencyName = $agency->agency_name ?? 'Dinas Perpustakaan dan Kearsipan';
    @endphp


    <div class="py-2 sm:py-4 w-full">
        <div class="bg-white overflow-hidden shadow-xl shadow-slate-200/60 rounded-3xl p-5 sm:p-6 border border-slate-200/80 border-t-8 border-t-emerald-600">
            
            <!-- Header Logo / Instansi -->
            <div class="text-center pb-4 border-b border-slate-100">
                <div class="flex items-center justify-center gap-3 mb-2.5">
                    <img src="{{ asset('images/logos/surabaya.png') }}" alt="Logo {{ $govName }}" class="h-12 sm:h-14 w-auto object-contain">
                    @if($agency)
                        <div class="h-7 w-px bg-slate-200"></div>
                        <img src="{{ $agency->logo_url }}" alt="Logo {{ $agencyName }}" class="h-12 sm:h-14 w-auto max-w-[120px] object-contain">
                    @endif
                </div>
                <h2 class="text-xs font-bold text-slate-600 uppercase tracking-widest">{{ $govName }}</h2>
                <h1 class="text-lg sm:text-xl font-black text-slate-900 uppercase tracking-wide mt-0.5">{{ $agencyName }}</h1>
                <p class="text-xs text-slate-500 mt-1">Sistem Informasi Pendaftaran &amp; Validasi Magang Resmi</p>
            </div>

            <!-- Badge Status Verifikasi Sah -->
            <div class="vstatus mt-6 flex items-center space-x-4 bg-emerald-50 p-4 rounded-2xl border border-emerald-200 shadow-xs">
                <div class="vstatus-icon flex-shrink-0 bg-emerald-600 text-white p-3 rounded-full shadow-md">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div>
                    <span class="vbadge inline-block px-2.5 py-0.5 text-xs font-black bg-emerald-200 text-emerald-900 rounded-full mb-1">
                        TERVERIFIKASI SAH
                    </span>
                    <h3 class="font-extrabold text-emerald-950 text-base leading-tight">DOKUMEN SURAT BALASAN RESMI</h3>
                    <p class="text-xs text-emerald-800 mt-0.5">Surat Keterangan Diterima Magang ini terdaftar dan tercatat valid pada database sistem.</p>
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
                    @if($application->group_id && $application->group)
                        @php
                            $verifiedGroupMembers = $application->group->applications->filter(
                                fn ($gApp) => in_array($gApp->statusValue(), ['accepted', 'active', 'completed', 'waiting_approval'], true)
                            );
                        @endphp
                        <div class="vrow">
                            <dt class="vlabel">Tipe Pengajuan</dt>
                            <dd class="vvalue"><span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800">Kelompok Magang ({{ $verifiedGroupMembers->count() }} Peserta Magang)</span></dd>
                        </div>
                        <div class="vrow">
                            <dt class="vlabel">Daftar Anggota Kelompok</dt>
                            <dd class="vvalue">
                                <ul class="divide-y divide-slate-100 mt-1 border rounded-lg bg-slate-50 p-2 text-xs space-y-1.5">
                                    @foreach($verifiedGroupMembers as $gApp)
                                        <li class="flex items-start justify-between gap-2 py-1.5">
                                            <div class="min-w-0">
                                                <span class="font-bold text-slate-800 break-words">{{ $gApp->user?->name }}</span>
                                                <span class="text-slate-500 font-mono">({{ $gApp->user?->studentProfile?->nim ?? 'NIM' }})</span>
                                                <div class="text-[11px] text-slate-500">{{ $gApp->user?->studentProfile?->jurusan ?? '-' }}</div>
                                            </div>
                                            <span class="text-[11px] font-bold px-2 py-0.5 rounded shrink-0 {{ $gApp->role_in_group === 'leader' ? 'bg-blue-100 text-blue-700' : 'bg-slate-200 text-slate-700' }}">
                                                {{ $gApp->role_in_group === 'leader' ? 'Ketua' : 'Anggota' }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </dd>
                        </div>
                    @else
                        <div class="vrow">
                            <dt class="vlabel">Nama Peserta Magang</dt>
                            <dd class="vvalue">{{ $application->user->name }}</dd>
                        </div>
                        <div class="vrow">
                            <dt class="vlabel">{{ $sp?->identityNumberLabel() ?? 'NIM/NIS' }}</dt>
                            <dd class="vvalue is-mono">{{ $sp?->nim ?? '-' }}</dd>
                        </div>
                        <div class="vrow">
                            <dt class="vlabel">{{ $sp?->isSchoolStudent() ? 'Sekolah / Jurusan' : 'Universitas / Jurusan' }}</dt>
                            <dd class="vvalue">{{ $sp?->universitas ?? '-' }}<span class="vsub">{{ $sp?->jurusan ?? '-' }}</span></dd>
                        </div>
                    @endif
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
            <div class="mt-8 pt-4 border-t border-slate-100 text-center text-xs text-slate-500 space-y-2">
                <p>Halaman ini diterbitkan secara otomatis oleh Sistem Informasi Magang sebagai bentuk keaslian dokumen digital.</p>
                <p class="font-semibold text-slate-600">© {{ date('Y') }} {{ $agencyName }} {{ $govName }}</p>
                <div class="pt-1">
                    <a href="{{ url('/') }}" class="inline-flex items-center justify-center min-h-[40px] px-3 text-xs font-bold text-blue-600 hover:text-blue-800 rounded-xl hover:bg-blue-50/70 transition">
                        Kembali ke Beranda Portal Magang
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-guest-layout>
