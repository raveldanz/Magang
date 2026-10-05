{{-- Satu gelembung pesan (dirender di dalam <template x-for="item in timeline">) --}}
<div :id="`chat-msg-${item.m.id}`" :data-message-id="item.m.id" class="group/msg flex items-end gap-2 transition" :class="[isChannelChat ? 'justify-start w-full' : (item.m.is_mine ? 'justify-end' : 'justify-start'), item.grouped ? 'mt-1' : (isChannelChat ? 'mt-4' : 'mt-3')]">
    <template x-if="isGroupChat && !item.m.is_mine">
        <span class="w-8 h-8 rounded-full text-white text-xs font-bold flex items-center justify-center shrink-0 self-start mt-0.5"
              :class="item.grouped ? 'invisible' : ''" :style="{ backgroundColor: item.m.sender ? item.m.sender.color : '#94a3b8' }"
              x-text="item.m.sender ? item.m.sender.initials : '?'"></span>
    </template>
    <template x-if="isChannelChat">
        <div class="w-10 h-10 rounded-xl border border-slate-200/90 bg-white p-1 shrink-0 self-start mt-0.5 shadow-2xs overflow-hidden">
            <template x-if="active && active.avatar && active.avatar.logo_url">
                <img :src="active.avatar.logo_url" :alt="active.title" class="w-full h-full object-contain">
            </template>
            <template x-if="!active || !active.avatar || !active.avatar.logo_url">
                <span class="w-full h-full flex items-center justify-center text-blue-600 font-bold text-xs" x-text="active && active.avatar ? active.avatar.initials : 'P'"></span>
            </template>
        </div>
    </template>

    <div class="flex items-center gap-1 min-w-0" :class="isChannelChat ? 'w-full max-w-2xl' : (item.m.is_mine ? 'flex-row-reverse max-w-[88%] sm:max-w-[72%]' : 'max-w-[88%] sm:max-w-[72%]')">
        <div class="rounded-2xl shadow-2xs min-w-0 flex-1 transition" :class="[bubbleClass(item.m), isChannelChat ? 'p-0 overflow-hidden' : 'px-3 py-2']">
            {{-- Header Pengumuman Resmi Saluran --}}
            <template x-if="isChannelChat && !item.m.deleted">
                <div class="flex items-center justify-between gap-2 p-3 sm:px-4 sm:pt-3.5 sm:pb-2 border-b border-slate-100 bg-slate-50/50">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="text-xs font-bold text-slate-900 truncate" x-text="active ? active.title : 'Saluran Pengumuman'"></span>
                        <span x-show="active && active.scope_badge" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 shrink-0" x-text="active ? `[${active.scope_badge}]` : ''"></span>
                    </div>
                    <span class="text-[11px] text-slate-400 shrink-0" x-text="item.time"></span>
                </div>
            </template>

            <div :class="isChannelChat ? 'p-3.5 sm:p-4 min-w-0' : 'min-w-0'">
                <p x-show="!isChannelChat && item.showName && !item.m.deleted" class="text-xs font-bold mb-0.5 truncate" :style="{ color: item.m.sender ? item.m.sender.color : '#475569' }" x-text="item.m.sender ? item.m.sender.name : ''"></p>

                <template x-if="item.m.deleted">
                    <p class="text-sm italic flex items-center gap-1.5">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        Pesan ini telah dihapus
                    </p>
                </template>

                <template x-if="!item.m.deleted">
                    <div class="min-w-0">
                        {{-- Kutipan pesan yang dibalas --}}
                        <template x-if="item.m.reply_to">
                            <button type="button" @click="scrollToMessage(item.m.reply_to.id)"
                                    class="block w-full text-left mb-1.5 px-2.5 py-1.5 rounded-lg border-l-4 text-xs"
                                    :class="item.m.is_mine ? 'bg-white/15 border-white/70' : 'bg-slate-50 border-blue-400'">
                                <span class="block font-bold truncate" x-text="item.m.reply_to.sender_name || 'Pesan'"></span>
                                <span class="block truncate opacity-90" x-text="item.m.reply_to.preview"></span>
                            </button>
                        </template>

                        {{-- Foto (grid bila lebih dari satu) --}}
                        <template x-if="item.images.length">
                            <div class="grid gap-1 mb-1 -mx-1 -mt-0.5" :class="item.images.length > 1 ? 'grid-cols-2 w-64 sm:w-72' : 'grid-cols-1'">
                                <template x-for="(img, idx) in item.images" :key="img.id">
                                    <button type="button" @click="openLightbox(item.images, idx)" class="block rounded-xl overflow-hidden bg-slate-100" :class="item.images.length > 1 ? 'aspect-square' : ''" aria-label="Perbesar foto">
                                        <img :src="img.url" :alt="img.name" loading="lazy" @load="onMediaLoad()"
                                             :class="item.images.length > 1 ? 'w-full h-full object-cover' : 'max-h-72 w-auto max-w-full object-contain'">
                                    </button>
                                </template>
                            </div>
                        </template>

                        {{-- Video, audio/voice note, dokumen --}}
                        <template x-for="att in item.others" :key="att.id">
                            <div class="mb-1">
                                <template x-if="att.kind === 'video'">
                                    <video :src="att.url" controls preload="metadata" @loadedmetadata="onMediaLoad()" class="rounded-xl max-h-72 max-w-full bg-black"></video>
                                </template>
                                <template x-if="att.kind === 'audio'">
                                    <div class="flex items-center gap-2 min-w-[230px] py-0.5">
                                        <span class="w-9 h-9 rounded-full flex items-center justify-center shrink-0" :class="item.m.is_mine ? 'bg-white/20' : 'bg-blue-50 text-blue-600'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                                        </span>
                                        <audio :src="att.url" controls preload="metadata" class="h-10 w-full max-w-[240px]"></audio>
                                    </div>
                                </template>
                                <template x-if="att.kind === 'document'">
                                    <a :href="att.local ? null : (att.mime === 'application/pdf' ? att.url : att.download_url)"
                                       :target="att.mime === 'application/pdf' ? '_blank' : null" rel="noopener"
                                       class="flex items-center gap-3 p-2.5 rounded-xl min-w-[210px] transition"
                                       :class="item.m.is_mine ? 'bg-white/15 hover:bg-white/25' : 'bg-slate-50 hover:bg-slate-100 border border-slate-200'">
                                        <span class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" :class="item.m.is_mine ? 'bg-white/20' : 'bg-blue-50 text-blue-600'">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block text-sm font-semibold truncate" x-text="att.name"></span>
                                            <span class="block text-xs" :class="item.m.is_mine ? 'text-blue-100' : 'text-slate-500'" x-text="att.size_label"></span>
                                        </span>
                                        <svg x-show="!att.local" class="w-4 h-4 shrink-0 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    </a>
                                </template>
                            </div>
                        </template>

                        {{-- Teks (x-text: aman dari XSS; tautan http/https dapat diklik) --}}
                        <p x-show="item.parts.length" class="text-sm leading-relaxed break-words [overflow-wrap:anywhere]"><template x-for="(part, i) in item.parts" :key="i"><a :href="part.link ? part.v : null" :target="part.link ? '_blank' : null" :rel="part.link ? 'noopener noreferrer' : null" class="whitespace-pre-wrap" :class="part.link ? (item.m.is_mine ? 'underline underline-offset-2 text-white' : 'underline underline-offset-2 text-blue-600') : ''" x-text="part.v"></a></template></p>
                    </div>
                </template>

                {{-- Status baca & waktu (non-channel) --}}
                <div x-show="!isChannelChat" class="flex items-center justify-end gap-1 mt-0.5 text-xs" :class="item.m.deleted ? 'text-slate-400' : (item.m.is_mine ? 'text-blue-100' : 'text-slate-400')">
                    <span x-text="item.time"></span>
                    <template x-if="item.m.is_mine && item.m.pending">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-label="Mengirim"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                    <template x-if="item.m.is_mine && !item.m.pending && !item.m.failed && !item.m.deleted">
                        <button type="button" @click="isGroupChat && openReaders(item.m)" class="font-bold tracking-tighter"
                                :class="[readCount(item.m) > 0 ? 'text-sky-200' : '', isGroupChat ? 'cursor-pointer hover:underline' : 'cursor-default']"
                                :title="readLabel(item.m)" :aria-label="readLabel(item.m)">
                            <span x-text="readCount(item.m) > 0 ? '✓✓' : '✓'"></span><span x-show="isGroupChat && readCount(item.m) > 0" class="ml-0.5 font-semibold tracking-normal" x-text="readCount(item.m)"></span>
                        </button>
                    </template>
                </div>

                <template x-if="item.m.failed">
                    <div class="mt-1.5 pt-1.5 border-t border-white/25 flex items-center gap-3 text-xs font-semibold">
                        <span class="text-rose-100">Gagal terkirim</span>
                        <button type="button" @click="deliver(item.m.id)" class="underline">Coba lagi</button>
                        <button type="button" @click="discard(item.m.id)" class="underline">Batalkan</button>
                    </div>
                </template>
            </div>

            {{-- Bar Komentar Terintegrasi (Gaya Telegram Channel) --}}
            <template x-if="isChannelChat && !item.m.deleted">
                <button type="button" @click="openComments(item.m)" data-test="open-comments"
                        class="channel-comment-btn w-full hover:bg-blue-50/70 text-blue-700 font-medium text-xs py-2.5 px-3 sm:px-4 flex items-center justify-between cursor-pointer transition border-t border-slate-100 bg-slate-50/40 select-none">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 shrink-0 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span x-text="(item.m.comments_count && item.m.comments_count > 0) ? `${item.m.comments_count} Komentar` : 'Kirim Komentar'"></span>
                    </div>
                    <svg class="w-4 h-4 shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </template>
        </div>

        <button type="button" x-show="!item.m.pending && !item.m.deleted && typeof item.m.id === 'number'" @click.stop="openMenu($event, item.m)"
                class="shrink-0 w-8 h-8 rounded-full text-slate-400 hover:text-slate-700 hover:bg-white flex items-center justify-center opacity-0 group-hover/msg:opacity-100 focus:opacity-100 [@media(pointer:coarse)]:opacity-70 transition"
                aria-label="Aksi pesan" title="Balas, salin, hapus, laporkan">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M6 10a2 2 0 110 4 2 2 0 010-4zm6 0a2 2 0 110 4 2 2 0 010-4zm6 0a2 2 0 110 4 2 2 0 010-4z"/></svg>
        </button>
    </div>
</div>
