<nav x-data="{ open: false }" class="bg-white border-b border-slate-200/80 sticky top-0 z-50">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-6">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="focus:outline-none">
                        <x-application-logo class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links (Desktop) -->
                <div class="hidden sm:flex sm:items-center sm:space-x-1.5">

                    <!-- 1. MENU MAHASISWA -->
                    @if (Auth::user()?->role === 'mahasiswa')
                        <a href="{{ route('dashboard') }}" 
                           class="px-3.5 py-2 rounded-xl text-xs font-semibold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600 border border-blue-100 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            {{ __('Dashboard') }}
                        </a>

                        <a href="{{ route('student.profile.edit') }}" 
                           class="px-3.5 py-2 rounded-xl text-xs font-semibold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('student.profile.*') ? 'bg-blue-50 text-blue-600 border border-blue-100 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            {{ __('Profil Saya') }}
                        </a>

                        <a href="{{ route('student.application.create') }}" 
                           class="px-3.5 py-2 rounded-xl text-xs font-semibold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('student.application.*') ? 'bg-blue-50 text-blue-600 border border-blue-100 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            {{ __('Pengajuan Magang') }}
                        </a>

                        <a href="{{ route('student.logbook.index') }}" 
                           class="px-3.5 py-2 rounded-xl text-xs font-semibold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('student.logbook.*') ? 'bg-blue-50 text-blue-600 border border-blue-100 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            {{ __('Logbook Magang') }}
                        </a>
                    @endif

                    <!-- 2. MENU PEMBIMBING LAPANGAN (MENTOR) -->
                    @if (Auth::user()?->role === 'mentor' || Auth::user()?->role === 'pembimbing')
                        <a href="{{ route('mentor.dashboard') }}" 
                           class="px-3.5 py-2 rounded-xl text-xs font-semibold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('mentor.dashboard') ? 'bg-blue-50 text-blue-600 border border-blue-100 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            {{ __('Portal Pembimbing') }}
                        </a>

                        <a href="{{ route('mentor.logbooks.index') }}" 
                           class="px-3.5 py-2 rounded-xl text-xs font-semibold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('mentor.logbooks.*') || request()->routeIs('mentor.students.*') || request()->routeIs('mentor.evaluations.*') ? 'bg-blue-50 text-blue-600 border border-blue-100 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            {{ __('Review Logbook') }}
                        </a>
                    @endif

                    <!-- 3. MENU ADMIN -->
                    @if (Auth::user()?->role === 'admin')
                        <a href="{{ route('admin.applications.index') }}" 
                           class="px-3.5 py-2 rounded-xl text-xs font-semibold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('admin.applications.*') ? 'bg-blue-50 text-blue-600 border border-blue-100 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            {{ __('Verifikasi Magang') }}
                        </a>

                        @if (Route::has('admin.logbooks.index'))
                            <a href="{{ route('admin.logbooks.index') }}" 
                               class="px-3.5 py-2 rounded-xl text-xs font-semibold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('admin.logbooks.*') ? 'bg-blue-50 text-blue-600 border border-blue-100 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                {{ __('Review Logbook') }}
                            </a>
                        @endif

                        <x-nav-link :href="route('admin.units.index')"
                            :active="request()->routeIs('admin.units.*')">
                            {{ __('Manajemen Divisi') }}
                        </x-nav-link>
                        <a href="{{ route('admin.units.index') }}" 
                           class="px-3.5 py-2 rounded-xl text-xs font-semibold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('admin.units.*') ? 'bg-blue-50 text-blue-600 border border-blue-100 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            {{ __('Unit & Kuota') }}
                        </a>

                        <a href="{{ route('admin.certificates.index') }}" 
                           class="px-3.5 py-2 rounded-xl text-xs font-semibold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('admin.certificates.*') ? 'bg-blue-50 text-blue-600 border border-blue-100 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            {{ __('Sertifikat') }}
                        </a>

                        <a href="{{ route('admin.agency_profile.edit') }}" 
                           class="px-3.5 py-2 rounded-xl text-xs font-semibold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('admin.agency_profile.*') ? 'bg-blue-50 text-blue-600 border border-blue-100 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            {{ __('Pengaturan') }}
                        </a>
                    @endif

                    <!-- 4. MENU DOSEN PEMBIMBING LAPANGAN -->
                    <!-- 4. MENU DOSEN (DPL KAMPUS) -->
                    @if (Auth::user()?->role === 'dosen' || Auth::user()?->role === 'academic_advisor')
                        <x-nav-link :href="route('lecturer.dashboard')" :active="request()->routeIs('lecturer.dashboard') || request()->routeIs('lecturer.students.*') || request()->routeIs('lecturer.evaluations.*')">
                            {{ __('Portal Dosen') }}
                        </x-nav-link>
                        <a href="{{ route('lecturer.dashboard') }}" 
                           class="px-3.5 py-2 rounded-xl text-xs font-semibold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('lecturer.dashboard') ? 'bg-blue-50 text-blue-600 border border-blue-100 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            {{ __('Portal Dosen') }}
                        </a>

                        <x-nav-link :href="route('lecturer.monitoring.index')" :active="request()->routeIs('lecturer.monitoring.*')">
                            {{ __('Mahasiswa Bimbingan') }}
                        </x-nav-link>

                        <x-nav-link :href="route('lecturer.logbooks.index')" :active="request()->routeIs('lecturer.logbooks.*')">
                            {{ __('Logbook Bimbingan') }}
                        </x-nav-link>
                    @endif

                    <!-- 5. MENU RESMI PERGURUAN TINGGI (UNIVERSITAS) -->
                    @if (Auth::user()?->role === 'universitas')
                        <x-nav-link :href="route('university.dashboard')" :active="request()->routeIs('university.*')">
                            {{ __('Portal Universitas') }}
                        </x-nav-link>
                        <a href="{{ route('lecturer.monitoring.index') }}" 
                           class="px-3.5 py-2 rounded-xl text-xs font-semibold uppercase tracking-wider transition-all duration-200 {{ request()->routeIs('lecturer.monitoring.*') || request()->routeIs('lecturer.students.*') || request()->routeIs('lecturer.evaluations.*') ? 'bg-blue-50 text-blue-600 border border-blue-100 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                            {{ __('Monitoring Mahasiswa') }}
                        </a>
                    @endif

                </div>
            </div>

            <!-- Settings Dropdown (Desktop) -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-semibold text-slate-700 hover:bg-white hover:border-slate-300 focus:outline-none transition-all duration-200 cursor-pointer">
                            <div>{{ Auth::user()?->name ?? 'Guest' }}</div>
                            <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')" class="text-xs font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-600">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();" class="text-xs font-medium text-red-600 hover:bg-red-50">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger (Mobile Menu Toggle) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile View Dropdown -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white border-b border-slate-200">
        <div class="pt-2 pb-3 space-y-1 px-4">
            @if (Auth::user()?->role === 'mahasiswa')
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="rounded-xl">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('student.profile.edit')" :active="request()->routeIs('student.profile.*')" class="rounded-xl">
                    {{ __('Profil Saya') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('student.application.create')" :active="request()->routeIs('student.application.*')" class="rounded-xl">
                    {{ __('Pengajuan Magang') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('student.logbook.index')" :active="request()->routeIs('student.logbook.*')" class="rounded-xl">
                    {{ __('Logbook Magang') }}
                </x-responsive-nav-link>
            @elseif (Auth::user()?->role === 'mentor' || Auth::user()?->role === 'pembimbing')
                <x-responsive-nav-link :href="route('mentor.dashboard')" :active="request()->routeIs('mentor.dashboard')" class="rounded-xl">
                    {{ __('Portal Pembimbing (Mentor)') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('mentor.logbooks.index')" :active="request()->routeIs('mentor.logbooks.*') || request()->routeIs('mentor.students.*') || request()->routeIs('mentor.evaluations.*')" class="rounded-xl">
                    {{ __('Review Logbook Mahasiswa') }}
                </x-responsive-nav-link>
            @elseif (Auth::user()?->role === 'dosen' || Auth::user()?->role === 'academic_advisor')
                <x-responsive-nav-link :href="route('lecturer.dashboard')" :active="request()->routeIs('lecturer.dashboard') || request()->routeIs('lecturer.students.*') || request()->routeIs('lecturer.evaluations.*')">
                    {{ __('Portal Dosen') }}
                <x-responsive-nav-link :href="route('lecturer.dashboard')" :active="request()->routeIs('lecturer.dashboard')" class="rounded-xl">
                    {{ __('Portal Dosen (DPL Kampus)') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('lecturer.monitoring.index')" :active="request()->routeIs('lecturer.monitoring.*')">
                    {{ __('Mahasiswa Bimbingan') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('lecturer.logbooks.index')" :active="request()->routeIs('lecturer.logbooks.*')">
                    {{ __('Logbook Bimbingan') }}
                <x-responsive-nav-link :href="route('lecturer.monitoring.index')" :active="request()->routeIs('lecturer.monitoring.*') || request()->routeIs('lecturer.students.*') || request()->routeIs('lecturer.evaluations.*')" class="rounded-xl">
                    {{ __('Monitoring Mahasiswa') }}
                </x-responsive-nav-link>
            @elseif (Auth::user()?->role === 'admin')
                <x-responsive-nav-link :href="route('admin.applications.index')" :active="request()->routeIs('admin.applications.*')" class="rounded-xl">
                    {{ __('Verifikasi Magang') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.units.index')" :active="request()->routeIs('admin.units.*')">
                    {{ __('Manajemen Divisi') }}
                <x-responsive-nav-link :href="route('admin.units.index')" :active="request()->routeIs('admin.units.*')" class="rounded-xl">
                    {{ __('Manajemen Unit & Kuota') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.certificates.index')" :active="request()->routeIs('admin.certificates.*')" class="rounded-xl">
                    {{ __('Manajemen Sertifikat') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.agency_profile.edit')" :active="request()->routeIs('admin.agency_profile.*')" class="rounded-xl">
                    {{ __('Pengaturan Instansi') }}
                </x-responsive-nav-link>
            @elseif (Auth::user()?->role === 'universitas')
                <x-responsive-nav-link :href="route('university.dashboard')" :active="request()->routeIs('university.*')">
                    {{ __('Portal Universitas') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Mobile User Info & Logout -->
        <div class="pt-4 pb-3 border-t border-slate-100 px-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">
                    {{ substr(Auth::user()?->name ?? 'U', 0, 1) }}
                </div>
                <div>
                    <div class="font-bold text-sm text-slate-800">{{ Auth::user()?->name ?? 'Guest' }}</div>
                    <div class="font-medium text-xs text-slate-400">{{ Auth::user()?->email ?? '-' }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" class="rounded-xl">
                    {{ __('Profile') }}
                </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();" class="rounded-xl text-red-600">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>