// Notifikasi chat di semua halaman: badge jumlah belum dibaca (navbar), angka di judul tab,
// toast "Pesan baru", notifikasi desktop (opsional, izin browser) dan suara.
// Data dari GET /chat/api/summary (polling adaptif). Percakapan yang dibisukan tidak ikut.
import { chatFetch } from './api';

const SOUND_KEY = 'chat:sound';
const DESKTOP_KEY = 'chat:desktop';

function storageGet(key) {
    try { return localStorage.getItem(key); } catch (_) { return null; }
}

function storageSet(key, value) {
    try { localStorage.setItem(key, value); } catch (_) { /* mode privat */ }
}

// Nada singkat dua ketukan lewat WebAudio (tanpa berkas suara)
let audioContext = null;
function unlockAudio() {
    if (!audioContext && (window.AudioContext || window.webkitAudioContext)) {
        audioContext = new (window.AudioContext || window.webkitAudioContext)();
    }
    audioContext?.resume?.();
}

function playChime() {
    if (!audioContext || audioContext.state !== 'running') return;
    const now = audioContext.currentTime;
    [[880, 0], [1320, 0.12]].forEach(([freq, offset]) => {
        const osc = audioContext.createOscillator();
        const gain = audioContext.createGain();
        osc.type = 'sine';
        osc.frequency.value = freq;
        gain.gain.setValueAtTime(0.0001, now + offset);
        gain.gain.exponentialRampToValueAtTime(0.18, now + offset + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.0001, now + offset + 0.25);
        osc.connect(gain).connect(audioContext.destination);
        osc.start(now + offset);
        osc.stop(now + offset + 0.3);
    });
}

