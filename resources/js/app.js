import Alpine from 'alpinejs';
import { chatApp } from './chat/app';
import { registerChatNotifier } from './chat/notifier';

window.Alpine = Alpine;

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

Alpine.start();
