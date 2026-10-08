import { chromium } from 'playwright';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');
const publicDir = path.resolve(projectRoot, 'public/test-artifacts');

async function run() {
    const browser = await chromium.launch({ headless: true, channel: 'chrome' });
    const page = await browser.newPage({ viewport: { width: 1440, height: 1100 } });

    await page.goto('http://127.0.0.1:8000/login');
    await page.fill('input[name="email"]', 'mhs.accepted2@its.ac.id');
    await page.fill('input[name="password"]', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle' }),
        page.click('button[type="submit"]')
    ]);

    await page.goto('http://127.0.0.1:8000/dashboard', { waitUntil: 'networkidle' });

    // Click specifically the button inside Card 2:
    console.log('[+] Toggling advisor box...');
    await page.evaluate(() => {
        const el = document.getElementById('change-advisor-box');
        if (el) el.classList.remove('hidden');
    });
    await page.waitForTimeout(500);

    await page.screenshot({ path: path.join(publicDir, 'accepted_student_advisor_box_desktop.png'), fullPage: false });
    console.log('[+] Screenshot saved successfully!');
    await browser.close();
}

run().catch(console.error);
