{{-- ============ KONTAK: chat baru / grup baru / tambah anggota ============ --}}
<div x-show="modal === 'contacts'" x-cloak class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center sm:p-4" @keydown.escape.window="modal = null">
    <div class="absolute inset-0 bg-slate-900/50" @click="modal = null"></div>
    <div class="relative w-full sm:max-w-lg bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl flex flex-col max-h-[90vh]" role="dialog" aria-modal="true" aria-labelledby="chat-contacts-title">
        <div class="px-5 pt-5 pb-3 flex items-start justify-between gap-3 shrink-0">
            <div>
                <h2 id="chat-contacts-title" class="text-base font-extrabold text-slate-900" x-text="{ direct: 'Chat Baru', group: 'Buat Grup Baru', add: 'Tambah Anggota Grup' }[contactMode]"></h2>
                <p class="text-xs text-slate-500 mt-0.5">Hanya pengguna yang terkait dengan kegiatan magang Anda yang ditampilkan.</p>
            </div>
            <button type="button" @click="modal = null" class="w-9 h-9 rounded-xl text-slate-500 hover:bg-slate-100 flex items-center justify-center shrink-0" aria-label="Tutup">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div x-show="contactMode === 'group'" class="px-5 pb-3 space-y-2 shrink-0">
            <input type="text" x-ref="groupTitle" x-model="groupForm.title" maxlength="100" placeholder="Nama grup, mis. Magang Diskominfo Batch Oktober" aria-label="Nama grup"
                   class="w-full h-11 px-3.5 rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
            <textarea x-model="groupForm.description" rows="2" maxlength="300" placeholder="Deskripsi (opsional)" aria-label="Deskripsi grup"
                      class="w-full px-3.5 py-2.5 rounded-xl border-slate-200 text-sm resize-none focus:border-blue-500 focus:ring-blue-500"></textarea>
        </div>

        <div class="px-5 pb-3 shrink-0 space-y-2">
            <input type="search" x-ref="contactSearch" x-model="contactSearch" @input="searchContacts()" placeholder="Cari nama atau email…" aria-label="Cari kontak"
                   class="w-full h-11 px-3.5 rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
            <div x-show="contactMode !== 'direct' && selectedList.length" class="flex flex-wrap gap-1.5">
                <template x-for="contact in selectedList" :key="contact.id">
                    <button type="button" @click="pickContact(contact)" class="inline-flex items-center gap-1.5 h-7 pl-2.5 pr-2 rounded-full bg-blue-50 border border-blue-200 text-xs font-semibold text-blue-700">
                        <span class="max-w-[140px] truncate" x-text="contact.name"></span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </template>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto min-h-0 px-3 pb-3">
            <div x-show="contactsLoading" class="flex justify-center py-8"><span class="w-6 h-6 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></span></div>
            <p x-show="!contactsLoading && contactGroups.length === 0" class="text-center text-sm text-slate-500 py-8">Tidak ada kontak yang cocok.</p>
            <template x-for="group in contactGroups" :key="group.key">
                <div x-show="!contactsLoading" class="mb-2">
                    <p class="px-2 pt-2 pb-1 text-xs font-bold uppercase tracking-wide text-slate-400" x-text="group.label"></p>
                    <template x-for="contact in group.items" :key="contact.id">
                        <button type="button" @click="pickContact(contact)" :disabled="saving" :data-contact-user-id="contact.id"
                                class="w-full flex items-center gap-3 p-2.5 rounded-2xl text-left transition disabled:opacity-60 min-h-[56px]"
                                :class="isSelected(contact) ? 'bg-blue-50' : 'hover:bg-slate-50'">
                            <span class="relative w-10 h-10 rounded-full text-white text-sm font-bold flex items-center justify-center shrink-0" :style="{ backgroundColor: contact.color }">
                                <span x-text="contact.initials"></span>
                                <span x-show="contact.online" class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-bold text-slate-900 truncate" x-text="contact.name"></span>
                                <span class="block text-xs text-slate-500 truncate" x-text="subtitle(contact)"></span>
                            </span>
                            <span x-show="contactMode !== 'direct'" class="w-6 h-6 rounded-full border-2 flex items-center justify-center shrink-0 transition"
                                  :class="isSelected(contact) ? 'bg-blue-600 border-blue-600 text-white' : 'border-slate-300'">
                                <svg x-show="isSelected(contact)" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                        </button>
                    </template>
                </div>
            </template>
        </div>

        <div x-show="contactMode !== 'direct'" class="px-5 py-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 shrink-0">
            <button type="button" @click="modal = null" class="h-11 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold">Batal</button>
            <button type="button" @click="contactMode === 'group' ? createGroup() : addMembers()" :disabled="saving || !selectedList.length"
                    class="h-11 px-5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold shadow-xs disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-text="(contactMode === 'group' ? 'Buat Grup' : 'Tambahkan') + (selectedList.length ? ` (${selectedList.length})` : '')"></span>
            </button>
        </div>
    </div>
</div>

{{-- ============ UBAH GRUP ============ --}}
<div x-show="modal === 'editGroup'" x-cloak class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center sm:p-4" @keydown.escape.window="modal = null">
    <div class="absolute inset-0 bg-slate-900/50" @click="modal = null"></div>
    <form @submit.prevent="saveGroup()" class="relative w-full sm:max-w-md bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl p-5 space-y-3" role="dialog" aria-modal="true" aria-labelledby="chat-edit-title">
        <h2 id="chat-edit-title" class="text-base font-extrabold text-slate-900">Ubah Info Grup</h2>
        <label class="block space-y-1">
            <span class="text-xs font-bold text-slate-600">Nama grup</span>
            <input type="text" x-ref="editTitle" x-model="groupForm.title" maxlength="100" required class="w-full h-11 px-3.5 rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
        </label>
        <label class="block space-y-1">
            <span class="text-xs font-bold text-slate-600">Deskripsi</span>
            <textarea x-model="groupForm.description" rows="3" maxlength="300" class="w-full px-3.5 py-2.5 rounded-xl border-slate-200 text-sm resize-none focus:border-blue-500 focus:ring-blue-500"></textarea>
        </label>
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-1">
            <button type="button" @click="modal = null" class="h-11 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold">Batal</button>
            <button type="submit" :disabled="saving" class="h-11 px-5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold disabled:opacity-50">Simpan</button>
        </div>
    </form>
