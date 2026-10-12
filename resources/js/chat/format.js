// Format waktu, ukuran file, dan tautan untuk tampilan chat (Bahasa Indonesia).

const timeFormat = new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit' });
const dateFormat = new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
const weekdayFormat = new Intl.DateTimeFormat('id-ID', { weekday: 'long' });
const shortDateFormat = new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: '2-digit', year: '2-digit' });
const relative = new Intl.RelativeTimeFormat('id-ID', { numeric: 'auto' });

export function formatTime(date) {
    return timeFormat.format(date);
}

export function dateKey(date) {
    return `${date.getFullYear()}-${date.getMonth()}-${date.getDate()}`;
}

export function daysAgo(date) {
    const start = (d) => new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime();
    return Math.round((start(new Date()) - start(date)) / 86400000);
}

export function dateLabel(date) {
    const diff = daysAgo(date);
    if (diff === 0) return 'Hari ini';
    if (diff === 1) return 'Kemarin';
    return dateFormat.format(date);
}

export function listTime(iso) {
    if (!iso) return '';
    const date = new Date(iso);
    const diff = daysAgo(date);
    if (diff === 0) return timeFormat.format(date);
    if (diff === 1) return 'Kemarin';
    if (diff < 7) return weekdayFormat.format(date);
    return shortDateFormat.format(date);
}

export function lastSeenLabel(iso) {
    if (!iso) return 'Belum pernah aktif di chat';
    const seconds = Math.round((new Date(iso).getTime() - Date.now()) / 1000);
    const abs = Math.abs(seconds);
    if (abs < 60) return 'Terakhir dilihat baru saja';
    if (abs < 3600) return `Terakhir dilihat ${relative.format(Math.round(seconds / 60), 'minute')}`;
    if (abs < 86400) return `Terakhir dilihat ${relative.format(Math.round(seconds / 3600), 'hour')}`;
    return `Terakhir dilihat ${listTime(iso) === 'Kemarin' ? 'kemarin' : listTime(iso)}`;
}

export function formatBytes(bytes) {
    if (bytes >= 1048576) return `${(bytes / 1048576).toFixed(1).replace('.', ',')} MB`;
    return `${Math.max(1, Math.round(bytes / 1024))} KB`;
}

export function formatDuration(seconds) {
    const m = Math.floor(seconds / 60);
    const s = Math.floor(seconds % 60);
    return `${m}:${String(s).padStart(2, '0')}`;
}

// Nama panggilan tanpa gelar: "Ir. Siti Aminah, M.Kom" → "Siti", "Dr. Erina Nur Azizah" → "Erina"
export function shortName(name) {
    const tokens = String(name || '').trim().split(/[\s,]+/).filter(Boolean);
    return tokens.find((t) => !t.includes('.') && /^\p{L}/u.test(t)) || tokens[0] || '';
}

export function extensionOf(name) {
    return (String(name).split('.').pop() || '').toLowerCase();
}

// Teks biasa + tautan http(s). Dirender dengan x-text sehingga aman dari XSS.
export function linkify(text) {
    const parts = [];
    const pattern = /https?:\/\/[^\s<>"']+/gi;
    let last = 0;
    let match;
    while ((match = pattern.exec(text)) !== null) {
        const url = match[0].replace(/[.,!?;:)\]]+$/, '');
        if (match.index > last) parts.push({ link: false, v: text.slice(last, match.index) });
        parts.push({ link: true, v: url });
        last = match.index + url.length;
        pattern.lastIndex = last;
    }
    if (last < text.length) parts.push({ link: false, v: text.slice(last) });
    return parts;
}
