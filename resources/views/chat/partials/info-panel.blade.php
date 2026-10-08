{{-- Panel info percakapan: anggota, pengaturan, media & file --}}
<aside x-show="infoOpen && active" x-cloak
       class="absolute inset-y-0 right-0 z-30 w-full sm:w-96 xl:w-80 bg-white border-l border-slate-200/80 flex flex-col shadow-2xl xl:shadow-none xl:static xl:z-auto shrink-0">
    <template x-if="active">
        <div class="flex flex-col min-h-0 h-full">
            <div class="h-16 px-4 flex items-center justify-between gap-2 border-b border-slate-100 shrink-0">
                <p class="text-sm font-extrabold text-slate-900" x-text="isChannelChat ? 'Info Saluran' : (isGroupChat ? 'Info Grup' : 'Info Kontak')"></p>
                <button type="button" @click="infoOpen = false" class="w-9 h-9 rounded-xl text-slate-500 hover:bg-slate-100 flex items-center justify-center" aria-label="Tutup info">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div x-ref="infoScroll" class="flex-1 overflow-y-auto min-h-0 p-4 space-y-5 scrollbar-thin">
                <div class="text-center space-y-2">
                    <template x-if="isChannelChat">
                        <div class="mx-auto w-20 h-20 rounded-2xl border border-slate-200/90 bg-white p-2 flex items-center justify-center shadow-xs">
                            <template x-if="active.avatar && (active.avatar.logo_url || active.avatar.url)">
                                <img :src="active.avatar.logo_url || active.avatar.url" :alt="active.title" class="w-full h-full object-contain">
                            </template>
                            <template x-if="!active.avatar || (!active.avatar.logo_url && !active.avatar.url)">
                                <span class="text-2xl font-bold text-blue-700" x-text="active.avatar ? active.avatar.initials : 'P'"></span>
                            </template>
                        </div>
                    </template>
                    <template x-if="!isChannelChat">
                        <span class="mx-auto w-20 h-20 rounded-full text-white text-2xl font-bold flex items-center justify-center" :style="{ backgroundColor: active.avatar.color }">
                            <template x-if="active.avatar.group">
                                <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </template>
                            <template x-if="!active.avatar.group"><span x-text="active.avatar.initials"></span></template>
                        </span>
                    </template>
                    <div class="flex items-center justify-center gap-1.5 flex-wrap">
                        <p class="text-base font-extrabold text-slate-900 break-words" x-text="active.title"></p>
                        <span x-show="active.scope_badge" class="px-2 py-0.5 rounded text-[11px] font-bold"
                              :class="active.scope_badge === 'Pemkot' ? 'bg-amber-50 text-amber-800 border border-amber-200' : (active.scope_badge === 'Dinas' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200')"
                              x-text="`[${active.scope_badge}]`"></span>
                    </div>
                    <p class="text-xs text-slate-500" x-text="isChannelChat ? 'Saluran Pengumuman Resmi' : (isGroupChat ? `${active.member_count} anggota` : active.subtitle)"></p>
                    <template x-if="active.stage_badge && !isChannelChat && (!active.title.toLowerCase().startsWith(active.stage_badge.label.toLowerCase()) || active.stage_badge.is_urgent)">
                        <div class="pt-1">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold"
                                  :class="{
                                      'bg-amber-50 text-amber-700 border border-amber-200': active.stage_badge.theme === 'urgent',
                                      'bg-orange-50 text-orange-700 border border-orange-200': active.stage_badge.theme === 'warning',
                                      'bg-emerald-50 text-emerald-700 border border-emerald-200': active.stage_badge.theme === 'success',
                                      'bg-indigo-50 text-indigo-700 border border-indigo-200': active.stage_badge.theme === 'info',
                                      'bg-slate-100 text-slate-600 border border-slate-200': active.stage_badge.theme === 'neutral'
                                  }"
                                  :title="active.stage_badge.hint || active.stage_badge.label">
                                <span class="w-2 h-2 rounded-full shrink-0"
                                      :class="{
                                          'bg-amber-500': active.stage_badge.theme === 'urgent',
                                          'bg-orange-500': active.stage_badge.theme === 'warning',
                                          'bg-emerald-500': active.stage_badge.theme === 'success',
                                          'bg-indigo-500': active.stage_badge.theme === 'info',
                                          'bg-slate-400': active.stage_badge.theme === 'neutral'
                                      }"></span>
                                <span x-text="active.stage_badge.label"></span>
                            </span>
                        </div>
                    </template>
                    <p x-show="active.description" class="text-xs text-slate-600 bg-slate-50 border border-slate-100 rounded-xl p-2.5 text-left whitespace-pre-line" x-text="active.description"></p>
                    <template x-if="isChannelChat">
                        <p class="text-xs text-blue-700 bg-blue-50 border border-blue-200 rounded-xl p-2.5 text-left leading-relaxed">
                            Saluran ini diperuntukkan untuk siaran pengumuman resmi. Semua anggota dapat mengajukan tanggapan melalui utas komentar.
                        </p>
                    </template>
                    <template x-if="!active.description && (active.scope_type === 'mentor_guidance' || active.scope_type === 'dpl_guidance' || active.type === 'placement')">
                        <p class="text-xs text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl p-2.5 text-left leading-relaxed">
                            Grup bimbingan ini dibuat dan dikelola secara otomatis oleh sistem untuk mempermudah koordinasi mahasiswa bimbingan aktif dengan pembimbing.
                        </p>
                    </template>
                    <a x-show="active.placement_url" :href="active.placement_url" class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-800">
                        Buka detail magang mahasiswa →
                    </a>
                </div>

                {{-- Pengaturan pribadi --}}
                <div class="grid grid-cols-2 gap-2" x-show="!readOnly">
                    <button type="button" @click="toggleSetting('muted')" class="flex flex-col items-center gap-1 p-3 rounded-xl border text-xs font-semibold transition"
                            :class="active.muted ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-slate-200 text-slate-700 hover:bg-slate-50'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span x-text="active.muted ? 'Aktifkan notifikasi' : 'Bisukan'"></span>
                    </button>
                    <button type="button" @click="toggleSetting('pinned')" class="flex flex-col items-center gap-1 p-3 rounded-xl border text-xs font-semibold transition"
                            :class="active.pinned ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-slate-200 text-slate-700 hover:bg-slate-50'">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M16 3a1 1 0 01.707 1.707L15.414 6l2.586 2.586 1.293-1.293a1 1 0 111.414 1.414l-4 4-.707-.707-3 3V19a1 1 0 01-1.707.707l-3.5-3.5-3.586 3.586a1 1 0 01-1.414-1.414L6.586 14.8l-3.5-3.5A1 1 0 013.793 9.6H7.8l3-3-.707-.707 4-4A1 1 0 0116 3z"/></svg>
                        <span x-text="active.pinned ? 'Lepas sematan' : 'Sematkan'"></span>
                    </button>
                </div>

                {{-- Anggota grup --}}
                <template x-if="isGroupChat">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Anggota (<span x-text="active.member_count"></span>)</p>
                            <div class="flex items-center gap-1" x-show="active.can_manage && !readOnly">
                                <button type="button" @click="editGroup()" class="h-8 px-2.5 rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-100">Ubah</button>
                                <button type="button" @click="openContacts('add')" class="h-8 px-2.5 rounded-lg text-xs font-bold text-blue-600 hover:bg-blue-50">+ Tambah</button>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <template x-for="member in active.members" :key="member.id">
                                <div x-data="{ open: false }" class="rounded-xl" :class="open ? 'bg-slate-50' : 'hover:bg-slate-50'">
                                    <div class="flex items-center gap-3 p-2">
                                        <button type="button" @click="open = !open" class="flex items-center gap-3 min-w-0 flex-1 text-left" :aria-expanded="open" :aria-label="`Lihat kontak ${member.name}`">
                                            <span class="relative w-9 h-9 rounded-full text-white text-xs font-bold flex items-center justify-center shrink-0" :style="{ backgroundColor: member.color }">
                                                <span x-text="member.initials"></span>
                                                <span x-show="member.online" class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                                            </span>
                                            <span class="min-w-0 flex-1">
                                                <span class="flex items-center gap-1.5">
                                                    <span class="text-sm font-semibold text-slate-800 truncate" x-text="member.is_me ? `${member.name} (Anda)` : member.name"></span>
                                                    <span x-show="member.is_admin" class="px-1.5 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-100 text-xs font-bold shrink-0">Admin</span>
                                                </span>
                                                <span class="block text-xs text-slate-500 truncate" x-text="[member.role_label, member.org].filter(Boolean).join(' · ')"></span>
                                            </span>
                                            <svg class="w-4 h-4 text-slate-400 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                        <button type="button" x-show="active.can_manage && !member.is_me && !readOnly" @click="askRemoveMember(member)"
                                                class="w-8 h-8 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center shrink-0" :aria-label="`Keluarkan ${member.name}`" title="Keluarkan dari grup">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6"/></svg>
                                        </button>
                                    </div>
                                    <div x-show="open" x-cloak class="px-2 pb-2">
                                        <template x-for="person in [member]" :key="person.id">
                                            @include('chat.partials.contact-details')
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Kontak 1-on-1 --}}
                <template x-if="!isGroupChat && active.contact">
                    <div class="space-y-2">
                        <div class="rounded-xl border border-slate-200 divide-y divide-slate-100 text-sm">
                            <div class="p-3"><p class="text-xs text-slate-400">Peran</p><p class="font-semibold text-slate-800" x-text="active.contact.role_label || '-'"></p></div>
                            <div class="p-3"><p class="text-xs text-slate-400">Instansi / Kampus</p><p class="font-semibold text-slate-800" x-text="active.contact.org || '-'"></p></div>
                            <div class="p-3"><p class="text-xs text-slate-400">Status</p><p class="font-semibold" :class="headerOnline ? 'text-emerald-600' : 'text-slate-800'" x-text="headerOnline ? 'Online' : (state.presence ? headerSubtitle.split(' · ')[0] : '-')"></p></div>
                        </div>
                        <template x-for="person in [active.contact]" :key="person.id">
                            @include('chat.partials.contact-details')
                        </template>
                    </div>
                </template>

                {{-- Media & dokumen --}}
                <div class="space-y-2">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Foto & Video</p>
                    <div x-show="mediaLoading" class="flex justify-center py-4"><span class="w-5 h-5 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></span></div>
                    <p x-show="!mediaLoading && mediaVisual.length === 0" class="text-xs text-slate-500">Belum ada foto atau video.</p>
                    <div class="grid grid-cols-3 gap-1.5">
                        <template x-for="(item, idx) in mediaVisual" :key="item.id">
                            <button type="button" @click="item.kind === 'image' ? openLightbox(mediaVisual.filter((m) => m.kind === 'image'), mediaVisual.filter((m) => m.kind === 'image').indexOf(item)) : window.open(item.url, '_blank')"
                                    class="aspect-square rounded-lg overflow-hidden bg-slate-100 relative">
                                <template x-if="item.kind === 'image'"><img :src="item.url" :alt="item.name" loading="lazy" class="w-full h-full object-cover"></template>
                                <template x-if="item.kind === 'video'"><span class="absolute inset-0 flex items-center justify-center text-2xl bg-slate-800 text-white">▶</span></template>
                            </button>
                        </template>
                    </div>

                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400 pt-2">Dokumen & Audio</p>
                    <p x-show="!mediaLoading && mediaFiles.length === 0" class="text-xs text-slate-500">Belum ada dokumen atau audio.</p>
                    <div class="space-y-1">
                        <template x-for="file in mediaFiles" :key="file.id">
                            <a :href="file.download_url" class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50">
                                <span class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0" x-text="file.kind === 'audio' ? '🎤' : '📄'"></span>
                                <span class="min-w-0">
                                    <span class="block text-sm font-semibold text-slate-800 truncate" x-text="file.name"></span>
                                    <span class="block text-xs text-slate-500" x-text="`${file.size_label} · ${listTime(file.created_at)}`"></span>
                                </span>
                            </a>
                        </template>
                    </div>
                </div>

                <button type="button" x-show="active.can_leave && !readOnly" @click="askLeave()"
                        class="w-full h-11 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-600 hover:text-white text-sm font-bold transition">
                    Keluar dari Grup
                </button>
            </div>
        </div>
    </template>
</aside>
