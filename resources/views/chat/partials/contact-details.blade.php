{{-- Detail kontak satu orang (variabel Alpine `person`, berisi `details` dari ChatPresenter::contactDetails).
     Email, telepon & data studi hanya dikirim server bila ada hubungan magang langsung. --}}
<div class="space-y-2">
    <template x-if="person.details && person.details.visible">
        <div class="rounded-xl border border-slate-200 divide-y divide-slate-100 text-sm bg-white">
            <div class="p-3 flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-xs text-slate-400">Email</p>
                    <a :href="`mailto:${person.details.email}`" class="font-semibold text-blue-700 hover:underline break-all" x-text="person.details.email"></a>
                </div>
                <button type="button" @click="copyValue(person.details.email, 'Email')" class="h-8 px-2.5 rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-100 shrink-0" :aria-label="`Salin email ${person.name}`">Salin</button>
            </div>
            <div class="p-3">
                <p class="text-xs text-slate-400">Telepon / WhatsApp</p>
                <template x-if="person.details.phone">
                    <div class="flex items-center justify-between gap-2">
                        <a :href="`tel:${person.details.phone}`" class="font-semibold text-slate-800 hover:text-blue-700" x-text="person.details.phone"></a>
                        <div class="flex items-center gap-1 shrink-0">
                            <a x-show="person.details.whatsapp_url" :href="person.details.whatsapp_url" target="_blank" rel="noopener noreferrer"
                               class="h-8 px-2.5 rounded-lg text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 inline-flex items-center">WhatsApp</a>
                            <button type="button" @click="copyValue(person.details.phone, 'Nomor telepon')" class="h-8 px-2.5 rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-100">Salin</button>
                        </div>
                    </div>
                </template>
                <template x-if="!person.details.phone">
                    <p class="text-slate-400 italic">Belum diisi</p>
                </template>
            </div>
            <template x-for="field in person.details.fields" :key="field.label">
                <div class="p-3">
                    <p class="text-xs text-slate-400" x-text="field.label"></p>
                    <p class="font-semibold text-slate-800 break-words" x-text="field.value"></p>
                </div>
            </template>
        </div>
    </template>

    <template x-if="person.details && !person.details.visible">
        <div class="flex items-start gap-2.5 p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 leading-relaxed">
            <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <span>Email, nomor telepon, dan data studi disembunyikan. Informasi pribadi hanya terlihat oleh pihak yang memiliki hubungan magang langsung (mentor &amp; dosen pembimbing penempatan, admin dinas tujuan, admin kampus, dan Super Admin).</span>
        </div>
    </template>

    <template x-if="person.details && person.details.institution">
        <div class="rounded-xl border border-blue-100 bg-blue-50/60 p-3 text-sm space-y-1">
            <p class="text-xs font-bold text-blue-700" x-text="person.details.institution.label"></p>
            <p class="font-semibold text-slate-800" x-text="person.details.institution.name"></p>
            <a x-show="person.details.institution.phone" :href="`tel:${person.details.institution.phone}`" class="flex items-center gap-1.5 text-slate-700 hover:text-blue-700"><svg class="w-3.5 h-3.5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg><span x-text="person.details.institution.phone"></span></a>
            <a x-show="person.details.institution.email" :href="`mailto:${person.details.institution.email}`" class="flex items-center gap-1.5 text-slate-700 hover:text-blue-700 break-all"><svg class="w-3.5 h-3.5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg><span x-text="person.details.institution.email"></span></a>
            <p x-show="person.details.institution.address" class="text-xs text-slate-500 leading-relaxed" x-text="person.details.institution.address"></p>
        </div>
    </template>
</div>
