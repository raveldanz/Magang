<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                   
                    <span>Master Perguruan Tinggi Mitra (Universitas)</span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Kelola profil kampus, PIC Rektorat/Fakultas, data logo, dan statistik mahasiswa magang
                </p>
            </div>

            <a href="{{ route('admin.universities.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                <span>Tambah Universitas Baru</span>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Alert Success (Disembunyikan jika kartu kredensial baru aktif agar tidak ada pesan duplikat) -->
            @if (session('success') && !session('new_university_credential'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-xs flex items-center justify-between text-rose-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- Credential Flash Alert (Tema Resmi Kedinasan Pemerintah Kota Surabaya - Kemitraan Kampus) -->
            @if (session('new_university_credential'))
                @php $cred = session('new_university_credential'); @endphp
                <div class="relative overflow-hidden rounded-3xl shadow-2xl border border-blue-400/30 p-6 sm:p-7 space-y-5"
                     style="background: linear-gradient(135deg, #09172e 0%, #0d2857 45%, #07152c 100%) !important; color: #ffffff !important;">
                    
                    <!-- Watermark Lambang Perisai Kedinasan Pemkot Surabaya -->
                    <div class="absolute -right-8 -bottom-10 pointer-events-none opacity-[0.06] text-white">
                        <svg class="w-72 h-72" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 2.18l7 3.12v4.7c0 4.67-3.13 9.04-7 10.19-3.87-1.15-7-5.52-7-10.19V6.3l7-3.12z"/>
                        </svg>
                    </div>

                    <!-- Header Bagian Atas: Judul dan Tombol Aksi -->
                    <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-blue-400/20 pb-5">
                        <div class="space-y-1">
                            <h3 class="font-black text-lg sm:text-xl text-white tracking-tight flex items-center gap-2">
                                <span>Akun Admin Kampus Baru Berhasil Dibuat!</span>
                            </h3>
                            <p class="text-xs text-blue-100/80 leading-relaxed">
                                Kredensial login resmi untuk mitra <strong class="text-amber-300 font-bold underline decoration-amber-400/40 underline-offset-2">{{ $cred['univ_name'] }}</strong> siap diteruskan ke pihak kemahasiswaan/rektorat.
                            </p>
                        </div>

                        <!-- Action Buttons Group: Salin Kredensial & Login As -->
                        <div class="flex flex-wrap items-center gap-2.5 shrink-0 pt-1 lg:pt-0">
                            <!-- Tombol Salin Semua Kredensial -->
                            <button type="button" 
                                    id="btnCopyCredUniv"
                                    onclick="navigator.clipboard.writeText('Universitas: {{ addslashes($cred['univ_name']) }}\nPortal: {{ $cred['login_url'] }}\nEmail: {{ $cred['email'] }}\nPassword: {{ $cred['password'] }}'); this.innerHTML = '<svg class=\'w-4 h-4 text-emerald-300\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M5 13l4 4L19 7\'/></svg><span>Tersalin ke Clipboard!</span>'; setTimeout(() => this.innerHTML = '<svg class=\'w-4 h-4 text-blue-200\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3\'/></svg><span>Salin Semua Kredensial</span>', 3000);"
                                    class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-blue-950/70 hover:bg-blue-900/80 text-blue-100 border border-blue-400/30 rounded-xl text-xs font-bold transition shadow-sm hover:border-blue-300 cursor-pointer active:scale-95">
                                <svg class="w-4 h-4 text-blue-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                <span>Salin Semua Kredensial</span>
                            </button>

                            <!-- Tombol Login As Super Admin (Langsung Masuk Sekali Klik) -->
                            @if($isSuperAdmin && !empty($cred['user_id']))
                                <form action="{{ route('admin.impersonate', $cred['user_id']) }}" method="POST" class="inline-block m-0">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-600 hover:from-emerald-600 hover:to-teal-700 text-white rounded-xl text-xs font-black transition-all shadow-lg shadow-emerald-950/60 hover:shadow-emerald-900/80 border border-emerald-300/40 active:scale-95 cursor-pointer">
                                        <svg class="w-4 h-4 text-emerald-100 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                        </svg>
                                        <span>Masuk Sebagai Admin Kampus (Login As)</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <!-- 3 Kartu Kredensial Formal -->
                    <div class="relative z-10 grid grid-cols-1 md:grid-cols-3 gap-3.5">
                        <!-- URL Login Portal -->
                        <div class="p-4 rounded-2xl border border-blue-400/20 bg-slate-900/60 backdrop-blur-md flex flex-col justify-between hover:border-blue-400/40 transition group">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-sky-300 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-sky-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" stroke-width="2"/>
                                        <line x1="2" y1="12" x2="22" y2="12" stroke-width="2"/>
                                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" stroke-width="2"/>
                                    </svg>
                                    URL Portal Login
                                </span>
                                <button type="button" onclick="navigator.clipboard.writeText('{{ $cred['login_url'] }}');" class="text-blue-300 hover:text-white transition text-[10px] font-bold px-1.5 py-0.5 rounded bg-blue-800/40 hover:bg-blue-700/60 cursor-pointer" title="Salin URL">
                                    Salin
                                </button>
                            </div>
                            <div class="font-mono font-bold text-xs text-sky-300 select-all break-all mt-1">
                                {{ $cred['login_url'] }}
                            </div>
                        </div>

                        <!-- Email / Username -->
                        <div class="p-4 rounded-2xl border border-blue-400/20 bg-slate-900/60 backdrop-blur-md flex flex-col justify-between hover:border-blue-400/40 transition group">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-300 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" /></svg>
                                    Email / Akun Kampus
                                </span>
                                <button type="button" onclick="navigator.clipboard.writeText('{{ $cred['email'] }}');" class="text-emerald-300 hover:text-white transition text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-800/40 hover:bg-emerald-700/60 cursor-pointer" title="Salin Email">
                                    Salin
                                </button>
                            </div>
                            <div class="font-mono font-bold text-xs text-emerald-300 select-all break-all mt-1">
                                {{ $cred['email'] }}
                            </div>
                        </div>

                        <!-- Password Default -->
                        <div class="p-4 rounded-2xl border border-blue-400/20 bg-slate-900/60 backdrop-blur-md flex flex-col justify-between hover:border-blue-400/40 transition group">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-300 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                                    Password Default
                                </span>
                                <button type="button" onclick="navigator.clipboard.writeText('{{ $cred['password'] }}');" class="text-amber-300 hover:text-white transition text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-800/40 hover:bg-amber-700/60 cursor-pointer" title="Salin Password">
                                    Salin
                                </button>
                            </div>
                            <div class="font-mono font-bold text-xs text-amber-300 select-all mt-1 flex items-center justify-between">
                                <span>{{ $cred['password'] }}</span>
                                <span class="text-[10px] text-amber-200/70 font-sans font-normal">(Default)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Keterangan Otoritas Kemitraan Kampus -->
                    <div class="relative z-10 flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-blue-950/40 border border-blue-400/20 text-blue-200/90 text-xs">
                        <svg class="w-4 h-4 text-blue-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>Akun ini berwenang mengelola data mahasiswa pendaftar dari perguruan tinggi mitra, menetapkan Dosen Pembimbing Lapangan (DPL), serta memantau dan memvalidasi penilaian magang MBKM.</span>
                    </div>
                </div>
            @endif

            <!-- Warning Banner for Unregistered University Accounts -->
            @if(($unregisteredCount ?? 0) > 0)
                <div class="p-5 bg-amber-50 border-l-4 border-amber-500 rounded-2xl shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-amber-100 rounded-xl text-amber-700 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-amber-900 text-sm">Terdapat {{ $unregisteredCount }} Perguruan Tinggi Baru yang Belum Memiliki Akun Admin Kampus</h4>
                            <p class="text-xs text-amber-700 mt-0.5">Kampus terdaftar otomatis saat mahasiswa melakukan registrasi. Buatkan akun agar perwakilan kampus dapat login ke Portal Universitas.</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Executive Macro Stats Kemitraan Perguruan Tinggi -->
            @if(isset($macroStats))
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-2xs">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Kampus Mitra</span>
                            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                            </div>
                        </div>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-2xl font-black text-slate-900">{{ $macroStats['total_universities'] }}</span>
                            <span class="text-xs text-slate-500 font-medium">Perguruan Tinggi</span>
                        </div>
                    </div>

                    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-2xs">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Mahasiswa</span>
                            <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            </div>
                        </div>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-2xl font-black text-indigo-600">{{ $macroStats['total_students'] }}</span>
                            <span class="text-xs text-slate-500 font-medium">Mahasiswa Terdaftar</span>
                        </div>
                    </div>

                    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-2xs">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Dosen DPL</span>
                            <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                            </div>
                        </div>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-2xl font-black text-teal-600">{{ $macroStats['total_dosens'] }}</span>
                            <span class="text-xs text-slate-500 font-medium">Dosen Pembimbing</span>
                        </div>
                    </div>

                    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-2xs">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Mahasiswa Aktif Magang</span>
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            </div>
                        </div>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-2xl font-black text-emerald-600">{{ $macroStats['total_active_interns'] }}</span>
                            <span class="text-xs text-slate-500 font-medium">Di Instansi Dinas</span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Search Card -->
            <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-2xs">
                <form method="GET" action="{{ route('admin.universities.index') }}" class="flex items-center gap-3">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none text-slate-400" style="padding-left: 1rem !important;">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama perguruan tinggi, kode kampus (e.g. UNESA, ITS), email..." 
                               class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                               style="padding-left: 2.75rem !important; padding-right: 1rem !important; padding-top: 0.65rem !important; padding-bottom: 0.65rem !important;">
                    </div>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition cursor-pointer">
                        Cari
                    </button>
                    @if(request('search'))
                        <a href="{{ route('admin.universities.index') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            <!-- Universities Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($universities as $univ)
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-xs hover:shadow-md transition p-6 flex flex-col justify-between group">
                        <div>
                            <div class="flex items-start justify-between gap-4">
                                @php
                                    $univLogoUrl = null;
                                    if (!empty($univ->logo)) {
                                        if (file_exists(public_path($univ->logo))) {
                                            $univLogoUrl = asset($univ->logo);
                                        } elseif (file_exists(public_path('storage/' . $univ->logo))) {
                                            $univLogoUrl = asset('storage/' . $univ->logo);
                                        } elseif (file_exists(storage_path('app/public/' . $univ->logo))) {
                                            $univLogoUrl = asset('storage/' . $univ->logo);
                                        }
                                    }
                                    // Fallback for well-known campuses if logo is null
                                    if (!$univLogoUrl) {
                                        $uName = strtolower($univ->name ?? '');
                                        $uCode = strtolower($univ->code ?? '');
                                        if (str_contains($uName, 'unesa') || str_contains($uCode, 'unesa')) {
                                            $univLogoUrl = asset('images/logos/unesa.png');
                                        } elseif (str_contains($uName, 'its') || str_contains($uCode, 'its') || str_contains($uName, 'sepuluh nopember')) {
                                            $univLogoUrl = asset('images/logos/its.png');
                                        } elseif (str_contains($uName, 'unair') || str_contains($uCode, 'unair') || str_contains($uName, 'airlangga')) {
                                            $univLogoUrl = asset('images/logos/unair.png');
                                        } elseif (str_contains($uName, 'upn') || str_contains($uCode, 'upn') || str_contains($uName, 'veteran')) {
                                            $univLogoUrl = asset('images/logos/upnjatim.png');
                                        } elseif (str_contains($uName, 'unitomo') || str_contains($uCode, 'unitomo') || str_contains($uName, 'soetomo')) {
                                            $univLogoUrl = asset('images/logos/unitomo.png');
                                        }
                                    }
                                @endphp
                                <a href="{{ route('admin.universities.show', $univ->id) }}" class="w-14 h-14 rounded-2xl bg-white border border-slate-100 shadow-2xs flex items-center justify-center p-2 shrink-0 overflow-hidden hover:border-blue-300 transition" style="width: 56px; height: 56px; min-width: 56px; min-height: 56px; max-width: 56px; max-height: 56px;" title="Kelola {{ $univ->name }}">
                                    @if($univLogoUrl)
                                        <img src="{{ $univLogoUrl }}" alt="{{ $univ->name }}" class="w-10 h-10 object-contain shrink-0" style="width: 40px; height: 40px; max-width: 40px; max-height: 40px; object-fit: contain;">
                                    @else
                                        <img src="{{ asset('images/default-university.svg') }}" alt="Default Logo {{ $univ->name }}" class="w-10 h-10 object-contain shrink-0" style="width: 40px; height: 40px; max-width: 40px; max-height: 40px; object-fit: contain;">
                                    @endif
                                </a>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="px-2.5 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded-full text-xs font-black">
                                        {{ $univ->code }}
                                    </span>
                                    @if($univ->universityAdmin)
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-[10px] font-bold">
                                            Akun Aktif
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-full text-[10px] font-bold animate-pulse">
                                            Belum Ada Akun
                                        </span>
                                    @endif

                                    @if($isSuperAdmin)
                                        <!-- Dropdown Menu (3-dots) for Clean Secondary Actions -->
                                        <div x-data="{ menuOpen: false }" class="relative" @click.outside="menuOpen = false">
                                            <button type="button" 
                                                    @click="menuOpen = !menuOpen" 
                                                    class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition"
                                                    title="Menu Opsi">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                                </svg>
                                            </button>

                                            <div x-show="menuOpen" 
                                                 x-transition:enter="transition ease-out duration-100"
                                                 x-transition:enter-start="transform opacity-0 scale-95"
                                                 x-transition:enter-end="transform opacity-100 scale-100"
                                                 x-transition:leave="transition ease-in duration-75"
                                                 x-transition:leave-start="transform opacity-100 scale-100"
                                                 x-transition:leave-end="transform opacity-0 scale-95"
                                                 class="absolute right-0 mt-1 w-44 bg-white rounded-xl shadow-lg border border-slate-100 py-1.5 z-20"
                                                 style="display: none;">
                                                
                                                <a href="{{ route('admin.universities.edit', $univ->id) }}" 
                                                   class="flex items-center gap-2 px-3.5 py-2 text-xs text-slate-700 hover:bg-slate-50 font-semibold transition">
                                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    <span>Edit Data</span>
                                                </a>

                                                <a href="{{ route('admin.applications.index', ['university_id' => $univ->id]) }}" 
                                                   class="flex items-center gap-2 px-3.5 py-2 text-xs text-slate-700 hover:bg-slate-50 font-semibold transition">
                                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                                                    <span>Daftar Mahasiswa</span>
                                                </a>

                                                <a href="{{ route('admin.users.index', ['university_id' => $univ->id, 'role' => 'dosen']) }}" 
                                                   class="flex items-center gap-2 px-3.5 py-2 text-xs text-slate-700 hover:bg-slate-50 font-semibold transition">
                                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                    <span>Daftar Dosen DPL</span>
                                                </a>

                                                <a href="{{ route('admin.users.index', ['university_id' => $univ->id]) }}" 
                                                   class="flex items-center gap-2 px-3.5 py-2 text-xs text-slate-700 hover:bg-slate-50 font-semibold transition">
                                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                                    <span>Daftar Akun</span>
                                                </a>

                                                <div class="my-1 border-t border-slate-100"></div>

                                                <button type="button" 
                                                        @click="menuOpen = false; $dispatch('open-delete-modal', {
                                                            action: '{{ route('admin.universities.destroy', $univ->id) }}',
                                                            title: 'Hapus Perguruan Tinggi',
                                                            name: '{{ addslashes($univ->name) }}',
                                                            desc: 'Kode: {{ $univ->code }} &bull; {{ $univ->students_count }} Mahasiswa'
                                                        })" 
                                                        class="w-full text-left flex items-center gap-2 px-3.5 py-2 text-xs text-rose-600 hover:bg-rose-50 font-semibold transition cursor-pointer">
                                                    <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    <span>Hapus Kampus</span>
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-4">
                                <h3 class="font-black text-base text-gray-900 leading-snug">
                                    <a href="{{ route('admin.universities.show', $univ->id) }}" class="hover:text-blue-600 transition" title="Lihat Pusat Kendali Kampus">
                                        {{ $univ->name }}
                                    </a>
                                </h3>
                                <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $univ->address ?? 'Alamat belum diatur' }}</p>
                                
                                <div class="mt-3 pt-3 border-t border-slate-50 space-y-1">
                                    @if($univ->pic_name)
                                        <div class="text-[11px] text-blue-700 font-semibold truncate">
                                            PIC: {{ $univ->pic_name }} ({{ $univ->pic_position ?? 'Pimpinan' }})
                                        </div>
                                    @else
                                        <div class="text-[11px] text-slate-400 italic">
                                            PIC belum diisi
                                        </div>
                                    @endif

                                    @if($univ->universityAdmin)
                                        <div class="text-[11px] text-slate-600 truncate">
                                            <strong class="font-mono text-slate-700">{{ $univ->universityAdmin->email }}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Stat Counts (Interaktif Langsung) -->
                            <div class="grid grid-cols-3 gap-2 mt-4 pt-4 border-t border-gray-100 text-center">
                                <a href="{{ route('admin.applications.index', ['university_id' => $univ->id]) }}" 
                                   class="p-2 rounded-xl bg-blue-50 hover:bg-blue-100/80 border border-blue-100 hover:border-blue-200 transition group/stat block"
                                   title="Klik untuk lihat daftar mahasiswa">
                                    <div class="text-xs font-black text-blue-700">{{ $univ->students_count }}</div>
                                    <div class="text-[10px] text-blue-600 font-semibold uppercase">Mahasiswa</div>
                                </a>
                                <a href="{{ route('admin.users.index', ['university_id' => $univ->id, 'role' => 'dosen']) }}" 
                                   class="p-2 rounded-xl bg-slate-50 hover:bg-slate-100/80 border border-slate-100 hover:border-slate-200 transition group/stat block"
                                   title="Klik untuk lihat daftar dosen pembimbing">
                                    <div class="text-xs font-black text-slate-700 group-hover/stat:text-blue-700">{{ $univ->dosens_count }}</div>
                                    <div class="text-[10px] text-slate-600 group-hover/stat:text-blue-600 font-semibold uppercase">Dosen DPL</div>
                                </a>
                                <a href="{{ route('admin.users.index', ['university_id' => $univ->id]) }}" 
                                   class="p-2 rounded-xl bg-slate-50 hover:bg-slate-100/80 border border-slate-100 hover:border-slate-200 transition group/stat block"
                                   title="Klik untuk lihat seluruh akun kampus">
                                    <div class="text-xs font-black text-slate-700 group-hover/stat:text-blue-700">{{ $univ->users_count }}</div>
                                    <div class="text-[10px] text-slate-500 group-hover/stat:text-blue-600 font-semibold uppercase">Akun</div>
                                </a>
                            </div>
                        </div>

                        <!-- Action Footer: 1 Baris Elegan, Hierarki Jelas Tanpa Tombol Menumpuk -->
                        <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between gap-3">
                            <div>
                                @if(!$univ->universityAdmin)
                                    <form method="POST" action="{{ route('admin.universities.create_account', $univ->id) }}" class="inline-block m-0">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-xl text-xs font-bold transition cursor-pointer active:scale-95" title="Buatkan Akun Admin Kampus">
                                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            <span>Buat Akun</span>
                                        </button>
                                    </form>
                                @elseif($isSuperAdmin)
                                    <form action="{{ route('admin.impersonate', $univ->universityAdmin->id) }}" method="POST" class="inline-block m-0">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-xl text-xs font-bold transition shadow-2xs cursor-pointer active:scale-95" title="Masuk Sebagai Admin Kampus (Login As)">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                                            <span>Login As</span>
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <a href="{{ route('admin.universities.show', $univ->id) }}" 
                               class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-sm hover:shadow-md cursor-pointer ml-auto"
                               title="Buka Pusat Kendali & Manajemen Kampus">
                                <span>Kelola Kampus</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center text-gray-400 bg-white rounded-2xl border border-gray-100">
                        <p class="font-bold text-gray-600">Belum ada universitas terdaftar</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
