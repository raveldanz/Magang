// Halaman chat (resources/views/chat/index.blade.php) — komponen Alpine `chatApp`.
// Pembaruan memakai polling adaptif (config/chat.php → poll), tanpa server WebSocket.
import { chatFetch, sendJson, uploadForm } from './api';
import { dateKey, dateLabel, extensionOf, formatBytes, formatDuration, formatTime, lastSeenLabel, linkify, listTime, shortName } from './format';
import { compressImageIfNeeded, createVoiceRecorder, voiceRecordingSupported } from './media';

const ROLE_GROUPS = [
    ['super_admin', 'Super Admin / Helpdesk'],
    ['agency', 'Admin Dinas'],
    ['mentor', 'Mentor Lapangan'],
    ['lecturer', 'Dosen Pembimbing (DPL)'],
    ['campus', 'Admin Kampus'],
    ['student', 'Mahasiswa'],
    ['other', 'Lainnya'],
];

const EMPTY_STATE = { read_state: {}, typing: [], presence: null, meta_version: 0 };
let fileSeq = 0;

function kindOf(file) {
    if (file.type.startsWith('image/')) return 'image';
    if (file.type.startsWith('video/')) return 'video';
    if (file.type.startsWith('audio/')) return 'audio';
    return 'document';
}

function messagePreview(m) {
    if (m.deleted) return 'Pesan ini telah dihapus';
    if (m.body) return m.body.length > 90 ? `${m.body.slice(0, 90)}…` : m.body;
    const first = (m.attachments || [])[0];
    if (!first) return '';
    return { image: '📷 Foto', video: '🎬 Video', audio: '🎤 Pesan suara' }[first.kind] || `📎 ${first.name}`;
}

