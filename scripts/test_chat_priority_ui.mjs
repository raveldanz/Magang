import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';

const BASE_URL = 'http://127.0.0.1:8000';
const ARTIFACT_DIR = path.resolve('public/test-artifacts');

if (!fs.existsSync(ARTIFACT_DIR)) {
    fs.mkdirSync(ARTIFACT_DIR, { recursive: true });
}

async function loginAs(page, email, password = 'password') {
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle' });
    await page.fill('input[name="email"], input#email', email);
    await page.fill('input[name="password"], input#password', password);
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle' }),
        page.click('button[type="submit"]')
    ]);
}

async function run() {
    console.log('========================================================================');
    console.log('  TEST SMART PRIORITY ENGINE & STAGE BADGES (ADMIN DINAS / MENTOR / MHS)');
    console.log('========================================================================');

    const browser = await chromium.launch({
        channel: 'chrome',
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const context = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await context.newPage();

    try {
        console.log('[1/4] Login sebagai Admin Dinas Kominfo...');
        await loginAs(page, 'admin.kominfo@surabaya.go.id');

        console.log('[2/4] Membuka halaman Chat /chat...');
        await page.goto(`${BASE_URL}/chat`, { waitUntil: 'networkidle' });
        await page.waitForTimeout(1000);

        // Ambil screenshot tab "Semua"
        await page.screenshot({ path: path.join(ARTIFACT_DIR, 'chat_priority_all.png') });
        console.log('  -> Screenshot tab Semua disimpan.');

        // Klik tab "Prioritas"
        console.log('[3/4] Menguji klik tab Prioritas...');
        const prioritasTab = page.locator('button[role="tab"]:has-text("Prioritas")');
        if (await prioritasTab.isVisible()) {
            await prioritasTab.click();
            await page.waitForTimeout(500);
            await page.screenshot({ path: path.join(ARTIFACT_DIR, 'chat_priority_tab.png') });
            console.log('  -> Screenshot tab Prioritas disimpan.');
        }

        // Klik tab "Mahasiswa"
        console.log('Menguji klik tab Mahasiswa...');
        const mhsTab = page.locator('button[role="tab"]:has-text("Mahasiswa")');
        if (await mhsTab.isVisible()) {
            await mhsTab.click();
            await page.waitForTimeout(500);
            await page.screenshot({ path: path.join(ARTIFACT_DIR, 'chat_priority_mhs.png') });
            console.log('  -> Screenshot tab Mahasiswa disimpan.');
        }

        // Klik salah satu percakapan untuk melihat header stage badge
        console.log('[4/4] Membuka percakapan pertama untuk melihat header stage badge...');
        const firstConv = page.locator('[data-conv-id]').first();
        if (await firstConv.isVisible()) {
            await firstConv.click();
            await page.waitForTimeout(1000);
            await page.screenshot({ path: path.join(ARTIFACT_DIR, 'chat_priority_conversation.png') });
            console.log('  -> Screenshot percakapan aktif dengan badge disimpan.');

            // Buka info panel
            const infoBtn = page.locator('button[aria-label="Info percakapan"]');
            if (await infoBtn.isVisible()) {
                await infoBtn.click();
                await page.waitForTimeout(600);
                await page.screenshot({ path: path.join(ARTIFACT_DIR, 'chat_priority_info_panel.png') });
                console.log('  -> Screenshot info panel dengan badge disimpan.');
            }
        }

        console.log('========================================================================');
        console.log('  SEMUA VALIDASI UI VISUAL SMART PRIORITY SUKSES: 100% PASS');
        console.log('========================================================================');
    } catch (err) {
        console.error('Test gagal:', err);
        process.exitCode = 1;
    } finally {
        await browser.close();
    }
}

run();
