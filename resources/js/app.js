

import Alpine from 'alpinejs';

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

Alpine.start();
