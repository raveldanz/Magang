{{-- Drawer Utas Komentar Saluran Pengumuman (Telegram Channel Comments Style) --}}
<div x-show="commentsDrawer.open" x-cloak
     class="fixed inset-0 z-50 flex justify-end"
     @keydown.escape.window="closeComments()">

    {{-- Latar Gelap Belakang (Backdrop) --}}
    <div x-show="commentsDrawer.open"
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeComments()"
         class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs"></div>

    {{-- Slide-over Drawer Panel --}}
    <div x-show="commentsDrawer.open"
         x-transition:enter="transform transition ease-in-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transform transition ease-in-out duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="relative w-full max-w-md sm:max-w-lg bg-white shadow-2xl flex flex-col z-10 h-full border-l border-slate-200">

        {{-- Header Drawer --}}
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col gap-2 shrink-0">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Utas Komentar</h3>
                        <p class="text-xs text-slate-500" x-text="`${commentsDrawer.comments.length} komentar`"></p>
                    </div>
                </div>
                <button type="button" @click="closeComments()"
                        class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200 flex items-center justify-center transition"
                        aria-label="Tutup utas komentar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Ringkasan Pengumuman Induk --}}
            <template x-if="commentsDrawer.parentMessage">
                <div class="mt-1 p-2.5 rounded-xl bg-white border border-slate-200/90 text-xs">
                    <span class="block font-bold text-blue-700 truncate" x-text="commentsDrawer.parentMessage.sender ? commentsDrawer.parentMessage.sender.name : 'Pengumuman Resmi'"></span>
                    <p class="text-slate-600 mt-0.5 line-clamp-2 leading-relaxed" x-text="commentsDrawer.parentMessage.body || '(Lampiran Pengumuman)'"></p>
                </div>
            </template>
        </div>

        {{-- Body Drawer: Daftar Komentar --}}
        <div x-ref="commentsContainer" class="flex-1 overflow-y-auto p-4 space-y-3 bg-slate-50/50 min-h-0 scrollbar-thin">
            {{-- Loading State --}}
            <template x-if="commentsDrawer.loading">
                <div class="py-12 flex flex-col items-center justify-center gap-2 text-slate-400">
                    <span class="w-6 h-6 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></span>
                    <span class="text-xs font-medium">Memuat komentar…</span>
                </div>
            </template>

            {{-- Empty State --}}
            <template x-if="!commentsDrawer.loading && commentsDrawer.comments.length === 0">
                <div class="py-14 text-center px-4">
                    <div class="w-12 h-12 mx-auto rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-2.5">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <p class="text-xs font-bold text-slate-700">Belum ada komentar</p>
                    <p class="text-[11px] text-slate-400 mt-1 max-w-xs mx-auto">Ajukan pertanyaan atau tanggapan terkait pengumuman ini melalui formulir di bawah.</p>
                </div>
            </template>

            {{-- List Komentar --}}
            <template x-for="c in commentsDrawer.comments" :key="c.id">
                <div class="flex items-start gap-2.5">
                    <span class="w-7 h-7 rounded-full text-white text-[11px] font-bold flex items-center justify-center shrink-0 mt-0.5"
                          :style="{ backgroundColor: c.sender ? c.sender.color : '#94a3b8' }"
                          x-text="c.sender ? c.sender.initials : '?'"></span>
                    <div class="flex-1 min-w-0 bg-white border border-slate-200/90 rounded-2xl rounded-tl-sm p-3 shadow-2xs">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="text-xs font-bold text-slate-800 truncate" x-text="c.sender ? c.sender.name : 'Pengguna'"></span>
                            <span class="text-[10px] text-slate-400 shrink-0" x-text="formatCommentTime(c.created_at)"></span>
                        </div>
                        <p class="text-xs text-slate-700 leading-relaxed whitespace-pre-wrap break-words [overflow-wrap:anywhere]" x-text="c.body"></p>
                    </div>
                </div>
            </template>
        </div>

        {{-- Footer Drawer: Form Input Komentar --}}
        <div class="p-3 bg-white border-t border-slate-200 shrink-0">
            <form @submit.prevent="submitComment()" class="flex items-end gap-2">
                <textarea x-ref="commentInput" x-model="commentsDrawer.draft" rows="1" maxlength="2000"
                          :disabled="commentsDrawer.sending"
                          @keydown.enter.exact.prevent="submitComment()"
                          placeholder="Tulis komentar atau tanggapan…"
                          aria-label="Tulis komentar"
                          class="flex-1 min-w-0 resize-none rounded-xl border-slate-200 text-xs leading-relaxed py-2.5 px-3 max-h-32 focus:border-blue-500 focus:ring-blue-500"></textarea>
                <button type="submit"
                        :disabled="commentsDrawer.sending || !commentsDrawer.draft.trim()"
                        class="h-10 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold flex items-center justify-center gap-1.5 shadow-xs transition active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed shrink-0"
                        aria-label="Kirim komentar">
                    <span x-show="!commentsDrawer.sending">Kirim</span>
                    <span x-show="commentsDrawer.sending" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                </button>
            </form>
            <p class="text-[10px] text-slate-400 mt-1.5">Tekan Enter untuk mengirim tanggapan.</p>
        </div>
    </div>
</div>
