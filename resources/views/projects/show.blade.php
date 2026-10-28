<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ $backUrl }}" 
                   class="p-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition shadow-2xs" 
                   title="Kembali ke Halaman Sebelumnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200 uppercase tracking-wider">
                            Karya Inovasi Magang
                        </span>
                        <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold border {{ $project->category_badge_class }}">
                            {{ $project->category_label }}
                        </span>
                        <span class="text-xs text-slate-400">&bull;</span>
                        <span class="text-xs text-slate-500 font-medium">
                            {{ $placement->application?->unit?->name ?? 'Dinas Perpustakaan dan Kearsipan' }}
                        </span>
                    </div>
                    <h2 class="font-bold text-xl sm:text-2xl text-slate-900 leading-tight mt-1">
                        {{ $project->title }}
                    </h2>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @php
                    $isManagerRole = Auth::check() && (Auth::user()->isSuperAdmin() || in_array(Auth::user()->role, ['super_admin', 'superadmin', 'supervisor']));
                @endphp

                @if($isManagerRole)
                    {{-- KHUSUS SUPER ADMIN & SUPERVISOR: Tombol 'Kelola Proyek' Elegan dengan Dropdown Lengkap --}}
                    <div x-data="{ menuOpen: false }" class="relative" @click.outside="menuOpen = false">
                        <button @click="menuOpen = !menuOpen" 
                                type="button" 
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 transition cursor-pointer active:scale-95"
                                title="Kelola dan Moderasi Proyek Inovasi">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Kelola Proyek</span>
                            <svg class="w-3.5 h-3.5 text-blue-200 transition-transform duration-200" :class="{ 'rotate-180': menuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div x-show="menuOpen" 
                             x-cloak 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 top-full mt-2 w-64 bg-white rounded-2xl shadow-2xl border border-slate-200 py-1.5 z-50 text-xs divide-y divide-slate-100">
                            
                            {{-- 1. Pilihan Akses Tautan / Aplikasi Proyek (Jika Tersedia) --}}
                            @if($project->demo_url)
                                <div class="py-1">
                                    <a href="{{ $project->demo_url }}" target="_blank" rel="noopener noreferrer" 
                                       class="w-full text-left px-4 py-2.5 hover:bg-blue-50 flex items-center gap-2.5 text-blue-700 font-bold transition">
                                        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        <span class="truncate">Akses Tautan / Aplikasi Proyek</span>
                                    </a>
                                </div>
                            @endif

                            {{-- 2. Sembunyikan / Publikasikan Proyek --}}
                            <div class="py-1">
                                <form action="{{ route('projects.toggle_visibility', $project->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2.5 hover:bg-slate-50 flex items-center gap-2.5 text-slate-700 font-semibold cursor-pointer transition">
                                        @if($project->is_public)
                                            <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                            <div>
                                                <div class="font-bold text-slate-800">Sembunyikan Proyek</div>
                                                <div class="text-[10px] text-slate-400">Jadikan privat (hanya pengelola)</div>
                                            </div>
                                        @else
                                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <div>
                                                <div class="font-bold text-slate-800">Tampilkan ke Publik</div>
                                                <div class="text-[10px] text-slate-400">Dapat dilihat semua pengunjung</div>
                                            </div>
                                        @endif
                                    </button>
                                </form>
                            </div>

                            {{-- 3. Salin Tautan --}}
                            <div class="py-1">
                                <button type="button" 
                                        @click="navigator.clipboard.writeText(window.location.href); menuOpen = false; alert('Tautan proyek berhasil disalin!')" 
                                        class="w-full text-left px-4 py-2.5 hover:bg-slate-50 flex items-center gap-2.5 text-slate-700 font-semibold cursor-pointer transition">
                                    <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    <div>
                                        <div class="font-bold text-slate-800">Salin Tautan Proyek</div>
                                        <div class="text-[10px] text-slate-400">Salin link halaman ini</div>
                                    </div>
                                </button>
                            </div>

                            @if(Auth::user()->isSuperAdmin() || in_array(Auth::user()->role, ['super_admin', 'superadmin']))
                                {{-- 4. Hapus Proyek (Khusus Super Admin) --}}
                                <div class="py-1">
                                    <form action="{{ route('projects.destroy', $project->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus proyek inovasi ini secara permanen?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full text-left px-4 py-2.5 hover:bg-rose-50 flex items-center gap-2.5 text-rose-600 font-semibold cursor-pointer transition">
                                            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <div>
                                                <div class="font-bold text-rose-600">Hapus Proyek</div>
                                                <div class="text-[10px] text-rose-400">Hapus karya ini permanen</div>
                                            </div>
                                        </button>
                                    </form>
                                </div>
                            @endif

                        </div>
                    </div>
                @else
                    {{-- UNTUK USER LAIN (Mahasiswa, Dosen, Tamu / Publik): Tampilkan tombol Akses Tautan biasa jika tersedia demo_url --}}
                    @if($project->demo_url)
                        <a href="{{ $project->demo_url }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-xs transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            <span>Akses Tautan / Aplikasi Proyek</span>
                        </a>
                    @endif
                @endif
            </div>
        </div>
    </x-slot>

    @php
        $allScreenshots = [];
        if ($project->photo_url) {
            $allScreenshots[] = [
                'title' => 'Foto Sampul Utama',
                'url' => $project->photo_url,
            ];
        }
        foreach ($project->screenshot_urls as $idx => $sUrl) {
            $allScreenshots[] = [
                'title' => 'Tangkapan Layar ' . ($idx + 1),
                'url' => $sUrl,
            ];
        }
        $communityPhotoUrls = $project->community_photo_urls;
        $documents = is_array($project->documents) ? $project->documents : [];
    @endphp

    <div class="py-8" x-data="{
        activePhoto: @js($allScreenshots[0]['url'] ?? null),
        activeTitle: @js($allScreenshots[0]['title'] ?? 'Dokumentasi Proyek'),
        isLiked: @js($isLikedByMe),
        likesCount: {{ (int) $project->likes_count }},
        commentsCount: {{ (int) $project->comments_count }},
        likeLoading: false,
        shareToast: false,
        profanityAlert: '',
        commentInput: '',
        authorNameInput: '',

        async toggleProjectLike() {
            if (this.likeLoading) return;
            this.likeLoading = true;
            try {
                const resp = await fetch('{{ route('projects.like', $project->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await resp.json();
                if (data.success) {
                    this.isLiked = data.liked;
                    this.likesCount = data.likes_count;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.likeLoading = false;
            }
        },

        copyShareLink() {
            navigator.clipboard.writeText(window.location.href);
            this.shareToast = true;
            setTimeout(() => this.shareToast = false, 3000);
        },

        checkProfanity(text) {
            const badWords = [
                // Kata Kasar Jawa (Surabaya / Jatim & Jateng)
                'jancok','jancuk','jancik','dancok','dancuk','damput','jancukan',
                'ancok','ancuk','cok','cuk','coeg','taek','telek','tai','taik','silit',
                'matamu','ndasmu','cocotmu','lambemu','modar','peli','gathel','gapleki','tempik','peler',
                'kenthu','kirik',
                // Organ intim & seksual eksplisit
                'kontol','kntl','memek','mmk','jembut','jmbt','ngentot','ngewe','ewe','pantek',
                'puki','pukimak','pepek','itil','tetek','titit','bocong','dildo','masturbasi',
                'porno','bokep','vagina','penis','seks',
                // Umpatan kasar / peyoratif Indonesia
                'anjing','anjir','anjrit','babi','bangsat','bajingan','brengsek','kampret','asu',
                'lonte','perek','jablay','goblok','goblog','tolol','idiot','bego','dungu','pekok','geblek',
                // Slang & English profanity
                'fuck','fucking','fucked','fucker','motherfucker','mf',
                'shit','bullshit','bitch','bitches','bastard','asshole','cunt',
                'dick','dicks','cock','pussy','whore','slut','prick','retard','nigger','fag','faggot'
            ];
            const lower = text.toLowerCase();
            
            // 1. Cek batas kata utuh
            for (const w of badWords) {
                const regex = new RegExp('\\b' + w + '\\b', 'i');
                if (regex.test(lower)) {
                    return w;
                }
            }

            // 2. Cek stripped evasion (contoh: "j.a.n.c.o.k", "a_s_u")
            const stripped = lower.replace(/[^a-z0-9]/g, '');
            for (const w of ['jancok','jancuk','dancok','kontol','memek','ngentot','fuck','bitch','asu']) {
                if (stripped.includes(w)) {
                    return w;
                }
            }

            return '';
        },

        validateCommentLive() {
            const detected = this.checkProfanity(this.commentInput + ' ' + this.authorNameInput);
            if (detected) {
                this.profanityAlert = 'Terdeteksi kata tidak pantas (\x22' + detected + '\x22). Mohon gunakan bahasa yang sopan dan santun.';
            } else {
                this.profanityAlert = '';
            }
        }
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <!-- 1. Banner Ringkasan Identitas Mahasiswa & Penugasan -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-slate-400 font-semibold uppercase text-[10px] block mb-1">Mahasiswa Pengembang</span>
                        <span class="font-bold text-slate-900 text-sm block">{{ $student->name ?? '-' }}</span>
                        <span class="text-slate-500 text-[11px] font-mono block mt-0.5">NIM: {{ $student->studentProfile?->nim ?? '-' }}</span>
                    </div>

                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-slate-400 font-semibold uppercase text-[10px] block mb-1">Asal Perguruan Tinggi</span>
                        <span class="font-bold text-slate-800 text-sm block">{{ $student->studentProfile?->universitas ?? $student->university ?? '-' }}</span>
                        <span class="text-slate-500 text-[11px] block mt-0.5">{{ $student->studentProfile?->jurusan ?? '-' }}</span>
                    </div>

                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-slate-400 font-semibold uppercase text-[10px] block mb-1">Penempatan Unit Kerja Dinas</span>
                        <span class="font-bold text-slate-800 text-sm block">{{ $placement->application?->unit?->agencyProfile?->agency_name ?? 'Dispusip Surabaya' }}</span>
                        <span class="text-slate-500 text-[11px] block mt-0.5">{{ $placement->application?->unit?->name ?? 'Bidang Kerja' }}</span>
                    </div>

                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-slate-400 font-semibold uppercase text-[10px] block mb-1">Mentor Lapangan (Dinas)</span>
                        <span class="font-bold text-slate-800 text-sm block">{{ $placement->mentor?->name ?? $placement->pembimbing?->name ?? 'Belum Ditugaskan' }}</span>
                        <span class="text-slate-500 text-[11px] block mt-0.5">Dosen: {{ $placement->academicAdvisor?->name ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- 2. Galeri Tangkapan Layar & Mockup Proyek (Multi-Screenshot Showcase) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="font-bold text-base sm:text-lg text-slate-900">
                            Galeri Tangkapan Layar & Dokumentasi Visual Proyek
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Koleksi antarmuka aplikasi, alur kerja sistem, dan hasil visual yang dirancang mahasiswa
                        </p>
                    </div>
                    @if(count($allScreenshots) > 1)
                        <span class="px-3 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-full border border-blue-200">
                            {{ count($allScreenshots) }} Gambar Tersedia
                        </span>
                    @endif
                </div>

                @if(!empty($allScreenshots))
                    <!-- Display Layar Utama (Viewport Penuh, Anti-Pecah & Proporsional) -->
                    <div class="space-y-3">
                        <div class="w-full rounded-2xl bg-slate-950 border border-slate-800 overflow-hidden flex items-center justify-center relative p-3 sm:p-4 min-h-[280px] max-h-[560px]">
                            <img :src="activePhoto" :alt="activeTitle" class="max-h-[520px] w-auto max-w-full object-contain rounded-xl shadow-lg transition-all duration-300">
                            
                            <!-- Overlay Tombol Buka Tab Baru -->
                            <a :href="activePhoto" target="_blank" rel="noopener noreferrer" 
                               class="absolute bottom-4 right-4 px-3.5 py-2 bg-slate-900/80 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl backdrop-blur-md transition shadow-md flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                <span>Buka Resolusi Penuh</span>
                            </a>

                            <div class="absolute top-4 left-4 px-3 py-1 bg-black/60 text-white text-[11px] font-bold rounded-lg backdrop-blur-xs" x-text="activeTitle"></div>
                        </div>

                        <!-- Thumbnail Strip (Bila Terdapat Lebih Dari 1 Gambar) -->
                        @if(count($allScreenshots) > 1)
                            <div class="pt-2">
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Pilih Tangkapan Layar:</span>
                                <div class="flex items-center gap-3 overflow-x-auto pb-2 scrollbar-thin">
                                    @foreach($allScreenshots as $idx => $item)
                                        <button type="button" 
                                                @click="activePhoto = '{{ $item['url'] }}'; activeTitle = '{{ $item['title'] }}'"
                                                class="relative shrink-0 w-28 sm:w-36 aspect-video rounded-xl overflow-hidden border-2 transition cursor-pointer"
                                                :class="activePhoto === '{{ $item['url'] }}' ? 'border-blue-600 ring-2 ring-blue-500/30' : 'border-slate-200 opacity-70 hover:opacity-100 hover:border-slate-400'">
                                            <img src="{{ $item['url'] }}" alt="{{ $item['title'] }}" class="w-full h-full object-cover">
                                            <span class="absolute bottom-0 inset-x-0 bg-slate-900/80 text-[10px] text-white text-center py-0.5 truncate px-1 font-medium">
                                                {{ $item['title'] }}
                                            </span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="p-8 bg-slate-50 rounded-2xl text-center text-slate-500 text-xs">
                        Belum ada berkas tangkapan layar proyek yang diunggah.
                    </div>
                @endif
            </div>

            <!-- 3. Rincian Deskripsi Proyek -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-4">
                <div class="pb-3 border-b border-slate-100">
                    <h3 class="font-bold text-base sm:text-lg text-slate-900">
                        Deskripsi & Latar Belakang Inovasi
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Tujuan pembuatan, ruang lingkup teknologi, dan kontribusi mahasiswa selama masa penempatan
                    </p>
                </div>
                <div class="text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line space-y-2">
                    {{ $project->description }}
                </div>
            </div>

            <!-- 4. PUSAT AKSES & BERKAS PROYEK (SPACE BESAR KHUSUS AKSES PROYEK) -->
            <div class="bg-gradient-to-br from-blue-50/80 via-white to-indigo-50/40 rounded-2xl border-2 border-blue-200/90 p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-blue-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-blue-600 text-white uppercase tracking-wider">
                                Pusat Akses Proyek
                            </span>
                            <h3 class="font-bold text-lg sm:text-xl text-slate-900">
                                Akses Sistem, Repositori & Berkas Inovasi
                            </h3>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-600 mt-1">
                            Tautan langsung untuk mencoba sistem, memeriksa repositori kode sumber, atau mengunduh paket berkas aplikasi
                        </p>
                    </div>
                </div>

                <!-- Grid Kartu Akses Proyek (Spacious, Clean, High Contrast) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-5">
                    <!-- 1. Website / Live Demo -->
                    <div class="bg-white rounded-2xl border border-blue-200/80 p-5 shadow-2xs hover:shadow-md transition flex flex-col justify-between space-y-4">
                        <div class="space-y-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 block">Akses Aplikasi</span>
                                <h4 class="font-bold text-sm sm:text-base text-slate-900 mt-0.5">Website / Live Demo</h4>
                                <p class="text-xs text-slate-500 mt-1 line-clamp-2">
                                    {{ $project->demo_url ? 'Kunjungi sistem aplikasi daring yang sedang beroperasi.' : 'Tautan demo daring belum dilampirkan.' }}
                                </p>
                            </div>
                        </div>

                        @if($project->demo_url)
                            <a href="{{ $project->demo_url }}" target="_blank" rel="noopener noreferrer" 
                               class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                                <span>Buka Website Sistem</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        @else
                            <button type="button" disabled class="w-full px-4 py-2.5 bg-slate-100 text-slate-400 text-xs font-semibold rounded-xl cursor-not-allowed">
                                Tautan Tidak Tersedia
                            </button>
                        @endif
                    </div>

                    <!-- 2. Repositori / Cloud Storage -->
                    @php
                        $isGithub = $project->repository_url && (str_contains($project->repository_url, 'github.com') || str_contains($project->repository_url, 'gitlab.com'));
                        $isDrive = $project->repository_url && str_contains($project->repository_url, 'drive.google.com');
                        $isBehance = $project->repository_url && str_contains($project->repository_url, 'behance.net');
                        
                        $repoCategory = $isGithub ? 'Kode Sumber' : ($isDrive ? 'Penyimpanan Awan' : ($isBehance ? 'Portofolio Kreatif' : 'Tautan Sumber'));
                        $repoTitle = $isGithub ? 'Repositori GitHub / Git' : ($isDrive ? 'Folder Google Drive' : ($isBehance ? 'Portofolio Desain' : 'Repositori / Berkas Awan'));
                        $repoButton = $isGithub ? 'Buka Repositori GitHub' : ($isDrive ? 'Buka Google Drive Proyek' : ($isBehance ? 'Buka Portofolio Desain' : 'Buka Tautan Sumber'));
                        $repoDesc = $project->repository_url 
                            ? ($isGithub ? 'Inspeksi arsitektur kode sumber, commit, dan implementasi teknis.' : ($isDrive ? 'Akses folder Google Drive naskah, berkas master, atau materi proyek.' : 'Akses tautan materi daring karya inovasi mahasiswa.'))
                            : 'Tautan repositori atau berkas daring belum dilampirkan.';
                    @endphp
                    <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs hover:shadow-md transition flex flex-col justify-between space-y-4">
                        <div class="space-y-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center">
                                @if($isGithub)
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
                                @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                @endif
                            </div>
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-600 block">{{ $repoCategory }}</span>
                                <h4 class="font-bold text-sm sm:text-base text-slate-900 mt-0.5">{{ $repoTitle }}</h4>
                                <p class="text-xs text-slate-500 mt-1 line-clamp-2">
                                    {{ $repoDesc }}
                                </p>
                            </div>
                        </div>

                        @if($project->repository_url)
                            <a href="{{ $project->repository_url }}" target="_blank" rel="noopener noreferrer" 
                               class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-xs transition">
                                <span>{{ $repoButton }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        @else
                            <button type="button" disabled class="w-full px-4 py-2.5 bg-slate-100 text-slate-400 text-xs font-semibold rounded-xl cursor-not-allowed">
                                Repositori Belum Ada
                            </button>
                        @endif
                    </div>

                    <!-- 3. Paket Berkas Sumber ZIP -->
                    <div class="bg-white rounded-2xl border border-amber-200/90 p-5 shadow-2xs hover:shadow-md transition flex flex-col justify-between space-y-4">
                        <div class="space-y-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 block">Paket Arsip</span>
                                <h4 class="font-bold text-sm sm:text-base text-slate-900 mt-0.5">Berkas ZIP / Web Proyek</h4>
                                <p class="text-xs text-slate-500 mt-1 line-clamp-2">
                                    @if($project->source_code_path)
                                        {{ $project->source_code_name ?? 'Berkas_Proyek.zip' }} ({{ $project->source_code_size ?? 'Tersedia' }})
                                    @else
                                        Arsip berkas ZIP / website mandiri belum diunggah.
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if($project->source_code_path)
                            <a href="{{ asset('storage/' . $project->source_code_path) }}" download 
                               class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                                <span>Unduh Berkas ZIP Proyek</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            </a>
                        @else
                            <button type="button" disabled class="w-full px-4 py-2.5 bg-slate-100 text-slate-400 text-xs font-semibold rounded-xl cursor-not-allowed">
                                Berkas ZIP Belum Ada
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 4. Bagian Khusus: Manfaat untuk Masyarakat & Dokumentasi Kegiatan Lapangan -->
            <div class="bg-gradient-to-br from-emerald-50/60 via-white to-teal-50/30 rounded-2xl border border-emerald-200/80 p-6 sm:p-7 shadow-xs space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-emerald-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-600 text-white uppercase tracking-wider">
                                Dampak Sosial
                            </span>
                            <h3 class="font-bold text-base sm:text-lg text-emerald-950">
                                Manfaat Nyata untuk Masyarakat
                            </h3>
                        </div>
                        <p class="text-xs text-emerald-800 mt-1">
                            Dampak positif yang dirasakan langsung oleh masyarakat, warga binaan, atau pengguna layanan dinas
                        </p>
                    </div>
                </div>

                <!-- Narasi Manfaat Nyata -->
                <div class="p-5 bg-white rounded-2xl border border-emerald-100 shadow-2xs space-y-2">
                    <span class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider block">
                        Uraian Manfaat & Kontribusi Langsung:
                    </span>
                    <div class="text-xs sm:text-sm text-slate-800 leading-relaxed font-medium whitespace-pre-line">
                        {{ $project->community_benefit }}
                    </div>
                </div>

                <!-- Sub-bagian 4A: Foto Dokumentasi Sosialisasi Lapangan / Desa -->
                @if(!empty($communityPhotoUrls))
                    <div class="space-y-3 pt-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-emerald-900 uppercase tracking-wider block">
                                Foto Dokumentasi Sosialisasi / Presentasi Lapangan ({{ count($communityPhotoUrls) }} Foto)
                            </span>
                            <span class="text-[11px] text-slate-500">Klik untuk memperbesar gambar</span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
                            @foreach($communityPhotoUrls as $cIdx => $cUrl)
                                <a href="{{ $cUrl }}" target="_blank" rel="noopener noreferrer" 
                                   class="group relative aspect-video rounded-xl overflow-hidden bg-slate-900 border border-slate-200 shadow-2xs hover:shadow-md transition">
                                    <img src="{{ $cUrl }}" alt="Dokumentasi Masyarakat {{ $cIdx + 1 }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-semibold gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        <span>Perbesar</span>
                                    </div>
                                    <span class="absolute bottom-1.5 left-2 px-2 py-0.5 bg-black/60 text-white rounded text-[10px] font-medium">
                                        Dokumentasi {{ $cIdx + 1 }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Sub-bagian 4B: Video Dokumentasi (YouTube / Drive Embed Player) -->
                @if($project->video_url)
                    <div class="space-y-3 pt-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-emerald-900 uppercase tracking-wider block">
                                Video Dokumentasi Kegiatan / Presentasi
                            </span>
                            <a href="{{ $project->video_url }}" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-blue-600 hover:text-blue-800 underline flex items-center gap-1">
                                <span>Buka Tautan Sumber</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>

                        @if($project->youtube_embed_url)
                            <!-- Pemutar YouTube Responsif 16:9 -->
                            <div class="w-full rounded-2xl overflow-hidden bg-black aspect-video shadow-md border border-slate-200">
                                <iframe class="w-full h-full" 
                                        src="{{ $project->youtube_embed_url }}" 
                                        title="Video Dokumentasi Proyek" 
                                        frameborder="0" 
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                        allowfullscreen>
                                </iframe>
                            </div>
                        @else
                            <!-- Tautan Video Non-YouTube (Google Drive, dll) -->
                            <div class="p-5 bg-white rounded-2xl border border-slate-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm text-slate-900">Rekaman Video Dokumentasi</h4>
                                        <p class="text-xs text-slate-500 font-mono break-all mt-0.5">{{ $project->video_url }}</p>
                                    </div>
                                </div>
                                <a href="{{ $project->video_url }}" target="_blank" rel="noopener noreferrer" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs shrink-0 text-center">
                                    Tonton Video di Tab Baru
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- 5. Dokumen Pendukung & Buku Panduan Teknis (PDF / Manual Book) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-4">
                <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-base sm:text-lg text-slate-900">
                            Dokumen Pendukung & Berkas Lampiran Proyek
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Buku panduan (*manual book*), laporan teknis, atau proposal luaran magang mahasiswa
                        </p>
                    </div>
                    @if(!empty($documents))
                        <span class="px-3 py-1 bg-slate-100 text-slate-700 text-xs font-bold rounded-full">
                            {{ count($documents) }} Berkas
                        </span>
                    @endif
                </div>

                @if(!empty($documents))
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                        @foreach($documents as $doc)
                            @php
                                $docName = is_array($doc) ? ($doc['name'] ?? 'Dokumen Proyek.pdf') : basename($doc);
                                $docPath = is_array($doc) ? ($doc['path'] ?? $doc) : $doc;
                                $docSize = is_array($doc) ? ($doc['size'] ?? null) : null;
                                $docExt = strtoupper(pathinfo($docName, PATHINFO_EXTENSION) ?: 'PDF');
                            @endphp
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 hover:border-blue-300 transition flex items-start justify-between gap-3 shadow-2xs">
                                <div class="flex items-start gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-lg bg-red-100 text-red-700 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                                        {{ $docExt }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-xs text-slate-800 truncate" title="{{ $docName }}">
                                            {{ $docName }}
                                        </div>
                                        @if($docSize)
                                            <span class="text-[10px] text-slate-500 font-mono block mt-0.5">{{ $docSize }}</span>
                                        @endif
                                    </div>
                                </div>
                                <a href="{{ asset('storage/' . $docPath) }}" target="_blank" download="{{ $docName }}"
                                   class="p-2 rounded-lg bg-white border border-slate-200 text-blue-600 hover:bg-blue-50 transition shrink-0 shadow-2xs"
                                   title="Unduh Berkas">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 bg-slate-50 rounded-xl text-center text-slate-400 text-xs">
                        Tidak ada dokumen pendukung atau buku petunjuk yang dilampirkan pada proyek ini.
                    </div>
                @endif
            </div>

            {{-- 4. INTERAKSI PROYEK: LIKE & SHARE RESMI --}}
            <div class="bg-white rounded-3xl border border-slate-200 p-5 sm:p-6 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    {{-- Tombol Suka (Like) --}}
                    <button type="button" 
                            @click="toggleProjectLike()" 
                            :disabled="likeLoading"
                            class="inline-flex items-center justify-center gap-2.5 px-5 py-3 rounded-2xl font-bold text-xs sm:text-sm transition-all duration-200 cursor-pointer shadow-2xs active:scale-95 border"
                            :class="isLiked ? 'bg-rose-50 text-rose-600 border-rose-200 hover:bg-rose-100' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'">
                        <svg class="w-5 h-5 transition-transform" :class="isLiked ? 'fill-rose-500 text-rose-500 scale-110' : 'text-slate-400 group-hover:text-rose-500'" viewBox="0 0 24 24">
                            <path :fill="isLiked ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                        <span x-text="isLiked ? 'Disukai' : 'Suka Proyek Ini'"></span>
                        <span class="px-2 py-0.5 rounded-full text-xs" :class="isLiked ? 'bg-rose-100 text-rose-700' : 'bg-white text-slate-600 border border-slate-200'" x-text="likesCount"></span>
                    </button>

                    <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>

                    {{-- Indikator Jumlah Diskusi --}}
                    <div class="hidden sm:flex items-center gap-2 text-xs font-semibold text-slate-500">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        <span>{{ $project->comments->count() }} Komentar Masukan</span>
                    </div>
                </div>

                {{-- Opsi Bagikan (Share) --}}
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end relative" x-data="{ shareDropdown: false }">
                    <button type="button" 
                            @click="shareDropdown = !shareDropdown"
                            class="inline-flex items-center gap-2 px-4 py-3 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-xs sm:text-sm font-bold text-slate-700 transition cursor-pointer shadow-2xs">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                        <span>Bagikan</span>
                    </button>

                    {{-- Dropdown Pilihan Media Share --}}
                    <div x-show="shareDropdown" 
                         @click.outside="shareDropdown = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-cloak
                         class="absolute right-0 bottom-full mb-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 p-2 space-y-1 z-30">
                        <button type="button" 
                                @click="copyShareLink(); shareDropdown = false;" 
                                class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition text-left cursor-pointer">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span>Salin Tautan</span>
                        </button>
                        <a href="https://api.whatsapp.com/send?text={{ urlencode('Simak karya inovasi magang: ' . $project->title . ' - ' . url()->current()) }}" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 rounded-xl transition text-left">
                            <span class="w-4 h-4 text-emerald-500 font-bold text-center">W</span>
                            <span>WhatsApp</span>
                        </a>
                        <a href="https://t.me/share/url?url={{ urlencode(url()->current()) }}&text={{ urlencode($project->title) }}" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-sky-50 hover:text-sky-600 rounded-xl transition text-left">
                            <span class="w-4 h-4 text-sky-500 font-bold text-center">T</span>
                            <span>Telegram</span>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode('Karya Inovasi Magang Dispusip: ' . $project->title) }}" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 hover:text-slate-900 rounded-xl transition text-left">
                            <span class="w-4 h-4 text-slate-900 font-bold text-center">X</span>
                            <span>Twitter / X</span>
                        </a>
                    </div>

                    {{-- Toast Notifikasi Salin Tautan --}}
                    <div x-show="shareToast" 
                         x-transition 
                         x-cloak 
                         class="absolute right-0 -top-10 px-3 py-1.5 bg-slate-900 text-white text-[11px] font-bold rounded-lg shadow-md whitespace-nowrap">
                        Tautan berhasil disalin ke papan klip!
                    </div>
                </div>
            </div>

            {{-- 5. RUANG DISKUSI & ULASAN KOMENTAR --}}
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                            <span>Ulasan & Diskusi Karya</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800" x-text="commentsCount"></span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Sampaikan apresiasi, pertanyaan, atau masukan membangun untuk penyempurnaan karya mahasiswa ini.
                        </p>
                    </div>
                </div>

                {{-- Formulir Kirim Komentar --}}
                <form method="POST" action="{{ route('projects.comments.store', $project->id) }}" class="space-y-4">
                    @csrf
                    
                    @guest
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap Anda <span class="text-rose-500">*</span></label>
                                <input type="text" 
                                       name="author_name" 
                                       x-model="authorNameInput"
                                       @input="validateCommentLive()"
                                       placeholder="Contoh: Budi Santoso (Masyarakat / Praktisi)" 
                                       required
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                            <span class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-[10px]">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>
                            <span class="font-bold text-slate-800">{{ Auth::user()->name }}</span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-blue-700">
                                {{ Auth::user()->roleLabel() }}
                            </span>
                        </div>
                    @endguest

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Isi Komentar / Masukan <span class="text-rose-500">*</span></label>
                        <textarea name="comment" 
                                  rows="3" 
                                  x-model="commentInput"
                                  @input="validateCommentLive()"
                                  required 
                                  placeholder="Tuliskan ulasan positif, pertanyaan, atau saran konstruktif Anda di sini..."
                                  class="w-full p-3.5 rounded-2xl border border-slate-300 text-xs sm:text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition leading-relaxed"></textarea>
                    </div>

                    {{-- Peringatan Sensor Kata Kasar Real-Time --}}
                    <div x-show="profanityAlert" x-cloak class="p-3 bg-rose-50 border border-rose-200 rounded-xl flex items-center gap-2 text-rose-700 text-xs font-semibold">
                        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span x-text="profanityAlert"></span>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <span class="text-[11px] text-slate-400">
                            Sistem menerapkan filter otomatis untuk menjaga ruang diskusi tetap sopan dan edukatif.
                        </span>
                        <button type="submit" 
                                :disabled="Boolean(profanityAlert) || commentInput.trim().length < 2"
                                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-xs rounded-xl shadow-sm transition active:scale-95 cursor-pointer">
                            Kirim Komentar
                        </button>
                    </div>
                </form>

                {{-- Daftar Komentar yang Telah Diterbitkan --}}
                <div class="space-y-3.5 pt-4 border-t border-slate-100">
                    @forelse($project->comments as $comment)
                        <div x-data="{
                            commentLiked: {{ $comment->isLikedBy($user, $guestId) ? 'true' : 'false' }},
                            commentLikesCount: {{ (int) $comment->likes_count }},
                            commentLikeLoading: false,
                            async toggleCommentLike() {
                                if (this.commentLikeLoading) return;
                                this.commentLikeLoading = true;
                                try {
                                    const resp = await fetch('{{ route('projects.comments.like', $comment->id) }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'Accept': 'application/json'
                                        }
                                    });
                                    const data = await resp.json();
                                    if (data.success) {
                                        this.commentLiked = data.liked;
                                        this.commentLikesCount = data.likes_count;
                                    }
                                } catch(e) {
                                    console.error(e);
                                } finally {
                                    this.commentLikeLoading = false;
                                }
                            }
                        }" class="p-4 sm:p-5 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-slate-300 transition space-y-2.5">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($comment->author_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-extrabold text-xs sm:text-sm text-slate-900">{{ $comment->author_name }}</span>
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $comment->is_guest ? 'bg-slate-200 text-slate-700' : 'bg-blue-100 text-blue-800' }}">
                                                {{ $comment->author_role ?: 'Pengunjung' }}
                                            </span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">
                                            {{ $comment->created_at ? $comment->created_at->diffForHumans() : 'Baru saja' }}
                                        </div>
                                    </div>
                                </div>

                                {{-- Aksi Hapus Komentar (Khusus Super Admin & Admin Dinas) --}}
                                @if($canModerate || (Auth::check() && Auth::id() === $comment->user_id))
                                    <form method="POST" action="{{ route('projects.comments.destroy', $comment->id) }}" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus komentar ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                title="Hapus Komentar (Moderasi)" 
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <p class="text-xs sm:text-sm text-slate-700 leading-relaxed pl-10 whitespace-pre-line">
                                {{ $comment->comment }}
                            </p>

                            {{-- Tombol Suka Komentar --}}
                            <div class="flex items-center justify-between pl-10 pt-1">
                                <button type="button" 
                                        @click="toggleCommentLike()" 
                                        :disabled="commentLikeLoading"
                                        title="Suka komentar ini"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold transition cursor-pointer active:scale-95"
                                        :class="commentLiked ? 'bg-rose-50 text-rose-600 border border-rose-200' : 'bg-white text-slate-500 hover:text-rose-600 border border-slate-200'">
                                    <svg class="w-3.5 h-3.5 transition-transform" :class="commentLiked ? 'fill-rose-500 text-rose-500 scale-110' : 'text-slate-400'" viewBox="0 0 24 24">
                                        <path :fill="commentLiked ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                    </svg>
                                    <span x-text="commentLikesCount > 0 ? commentLikesCount : 'Suka'"></span>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200 text-slate-400 text-xs">
                            Belum ada ulasan atau diskusi untuk proyek ini. Jadilah yang pertama memberikan masukan!
                        </div>
                    @endforelse
                </div>

            </div>

            <!-- Footer Navigasi Bawah -->
            <div class="flex items-center justify-between pt-2">
                <a href="{{ $backUrl }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Halaman Sebelumnya</span>
                </a>

                @if($project->demo_url)
                    <a href="{{ $project->demo_url }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                        <span>Kunjungi Tautan Proyek</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>

