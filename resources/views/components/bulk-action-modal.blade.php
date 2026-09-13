<!-- Global Triple-Confirmation Bulk Action Modal (Alpine.js) -->
<div x-data="{
    open: false,
    mode: 'delete', // 'delete' or 'reset'
    action: '',
    title: 'Konfirmasi Aksi Massal',
    users: [],
    userIds: [],
    confirmedStep2: false,
    inputKeyword: '',
    get requiredKeyword() {
        return this.mode === 'delete' ? 'HAPUS SEMUA' : 'RESET SEMUA';
    },
    get isValid() {
        return this.confirmedStep2 && (this.inputKeyword.trim().toUpperCase() === this.requiredKeyword);
    },
    openModal(data) {
        this.action = data.action || '';
        this.mode = data.mode || 'delete';
        this.title = data.title || (this.mode === 'delete' ? 'Konfirmasi Hapus Massal' : 'Konfirmasi Reset Password Massal');
        this.users = data.users || [];
        this.userIds = data.userIds || [];
        this.confirmedStep2 = false;
        this.inputKeyword = '';
        this.open = true;
        this.$nextTick(() => {
            if (this.$refs.cancelBtn) this.$refs.cancelBtn.focus();
        });
    },
    closeModal() {
        this.open = false;
        this.users = [];
        this.userIds = [];
        this.confirmedStep2 = false;
        this.inputKeyword = '';
    },
    submitBulk() {
        if (!this.isValid) return;
        this.$refs.bulkForm.submit();
    }
}"
@open-bulk-modal.window="openModal($event.detail)"
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
         class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 flex items-center justify-center p-4 sm:p-6"
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
             class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-lg w-full overflow-hidden p-6 sm:p-7 space-y-5 text-left relative max-h-[92vh] flex flex-col">
            
            <!-- Hidden Form to execute POST bulk -->
            <form x-ref="bulkForm" :action="action" method="POST" class="hidden">
                @csrf
                <template x-for="id in userIds" :key="id">
                    <input type="hidden" name="user_ids[]" :value="id">
                </template>
            </form>

            <!-- Header -->
            <div class="flex items-start gap-3.5 shrink-0">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 shadow-xs"
                     :class="mode === 'delete' ? 'bg-rose-50 border border-rose-100 text-rose-600' : 'bg-amber-50 border border-amber-100 text-amber-600'">
                    <template x-if="mode === 'delete'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </template>
                    <template x-if="mode === 'reset'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </template>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider"
                              :class="mode === 'delete' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800'">
                            Tindakan Massal Krusial
                        </span>
                    </div>
                    <h3 class="text-base sm:text-lg font-black text-slate-900 leading-tight truncate" x-text="title"></h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Tindakan ini memerlukan <strong>3 lapis verifikasi keamanan</strong> untuk mencegah kesalahan fatal.
                    </p>
                </div>
                <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition" title="Tutup">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Scrollable Content Body -->
            <div class="flex-1 overflow-y-auto space-y-4 pr-1">
                
                <!-- LAPIS 1: Audit & Preview Affected Users -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/90 space-y-2">
                    <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                        <span class="flex items-center gap-1.5 text-slate-800 font-extrabold">
                            <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-[10px]">1</span>
                            Daftar Akun Terdampak
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black"
                              :class="mode === 'delete' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-800'">
                            <span x-text="userIds.length"></span> Pengguna Dipilih
                        </span>
                    </div>
                    
                    <!-- Scrollable chip list -->
                    <div class="max-h-28 overflow-y-auto divide-y divide-slate-100 bg-white rounded-xl border border-slate-200/80 p-2 text-xs">
                        <template x-for="user in users" :key="user.id">
                            <div class="py-1.5 px-2 flex items-center justify-between gap-2 hover:bg-slate-50 transition">
                                <div class="truncate">
                                    <span class="font-bold text-slate-800" x-text="user.name"></span>
                                    <span class="text-slate-400 text-[11px] ml-1" x-text="'<' + user.email + '>'"></span>
                                </div>
                                <span class="shrink-0 text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 uppercase" x-text="user.role"></span>
                            </div>
                        </template>
                    </div>

                    <p class="text-[11px] text-slate-500 leading-snug" x-show="mode === 'delete'">
                        * Akun dengan relasi data magang aktif (pengajuan / bimbingan) akan otomatis dilewati sistem demi integritas basis data.
                    </p>
                    <p class="text-[11px] text-slate-500 leading-snug" x-show="mode === 'reset'">
                        * Seluruh kata sandi akun di atas akan diubah seketika menjadi: <code class="font-mono font-bold text-amber-800 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">password</code>
                    </p>
                </div>

                <!-- LAPIS 2: Pernyataan & Kesadaran Risiko Checkbox -->
                <div class="space-y-1.5">
                    <div class="flex items-center gap-1.5 text-xs font-extrabold text-slate-800">
                        <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-[10px]">2</span>
                        Pernyataan Tanggung Jawab
                    </div>
                    <label class="flex items-start gap-3 p-3.5 rounded-2xl border transition select-none cursor-pointer"
                           :class="confirmedStep2 
                               ? (mode === 'delete' ? 'bg-rose-50/80 border-rose-300' : 'bg-amber-50/80 border-amber-300') 
                               : 'bg-white border-slate-200 hover:bg-slate-50'">
                        <input type="checkbox" 
                               x-model="confirmedStep2" 
                               class="mt-0.5 w-4 h-4 rounded cursor-pointer shrink-0"
                               :class="mode === 'delete' ? 'text-rose-600 border-rose-300 focus:ring-rose-500' : 'text-amber-600 border-amber-300 focus:ring-amber-500'">
                        <span class="text-xs font-semibold leading-relaxed"
                              :class="mode === 'delete' ? 'text-rose-950' : 'text-amber-950'">
                            Saya menyatakan bertanggung jawab penuh dan memahami bahwa aksi ini akan berdampak serentak pada <strong x-text="userIds.length + ' akun pengguna'"></strong>.
                        </span>
                    </label>
                </div>

                <!-- LAPIS 3: Ketik Teks Konfirmasi Tepat -->
                <div class="space-y-2">
                    <div class="flex items-center gap-1.5 text-xs font-extrabold text-slate-800">
                        <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-[10px]">3</span>
                        Ketik Teks Konfirmasi
                    </div>
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                        <p class="text-xs text-slate-600 leading-snug">
                            Ketik kata kunci tepat berikut untuk membuka kunci tombol eksekusi:
                        </p>
                        <div class="flex items-center gap-2">
                            <span class="select-all font-mono font-black text-sm tracking-wider px-3 py-1 rounded-xl border shadow-2xs"
                                  :class="mode === 'delete' ? 'bg-rose-100/70 border-rose-300 text-rose-800' : 'bg-amber-100/70 border-amber-300 text-amber-900'"
                                  x-text="requiredKeyword"></span>
                            <span class="text-[11px] text-slate-400 italic">(huruf kapital)</span>
                        </div>
                        <div class="relative">
                            <input type="text"
                                   x-model="inputKeyword"
                                   @input="inputKeyword = $event.target.value.toUpperCase()"
                                   :placeholder="'Ketik \'' + requiredKeyword + '\' di sini...'"
                                   class="w-full px-3.5 py-2 text-xs font-mono font-bold rounded-xl border transition uppercase tracking-wider pr-10"
                                   :class="inputKeyword.trim().toUpperCase() === requiredKeyword 
                                       ? 'border-emerald-400 ring-2 ring-emerald-500/20 bg-emerald-50/30 text-emerald-900' 
                                       : 'border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20'">
                            <div x-show="inputKeyword.trim().toUpperCase() === requiredKeyword" 
                                 class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-emerald-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer Action Buttons -->
            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center sm:justify-end gap-2.5 pt-3 border-t border-slate-100 shrink-0">
                <button type="button" 
                        x-ref="cancelBtn"
                        @click="closeModal()" 
                        class="w-full sm:w-auto px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition text-center cursor-pointer">
                    Batal
                </button>
                <button type="button" 
                        :disabled="!isValid" 
                        @click="submitBulk()" 
                        class="w-full sm:w-auto px-6 py-2.5 text-xs font-bold rounded-xl transition flex items-center justify-center gap-2 shadow-sm"
                        :class="isValid 
                            ? (mode === 'delete' 
                                ? 'bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white cursor-pointer shadow-rose-600/20' 
                                : 'bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white cursor-pointer shadow-amber-500/20') 
                            : 'bg-slate-200 text-slate-400 cursor-not-allowed border border-slate-300/60'">
                    <template x-if="mode === 'delete'">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </template>
                    <template x-if="mode === 'reset'">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </template>
                    <span x-text="mode === 'delete' ? 'Eksekusi Hapus Massal' : 'Eksekusi Reset Massal'"></span>
                </button>
            </div>

        </div>
    </div>
</div>
