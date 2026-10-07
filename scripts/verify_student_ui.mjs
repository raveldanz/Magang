import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');
const publicDir = path.resolve(projectRoot, 'public/test-artifacts');

if (!fs.existsSync(publicDir)) {
    fs.mkdirSync(publicDir, { recursive: true });
}

async function run() {
    console.log('[+] Launching local Chrome browser...');
    const browser = await chromium.launch({
        headless: true,
        channel: 'chrome'
    });

    const context = await browser.newContext();
    const page = await context.newPage();

    // Login as student
    console.log('[+] Logging in as student (mhs.aktif3@upnjatim.ac.id)...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await page.fill('input[name="email"]', 'mhs.aktif3@upnjatim.ac.id');
    await page.fill('input[name="password"]', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle' }),
        page.click('button[type="submit"]')
    ]);

    // 1. Desktop Dashboard (1440x900)
    console.log('[+] Capturing Desktop Dashboard (1440x900)...');
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.goto('http://127.0.0.1:8000/dashboard', { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(publicDir, 'student_dashboard_desktop.png'), fullPage: true });

    // Scroll slightly to capture cards + detail area identical to user's screenshot
    await page.evaluate(() => {
        window.scrollTo(0, 320);
    });
    await new Promise(r => setTimeout(r, 300));
    await page.screenshot({ path: path.join(publicDir, 'student_dashboard_desktop_cards.png'), fullPage: false });

    // 2. Mobile Dashboard (400x611)
    console.log('[+] Capturing Mobile Dashboard (400x611)...');
    await page.setViewportSize({ width: 400, height: 611 });
    await page.goto('http://127.0.0.1:8000/dashboard', { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(publicDir, 'student_dashboard_mobile_full.png'), fullPage: true });
    
    await page.evaluate(() => {
        window.scrollTo(0, 680);
    });
    await new Promise(r => setTimeout(r, 300));
    await page.screenshot({ path: path.join(publicDir, 'student_dashboard_mobile_cards.png'), fullPage: false });

    console.log('[+] All targeted visual screenshots saved successfully!');

    // 3. Login as Anisa Rahmawati (COMPLETED status, as shown in user screenshot)
    console.log('[+] Logging in as Anisa Rahmawati (anisa.rahma@mhs.unesa.ac.id)...');
    const anisaContext = await browser.newContext();
    const anisaPage = await anisaContext.newPage();
    await anisaPage.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await anisaPage.fill('input[name="email"]', 'anisa.rahma@mhs.unesa.ac.id');
    await anisaPage.fill('input[name="password"]', 'password');
    await Promise.all([
        anisaPage.waitForNavigation({ waitUntil: 'networkidle' }),
        anisaPage.click('button[type="submit"]')
    ]);

    // Set mobile viewport 400x611 (matching user screenshot)
    await anisaPage.setViewportSize({ width: 400, height: 611 });
    await anisaPage.goto('http://127.0.0.1:8000/dashboard', { waitUntil: 'networkidle' });
    await anisaPage.screenshot({ path: path.join(publicDir, 'anisa_mobile_hero_card.png'), fullPage: false });

    await anisaPage.evaluate(() => {
        window.scrollTo(0, 920);
    });
    await new Promise(r => setTimeout(r, 300));
    await anisaPage.screenshot({ path: path.join(publicDir, 'anisa_mobile_scrolled.png'), fullPage: false });

    // Click "Lihat Detail" to verify modal on mobile
    await anisaPage.click('button:has-text("Lihat Detail")');
    await new Promise(r => setTimeout(r, 400));
    await anisaPage.screenshot({ path: path.join(publicDir, 'anisa_mobile_modal.png'), fullPage: false });

    // Also desktop view for Anisa
    await anisaPage.setViewportSize({ width: 1440, height: 900 });
    await anisaPage.goto('http://127.0.0.1:8000/dashboard', { waitUntil: 'networkidle' });
    await anisaPage.screenshot({ path: path.join(publicDir, 'anisa_desktop_hero_card.png'), fullPage: false });

    // Scroll down to capture Anisa's 2 cards + Detail Penempatan Magang
    await anisaPage.evaluate(() => {
        window.scrollTo(0, 480);
    });
    await new Promise(r => setTimeout(r, 300));
    await anisaPage.screenshot({ path: path.join(publicDir, 'anisa_desktop_cards.png'), fullPage: false });

    // Click "Lihat Detail" to verify modal on desktop
    await anisaPage.click('button:has-text("Lihat Detail")');
    await new Promise(r => setTimeout(r, 400));
    await anisaPage.screenshot({ path: path.join(publicDir, 'anisa_desktop_modal.png'), fullPage: false });

    console.log('[+] Anisa screenshots captured successfully!');
    await browser.close();
}

run().catch(err => {
    console.error('[-] Error:', err);
    process.exit(1);
});
