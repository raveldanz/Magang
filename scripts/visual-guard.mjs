/**
 * Hard Visual Guard Module
 * Lokasi: scripts/visual-guard.mjs
 * 
 * Verifikator visual wajib untuk semua runner pengujian (Tier 1 Puppeteer & Tier 3 Playwright).
 * Menjamin sistem tidak pernah menilai halaman unstyled secara buta.
 */

import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const defaultProjectRoot = path.resolve(__dirname, '..');

/**
 * 1. Periksa keberadaan public/build/manifest.json
 * Jika tidak ada, batalkan pengujian seketika dengan status FAIL (Vite unbuilt).
 * Juga membersihkan file public/hot kedaluwarsa jika dev server tidak aktif
 * agar Laravel tidak menghasilkan tag link css yang putus.
 */
export function assertManifestBuilt(projectRoot = defaultProjectRoot) {
    const manifestPath = path.resolve(projectRoot, 'public/build/manifest.json');
    const legacyManifestPath = path.resolve(projectRoot, 'public/build/.vite/manifest.json');
    const manifestExists = fs.existsSync(manifestPath) || fs.existsSync(legacyManifestPath);

    if (!manifestExists) {
        const errorMsg = "FAIL (Vite unbuilt): Berkas 'public/build/manifest.json' tidak ditemukan! " +
            "Jalankan 'npm run build' terlebih dahulu sebelum menjalankan pengujian antarmuka.";
        const err = new Error(errorMsg);
        err.code = 'VITE_UNBUILT';
        throw err;
    }

    // Bersihkan public/hot jika dev server tidak aktif agar Laravel merender asset build produksi
    const hotPath = path.resolve(projectRoot, 'public/hot');
    if (fs.existsSync(hotPath)) {
        try {
            // Jika ada stale hot file, hapus agar Blade membaca manifest.json
            fs.unlinkSync(hotPath);
            console.log('[VisualGuard] Stale public/hot terdeteksi dan berhasil dibersihkan untuk mode produksi build.');
        } catch (e) {
            console.warn('[VisualGuard] Peringatan: Tidak dapat menghapus public/hot:', e.message);
        }
    }

    return true;
}

/**
 * Helper: Menghitung Luminance & WCAG Contrast Ratio
 */
export function getLuminance(r, g, b) {
    const a = [r, g, b].map(v => {
        v /= 255;
        return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
    });
    return a[0] * 0.2126 + a[1] * 0.7152 + a[2] * 0.0722;
}

export function parseRgb(colorStr) {
    if (!colorStr) return [0, 0, 0];
    const match = colorStr.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
    if (match) {
        return [parseInt(match[1]), parseInt(match[2]), parseInt(match[3])];
    }
    return [0, 0, 0];
}

export function getContrastRatio(rgb1, rgb2) {
    const lum1 = getLuminance(rgb1[0], rgb1[1], rgb1[2]);
    const lum2 = getLuminance(rgb2[0], rgb2[1], rgb2[2]);
    const brightest = Math.max(lum1, lum2);
    const darkest = Math.min(lum1, lum2);
    return Number(((brightest + 0.05) / (darkest + 0.05)).toFixed(2));
}

/**
 * 2. Periksa computed style elemen `body` dan elemen interaktif utama
 * - Jika font-family masih default browser (misal 'Times New Roman'), lempar error: "UNSTYLED_HTML_DETECTED"
 * - Jika tombol utama bernilai background 'rgb(240, 240, 240)', lempar error: "DEFAULT_BUTTON_DETECTED"
 * 
 * Bekerja kompatibel untuk Puppeteer Page dan Playwright Page.
 */
