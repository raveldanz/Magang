<!-- Global Double-Confirmation Reset Password Modal (Alpine.js) -->
<div x-data="{
    open: false,
    openedAt: 0,
    action: '',
    title: 'Konfirmasi Reset Password',
    name: '',
    email: '',
    role: '',
    confirmed: false,
    openModal(data) {
        this.action = data.action || '';
        this.title = data.title || 'Konfirmasi Reset Password';
        this.name = data.name || '';
        this.email = data.email || '';
        this.role = data.role || '';
        this.confirmed = false;
        this.openedAt = Date.now();
        this.open = true;
        this.$nextTick(() => {
            if (this.$refs.cancelBtn) this.$refs.cancelBtn.focus();
        });
    },
    // Abaikan klik yang sama dengan klik pembuka modal (lihat confirm-action-modal).
    closeFromOutside() {
        if (Date.now() - this.openedAt < 400) return;
        this.closeModal();
    },
    closeModal() {
        this.open = false;
        this.action = '';
        this.name = '';
        this.email = '';
        this.role = '';
        this.confirmed = false;
    },
    submitReset() {
        if (!this.confirmed) return;
        this.$refs.resetForm.submit();
    }
}"
@open-reset-modal.window="openModal($event.detail)"
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
             @click.outside="closeFromOutside()"
             class="bg-white rounded-2xl sm:rounded-3xl shadow-2xl border border-slate-100 max-w-[calc(100vw-1.5rem)] sm:max-w-md w-full max-h-[90vh] overflow-y-auto p-5 sm:p-7 space-y-5 text-left relative">
            
            <!-- Hidden Form to execute POST reset -->
            <form x-ref="resetForm" :action="action" method="POST" class="hidden">
                @csrf
            </form>

            <!-- Header: Key Icon + Title -->
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 shrink-0 shadow-xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <div class="flex-1 space-y-1">
                    <h3 class="text-base font-black text-slate-900 leading-tight" x-text="title"></h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Tindakan ini mengganti kata sandi akun dengan kata sandi sementara acak. Pemilik akun wajib menggantinya saat masuk.
                    </p>
                </div>
                <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-700 hover:bg-slate-100 min-w-[44px] min-h-[44px] sm:min-w-[36px] sm:min-h-[36px] flex items-center justify-center rounded-xl transition cursor-pointer shrink-0" title="Tutup" aria-label="Tutup">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Target User Details Box -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">Akun yang akan direset:</span>
                <div class="flex items-center justify-between gap-2">
                    <div class="font-black text-sm text-slate-900 break-words" x-text="name"></div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider bg-slate-200 text-slate-700" x-text="role"></span>
                </div>
                <div class="text-xs text-slate-500 font-medium break-words" x-text="email"></div>
                <div class="pt-2 border-t border-slate-200/60 text-xs text-slate-600 flex items-center gap-1.5">
                    <span class="text-slate-400">Password baru:</span>
                    <code class="px-2 py-0.5 bg-amber-100/70 border border-amber-200 text-amber-900 font-bold rounded text-xs">dibuat acak, tampil satu kali setelah reset</code>
                </div>
            </div>

            <!-- Double Confirmation Checkbox -->
            <label class="flex items-start gap-3 p-3.5 rounded-2xl border border-amber-200 bg-amber-50/50 cursor-pointer select-none hover:bg-amber-50 transition">
                <input type="checkbox" 
                       x-model="confirmed" 
                       class="mt-0.5 w-4 h-4 rounded text-amber-600 border-amber-300 focus:ring-amber-500 cursor-pointer shrink-0">
                <span class="text-xs text-amber-950 font-semibold leading-snug">
                    Saya mengonfirmasi bahwa saya ingin mereset password akun ini dan akan mencatat password sementaranya.
                </span>
            </label>

            <!-- Action Buttons -->
            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center sm:justify-end gap-2.5 pt-2 border-t border-slate-100">
                <button type="button" 
                        x-ref="cancelBtn"
                        @click="closeModal()" 
                        class="w-full sm:w-auto min-h-[44px] sm:min-h-[40px] px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-bold rounded-xl transition text-center cursor-pointer">
                    Batal
                </button>
                <button type="button" 
                        :disabled="!confirmed" 
                        @click="submitReset()" 
                        class="w-full sm:w-auto min-h-[44px] sm:min-h-[40px] px-6 py-2.5 text-xs sm:text-sm font-bold rounded-xl transition flex items-center justify-center gap-2 shadow-xs"
                        :class="confirmed 
                            ? 'bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white cursor-pointer shadow-amber-500/20' 
                            : 'bg-slate-200 text-slate-400 cursor-not-allowed border border-slate-300/60'">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Ya, Reset Password</span>
                </button>
            </div>

        </div>
    </div>
</div>
