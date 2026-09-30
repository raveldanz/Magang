// Persiapan lampiran di browser: kompresi foto & perekam voice note.

function loadImage(file) {
    return new Promise((resolve, reject) => {
        const url = URL.createObjectURL(file);
        const img = new Image();
        img.onload = () => { URL.revokeObjectURL(url); resolve(img); };
        img.onerror = () => { URL.revokeObjectURL(url); reject(new Error('Gambar tidak dapat dibaca.')); };
        img.src = url;
    });
}

/**
 * Foto dari HP sering 3–6 MB, sedangkan batas upload PHP bisa hanya 2 MB.
 * Foto JPEG/WebP besar diperkecil (maks. sisi 1920px) sebelum diunggah; PNG hanya bila melebihi batas.
 */
export async function compressImageIfNeeded(file, maxBytes) {
    const isPhoto = /^image\/(jpeg|webp)$/.test(file.type);
    const isPng = file.type === 'image/png';
    if (!isPhoto && !isPng) return file;
    if (file.size <= maxBytes && (isPng || file.size <= 1.5 * 1024 * 1024)) return file;

    const img = await loadImage(file);
    for (const [edge, quality] of [[1920, 0.85], [1600, 0.78], [1280, 0.7]]) {
        const scale = Math.min(1, edge / Math.max(img.naturalWidth, img.naturalHeight));
        const canvas = document.createElement('canvas');
        canvas.width = Math.round(img.naturalWidth * scale);
        canvas.height = Math.round(img.naturalHeight * scale);
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#ffffff'; // latar putih untuk PNG transparan
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', quality));
        if (blob && blob.size <= maxBytes) {
            const name = file.name.replace(/\.[^.]+$/, '') + '.jpg';
            return new File([blob], name, { type: 'image/jpeg', lastModified: Date.now() });
        }
    }
    return file;
}

const RECORDER_TYPES = [
    ['audio/webm;codecs=opus', 'webm'],
    ['audio/webm', 'webm'],
    ['audio/mp4', 'm4a'],
    ['audio/ogg;codecs=opus', 'ogg'],
];

export function voiceRecordingSupported() {
    return !!(navigator.mediaDevices?.getUserMedia && window.MediaRecorder);
}

/**
 * Perekam voice note. start() meminta izin mikrofon; stop() menghasilkan File siap diunggah.
 * Butuh HTTPS atau localhost (aturan browser untuk akses mikrofon).
 */
export function createVoiceRecorder() {
    let recorder = null;
    let stream = null;
    let chunks = [];
    let format = RECORDER_TYPES[0];

    const release = () => {
        stream?.getTracks().forEach((track) => track.stop());
        stream = null;
        recorder = null;
    };

    return {
        async start() {
            if (!voiceRecordingSupported()) {
                throw new Error('Browser ini tidak mendukung perekaman suara.');
            }
            try {
                stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            } catch (_) {
                throw new Error('Izin mikrofon ditolak. Aktifkan akses mikrofon untuk situs ini di pengaturan browser.');
            }
            format = RECORDER_TYPES.find(([type]) => MediaRecorder.isTypeSupported?.(type)) || ['', 'webm'];
            recorder = new MediaRecorder(stream, format[0] ? { mimeType: format[0] } : undefined);
            chunks = [];
            recorder.ondataavailable = (e) => { if (e.data.size) chunks.push(e.data); };
            recorder.start(250);
        },

        stop() {
            return new Promise((resolve) => {
                if (!recorder) { resolve(null); return; }
                recorder.onstop = () => {
                    const type = (recorder?.mimeType || format[0] || 'audio/webm').split(';')[0];
                    const blob = new Blob(chunks, { type });
                    release();
                    const stamp = new Date().toISOString().slice(0, 19).replace(/[:T]/g, '-');
                    resolve(blob.size ? new File([blob], `pesan-suara-${stamp}.${format[1]}`, { type }) : null);
                };
                recorder.stop();
            });
        },

        cancel() {
            if (recorder && recorder.state !== 'inactive') {
                recorder.onstop = () => release();
                recorder.stop();
            } else {
                release();
            }
        },
    };
}
