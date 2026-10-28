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

async function runAudit() {
    console.log('🚀 Memulai Audit Visual Playwright (Chrome Channel)...');

    const browser = await chromium.launch({
        channel: 'chrome',
        headless: true,
    });

    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 }
    });
    const page = await context.newPage();

    try {
        // 1. Audit Katalog Publik (/proyek-dinas)
        console.log('📸 1. Mengaudit Katalog Proyek Dinas Publik (/proyek-dinas)...');
        await page.goto('http://127.0.0.1:8000/proyek-dinas', { waitUntil: 'networkidle' });
        await page.waitForTimeout(1000);
        await page.screenshot({
            path: path.join(artifactDir, 'katalog_proyek_2020_2026.png'),
            fullPage: false
        });
        console.log('✅ Screenshot katalog_proyek_2020_2026.png tersimpan.');

        // Test Click on Card Title / Image to Open Detail
        const firstCardTitleLink = page.locator('a.group\\/title').first();
        if (await firstCardTitleLink.isVisible()) {
            console.log('🔗 Menguji klik judul proyek pada card...');
            await firstCardTitleLink.click();
            await page.waitForLoadState('networkidle');
            console.log('✅ Berhasil berpindah ke URL detail:', page.url());
        }

        // 2. Audit Login Super Admin (/login)
        console.log('📸 2. Mengaudit Dashboard & Navbar Super Admin...');
        await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
        await page.fill('input[name="email"]', 'admin@surabaya.go.id');
        await page.fill('input[name="password"]', 'password');
        await page.click('button[type="submit"]');
        await page.waitForLoadState('networkidle');

        // Navigasi ke /proyek-dinas sebagai Super Admin
        await page.goto('http://127.0.0.1:8000/proyek-dinas', { waitUntil: 'networkidle' });
        await page.waitForTimeout(1000);

        // Buka menu titik 3 pada kartu pertama
        const dotsButton = page.locator('button[title="Opsi Proyek"]').first();
        if (await dotsButton.isVisible()) {
            await dotsButton.click();
            await page.waitForTimeout(500);
        }

        await page.screenshot({
            path: path.join(artifactDir, 'katalog_super_admin_titik3.png'),
            fullPage: false
        });
        console.log('✅ Screenshot katalog_super_admin_titik3.png tersimpan.');

        // 3. Audit Halaman Detail Proyek sebagai Super Admin
        console.log('📸 3. Mengaudit Detail Proyek (/projects/16) dengan Menu 3-Titik...');
        await page.goto('http://127.0.0.1:8000/projects/16', { waitUntil: 'networkidle' });
        await page.waitForTimeout(1000);

        const headerDotsButton = page.locator('button[title="Opsi Kelola Proyek"]').first();
        if (await headerDotsButton.isVisible()) {
            await headerDotsButton.click();
            await page.waitForTimeout(500);
        }

        await page.screenshot({
            path: path.join(artifactDir, 'detail_proyek_super_admin_titik3.png'),
            fullPage: false
        });
        console.log('✅ Screenshot detail_proyek_super_admin_titik3.png tersimpan.');

        // 4. Audit Dashboard Mahasiswa (Navbar Bersih Tanpa Proyek Dinas)
        console.log('📸 4. Mengaudit Dashboard Mahasiswa...');
        const mhsContext = await browser.newContext({ viewport: { width: 1440, height: 900 } });
        const mhsPage = await mhsContext.newPage();
        await mhsPage.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
        await mhsPage.fill('input[name="email"]', 'eka.aktif@unesa.ac.id');
        await mhsPage.fill('input[name="password"]', 'password');
        await mhsPage.click('button[type="submit"]');
        await mhsPage.waitForLoadState('networkidle');

        await mhsPage.waitForTimeout(1000);
        await mhsPage.screenshot({
            path: path.join(artifactDir, 'dashboard_mahasiswa_clean_nav.png'),
            fullPage: false
        });
        console.log('✅ Screenshot dashboard_mahasiswa_clean_nav.png tersimpan.');

        console.log('🎉 Seluruh Audit Visual Playwright Berhasil 100%!');
    } catch (err) {
        console.error('❌ Gagal menjalankan audit visual:', err);
        process.exit(1);
    } finally {
        await browser.close();
    }
}

runAudit();
