<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                    <span>Master Instansi Dinas Pemerintah Kota Surabaya</span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Kelola data instansi dinas resmi tempat pelaksanaan program magang MBKM
                </p>
            </div>

            @if($isSuperAdmin)
                <a href="{{ route('admin.agencies.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Instansi Baru</span>
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Alert Success -->
            @if (session('success') && !session('new_agency_credential'))
                <div
                    class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div
                    class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-xs flex items-center justify-between text-rose-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- Credential Flash Alert -->
            @if (session('new_agency_credential'))
                @php $cred = session('new_agency_credential'); @endphp
                <div class="relative overflow-hidden rounded-3xl shadow-2xl border border-blue-400/30 p-6 sm:p-7 space-y-5"
                    style="background: linear-gradient(135deg, #09172e 0%, #0d2857 45%, #07152c 100%) !important; color: #ffffff !important;">

                    <div class="absolute -right-8 -bottom-10 pointer-events-none opacity-[0.06] text-white">
                        <svg class="w-72 h-72" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 2.18l7 3.12v4.7c0 4.67-3.13 9.04-7 10.19-3.87-1.15-7-5.52-7-10.19V6.3l7-3.12z" />
                        </svg>
                    </div>

                    <div
                        class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-blue-400/20 pb-5">
                        <div class="space-y-1">
                            <h3 class="font-black text-lg sm:text-xl text-white tracking-tight flex items-center gap-2">
                                <span>Akun Admin Dinas Baru Berhasil Dibuat!</span>
                            </h3>
                            <p class="text-xs text-blue-100/80 leading-relaxed">
                                Kredensial login resmi untuk instansi <strong
                                    class="text-amber-300 font-bold underline decoration-amber-400/40 underline-offset-2">{{ $cred['agency_name'] }}</strong>
                                siap digunakan oleh PIC/Admin dinas terkait.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2.5 shrink-0 pt-1 lg:pt-0">
                            <button type="button"
                                onclick="navigator.clipboard.writeText('Instansi: {{ addslashes($cred['agency_name']) }}\nPortal: {{ $cred['login_url'] }}\nEmail: {{ $cred['email'] }}\nPassword: {{ $cred['password'] }}'); this.innerHTML = '<svg class=\'w-4 h-4 text-emerald-300\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M5 13l4 4L19 7\'/></svg><span>Tersalin!</span>'; setTimeout(() => this.innerHTML = '<svg class=\'w-4 h-4 text-blue-200\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3\'/></svg><span>Salin Semua</span>', 2500);"
                                class="inline-flex items-center gap-2 px-3.5 py-2 bg-blue-950/70 hover:bg-blue-900/80 text-blue-100 border border-blue-400/30 rounded-xl text-xs font-bold transition shadow-sm hover:border-blue-300 cursor-pointer active:scale-95">
                                <svg class="w-4 h-4 text-blue-200 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                </svg>
                                <span>Salin Semua</span>
                            </button>

                            @if($isSuperAdmin && !empty($cred['user_id']))
                                <form action="{{ route('admin.impersonate', $cred['user_id']) }}" method="POST"
                                    class="inline-block m-0">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-600 hover:from-emerald-600 hover:to-teal-700 text-white rounded-xl text-xs font-black transition-all shadow-lg border border-emerald-300/40 active:scale-95 cursor-pointer">
                                        <svg class="w-4 h-4 text-emerald-100 shrink-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                        </svg>
                                        <span>Login As Admin Dinas</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <div class="relative z-10 grid grid-cols-1 md:grid-cols-3 gap-3.5">
                        <div
                            class="p-3.5 rounded-2xl border border-blue-400/20 bg-slate-900/60 backdrop-blur-md flex flex-col justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-sky-300">URL Portal
                                Login</span>
                            <div class="font-mono font-bold text-xs text-sky-300 select-all truncate mt-1">
                                {{ $cred['login_url'] }}</div>
                        </div>
                        <div
                            class="p-3.5 rounded-2xl border border-blue-400/20 bg-slate-900/60 backdrop-blur-md flex flex-col justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-300">Email Akun
                                Dinas</span>
                            <div class="font-mono font-bold text-xs text-emerald-300 select-all truncate mt-1">
                                {{ $cred['email'] }}</div>
                        </div>
                        <div
                            class="p-3.5 rounded-2xl border border-blue-400/20 bg-slate-900/60 backdrop-blur-md flex flex-col justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-300">Password
                                Default</span>
                            <div class="font-mono font-bold text-xs text-amber-300 select-all truncate mt-1">
                                {{ $cred['password'] }}</div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Warning Banner -->
            @if(($unregisteredCount ?? 0) > 0)
                <div
                    class="p-4 bg-amber-50 border-l-4 border-amber-500 rounded-2xl shadow-xs flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-amber-100 rounded-xl text-amber-700 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-amber-900 text-xs sm:text-sm">Terdapat {{ $unregisteredCount }}
                                Instansi yang Belum Memiliki Akun Admin</h4>
                            <p class="text-[11px] text-amber-700 mt-0.5">Buatkan akun agar perwakilan dinas dapat
                                memverifikasi mahasiswa dan memonitor kuota.</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Executive Macro Stats se-Kota Surabaya -->
            @if(isset($macroStats))
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs hover:shadow-xs transition">
                        <div class="text-xs font-semibold text-slate-500">Total Instansi</div>
                        <div class="mt-2 text-3xl font-black text-slate-900 tracking-tight">
                            {{ $macroStats['total_agencies'] }}</div>
                        <div class="mt-1 text-xs text-slate-400">Instansi dinas terdaftar</div>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs hover:shadow-xs transition">
                        <div class="text-xs font-semibold text-slate-500">Unit Kerja</div>
                        <div class="mt-2 text-3xl font-black text-slate-900 tracking-tight">{{ $macroStats['total_units'] }}
                        </div>
                        <div class="mt-1 text-xs text-slate-400">Bidang kerja aktif</div>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs hover:shadow-xs transition">
                        <div class="text-xs font-semibold text-slate-500">Kuota Magang</div>
                        <div class="mt-2 text-3xl font-black text-slate-900 tracking-tight">{{ $macroStats['total_quota'] }}
                        </div>
                        <div class="mt-1 text-xs text-slate-400">{{ $macroStats['total_filled'] }} kuota telah terisi</div>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs hover:shadow-xs transition">
                        <div class="text-xs font-semibold text-slate-500">Total Personel</div>
                        <div class="mt-2 text-3xl font-black text-slate-900 tracking-tight">{{ $macroStats['total_staff'] }}
                        </div>
                        <div class="mt-1 text-xs text-slate-400">Admin dinas & mentor</div>
                    </div>
                </div>
            @endif

            <!-- Search Bar -->
            <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-2xs">
                <form method="GET" action="{{ route('admin.agencies.index') }}" class="flex items-center gap-3">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none text-slate-400"
                            style="padding-left: 1rem !important;">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama instansi dinas, email, atau kota..."
                            class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                            style="padding-left: 2.75rem !important; padding-right: 1rem !important; padding-top: 0.65rem !important; padding-bottom: 0.65rem !important;">
                    </div>
                    <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition cursor-pointer">
                        Cari
                    </button>
                    @if(request('search'))
                        <a href="{{ route('admin.agencies.index') }}"
                            class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            <!-- List Instansi Grid (Struktur Bersih, Konsisten & Tanpa Redundan) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @forelse($agencies as $agency)
                    @php
                        $agencyLogoUrl = null;
                        if (!empty($agency->logo)) {
                            if (file_exists(public_path($agency->logo))) {
                                $agencyLogoUrl = asset($agency->logo);
                            } elseif (file_exists(public_path('storage/' . $agency->logo)) || file_exists(storage_path('app/public/' . $agency->logo))) {
                                $agencyLogoUrl = asset('storage/' . $agency->logo);
                            }
                        }
                        if (!$agencyLogoUrl) {
                            $agencyLogoUrl = asset('images/default-agency.svg');
                        }
                        $firstAdmin = $agency->users->firstWhere('role', 'admin');
                    @endphp

                    <div
                        class="bg-white rounded-3xl border border-slate-200/90 shadow-xs hover:shadow-md transition p-6 flex flex-col justify-between">
                        <div>
                            <!-- Header: Logo, Status Badge, & Menu Opsi Kanan Atas -->
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <div class="w-13 h-13 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center p-2.5 shrink-0 overflow-hidden shadow-2xs"
                                    style="width: 52px; height: 52px;">
                                    <img src="{{ $agencyLogoUrl }}" alt="{{ $agency->agency_name }}"
                                        class="w-9 h-9 object-contain shrink-0">
                                </div>

                                <div class="flex items-center gap-1.5 shrink-0">
                                    @if(($agency->total_admins ?? 0) > 0)
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Akun Aktif
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            Belum Ada Akun
                                        </span>
                                    @endif

                                   
                                </div>
                            </div>

                            <!-- Identitas Instansi Dinas -->
                            <h3 class="font-bold text-slate-900 text-[18px] leading-snug line-clamp-2 h-14 mb-1"
                                title="{{ $agency->agency_name }}">
                                {{ $agency->agency_name }}
                            </h3>


                            <p class="text-[11px] text-slate-500 line-clamp-2 h-8 leading-relaxed mb-2"
                                title="{{ $agency->address }}">
                                {{ $agency->address ?? 'Alamat kantor belum diatur' }}
                            </p>

                            <div class="text-[11px] font-mono text-slate-400 truncate mb-5">
                                Admin:
                                @if($firstAdmin)
                                    <span class="text-slate-700 font-medium">{{ $firstAdmin->email }}</span>
                                @else
                                    <span class="text-slate-400 italic">Belum dibuatkan akun</span>
                                @endif
                            </div>

                            <!-- Metrik Ringkas & Seragam (3 Kolom Berlatar Lembut) -->
                            <div
                                class="grid grid-cols-3 gap-2 p-3 bg-slate-50/80 rounded-2xl border border-slate-100 text-center mb-5 select-none">
                                <div>
                                    <div class="text-sm font-black text-slate-900">
                                        {{ $agency->units_count ?? count($agency->units ?? []) }}</div>
                                    <div class="text-[10px] font-medium text-slate-500 mt-0.5">Unit Kerja</div>
                                </div>
                                <div class="border-x border-slate-200/60">
                                    <div class="text-sm font-black text-blue-600">{{ $agency->remaining_quota }}</div>
                                    <div class="text-[10px] font-medium text-blue-600/90 mt-0.5">Sisa Kuota</div>
                                </div>
                                <div>
                                    <div class="text-sm font-black text-slate-900">
                                        {{ ($agency->total_admins ?? 0) + ($agency->total_mentors ?? 0) }}</div>
                                    <div class="text-[10px] font-medium text-slate-500 mt-0.5">Personel</div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Footer Sejajar (Format Baku: Tombol Sekunder & Tombol Primer) -->
                        <div class="flex items-center gap-2 pt-2">
                            @if(($agency->total_admins ?? 0) === 0)
                                <form method="POST" action="{{ route('admin.agencies.create_account', $agency->id) }}"
                                    class="w-1/3 m-0">
                                    @csrf
                                    <button type="submit"
                                        class="w-full py-2.5 px-3 rounded-xl border border-slate-900 text-slate-900 bg-white hover:bg-slate-900 hover:text-white font-bold text-xs transition cursor-pointer active:scale-95">
                                        Buat Akun
                                    </button>
                                </form>
                            @elseif($isSuperAdmin && $firstAdmin)
                                <form action="{{ route('admin.impersonate', $firstAdmin->id) }}" method="POST"
                                    class="w-1/3 m-0">
                                    @csrf
                                    <button type="submit"
                                        class="w-full py-2.5 px-3 rounded-xl border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 font-bold text-xs transition cursor-pointer active:scale-95"
                                        title="Masuk Sebagai Admin Dinas">
                                        Login As
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('admin.agencies.show', $agency->id) }}"
                                class="flex-1 py-2.5 px-3 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold text-xs text-center shadow-xs transition cursor-pointer">
                                Kelola Dinas
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center text-gray-400 bg-white rounded-2xl border border-gray-100">
                        <p class="font-bold text-gray-600">Belum ada data instansi dinas</p>
                        <p class="text-xs mt-1">Gunakan tombol "Tambah Instansi Baru" untuk menambahkan dinas.</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>