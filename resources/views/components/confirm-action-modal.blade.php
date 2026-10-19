<!-- Global Confirmation Modal untuk aksi non-destruktif (Alpine.js) -->
<!--
    Pemakaian:
    $dispatch('open-confirm-modal', {
        action: '/url',            // form tersembunyi dikirim ke URL ini (POST), atau
        form: $el.closest('form'), // kirim form yang sudah ada (ikut membawa semua field-nya)
        submitter: $el,            // opsional: tombol submit (name/value ikut terkirim)
        title: 'Buat Akun Admin',
        message: 'Penjelasan singkat dampak tindakan.',
        label: 'Objek tindakan:', name: 'Nama Objek', desc: 'Keterangan tambahan',
        confirmText: 'Ya, Buat Akun',
        tone: 'blue'               // blue | emerald | amber | rose
    })
-->
<div x-data="{
    open: false,
    action: '',
    form: null,
    submitter: null,
    title: 'Konfirmasi Tindakan',
    message: '',
    label: 'Objek tindakan:',
    name: '',
    desc: '',
    confirmText: 'Ya, Lanjutkan',
    tone: 'blue',
    tones: {
        blue:    { icon: 'bg-blue-50 border-blue-100 text-blue-600',          btn: 'bg-blue-600 hover:bg-blue-700 active:bg-blue-800 shadow-blue-600/20' },
        emerald: { icon: 'bg-emerald-50 border-emerald-100 text-emerald-600', btn: 'bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 shadow-emerald-600/20' },
        amber:   { icon: 'bg-amber-50 border-amber-100 text-amber-600',       btn: 'bg-amber-500 hover:bg-amber-600 active:bg-amber-700 shadow-amber-500/20' },
        rose:    { icon: 'bg-rose-50 border-rose-100 text-rose-600',          btn: 'bg-rose-600 hover:bg-rose-700 active:bg-rose-800 shadow-rose-600/20' },
    },
    openModal(data) {
        this.action = data.action || '';
        this.form = data.form || null;
        this.submitter = data.submitter || null;
        this.title = data.title || 'Konfirmasi Tindakan';
        this.message = data.message || '';
        this.label = data.label || 'Objek tindakan:';
        this.name = data.name || '';
        this.desc = data.desc || '';
        this.confirmText = data.confirmText || 'Ya, Lanjutkan';
        this.tone = this.tones[data.tone] ? data.tone : 'blue';
        this.open = true;
        this.$nextTick(() => {
            if (this.$refs.cancelBtn) this.$refs.cancelBtn.focus();
        });
    },
    closeModal() {
        this.open = false;
        this.form = null;
        this.submitter = null;
    },
    submitAction() {
        if (this.form) {
            // form.submit() tidak memicu event submit lagi, jadi modal tidak terbuka ulang.
            const form = this.form, submitter = this.submitter;
            this.closeModal();
            if (submitter && submitter.name) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = submitter.name;
                hidden.value = submitter.value;
                form.appendChild(hidden);
            }
            form.submit();
            return;
        }
        if (this.action) this.$refs.actionForm.submit();
    }
}" @open-confirm-modal.window="openModal($event.detail)" @keydown.escape.window="if(open) closeModal()" x-cloak>
    <!-- Modal Backdrop -->
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 sm:p-6"
        style="z-index: 99998;">

        <!-- Modal Card -->
        <div x-show="open" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" @click.outside="closeModal()"
            class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-md w-full overflow-hidden p-6 sm:p-7 space-y-5 text-left relative"
            role="dialog" aria-modal="true" :aria-label="title">

            <!-- Hidden Form untuk aksi berbasis URL (POST) -->
            <form x-ref="actionForm" :action="action" method="POST" class="hidden">
                @csrf
            </form>

            <!-- Header: Icon + Title -->
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl border flex items-center justify-center shrink-0 shadow-xs"
                     :class="tones[tone].icon">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1 space-y-1">
                    <h3 class="text-base font-black text-slate-900 leading-tight" x-text="title"></h3>
                    <p class="text-xs text-slate-500 leading-relaxed" x-show="message" x-text="message"></p>
                </div>
                <button type="button" @click="closeModal()"
                    class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition" title="Tutup">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Target Detail Box -->
            <template x-if="name">
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block" x-text="label"></span>
                    <div class="font-black text-sm text-slate-900 break-words" x-text="name"></div>
                    <template x-if="desc">
                        <div class="text-xs text-slate-500 font-medium break-words mt-0.5" x-text="desc"></div>
                    </template>
                </div>
            </template>

            <!-- Action Buttons -->
            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center sm:justify-end gap-2.5 pt-2 border-t border-slate-100">
                <button type="button" x-ref="cancelBtn" @click="closeModal()"
                    class="w-full sm:w-auto px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition text-center cursor-pointer">
                    Batal
                </button>
                <button type="button" @click="submitAction()"
                    class="w-full sm:w-auto px-6 py-2.5 text-xs font-bold rounded-xl transition flex items-center justify-center gap-2 shadow-sm text-white cursor-pointer"
                    :class="tones[tone].btn">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span x-text="confirmText"></span>
                </button>
            </div>

        </div>
    </div>
</div>
