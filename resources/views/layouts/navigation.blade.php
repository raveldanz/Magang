<nav x-data="{ mobileMenuOpen: false }" 
     class="border-b border-slate-100 bg-white transition-all shadow-xs">
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

                    // Dynamic User Logo Resolver
                    $navAvatarLogo = null;
                    $navAvatarInitials = strtoupper(substr($user->name ?? 'U', 0, 1));

                    if ($user) {
                        if ($user->agency_profile_id && $user->agencyProfile) {
                            $logoPath = $user->agencyProfile->logo ?? null;
                            if ($logoPath && (file_exists(public_path('storage/' . $logoPath)) || file_exists(storage_path('app/public/' . $logoPath)))) {
                                $navAvatarLogo = asset('storage/' . $logoPath);
                            } elseif ($logoPath && file_exists(public_path($logoPath))) {
                                $navAvatarLogo = asset($logoPath);
                            } else {
                                $aName = strtolower($user->agencyProfile->agency_name ?? '');
                                if (str_contains($aName, 'kominfo') || str_contains($aName, 'komunikasi')) $navAvatarLogo = asset('images/logos/diskominfo.png');
                                elseif (str_contains($aName, 'penduduk') || str_contains($aName, 'dukcapil')) $navAvatarLogo = asset('images/logos/dispendukcapil.png');
                                elseif (str_contains($aName, 'pustaka') || str_contains($aName, 'pusip')) $navAvatarLogo = asset('images/logos/dispusip.png');
                            }
                        } elseif ($user->university_id || $isUniversitas || $isDosen || $isMahasiswa) {
                            $univObj = null;
                            if ($user->university_id) {
                                $univObj = \App\Models\University::find($user->university_id);
                            }
                            $uName = '';
                            if ($univObj) {
                                if ($univObj->logo && (file_exists(public_path('storage/' . $univObj->logo)) || file_exists(storage_path('app/public/' . $univObj->logo)))) {
                                    $navAvatarLogo = asset('storage/' . $univObj->logo);
                                } elseif ($univObj->logo && file_exists(public_path($univObj->logo))) {
                                    $navAvatarLogo = asset($univObj->logo);
                                }
                                $uName = strtolower($univObj->name ?? '');
                            }
                            if (!$navAvatarLogo) {
                                if (empty($uName)) {
                                    $rawUniv = $user->university ?? null;
                                    $uName = strtolower(is_string($rawUniv) ? $rawUniv : ($user->studentProfile?->universitas ?? ''));
                                }
                                if (str_contains($uName, 'unesa') || str_contains($uName, 'negeri surabaya')) $navAvatarLogo = asset('images/logos/unesa.png');
                                elseif (str_contains($uName, 'its') || str_contains($uName, 'sepuluh nopember')) $navAvatarLogo = asset('images/logos/its.png');
                                elseif (str_contains($uName, 'unair') || str_contains($uName, 'airlangga')) $navAvatarLogo = asset('images/logos/unair.png');
                                elseif (str_contains($uName, 'upn') || str_contains($uName, 'veteran')) $navAvatarLogo = asset('images/logos/upnjatim.png');
                                elseif (str_contains($uName, 'unitomo') || str_contains($uName, 'soetomo')) $navAvatarLogo = asset('images/logos/unitomo.png');
                            }
                        } elseif ($isSuperAdmin) {
                            $navAvatarLogo = asset('images/logos/surabaya.png');
                        }
                    }
                @endphp

                <a href="{{ $dashboardRoute }}" class="flex items-center gap-3.5 group">
                    <img src="{{ asset('images/logo.png') }}" 
                         alt="Logo Pemkot Surabaya" 
                         class="h-11 w-auto object-contain transition group-hover:scale-105" 
                         onerror="this.src='{{ asset('images/default-avatar.png') }}';">
                    <div class="hidden sm:block">
                        <div class="font-extrabold text-slate-800 text-sm leading-tight tracking-tight group-hover:text-blue-600 transition">
                            SIP-MAGANG
                        </div>
                        <div class="text-[11px] font-semibold text-slate-500 leading-tight">
                            Pemerintah Kota Surabaya
                        </div>
                    </div>
                </a>
            </div>

            {{-- 2. DESKTOP CENTER NAVIGATION LINKS --}}
            <div class="hidden md:flex items-center gap-6 lg:gap-8">
                <div class="flex items-center space-x-6 text-xs lg:text-sm font-semibold text-slate-600">

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
                            Instansi
                        </a>
                        <a href="{{ route('admin.universities.index') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.universities.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Universitas
                        </a>

                        {{-- Dropdown Pengguna --}}
                        <div x-data="{ open: false }" class="relative" @click.outside="open = false">
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
                                 class="absolute left-0 top-full mt-3 w-64 rounded-2xl p-2 space-y-1 border border-slate-100 bg-white shadow-2xl z-50">
                                
                                <a href="{{ route('admin.users.index') }}" 
                                   class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-slate-50 transition group {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-600 font-bold' : '' }}">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-800 group-hover:text-blue-600">Semua Pengguna</div>
                                        <div class="text-[11px] text-slate-400">Akun, role & impersonasi</div>
                                    </div>
                                </a>

                                <a href="{{ route('admin.mentors.index') }}" 
                                   class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-slate-50 transition group {{ request()->routeIs('admin.mentors.*') ? 'bg-blue-50 text-blue-600 font-bold' : '' }}">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-800 group-hover:text-blue-600">Mentor Lapangan</div>
                                        <div class="text-[11px] text-slate-400">Pembimbing teknis dinas</div>
                                    </div>
                                </a>
                            </div>
                        </div>

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
                        <a href="{{ route('student.final_report.index') }}" 
                           class="transition py-1 {{ request()->routeIs('student.final_report.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Laporan Akhir
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

            {{-- 3. RIGHT SECTION: NOTIFICATION BELL & USER PROFILE DROPDOWN (DESKTOP) --}}
            <div class="hidden md:flex items-center gap-3">

                {{-- Notification Bell Dropdown --}}
                @php
                    $navHasUnreadDot = Auth::user() ? \App\Services\NotificationService::hasUnreadDot(Auth::user()) : false;
                    $navQuickNotifs = Auth::user() ? array_slice(\App\Services\NotificationService::getNotificationsForUser(Auth::user()), 0, 4) : [];
                @endphp
                <div x-data="{ notifOpen: false }" class="relative" @click.outside="notifOpen = false">
                    <button @click="notifOpen = !notifOpen" 
                            type="button"
                            title="Pemberitahuan Sistem"
                            class="relative p-2.5 rounded-full border border-slate-200 hover:border-slate-300 bg-white text-slate-600 hover:text-blue-600 transition cursor-pointer shadow-2xs">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        @if($navHasUnreadDot)
                            <span class="absolute top-2 right-2 w-2.5 h-2.5 rounded-full bg-rose-500 ring-2 ring-white animate-pulse"></span>
                        @endif
                    </button>

                    <div x-show="notifOpen" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                         x-cloak
                         class="absolute right-0 top-full mt-2 w-80 sm:w-96 rounded-3xl p-3 space-y-2 border border-slate-100 bg-white shadow-2xl z-50">
                        
                        <div class="flex items-center justify-between px-3 py-2 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <span class="font-extrabold text-xs text-slate-900">Pemberitahuan Sistem</span>
                                @if($navHasUnreadDot)
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                @endif
                            </div>
                            <a href="{{ route('notifications.index') }}" class="text-[11px] font-bold text-blue-600 hover:text-blue-800">
                                Lihat Semua &rarr;
                            </a>
                        </div>

                        <div class="space-y-1.5 max-h-72 overflow-y-auto">
                            @forelse($navQuickNotifs as $qn)
                                <a href="{{ $qn['action_url'] ?? route('notifications.index') }}" class="flex items-start gap-3 p-2.5 rounded-2xl hover:bg-slate-50 transition block">
                                    <span class="text-xl shrink-0 mt-0.5">{{ $qn['icon'] ?? '' }}</span>
                                    <div class="space-y-0.5 overflow-hidden">
                                        <div class="text-xs font-bold text-slate-800 truncate">{{ $qn['title'] }}</div>
                                        <div class="text-[11px] text-slate-500 line-clamp-1">{{ $qn['message'] }}</div>
                                        <div class="text-[10px] text-slate-400 font-medium">{{ $qn['time'] }}</div>
                                    </div>
                                </a>
                            @empty
                                <div class="py-6 text-center text-xs text-slate-400">
                                    Tidak ada pemberitahuan baru
                                </div>
                            @endforelse
                        </div>

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs px-2">
                            @if($isSuperAdmin || $isAdminDinas)
                                <a href="{{ route('admin.feedbacks.index') }}" class="text-[11px] font-bold text-slate-600 hover:text-blue-600 flex items-center gap-1">
                                     <span>Feedback</span>
                                </a>
                            @else
                                <a href="{{ route('feedbacks.create') }}" class="text-[11px] font-bold text-slate-600 hover:text-blue-600 flex items-center gap-1">
                                     <span>Kirim Laporan Kendala</span>
                                </a>
                            @endif
                            <a href="{{ route('notifications.index') }}" class="text-[11px] font-bold text-blue-600 hover:text-blue-800">
                                Pusat Tindakan &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                {{-- User Profile Dropdown --}}
                <div x-data="{ profileOpen: false }" class="relative" @click.outside="profileOpen = false">
                    <button @click="profileOpen = !profileOpen" 
                            class="flex items-center gap-2.5 px-3 py-1.5 rounded-full border border-slate-200 hover:border-slate-300 bg-white transition cursor-pointer shadow-2xs">
                        <span class="text-xs font-semibold text-slate-700 max-w-[140px] truncate">{{ Auth::user()->name ?? 'Pengguna' }}</span>
                        @if($navAvatarLogo)
                            <div class="w-6 h-6 rounded-full bg-white border border-slate-200 flex items-center justify-center shrink-0 overflow-hidden shadow-2xs">
                                <img src="{{ $navAvatarLogo }}" alt="Logo" class="w-full h-full object-contain p-0.5">
                            </div>
                        @else
                            <div class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold shrink-0">
                                {{ $navAvatarInitials }}
                            </div>
                        @endif
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
                         class="absolute right-0 top-full mt-2 w-72 rounded-3xl p-3 space-y-1 border border-slate-100 bg-white shadow-2xl z-50">
                        
                        {{-- User Header Info --}}
                        <div class="px-3 py-2.5 bg-slate-50 rounded-2xl mb-2 flex items-center justify-between" style="background-color: #f8fafc !important;">
                            <div class="flex items-center gap-2.5 overflow-hidden">
                                @if($navAvatarLogo)
                                    <div class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center shrink-0 overflow-hidden shadow-2xs">
                                        <img src="{{ $navAvatarLogo }}" alt="Logo" class="w-full h-full object-contain p-1">
                                    </div>
                                @else
                                    <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xs font-bold shrink-0">
                                        {{ $navAvatarInitials }}
                                    </div>
                                @endif
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
                                 Dashboard
                            </a>
                            <a href="{{ route('admin.applications.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Verifikasi Pengajuan
                            </a>
                            <a href="{{ route('admin.agencies.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                 Instansi
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Kelola Pengguna
                            </a>
                        @elseif ($isAdminDinas)
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                             Dashboard
                            </a>
                            <a href="{{ route('admin.applications.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Verifikasi Pengajuan
                            </a>
                            <a href="{{ route('admin.units.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Divisi & Kuota Unit
                            </a>
                            <a href="{{ route('admin.mentors.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Mentor Lapangan
                            </a>
                        @elseif ($isMahasiswa)
                            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                 Dashboard Saya
                            </a>
                            <a href="{{ route('student.profile.edit') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Profil Saya
                            </a>
                            <a href="{{ route('student.logbook.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Logbook Magang
                            </a>
                            <a href="{{ route('student.final_report.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Laporan Akhir
                            </a>
                        @elseif ($isDosen)
                            <a href="{{ route('lecturer.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Portal Dosen
                            </a>
                            <a href="{{ route('lecturer.monitoring.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Mahasiswa Bimbingan
                            </a>
                        @elseif ($isMentor)
                            <a href="{{ route('mentor.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Portal Mentor
                            </a>
                            <a href="{{ route('mentor.logbooks.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Review Logbook
                            </a>
                        @elseif ($isUniversitas)
                            <a href="{{ route('university.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Portal Universitas
                            </a>
                            <a href="{{ route('university.profile.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Profil Kampus
                            </a>
                        @endif

                        {{-- Unified Feedback & Notification Links in Profile --}}
                        <div class="pt-1.5 border-t border-slate-100 mt-1.5 space-y-0.5">
                            <a href="{{ route('notifications.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Pemberitahuan Sistem
                            </a>

                            @if($isSuperAdmin || $isAdminDinas)
                                <a href="{{ route('admin.feedbacks.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                    Feedback
                                </a>
                            @else
                                <a href="{{ route('feedbacks.create') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                    Kirim Laporan Kendala
                                </a>
                                <a href="{{ route('feedbacks.my') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                    Riwayat Masukan Saya
                                </a>
                            @endif
                        </div>

                        <div class="pt-2 border-t border-slate-100">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2.5 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-2xl transition cursor-pointer">
                                    Keluar Sistem
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
         @click.outside="mobileMenuOpen = false"
         :class="{ 'hidden pointer-events-none': !mobileMenuOpen }"
         class="md:hidden absolute top-full left-0 right-0 w-full px-4 py-5 space-y-3 border-b border-slate-200 bg-white shadow-2xl z-50">

        {{-- Info Profil Pengguna di Mobile --}}
        <div class="p-3.5 rounded-2xl border border-slate-100 flex items-center justify-between" style="background-color: #f8fafc !important;">
            <div class="flex items-center gap-3">
                @if($navAvatarLogo)
                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center shrink-0 overflow-hidden shadow-2xs">
                        <img src="{{ $navAvatarLogo }}" alt="Logo" class="w-full h-full object-contain p-1">
                    </div>
                @else
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xs font-bold shrink-0">
                        {{ $navAvatarInitials }}
                    </div>
                @endif
                <div>
                    <div class="text-xs font-bold text-slate-800">{{ Auth::user()->name ?? 'Pengguna' }}</div>
                    <div class="text-[10px] font-semibold text-blue-600 uppercase">{{ strtoupper(str_replace('_', ' ', Auth::user()->role ?? 'Role')) }}</div>
                </div>
            </div>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">Online</span>
        </div>

        {{-- Daftar Navigasi Mobile per Role --}}
        <div class="space-y-1">

            {{-- 5.1 Menu Mobile Super Admin --}}
            @if ($isSuperAdmin)
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.applications.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.applications.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    
                    <span>Pengajuan</span>
                </a>
                <a href="{{ route('admin.agencies.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.agencies.*') || request()->routeIs('admin.units.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    
                    <span>Instansi</span>
                </a>
                <a href="{{ route('admin.universities.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.universities.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    
                    <span>Universitas</span>
                </a>
                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
              
                    <span>Semua Pengguna</span>
                </a>
                <a href="{{ route('admin.mentors.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.mentors.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
               
                    <span>Mentor Lapangan</span>
                </a>

            {{-- 5.2 Menu Mobile Admin Dinas --}}
            @elseif ($isAdminDinas)
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                   
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.applications.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.applications.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    
                    <span>Verifikasi Pengajuan</span>
                </a>
                <a href="{{ route('admin.units.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.units.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    
                    <span>Divisi & Kuota Unit</span>
                </a>
                <a href="{{ route('admin.mentors.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.mentors.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    
                    <span>Mentor Lapangan</span>
                </a>
                <a href="{{ route('admin.logbooks.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.logbooks.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    
                    <span>Monitoring Logbook</span>
                </a>
                <a href="{{ route('admin.certificates.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.certificates.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    
                    <span>Terbitkan Sertifikat</span>
                </a>
                <a href="{{ route('admin.agency_profile.edit') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.agency_profile.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    
                    <span>Profil Dinas</span>
                </a>

            {{-- 5.3 Menu Mobile Mahasiswa --}}
            @elseif ($isMahasiswa)
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    
                    <span>Dashboard Saya</span>
                </a>
                <a href="{{ route('student.profile.edit') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('student.profile.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    
                    <span>Profil Saya</span>
                </a>
                <a href="{{ route('student.application.create') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('student.application.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    
                    <span>Pengajuan Magang</span>
                </a>
                <a href="{{ route('student.logbook.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('student.logbook.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    
                    <span>Logbook Magang</span>
                </a>
                <a href="{{ route('student.final_report.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('student.final_report.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    
                    <span>Laporan Akhir</span>
                </a>

            {{-- 5.4 Menu Mobile Dosen (DPL) --}}
            @elseif ($isDosen)
                <a href="{{ route('lecturer.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('lecturer.dashboard') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    
                    <span>Portal Dosen</span>
                </a>
                <a href="{{ route('lecturer.monitoring.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('lecturer.monitoring.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    
                    <span>Mahasiswa Bimbingan</span>
                </a>
                <a href="{{ route('lecturer.logbooks.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('lecturer.logbooks.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    
                    <span>Logbook Bimbingan</span>
                </a>

            {{-- 5.5 Menu Mobile Mentor Lapangan --}}
            @elseif ($isMentor)
                <a href="{{ route('mentor.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('mentor.dashboard') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    
                    <span>Portal Mentor</span>
                </a>
                <a href="{{ route('mentor.logbooks.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('mentor.logbooks.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    
                    <span>Review Logbook</span>
                </a>

            {{-- 5.6 Menu Mobile Universitas --}}
            @elseif ($isUniversitas)
                <a href="{{ route('university.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('university.dashboard') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                   
                    <span>Portal Universitas</span>
                </a>
                <a href="{{ route('university.lecturers.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('university.lecturers.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    
                    <span>Dosen Pembimbing</span>
                </a>
                <a href="{{ route('university.profile.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('university.profile.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    
                    <span>Profil Kampus</span>
                </a>
            @endif

        </div>

        {{-- Mobile Utility Links --}}
        <div class="pt-2 border-t border-slate-100 space-y-1">
            <a href="{{ route('notifications.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('notifications.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                
                <span>Pemberitahuan Sistem</span>
            </a>
            @if($isSuperAdmin || $isAdminDinas)
                <a href="{{ route('admin.feedbacks.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.feedbacks.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    
                    <span>Feedback</span>
                </a>
            @else
                <a href="{{ route('feedbacks.create') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('feedbacks.create') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                   
                    <span>Kirim Laporan Kendala</span>
                </a>
                <a href="{{ route('feedbacks.my') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('feedbacks.my') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                  
                    <span>Riwayat Masukan Saya</span>
                </a>
            @endif
        </div>

        {{-- Tombol Logout Mobile --}}
        <div class="pt-3 border-t border-slate-100">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 transition cursor-pointer">
                    
                    <span>Keluar Sistem</span>
                </button>
            </form>
        </div>
    </div>
</nav>