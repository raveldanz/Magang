<!-- Global Double-Confirmation Delete Modal (Alpine.js) -->
<div x-data="{
    open: false,
    action: '',
    title: 'Konfirmasi Hapus Data',
    name: '',
    desc: '',
    confirmed: false,
    openModal(data) {
        this.action = data.action || '';
        this.title = data.title || 'Konfirmasi Hapus Data';
        this.name = data.name || '';
        this.desc = data.desc || '';
        this.confirmed = false;
        this.open = true;
        this.$nextTick(() => {
            if (this.$refs.cancelBtn) this.$refs.cancelBtn.focus();
        });
    },
    closeModal() {
        this.open = false;
        this.action = '';
        this.name = '';
        this.desc = '';
        this.confirmed = false;
    },
    submitDelete() {
        if (!this.confirmed) return;
        this.$refs.deleteForm.submit();
    }
}"
@open-delete-modal.window="openModal($event.detail)"
@keydown.escape.window="if(open) closeModal()"
x-cloak>
    <!-- Modal Backdrop -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 sm:p-6"
         style="z-index: 99998;">
        
        <!-- Modal Card -->
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             @click.outside="closeModal()"
             class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-md w-full overflow-hidden p-6 sm:p-7 space-y-5 text-left relative">
            
            <!-- Hidden Form to execute DELETE -->
            <form x-ref="deleteForm" :action="action" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>

            <!-- Header: Warning Icon + Title -->
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 shrink-0 shadow-xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <div class="flex-1 space-y-1">
                    <h3 class="text-base font-black text-slate-900 leading-tight" x-text="title"></h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Tindakan ini bersifat permanen. Mohon periksa kembali data di bawah sebelum melanjutkan.
                    </p>
                </div>
                <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition" title="Tutup">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Target Entity Details Box -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Objek yang akan dihapus:</span>
                <div class="font-black text-sm text-slate-900 break-words" x-text="name"></div>
                <template x-if="desc">
                    <div class="text-xs text-slate-500 font-medium break-words mt-0.5" x-text="desc"></div>
                </template>
            </div>

            <!-- Double Confirmation (2x Checkbox) to Prevent Accidental Click -->
            <label class="flex items-start gap-3 p-3.5 rounded-2xl border border-rose-200 bg-rose-50/50 cursor-pointer select-none hover:bg-rose-50 transition">
                <input type="checkbox" 
                       x-model="confirmed" 
                       class="mt-0.5 w-4 h-4 rounded text-rose-600 border-rose-300 focus:ring-rose-500 cursor-pointer shrink-0">
                <span class="text-xs text-rose-950 font-semibold leading-snug">
                    Saya mengonfirmasi bahwa saya benar-benar ingin menghapus data ini dan memahami risikonya.
                </span>
            </label>

            <!-- Action Buttons: Cancel (Safe) vs Confirm Delete -->
            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center sm:justify-end gap-2.5 pt-2 border-t border-slate-100">
                <button type="button" 
                        x-ref="cancelBtn"
                        @click="closeModal()" 
                        class="w-full sm:w-auto px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition text-center cursor-pointer">
                    Batal
                </button>
                <button type="button" 
                        :disabled="!confirmed" 
                        @click="submitDelete()" 
                        class="w-full sm:w-auto px-6 py-2.5 text-xs font-bold rounded-xl transition flex items-center justify-center gap-2 shadow-sm"
                        :class="confirmed 
                            ? 'bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white cursor-pointer shadow-rose-600/20' 
                            : 'bg-slate-200 text-slate-400 cursor-not-allowed border border-slate-300/60'">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Ya, Hapus Permanen</span>
                </button>
            </div>

        </div>
    </div>
</div>