export function chatApp(config) {
    return {
        cfg: config,
        readOnly: !!config.readOnly,
        ROLE_GROUPS,

        // Daftar percakapan
        conversations: [],
        convLoading: true,
        search: '',
        listFilter: 'all',

        // Percakapan aktif
        activeId: null,
        active: null,
        state: { ...EMPTY_STATE },
        messages: [],
        hasMore: false,
        loadingMessages: false,
        loadingOlder: false,
        pending: [],

        // Penulis pesan
        draft: '',
        files: [],
        preparingFiles: false,
        sending: false,
        uploadProgress: 0,
        replyTo: null,
        recording: false,
        recordSeconds: 0,

        // Tampilan
        newBelow: 0,
        stickToBottom: true,
        infoOpen: false,
        media: [],
        mediaLoading: false,
        menu: null,
        lightbox: null,
        dragging: false,

        // Utas komentar saluran (Telegram Channel comments)
        commentsDrawer: {
            open: false,
            parentMessage: null,
            comments: [],
            loading: false,
            sending: false,
            draft: '',
        },

        // Modal: contacts | editGroup | report | readers | confirm
        modal: null,
        contactMode: 'direct', // direct | group | add
        contacts: [],
        contactsLoading: false,
        contactSearch: '',
        selected: {},
        groupForm: { title: '', description: '' },
        reportForm: { messageId: null, reason: '', note: '' },
        confirmBox: { title: '', message: '', label: '', run: null },
        readersFor: null,
        saving: false,

        sessionExpired: false,
        connectionIssue: false,

        _timer: null,
        _inflight: false,
        _tick: 0,
        _since: null,
        _lastMarked: {},
        _typingSentAt: 0,
        _searchTimer: null,
        _dragDepth: 0,
        _recorder: null,
        _recordTimer: null,

        init() {
            this.fitHeight();
            window.addEventListener('resize', () => { this.fitHeight(); this.closeMenu(); });
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) { this.poll(true); this.markRead(); }
            });
            // Toast "Pesan baru" di halaman ini membuka percakapan tanpa memuat ulang halaman
            window.__chatOpenConversation = (id) => this.open(id);
            window.addEventListener('chat:summary', (e) => { if (e.detail?.messages?.length) this.loadConversations(); });

            this.loadConversations().finally(() => {
                if (this.cfg.initialConversationId) this.open(this.cfg.initialConversationId, false);
            });
            this.schedule();
        },

        // ================= Utilitas =================
        fitHeight() {
            const shell = this.$refs.shell;
            if (!shell) return;
            const top = shell.getBoundingClientRect().top + window.scrollY;
            const gap = window.innerWidth >= 640 ? 20 : 0;
            shell.style.height = `${Math.max(440, window.innerHeight - top - gap)}px`;
        },

        url(name, id, extra = {}) {
            return this.cfg.urls[name].replace('__ID__', id).replace('__USER__', extra.user ?? '');
        },

        listTime,
        formatDuration,

        handleError(error) {
            if (error.status === 401 || error.status === 419) {
                this.sessionExpired = true;
                return;
            }
            window.notify(error.message, 'error');
        },

        // ================= Daftar percakapan =================
        get filteredConversations() {
            const q = this.search.trim().toLowerCase();
            const list = this.conversations.filter((c) => {
                if (this.listFilter === 'channels') return c.type === 'channel';
                // Saluran hanya tampil di menu Saluran Pengumuman agar tab Semua dan lainnya fokus ke chat personal dan grup
                if (c.type === 'channel') return false;

                if (this.listFilter === 'unread' && !c.unread) return false;
                if (this.listFilter === 'groups' && c.type === 'direct') return false;
                if (this.listFilter === 'priority' && !c.stage_badge?.is_urgent) return false;
                if (this.listFilter === 'students') {
                    const isStudent = c.stage_badge?.role_group === 'student' || c.type === 'placement' || c.scope_type === 'mentor_guidance' || c.scope_type === 'dpl_guidance' || c.contact?.role_group === 'student';
                    if (!isStudent) return false;
                }
                if (this.listFilter === 'staff') {
                    const isStaff = c.type === 'direct' && c.contact?.role_group && c.contact.role_group !== 'student';
                    if (!isStaff) return false;
                }
                return !q || [c.title, c.subtitle].join(' ').toLowerCase().includes(q);
            });

            if (this.listFilter === 'priority') {
                return [...list].sort((a, b) => {
                    const unreadA = (a.unread > 0 && !a.muted) ? 1 : 0;
                    const unreadB = (b.unread > 0 && !b.muted) ? 1 : 0;
                    if (unreadA !== unreadB) return unreadB - unreadA;

                    const rankA = a.stage_badge?.priority_rank ?? 99;
                    const rankB = b.stage_badge?.priority_rank ?? 99;
                    if (rankA !== rankB) return rankA - rankB;

                    const timeA = a.last_message ? a.last_message.created_at : a.sort_at;
                    const timeB = b.last_message ? b.last_message.created_at : b.sort_at;
                    return (timeB || '').localeCompare(timeA || '');
                });
            }

            return list;
        },

        get totalPriorityCount() {
            return this.conversations.filter((c) => c.stage_badge?.is_urgent).length;
        },

        get totalUnreadGroups() {
            return this.conversations.filter((c) => c.type !== 'channel' && c.unread > 0).length;
        },

        get totalUnreadChannels() {
            return this.conversations.filter((c) => c.type === 'channel' && c.unread > 0).length;
        },

        async loadConversations(throwErrors = false) {
            try {
                const data = await chatFetch(this.cfg.urls.conversations);
                // Saat Login As pesan tidak ditandai dibaca, jadi jumlah belum dibaca tetap ditampilkan apa adanya
                this.conversations = data.conversations.map((c) => (c.id === this.activeId && !document.hidden && !this.readOnly ? { ...c, unread: 0 } : c));
            } catch (error) {
                if (throwErrors) throw error;
                this.handleError(error);
            } finally {
                this.convLoading = false;
            }
        },

        // ================= Percakapan aktif =================
        get isGroupChat() {
            return !!this.active && this.active.type !== 'direct' && this.active.type !== 'channel';
        },

        get isChannelChat() {
            return !!this.active && this.active.type === 'channel';
        },

        get canSend() {
            return !this.readOnly && !this.sessionExpired && !!this.active && this.active.can_send !== false;
        },

        get headerSubtitle() {
            if (!this.active) return '';
            if (this.isChannelChat) {
                const count = this.active.member_count ? `${this.active.member_count} anggota` : 'Saluran Resmi';
                return `${count} · Pengumuman Resmi`;
            }
            const typing = this.state.typing || [];
            if (typing.length) {
                if (typing.length > 1) return `${typing.length} orang sedang mengetik…`;
                return this.isGroupChat ? `${shortName(typing[0])} sedang mengetik…` : 'sedang mengetik…';
            }
            if (!this.isGroupChat) {
                const presence = this.state.presence;
                const role = this.active.contact?.role_label || '';
                const status = presence ? (presence.online ? 'Online' : lastSeenLabel(presence.last_seen_at)) : '';
                return [status, role].filter(Boolean).join(' · ');
            }
            const others = (this.active.members || []).filter((m) => !m.is_me).map((m) => shortName(m.name));
            const count = this.active.member_count || others.length + 1;
            return `${count} anggota · ${others.slice(0, 4).join(', ')}${others.length > 4 ? ', …' : ''}`;
        },

        get headerTyping() {
            return !this.isChannelChat && (this.state.typing || []).length > 0;
        },

        get headerOnline() {
            return !this.isGroupChat && !this.isChannelChat && !!this.state.presence?.online;
        },

        async open(id, updateUrl = true) {
            id = Number(id);
            if (this.activeId === id && this.messages.length) return;

            this.stopRecording(false);
            this.activeId = id;
            this.active = this.conversations.find((c) => c.id === id) || null;
            this.state = { ...EMPTY_STATE };
            this.messages = [];
            this.hasMore = false;
            this.newBelow = 0;
            this.stickToBottom = true;
            this.draft = '';
            this.clearFiles();
            this.replyTo = null;
            this.media = [];
            this.closeMenu();
            this._since = null;
            window.__chatActiveConversationId = id;
            if (updateUrl) history.replaceState(null, '', this.url('show', id));

            this.loadingMessages = true;
            try {
                const data = await chatFetch(`${this.url('messages', id)}?detail=1`);
                if (this.activeId !== id) return;
                this.applyDetail(data.conversation);
                this.state = data.state;
                this.messages = data.messages;
                this.hasMore = data.has_more;
                this._since = data.server_time;
                this.$nextTick(() => {
                    this.scrollToBottom();
                    this.markRead();
                    if (window.matchMedia('(pointer: fine)').matches) this.$refs.composer?.focus();
                });
                if (this.infoOpen) this.loadMedia();
            } catch (error) {
                this.handleError(error);
                if (error.status === 403 || error.status === 404) this.closeConversation();
            } finally {
                this.loadingMessages = false;
            }
        },

        closeConversation() {
            this.stopRecording(false);
            this.closeComments();
            this.activeId = null;
            this.active = null;
            this.messages = [];
            this.infoOpen = false;
            window.__chatActiveConversationId = null;
            history.replaceState(null, '', this.cfg.urls.index);
        },

        applyDetail(detail) {
            if (!detail) return;
            this.active = detail;
            const index = this.conversations.findIndex((c) => c.id === detail.id);
            if (index >= 0) {
                const { title, subtitle, avatar, contact, muted, pinned } = detail;
                this.conversations[index] = { ...this.conversations[index], title, subtitle, avatar, contact, muted, pinned };
            }
        },

        async refreshDetail() {
            const id = this.activeId;
            if (!id) return;
            try {
                const data = await chatFetch(this.url('detail', id));
                if (this.activeId === id) {
                    this.applyDetail(data.conversation);
                    this.state = data.state;
                }
            } catch (error) {
                if (error.status === 403) this.lostAccess();
            }
        },

        lostAccess() {
            window.notify('Anda tidak lagi menjadi anggota percakapan ini.', 'warning');
            this.closeConversation();
            this.loadConversations();
        },

        get lastId() {
            return this.messages.reduce((max, m) => (typeof m.id === 'number' && m.id > max ? m.id : max), 0);
        },

        get firstId() {
            return this.messages.length ? this.messages[0].id : 0;
        },

        get timeline() {
            const items = [];
            let previousDay = null;
            let previousSender = null;
            let previousTime = 0;
            const local = this.pending.filter((p) => p.conversation_id === this.activeId);

            for (const m of [...this.messages, ...local]) {
                const date = m.created_at ? new Date(m.created_at) : new Date();
                const day = dateKey(date);
                if (day !== previousDay) {
                    items.push({ kind: 'date', key: `d-${day}`, label: dateLabel(date) });
                    previousSender = null;
                }
                previousDay = day;

                if (m.type === 'system') {
                    items.push({ kind: 'system', key: `m-${m.id}`, m });
                    previousSender = null;
                    continue;
                }

                const senderKey = m.is_mine ? 'me' : (m.sender?.id ?? 'x');
                const grouped = previousSender === senderKey && date - previousTime < 5 * 60 * 1000;
                const attachments = m.attachments || [];
                items.push({
                    kind: 'msg',
                    key: `m-${m.id}`,
                    m,
                    grouped,
                    showName: this.isGroupChat && !m.is_mine && !grouped,
                    time: formatTime(date),
                    parts: m.body ? linkify(m.body) : [],
                    images: attachments.filter((a) => a.kind === 'image'),
                    others: attachments.filter((a) => a.kind !== 'image'),
                });
                previousSender = senderKey;
                previousTime = date;
            }
            return items;
        },

        bubbleClass(message) {
            if (message.deleted) return 'bg-white text-slate-400 border border-dashed border-slate-300';
            const failed = message.failed ? ' ring-2 ring-rose-400' : '';
            if (this.isChannelChat) {
                return 'bg-white text-slate-800 border border-slate-200/80 rounded-2xl shadow-sm' + failed;
            }
            return (message.is_mine
                ? 'bg-blue-600 text-white rounded-br-md'
                : 'bg-white text-slate-800 border border-slate-200/80 rounded-bl-md') + failed;
        },

        // ================= Status baca =================
        readCount(message) {
            if (typeof message.id !== 'number') return 0;
            const me = this.cfg.me.id;
            return Object.entries(this.state.read_state || {})
                .filter(([userId, lastRead]) => Number(userId) !== me && lastRead >= message.id).length;
        },

        get othersCount() {
            return Math.max(1, Object.keys(this.state.read_state || {}).length - 1);
        },

        readLabel(message) {
            const count = this.readCount(message);
            if (!this.isGroupChat) return count ? 'Dibaca' : 'Terkirim';
            if (!count) return 'Terkirim';
            return count >= this.othersCount ? 'Dibaca semua anggota' : `Dibaca ${count} orang`;
        },

        get readers() {
            const message = this.readersFor;
            if (!message || !this.active) return { read: [], unread: [] };
            const members = (this.active.members || []).filter((m) => !m.is_me);
            const hasRead = (m) => (this.state.read_state?.[m.id] ?? 0) >= message.id;
            return { read: members.filter(hasRead), unread: members.filter((m) => !hasRead(m)) };
        },

        // ================= Gulir =================
        isNearBottom() {
            const s = this.$refs.scroller;
            return !s || s.scrollHeight - s.scrollTop - s.clientHeight < 120;
        },

        scrollToBottom(smooth = false) {
            const s = this.$refs.scroller;
            if (s) s.scrollTo({ top: s.scrollHeight, behavior: smooth ? 'smooth' : 'auto' });
            this.newBelow = 0;
            this.stickToBottom = true;
        },

        onScroll() {
            const s = this.$refs.scroller;
            if (!s) return;
            if (this.menu && this.menu.desktop && Math.abs(s.scrollTop - this.menu.scrollTop) > 40) this.closeMenu();
            this.stickToBottom = this.isNearBottom();
            if (this.stickToBottom) this.newBelow = 0;
            if (s.scrollTop < 60 && this.hasMore && !this.loadingOlder && !this.loadingMessages) this.loadOlder();
        },

        // Gambar/video selesai dimuat → tinggi berubah; tetap di bawah bila sebelumnya di bawah
        onMediaLoad() {
            if (this.stickToBottom) this.scrollToBottom();
        },

        scrollToMessage(id) {
            const el = id ? document.getElementById(`chat-msg-${id}`) : null;
            if (!el) {
                window.notify('Pesan asli ada di riwayat lama. Gulir ke atas untuk memuatnya.', 'info');
                return;
            }
            el.scrollIntoView({ block: 'center', behavior: 'smooth' });
            el.classList.add('ring-2', 'ring-amber-300', 'rounded-2xl');
            setTimeout(() => el.classList.remove('ring-2', 'ring-amber-300', 'rounded-2xl'), 1600);
        },

        async loadOlder() {
            const id = this.activeId;
            const s = this.$refs.scroller;
            const previousHeight = s.scrollHeight;
            const previousTop = s.scrollTop;
            this.loadingOlder = true;
            try {
                const data = await chatFetch(`${this.url('messages', id)}?before=${this.firstId}`);
                if (this.activeId !== id) return;
                this.messages = [...data.messages, ...this.messages];
                this.hasMore = data.has_more;
                this.$nextTick(() => { s.scrollTop = s.scrollHeight - previousHeight + previousTop; });
            } catch (error) {
                this.handleError(error);
            } finally {
                this.loadingOlder = false;
            }
        },

        // ================= Polling =================
        schedule() {
            clearTimeout(this._timer);
            const poll = this.cfg.poll;
            let delay = document.hidden ? poll.messages_hidden : poll.messages_visible;
            if (this.connectionIssue) delay = Math.min(delay * 3, 30000);
            this._timer = setTimeout(() => this.poll(), delay);
        },

        async poll(immediate = false) {
            if (this.sessionExpired || this._inflight) return;
            this._inflight = true;
            clearTimeout(this._timer);
            try {
                this._tick += 1;
                if (this.activeId && !this.loadingMessages && !this.sending) await this.fetchNew();
                if (this.commentsDrawer.open && this.commentsDrawer.parentMessage && !this.commentsDrawer.loading && !this.commentsDrawer.sending) {
                    await this.fetchComments();
                }
                if (immediate || !this.activeId || this._tick % 3 === 0) await this.loadConversations(true);
                this.connectionIssue = false;
            } catch (error) {
                if (error.status === 401 || error.status === 419) this.sessionExpired = true;
                else if (error.status === 403 || error.status === 404) { if (this.activeId) this.lostAccess(); }
                else this.connectionIssue = true;
            } finally {
                this._inflight = false;
                if (!this.sessionExpired) this.schedule();
            }
        },

        async fetchNew() {
            const id = this.activeId;
            const params = new URLSearchParams({ after: String(this.lastId) });
            if (this._since) params.set('since', this._since);
            const data = await chatFetch(`${this.url('messages', id)}?${params}`);
            if (this.activeId !== id) return;

            this._since = data.server_time;
            const previousVersion = this.active?.meta_version;
            this.state = data.state;
            if (data.changed?.length) {
                this.applyChanged(data.changed);
                this.loadConversations();
            }
            if (previousVersion !== undefined && data.state.meta_version !== previousVersion) this.refreshDetail();

            const known = new Set(this.messages.map((m) => m.id));
            const fresh = data.messages.filter((m) => !known.has(m.id));
            if (!fresh.length) return;

            const stick = this.isNearBottom();
            this.messages = [...this.messages, ...fresh];
            const incoming = fresh.filter((m) => !m.is_mine && m.type !== 'system').length;
            this.$nextTick(() => {
                if (stick) this.scrollToBottom(true);
                else this.newBelow += incoming;
            });
            this.markRead();
            this.loadConversations(); // perbarui pratinjau pesan terakhir di daftar kiri
        },

        applyChanged(changed) {
            const updates = new Map(changed.map((m) => [m.id, m]));
            this.messages = this.messages.map((m) => updates.get(m.id) || m);
        },

        async markRead() {
            const id = this.activeId;
            const last = this.lastId;
            if (this.readOnly || !id || !last || document.hidden) return;
            if (last <= (this._lastMarked[id] || 0)) return;
            this._lastMarked[id] = last;
            try {
                await sendJson(this.url('read', id), { message_id: last });
                const conversation = this.conversations.find((c) => c.id === id);
                if (conversation) conversation.unread = 0;
                window.dispatchEvent(new CustomEvent('chat:unread-changed'));
            } catch (_) {
                this._lastMarked[id] = 0; // dicoba lagi pada polling berikutnya
            }
        },

        // ================= Aksi pesan =================
        openMenu(event, message) {
            if (message.pending || message.deleted || typeof message.id !== 'number') return;
            const rect = event.currentTarget.getBoundingClientRect();
            const desktop = window.innerWidth >= 640;
            const left = message.is_mine ? rect.right - 220 : rect.left;
            this.menu = {
                message,
                desktop,
                scrollTop: this.$refs.scroller?.scrollTop ?? 0,
                style: desktop
                    ? `top: ${Math.min(rect.bottom + 6, window.innerHeight - 280)}px; left: ${Math.min(Math.max(8, left), window.innerWidth - 228)}px;`
                    : '',
            };
        },

        closeMenu() {
            this.menu = null;
        },

        canDelete(message) {
            return !this.readOnly && message.type !== 'system' && !message.deleted && typeof message.id === 'number'
                && (message.is_mine || this.cfg.isSuperAdmin);
        },

        canReport(message) {
            return !this.readOnly && !message.is_mine && message.type !== 'system' && !message.deleted;
        },

        reply(message) {
            this.closeMenu();
            if (!this.canSend) return;
            this.replyTo = { id: message.id, sender_name: message.is_mine ? 'Anda' : (message.sender?.name || ''), preview: messagePreview(message) };
            this.$nextTick(() => this.$refs.composer?.focus());
        },

        async copyText(message) {
            this.closeMenu();
            try {
                await navigator.clipboard.writeText(message.body || '');
                window.notify('Teks pesan disalin.', 'success');
            } catch (_) {
                window.notify('Browser tidak mengizinkan menyalin teks.', 'warning');
            }
        },

        askDelete(message) {
            this.closeMenu();
            this.ask({
                title: 'Hapus pesan untuk semua?',
                message: 'Isi pesan dan lampirannya akan dihapus untuk semua peserta, diganti keterangan "Pesan ini telah dihapus".',
                label: 'Hapus untuk Semua',
                run: async () => {
                    const data = await sendJson(this.url('deleteMessage', message.id), {}, 'DELETE');
                    this.applyChanged([data.message]);
                    this.loadConversations();
                },
            });
        },

        openReport(message) {
            this.closeMenu();
            this.reportForm = { messageId: message.id, reason: '', note: '' };
            this.modal = 'report';
        },

        async submitReport() {
            if (!this.reportForm.reason) {
                window.notify('Pilih alasan pelaporan.', 'warning');
                return;
            }
            this.saving = true;
            try {
                const data = await sendJson(this.url('report', this.reportForm.messageId), {
                    reason: this.reportForm.reason,
                    note: this.reportForm.note.trim() || null,
                });
                this.modal = null;
                window.notify(data.message, 'success');
            } catch (error) {
                this.handleError(error);
            } finally {
                this.saving = false;
            }
        },

        openReaders(message) {
            this.closeMenu();
            this.readersFor = message;
            this.modal = 'readers';
        },

        ask(options) {
            this.confirmBox = options;
            this.modal = 'confirm';
        },

        async runConfirm() {
            if (!this.confirmBox.run || this.saving) return;
            this.saving = true;
            try {
                await this.confirmBox.run();
                this.modal = null;
            } catch (error) {
                this.handleError(error);
            } finally {
                this.saving = false;
            }
        },

        // ================= Menulis & mengirim =================
        get maxUploadLabel() {
            return formatBytes(this.cfg.maxUploadKb * 1024);
        },

        get acceptAttr() {
            return this.cfg.allowedExtensions.map((ext) => `.${ext}`).join(',');
        },

        get totalBytes() {
            return this.files.reduce((sum, f) => sum + f.size, 0);
        },

        get voiceSupported() {
            return voiceRecordingSupported();
        },

        onKeydown(event) {
            if (event.key === 'Escape' && this.replyTo) {
                this.replyTo = null;
                return;
            }
            // Di HP, Enter = baris baru (kirim lewat tombol); di desktop, Enter = kirim
            if (event.key === 'Enter' && !event.shiftKey && !event.isComposing && window.matchMedia('(pointer: fine)').matches) {
                event.preventDefault();
                this.send();
            }
        },

        onInput(event) {
            const el = event.target;
            el.style.height = 'auto';
            el.style.height = `${Math.min(el.scrollHeight, 160)}px`;
            this.pingTyping();
        },

        pingTyping() {
            if (this.readOnly || !this.activeId || !this.draft.trim()) return;
            if (Date.now() - this._typingSentAt < 3000) return;
            this._typingSentAt = Date.now();
            sendJson(this.url('typing', this.activeId)).catch(() => {});
        },

        pickFiles() {
            if (this.canSend) this.$refs.fileInput.click();
        },

        async onFilesChosen(event) {
            const chosen = [...(event.target.files || [])];
            event.target.value = '';
            await this.addFiles(chosen);
        },

        onPaste(event) {
            const pasted = [...(event.clipboardData?.files || [])];
            if (pasted.length && this.canSend) {
                event.preventDefault();
                this.addFiles(pasted);
            }
        },

        dragIn() {
            if (!this.activeId || !this.canSend) return;
            this._dragDepth += 1;
            this.dragging = true;
        },

        dragOut() {
            this._dragDepth = Math.max(0, this._dragDepth - 1);
            if (this._dragDepth === 0) this.dragging = false;
        },

        onDrop(event) {
            this._dragDepth = 0;
            this.dragging = false;
            const dropped = [...(event.dataTransfer?.files || [])];
            if (dropped.length && this.activeId && this.canSend) this.addFiles(dropped);
        },

        async addFiles(list) {
            if (!this.canSend || !list.length) return;
            const room = this.cfg.maxFiles - this.files.length;
            if (list.length > room) window.notify(`Maksimal ${this.cfg.maxFiles} lampiran dalam satu pesan.`, 'warning');

            const maxBytes = this.cfg.maxUploadKb * 1024;
            this.preparingFiles = true;
            try {
                for (const original of list.slice(0, Math.max(0, room))) {
                    if (!this.cfg.allowedExtensions.includes(extensionOf(original.name))) {
                        window.notify(`"${original.name}" tidak didukung. Gunakan foto, video, audio, PDF, dokumen Office, TXT/CSV, atau ZIP/RAR.`, 'error');
                        continue;
                    }
                    let file = original;
                    if (original.type.startsWith('image/')) {
                        try { file = await compressImageIfNeeded(original, maxBytes); } catch (_) { file = original; }
                    }
                    if (file.size > maxBytes) {
                        window.notify(`"${original.name}" melebihi batas ${this.maxUploadLabel} per file.`, 'error');
                        continue;
                    }
                    this.files.push({
                        id: ++fileSeq,
                        file,
                        name: file.name,
                        size: file.size,
                        kind: kindOf(file),
                        preview: file.type.startsWith('image/') ? URL.createObjectURL(file) : null,
                    });
                }
            } finally {
                this.preparingFiles = false;
            }
            this.$nextTick(() => this.$refs.composer?.focus());
        },

        removeFile(id) {
            const entry = this.files.find((f) => f.id === id);
            if (entry?.preview) URL.revokeObjectURL(entry.preview);
            this.files = this.files.filter((f) => f.id !== id);
        },

        clearFiles() {
            this.files.forEach((f) => f.preview && URL.revokeObjectURL(f.preview));
            this.files = [];
        },

        makeTemp(body, files, voice) {
            return {
                id: `tmp-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`,
                conversation_id: this.activeId,
                type: 'text',
                is_mine: true,
                sender: this.cfg.me,
                body: body || null,
                deleted: false,
                reply_to: this.replyTo ? { ...this.replyTo } : null,
                attachments: files.map((f) => ({
                    id: `tmp-${f.id}`,
                    kind: voice ? 'audio' : f.kind,
                    name: voice ? 'Pesan suara' : f.name,
                    size_label: formatBytes(f.size),
                    url: f.preview || (['audio', 'video'].includes(voice ? 'audio' : f.kind) ? URL.createObjectURL(f.file) : null),
                    download_url: null,
                    local: true,
                })),
                created_at: new Date().toISOString(),
                pending: true,
                failed: false,
                upload: { files: files.map((f) => f.file), voice, replyToId: this.replyTo?.id ?? null },
            };
        },

        async send() {
            if (!this.canSend || this.sending || this.preparingFiles || this.recording) return;
            const body = this.draft.trim();
            if (!body && !this.files.length) return;
            if (body.length > this.cfg.maxBodyLength) {
                window.notify(`Pesan terlalu panjang (maksimal ${this.cfg.maxBodyLength} karakter).`, 'error');
                return;
            }
            const maxTotal = this.cfg.maxTotalKb * 1024;
            if (maxTotal && this.totalBytes > maxTotal) {
                window.notify(`Total lampiran melebihi ${formatBytes(maxTotal)}. Kirim sebagian file di pesan terpisah.`, 'error');
                return;
            }

            const temp = this.makeTemp(body, this.files.slice(), false);
            this.pending.push(temp);
            this.draft = '';
            this.files = []; // pratinjau tetap dipakai pesan sementara, dibersihkan setelah terkirim
            this.replyTo = null;
            if (this.$refs.composer) this.$refs.composer.style.height = 'auto';
            this.$nextTick(() => this.scrollToBottom(true));
            await this.deliver(temp.id);
        },

        async deliver(tempId) {
            const temp = this.pending.find((p) => p.id === tempId);
            if (!temp) return;
            temp.pending = true;
            temp.failed = false;
            this.sending = true;
            this.uploadProgress = 0;

            const form = new FormData();
            if (temp.body) form.append('body', temp.body);
            if (temp.upload.replyToId) form.append('reply_to_id', String(temp.upload.replyToId));
            if (temp.upload.voice) form.append('voice', '1');
            temp.upload.files.forEach((file) => form.append('files[]', file, file.name));

            try {
                const data = await uploadForm(this.url('send', temp.conversation_id), form, (p) => { this.uploadProgress = p; });
                this.pending = this.pending.filter((p) => p.id !== tempId);
                this.revokeTemp(temp);
                if (this.activeId === temp.conversation_id && !this.messages.some((m) => m.id === data.message.id)) {
                    this.messages = [...this.messages, data.message];
                }
                this.$nextTick(() => this.scrollToBottom(true));
                this.loadConversations();
            } catch (error) {
                temp.pending = false;
                temp.failed = true;
                if (error.status === 401 || error.status === 419) this.sessionExpired = true;
                window.notify(error.message, 'error');
            } finally {
                this.sending = false;
                this.uploadProgress = 0;
            }
        },

        revokeTemp(temp) {
            (temp.attachments || []).forEach((a) => { if (a.local && a.url) URL.revokeObjectURL(a.url); });
        },

        discard(tempId) {
            const temp = this.pending.find((p) => p.id === tempId);
            if (temp) this.revokeTemp(temp);
            this.pending = this.pending.filter((p) => p.id !== tempId);
        },

        // ================= Voice note =================
        async startRecording() {
            if (!this.canSend || this.recording || this.sending) return;
            this._recorder = createVoiceRecorder();
            try {
                await this._recorder.start();
            } catch (error) {
                this._recorder = null;
                window.notify(error.message, 'error');
                return;
            }
            this.recording = true;
            this.recordSeconds = 0;
            this._recordTimer = setInterval(() => {
                this.recordSeconds += 1;
                if (this.recordSeconds >= this.cfg.voiceMaxSeconds) this.stopRecording(true);
            }, 1000);
        },

        async stopRecording(send = true) {
            if (!this._recorder) return;
            clearInterval(this._recordTimer);
            const recorder = this._recorder;
            const seconds = this.recordSeconds;
            this._recorder = null;
            this.recording = false;
            if (!send) {
                recorder.cancel();
                return;
            }

            const file = await recorder.stop();
            if (!file || seconds < 1) {
                window.notify('Rekaman terlalu singkat.', 'warning');
                return;
            }
            if (file.size > this.cfg.maxUploadKb * 1024) {
                window.notify(`Rekaman melebihi batas ${this.maxUploadLabel}. Rekam pesan yang lebih singkat.`, 'error');
                return;
            }

            const temp = this.makeTemp('', [{ id: ++fileSeq, file, name: file.name, size: file.size, kind: 'audio', preview: null }], true);
            this.replyTo = null;
            this.pending.push(temp);
            this.$nextTick(() => this.scrollToBottom(true));
            await this.deliver(temp.id);
        },

        // ================= Chat baru, grup & anggota =================
        openContacts(mode) {
            this.contactMode = mode;
            this.selected = {};
            this.contactSearch = '';
            if (mode === 'group') this.groupForm = { title: '', description: '' };
            this.modal = 'contacts';
            this.loadContacts();
            this.$nextTick(() => (mode === 'group' ? this.$refs.groupTitle : this.$refs.contactSearch)?.focus());
        },

        searchContacts() {
            clearTimeout(this._searchTimer);
            this._searchTimer = setTimeout(() => this.loadContacts(), 300);
        },

        async loadContacts() {
            const q = this.contactSearch.trim();
            this.contactsLoading = true;
            try {
                const data = await chatFetch(q ? `${this.cfg.urls.contacts}?q=${encodeURIComponent(q)}` : this.cfg.urls.contacts);
                if (q === this.contactSearch.trim()) this.contacts = data.contacts;
            } catch (error) {
                this.handleError(error);
            } finally {
                this.contactsLoading = false;
            }
        },

        get contactGroups() {
            const excluded = new Set(this.contactMode === 'add' ? (this.active?.members || []).map((m) => m.id) : []);
            return ROLE_GROUPS
                .map(([key, label]) => ({ key, label, items: this.contacts.filter((c) => c.role_group === key && !excluded.has(c.id)) }))
                .filter((group) => group.items.length);
        },

        get selectedList() {
            return Object.values(this.selected);
        },

        isSelected(contact) {
            return !!this.selected[contact.id];
        },

        pickContact(contact) {
            if (this.contactMode === 'direct') {
                this.startWith(contact);
                return;
            }
            const next = { ...this.selected };
            if (next[contact.id]) delete next[contact.id];
            else next[contact.id] = contact;
            this.selected = next;
        },

        subtitle(contact) {
            return [contact.role_label, contact.org].filter(Boolean).join(' · ');
        },

        async startWith(contact) {
            if (this.saving) return;
            this.saving = true;
            try {
                const data = await sendJson(this.cfg.urls.start, { user_id: contact.id });
                this.modal = null;
                this.activeId = null;
                await this.open(data.conversation.id);
                if (!this.active) this.active = data.conversation;
            } catch (error) {
                this.handleError(error);
            } finally {
                this.saving = false;
            }
        },

        async createGroup() {
            const title = this.groupForm.title.trim();
            if (title.length < 3) {
                window.notify('Nama grup minimal 3 karakter.', 'warning');
                return;
            }
            if (!this.selectedList.length) {
                window.notify('Pilih minimal satu anggota grup.', 'warning');
                return;
            }
            this.saving = true;
            try {
                const data = await sendJson(this.cfg.urls.groups, {
                    title,
                    description: this.groupForm.description.trim() || null,
                    member_ids: this.selectedList.map((c) => c.id),
                });
                this.modal = null;
                await this.loadConversations();
                this.activeId = null;
                await this.open(data.conversation.id);
                window.notify(`Grup "${title}" berhasil dibuat.`, 'success');
            } catch (error) {
                this.handleError(error);
            } finally {
                this.saving = false;
            }
        },

        async addMembers() {
            if (!this.selectedList.length || this.saving) return;
            this.saving = true;
            try {
                const data = await sendJson(this.url('members', this.activeId), { user_ids: this.selectedList.map((c) => c.id) });
                this.modal = null;
                this.applyDetail(data.conversation);
                window.notify('Anggota baru ditambahkan ke grup.', 'success');
                this.poll(true);
            } catch (error) {
                this.handleError(error);
            } finally {
                this.saving = false;
            }
        },

        // ================= Panel info =================
        toggleInfo() {
            this.infoOpen = !this.infoOpen;
            if (this.infoOpen) {
                this.loadMedia();
                this.$nextTick(() => { if (this.$refs.infoScroll) this.$refs.infoScroll.scrollTop = 0; });
            }
        },

        async copyValue(value, label) {
            try {
                await navigator.clipboard.writeText(String(value || ''));
                window.notify(`${label} disalin.`, 'success');
            } catch (_) {
                window.notify('Browser tidak mengizinkan menyalin teks.', 'warning');
            }
        },

        async loadMedia() {
            const id = this.activeId;
            if (!id) return;
            this.mediaLoading = true;
            try {
                const data = await chatFetch(this.url('media', id));
                if (this.activeId === id) this.media = data.media;
            } catch (error) {
                this.handleError(error);
            } finally {
                this.mediaLoading = false;
            }
        },

        get mediaVisual() {
            return this.media.filter((a) => a.kind === 'image' || a.kind === 'video');
        },

        get mediaFiles() {
            return this.media.filter((a) => a.kind === 'document' || a.kind === 'audio');
        },

        async toggleSetting(key) {
            if (!this.active || this.readOnly) return;
            try {
                const data = await sendJson(this.url('settings', this.activeId), { [key]: !this.active[key] });
                this.active.muted = data.muted;
                this.active.pinned = data.pinned;
                this.applyDetail(this.active);
                await this.loadConversations();
                window.dispatchEvent(new CustomEvent('chat:unread-changed'));
                window.notify(key === 'muted'
                    ? (data.muted ? 'Percakapan dibisukan. Tidak ada notifikasi untuk pesan baru.' : 'Notifikasi percakapan diaktifkan kembali.')
                    : (data.pinned ? 'Percakapan disematkan di atas daftar.' : 'Sematan percakapan dilepas.'), 'success');
            } catch (error) {
                this.handleError(error);
            }
        },

        editGroup() {
            this.groupForm = { title: this.active.title, description: this.active.description || '' };
            this.modal = 'editGroup';
            this.$nextTick(() => this.$refs.editTitle?.focus());
        },

        async saveGroup() {
            const title = this.groupForm.title.trim();
            if (title.length < 3) {
                window.notify('Nama grup minimal 3 karakter.', 'warning');
                return;
            }
            this.saving = true;
            try {
                const data = await sendJson(this.url('update', this.activeId), { title, description: this.groupForm.description.trim() || null }, 'PATCH');
                this.applyDetail(data.conversation);
                this.modal = null;
                this.poll(true);
            } catch (error) {
                this.handleError(error);
            } finally {
                this.saving = false;
            }
        },

        askRemoveMember(member) {
            this.ask({
                title: `Keluarkan ${member.name}?`,
                message: 'Anggota yang dikeluarkan tidak bisa lagi membaca maupun mengirim pesan di grup ini.',
                label: 'Keluarkan dari Grup',
                run: async () => {
                    const data = await sendJson(this.url('removeMember', this.activeId, { user: member.id }), {}, 'DELETE');
                    this.applyDetail(data.conversation);
                    this.poll(true);
                },
            });
        },

        askLeave() {
            this.ask({
                title: 'Keluar dari grup?',
                message: 'Anda tidak akan menerima pesan baru dari grup ini. Admin grup dapat menambahkan Anda kembali.',
                label: 'Keluar dari Grup',
                run: async () => {
                    await sendJson(this.url('leave', this.activeId));
                    this.closeConversation();
                    await this.loadConversations();
                },
            });
        },

        // ================= Lightbox foto (LRN-019) =================
        openLightbox(items, index) {
            this.lightbox = { items, index };
        },

        get lightboxItem() {
            return this.lightbox ? this.lightbox.items[this.lightbox.index] : null;
        },

        lightboxStep(delta) {
            if (!this.lightbox || this.lightbox.items.length < 2) return;
            const count = this.lightbox.items.length;
            this.lightbox.index = (this.lightbox.index + delta + count) % count;
        },

        // ================= Utas Komentar Saluran (Channel Comments) =================
        async openComments(message) {
            if (!message || typeof message.id !== 'number') return;
            this.commentsDrawer.parentMessage = message;
            this.commentsDrawer.open = true;
            this.commentsDrawer.loading = true;
            this.commentsDrawer.comments = [];
            this.commentsDrawer.draft = '';

            try {
                const res = await chatFetch(this.url('comments', message.id));
                if (this.commentsDrawer.parentMessage?.id === message.id) {
                    this.commentsDrawer.comments = res.comments || [];
                    message.comments_count = this.commentsDrawer.comments.length;
                }
                this.$nextTick(() => {
                    this.scrollCommentsToBottom();
                    this.$refs.commentInput?.focus();
                });
            } catch (err) {
                this.handleError(err);
            } finally {
                this.commentsDrawer.loading = false;
            }
        },

        closeComments() {
            this.commentsDrawer.open = false;
            this.commentsDrawer.parentMessage = null;
            this.commentsDrawer.comments = [];
            this.commentsDrawer.draft = '';
            this.commentsDrawer.loading = false;
            this.commentsDrawer.sending = false;
        },

        scrollCommentsToBottom() {
            const c = this.$refs.commentsContainer;
            if (c) {
                c.scrollTop = c.scrollHeight;
            }
        },

        async submitComment() {
            const text = this.commentsDrawer.draft.trim();
            const parent = this.commentsDrawer.parentMessage;
            if (!text || !parent || this.commentsDrawer.sending) return;

            this.commentsDrawer.sending = true;
            try {
                const res = await sendJson(this.url('sendComment', parent.id), { body: text });
                if (res.comment) {
                    this.commentsDrawer.comments.push(res.comment);
                    parent.comments_count = (parent.comments_count || 0) + 1;
                    this.commentsDrawer.draft = '';
                    this.$nextTick(() => {
                        this.scrollCommentsToBottom();
                    });
                }
            } catch (err) {
                this.handleError(err);
            } finally {
                this.commentsDrawer.sending = false;
            }
        },

        formatCommentTime(iso) {
            if (!iso) return '';
            const d = new Date(iso);
            return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },

        async fetchComments() {
            const parent = this.commentsDrawer.parentMessage;
            if (!parent || !this.commentsDrawer.open) return;
            try {
                const res = await chatFetch(this.url('comments', parent.id));
                if (res.comments && this.commentsDrawer.parentMessage?.id === parent.id) {
                    const previousCount = this.commentsDrawer.comments.length;
                    this.commentsDrawer.comments = res.comments;
                    parent.comments_count = res.comments.length;
                    if (res.comments.length > previousCount) {
                        this.$nextTick(() => this.scrollCommentsToBottom());
                    }
                }
            } catch (_) {}
        },
    };
}
