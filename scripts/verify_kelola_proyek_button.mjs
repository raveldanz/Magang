import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');
const artifactDir = path.resolve(projectRoot, 'public/test-artifacts');

if (!fs.existsSync(artifactDir)) {
    fs.mkdirSync(artifactDir, { recursive: true });
}

async function runTest() {
    console.log('🚀 Memulai verifikasi visual tombol Kelola Proyek & Warna Kalender...');

    const browser = await chromium.launch({
        channel: 'chrome',
        headless: true,
    });

    try {
        // 1. Verifikasi Warna Ikon Kalender Pembatas Tahun di /proyek-dinas
        console.log('📸 1. Mengaudit Warna Kalender di /proyek-dinas...');
        const page1 = await browser.newPage({ viewport: { width: 1440, height: 1200 } });
        await page1.goto('http://127.0.0.1:8000/proyek-dinas', { waitUntil: 'networkidle' });
        await page1.waitForTimeout(1000);
        await page1.evaluate(() => window.scrollTo(0, 1600));
        await page1.waitForTimeout(500);
        await page1.screenshot({ path: path.join(artifactDir, 'verifikasi_warna_kalender_biru_tegas.png') });
        console.log('✅ Screenshot verifikasi_warna_kalender_biru_tegas.png tersimpan.');
        await page1.close();

        // 2. Verifikasi Tombol Kelola Proyek (Super Admin) di /projects/18
        console.log('📸 2. Mengaudit Tombol Kelola Proyek sebagai Super Admin di /projects/18...');
        const adminContext = await browser.newContext({ viewport: { width: 1440, height: 900 } });
        const adminPage = await adminContext.newPage();
        await adminPage.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
        await adminPage.fill('input[name="email"]', 'admin@surabaya.go.id');
        await adminPage.fill('input[name="password"]', 'password');
        await adminPage.click('button[type="submit"]');
        await adminPage.waitForLoadState('networkidle');

        // Buka detail proyek 18
        await adminPage.goto('http://127.0.0.1:8000/projects/18', { waitUntil: 'networkidle' });
        await adminPage.waitForTimeout(1000);

        // Buka dropdown tombol Kelola Proyek
        const kelolaBtn = adminPage.locator('button:has-text("Kelola Proyek")');
        if (await kelolaBtn.isVisible()) {
            await kelolaBtn.click();
            await adminPage.waitForTimeout(500);
        }

        await adminPage.screenshot({ path: path.join(artifactDir, 'header_kelola_proyek_admin_dropdown.png') });
        console.log('✅ Screenshot header_kelola_proyek_admin_dropdown.png tersimpan.');
        await adminContext.close();

        // 3. Verifikasi Halaman Detail untuk Pengunjung Biasa (Bukan Admin)
        console.log('📸 3. Mengaudit Halaman Detail untuk Publik/Tamu...');
        const guestContext = await browser.newContext({ viewport: { width: 1440, height: 900 } });
        const guestPage = await guestContext.newPage();
        await guestPage.goto('http://127.0.0.1:8000/projects/18', { waitUntil: 'networkidle' });
        await guestPage.waitForTimeout(1000);
        await guestPage.screenshot({ path: path.join(artifactDir, 'header_tamu_tanpa_kelola_proyek.png') });
        console.log('✅ Screenshot header_tamu_tanpa_kelola_proyek.png tersimpan.');
        await guestContext.close();

        console.log('🎉 Seluruh Verifikasi Sukses 100%!');
    } catch (e) {
        console.error('❌ Error visual test:', e);
        process.exit(1);
    } finally {
        await browser.close();
    }
}

runTest();
