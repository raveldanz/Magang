// Pemanggil endpoint chat: JSON + CSRF + pesan error berbahasa Indonesia.

export function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function statusMessage(status) {
    if (status === 401 || status === 419) return 'Sesi Anda berakhir. Muat ulang halaman lalu login kembali.';
    if (status === 403) return 'Anda tidak memiliki akses untuk tindakan ini.';
    if (status === 404) return 'Data tidak ditemukan atau sudah dihapus.';
    if (status === 413) return 'Ukuran lampiran terlalu besar untuk diunggah.';
    if (status === 429) return 'Terlalu banyak permintaan. Tunggu sebentar lalu coba lagi.';
    return 'Koneksi bermasalah. Periksa internet Anda lalu coba lagi.';
}

export function errorFrom(status, data) {
    let message = null;
    if (data?.errors) {
        const first = Object.values(data.errors)[0];
        message = Array.isArray(first) ? first[0] : first;
    }
    // Pesan bawaan framework yang teknis (bahasa Inggris) diganti pesan umum
    const technical = /^(Server Error|Unauthenticated\.|CSRF token mismatch\.|The POST data is too large\.|Not Found|Too Many Attempts\.)$/i;
    if (!message && data?.message && !technical.test(data.message) && status !== 413) {
        message = data.message;
    }
    const error = new Error(message || statusMessage(status));
    error.status = status;
    return error;
}

export async function chatFetch(url, options = {}) {
    let response;
    try {
        response = await fetch(url, {
            credentials: 'same-origin',
            ...options,
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken(),
                ...(options.headers || {}),
            },
        });
    } catch (_) {
        throw errorFrom(0, null);
    }

    let data = null;
    try { data = await response.json(); } catch (_) { /* respons bukan JSON */ }
    if (!response.ok) throw errorFrom(response.status, data);
    return data;
}

export function sendJson(url, payload = {}, method = 'POST') {
    return chatFetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
    });
}

// XMLHttpRequest dipakai (bukan fetch) agar progres unggah bisa ditampilkan
export function uploadForm(url, formData, onProgress) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', url);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken());
        xhr.upload.onprogress = (e) => { if (e.lengthComputable) onProgress(Math.round((e.loaded / e.total) * 100)); };
        xhr.onload = () => {
            let data = null;
            try { data = JSON.parse(xhr.responseText); } catch (_) { /* bukan JSON */ }
            if (xhr.status >= 200 && xhr.status < 300) resolve(data);
            else reject(errorFrom(xhr.status, data));
        };
        xhr.onerror = () => reject(errorFrom(0, null));
        xhr.send(formData);
    });
}
