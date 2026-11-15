/**
 * Pengaman form untuk seluruh aplikasi.
 *
 * 1. Penjaga ukuran berkas: berkas yang melebihi batas ditolak SEBELUM dikirim.
 *    Batas per kolom diambil dari atribut data-max-kb (sama dengan aturan validasi server),
 *    ditambah batas PHP yang sedang berlaku (meta upload-file-max-kb / upload-post-max-kb).
 *    Tanpa ini, berkas 30 MB tetap terunggah penuh, lalu PHP membuang seluruh isi form.
 * 2. Simpan isian otomatis (form[data-autosave="kunci-unik"]): isian disimpan di sessionStorage
 *    tab ini dan dipulihkan setelah halaman dimuat ulang / gagal kirim; dihapus setelah berhasil.
 * 3. Kunci tombol kirim saat form dengan berkas atau form data-autosave sedang dikirim,
 *    agar tidak diklik berulang ketika unggahan lambat.
 */

const STORE_PREFIX = 'autosave:';
const SUBMITTED_SUFFIX = ':submitted';
const DRAFT_MAX_AGE_MS = 12 * 60 * 60 * 1000;
const SKIP_TYPES = ['password', 'file', 'submit', 'button', 'reset', 'image'];

const metaNumber = (name) => {
    const value = Number(document.querySelector(`meta[name="${name}"]`)?.content || 0);
    return Number.isFinite(value) && value > 0 ? value : 0;
};

const notify = (message, type = 'error') => (window.notify ? window.notify(message, type) : window.alert(message));

export const formatSize = (bytes) => {
    const mb = bytes / (1024 * 1024);
    if (mb >= 1) {
        return `${mb.toLocaleString('id-ID', { maximumFractionDigits: mb >= 10 ? 0 : 1 })} MB`;
    }
    return `${Math.max(1, Math.round(bytes / 1024)).toLocaleString('id-ID')} KB`;
};

const storage = {
    get(key) {
        try {
            return JSON.parse(window.sessionStorage.getItem(key) || 'null');
        } catch (error) {
            return null;
        }
    },
    set(key, value) {
        try {
            window.sessionStorage.setItem(key, JSON.stringify(value));
        } catch (error) {
            // Penyimpanan penuh / diblokir (mode privat): form tetap berfungsi tanpa simpan otomatis.
        }
    },
    remove(key) {
        try {
            window.sessionStorage.removeItem(key);
        } catch (error) {
            // abaikan
        }
    },
    keys() {
        try {
            return Object.keys(window.sessionStorage);
        } catch (error) {
            return [];
        }
    },
};

/* ------------------------------------------------------------------ 1. Penjaga ukuran berkas */

/** Batas satu berkas (byte) untuk input ini, atau 0 bila tidak dijaga. */
const fileLimitBytes = (input) => {
    const fieldKb = Number(input.dataset.maxKb || 0);
    const phpKb = metaNumber('upload-file-max-kb');
    const limits = [fieldKb, phpKb].filter((kb) => kb > 0);
    return limits.length ? Math.min(...limits) * 1024 : 0;
};

const isGuarded = (input) => input instanceof HTMLInputElement && input.type === 'file' && input.name && input.dataset.uploadGuard !== 'off';

const oversizedFile = (input) => {
    const limit = fileLimitBytes(input);
    if (!limit) return null;
    for (const file of input.files || []) {
        if (file.size > limit) return { file, limit };
    }
    return null;
};

const oversizeMessage = ({ file, limit }) =>
    `Berkas "${file.name}" berukuran ${formatSize(file.size)}, melebihi batas ${formatSize(limit)}. `
    + 'Berkas tidak dikirim. Silakan kompres atau pilih berkas yang lebih kecil.';

/**
 * Foto iPhone berformat HEIC/HEIF tidak diterima server (hanya JPG/PNG/PDF) dan tidak bisa
 * ditampilkan sebagian besar browser. Tolak di tempat dengan petunjuk, daripada gagal setelah unggah.
 */
const heicFile = (input) => {
    if (/hei[cf]/i.test(input.accept || '')) return null;
    for (const file of input.files || []) {
        if (/\.hei[cf]$/i.test(file.name) || /^image\/hei[cf]/i.test(file.type)) return file;
    }
    return null;
};

