{{-- Lightbox foto (LRN-019): perbesar, geser antar-foto, unduh --}}
<div x-show="lightbox" x-cloak class="fixed inset-0 z-[80] bg-slate-950/95 flex flex-col"
     @keydown.escape.window="lightbox = null" @keydown.arrow-left.window="lightboxStep(-1)" @keydown.arrow-right.window="lightboxStep(1)">
    <div class="flex items-center justify-between gap-3 p-3 sm:p-4 text-white shrink-0">
        <p class="text-sm font-semibold truncate">
            <span x-text="lightboxItem ? lightboxItem.name : ''"></span>
            <span x-show="lightbox && lightbox.items.length > 1" class="text-white/60 font-normal" x-text="lightbox ? ` · ${lightbox.index + 1}/${lightbox.items.length}` : ''"></span>
        </p>
        <div class="flex items-center gap-2 shrink-0">
            <a x-show="lightboxItem && lightboxItem.download_url" :href="lightboxItem ? lightboxItem.download_url : null" class="h-10 px-3.5 rounded-xl bg-white/10 hover:bg-white/20 text-xs font-bold flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Unduh
            </a>
            <button type="button" @click="lightbox = null" class="w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center" aria-label="Tutup">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
    <div class="flex-1 min-h-0 flex items-center justify-center gap-2 p-3 sm:p-8" @click.self="lightbox = null">
        <button type="button" x-show="lightbox && lightbox.items.length > 1" @click="lightboxStep(-1)" class="w-11 h-11 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center shrink-0" aria-label="Foto sebelumnya">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <img :src="lightboxItem ? lightboxItem.url : ''" :alt="lightboxItem ? lightboxItem.name : ''" class="max-w-full max-h-full object-contain rounded-lg min-w-0">
        <button type="button" x-show="lightbox && lightbox.items.length > 1" @click="lightboxStep(1)" class="w-11 h-11 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center shrink-0" aria-label="Foto berikutnya">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        </button>
    </div>
</div>
