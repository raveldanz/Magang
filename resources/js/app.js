import Alpine from 'alpinejs';
import { chatApp } from './chat/app';
import { registerChatNotifier } from './chat/notifier';
import { initAutosave, registerFormGuards } from './forms';

window.Alpine = Alpine;

// navigator.clipboard hanya ada di konteks aman (HTTPS / localhost). Saat aplikasi dibuka lewat
// alamat IP jaringan lokal (http://192.168.x.x), sediakan pengganti agar semua tombol "Salin" tetap berfungsi.
if (!navigator.clipboard) {
    const legacyCopy = (text) => new Promise((resolve, reject) => {
        const area = document.createElement('textarea');
        area.value = String(text ?? '');
        area.setAttribute('readonly', '');
        area.style.cssText = 'position:fixed;top:0;left:0;opacity:0;pointer-events:none;';
        document.body.appendChild(area);
        area.select();
        area.setSelectionRange(0, area.value.length);
        let copied = false;
        try {
            copied = document.execCommand('copy');
        } catch (error) {
            copied = false;
        }
        area.remove();
        copied ? resolve() : reject(new Error('Gagal menyalin teks.'));
    });

    try {
        Object.defineProperty(navigator, 'clipboard', { value: { writeText: legacyCopy }, configurable: true });
    } catch (error) {
        // Browser menolak penambahan properti: tombol salin tetap seperti semula.
    }
}

// Notifikasi standar aplikasi (pengganti alert() bawaan browser).
// Tampil sebagai toast di layout app; jatuh ke alert() hanya jika halaman tidak memuat wadah toast.
window.notify = (message, type = 'error') => {
    if (document.querySelector('[data-toast-root]')) {
        window.dispatchEvent(new CustomEvent('toast', { detail: { type, message } }));
    } else {
        window.alert(message);
    }
};

// Fitur chat: komponen halaman chat + badge/toast/notifikasi desktop pesan baru di semua halaman
Alpine.data('chatApp', chatApp);
registerChatNotifier(Alpine);

// Penjaga ukuran berkas + kunci tombol kirim (dipasang sebelum Alpine agar berjalan lebih dulu)
registerFormGuards();

Alpine.start();

// Pulihkan isian form[data-autosave] setelah komponen Alpine siap
window.setTimeout(initAutosave, 0);