</div>

{{-- ============ LAPORKAN PESAN ============ --}}
<div x-show="modal === 'report'" x-cloak class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center sm:p-4" @keydown.escape.window="modal = null">
    <div class="absolute inset-0 bg-slate-900/50" @click="modal = null"></div>
    <form @submit.prevent="submitReport()" class="relative w-full sm:max-w-md bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl p-5 space-y-3" role="dialog" aria-modal="true" aria-labelledby="chat-report-title">
        <div>
            <h2 id="chat-report-title" class="text-base font-extrabold text-slate-900">Laporkan Pesan</h2>
            <p class="text-xs text-slate-500 mt-0.5">Salinan pesan dikirim ke Super Admin sebagai tiket untuk ditinjau. Pengirim tidak diberi tahu siapa pelapornya.</p>
        </div>
        <div class="space-y-1.5">
            <template x-for="(label, key) in cfg.reportReasons" :key="key">
                <label class="flex items-center gap-3 p-2.5 rounded-xl border cursor-pointer transition" :class="reportForm.reason === key ? 'border-amber-300 bg-amber-50' : 'border-slate-200 hover:bg-slate-50'">
                    <input type="radio" name="chat-report-reason" :value="key" x-model="reportForm.reason" class="text-amber-600 focus:ring-amber-500">
                    <span class="text-sm font-semibold text-slate-800" x-text="label"></span>
                </label>
            </template>
        </div>
        <textarea x-model="reportForm.note" rows="3" maxlength="1000" :required="reportForm.reason === 'lainnya'"
                  :placeholder="reportForm.reason === 'lainnya' ? 'Jelaskan alasan pelaporan (wajib)' : 'Catatan tambahan (opsional)'"
                  class="w-full px-3.5 py-2.5 rounded-xl border-slate-200 text-sm resize-none focus:border-amber-500 focus:ring-amber-500"></textarea>
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
            <button type="button" @click="modal = null" class="h-11 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold">Batal</button>
            <button type="submit" :disabled="saving || !reportForm.reason" class="h-11 px-5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold disabled:opacity-50">Kirim Laporan</button>
        </div>
    </form>
</div>

{{-- ============ INFO DIBACA (GRUP) ============ --}}
<div x-show="modal === 'readers'" x-cloak class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center sm:p-4" @keydown.escape.window="modal = null">
    <div class="absolute inset-0 bg-slate-900/50" @click="modal = null"></div>
    <div class="relative w-full sm:max-w-md bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl p-5 space-y-4 max-h-[85vh] overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="chat-readers-title">
        <div class="flex items-center justify-between gap-3">
            <h2 id="chat-readers-title" class="text-base font-extrabold text-slate-900">Info Dibaca</h2>
            <button type="button" @click="modal = null" class="w-9 h-9 rounded-xl text-slate-500 hover:bg-slate-100 flex items-center justify-center" aria-label="Tutup">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <p class="text-sm text-slate-600 bg-slate-50 border border-slate-100 rounded-xl p-3 line-clamp-3" x-text="readersFor ? (readersFor.body || 'Lampiran') : ''"></p>
        <div>
            <p class="text-xs font-bold uppercase tracking-wide text-emerald-600 mb-1.5">Dibaca oleh (<span x-text="readers.read.length"></span>)</p>
            <p x-show="!readers.read.length" class="text-xs text-slate-500">Belum ada yang membaca.</p>
            <template x-for="member in readers.read" :key="member.id">
                <p class="flex items-center gap-2 py-1 text-sm text-slate-800"><span class="text-emerald-600 font-bold">✓✓</span><span x-text="member.name"></span></p>
            </template>
        </div>
        <div>
            <p class="text-xs font-bold uppercase tracking-wide text-slate-400 mb-1.5">Belum dibaca (<span x-text="readers.unread.length"></span>)</p>
            <template x-for="member in readers.unread" :key="member.id">
                <p class="flex items-center gap-2 py-1 text-sm text-slate-600"><span class="text-slate-400 font-bold">✓</span><span x-text="member.name"></span></p>
            </template>
        </div>
    </div>
</div>

{{-- ============ KONFIRMASI (pengganti confirm() bawaan browser, LRN-035) ============ --}}
<div x-show="modal === 'confirm'" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4" @keydown.escape.window="modal = null">
    <div class="absolute inset-0 bg-slate-950/60" @click="modal = null"></div>
    <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl p-6 space-y-4" role="alertdialog" aria-modal="true" aria-labelledby="chat-confirm-title">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div class="min-w-0">
                <h2 id="chat-confirm-title" class="text-base font-extrabold text-slate-900" x-text="confirmBox.title"></h2>
                <p class="text-sm text-slate-600 mt-1" x-text="confirmBox.message"></p>
            </div>
        </div>
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
            <button type="button" @click="modal = null" class="h-11 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold">Batal</button>
            <button type="button" @click="runConfirm()" :disabled="saving" class="h-11 px-5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold disabled:opacity-50" x-text="confirmBox.label"></button>
        </div>
    </div>
</div>
