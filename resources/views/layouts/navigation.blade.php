<nav x-data="{ mobileMenuOpen: false }" 
     class="border-b border-slate-100 transition-all"
     style="position: sticky !important; top: 0 !important; z-index: 1000 !important; background-color: #ffffff !important; opacity: 1 !important; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05) !important;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20 items-center">
            
            {{-- 1. BRAND LOGO RESMI PEMKOT SURABAYA --}}
            <div class="flex items-center gap-4 lg:gap-8 shrink-0">
                @php
                    $user = Auth::user();
                    $isSuperAdmin = $user && ($user->role === 'super_admin' || ($user->role === 'admin' && is_null($user->agency_profile_id)));
                    $isAdminDinas = $user && ($user->role === 'admin' && !is_null($user->agency_profile_id));
                    $isMahasiswa = $user && $user->role === 'mahasiswa';
                    $isDosen = $user && ($user->role === 'dosen' || $user->role === 'academic_advisor');
                    $isMentor = $user && ($user->role === 'mentor' || $user->role === 'pembimbing');
                    $isUniversitas = $user && $user->role === 'universitas';

                    $dashboardRoute = route('dashboard');
                    if ($isSuperAdmin || $isAdminDinas) {
                        $dashboardRoute = route('admin.dashboard');
                    } elseif ($isMentor) {
                        $dashboardRoute = route('mentor.dashboard');
                    } elseif ($isDosen) {
                        $dashboardRoute = route('lecturer.dashboard');
                    } elseif ($isUniversitas) {
                        $dashboardRoute = route('university.dashboard');
                    }
                @endphp

                <a href="{{ $dashboardRoute }}" class="flex items-center group py-2">
                    <img src="{{ asset('images/logos/surabaya.png') }}" 
                         alt="Pemerintah Kota Surabaya" 
                         class="h-10 sm:h-11 w-auto object-contain shrink-0 transition-transform group-hover:scale-105"
                         style="height: 42px; width: auto; max-height: 46px; object-fit: contain;">
                </a>

                {{-- 2. DESKTOP NAVIGATION BAR (768px+ DIPASTIKAN SELALU MUNCUL HORIZONTAL) --}}
                <div class="hidden md:flex items-center space-x-3 lg:space-x-5 xl:space-x-7 text-xs lg:text-sm font-medium">

                    {{-- 2.1 SUPER ADMIN --}}
                    @if ($isSuperAdmin)
                        <a href="{{ route('admin.dashboard') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.dashboard') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Dashboard
                        </a>

                        <a href="{{ route('admin.applications.index') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.applications.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Pengajuan
                        </a>

                        <a href="{{ route('admin.agencies.index') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.agencies.*') || request()->routeIs('admin.units.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Instansi & Unit
                        </a>

                        <a href="{{ route('admin.universities.index') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.universities.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Universitas
                        </a>

                        {{-- Dropdown Pengguna --}}
                        <div x-data="{ open: false }" class="relative" @click.away="open = false">
                            <button @click="open = !open" 
                                    type="button" 
                                    class="flex items-center gap-1 py-1 transition focus:outline-none cursor-pointer {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.mentors.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                                <span>Pengguna</span>
                                <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{'rotate-180 text-blue-600': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                 x-cloak
                                 class="absolute left-0 top-full mt-3 w-64 rounded-2xl p-2 space-y-1 border border-slate-100"
                                 style="background-color: #ffffff !important; opacity: 1 !important; z-index: 99999 !important; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15) !important; display: none;">
                                
                                <a href="{{ route('admin.users.index') }}" 
                                   class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-slate-50 transition group {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-600 font-bold' : '' }}">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold shrink-0">👥</div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-800 group-hover:text-blue-600">Semua Pengguna</div>
                                        <div class="text-[11px] text-slate-400">Akun, role & impersonasi</div>
                                    </div>
                                </a>

                                <a href="{{ route('admin.mentors.index') }}" 
                                   class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-slate-50 transition group {{ request()->routeIs('admin.mentors.*') ? 'bg-blue-50 text-blue-600 font-bold' : '' }}">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold shrink-0">👔</div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-800 group-hover:text-indigo-600">Mentor Lapangan</div>
                                        <div class="text-[11px] text-slate-400">Pembimbing teknis dinas</div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <a href="{{ route('admin.audit_logs.index') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.audit_logs.*') || request()->routeIs('admin.audit-logs.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Log Audit
                        </a>

                    {{-- 2.2 ADMIN DINAS --}}
                    @elseif ($isAdminDinas)
                        <a href="{{ route('admin.dashboard') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.dashboard') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('admin.applications.index') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.applications.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Verifikasi
                        </a>
                        <a href="{{ route('admin.units.index') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.units.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Divisi & Kuota
                        </a>
                        <a href="{{ route('admin.mentors.index') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.mentors.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Mentor Dinas
                        </a>
                        <a href="{{ route('admin.logbooks.index') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.logbooks.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Logbook
                        </a>
                        <a href="{{ route('admin.certificates.index') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.certificates.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Sertifikat
                        </a>
                        <a href="{{ route('admin.agency_profile.edit') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.agency_profile.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Profil Dinas
                        </a>

                    {{-- 2.3 MAHASISWA --}}
                    @elseif ($isMahasiswa)
                        <a href="{{ route('dashboard') }}" 
                           class="transition py-1 {{ request()->routeIs('dashboard') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('student.profile.edit') }}" 
                           class="transition py-1 {{ request()->routeIs('student.profile.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Profil Saya
                        </a>
                        <a href="{{ route('student.application.create') }}" 
                           class="transition py-1 {{ request()->routeIs('student.application.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Pengajuan Magang
                        </a>
                        <a href="{{ route('student.logbook.index') }}" 
                           class="transition py-1 {{ request()->routeIs('student.logbook.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Logbook Magang
                        </a>

                    {{-- 2.4 DOSEN (DPL) --}}
                    @elseif ($isDosen)
                        <a href="{{ route('lecturer.dashboard') }}" 
                           class="transition py-1 {{ request()->routeIs('lecturer.dashboard') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Portal Dosen
                        </a>
                        <a href="{{ route('lecturer.monitoring.index') }}" 
                           class="transition py-1 {{ request()->routeIs('lecturer.monitoring.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Mahasiswa Bimbingan
                        </a>
                        <a href="{{ route('lecturer.logbooks.index') }}" 
                           class="transition py-1 {{ request()->routeIs('lecturer.logbooks.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Logbook Bimbingan
                        </a>

                    {{-- 2.5 MENTOR --}}
                    @elseif ($isMentor)
                        <a href="{{ route('mentor.dashboard') }}" 
                           class="transition py-1 {{ request()->routeIs('mentor.dashboard') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Portal Mentor
                        </a>
                        <a href="{{ route('mentor.logbooks.index') }}" 
                           class="transition py-1 {{ request()->routeIs('mentor.logbooks.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Review Logbook
                        </a>

                    {{-- 2.6 UNIVERSITAS --}}
                    @elseif ($isUniversitas)
                        <a href="{{ route('university.dashboard') }}" 
                           class="transition py-1 {{ request()->routeIs('university.dashboard') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Portal Universitas
                        </a>
                        <a href="{{ route('university.lecturers.index') }}" 
                           class="transition py-1 {{ request()->routeIs('university.lecturers.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Dosen Pembimbing
                        </a>
                        <a href="{{ route('university.profile.index') }}" 
                           class="transition py-1 {{ request()->routeIs('university.profile.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Profil Kampus
                        </a>
                    @endif

                </div>
            </div>

            {{-- 3. RIGHT SECTION: USER BADGE & PROFILE DROPDOWN (DESKTOP) --}}
            <div class="hidden md:flex items-center gap-3">
                <div x-data="{ profileOpen: false }" class="relative" @click.away="profileOpen = false">
                    <button @click="profileOpen = !profileOpen" 
                            class="flex items-center gap-2.5 px-3 py-1.5 rounded-full border border-slate-200 hover:border-slate-300 bg-white transition cursor-pointer shadow-2xs">
                        <span class="text-xs font-semibold text-slate-700 max-w-[140px] truncate">{{ Auth::user()->name ?? 'Pengguna' }}</span>
                        <div class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold shrink-0">
                            {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                        </div>
                        <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{'rotate-180 text-blue-600': profileOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="profileOpen" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                         x-cloak
                         class="absolute right-0 top-full mt-2 w-72 rounded-3xl p-3 space-y-1 border border-slate-100"
                         style="background-color: #ffffff !important; opacity: 1 !important; z-index: 99999 !important; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important; display: none;">
                        
                        {{-- User Header Info --}}
                        <div class="px-3 py-2.5 bg-slate-50 rounded-2xl mb-2 flex items-center justify-between" style="background-color: #f8fafc !important;">
                            <div class="flex items-center gap-2.5 overflow-hidden">
                                <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xs font-bold shrink-0">
                                    {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                                </div>
                                <div class="overflow-hidden">
                                    <div class="text-xs font-bold text-slate-900 truncate">{{ Auth::user()->name ?? 'User' }}</div>
                                    <div class="text-[10px] font-semibold text-blue-600 uppercase tracking-wide">{{ strtoupper(str_replace('_', ' ', Auth::user()->role ?? 'Role')) }}</div>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">
                                Online
                            </span>
                        </div>

                        {{-- Role Quick Links --}}
                        @if ($isSuperAdmin)
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                <span class="text-sm">📊</span> Dashboard
                            </a>
                            <a href="{{ route('admin.applications.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                <span class="text-sm">📋</span> Verifikasi Pengajuan
                            </a>
                            <a href="{{ route('admin.agencies.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                <span class="text-sm">🏢</span> Instansi & Unit
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                <span class="text-sm">👥</span> Kelola Pengguna
                            </a>
                        @elseif ($isAdminDinas)
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                <span class="text-sm">📊</span> Dashboard
                            </a>
                            <a href="{{ route('admin.applications.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                <span class="text-sm">📋</span> Verifikasi Pengajuan
                            </a>
                            <a href="{{ route('admin.units.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                <span class="text-sm">🏢</span> Divisi & Kuota Unit
                            </a>
                            <a href="{{ route('admin.mentors.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                <span class="text-sm">👔</span> Mentor Lapangan
                            </a>
                        @elseif ($isMahasiswa)
                            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                <span class="text-sm">📊</span> Dashboard Saya
                            </a>
                            <a href="{{ route('student.profile.edit') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                <span class="text-sm">👤</span> Profil Saya
                            </a>
                            <a href="{{ route('student.logbook.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                <span class="text-sm">📖</span> Logbook Magang
                            </a>
                        @elseif ($isDosen)
                            <a href="{{ route('lecturer.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                <span class="text-sm">👨‍🏫</span> Portal Dosen
                            </a>
                            <a href="{{ route('lecturer.monitoring.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                <span class="text-sm">🎓</span> Mahasiswa Bimbingan
                            </a>
                        @elseif ($isMentor)
                            <a href="{{ route('mentor.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                <span class="text-sm">👔</span> Portal Mentor
                            </a>
                            <a href="{{ route('mentor.logbooks.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                <span class="text-sm">📖</span> Review Logbook
                            </a>
                        @elseif ($isUniversitas)
                            <a href="{{ route('university.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                <span class="text-sm">🏛️</span> Portal Universitas
                            </a>
                            <a href="{{ route('university.profile.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                <span class="text-sm">🏢</span> Profil Kampus
                            </a>
                        @endif

                        <div class="pt-2 border-t border-slate-100">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2.5 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-2xl transition cursor-pointer">
                                    <span>🚪</span> Keluar Sistem
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. MOBILE / TABLET MENU TOGGLE BUTTON (HANYA MUNCUL DI LAYAR KECIL < 768px) --}}
            <div class="flex items-center md:hidden">
                <button @click="mobileMenuOpen = !mobileMenuOpen" 
                        type="button"
                        class="p-2 rounded-xl text-slate-600 hover:bg-slate-100 focus:outline-none transition cursor-pointer"
                        aria-label="Buka Menu">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': mobileMenuOpen, 'inline-flex': !mobileMenuOpen }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !mobileMenuOpen, 'inline-flex': mobileMenuOpen }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

        </div>
    </div>

    {{-- 5. FLOATING MOBILE MENU OVERLAY (100% PUTIH SOLID OPAQUE - TIDAK TEMBUS PANDANG) --}}
    <div x-show="mobileMenuOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2 scale-98"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 -translate-y-2 scale-98"
         x-cloak
         @click.away="mobileMenuOpen = false"
         class="md:hidden absolute top-full left-0 right-0 w-full px-4 py-5 space-y-3 border-b border-slate-200"
         style="background-color: #ffffff !important; opacity: 1 !important; z-index: 99999 !important; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important; display: none;">

        {{-- Info Profil Pengguna di Mobile --}}
        <div class="p-3.5 rounded-2xl border border-slate-100 flex items-center justify-between" style="background-color: #f8fafc !important;">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xs font-bold shrink-0">
                    {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                </div>
                <div>
                    <div class="text-xs font-bold text-slate-800">{{ Auth::user()->name ?? 'Pengguna' }}</div>
                    <div class="text-[10px] font-semibold text-slate-400 uppercase">{{ strtoupper(str_replace('_', ' ', Auth::user()->role ?? 'Role')) }}</div>
                </div>
            </div>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">Online</span>
        </div>

        {{-- Daftar Navigasi Mobile per Role --}}
        <div class="space-y-1">

            {{-- 5.1 Menu Mobile Super Admin --}}
            @if ($isSuperAdmin)
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span>📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.applications.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.applications.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span>📋</span>
                    <span>Pengajuan</span>
                </a>
                <a href="{{ route('admin.agencies.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.agencies.*') || request()->routeIs('admin.units.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span>🏢</span>
                    <span>Instansi & Unit</span>
                </a>
                <a href="{{ route('admin.universities.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.universities.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span>🎓</span>
                    <span>Universitas</span>
                </a>
                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span>👥</span>
                    <span>Semua Pengguna</span>
                </a>
                <a href="{{ route('admin.mentors.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.mentors.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span>👔</span>
                    <span>Mentor Lapangan</span>
                </a>
                <a href="{{ route('admin.audit_logs.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.audit_logs.*') || request()->routeIs('admin.audit-logs.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span>📜</span>
                    <span>Log Audit</span>
                </a>

            {{-- 5.2 Menu Mobile Admin Dinas --}}
            @elseif ($isAdminDinas)
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span>📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.applications.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.applications.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span>📋</span>
                    <span>Verifikasi Pengajuan</span>
                </a>
                <a href="{{ route('admin.units.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.units.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span>🏢</span>
                    <span>Divisi & Kuota Unit</span>
                </a>
                <a href="{{ route('admin.mentors.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.mentors.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span>👔</span>
                    <span>Mentor Lapangan</span>
                </a>
                <a href="{{ route('admin.logbooks.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.logbooks.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span>📖</span>
                    <span>Monitoring Logbook</span>
                </a>
                <a href="{{ route('admin.certificates.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.certificates.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span>🏆</span>
                    <span>Terbitkan Sertifikat</span>
                </a>
                <a href="{{ route('admin.agency_profile.edit') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.agency_profile.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span>⚙️</span>
                    <span>Profil Dinas</span>
                </a>

            {{-- 5.3 Menu Mobile Mahasiswa --}}
            @elseif ($isMahasiswa)
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span>📊</span>
                    <span>Dashboard Saya</span>
                </a>
                <a href="{{ route('student.profile.edit') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('student.profile.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span>👤</span>
                    <span>Profil Saya</span>
                </a>
                <a href="{{ route('student.application.create') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('student.application.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span>📝</span>
                    <span>Pengajuan Magang</span>
                </a>
                <a href="{{ route('student.logbook.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('student.logbook.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span>📖</span>
                    <span>Logbook Magang</span>
                </a>

            {{-- 5.4 Menu Mobile Dosen (DPL) --}}
            @elseif ($isDosen)
                <a href="{{ route('lecturer.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('lecturer.dashboard') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span>👨‍🏫</span>
                    <span>Portal Dosen</span>
                </a>
                <a href="{{ route('lecturer.monitoring.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('lecturer.monitoring.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span>🎓</span>
                    <span>Mahasiswa Bimbingan</span>
                </a>
                <a href="{{ route('lecturer.logbooks.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('lecturer.logbooks.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span>📖</span>
                    <span>Logbook Bimbingan</span>
                </a>

            {{-- 5.5 Menu Mobile Mentor Lapangan --}}
            @elseif ($isMentor)
                <a href="{{ route('mentor.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('mentor.dashboard') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span>👔</span>
                    <span>Portal Mentor</span>
                </a>
                <a href="{{ route('mentor.logbooks.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('mentor.logbooks.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span>📖</span>
                    <span>Review Logbook</span>
                </a>

            {{-- 5.6 Menu Mobile Universitas --}}
            @elseif ($isUniversitas)
                <a href="{{ route('university.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('university.dashboard') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span>🏛️</span>
                    <span>Portal Universitas</span>
                </a>
                <a href="{{ route('university.lecturers.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('university.lecturers.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span>👨‍🏫</span>
                    <span>Dosen Pembimbing</span>
                </a>
                <a href="{{ route('university.profile.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('university.profile.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <span>🏢</span>
                    <span>Profil Kampus</span>
                </a>
            @endif

        </div>

        {{-- Tombol Logout Mobile --}}
        <div class="pt-3 border-t border-slate-100">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 transition cursor-pointer">
                    <span>🚪</span>
                    <span>Keluar Sistem</span>
                </button>
            </form>
        </div>
    </div>
</nav>