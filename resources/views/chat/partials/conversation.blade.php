{{-- Isi percakapan aktif --}}
<section class="flex-1 min-w-0 flex-col bg-[#F5F8FC] min-h-0 relative"
         :class="activeId ? 'flex' : 'hidden md:flex'"
         @dragenter.prevent="dragIn()" @dragover.prevent @dragleave.prevent="dragOut()" @drop.prevent="onDrop($event)">

    <template x-if="!activeId">
        <div class="flex-1 flex flex-col items-center justify-center text-center p-8">
            <div class="w-16 h-16 rounded-2xl bg-white border border-slate-200 text-blue-600 flex items-center justify-center mb-4 shadow-2xs">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
            </div>
            <p class="text-base font-bold text-slate-800">Pilih percakapan</p>
            <p class="text-sm text-slate-500 mt-1 max-w-sm">Buka percakapan di sebelah kiri atau tekan <span class="font-semibold text-slate-700">Chat Baru</span> untuk menghubungi kontak Anda.</p>
        </div>
    </template>

    <template x-if="activeId">
        <div class="flex-1 flex flex-col min-h-0">
            {{-- Kepala percakapan --}}
            <div class="h-16 px-2 sm:px-4 flex items-center gap-2 sm:gap-3 bg-white border-b border-slate-200/80 shrink-0">
                <button type="button" @click="closeConversation()" class="md:hidden w-10 h-10 rounded-xl text-slate-600 hover:bg-slate-100 flex items-center justify-center shrink-0" aria-label="Kembali ke daftar percakapan">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <template x-if="active">
                    <button type="button" @click="toggleInfo()" class="flex items-center gap-3 min-w-0 flex-1 text-left rounded-xl py-1 hover:bg-slate-50 transition" aria-label="Lihat info percakapan">
                        <span class="relative w-10 h-10 rounded-full text-white text-sm font-bold flex items-center justify-center shrink-0" :style="{ backgroundColor: active.avatar.color }">
                            <template x-if="active.avatar.group">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </template>
                            <template x-if="!active.avatar.group"><span x-text="active.avatar.initials"></span></template>
                            <span x-show="headerOnline" class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                        </span>
                        <span class="min-w-0">
                            <span class="flex items-center gap-2">
                                <span class="text-sm font-bold text-slate-900 truncate" x-text="active.title"></span>
                                <span class="hidden sm:inline-flex"><span x-show="active.type === 'placement'" class="px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold whitespace-nowrap">Grup Bimbingan</span></span>
                                <span x-show="active.contact && active.contact.inactive" class="px-2 py-0.5 rounded-full bg-slate-100 border border-slate-300 text-slate-600 text-xs font-semibold whitespace-nowrap">Nonaktif</span>{{-- status-guard:ignore --}}
                                <svg x-show="active.muted" class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-label="Dibisukan"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15zM17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/></svg>
                            </span>
                            <span class="block text-xs truncate" :class="headerTyping ? 'text-blue-600 font-semibold' : (headerOnline ? 'text-emerald-600' : 'text-slate-500')" x-text="headerSubtitle"></span>
                        </span>
                    </button>
                </template>
                <span x-show="connectionIssue" class="hidden sm:inline text-xs text-amber-600 font-semibold whitespace-nowrap">Menyambungkan ulang…</span>
                <button type="button" @click="toggleInfo()" class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition"
                        :class="infoOpen ? 'bg-blue-50 text-blue-600' : 'text-slate-500 hover:bg-slate-100'" aria-label="Info percakapan" title="Info percakapan">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </button>
            </div>

            {{-- Daftar pesan --}}
            <div x-ref="scroller" @scroll.passive="onScroll()" class="flex-1 overflow-y-auto min-h-0 px-3 sm:px-6 py-4" aria-live="polite">
                <div x-show="loadingOlder" class="flex justify-center py-2"><span class="w-5 h-5 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></span></div>
                <p x-show="!hasMore && !loadingMessages && messages.length > 0" class="text-center text-xs text-slate-400 py-2">Awal percakapan</p>
                <div x-show="loadingMessages" class="flex justify-center py-10"><span class="w-6 h-6 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></span></div>
                <p x-show="!loadingMessages && messages.length === 0 && pending.length === 0" class="text-center text-sm text-slate-500 py-10">Belum ada pesan. Sapa lebih dulu 👋</p>

                <template x-for="item in timeline" :key="item.key">
                    <div>
                        <template x-if="item.kind === 'date'">
                            <div class="flex justify-center my-3">
                                <span class="px-3 py-1 rounded-full bg-white border border-slate-200 text-xs font-semibold text-slate-500 shadow-2xs" x-text="item.label"></span>
                            </div>
                        </template>

                        <template x-if="item.kind === 'system'">
                            <div class="flex justify-center my-2">
                                <span class="max-w-[90%] text-center px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200 text-xs text-slate-600 leading-relaxed" x-text="item.m.body"></span>
                            </div>
                        </template>

                        <template x-if="item.kind === 'msg'">
                            @include('chat.partials.message')
                        </template>
                    </div>
                </template>
            </div>

            {{-- Pesan baru di bawah saat pengguna sedang menggulir ke atas --}}
            <button type="button" x-show="newBelow > 0" x-cloak @click="scrollToBottom(true)"
                    class="absolute bottom-32 left-1/2 -translate-x-1/2 z-10 px-4 py-2 rounded-full bg-blue-600 text-white text-xs font-bold shadow-lg">
                <span x-text="newBelow"></span> pesan baru ↓
            </button>

            @include('chat.partials.composer')
        </div>
    </template>

    <div x-show="dragging" x-cloak class="absolute inset-2 z-20 rounded-2xl border-2 border-dashed border-blue-400 bg-blue-50/90 flex items-center justify-center pointer-events-none">
        <p class="text-sm font-bold text-blue-700">Lepaskan file untuk dilampirkan (maks. <span x-text="cfg.maxFiles"></span> file)</p>
    </div>
</section>