const heicMessage = (file) =>
    `Berkas "${file.name}" berformat HEIC (foto iPhone) dan tidak didukung. Berkas tidak dikirim. `
    + 'Pilih foto langsung dari Galeri/Foto agar otomatis menjadi JPG, atau ubah dulu ke JPG atau PNG.';

const clearInput = (input) => {
    input.value = '';
    // Beri tahu komponen Alpine (nama berkas/pratinjau) bahwa pilihan dikosongkan.
    input.dispatchEvent(new CustomEvent('change', { bubbles: true, detail: { uploadGuard: true } }));
};

function onFileChange(event) {
    const input = event.target;
    if (!isGuarded(input) || event.detail?.uploadGuard) return;
    // Fase bubble: penangan bawaan kolom (bila ada) sudah berjalan; jika ia sudah mengosongkan, selesai.
    const heic = heicFile(input);
    if (heic) {
        clearInput(input);
        notify(heicMessage(heic));
        return;
    }
    const problem = oversizedFile(input);
    if (!problem) return;
    clearInput(input);
    notify(oversizeMessage(problem));
}

/** Periksa semua berkas di form. Mengembalikan pesan kesalahan atau null. */
export function uploadProblem(form) {
    let total = 0;
    for (const input of form.querySelectorAll('input[type="file"]')) {
        if (!isGuarded(input) || input.disabled) continue;
        const heic = heicFile(input);
        if (heic) return { input, message: heicMessage(heic) };
        const problem = oversizedFile(input);
        if (problem) return { input, message: oversizeMessage(problem) };
        for (const file of input.files || []) total += file.size;
    }

    const postKb = metaNumber('upload-post-max-kb');
    // Sisakan ruang untuk isian teks & pembungkus multipart.
    const postLimit = postKb * 1024 - 256 * 1024;
    if (postKb && total > postLimit) {
        return {
            input: null,
            message: `Total berkas yang dipilih ${formatSize(total)}, melebihi batas sekali kirim server `
                + `(${formatSize(postKb * 1024)}). Kurangi jumlah atau ukuran berkas lalu kirim lagi.`,
        };
    }
    return null;
}

function onSubmitCapture(event) {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    const problem = uploadProblem(form);
    if (!problem) return;
    event.preventDefault();
    event.stopImmediatePropagation();
    if (problem.input) {
        clearInput(problem.input);
        problem.input.closest('label, div')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    notify(problem.message);
}

/* ------------------------------------------------------------- 3. Kunci tombol saat mengirim */

const hasSelectedFiles = (form) => [...form.querySelectorAll('input[type="file"]')].some((input) => input.name && input.files?.length);

export function lockFormForSubmit(form) {
    if (!form || form.dataset.submitting === '1' || form.target === '_blank') return;
    markSubmitted(form);
    form.dataset.submitting = '1';
    form.setAttribute('aria-busy', 'true');
    const label = hasSelectedFiles(form) ? 'Mengunggah berkas, mohon tunggu...' : 'Menyimpan, mohon tunggu...';
    const buttons = [
        ...form.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]'),
        ...(form.id ? document.querySelectorAll(`button[form="${CSS.escape(form.id)}"]`) : []),
    ];
    for (const button of buttons) {
        button.dataset.originalHtml = button.tagName === 'INPUT' ? button.value : button.innerHTML;
        button.style.minWidth = `${button.offsetWidth}px`;
        button.disabled = true;
        button.classList.add('opacity-70', 'cursor-wait');
        if (button.tagName === 'INPUT') button.value = label;
        else button.textContent = label;
    }
}

function unlockForms() {
    for (const form of document.querySelectorAll('form[data-submitting="1"]')) {
        delete form.dataset.submitting;
        form.removeAttribute('aria-busy');
        for (const button of form.querySelectorAll('[data-original-html]')) {
            if (button.tagName === 'INPUT') button.value = button.dataset.originalHtml;
            else button.innerHTML = button.dataset.originalHtml;
            delete button.dataset.originalHtml;
            button.disabled = false;
            button.classList.remove('opacity-70', 'cursor-wait');
            button.style.minWidth = '';
        }
    }
}

function onSubmitBubble(event) {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (!hasSelectedFiles(form) && !form.dataset.autosave) return;
    // Ditunda satu tick: data form (termasuk nilai tombol yang diklik) sudah terkumpul
    // dan penangan lain (modal konfirmasi) sempat membatalkan pengiriman.
    window.setTimeout(() => {
        if (!event.defaultPrevented) lockFormForSubmit(form);
    }, 0);
}

