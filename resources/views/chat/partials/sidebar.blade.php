{{-- Daftar percakapan --}}
<aside class="w-full md:w-80 lg:w-96 shrink-0 flex-col border-r border-slate-200/80 bg-white min-h-0"
       :class="activeId ? 'hidden md:flex' : 'flex'">
    {{-- `relative` di sini: panel pengaturan notifikasi selebar sidebar, tidak terpotong tepi kartu --}}
    <div x-data="{ prefsOpen: false }" @keydown.escape.window="prefsOpen = false" class="relative px-4 pt-4 pb-3 border-b border-slate-100 space-y-3 shrink-0">
        <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
                <h1 class="text-lg font-extrabold text-slate-900 leading-tight">Pesan</h1>
                <p class="text-xs text-slate-500 truncate">Chat dengan mentor, DPL, dinas & kampus</p>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                {{-- Pengaturan notifikasi --}}
                <button type="button" @click.stop="prefsOpen = !prefsOpen" :aria-expanded="prefsOpen"
                        class="w-10 h-10 rounded-xl border flex items-center justify-center transition"
                        :class="prefsOpen ? 'border-blue-200 bg-blue-50 text-blue-600' : 'border-slate-200 text-slate-600 hover:text-blue-600 hover:border-blue-200'"
                        aria-label="Pengaturan notifikasi chat" title="Pengaturan notifikasi">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </button>

                {{-- Chat / grup baru --}}
                <div x-data="{ open: false }" class="relative" @click.outside="open = false">
                    <button type="button" @click="cfg.canCreateGroups && !readOnly ? (open = !open) : openContacts('direct')"
                            class="inline-flex items-center gap-1.5 h-10 px-3.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition active:scale-95 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Chat Baru</span>
                    </button>
                    <div x-show="open" x-cloak x-transition class="absolute right-0 top-full mt-2 w-60 rounded-2xl p-1.5 bg-white border border-slate-200 shadow-2xl z-40">
                        <button type="button" @click="open = false; openContacts('direct')" class="w-full flex items-center gap-3 p-2.5 rounded-xl hover:bg-blue-50 text-left">
                            <span class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></span>
                            <span><span class="block text-sm font-bold text-slate-800">Chat pribadi</span><span class="block text-xs text-slate-500">Pesan 1-on-1 ke satu kontak</span></span>
                        </button>
                        <button type="button" @click="open = false; openContacts('group')" class="w-full flex items-center gap-3 p-2.5 rounded-xl hover:bg-blue-50 text-left">
                            <span class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></span>
                            <span><span class="block text-sm font-bold text-slate-800">Grup baru</span><span class="block text-xs text-slate-500">Koordinasi banyak orang sekaligus</span></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Panel pengaturan notifikasi chat --}}
        <div x-show="prefsOpen" x-cloak x-transition.origin.top @click.outside="prefsOpen = false"
             class="absolute left-3 right-3 top-[4.25rem] z-40 rounded-2xl p-2 bg-white border border-slate-200 shadow-2xl space-y-1" role="dialog" aria-label="Pengaturan notifikasi chat">
            <p class="px-2.5 pt-1.5 pb-1 text-xs font-bold uppercase tracking-wide text-slate-400">Notifikasi Chat</p>
            <button type="button" @click="$store.chatPrefs.toggleDesktop()" class="w-full flex items-center justify-between gap-3 p-2.5 rounded-xl hover:bg-slate-50 text-left">
                <span class="min-w-0">
                    <span class="block text-sm font-semibold text-slate-800">Notifikasi desktop</span>
                    <span class="block text-xs"
                          :class="$store.chatPrefs.permission === 'denied' ? 'text-rose-600' : 'text-slate-500'"
                          x-text="{
                              unsupported: 'Browser ini tidak mendukung notifikasi desktop',
                              denied: 'Diblokir browser — izinkan notifikasi untuk situs ini di pengaturan browser',
                          }[$store.chatPrefs.permission] || ($store.chatPrefs.desktop ? 'Aktif — muncul saat jendela ini tidak sedang dibuka' : 'Nonaktif — tekan untuk mengaktifkan')"></span>
                </span>
                <span class="w-10 h-6 rounded-full p-0.5 transition shrink-0" :class="$store.chatPrefs.desktop ? 'bg-blue-600' : 'bg-slate-300'">
                    <span class="block w-5 h-5 rounded-full bg-white shadow transition" :class="$store.chatPrefs.desktop ? 'translate-x-4' : ''"></span>
                </span>
            </button>
            <button type="button" @click="$store.chatPrefs.toggleSound()" class="w-full flex items-center justify-between gap-3 p-2.5 rounded-xl hover:bg-slate-50 text-left">
                <span class="min-w-0">
                    <span class="block text-sm font-semibold text-slate-800">Suara pesan masuk</span>
                    <span class="block text-xs text-slate-500" x-text="$store.chatPrefs.sound ? 'Aktif — nada singkat saat ada pesan baru' : 'Nonaktif'"></span>
                </span>
                <span class="w-10 h-6 rounded-full p-0.5 transition shrink-0" :class="$store.chatPrefs.sound ? 'bg-blue-600' : 'bg-slate-300'">
                    <span class="block w-5 h-5 rounded-full bg-white shadow transition" :class="$store.chatPrefs.sound ? 'translate-x-4' : ''"></span>
                </span>
            </button>
            <div class="flex items-center justify-between gap-2 px-2.5 pt-1 pb-1.5">
                <p class="text-xs text-slate-500">Percakapan yang dibisukan tidak memunculkan notifikasi.</p>
                <button type="button" @click="$store.chatPrefs.testSound()" class="h-8 px-2.5 rounded-lg text-xs font-bold text-blue-600 hover:bg-blue-50 whitespace-nowrap">Tes suara</button>
            </div>
        </div>

        <div class="relative">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
            <input type="search" x-model="search" placeholder="Cari percakapan…" aria-label="Cari percakapan"
                   class="w-full h-10 pl-9 pr-3 rounded-xl border-slate-200 text-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div class="flex items-center gap-1.5 overflow-x-auto" role="tablist" aria-label="Saring percakapan">
            <template x-for="option in [['all', 'Semua'], ['unread', 'Belum dibaca'], ['groups', 'Grup']]" :key="option[0]">
                <button type="button" @click="listFilter = option[0]" role="tab" :aria-selected="listFilter === option[0]"
                        class="h-8 px-3 rounded-full text-xs font-bold whitespace-nowrap transition"
                        :class="listFilter === option[0] ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                    <span x-text="option[1]"></span><span x-show="option[0] === 'unread' && totalUnreadGroups > 0" x-text="` (${totalUnreadGroups})`"></span>
                </button>
            </template>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto min-h-0">
        <template x-if="convLoading">
            <div class="p-4 space-y-3">
                <template x-for="i in 5" :key="i">
                    <div class="flex items-center gap-3 animate-pulse">
                        <div class="w-11 h-11 rounded-full bg-slate-100"></div>
                        <div class="flex-1 space-y-2"><div class="h-3 bg-slate-100 rounded w-2/3"></div><div class="h-3 bg-slate-100 rounded w-1/2"></div></div>
                    </div>
                </template>
            </div>
        </template>

        <template x-if="!convLoading && conversations.length === 0">
            <div class="px-6 py-12 text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </div>
                <p class="text-sm font-bold text-slate-800">Belum ada percakapan</p>
                <p class="text-xs text-slate-500 mt-1">Mulai chat dengan mentor, DPL, admin dinas, atau admin kampus Anda.</p>
            </div>
        </template>

        <template x-if="!convLoading && conversations.length > 0 && filteredConversations.length === 0">
            <p class="px-6 py-10 text-center text-xs text-slate-500">Tidak ada percakapan yang cocok.</p>
        </template>

        <template x-for="c in filteredConversations" :key="c.id">
            <button type="button" @click="open(c.id)"
                    class="w-full flex items-center gap-3 px-4 py-3 text-left border-b border-slate-100/80 transition min-h-[72px]"
                    :class="c.id === activeId ? 'bg-blue-50/80' : 'hover:bg-slate-50'">
                <span class="relative w-11 h-11 rounded-full text-white text-sm font-bold flex items-center justify-center shrink-0"
                      :style="{ backgroundColor: c.avatar.color }">
                    <template x-if="c.avatar.group">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </template>
                    <template x-if="!c.avatar.group"><span x-text="c.avatar.initials"></span></template>
                    <span x-show="c.contact && c.contact.online" class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-emerald-500 ring-2 ring-white" title="Online"></span>
                </span>
                <span class="flex-1 min-w-0">
                    <span class="flex items-center justify-between gap-2">
                        <span class="flex items-center gap-1 min-w-0">
                            <span class="text-sm font-bold text-slate-900 truncate" x-text="c.title"></span>
                            <svg x-show="c.pinned" class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-label="Disematkan"><path d="M16 3a1 1 0 01.707 1.707L15.414 6l2.586 2.586 1.293-1.293a1 1 0 111.414 1.414l-4 4-.707-.707-3 3V19a1 1 0 01-1.707.707l-3.5-3.5-3.586 3.586a1 1 0 01-1.414-1.414L6.586 14.8l-3.5-3.5A1 1 0 013.793 9.6H7.8l3-3-.707-.707 4-4A1 1 0 0116 3z"/></svg>
                        </span>
                        <span class="text-xs shrink-0" :class="c.unread && !c.muted ? 'text-blue-600 font-bold' : 'text-slate-400'" x-text="listTime(c.last_message ? c.last_message.created_at : c.sort_at)"></span>
                    </span>
                    <span class="block text-xs text-slate-400 truncate" x-text="c.subtitle"></span>
                    <span class="flex items-center justify-between gap-2 mt-0.5">
                        <span class="text-xs truncate" :class="c.unread && !c.muted ? 'text-slate-800 font-semibold' : 'text-slate-500'">
                            <span x-show="c.last_message && c.last_message.is_mine" class="text-slate-400">Anda: </span><span x-show="c.last_message && c.last_message.sender_name" class="text-slate-500" x-text="c.last_message ? `${c.last_message.sender_name}: ` : ''"></span><span :class="c.last_message && c.last_message.system ? 'italic' : ''" x-text="c.last_message ? c.last_message.preview : 'Belum ada pesan'"></span>
                        </span>
                        <span class="flex items-center gap-1 shrink-0">
                            <svg x-show="c.muted" class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-label="Dibisukan"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15zM17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/></svg>
                            <span x-show="c.unread > 0" class="min-w-[20px] h-5 px-1.5 rounded-full text-white text-xs font-bold flex items-center justify-center"
                                  :class="c.muted ? 'bg-slate-400' : 'bg-blue-600'" x-text="c.unread > 99 ? '99+' : c.unread"></span>
                        </span>
                    </span>
                </span>
            </button>
        </template>
    </div>
</aside>
