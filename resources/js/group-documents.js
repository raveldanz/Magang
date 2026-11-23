/**
 * Gabung CV / KTP-KTM seluruh anggota kelompok menjadi satu PDF di peramban.
 *
 * Tautan bertanda [data-group-bundle] menunjuk ke route admin.groups.documents (manifest JSON). Server memeriksa
 * hak akses & kelengkapan; berkas tiap anggota diambil lewat URL di manifest lalu digabung dengan pdf-lib
 * (dimuat hanya saat dibutuhkan). Tidak memerlukan Node.js di server, berjalan tanpa internet.
 */

const FAIL = 'PDF gabungan belum dapat dibuat. Muat ulang halaman lalu coba lagi.';

export async function mergeDocuments(files) {
    const { PDFDocument, PageSizes } = await import('pdf-lib');
    const result = await PDFDocument.create();

    for (const file of files) {
        try {
            if (file.type === 'application/pdf') {
                const source = await PDFDocument.load(file.bytes);
                if (!source.getPageCount()) throw new Error('kosong');
                const pages = await result.copyPages(source, source.getPageIndices());
                pages.forEach((page) => result.addPage(page));
            } else {
                const image = file.type === 'image/png'
                    ? await result.embedPng(file.bytes)
                    : await result.embedJpg(file.bytes);
                const page = result.addPage(PageSizes.A4);
                const { width, height } = image.scaleToFit(page.getWidth() - 48, page.getHeight() - 48);
                page.drawImage(image, {
                    x: (page.getWidth() - width) / 2,
                    y: (page.getHeight() - height) / 2,
                    width,
                    height,
                });
            }
        } catch {
            throw new Error(`Berkas milik ${file.name} tidak dapat dibaca. Periksa apakah berkas rusak atau PDF diproteksi kata sandi.`);
        }
    }

    return result.save({ useObjectStreams: false });
}

function showMessage(link, text) {
    let box = link.parentElement?.querySelector('[data-group-bundle-message]');
    if (!box) {
        box = document.createElement('p');
        box.setAttribute('data-group-bundle-message', '');
        box.setAttribute('role', 'alert');
        box.className = 'mt-1 text-sm font-semibold text-rose-700';
        link.insertAdjacentElement('afterend', box);
    }
    box.textContent = text;
    box.hidden = text === '';
}

async function buildBundle(link) {
    const response = await fetch(link.href, {
        credentials: 'same-origin',
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    });
    const manifest = await response.json().catch(() => ({}));
    if (!response.ok || !Array.isArray(manifest.files)) {
        throw new Error(manifest.message || FAIL);
    }

    const files = [];
    for (const item of manifest.files) {
        const fileResponse = await fetch(item.url, { credentials: 'same-origin' });
        if (!fileResponse.ok) throw new Error(`Berkas milik ${item.name} tidak dapat diunduh.`);
        files.push({ name: item.name, type: item.type, bytes: await fileResponse.arrayBuffer() });
    }

    const bytes = await mergeDocuments(files);
    const url = URL.createObjectURL(new Blob([bytes], { type: 'application/pdf' }));
    const anchor = document.createElement('a');
    anchor.href = url;
    anchor.download = manifest.filename || 'Dokumen-Kelompok.pdf';
    document.body.appendChild(anchor);
    anchor.click();
    anchor.remove();
    setTimeout(() => URL.revokeObjectURL(url), 60000);
}

document.addEventListener('click', async (event) => {
    const link = event.target.closest('a[data-group-bundle]');
    if (!link || event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey) return;
    event.preventDefault();
    if (link.getAttribute('aria-busy') === 'true') return;

    const original = [...link.childNodes].map((node) => node.cloneNode(true));
    link.setAttribute('aria-busy', 'true');
    link.textContent = 'Menyiapkan PDF…';
    showMessage(link, '');
    try {
        await buildBundle(link);
    } catch (error) {
        showMessage(link, error instanceof Error && error.message ? error.message : FAIL);
    } finally {
        link.replaceChildren(...original);
        link.removeAttribute('aria-busy');
    }
});
