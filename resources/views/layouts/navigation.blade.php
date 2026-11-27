<nav x-data="{ 
        mobileMenuOpen: false,
        init() {
            this.$watch('mobileMenuOpen', value => {
                document.documentElement.classList.toggle('overflow-hidden', value);
                document.body.classList.toggle('overflow-hidden', value);
            });
        }
     }"
     @keydown.escape.window="mobileMenuOpen = false"
     class="border-b border-slate-200/80 bg-white transition-all shadow-xs relative z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 sm:h-20 items-center gap-3">
            
            {{-- 1. BRAND LOGO RESMI PEMKOT SURABAYA --}}
            <div class="flex items-center gap-4 lg:gap-8 shrink-0">
                @php
                    $user = Auth::user();
                    $isSuperAdmin = ($user?->isSuperAdmin() ?? false);
                    $isSupervisor = $user && ($user->role === 'supervisor' || ($user->isSupervisor() ?? false));
                    $isAdminDinas = $user && ($user->role === 'admin' && !is_null($user->agency_profile_id));
                    $isBidangAdmin = $user && ($user->isBidangAdmin() ?? false);
                    $isMahasiswa = $user && $user->role === 'mahasiswa';
                    $isDosen = $user && ($user->role === 'dosen' || $user->role === 'academic_advisor');
                    $isMentor = $user && ($user->role === 'mentor' || $user->role === 'pembimbing');
                    $isUniversitas = $user && $user->role === 'universitas';

                    $dashboardRoute = route('dashboard');
                    if ($isSupervisor) {
                        $dashboardRoute = route('supervisor.dashboard');
                    } elseif ($isSuperAdmin || $isAdminDinas) {
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
                        if ($user->agency_profile_id) {
                            $navAvatarLogo = $user->agencyProfile?->logo_url 
                                ?? \App\Models\AgencyProfile::find($user->agency_profile_id)?->logo_url;
                        } elseif ($user->university_id || $isUniversitas || $isDosen || $isMahasiswa) {
                            $univObj = null;
                            if ($user->university_id) {
                                $univObj = \App\Models\University::find($user->university_id);
                            }
                            if ($univObj) {
                                $navAvatarLogo = $univObj->logo_url;
                            }
                            if (!$navAvatarLogo) {
                                $rawUniv = $user->university ?? null;
                                $uName = strtolower(is_string($rawUniv) ? $rawUniv : ($user->studentProfile?->universitas ?? ''));
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

                    if (!$navAvatarLogo && isset($agencyProfile) && !empty($agencyProfile?->logo_url)) {
                        $navAvatarLogo = $agencyProfile->logo_url;
                    }

                    $driver = \DB::connection()->getDriverName();
                    $like = $driver === 'pgsql' ? 'ilike' : 'like';
                    $dispusipAgency = \App\Models\AgencyProfile::where('agency_name', $like, '%perpustakaan%')
                        ->orWhere('acronym', $like, '%dispusip%')
                        ->first();
                    $instansiNavUrl = $dispusipAgency ? route('admin.agencies.show', $dispusipAgency->id) : route('admin.agencies.index');
                @endphp

                <a href="{{ url('/') }}" class="flex items-center gap-2.5 sm:gap-3 group py-1.5 shrink-0" title="Portal Magang Dinas Perpustakaan dan Kearsipan Kota Surabaya">
                    <img src="{{ asset('images/logos/surabaya.png') }}" 
                         alt="Pemerintah Kota Surabaya" 
                         class="h-9 sm:h-10 w-auto object-contain shrink-0 transition-transform group-hover:scale-105"
                         style="aspect-ratio: 960/1234; height: 38px; width: auto; max-height: 40px; object-fit: contain;">
                    <div class="h-6 w-px bg-slate-200"></div>
                    <img src="{{ asset('images/logos/dispusip.png') }}?v=5" 
                         alt="Dispusip Surabaya" 
                         class="h-9 sm:h-10 w-auto object-contain shrink-0 transition-transform group-hover:scale-105"
                         style="height: 38px; width: auto; max-height: 40px; object-fit: contain;">
                    
                </a>

                {{-- 2. DESKTOP NAVIGATION BAR (>= 1024px agar lega & proporsional) --}}
                <div class="hidden lg:flex items-center space-x-5 xl:space-x-7 text-sm font-semibold text-slate-600">

                    @if ($isSuperAdmin)
                        <a href="{{ route('admin.dashboard') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.dashboard') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('admin.applications.index') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.applications.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Pengajuan
                        </a>
                        <a href="{{ $instansiNavUrl }}" 
                           class="transition py-1 {{ request()->routeIs('admin.agencies.*') || request()->routeIs('admin.units.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Instansi
                        </a>
                        <a href="{{ route('admin.universities.index') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.universities.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Kampus & Sekolah
                        </a>

                        <a href="{{ route('public.projects.index') }}" 
                           class="transition py-1 {{ request()->routeIs('public.projects.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Proyek Dinas
                        </a>

                        @if(auth()->user()->isSuperAdmin())
                            <a href="{{ route('admin.settings.edit') }}" class="px-2 py-1 font-semibold hover:text-blue-600 {{ request()->routeIs('admin.settings.*') ? 'text-blue-600' : 'text-slate-600' }}">Pengaturan Sistem</a>
                        @endif
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
                                 class="absolute left-0 top-full mt-3 w-64 max-w-[calc(100vw-2rem)] rounded-2xl p-2 space-y-1 border border-slate-100 bg-white shadow-2xl z-50">
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

                    @elseif ($isSupervisor)
                        <a href="{{ route('supervisor.dashboard') }}" 
                           class="transition py-1 {{ request()->routeIs('supervisor.dashboard') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('supervisor.applications.index') }}" 
                           class="transition py-1 {{ request()->routeIs('supervisor.applications.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Pengajuan
                        </a>
                        <a href="{{ route('supervisor.certificates.index') }}" 
                           class="transition py-1 {{ request()->routeIs('supervisor.certificates.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Sertifikat
                        </a>
                        <a href="{{ route('supervisor.reports.index') }}" 
                           class="transition py-1 {{ request()->routeIs('supervisor.reports.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Laporan
                        </a>
                        <a href="{{ route('public.projects.index') }}" 
                           class="transition py-1 {{ request()->routeIs('public.projects.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Proyek Dinas
                        </a>

                    @elseif ($isAdminDinas)
                        <a href="{{ route('admin.dashboard') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.dashboard') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('admin.applications.index') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.applications.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Pengajuan
                        </a>
                        @if ($isBidangAdmin)
                            <a href="{{ route('admin.units.show', Auth::user()->unit_id) }}" 
                               class="transition py-1 {{ request()->routeIs('admin.units.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                                Kelola Bidang
                            </a>
                            <a href="{{ route('admin.mentors.index') }}" 
                               class="transition py-1 {{ request()->routeIs('admin.mentors.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                                Mentor Bidang
                            </a>
                        @else
                            <a href="{{ route('admin.units.index') }}" 
                               class="transition py-1 {{ request()->routeIs('admin.units.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                                Unit Bidang
                            </a>
                            <a href="{{ route('admin.mentors.index') }}" 
                               class="transition py-1 {{ request()->routeIs('admin.mentors.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                                Mentor Dinas
                            </a>
                        @endif
                        <a href="{{ route('admin.logbooks.index') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.logbooks.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Logbook
                        </a>
                        <a href="{{ route('admin.certificates.index') }}" 
                           class="transition py-1 {{ request()->routeIs('admin.certificates.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Sertifikat
                        </a>
                        @if (! $isBidangAdmin)
                            <a href="{{ route('admin.agency_profile.edit') }}" 
                               class="transition py-1 {{ request()->routeIs('admin.agency_profile.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                                Profil Dinas
                            </a>
                        @endif

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

                    @elseif ($isDosen)
                        <a href="{{ route('lecturer.dashboard') }}" 
                           class="transition py-1 {{ request()->routeIs('lecturer.dashboard') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Portal Pembimbing
                        </a>
                        <a href="{{ route('lecturer.monitoring.index') }}" 
                           class="transition py-1 {{ request()->routeIs('lecturer.monitoring.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Peserta Bimbingan
                        </a>
                        <a href="{{ route('lecturer.logbooks.index') }}" 
                           class="transition py-1 {{ request()->routeIs('lecturer.logbooks.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Logbook Bimbingan
                        </a>

                    @elseif ($isMentor)
                        <a href="{{ route('mentor.dashboard') }}"
                           class="transition py-1 text-base {{ request()->routeIs('mentor.dashboard', 'mentor.students.*', 'mentor.evaluations.*') ? 'text-blue-700 font-bold border-b-2 border-blue-600' : 'text-slate-700 hover:text-blue-700' }}">
                            Beranda &amp; Tugas
                        </a>
                        <a href="{{ route('mentor.logbooks.index') }}"
                           class="transition py-1 text-base {{ request()->routeIs('mentor.logbooks.*') ? 'text-blue-700 font-bold border-b-2 border-blue-600' : 'text-slate-700 hover:text-blue-700' }}">
                            Periksa Logbook
                        </a>

                    @elseif ($isUniversitas)
                        <a href="{{ route('university.dashboard') }}" 
                           class="transition py-1 {{ request()->routeIs('university.dashboard') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Portal Kampus/Sekolah
                        </a>
                        <a href="{{ route('university.lecturers.index') }}" 
                           class="transition py-1 {{ request()->routeIs('university.lecturers.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Pembimbing
                        </a>
                        <a href="{{ route('university.profile.index') }}" 
                           class="transition py-1 {{ request()->routeIs('university.profile.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Profil Kampus
                        </a>
                    @endif
                </div>
            </div>

            {{-- 3. RIGHT SECTION: NOTIFICATION & USER PROFILE (DESKTOP >= 1024px) --}}
            @auth
                @php
                    $navAllNotifs = Auth::user() ? \App\Services\NotificationService::getNotificationsForUser(Auth::user()) : [];
                    $navHasUnreadDot = Auth::user() ? (\App\Services\NotificationService::hasUnreadDot(Auth::user(), $navAllNotifs) || ((int) ($unreadCount ?? 0) > 0)) : false;
                    // Pesan chat belum dibaca didahulukan agar tidak tenggelam di bawah pemberitahuan otomatis lain
                    $navIsUnreadChat = fn ($n) => ($n['category'] ?? '') === 'chat' && empty($n['is_read']);
                    $navQuickNotifs = array_slice(array_merge(
                        array_values(array_filter($navAllNotifs, $navIsUnreadChat)),
                        array_values(array_filter($navAllNotifs, fn ($n) => !$navIsUnreadChat($n)))
                    ), 0, 4);
                    $navChatUnread = (int) ($chatUnread ?? 0);
                    $navChatLabel = $navChatUnread > 99 ? '99+' : (string) $navChatUnread;
                @endphp
            <div class="hidden lg:flex items-center gap-3">
                {{-- Pesan / Chat (badge diperbarui live via Alpine.store('chat')) --}}
                <a href="{{ route('chat.index') }}" title="Pesan" aria-label="Pesan"
                   class="relative p-2.5 rounded-full border transition shadow-2xs {{ request()->routeIs('chat.*') ? 'border-blue-200 bg-blue-50 text-blue-600' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-600 hover:text-blue-600' }}">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    <span x-show="$store.chat.unread > 0" x-text="$store.chat.label" data-chat-badge
                          class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-500 text-white text-[10px] font-bold leading-[18px] text-center ring-2 ring-white"
                          @if($navChatUnread < 1) style="display: none;" @endif>{{ $navChatLabel }}</span>
                </a>

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
                         class="absolute right-0 top-full mt-2 w-80 sm:w-96 max-w-[calc(100vw-2rem)] rounded-3xl p-3 space-y-2 border border-slate-100 bg-white shadow-2xl z-50">
                        
                        <div class="flex items-center justify-between px-3 py-2 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <span class="font-extrabold text-xs text-slate-900">Pemberitahuan Sistem</span>
                                @if($navHasUnreadDot)
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                @endif
                            </div>
                            <a href="{{ route('notifications.index') }}" class="text-[11px] font-bold text-blue-600 hover:text-blue-800">
                                Lihat Semua 
                            </a>
                        </div>

                        <div class="space-y-1.5 max-h-72 overflow-y-auto">
                            @forelse($navQuickNotifs as $qn)
                                <a href="{{ $qn['action_url'] ?? route('notifications.index') }}" class="flex items-start gap-3 p-2.5 rounded-2xl hover:bg-slate-50 transition block">
                                    {{-- Ikon garis, bukan emoji tersimpan ($qn['icon']): tampilan peran senior tanpa emoji --}}
                                    <svg class="w-5 h-5 shrink-0 mt-0.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 11-6 0"/></svg>
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
                                Pusat Tindakan 
                            </a>
                        </div>
                    </div>
                </div>

                <div x-data="{ profileOpen: false }" class="relative" @click.outside="profileOpen = false">
                    <button @click="profileOpen = !profileOpen" 
                            class="flex items-center gap-2.5 px-3 py-1.5 rounded-full border border-slate-200 hover:border-slate-300 bg-white transition cursor-pointer shadow-2xs">
                        <span class="text-xs font-semibold text-slate-700 max-w-[140px] truncate">{{ Auth::user()->name ?? 'Pengguna' }}</span>
                        @if($navAvatarLogo)
                            <div class="rounded-lg bg-white border border-slate-200 flex items-center justify-center shrink-0 overflow-hidden shadow-2xs" style="height: 28px; min-width: 28px; max-width: 52px; padding: 2px;">
                                <img src="{{ $navAvatarLogo }}" alt="Logo" class="object-contain" style="height: 100%; width: auto; max-width: 100%;" onerror="this.onerror=null; this.src='{{ asset('images/logos/surabaya.png') }}';">
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
                         class="absolute right-0 top-full mt-2 w-72 max-w-[calc(100vw-2rem)] rounded-3xl p-3 space-y-1 border border-slate-100 bg-white shadow-2xl z-50">
                        
                        <div class="px-3 py-2.5 bg-slate-50 rounded-2xl mb-2 flex items-center justify-between" style="background-color: #f8fafc !important;">
                            <div class="flex items-center gap-2.5 overflow-hidden">
                                @if($navAvatarLogo)
                                    <div class="rounded-xl bg-white border border-slate-200 flex items-center justify-center shrink-0 overflow-hidden shadow-2xs" style="height: 36px; min-width: 36px; max-width: 64px; padding: 4px;">
                                        <img src="{{ $navAvatarLogo }}" alt="Logo" class="object-contain" style="height: 100%; width: auto; max-width: 100%;" onerror="this.onerror=null; this.src='{{ asset('images/logos/surabaya.png') }}';">
                                    </div>
                                @else
                                    <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xs font-bold shrink-0">
                                        {{ $navAvatarInitials }}
                                    </div>
                                @endif
                                <div class="overflow-hidden">
                                    <div class="text-xs font-bold text-slate-900 truncate">{{ Auth::user()->name ?? 'User' }}</div>
                                    <div class="text-[10px] font-semibold text-blue-600 tracking-wide">{{ Auth::user()->roleLabel() }}</div>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">
                                Online
                            </span>
                        </div>

                        <a href="{{ url('/') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                            Home
                        </a>
                        @if ($isSuperAdmin || $isSupervisor || !Auth::check())
                            <a href="{{ route('public.projects.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition {{ request()->routeIs('public.projects.*') ? 'bg-blue-50 text-blue-600 font-bold' : '' }}">
                                Proyek Dinas
                            </a>
                        @endif

                        @if ($isSuperAdmin)
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Dashboard
                            </a>
                            <a href="{{ route('admin.applications.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Pengajuan
                            </a>
                            <a href="{{ $instansiNavUrl }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Instansi
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Kelola Pengguna
                            </a>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Pengaturan Akun
                            </a>
                        @elseif ($isSupervisor)
                            <a href="{{ route('supervisor.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Dashboard
                            </a>
                            <a href="{{ route('supervisor.applications.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Pengajuan
                            </a>
                            <a href="{{ route('supervisor.certificates.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Sertifikat
                            </a>
                            <a href="{{ route('supervisor.reports.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Laporan
                            </a>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Pengaturan Akun
                            </a>
                        @elseif ($isAdminDinas)
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Dashboard
                            </a>
                            <a href="{{ route('admin.applications.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Pengajuan
                            </a>
                            <a href="{{ route('admin.units.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Unit Bidang
                            </a>
                            <a href="{{ route('admin.mentors.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Mentor Lapangan
                            </a>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Pengaturan Akun
                            </a>
                        @elseif ($isMahasiswa)
                            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Dashboard Saya
                            </a>
                            <a href="{{ route('student.profile.edit') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Profil Saya
                            </a>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Pengaturan Akun
                            </a>
                            <a href="{{ route('student.logbook.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Logbook Magang
                            </a>
                            <a href="{{ route('student.final_report.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Laporan Akhir
                            </a>
                        @elseif ($isDosen)
                            <a href="{{ route('lecturer.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Portal Pembimbing
                            </a>
                            <a href="{{ route('lecturer.monitoring.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Peserta Bimbingan
                            </a>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Pengaturan Akun
                            </a>
                        @elseif ($isMentor)
                            <a href="{{ route('mentor.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-sm font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Beranda &amp; Tugas
                            </a>
                            <a href="{{ route('mentor.logbooks.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-sm font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Periksa Logbook
                            </a>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Pengaturan Akun
                            </a>
                        @elseif ($isUniversitas)
                            <a href="{{ route('university.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Portal Kampus/Sekolah
                            </a>
                            <a href="{{ route('university.lecturers.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Pembimbing
                            </a>
                            <a href="{{ route('university.profile.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Profil Kampus
                            </a>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Pengaturan Akun
                            </a>
                        @endif

                        <div class="pt-1.5 border-t border-slate-100 mt-1.5 space-y-0.5">
                            <a href="{{ route('chat.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Pesan
                            </a>
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
            @else
            <div class="hidden lg:flex items-center gap-2.5">
                <a href="{{ url('/') }}" class="text-sm font-semibold text-slate-700 hover:text-blue-600 transition px-3 py-2">
                    Home
                </a>
                <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-700 hover:text-blue-600 transition px-3 py-2">
                    Masuk
                </a>
                <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-sm font-bold rounded-xl shadow-xs transition">
                    Daftar
                </a>
            </div>
            @endauth

            {{-- 4. MOBILE & TABLET QUICK ACTIONS + HAMBURGER (< 1024px, touch target >= 44x44px) --}}
            <div class="flex items-center gap-1.5 lg:hidden shrink-0">
                @auth
                <a href="{{ route('chat.index') }}"
                   class="relative min-w-[44px] min-h-[44px] inline-flex items-center justify-center rounded-xl transition {{ request()->routeIs('chat.*') ? 'bg-blue-50 text-blue-600' : 'text-slate-600 hover:bg-slate-100 hover:text-blue-600' }}"
                   aria-label="Pesan">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    <span x-show="$store.chat.unread > 0" x-text="$store.chat.label" data-chat-badge
                          class="absolute top-1 right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-500 text-white text-[10px] font-bold leading-[18px] text-center ring-2 ring-white"
                          @if(($chatUnread ?? 0) < 1) style="display: none;" @endif>{{ ($chatUnread ?? 0) > 99 ? '99+' : ($chatUnread ?? 0) }}</span>
                </a>
                <a href="{{ route('notifications.index') }}"
                   class="relative min-w-[44px] min-h-[44px] inline-flex items-center justify-center rounded-xl transition {{ request()->routeIs('notifications.*') ? 'bg-blue-50 text-blue-600' : 'text-slate-600 hover:bg-slate-100 hover:text-blue-600' }}"
                   aria-label="Pemberitahuan Sistem">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @if($navHasUnreadDot ?? false)
                        <span class="absolute top-2.5 right-2.5 w-2.5 h-2.5 rounded-full bg-rose-500 ring-2 ring-white"></span>
                    @endif
                </a>
                @endauth
                <button @click="mobileMenuOpen = !mobileMenuOpen"
                        type="button"
                        class="min-w-[44px] min-h-[44px] inline-flex items-center justify-center rounded-xl border border-slate-200/80 bg-white text-slate-700 hover:bg-slate-50 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500/30 transition cursor-pointer shadow-2xs"
                        :aria-expanded="mobileMenuOpen.toString()"
                        aria-controls="mobile-nav-drawer"
                        aria-label="Buka Menu">
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': mobileMenuOpen, 'inline-flex': !mobileMenuOpen }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !mobileMenuOpen, 'inline-flex': mobileMenuOpen }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

        </div>
    </div>

    {{-- 5. OFF-CANVAS SLIDE-OVER DRAWER (< 1024px) DENGAN BACKDROP & TOMBOL CLOSE JELAS --}}
    <div x-show="mobileMenuOpen"
         x-cloak
         class="lg:hidden fixed inset-0 z-[70]"
         role="dialog"
         aria-modal="true"
         aria-label="Menu Navigasi">

        {{-- Backdrop Gelap --}}
        <div x-show="mobileMenuOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="mobileMenuOpen = false"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"></div>

        {{-- Panel Drawer Samping Kanan (Anti-Overflow 375px, Transisi Halus) --}}
        <aside id="mobile-nav-drawer"
               data-nav-drawer
               x-show="mobileMenuOpen"
               x-transition:enter="transform transition ease-out duration-200"
               x-transition:enter-start="translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transform transition ease-in duration-150"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="translate-x-full"
               class="fixed inset-y-0 right-0 z-10 w-[min(86vw,340px)] max-w-full bg-white shadow-2xl border-l border-slate-200 flex flex-col">

            {{-- Drawer Header + Tombol Close >= 44x44px --}}
            <div class="flex items-center justify-between gap-3 px-4 py-3.5 border-b border-slate-100 bg-white shrink-0">
                <div class="flex items-center gap-2 min-w-0">
                    <img src="{{ asset('images/logos/surabaya.png') }}" alt="Pemkot Surabaya" class="h-7 w-auto object-contain shrink-0">
                    <div class="h-5 w-px bg-slate-200 shrink-0"></div>
                    <img src="{{ asset('images/logos/dispusip.png') }}?v=5" alt="Dispusip Surabaya" class="h-7 w-auto object-contain shrink-0">
                    <span class="text-xs font-extrabold text-slate-800 truncate ml-0.5">Portal Magang</span>
                </div>
                <button type="button"
                        data-nav-drawer-close
                        @click="mobileMenuOpen = false"
                        aria-label="Tutup Menu"
                        class="min-w-[44px] min-h-[44px] shrink-0 inline-flex items-center justify-center rounded-xl border border-slate-200/80 bg-slate-50 text-slate-600 hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/30 transition cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Drawer Scrollable Body --}}
            <div class="flex-1 overflow-y-auto overscroll-contain px-4 py-4 space-y-4">
                @auth
                @php
                    $mLinkBase = 'flex items-center justify-between gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition min-h-[44px]';
                    $mLinkActive = 'bg-blue-50 text-blue-700 font-bold ring-1 ring-inset ring-blue-200/70';
                    $mLinkIdle = 'text-slate-700 hover:bg-slate-50 hover:text-slate-900';
                @endphp

                {{-- Kartu Profil Pengguna --}}
                <div class="p-3 rounded-2xl border border-slate-200/80 flex items-center justify-between gap-2.5 bg-slate-50">
                    <div class="flex items-center gap-2.5 min-w-0">
                        @if($navAvatarLogo)
                            <div class="rounded-xl bg-white border border-slate-200 flex items-center justify-center shrink-0 overflow-hidden shadow-2xs" style="height: 36px; min-width: 36px; max-width: 56px; padding: 4px;">
                                <img src="{{ $navAvatarLogo }}" alt="Logo" class="object-contain" style="height: 100%; width: auto; max-width: 100%;" onerror="this.onerror=null; this.src='{{ asset('images/logos/surabaya.png') }}';">
                            </div>
                        @else
                            <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xs font-bold shrink-0">
                                {{ $navAvatarInitials }}
                            </div>
                        @endif
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-slate-900 leading-tight truncate">{{ Auth::user()->name ?? 'Pengguna' }}</div>
                            <div class="text-[11px] font-semibold text-blue-600 truncate mt-0.5">{{ Auth::user()->roleLabel() }}</div>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">Online</span>
                </div>

                {{-- Daftar Navigasi Menu Utama --}}
                <div class="space-y-1">
                    <p class="px-3.5 pb-1 text-[11px] font-bold uppercase tracking-wider text-slate-400">Menu Utama</p>
                    <a href="{{ url('/') }}" class="{{ $mLinkBase }} {{ request()->is('/') ? $mLinkActive : $mLinkIdle }}">
                        <span>Home</span>
                    </a>
                    @if ($isSuperAdmin || $isSupervisor)
                        <a href="{{ route('public.projects.index') }}" class="{{ $mLinkBase }} {{ request()->routeIs('public.projects.*') ? $mLinkActive : $mLinkIdle }}">
                            <span>Proyek Dinas</span>
                        </a>
                    @endif
                    @if ($isSuperAdmin)
                        <a href="{{ route('admin.dashboard') }}" class="{{ $mLinkBase }} {{ request()->routeIs('admin.dashboard') ? $mLinkActive : $mLinkIdle }}">
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('admin.applications.index') }}" class="{{ $mLinkBase }} {{ request()->routeIs('admin.applications.*') ? $mLinkActive : $mLinkIdle }}">
                            <span>Pengajuan</span>
                        </a>
                        <a href="{{ $instansiNavUrl }}" class="{{ $mLinkBase }} {{ request()->routeIs('admin.agencies.*') || request()->routeIs('admin.units.*') ? $mLinkActive : $mLinkIdle }}">
                            <span>Instansi Dinas</span>
                        </a>
                        <a href="{{ route('admin.universities.index') }}" class="{{ $mLinkBase }} {{ request()->routeIs('admin.universities.*') ? $mLinkActive : $mLinkIdle }}">
                            <span>Kampus & Sekolah</span>
                        </a>
                        @if(auth()->user()->isSuperAdmin())
                            <a href="{{ route('admin.settings.edit') }}" class="px-2 py-1 font-semibold hover:text-blue-600 {{ request()->routeIs('admin.settings.*') ? 'text-blue-600' : 'text-slate-600' }}">Pengaturan Sistem</a>
                        @endif
                        <a href="{{ route('admin.users.index') }}" class="{{ $mLinkBase }} {{ request()->routeIs('admin.users.*') ? $mLinkActive : $mLinkIdle }}">
                            <span>Semua Pengguna</span>
                        </a>
                        <a href="{{ route('admin.mentors.index') }}" class="{{ $mLinkBase }} {{ request()->routeIs('admin.mentors.*') ? $mLinkActive : $mLinkIdle }}">
                            <span>Mentor Lapangan</span>
                        </a>
                        <a href="{{ route('profile.edit') }}" class="{{ $mLinkBase }} {{ request()->routeIs('profile.edit') ? $mLinkActive : $mLinkIdle }}">
                            <span>Pengaturan Akun</span>
                        </a>

                    @elseif ($isSupervisor)
                        <a href="{{ route('supervisor.dashboard') }}" class="{{ $mLinkBase }} {{ request()->routeIs('supervisor.dashboard') ? $mLinkActive : $mLinkIdle }}">
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('supervisor.applications.index') }}" class="{{ $mLinkBase }} {{ request()->routeIs('supervisor.applications.*') ? $mLinkActive : $mLinkIdle }}">
                            <span>Pengajuan</span>
                        </a>
                        <a href="{{ route('supervisor.certificates.index') }}" class="{{ $mLinkBase }} {{ request()->routeIs('supervisor.certificates.*') ? $mLinkActive : $mLinkIdle }}">
                            <span>Sertifikat</span>
                        </a>
                        <a href="{{ route('supervisor.reports.index') }}" class="{{ $mLinkBase }} {{ request()->routeIs('supervisor.reports.*') ? $mLinkActive : $mLinkIdle }}">
                            <span>Laporan</span>
                        </a>
                        <a href="{{ route('profile.edit') }}" class="{{ $mLinkBase }} {{ request()->routeIs('profile.edit') ? $mLinkActive : $mLinkIdle }}">
                            <span>Pengaturan Akun</span>
                        </a>

                    @elseif ($isAdminDinas)
                        <a href="{{ route('admin.dashboard') }}" class="{{ $mLinkBase }} {{ request()->routeIs('admin.dashboard') ? $mLinkActive : $mLinkIdle }}">
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('admin.applications.index') }}" class="{{ $mLinkBase }} {{ request()->routeIs('admin.applications.*') ? $mLinkActive : $mLinkIdle }}">
                            <span>Pengajuan</span>
                        </a>
                        @if ($isBidangAdmin)
                            <a href="{{ route('admin.units.show', Auth::user()->unit_id) }}" class="{{ $mLinkBase }} {{ request()->routeIs('admin.units.*') ? $mLinkActive : $mLinkIdle }}">
                                <span>Kelola Bidang</span>
                            </a>
                            <a href="{{ route('admin.mentors.index') }}" class="{{ $mLinkBase }} {{ request()->routeIs('admin.mentors.*') ? $mLinkActive : $mLinkIdle }}">
                                <span>Mentor Bidang</span>
                            </a>
                        @else
                            <a href="{{ route('admin.units.index') }}" class="{{ $mLinkBase }} {{ request()->routeIs('admin.units.*') ? $mLinkActive : $mLinkIdle }}">
                                <span>Unit Bidang</span>
                            </a>
                            <a href="{{ route('admin.mentors.index') }}" class="{{ $mLinkBase }} {{ request()->routeIs('admin.mentors.*') ? $mLinkActive : $mLinkIdle }}">
                                <span>Mentor Lapangan</span>
                            </a>
                        @endif
                        <a href="{{ route('admin.logbooks.index') }}" class="{{ $mLinkBase }} {{ request()->routeIs('admin.logbooks.*') ? $mLinkActive : $mLinkIdle }}">
                            <span>Monitoring Logbook</span>
                        </a>
                        <a href="{{ route('admin.certificates.index') }}" class="{{ $mLinkBase }} {{ request()->routeIs('admin.certificates.*') ? $mLinkActive : $mLinkIdle }}">
                            <span>Terbitkan Sertifikat</span>
                        </a>
                        @if (! $isBidangAdmin)
                            <a href="{{ route('admin.agency_profile.edit') }}" class="{{ $mLinkBase }} {{ request()->routeIs('admin.agency_profile.*') ? $mLinkActive : $mLinkIdle }}">
                                <span>Profil Dinas</span>
                            </a>
                        @endif
                        <a href="{{ route('profile.edit') }}" class="{{ $mLinkBase }} {{ request()->routeIs('profile.edit') ? $mLinkActive : $mLinkIdle }}">
                            <span>Pengaturan Akun</span>
                        </a>

                    @elseif ($isMahasiswa)
                        <a href="{{ route('dashboard') }}" class="{{ $mLinkBase }} {{ request()->routeIs('dashboard') ? $mLinkActive : $mLinkIdle }}">
                            <span>Dashboard Saya</span>
                        </a>
                        <a href="{{ route('student.profile.edit') }}" class="{{ $mLinkBase }} {{ request()->routeIs('student.profile.*') ? $mLinkActive : $mLinkIdle }}">
                            <span>Profil Saya</span>
                        </a>
                        <a href="{{ route('student.application.create') }}" class="{{ $mLinkBase }} {{ request()->routeIs('student.application.*') ? $mLinkActive : $mLinkIdle }}">
                            <span>Pengajuan Magang</span>
                        </a>
                        <a href="{{ route('student.logbook.index') }}" class="{{ $mLinkBase }} {{ request()->routeIs('student.logbook.*') ? $mLinkActive : $mLinkIdle }}">
                            <span>Logbook Magang</span>
                        </a>
                        <a href="{{ route('student.final_report.index') }}" class="{{ $mLinkBase }} {{ request()->routeIs('student.final_report.*') ? $mLinkActive : $mLinkIdle }}">
                            <span>Laporan Akhir</span>
                        </a>
                        <a href="{{ route('profile.edit') }}" class="{{ $mLinkBase }} {{ request()->routeIs('profile.edit') ? $mLinkActive : $mLinkIdle }}">
                            <span>Pengaturan Akun</span>
                        </a>

                    @elseif ($isDosen)
                        <a href="{{ route('lecturer.dashboard') }}" class="{{ $mLinkBase }} {{ request()->routeIs('lecturer.dashboard') ? $mLinkActive : $mLinkIdle }}">
                            <span>Portal Pembimbing</span>
                        </a>
                        <a href="{{ route('lecturer.monitoring.index') }}" class="{{ $mLinkBase }} {{ request()->routeIs('lecturer.monitoring.*') ? $mLinkActive : $mLinkIdle }}">
                            <span>Peserta Bimbingan</span>
                        </a>
                        <a href="{{ route('lecturer.logbooks.index') }}" class="{{ $mLinkBase }} {{ request()->routeIs('lecturer.logbooks.*') ? $mLinkActive : $mLinkIdle }}">
                            <span>Logbook Bimbingan</span>
                        </a>
                        <a href="{{ route('profile.edit') }}" class="{{ $mLinkBase }} {{ request()->routeIs('profile.edit') ? $mLinkActive : $mLinkIdle }}">
                            <span>Pengaturan Akun</span>
                        </a>

                    @elseif ($isMentor)
                        <a href="{{ route('mentor.dashboard') }}" class="flex items-center justify-between gap-3 px-3.5 py-2.5 rounded-xl text-base font-semibold transition min-h-[48px] {{ request()->routeIs('mentor.dashboard', 'mentor.students.*', 'mentor.evaluations.*') ? $mLinkActive : 'text-slate-800 hover:bg-slate-50' }}">
                            <span>Beranda &amp; Tugas</span>
                        </a>
                        <a href="{{ route('mentor.logbooks.index') }}" class="flex items-center justify-between gap-3 px-3.5 py-2.5 rounded-xl text-base font-semibold transition min-h-[48px] {{ request()->routeIs('mentor.logbooks.*') ? $mLinkActive : 'text-slate-800 hover:bg-slate-50' }}">
                            <span>Periksa Logbook</span>
                        </a>
                        <a href="{{ route('profile.edit') }}" class="{{ $mLinkBase }} {{ request()->routeIs('profile.edit') ? $mLinkActive : $mLinkIdle }}">
                            <span>Pengaturan Akun</span>
                        </a>

                    @elseif ($isUniversitas)
                        <a href="{{ route('university.dashboard') }}" class="{{ $mLinkBase }} {{ request()->routeIs('university.dashboard') ? $mLinkActive : $mLinkIdle }}">
                            <span>Portal Kampus/Sekolah</span>
                        </a>
                        <a href="{{ route('university.lecturers.index') }}" class="{{ $mLinkBase }} {{ request()->routeIs('university.lecturers.*') ? $mLinkActive : $mLinkIdle }}">
                            <span>Pembimbing</span>
                        </a>
                        <a href="{{ route('university.profile.index') }}" class="{{ $mLinkBase }} {{ request()->routeIs('university.profile.*') ? $mLinkActive : $mLinkIdle }}">
                            <span>Profil Kampus</span>
                        </a>
                        <a href="{{ route('profile.edit') }}" class="{{ $mLinkBase }} {{ request()->routeIs('profile.edit') ? $mLinkActive : $mLinkIdle }}">
                            <span>Pengaturan Akun</span>
                        </a>
                    @endif
                </div>

                {{-- Komunikasi & Bantuan --}}
                <div class="pt-3 border-t border-slate-100 space-y-1">
                    <p class="px-3.5 pb-1 text-[11px] font-bold uppercase tracking-wider text-slate-400">Komunikasi &amp; Bantuan</p>
                    <a href="{{ route('chat.index') }}" class="{{ $mLinkBase }} {{ request()->routeIs('chat.*') ? $mLinkActive : $mLinkIdle }}">
                        <span class="flex-1">Pesan</span>
                        <span x-show="$store.chat.unread > 0" x-text="$store.chat.label"
                              class="min-w-[20px] h-5 px-1.5 rounded-full bg-rose-500 text-white text-xs font-bold flex items-center justify-center"
                              @if(($chatUnread ?? 0) < 1) style="display: none;" @endif>{{ ($chatUnread ?? 0) > 99 ? '99+' : ($chatUnread ?? 0) }}</span>
                    </a>
                    <a href="{{ route('notifications.index') }}" class="{{ $mLinkBase }} {{ request()->routeIs('notifications.*') ? $mLinkActive : $mLinkIdle }}">
                        <span class="flex-1">Pemberitahuan Sistem</span>
                        @if($navHasUnreadDot ?? false)
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shrink-0"></span>
                        @endif
                    </a>
                    @if($isSuperAdmin || $isAdminDinas)
                        <a href="{{ route('admin.feedbacks.index') }}" class="{{ $mLinkBase }} {{ request()->routeIs('admin.feedbacks.*') ? $mLinkActive : $mLinkIdle }}">
                            <span>Feedback & Masukan</span>
                        </a>
                    @else
                        <a href="{{ route('feedbacks.create') }}" class="{{ $mLinkBase }} {{ request()->routeIs('feedbacks.create') ? $mLinkActive : $mLinkIdle }}">
                            <span>Kirim Laporan Kendala</span>
                        </a>
                        <a href="{{ route('feedbacks.my') }}" class="{{ $mLinkBase }} {{ request()->routeIs('feedbacks.my') ? $mLinkActive : $mLinkIdle }}">
                            <span>Riwayat Masukan Saya</span>
                        </a>
                    @endif
                </div>
                @else
                <div class="p-4 rounded-2xl border border-slate-200/80 bg-slate-50 space-y-3">
                    <div class="flex items-center gap-2.5">
                        <img src="{{ asset('images/logos/surabaya.png') }}" alt="Surabaya" class="h-8 w-auto object-contain">
                        <span class="text-sm font-bold text-slate-800">SIM Magang Surabaya</span>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">Silakan masuk ke akun Anda atau daftar untuk mengakses portal magang Pemerintah Kota Surabaya.</p>
                    <div class="flex flex-col gap-2 pt-1">
                        <a href="{{ url('/') }}" class="btn-ghost w-full bg-slate-100 hover:bg-slate-200">
                            Home
                        </a>
                        <a href="{{ route('login') }}" class="btn-primary w-full">
                            Masuk ke Akun
                        </a>
                        <a href="{{ route('register') }}" class="btn-secondary w-full">
                            Daftar Akun
                        </a>
                    </div>
                </div>
                @endauth
            </div>

            @auth
            {{-- Sticky Footer: Logout Button (>= 44px touch target) --}}
            <div class="p-4 border-t border-slate-100 bg-slate-50/70 shrink-0">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full min-h-[44px] flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200/80 transition cursor-pointer active:scale-[0.98]">
                        <span>Keluar Sistem</span>
                    </button>
                </form>
            </div>
            @endauth
        </aside>
    </div>
</nav>