/* ------------------------------------------------------------- 2. Simpan isian otomatis */

const storeKey = (form) => STORE_PREFIX + form.dataset.autosave;

const savableFields = (form) => [...form.elements].filter((el) => el.name
    && !el.name.startsWith('_')
    && !el.disabled
    && !SKIP_TYPES.includes(el.type)
    && el.dataset.autosave !== 'off');

function collect(form) {
    const counter = {};
    return savableFields(form).map((el) => {
        const index = counter[el.name] = (counter[el.name] ?? -1) + 1;
        const entry = { n: el.name, i: index, v: el.value };
        if (el.type === 'checkbox' || el.type === 'radio') entry.c = el.checked;
        return entry;
    });
}

function saveDraft(form) {
    storage.set(storeKey(form), { at: Date.now(), path: window.location.pathname, fields: collect(form) });
}

function markSubmitted(form) {
    if (!form.dataset.autosave) return;
    saveDraft(form);
    storage.set(storeKey(form) + SUBMITTED_SUFFIX, Date.now());
}

function setFieldValue(el, entry) {
    if (el.type === 'checkbox' || el.type === 'radio') {
        if (el.checked === Boolean(entry.c)) return false;
        el.checked = Boolean(entry.c);
    } else if (el.type === 'hidden') {
        // Nilai tersembunyi dikelola komponen (mis. pilihan kampus); minta komponen memulihkannya sendiri.
        if (el.value === entry.v) return false;
        el.dispatchEvent(new CustomEvent('restore-value', { bubbles: true, detail: entry.v }));
        return true;
    } else {
        if (el.value === entry.v) return false;
        el.value = entry.v;
    }
    el.dispatchEvent(new Event('input', { bubbles: true }));
    el.dispatchEvent(new Event('change', { bubbles: true }));
    return true;
}

function restoreDraft(form) {
    const draft = storage.get(storeKey(form));
    if (!draft || !Array.isArray(draft.fields) || Date.now() - draft.at > DRAFT_MAX_AGE_MS) {
        storage.remove(storeKey(form));
        return false;
    }

    const byName = {};
    for (const el of savableFields(form)) (byName[el.name] ||= []).push(el);

    let restored = 0;
    for (const entry of draft.fields) {
        const el = byName[entry.n]?.[entry.i];
        if (el && setFieldValue(el, entry)) restored++;
    }
    return restored > 0;
}

function purgeAllDrafts() {
    for (const key of storage.keys()) {
        if (key.startsWith(STORE_PREFIX)) storage.remove(key);
    }
}

/** Setelah kiriman berhasil (halaman tujuan membawa pesan sukses), hapus draf form yang baru dikirim. */
function settleSubmittedDrafts() {
    const succeeded = !!document.querySelector('meta[name="flash-success"]');
    for (const key of storage.keys()) {
        if (!key.startsWith(STORE_PREFIX) || !key.endsWith(SUBMITTED_SUFFIX)) continue;
        storage.remove(key);
        if (succeeded) storage.remove(key.slice(0, -SUBMITTED_SUFFIX.length));
    }
}

export function initAutosave() {
    if (document.body?.hasAttribute('data-autosave-purge')) {
        purgeAllDrafts();
        return;
    }
    settleSubmittedDrafts();

    const forms = [...document.querySelectorAll('form[data-autosave]')];
    let restoredAny = false;
    for (const form of forms) {
        if (restoreDraft(form)) restoredAny = true;
        let timer = null;
        const schedule = () => {
            window.clearTimeout(timer);
            timer = window.setTimeout(() => saveDraft(form), 300);
        };
        form.addEventListener('input', schedule);
        form.addEventListener('change', schedule);
    }
    if (restoredAny) {
        notify('Isian yang belum tersimpan sudah dipulihkan. Periksa kembali, pilih ulang berkas (berkas tidak ikut tersimpan), lalu kirim.', 'info');
    }
}

/* ------------------------------------------------------------------------------ Pemasangan */

export function registerFormGuards() {
    document.addEventListener('change', onFileChange);
    document.addEventListener('submit', onSubmitCapture, true);
    document.addEventListener('submit', onSubmitBubble);
    // Kembali lewat tombol Back (bfcache): aktifkan lagi tombol yang terkunci.
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) unlockForms();
    });
    window.lockFormForSubmit = lockFormForSubmit;
}
