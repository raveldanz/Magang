import { chromium } from 'playwright';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');
const publicDir = path.resolve(projectRoot, 'public/test-artifacts');

async function run() {
    const browser = await chromium.launch({
        headless: true,
        channel: 'chrome'
    });

    console.log('=== TEST 1: Citra Rejected QA (Status REJECTED) ===');
    const contextCitra = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const pageCitra = await contextCitra.newPage();

    await pageCitra.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await pageCitra.fill('input[name="email"]', 'mhs.qa.rejected@test.local');
    await pageCitra.fill('input[name="password"]', 'password');
    await Promise.all([
        pageCitra.waitForNavigation({ waitUntil: 'networkidle' }),
        pageCitra.click('button[type="submit"]')
    ]);

    await pageCitra.goto('http://127.0.0.1:8000/dashboard', { waitUntil: 'networkidle' });
    await pageCitra.screenshot({ path: path.join(publicDir, 'citra_dashboard_clean_desktop.png'), fullPage: false });

    // Mobile view for Citra
    await pageCitra.setViewportSize({ width: 375, height: 812 });
    await pageCitra.waitForTimeout(500);
    await pageCitra.screenshot({ path: path.join(publicDir, 'citra_dashboard_clean_mobile.png'), fullPage: false });
    await contextCitra.close();

    console.log('=== TEST 2: Accepted / Active Student (Advisor Form Toggle) ===');
    const contextActive = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const pageActive = await contextActive.newPage();

    await pageActive.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await pageActive.fill('input[name="email"]', 'mhs.accepted2@its.ac.id');
    await pageActive.fill('input[name="password"]', 'password');
    await Promise.all([
        pageActive.waitForNavigation({ waitUntil: 'networkidle' }),
        pageActive.click('button[type="submit"]')
    ]);

    await pageActive.goto('http://127.0.0.1:8000/dashboard', { waitUntil: 'networkidle' });
    await pageActive.screenshot({ path: path.join(publicDir, 'accepted_student_cards_desktop.png'), fullPage: false });

    // Click toggle advisor
    const toggleBtn = await pageActive.locator('button:has-text("Dosen Pembimbing")').first();
    if (await toggleBtn.isVisible()) {
        console.log('[+] Clicking toggle advisor button...');
        await toggleBtn.click();
        await pageActive.waitForTimeout(600);
        await pageActive.screenshot({ path: path.join(publicDir, 'accepted_student_advisor_box_desktop.png'), fullPage: false });
    }

    await contextActive.close();
    await browser.close();
    console.log('[+] All tests completed! Artifacts saved in public/test-artifacts/');
}

run().catch(err => {
    console.error('[-] Error:', err);
    process.exit(1);
});