export function registerChatNotifier(Alpine) {
    const body = document.body;
    const userKey = body.dataset.chatUser || 'guest';
    const LAST_SEEN_KEY = `chat:last-seen-message-id:${userKey}`;
    const GREETED_KEY = `chat:greeted:${userKey}`;

    Alpine.store('chat', {
        unread: parseInt(body.dataset.chatUnread || '0', 10) || 0,
        get label() {
            return this.unread > 99 ? '99+' : String(this.unread);
        },
    });

    const notificationSupported = 'Notification' in window;
    Alpine.store('chatPrefs', {
        sound: storageGet(SOUND_KEY) !== 'off',
        desktop: storageGet(DESKTOP_KEY) === 'on',
        permission: notificationSupported ? Notification.permission : 'unsupported',
        testSound() {
            unlockAudio();
            setTimeout(playChime, 60);
        },
        toggleSound() {
            this.sound = !this.sound;
            storageSet(SOUND_KEY, this.sound ? 'on' : 'off');
            if (this.sound) { unlockAudio(); setTimeout(playChime, 60); }
        },
        async toggleDesktop() {
            if (!notificationSupported) {
                window.notify('Browser ini tidak mendukung notifikasi desktop.', 'warning');
                return;
            }
            if (this.desktop) {
                this.desktop = false;
                storageSet(DESKTOP_KEY, 'off');
                return;
            }
            this.permission = Notification.permission === 'default' ? await Notification.requestPermission() : Notification.permission;
            if (this.permission === 'granted') {
                this.desktop = true;
                storageSet(DESKTOP_KEY, 'on');
                window.notify('Notifikasi desktop aktif. Pesan baru akan muncul walau tab ini tidak sedang dibuka.', 'success');
            } else {
                window.notify('Izin notifikasi diblokir browser. Izinkan notifikasi untuk situs ini di pengaturan browser.', 'warning');
            }
        },
    });

    Alpine.data('chatToasts', () => ({
        items: [],
        push(item) {
            if (this.items.some((t) => t.id === item.id)) return;
            this.items = [...this.items.slice(-2), item];
            setTimeout(() => this.remove(item.id), 7000);
        },
        remove(id) {
            this.items = this.items.filter((t) => t.id !== id);
        },
    }));

    // Browser hanya mengizinkan suara setelah ada interaksi pengguna
    ['pointerdown', 'keydown'].forEach((type) => document.addEventListener(type, unlockAudio, { once: true, passive: true }));

    const summaryUrl = body.dataset.chatSummaryUrl;
    if (!summaryUrl) return;

    const visibleDelay = parseInt(body.dataset.chatPollVisible || '15000', 10);
    const hiddenDelay = parseInt(body.dataset.chatPollHidden || '60000', 10);
    const baseTitle = document.title.replace(/^\(\d+\+?\)\s*/, '');
    let after = null;
    let timer = null;
    let inflight = false;
    let stopped = false;

    const setTitle = (count) => {
        document.title = count > 0 ? `(${count > 99 ? '99+' : count}) ${baseTitle}` : baseTitle;
    };

    const schedule = (delay) => {
        clearTimeout(timer);
        if (!stopped) timer = setTimeout(poll, delay ?? (document.hidden ? hiddenDelay : visibleDelay));
    };

    const announce = (messages) => {
        const prefs = Alpine.store('chatPrefs');
        // Percakapan yang sedang dibuka & terlihat tidak perlu diberi tahu lagi
        const away = document.hidden || !document.hasFocus();
        const fresh = messages.filter((m) => away || m.conversation_id !== window.__chatActiveConversationId);
        if (!fresh.length) return;

        fresh.slice().reverse().forEach((m) => window.dispatchEvent(new CustomEvent('chat-toast', { detail: m })));
        if (prefs.sound) playChime();

        if (prefs.desktop && away && notificationSupported && Notification.permission === 'granted') {
            fresh.slice(0, 3).forEach((m) => {
                const notification = new Notification(m.title, {
                    body: m.preview,
                    tag: `chat-${m.conversation_id}`,
                    icon: '/images/logos/surabaya.png',
                });
                notification.onclick = () => {
                    window.focus();
                    window.location.href = m.url;
                    notification.close();
                };
            });
        }
    };

    // Sekali per sesi browser: beri tahu bila ada pesan yang belum dibaca saat pertama membuka aplikasi
    const greetUnread = (count) => {
        let greeted = false;
        try { greeted = sessionStorage.getItem(GREETED_KEY) === '1'; sessionStorage.setItem(GREETED_KEY, '1'); } catch (_) { /* mode privat */ }
        if (greeted || count < 1 || window.__chatOpenConversation) return;
        window.dispatchEvent(new CustomEvent('chat-toast', { detail: {
            id: `summary-${Date.now()}`,
            conversation_id: null,
            url: '/chat',
            title: 'Pesan belum dibaca',
            sender: { initials: '💬', color: '#2563eb' },
            preview: `Anda memiliki ${count} pesan chat yang belum dibaca.`,
        } }));
    };

    async function poll() {
        if (inflight || stopped) return;
        inflight = true;
        try {
            // Tab lain mungkin sudah memberi tahu pesan yang sama
            const shared = parseInt(storageGet(LAST_SEEN_KEY) ?? '', 10);
            if (Number.isFinite(shared) && (after === null || shared > after)) after = shared;

            const data = await chatFetch(after === null ? summaryUrl : `${summaryUrl}?after=${after}`);
            Alpine.store('chat').unread = data.unread_total;
            setTitle(data.unread_total);
            window.dispatchEvent(new CustomEvent('chat:summary', { detail: data }));

            if (after !== null && data.messages.length) announce(data.messages);
            if (after === null) greetUnread(data.unread_total);

            after = Math.max(after ?? 0, data.latest_id || 0);
            storageSet(LAST_SEEN_KEY, String(after));
        } catch (error) {
            if (error.status === 401 || error.status === 419) stopped = true;
        } finally {
            inflight = false;
            schedule();
        }
    }

    setTitle(Alpine.store('chat').unread);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) schedule(300); });
    window.addEventListener('chat:unread-changed', () => schedule(300));
    schedule(1500);
}
