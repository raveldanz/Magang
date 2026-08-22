<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                        <span class="font-black text-base text-gray-900 tracking-tight hidden md:inline">SIP-MAGANG</span>
                    </a>
                </div>

                @php
                    $user = Auth::user();
                    $isSuperAdmin = $user && ($user->role === 'super_admin' || ($user->role === 'admin' && is_null($user->agency_profile_id)));
                    $isAdminDinas = $user && ($user->role === 'admin' && !is_null($user->agency_profile_id));
                @endphp

                <!-- Navigation Links (Desktop) -->
                <div class="hidden space-x-6 lg:space-x-8 sm:-my-px sm:ms-8 sm:flex items-center">

                    <!-- 1. MENU MAHASISWA -->
                    @if ($user?->role === 'mahasiswa')
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>

                        <x-nav-link :href="route('student.profile.edit')" :active="request()->routeIs('student.profile.*')">
                            {{ __('Profil Saya') }}
                        </x-nav-link>

                        <x-nav-link :href="route('student.application.create')" :active="request()->routeIs('student.application.*')">
                            {{ __('Pengajuan Magang') }}
                        </x-nav-link>

                        <x-nav-link :href="route('student.logbook.index')" :active="request()->routeIs('student.logbook.*')">
                            {{ __('Logbook Magang') }}
                        </x-nav-link>
                    @endif

                    <!-- 2. MENU PEMBIMBING LAPANGAN (MENTOR) -->
                    @if ($user?->role === 'mentor' || $user?->role === 'pembimbing')
                        <x-nav-link :href="route('mentor.dashboard')" :active="request()->routeIs('mentor.dashboard') || request()->routeIs('mentor.students.*') || request()->routeIs('mentor.evaluations.*')">
                            {{ __('Portal Mentor') }}
                        </x-nav-link>

                        <x-nav-link :href="route('mentor.logbooks.index')" :active="request()->routeIs('mentor.logbooks.*')">
                            {{ __('Review Logbook') }}
                        </x-nav-link>
                    @endif

                    <!-- 3. MENU SUPER ADMIN (GLOBAL GOVERNANCE) -->
                    @if ($isSuperAdmin)
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.applications.index')" :active="request()->routeIs('admin.applications.*')">
                            {{ __('Pengajuan') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.agencies.index')" :active="request()->routeIs('admin.agencies.*') || request()->routeIs('admin.units.*')">
                            {{ __('Instansi & Unit') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.universities.index')" :active="request()->routeIs('admin.universities.*')">
                            {{ __('Universitas') }}
                        </x-nav-link>

                        <!-- Dropdown Menu Pengguna & Mentor (Alpine.js) -->
                        <div x-data="{ open: false }" class="relative inline-flex items-center h-full" @click.away="open = false">
                            <button @click="open = !open" 
                                    type="button" 
                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold rounded-xl text-slate-700 hover:text-blue-700 hover:bg-blue-50 transition cursor-pointer {{ (request()->routeIs('admin.users.*') || request()->routeIs('admin.mentors.*')) ? 'bg-blue-50 text-blue-700 font-bold' : '' }}">
                                <span>👥 Pengguna & Mentor</span>
                                <svg class="w-4 h-4 transition-transform duration-200 opacity-70" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 x-cloak
                                 class="absolute left-0 mt-2 w-64 bg-white rounded-2xl shadow-2xl border border-slate-100 z-[100] p-2 space-y-1"
                                 style="display: none;">
                                <a href="{{ route('admin.users.index') }}" 
                                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-blue-50 text-slate-700 hover:text-blue-700 transition group {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-700 font-bold' : '' }}">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-sm font-bold group-hover:bg-blue-600 group-hover:text-white transition">
                                        👥
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-800 group-hover:text-blue-700">Semua Pengguna</div>
                                        <div class="text-[11px] text-slate-400">Kelola akun, role & impersonasi</div>
                                    </div>
                                </a>
                                <a href="{{ route('admin.mentors.index') }}" 
                                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-blue-50 text-slate-700 hover:text-blue-700 transition group {{ request()->routeIs('admin.mentors.*') ? 'bg-blue-50 text-blue-700 font-bold' : '' }}">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-bold group-hover:bg-indigo-600 group-hover:text-white transition">
                                        👔
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-800 group-hover:text-indigo-700">Mentor Lapangan</div>
                                        <div class="text-[11px] text-slate-400">Pembimbing teknis dinas</div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <x-nav-link :href="route('admin.audit_logs.index')" :active="request()->routeIs('admin.audit_logs.*')">
                            {{ __('Log Audit') }}
                        </x-nav-link>
                    @elseif ($isAdminDinas)
                        <!-- 4. MENU ADMIN INSTANSI DINAS -->
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.applications.index')" :active="request()->routeIs('admin.applications.*')">
                            {{ __('Verifikasi') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.units.index')" :active="request()->routeIs('admin.units.*')">
                            {{ __('Divisi & Kuota') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.mentors.index')" :active="request()->routeIs('admin.mentors.*')">
                            {{ __('Mentor Dinas') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.logbooks.index')" :active="request()->routeIs('admin.logbooks.*')">
                            {{ __('Logbook') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.certificates.index')" :active="request()->routeIs('admin.certificates.*')">
                            {{ __('Sertifikat') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.agency_profile.edit')" :active="request()->routeIs('admin.agency_profile.*')">
                            {{ __('Profil Dinas') }}
                        </x-nav-link>
                    @endif

                    <!-- 5. MENU DOSEN PEMBIMBING LAPANGAN -->
                    @if ($user?->role === 'dosen' || $user?->role === 'academic_advisor')
                        <x-nav-link :href="route('lecturer.dashboard')" :active="request()->routeIs('lecturer.dashboard') || request()->routeIs('lecturer.students.*') || request()->routeIs('lecturer.evaluations.*')">
                            {{ __('Portal Dosen') }}
                        </x-nav-link>

                        <x-nav-link :href="route('lecturer.monitoring.index')" :active="request()->routeIs('lecturer.monitoring.*')">
                            {{ __('Mahasiswa Bimbingan') }}
                        </x-nav-link>

                        <x-nav-link :href="route('lecturer.logbooks.index')" :active="request()->routeIs('lecturer.logbooks.*')">
                            {{ __('Logbook Bimbingan') }}
                        </x-nav-link>
                    @endif

                    <!-- 6. MENU RESMI PERGURUAN TINGGI (UNIVERSITAS) -->
                    @if ($user?->role === 'universitas')
                        <x-nav-link :href="route('university.dashboard')" :active="request()->routeIs('university.dashboard') || request()->routeIs('university.students.*')">
                            {{ __('Portal Universitas') }}
                        </x-nav-link>

                        <x-nav-link :href="route('university.lecturers.index')" :active="request()->routeIs('university.lecturers.*')">
                            {{ __('Daftar Dosen Pembimbing') }}
                        </x-nav-link>

                        <x-nav-link :href="route('university.profile.index')" :active="request()->routeIs('university.profile.*')">
                            {{ __('Profil Kampus') }}
                        </x-nav-link>
                    @endif

                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-bold rounded-xl text-gray-700 bg-white hover:text-gray-900 focus:outline-none transition ease-in-out duration-150 shadow-2xs border-gray-200">
                            <div>{{ Auth::user()?->name ?? 'User' }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile Saya') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Keluar (Log Out)') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger (Mobile Menu) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu (Mobile View) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @if ($user?->role === 'mahasiswa')
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('student.profile.edit')" :active="request()->routeIs('student.profile.*')">
                    {{ __('Profil Saya') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('student.application.create')" :active="request()->routeIs('student.application.*')">
                    {{ __('Pengajuan Magang') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('student.logbook.index')" :active="request()->routeIs('student.logbook.*')">
                    {{ __('Logbook Magang') }}
                </x-responsive-nav-link>
            @elseif ($user?->role === 'mentor' || $user?->role === 'pembimbing')
                <x-responsive-nav-link :href="route('mentor.dashboard')" :active="request()->routeIs('mentor.dashboard') || request()->routeIs('mentor.students.*') || request()->routeIs('mentor.evaluations.*')">
                    {{ __('Portal Mentor') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('mentor.logbooks.index')" :active="request()->routeIs('mentor.logbooks.*')">
                    {{ __('Review Logbook') }}
                </x-responsive-nav-link>
            @elseif ($isSuperAdmin)
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                    {{ __('Executive Dashboard') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.applications.index')" :active="request()->routeIs('admin.applications.*')">
                    {{ __('Semua Pengajuan') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.agencies.index')" :active="request()->routeIs('admin.agencies.*')">
                    {{ __('Master Instansi') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.units.index')" :active="request()->routeIs('admin.units.*')">
                    {{ __('Master Unit & Kuota') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.universities.index')" :active="request()->routeIs('admin.universities.*')">
                    {{ __('Master Universitas') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                    {{ __('Kelola Pengguna') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.mentors.index')" :active="request()->routeIs('admin.mentors.*')">
                    {{ __('Kelola Mentor') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.audit_logs.index')" :active="request()->routeIs('admin.audit_logs.*')">
                    {{ __('Log Audit') }}
                </x-responsive-nav-link>
            @elseif ($isAdminDinas)
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                    {{ __('Dashboard Dinas') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.applications.index')" :active="request()->routeIs('admin.applications.*')">
                    {{ __('Verifikasi Magang') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.units.index')" :active="request()->routeIs('admin.units.*')">
                    {{ __('Divisi & Kuota') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.mentors.index')" :active="request()->routeIs('admin.mentors.*')">
                    {{ __('Mentor Dinas') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.logbooks.index')" :active="request()->routeIs('admin.logbooks.*')">
                    {{ __('Logbook') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.certificates.index')" :active="request()->routeIs('admin.certificates.*')">
                    {{ __('Sertifikat') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.agency_profile.edit')" :active="request()->routeIs('admin.agency_profile.*')">
                    {{ __('Profil Dinas') }}
                </x-responsive-nav-link>
            @elseif ($user?->role === 'dosen' || $user?->role === 'academic_advisor')
                <x-responsive-nav-link :href="route('lecturer.dashboard')" :active="request()->routeIs('lecturer.dashboard') || request()->routeIs('lecturer.students.*') || request()->routeIs('lecturer.evaluations.*')">
                    {{ __('Portal Dosen') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('lecturer.monitoring.index')" :active="request()->routeIs('lecturer.monitoring.*')">
                    {{ __('Mahasiswa Bimbingan') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('lecturer.logbooks.index')" :active="request()->routeIs('lecturer.logbooks.*')">
                    {{ __('Logbook Bimbingan') }}
                </x-responsive-nav-link>
            @elseif ($user?->role === 'universitas')
                <x-responsive-nav-link :href="route('university.dashboard')" :active="request()->routeIs('university.dashboard') || request()->routeIs('university.students.*')">
                    {{ __('Portal Universitas') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('university.lecturers.index')" :active="request()->routeIs('university.lecturers.*')">
                    {{ __('Daftar Dosen Pembimbing') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('university.profile.index')" :active="request()->routeIs('university.profile.*')">
                    {{ __('Profil Kampus') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()?->name ?? 'Guest' }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()?->email ?? '-' }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profil Saya') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Keluar (Log Out)') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>