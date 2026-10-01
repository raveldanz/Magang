{{-- Menu aksi pesan: popover di desktop, lembar bawah di HP --}}
<div x-show="menu" x-cloak class="fixed inset-0 z-[65]" @click="closeMenu()" @keydown.escape.window="closeMenu()">
    <div class="absolute inset-0 bg-slate-900/30 sm:bg-transparent"></div>
    <div class="absolute inset-x-3 bottom-3 sm:inset-auto sm:w-56 bg-white rounded-2xl shadow-2xl border border-slate-200 p-1.5" :style="menu ? menu.style : ''" @click.stop role="menu">
        <template x-if="menu">
            <div class="space-y-0.5">
                <button type="button" x-show="canSend" @click="reply(menu.message)" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50" role="menuitem">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                    Balas
                </button>
                <button type="button" x-show="menu.message.body" @click="copyText(menu.message)" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50" role="menuitem">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    Salin teks
                </button>
                <button type="button" x-show="isGroupChat && menu.message.is_mine" @click="openReaders(menu.message)" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50" role="menuitem">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Info dibaca
                </button>
                <template x-for="att in menu.message.attachments" :key="att.id">
                    <a :href="att.download_url" x-show="att.download_url" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50" role="menuitem">
                        <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span class="truncate" x-text="`Unduh ${att.name}`"></span>
                    </a>
                </template>
                <button type="button" x-show="canReport(menu.message)" @click="openReport(menu.message)" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-amber-700 hover:bg-amber-50" role="menuitem">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                    Laporkan pesan
                </button>
                <button type="button" x-show="canDelete(menu.message)" @click="askDelete(menu.message)" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-rose-600 hover:bg-rose-50" role="menuitem">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span x-text="menu.message.is_mine ? 'Hapus untuk semua' : 'Hapus (moderasi)'"></span>
                </button>
            </div>
        </template>
    </div>
</div>
