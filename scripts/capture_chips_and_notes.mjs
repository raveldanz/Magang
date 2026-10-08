import { chromium } from 'playwright';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');
const publicDir = path.resolve(projectRoot, 'public/test-artifacts');

async function run() {
    const browser = await chromium.launch({ headless: true, channel: 'chrome' });

    try {
        const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
        const page = await ctx.newPage();

        await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'load' });
        await page.fill('input[name="email"]', 'dosen.unesa@unesa.ac.id');
        await page.fill('input[name="password"]', 'password');
        await page.click('button[type="submit"]');
        await page.waitForURL(url => !url.pathname.includes('/login'), { timeout: 15000 });

        await page.goto('http://127.0.0.1:8000/lecturer/students/3', { waitUntil: 'networkidle' });

        // Tab Laporan Akhir
        await page.click('button:has-text("Laporan Akhir")');
        await page.waitForTimeout(300);
        await page.screenshot({ path: path.join(publicDir, 'report_review_chips.png'), fullPage: true });

        // Tab Logbook
        await page.click('button:has-text("Logbook")');
        await page.waitForTimeout(300);
        // Expand the first weekly bundle accordion
        const toggleBtn = await page.$('button:has-text("Minggu")');
        if (toggleBtn) {
            await toggleBtn.click();
            await page.waitForTimeout(300);
        }
        await page.screenshot({ path: path.join(publicDir, 'logbook_review_chips.png'), fullPage: true });

        await ctx.close();
        console.log('[+] Chips and accordion captures completed!');
    } catch (e) {
        console.error('Error during chips capture:', e);
    } finally {
        await browser.close();
    }
}

run();