export async function assertVisualStyles(page, options = {}) {
    const requireButton = options.requireButton ?? true;

    const evaluation = await page.evaluate((reqBtn) => {
        // --- A. Inspeksi Body Font ---
        const bodyStyle = window.getComputedStyle(document.body);
        const bodyFont = bodyStyle.fontFamily || '';
        const bodyBg = bodyStyle.backgroundColor || '';

        const fontLower = bodyFont.toLowerCase();
        // Deteksi font default browser unstyled:
        // Default Blink/WebKit: "Times New Roman", "Times", generic "serif"
        const isTimes = fontLower.includes('times new roman') || fontLower.includes('times');
        const hasModernSans = fontLower.includes('figtree') || 
                              fontLower.includes('sans-serif') || 
                              fontLower.includes('system-ui') || 
                              fontLower.includes('inter') || 
                              fontLower.includes('arial');

        const isUnstyledFont = isTimes && !hasModernSans;

        // --- B. Inspeksi Elemen Interaktif Utama (Tombol Primary) ---
        const primaryBtn = document.querySelector('main button[type="submit"]') ||
                           document.querySelector('main button.bg-blue-600') ||
                           document.querySelector('button.bg-blue-600') ||
                           document.querySelector('button[type="submit"]') ||
                           document.querySelector('form button') ||
                           document.querySelector('button');

        let btnData = null;
        let isDefaultButton = false;

        if (primaryBtn) {
            const btnStyle = window.getComputedStyle(primaryBtn);
            const bg = (btnStyle.backgroundColor || '').trim();
            const color = (btnStyle.color || '').trim();

            // Default browser button background: rgb(240, 240, 240), rgba(240, 240, 240, 1), buttonface
            if (bg === 'rgb(240, 240, 240)' || bg === 'rgba(240, 240, 240, 1)' || bg === 'buttonface') {
                isDefaultButton = true;
            }

            btnData = {
                text: primaryBtn.innerText ? primaryBtn.innerText.trim() : (primaryBtn.value || ''),
                tagName: primaryBtn.tagName,
                className: primaryBtn.className,
                color: color,
                backgroundColor: bg,
                fontFamily: btnStyle.fontFamily,
                borderRadius: btnStyle.borderRadius,
                padding: btnStyle.padding
            };
        }

        // --- C. Inspeksi Card Container Layout Centering ---
        const card = document.querySelector('.bg-white, form, .rounded-3xl');
        let cardData = null;
        const docWidth = document.documentElement.clientWidth;
        const scrollWidth = document.documentElement.scrollWidth;
        const hasHorizontalScroll = scrollWidth > docWidth;

        if (card) {
            const rect = card.getBoundingClientRect();
            const cardCenterX = rect.left + rect.width / 2;
            const docCenterX = docWidth / 2;
            const centerOffset = Math.abs(cardCenterX - docCenterX);
            cardData = {
                width: Math.round(rect.width),
                height: Math.round(rect.height),
                centerOffsetPx: Math.round(centerOffset),
                isHorizontallyCentered: centerOffset <= 24
            };
        }

        return {
            bodyFont,
            bodyBg,
            isUnstyledFont,
            primaryBtn: btnData,
            isDefaultButton,
            reqBtn,
            card: cardData,
            docWidth,
            scrollWidth,
            hasHorizontalScroll
        };
    }, requireButton);

    // Hard Guard 1: Font Family Default Browser
    if (evaluation.isUnstyledFont) {
        const msg = `UNSTYLED_HTML_DETECTED: Elemen <body> menggunakan font-family default browser ('${evaluation.bodyFont}'). Stylesheet Tailwind CSS gagal dimuat!`;
        const err = new Error(msg);
        err.code = 'UNSTYLED_HTML_DETECTED';
        throw err;
    }

    // Hard Guard 2: Background Tombol Default Browser
    if (evaluation.primaryBtn && evaluation.isDefaultButton) {
        const msg = `DEFAULT_BUTTON_DETECTED: Tombol '${evaluation.primaryBtn.text}' memiliki background '${evaluation.primaryBtn.backgroundColor}' (default browser button style). Kelas Tailwind CSS (misal 'bg-blue-600') tidak ter-render!`;
        const err = new Error(msg);
        err.code = 'DEFAULT_BUTTON_DETECTED';
        throw err;
    }

    if (requireButton && !evaluation.primaryBtn) {
        const msg = "NO_PRIMARY_BUTTON_DETECTED: Halaman tidak memiliki elemen tombol interaktif utama.";
        const err = new Error(msg);
        err.code = 'NO_PRIMARY_BUTTON_DETECTED';
        throw err;
    }

    // Hitung Rasio Kontras WCAG AA jika tombol ada
    let contrastRatio = 0;
    let wcagPass = false;
    if (evaluation.primaryBtn) {
        const textColorRgb = parseRgb(evaluation.primaryBtn.color);
        const bgRgb = parseRgb(evaluation.primaryBtn.backgroundColor);
        contrastRatio = getContrastRatio(textColorRgb, bgRgb);
        wcagPass = contrastRatio >= 4.5;
    }

    return {
        ...evaluation,
        contrastRatio,
        wcagPass
    };
}

/**
 * Runner Mandiri Visual Guard CLI
 */
if (process.argv[1] === fileURLToPath(import.meta.url)) {
    console.log('====================================================');
    console.log('  HARD VISUAL GUARD: VERIFIKASI BUILD & STYLING');
    console.log('====================================================');

    try {
        console.log('[1/2] Memeriksa Vite build manifest...');
        assertManifestBuilt();
        console.log('[+] PASS: public/build/manifest.json terverifikasi aktif.');
    } catch (err) {
        console.error(`[-] ${err.message}`);
        process.exit(1);
    }
}
