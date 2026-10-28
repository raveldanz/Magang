import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');

const brainDir = 'C:\\Users\\TK ABA SBY 69 (3)\\.gemini\\antigravity-ide\\brain\\49f22495-dbe7-417d-b0e2-10b44c961920';
const publicDir = path.resolve(projectRoot, 'public/test-artifacts');

if (!fs.existsSync(publicDir)) {
    fs.mkdirSync(publicDir, { recursive: true });
}

function saveArtifact(srcFilename) {
    const srcPath = path.join(publicDir, srcFilename);
    const destPath = path.join(brainDir, srcFilename);
    try {
        if (fs.existsSync(srcPath)) {
            fs.copyFileSync(srcPath, destPath);
        }
    } catch (e) {
        console.error(`[Artifact Copy Warning] ${e.message}`);
    }
}

async function run() {
    console.log('[Visual Test] Menjalankan Chromium channel: chrome...');
    const browser = await chromium.launch({
        headless: true,
        channel: 'chrome'
    });

    // 1. Tampilan Publik Katalog Proyek Dinas (/proyek-dinas)
    const guestContext = await browser.newContext({
        viewport: { width: 1440, height: 900 }
    });
    const guestPage = await guestContext.newPage();

    console.log('[Visual Test] Mengakses katalog publik: http://127.0.0.1:8000/proyek-dinas...');
    await guestPage.goto('http://127.0.0.1:8000/proyek-dinas', { waitUntil: 'networkidle' });
    await guestPage.waitForTimeout(1000);

    const shotCatalog = 'proyek_dinas_katalog_public.png';
    await guestPage.screenshot({ path: path.join(publicDir, shotCatalog), fullPage: true });
    saveArtifact(shotCatalog);
    console.log(`[Visual Test] Tersimpan: ${shotCatalog}`);

    // 2. Detail Proyek (ID 16) sebagai Pengunjung Tamu (Guest)
    console.log('[Visual Test] Membuka detail proyek ID 16 sebagai tamu...');
    await guestPage.goto('http://127.0.0.1:8000/projects/16', { waitUntil: 'networkidle' });
    await guestPage.waitForTimeout(1000);

    // Like proyek via AJAX
    console.log('[Visual Test] Menguji klik tombol Like sebagai tamu...');
    const likeBtn = await guestPage.locator('button:has-text("Suka Proyek Ini"), button:has-text("Disukai")').first();
    if (await likeBtn.isVisible()) {
        await likeBtn.click();
        await guestPage.waitForTimeout(1200);
    }

    // Buka dropdown Bagikan (Share)
    console.log('[Visual Test] Membuka dropdown Bagikan...');
    const shareBtn = await guestPage.locator('button:has-text("Bagikan")').first();
    if (await shareBtn.isVisible()) {
        await shareBtn.click();
        await guestPage.waitForTimeout(500);
    }

    const shotLikeShare = 'proyek_dinas_like_share_guest.png';
    await guestPage.screenshot({ path: path.join(publicDir, shotLikeShare) });
    saveArtifact(shotLikeShare);
    console.log(`[Visual Test] Tersimpan: ${shotLikeShare}`);

    // 3. Uji Filter Sensor Kata Kotor (Profanity Filter Real-Time)
    console.log('[Visual Test] Menguji pemicu peringatan sensor kata kotor...');
    const nameInput = guestPage.locator('input[name="author_name"]');
    if (await nameInput.isVisible()) {
        await nameInput.fill('Pengunjung Uji');
    }

    const commentTextarea = guestPage.locator('textarea[name="comment"]');
    if (await commentTextarea.isVisible()) {
        await commentTextarea.fill('Sistem ini anjing dan tidak berguna');
        await guestPage.waitForTimeout(800);
    }

    const shotProfanity = 'proyek_dinas_live_profanity_alert.png';
    await guestPage.screenshot({ path: path.join(publicDir, shotProfanity) });
    saveArtifact(shotProfanity);
    console.log(`[Visual Test] Tersimpan: ${shotProfanity}`);

    // Bersihkan dan ketik komentar sopan & berkualitas
    console.log('[Visual Test] Mengirim komentar berkualitas...');
    if (await commentTextarea.isVisible()) {
        await commentTextarea.fill('Luar biasa inovasi SIM-Magang ini, sangat mempermudah administrasi pelayanan dinas dan bermanfaat luas bagi masyarakat kota Surabaya!');
        await guestPage.waitForTimeout(500);
        const submitCommentBtn = guestPage.locator('button:has-text("Kirim Komentar")');
        await submitCommentBtn.click();
        await guestPage.waitForNavigation({ waitUntil: 'networkidle' }).catch(() => {});
        await guestPage.waitForTimeout(1000);
    }

    const shotCommentSuccess = 'proyek_dinas_comment_published.png';
    await guestPage.screenshot({ path: path.join(publicDir, shotCommentSuccess), fullPage: true });
    saveArtifact(shotCommentSuccess);
    console.log(`[Visual Test] Tersimpan: ${shotCommentSuccess}`);

    await guestContext.close();

    // 4. Login sebagai Super Admin untuk Verifikasi Tombol Moderasi Hapus Komentar
    const adminContext = await browser.newContext({
        viewport: { width: 1440, height: 900 }
    });
    const adminPage = await adminContext.newPage();

    console.log('[Visual Test] Login Super Admin: admin@surabaya.go.id...');
    await adminPage.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await adminPage.fill('input[name="email"]', 'admin@surabaya.go.id');
    await adminPage.fill('input[name="password"]', 'password');
    await adminPage.click('button[type="submit"]');
    await adminPage.waitForTimeout(2000);

    console.log('[Visual Test] Super Admin membuka detail proyek ID 16 untuk cek moderasi...');
    await adminPage.goto('http://127.0.0.1:8000/projects/16', { waitUntil: 'networkidle' });
    await adminPage.waitForTimeout(1000);

    const shotAdminModeration = 'proyek_dinas_admin_moderation_view.png';
    await adminPage.screenshot({ path: path.join(publicDir, shotAdminModeration), fullPage: true });
    saveArtifact(shotAdminModeration);
    console.log(`[Visual Test] Tersimpan: ${shotAdminModeration}`);

    await adminContext.close();

    // 5. Tampilan Mobile Responsive (390 x 844 iPhone 14 / Pixel)
    const mobileContext = await browser.newContext({
        viewport: { width: 390, height: 844 },
        isMobile: true
    });
    const mobilePage = await mobileContext.newPage();

    console.log('[Visual Test] Mengakses katalog versi mobile...');
    await mobilePage.goto('http://127.0.0.1:8000/proyek-dinas', { waitUntil: 'networkidle' });
    await mobilePage.waitForTimeout(1000);

    const shotMobile = 'proyek_dinas_mobile_responsive.png';
    await mobilePage.screenshot({ path: path.join(publicDir, shotMobile), fullPage: true });
    saveArtifact(shotMobile);
    console.log(`[Visual Test] Tersimpan: ${shotMobile}`);

    await mobileContext.close();
    await browser.close();
    console.log('[Visual Test] Selesai! Seluruh tangkapan layar berhasil disimpan.');
}

run().catch(err => {
    console.error('[Visual Test Error]', err);
    process.exit(1);
});
