<nav x-data="{ mobileMenuOpen: false }" class="bg-white/95 backdrop-blur-md border-b border-slate-100 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20 items-center">
            
            {{-- Brand Logo --}}
            <div class="flex items-center gap-8 lg:gap-10">
                @php
                    $user = Auth::user();
                    $isSuperAdmin = $user && ($user->role === 'super_admin' || ($user->role === 'admin' && is_null($user->agency_profile_id)));
                    $isAdminDinas = $user && ($user->role === 'admin' && !is_null($user->agency_profile_id));
                    $dashboardRoute = route('dashboard');
                    if ($isSuperAdmin || $isAdminDinas) {
                        $dashboardRoute = route('admin.dashboard');
                    } elseif ($user?->role === 'mentor' || $user?->role === 'pembimbing') {
                        $dashboardRoute = route('mentor.dashboard');
                    } elseif ($user?->role === 'dosen' || $user?->role === 'academic_advisor') {
                        $dashboardRoute = route('lecturer.dashboard');
                    } elseif ($user?->role === 'universitas') {
                        $dashboardRoute = route('university.dashboard');
                    }
                @endphp

                <a href="{{ $dashboardRoute }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/logos/surabaya.png') }}" 
                         alt="Pemkot Surabaya" 
                         class="h-9 w-auto object-contain shrink-0"
                         onerror="this.src='{{ asset('logo.png') }}'">
                    <div class="flex flex-col">
                        <span class="text-sm font-black tracking-tight text-slate-900 leading-none">SIP-MAGANG</span>
                        <span class="text-[10px] font-bold text-slate-400 tracking-wider uppercase mt-1">PEMKOT SURABAYA</span>
                    </div>
                </a>

                {{-- Desktop Navigation (Gojek-Style Clean Typography & Spacing) --}}
                <div class="hidden md:flex items-center space-x-6 lg:space-x-8 text-sm font-medium">

                    {{-- 1. SUPER ADMIN NAVIGATION --}}
                    @if ($isSuperAdmin)
                        <a href="{{ route('admin.dashboard') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.dashboard') ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600' }}">
                            Dashboard
                        </a>

                        <a href="{{ route('admin.applications.index') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.applications.*') ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600' }}">
                            Pengajuan
                        </a>

                        <a href="{{ route('admin.agencies.index') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.agencies.*') || request()->routeIs('admin.units.*') ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600' }}">
                            Instansi & Unit
                        </a>

                        <a href="{{ route('admin.universities.index') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.universities.*') ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600' }}">
                            Universitas
                        </a>

                        {{-- Dropdown Pengguna --}}
                        <div x-data="{ open: false }" class="relative" @click.away="open = false">
                            <button @click="open = !open" 
                                    type="button" 
                                    class="flex items-center gap-1.5 py-1 transition focus:outline-none cursor-pointer {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.mentors.*') ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600' }}">
                                <span>Pengguna</span>
                                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{'rotate-180 text-blue-600': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                 class="absolute left-0 top-full mt-3 w-64 bg-white rounded-2xl shadow-xl border border-slate-100 p-2 z-50 space-y-1"
                                 style="display: none;">
                                
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
                           class="transition py-1 {{ request()->routeIs('admin.audit_logs.*') || request()->routeIs('admin.audit-logs.*') ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600' }}">
                            Log Audit
                        </a>

                    {{-- 2. ADMIN DINAS NAVIGATION --}}
                    @elseif ($isAdminDinas)
                        <a href="{{ route('admin.dashboard') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.dashboard') ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('admin.applications.index') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.applications.*') ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600' }}">
                            Verifikasi
                        </a>
                        <a href="{{ route('admin.units.index') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.units.*') ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600' }}">
                            Divisi & Kuota
                        </a>
                        <a href="{{ route('admin.mentors.index') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.mentors.*') ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600' }}">
                            Mentor Dinas
                        </a>
                        <a href="{{ route('admin.logbooks.index') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.logbooks.*') ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600' }}">
                            Logbook
                        </a>
                        <a href="{{ route('admin.certificates.index') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.certificates.*') ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600' }}">
                            Sertifikat
                        </a>
                        <a href="{{ route('admin.agency_profile.edit') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.agency_profile.*') ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600' }}">
                            Profil Dinas
                        </a>

                    {{-- 3. MAHASISWA NAVIGATION --}}
                    @elseif ($user?->role === 'mahasiswa')
                        <a href="{{ route('dashboard') }}" 
                           class="transition py-1 {{ request()->routeIs('dashboard') ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('student.profile.edit') }}" 
                           class="transition py-1 {{ request()->routeIs('student.profile.*') ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600' }}">
                            Profil Saya
                        </a>
                        <a href="{{ route('student.application.create') }}" 
                           class="transition py-1 {{ request()->routeIs('student.application.*') ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600' }}">
                            Pengajuan Magang
                        </a>
                        <a href="{{ route('student.logbook.index') }}" 
                           class="transition py-1 {{ request()->routeIs('student.logbook.*') ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600' }}">
                            Logbook Magang
                        </a>

                    {{-- 4. MENTOR NAVIGATION --}}
                    @elseif ($user?->role === 'mentor' || $user?->role === 'pembimbing')
                        <a href="{{ route('mentor.dashboard') }}" 
                           class="transition py-1 {{ request()->routeIs('mentor.dashboard') ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600' }}">
                            Portal Mentor
                        </a>
                        <a href="{{ route('mentor.logbooks.index') }}" 
                           class="transition py-1 {{ request()->routeIs('mentor.logbooks.*') ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600' }}">
                            Review Logbook
                        </a>

                    {{-- 5. DOSEN (DPL) NAVIGATION --}}
                    @elseif ($user?->role === 'dosen' || $user?->role === 'academic_advisor')
                        <a href="{{ route('lecturer.dashboard') }}" 
                           class="transition py-1 {{ request()->routeIs('lecturer.dashboard') ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600' }}">
                            Portal Dosen
                        </a>
                        <a href="{{ route('lecturer.monitoring.index') }}" 
                           class="transition py-1 {{ request()->routeIs('lecturer.monitoring.*') ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600' }}">
                            Mahasiswa Bimbingan
                        </a>
                        <a href="{{ route('lecturer.logbooks.index') }}" 
                           class="transition py-1 {{ request()->routeIs('lecturer.logbooks.*') ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600' }}">
                            Logbook Bimbingan
                        </a>

                    {{-- 6. UNIVERSITAS NAVIGATION --}}
                    @elseif ($user?->role === 'universitas')
                        <a href="{{ route('university.dashboard') }}" 
                           class="transition py-1 {{ request()->routeIs('university.dashboard') ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600' }}">
                            Portal Universitas
                        </a>
                        <a href="{{ route('university.lecturers.index') }}" 
                           class="transition py-1 {{ request()->routeIs('university.lecturers.*') ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600' }}">
                            Dosen Pembimbing
                        </a>
                        <a href="{{ route('university.profile.index') }}" 
                           class="transition py-1 {{ request()->routeIs('university.profile.*') ? 'text-blue-600 font-bold' : 'text-slate-600 hover:text-blue-600' }}">
                            Profil Kampus
                        </a>
                    @endif

                </div>
            </div>

            {{-- Right Section: Profile & Logout --}}
            <div class="hidden md:flex items-center gap-3">
                <div x-data="{ profileOpen: false }" class="relative" @click.away="profileOpen = false">
                    <button @click="profileOpen = !profileOpen" 
                            class="flex items-center gap-2.5 px-3 py-1.5 rounded-full border border-slate-200 hover:border-slate-300 bg-white transition cursor-pointer shadow-2xs">
                        <span class="text-xs font-semibold text-slate-700">{{ Auth::user()->name ?? 'Administrator' }}</span>
                        <div class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold">
                            {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                        </div>
                        <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{'rotate-180 text-blue-600': profileOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="profileOpen" 
                         x-transition 
                         x-cloak
                         class="absolute right-0 top-full mt-2 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 py-1.5 z-50"
                         style="display: none;">
                        <div class="px-4 py-2 border-b border-slate-100">
                            <div class="text-xs font-bold text-slate-800 truncate">{{ Auth::user()->name ?? 'User' }}</div>
                            <div class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">{{ Auth::user()->role ?? 'Role' }}</div>
                        </div>
                        @if(Auth::user()?->role === 'mahasiswa')
                            <a href="{{ route('student.profile.edit') }}" class="block px-4 py-2 text-xs text-slate-600 hover:bg-slate-50 transition">Profil Saya</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition flex items-center gap-2 cursor-pointer">
                                <span>🚪</span> Keluar Sistem
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Mobile Menu Hamburger --}}
            <div class="flex items-center md:hidden">
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 rounded-xl text-slate-600 hover:bg-slate-100 cursor-pointer">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': mobileMenuOpen, 'inline-flex': !mobileMenuOpen }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !mobileMenuOpen, 'inline-flex': mobileMenuOpen }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

        </div>
    </div>

    {{-- Mobile Menu Dropdown --}}
    <div x-show="mobileMenuOpen" x-cloak class="md:hidden border-t border-slate-100 bg-white px-4 pt-2 pb-6 space-y-2">
        @if ($isSuperAdmin)
            <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-600' : 'text-slate-600' }}">Dashboard</a>
            <a href="{{ route('admin.applications.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold {{ request()->routeIs('admin.applications.*') ? 'bg-blue-50 text-blue-600' : 'text-slate-600' }}">Pengajuan</a>
            <a href="{{ route('admin.agencies.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold {{ request()->routeIs('admin.agencies.*') ? 'bg-blue-50 text-blue-600' : 'text-slate-600' }}">Instansi & Unit</a>
            <a href="{{ route('admin.universities.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold {{ request()->routeIs('admin.universities.*') ? 'bg-blue-50 text-blue-600' : 'text-slate-600' }}">Universitas</a>
            <a href="{{ route('admin.users.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-600' : 'text-slate-600' }}">Kelola Pengguna</a>
            <a href="{{ route('admin.mentors.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold {{ request()->routeIs('admin.mentors.*') ? 'bg-blue-50 text-blue-600' : 'text-slate-600' }}">Mentor Lapangan</a>
            <a href="{{ route('admin.audit_logs.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold {{ request()->routeIs('admin.audit_logs.*') ? 'bg-blue-50 text-blue-600' : 'text-slate-600' }}">Log Audit</a>
        @endif
        <div class="pt-4 border-t border-slate-100">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-left px-3 py-2 rounded-xl text-sm font-bold text-rose-600 hover:bg-rose-50">
                    Keluar Sistem
                </button>
            </form>
        </div>
    </div>
</nav>