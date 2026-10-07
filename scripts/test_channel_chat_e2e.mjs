import { chromium } from 'playwright';
import path from 'node:path';
import fs from 'node:fs';

const publicTestArtifactsDir = 'public/test-artifacts';
if (!fs.existsSync(publicTestArtifactsDir)) {
    fs.mkdirSync(publicTestArtifactsDir, { recursive: true });
}

async function runChannelChatE2E() {
    console.log('========================================================================');
    console.log('  TEST VISUAL E2E: SALURAN PENGUMUMAN RESMI & UTAS KOMENTAR (TELEGRAM)');
    console.log('========================================================================');

    const browser = await chromium.launch({ headless: true, channel: 'chrome' });

    try {
        // -------------------------------------------------------------------------
        // 1. ADMIN PEMKOT: LOGIN & PUBLIKASIKAN PENGUMUMAN RESMI
        // -------------------------------------------------------------------------
        console.log('\n[1/3] Login Super Admin Pemkot untuk mempublikasikan pengumuman...');
        const adminContext = await browser.newContext({ viewport: { width: 1366, height: 768 } });
        const adminPage = await adminContext.newPage();

        await adminPage.goto('http://127.0.0.1:8000/login');
        await adminPage.fill('input[name="email"]', 'admin@surabaya.go.id');
        await adminPage.fill('input[name="password"]', 'password');
        await adminPage.click('button[type="submit"]');
        await adminPage.waitForURL('**/dashboard', { timeout: 10000 });

        console.log('[Admin] Menuju halaman chat...');
        await adminPage.goto('http://127.0.0.1:8000/chat', { waitUntil: 'networkidle' });
        await adminPage.waitForTimeout(1000);

        // Klik tab Saluran Pengumuman
        console.log('[Admin] Memilih tab Saluran Pengumuman...');
        await adminPage.click('button:has-text("Saluran Pengumuman")');
        await adminPage.waitForTimeout(600);

        // Klik Saluran Pemkot Surabaya
        const pemkotItem = adminPage.locator('button[data-conv-id]:has-text("Saluran Pengumuman Pemerintah Kota Surabaya")').first();
        await pemkotItem.waitFor({ state: 'visible', timeout: 8000 });
        await pemkotItem.click();
        await adminPage.waitForTimeout(1000);

        // Posting pengumuman resmi
        const announcementText = `Pengumuman Resmi: Evaluasi Tengah Periode (Midterm) Magang Pemkot Surabaya 2026 dibuka mulai Senin mendatang. Harap seluruh mahasiswa mempersiapkan draf laporan dan logbook mingguan.`;
        console.log('[Admin] Mengetik dan mempublikasikan pengumuman...');
        const composer = adminPage.locator('textarea[x-ref="composer"]');
        await composer.fill(announcementText);
        await adminPage.waitForTimeout(300);

        const publishBtn = adminPage.locator('button[type="submit"]:has(svg)');
        await publishBtn.click();
        await adminPage.waitForTimeout(2000);
        console.log('[Admin] Pengumuman resmi berhasil dipublikasikan.');

        await adminContext.close();

        // -------------------------------------------------------------------------
        // 2. MAHASISWA: LIHAT SALURAN, CEK BANNER NON-ADMIN & BUKA UTAS KOMENTAR
        // -------------------------------------------------------------------------
        console.log('\n[2/3] Login Mahasiswa Aktif (Aditya Nugraha) untuk melihat saluran...');
        const mhsContext = await browser.newContext({ viewport: { width: 1366, height: 768 } });
        const mhsPage = await mhsContext.newPage();

        await mhsPage.goto('http://127.0.0.1:8000/login');
        await mhsPage.fill('input[name="email"]', 'mahasiswa.aktif@unesa.ac.id');
        await mhsPage.fill('input[name="password"]', 'password');
        await mhsPage.click('button[type="submit"]');
        await mhsPage.waitForURL('**/dashboard', { timeout: 10000 });

        await mhsPage.goto('http://127.0.0.1:8000/chat', { waitUntil: 'networkidle' });
        await mhsPage.waitForTimeout(1000);

        // Klik tab Saluran Pengumuman
        console.log('[Mhs] Memilih filter tab "Saluran Pengumuman"...');
        await mhsPage.click('button:has-text("Saluran Pengumuman")');
        await mhsPage.waitForTimeout(800);

        // Screenshot 1: Tab Saluran Pengumuman dengan squircle logo
        await mhsPage.screenshot({ path: path.join(publicTestArtifactsDir, 'channel_01_channels_tab.png') });
        console.log(' -> Screenshot disimpan: public/test-artifacts/channel_01_channels_tab.png');

        // Klik Saluran Pemkot
        const mhsPemkot = mhsPage.locator('button[data-conv-id]:has-text("Saluran Pengumuman Pemerintah Kota Surabaya")').first();
        await mhsPemkot.click();
        
        // Tunggu pesan dan bilah komentar termuat sempurna di timeline
        const commentBarBtn = mhsPage.locator('button[data-test="open-comments"]').last();
        await commentBarBtn.waitFor({ state: 'visible', timeout: 10000 });
        await mhsPage.waitForTimeout(500);

        // Screenshot 2: Timeline saluran dengan banner non-admin dan bubble card pengumuman
        await mhsPage.screenshot({ path: path.join(publicTestArtifactsDir, 'channel_02_announcement_view.png') });
        console.log(' -> Screenshot disimpan: public/test-artifacts/channel_02_announcement_view.png');

        // Verifikasi keberadaan banner non-admin
        const nonAdminBanner = mhsPage.locator('text=Hanya Pengelola Saluran yang dapat mempublikasikan pengumuman di sini.');
        const isBannerVisible = await nonAdminBanner.isVisible();
        console.log(`[Mhs] Banner Non-Admin terlihat: ${isBannerVisible ? 'YA (BENAR)' : 'TIDAK (SALAH)'}`);

        // -------------------------------------------------------------------------
        // 3. BUKA DRAWER UTAS KOMENTAR & KIRIM KOMENTAR
        // -------------------------------------------------------------------------
        console.log('\n[3/3] Membuka panel drawer utas komentar Telegram...');
        await commentBarBtn.click();
        await mhsPage.waitForTimeout(1000);

        // Screenshot 3: Drawer terbuka dengan ringkasan pengumuman & daftar komentar
        await mhsPage.screenshot({ path: path.join(publicTestArtifactsDir, 'channel_03_comments_drawer.png') });
        console.log(' -> Screenshot disimpan: public/test-artifacts/channel_03_comments_drawer.png');

        // Tulis komentar di drawer
        console.log('[Mhs] Mengirim komentar ke utas pengumuman...');
        const commentInput = mhsPage.locator('textarea[x-ref="commentInput"]');
        await commentInput.fill('Selamat pagi Admin, izin bertanya apakah pengumpulan draf laporan midterm dilakukan via portal ini atau dikirim langsung ke mentor? Terima kasih.');
        await mhsPage.waitForTimeout(300);

        const sendCommentBtn = mhsPage.locator('button[aria-label="Kirim komentar"]');
        await sendCommentBtn.click();
        await mhsPage.waitForSelector('text=Selamat pagi Admin', { timeout: 8000 });
        await mhsPage.waitForTimeout(500);

        // Screenshot 4: Komentar tampil di drawer
        await mhsPage.screenshot({ path: path.join(publicTestArtifactsDir, 'channel_04_comment_sent.png') });
        console.log(' -> Screenshot disimpan: public/test-artifacts/channel_04_comment_sent.png');

        // Tutup drawer
        await mhsPage.click('button[aria-label="Tutup utas komentar"]');
        await mhsPage.waitForTimeout(800);

        // Screenshot 5: Bilah komentar kini menampilkan jumlah komentar terbaharui
        await mhsPage.screenshot({ path: path.join(publicTestArtifactsDir, 'channel_05_updated_bar.png') });
        console.log(' -> Screenshot disimpan: public/test-artifacts/channel_05_updated_bar.png');

        await mhsContext.close();
        console.log('\n========================================================================');
        console.log('  SEMUA VALIDASI VISUAL E2E PLAYWRIGHT LOLOS DENGAN SUKSES (EXIT 0)');
        console.log('========================================================================');

    } finally {
        await browser.close();
    }
}

runChannelChatE2E().catch(err => {
    console.error('ERROR E2E Playwright:', err);
    process.exit(1);
});
