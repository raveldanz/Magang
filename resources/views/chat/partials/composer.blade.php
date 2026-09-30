{{-- Penulis pesan: teks, balasan, lampiran (maks. 5), voice note --}}
<div class="bg-white border-t border-slate-200/80 px-3 sm:px-4 pt-3 pb-3 shrink-0">
    <div x-show="sessionExpired" class="mb-2 p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium flex items-center justify-between gap-3">
        <span>Sesi Anda berakhir. Muat ulang halaman untuk melanjutkan chat.</span>
        <button type="button" @click="location.reload()" class="font-bold underline whitespace-nowrap">Muat ulang</button>
    </div>
    <div x-show="readOnly" class="mb-2 p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-medium">
        Mode Login As: chat hanya dapat dibaca, dan pesan yang dibuka tidak ditandai dibaca untuk akun ini. Kembali ke akun Anda untuk mengirim pesan.
    </div>
    <div x-show="active && active.can_send === false" class="mb-2 p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-600 text-xs font-medium">
        Akun ini sudah dinonaktifkan, sehingga tidak dapat menerima pesan baru.
    </div>

    {{-- Sedang membalas --}}
    <div x-show="replyTo" x-cloak class="mb-2 flex items-center gap-3 p-2.5 rounded-xl bg-blue-50 border border-blue-100">
        <span class="w-1 self-stretch rounded-full bg-blue-500 shrink-0"></span>
        <div class="min-w-0 flex-1">
            <p class="text-xs font-bold text-blue-700">Membalas <span x-text="replyTo ? replyTo.sender_name : ''"></span></p>
            <p class="text-xs text-slate-600 truncate" x-text="replyTo ? replyTo.preview : ''"></p>
        </div>
        <button type="button" @click="replyTo = null" class="w-8 h-8 rounded-lg text-slate-500 hover:bg-blue-100 flex items-center justify-center shrink-0" aria-label="Batal membalas">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    {{-- Lampiran yang akan dikirim --}}
    <div x-show="files.length || preparingFiles" x-cloak class="mb-2">
        <div class="flex gap-2 overflow-x-auto pb-1">
            <template x-for="f in files" :key="f.id">
                <div class="relative flex items-center gap-2 p-1.5 pr-8 rounded-xl bg-slate-50 border border-slate-200 shrink-0 max-w-[220px]">
                    <template x-if="f.preview"><img :src="f.preview" alt="Pratinjau lampiran" class="w-11 h-11 rounded-lg object-cover shrink-0"></template>
                    <template x-if="!f.preview">
                        <span class="w-11 h-11 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 text-lg" x-text="{ video: '🎬', audio: '🎵', document: '📄' }[f.kind] || '📄'"></span>
                    </template>
                    <span class="min-w-0">
                        <span class="block text-xs font-semibold text-slate-800 truncate" x-text="f.name"></span>
                        <span class="block text-xs text-slate-500" x-text="`${(f.size / 1048576).toFixed(2).replace('.', ',')} MB`"></span>
                    </span>
                    <button type="button" x-show="!sending" @click="removeFile(f.id)" class="absolute top-1 right-1 w-6 h-6 rounded-md text-slate-500 hover:bg-slate-200 flex items-center justify-center" aria-label="Hapus lampiran">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>
            <div x-show="preparingFiles" class="flex items-center gap-2 px-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 shrink-0 h-14">
                <span class="w-4 h-4 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></span> Menyiapkan file…
            </div>
        </div>
        <p class="text-xs text-slate-500 mt-1">
            <span x-text="`${files.length}/${cfg.maxFiles} lampiran`"></span>
            <span x-show="cfg.maxTotalKb && totalBytes > cfg.maxTotalKb * 1024" class="text-rose-600 font-semibold"> · total melebihi batas unggah server</span>
        </p>
    </div>

    <div x-show="sending && uploadProgress > 0 && uploadProgress < 100" class="mb-2 h-1.5 rounded-full bg-slate-200 overflow-hidden">
        <div class="h-full bg-blue-600 transition-all" :style="{ width: uploadProgress + '%' }"></div>
    </div>

    {{-- Sedang merekam voice note --}}
    <div x-show="recording" x-cloak class="flex items-center gap-3 h-11 px-3 rounded-xl bg-rose-50 border border-rose-200">
        <span class="relative flex w-3 h-3 shrink-0"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span><span class="relative inline-flex rounded-full w-3 h-3 bg-rose-500"></span></span>
        <span class="text-sm font-semibold text-rose-700 flex-1">Merekam <span x-text="formatDuration(recordSeconds)"></span> <span class="text-xs font-normal text-rose-500" x-text="`/ ${formatDuration(cfg.voiceMaxSeconds)}`"></span></span>
        <button type="button" @click="stopRecording(false)" class="h-8 px-3 rounded-lg text-xs font-bold text-slate-600 hover:bg-white" aria-label="Batalkan rekaman">Batal</button>
        <button type="button" @click="stopRecording(true)" aria-label="Kirim pesan suara" class="h-8 px-3 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg> Kirim
        </button>
    </div>

    <form x-show="!recording" @submit.prevent="send()" class="flex items-end gap-2">
        <input type="file" x-ref="fileInput" class="hidden" multiple :accept="acceptAttr" @change="onFilesChosen($event)">
        <button type="button" @click="pickFiles()" :disabled="!canSend || files.length >= cfg.maxFiles"
                class="w-11 h-11 rounded-xl border border-slate-200 text-slate-600 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50 flex items-center justify-center shrink-0 transition disabled:opacity-40 disabled:cursor-not-allowed"
                title="Lampirkan foto, video, audio, atau dokumen" aria-label="Lampirkan file">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
        </button>
        <textarea x-ref="composer" x-model="draft" rows="1" :maxlength="cfg.maxBodyLength" :disabled="!canSend"
                  @keydown="onKeydown($event)" @input="onInput($event)" @paste="onPaste($event)"
                  :placeholder="replyTo ? 'Tulis balasan…' : 'Tulis pesan…'" aria-label="Tulis pesan"
                  class="flex-1 min-w-0 resize-none rounded-xl border-slate-200 text-sm leading-relaxed py-2.5 max-h-40 focus:border-blue-500 focus:ring-blue-500 disabled:bg-slate-50"></textarea>
        <template x-if="draft.trim() || files.length || !voiceSupported">
            <button type="submit" :disabled="!canSend || sending || preparingFiles || (!draft.trim() && !files.length)"
                    class="w-11 h-11 rounded-xl bg-blue-600 hover:bg-blue-700 text-white flex items-center justify-center shrink-0 shadow-xs transition active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed"
                    aria-label="Kirim pesan">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            </button>
        </template>
        <template x-if="!draft.trim() && !files.length && voiceSupported">
            <button type="button" @click="startRecording()" :disabled="!canSend || sending"
                    class="w-11 h-11 rounded-xl bg-blue-600 hover:bg-blue-700 text-white flex items-center justify-center shrink-0 shadow-xs transition active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed"
                    aria-label="Rekam pesan suara" title="Rekam pesan suara (voice note)">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
            </button>
        </template>
    </form>
    <p class="hidden sm:block mt-1.5 text-xs text-slate-400">Enter untuk kirim · Shift+Enter baris baru · Maks. <span x-text="cfg.maxFiles"></span> lampiran, <span x-text="maxUploadLabel"></span> per file (foto besar dikompres otomatis)</p>
</div>
