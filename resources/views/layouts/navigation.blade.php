<nav x-data="{ 
        mobileMenuOpen: false,
        init() {
            this.$watch('mobileMenuOpen', value => {
                if (value) {
                    document.body.classList.add('overflow-hidden');
                } else {
                    document.body.classList.remove('overflow-hidden');
                }
            });
        }
     }" 
     class="border-b border-slate-100 bg-white transition-all shadow-xs relative z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20 items-center">
            
            {{-- 1. BRAND LOGO RESMI PEMKOT SURABAYA --}}
            <div class="flex items-center gap-4 lg:gap-8 shrink-0">
                @php
                    $user = Auth::user();
                    $isSuperAdmin = ($user?->isSuperAdmin() ?? false);
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
                            $navAvatarLogo = $user->agencyProfile->logo_url;
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
                @endphp

                <a href="{{ $dashboardRoute }}" class="flex items-center group py-2">
                    <img src="{{ asset('images/logos/surabaya.png') }}" 
                         alt="Pemerintah Kota Surabaya" 
                         class="h-10 sm:h-11 w-auto object-contain shrink-0 transition-transform group-hover:scale-105"
                         style="height: 42px; width: auto; max-height: 46px; object-fit: contain;">
                </a>

                {{-- 2. DESKTOP NAVIGATION BAR --}}
                <div class="hidden md:flex items-center space-x-3 lg:space-x-5 xl:space-x-7 text-xs lg:text-sm font-semibold text-slate-600">

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

                    @elseif ($isMentor)
                        <a href="{{ route('mentor.dashboard') }}" 
                           class="transition py-1 {{ request()->routeIs('mentor.dashboard') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Portal Mentor
                        </a>
                        <a href="{{ route('mentor.logbooks.index') }}" 
                           class="transition py-1 {{ request()->routeIs('mentor.logbooks.*') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-slate-600 hover:text-blue-600' }}">
                            Review Logbook
                        </a>

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

            {{-- 3. RIGHT SECTION: NOTIFICATION & USER PROFILE (DESKTOP) --}}
            <div class="hidden md:flex items-center gap-3">

                @php
                    $navHasUnreadDot = Auth::user() ? \App\Services\NotificationService::hasUnreadDot(Auth::user()) : false;
                    $navAllNotifs = Auth::user() ? \App\Services\NotificationService::getNotificationsForUser(Auth::user()) : [];
                    // Pesan chat belum dibaca didahulukan agar tidak tenggelam di bawah pemberitahuan otomatis lain
                    $navIsUnreadChat = fn ($n) => ($n['category'] ?? '') === 'chat' && empty($n['is_read']);
                    $navQuickNotifs = array_slice(array_merge(
                        array_values(array_filter($navAllNotifs, $navIsUnreadChat)),
                        array_values(array_filter($navAllNotifs, fn ($n) => !$navIsUnreadChat($n)))
                    ), 0, 4);
                    $navChatUnread = (int) ($chatUnread ?? 0);
                    $navChatLabel = $navChatUnread > 99 ? '99+' : (string) $navChatUnread;
                @endphp
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
                         class="absolute right-0 top-full mt-2 w-80 sm:w-96 rounded-3xl p-3 space-y-2 border border-slate-100 bg-white shadow-2xl z-50">
                        
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
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Pengaturan Akun
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
                                Portal Dosen
                            </a>
                            <a href="{{ route('lecturer.monitoring.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Mahasiswa Bimbingan
                            </a>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Pengaturan Akun
                            </a>
                        @elseif ($isMentor)
                            <a href="{{ route('mentor.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Portal Mentor
                            </a>
                            <a href="{{ route('mentor.logbooks.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Review Logbook
                            </a>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Pengaturan Akun
                            </a>
                        @elseif ($isUniversitas)
                            <a href="{{ route('university.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Portal Universitas
                            </a>
                            <a href="{{ route('university.lecturers.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                Dosen Pembimbing
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

            {{-- 4. MOBILE MENU TOGGLE BUTTON --}}
            <div class="flex items-center gap-1 md:hidden">
                <a href="{{ route('chat.index') }}" class="relative p-2 rounded-xl text-slate-600 hover:bg-slate-100 transition" aria-label="Pesan">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    <span x-show="$store.chat.unread > 0" x-text="$store.chat.label" data-chat-badge
                          class="absolute top-0.5 right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-500 text-white text-[10px] font-bold leading-[18px] text-center ring-2 ring-white"
                          @if(($chatUnread ?? 0) < 1) style="display: none;" @endif>{{ ($chatUnread ?? 0) > 99 ? '99+' : ($chatUnread ?? 0) }}</span>
                </a>
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

    {{-- 5. FLOATING MOBILE MENU WITH FULL-SCREEN BACKDROP OVERLAY --}}
    <div x-show="mobileMenuOpen" 
         x-cloak 
         x-init="$watch('mobileMenuOpen', value => {
             if (value) {
                 document.documentElement.classList.add('overflow-hidden');
                 document.body.classList.add('overflow-hidden', 'touch-none');
             } else {
                 document.documentElement.classList.remove('overflow-hidden');
                 document.body.classList.remove('overflow-hidden', 'touch-none');
             }
         })"
         class="md:hidden relative z-50">

        {{-- Backdrop Gelap (Mencegah scroll di background layar) --}}
        <div x-show="mobileMenuOpen"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="mobileMenuOpen = false"
             @touchmove.prevent
             @wheel.prevent
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"></div>

        {{-- Panel Menu Mobile (Posisi dinaikkan ke top-14, menempel rapi di bawah navbar) --}}
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2 scale-98"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 -translate-y-2 scale-98"
             class="fixed inset-x-3 sm:inset-x-4 top-14 max-h-[calc(100vh-4.5rem)] overflow-y-auto bg-white rounded-3xl p-4 sm:p-5 shadow-2xl border border-slate-200 space-y-3 overscroll-contain">

            {{-- Profil Mahasiswa / User Header --}}
            <div class="p-3 rounded-2xl border border-slate-100 flex items-center justify-between bg-slate-50">
                <div class="flex items-center gap-2.5">
                    @if($navAvatarLogo)
                        <div class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center shrink-0 overflow-hidden shadow-2xs">
                            <img src="{{ $navAvatarLogo }}" alt="Logo" class="w-full h-full object-contain p-1">
                        </div>
                    @else
                        <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xs font-bold shrink-0">
                            {{ $navAvatarInitials }}
                        </div>
                    @endif
                    <div>
                        <div class="text-xs font-bold text-slate-800 leading-tight">{{ Auth::user()->name ?? 'Pengguna' }}</div>
                        <div class="text-[10px] font-semibold text-blue-600 uppercase">{{ strtoupper(str_replace('_', ' ', Auth::user()->role ?? 'Role')) }}</div>
                    </div>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">Online</span>
            </div>

            {{-- Daftar Navigasi Menu Mobile --}}
            <div class="space-y-1">
                @if ($isSuperAdmin)
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        </div>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('admin.applications.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('admin.applications.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('admin.applications.*') ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <span>Pengajuan</span>
                    </a>
                    <a href="{{ route('admin.agencies.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('admin.agencies.*') || request()->routeIs('admin.units.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('admin.agencies.*') || request()->routeIs('admin.units.*') ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <span>Instansi Dinas</span>
                    </a>
                    <a href="{{ route('admin.universities.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('admin.universities.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('admin.universities.*') ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                        </div>
                        <span>Perguruan Tinggi</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('admin.users.*') ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <span>Semua Pengguna</span>
                    </a>
                    <a href="{{ route('admin.mentors.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('admin.mentors.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('admin.mentors.*') ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <span>Mentor Lapangan</span>
                    </a>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('profile.edit') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        <span>Pengaturan Akun</span>
                    </a>

                @elseif ($isAdminDinas)
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        </div>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('admin.applications.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('admin.applications.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('admin.applications.*') ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <span>Verifikasi Pengajuan</span>
                    </a>
                    <a href="{{ route('admin.units.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('admin.units.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('admin.units.*') ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        </div>
                        <span>Divisi & Kuota Unit</span>
                    </a>
                    <a href="{{ route('admin.mentors.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('admin.mentors.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('admin.mentors.*') ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <span>Mentor Lapangan</span>
                    </a>
                    <a href="{{ route('admin.logbooks.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('admin.logbooks.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('admin.logbooks.*') ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        <span>Monitoring Logbook</span>
                    </a>
                    <a href="{{ route('admin.certificates.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('admin.certificates.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('admin.certificates.*') ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                        </div>
                        <span>Terbitkan Sertifikat</span>
                    </a>
                    <a href="{{ route('admin.agency_profile.edit') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('admin.agency_profile.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('admin.agency_profile.*') ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <span>Profil Dinas</span>
                    </a>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('profile.edit') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        <span>Pengaturan Akun</span>
                    </a>

                @elseif ($isMahasiswa)
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    
                        <span>Dashboard Saya</span>
                    </a>
                    <a href="{{ route('student.profile.edit') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('student.profile.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        
                        <span>Profil Saya</span>
                    </a>
                    <a href="{{ route('student.application.create') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('student.application.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        
                        <span>Pengajuan Magang</span>
                    </a>
                    <a href="{{ route('student.logbook.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('student.logbook.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        
                        <span>Logbook Magang</span>
                    </a>
                    <a href="{{ route('student.final_report.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('student.final_report.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                       
                        <span>Laporan Akhir</span>
                    </a>

                @elseif ($isDosen)
                    <a href="{{ route('lecturer.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('lecturer.dashboard') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        
                        <span>Portal Dosen</span>
                    </a>
                    <a href="{{ route('lecturer.monitoring.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('lecturer.monitoring.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        
                        <span>Mahasiswa Bimbingan</span>
                    </a>
                    <a href="{{ route('lecturer.logbooks.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('lecturer.logbooks.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        
                        <span>Logbook Bimbingan</span>
                    </a>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('profile.edit') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        <span>Pengaturan Akun</span>
                    </a>

                @elseif ($isMentor)
                    <a href="{{ route('mentor.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('mentor.dashboard') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        
                        <span>Portal Mentor</span>
                    </a>
                    <a href="{{ route('mentor.logbooks.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('mentor.logbooks.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        
                        <span>Review Logbook</span>
                    </a>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('profile.edit') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        <span>Pengaturan Akun</span>
                    </a>

                @elseif ($isUniversitas)
                    <a href="{{ route('university.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('university.dashboard') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        
                        <span>Portal Universitas</span>
                    </a>
                    <a href="{{ route('university.lecturers.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('university.lecturers.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        
                        <span>Dosen Pembimbing</span>
                    </a>
                    <a href="{{ route('university.profile.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('university.profile.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        
                        <span>Profil Kampus</span>
                    </a>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold transition {{ request()->routeIs('profile.edit') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        <span>Pengaturan Akun</span>
                    </a>
                @endif
            </div>

            {{-- Bantuan & Masukan --}}
            <div class="pt-2 border-t border-slate-100 space-y-1">
                <a href="{{ route('chat.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('chat.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('chat.*') ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    </div>
                    <span class="flex-1">Pesan</span>
                    <span x-show="$store.chat.unread > 0" x-text="$store.chat.label"
                          class="min-w-[20px] h-5 px-1.5 rounded-full bg-rose-500 text-white text-xs font-bold flex items-center justify-center"
                          @if(($chatUnread ?? 0) < 1) style="display: none;" @endif>{{ ($chatUnread ?? 0) > 99 ? '99+' : ($chatUnread ?? 0) }}</span>
                </a>
                <a href="{{ route('notifications.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('notifications.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                    
                    <span>Pemberitahuan Sistem</span>
                </a>
                @if($isSuperAdmin || $isAdminDinas)
                    <a href="{{ route('admin.feedbacks.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('admin.feedbacks.*') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                       
                        <span>Feedback & Masukan</span>
                    </a>
                @else
                    <a href="{{ route('feedbacks.create') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('feedbacks.create') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                       
                        <span>Kirim Laporan Kendala</span>
                    </a>
                    <a href="{{ route('feedbacks.my') }}" class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition min-h-[40px] {{ request()->routeIs('feedbacks.my') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                        
                        <span>Riwayat Masukan Saya</span>
                    </a>
                @endif
            </div>

            {{-- Logout Button --}}
            <div class="pt-2 border-t border-slate-100">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 transition cursor-pointer active:scale-[0.99] min-h-[40px]">
                         <span>Keluar Sistem</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>