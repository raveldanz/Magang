import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';

async function main() {
    console.log('Launching Playwright Chrome...');
    const browser = await chromium.launch({
        channel: 'chrome',
        headless: true
    });

    const outputDir = path.resolve('public/test-artifacts');
    if (!fs.existsSync(outputDir)) {
        fs.mkdirSync(outputDir, { recursive: true });
    }

    const context = await browser.newContext({
        viewport: { width: 1366, height: 768 },
        extraHTTPHeaders: { 'Connection': 'close' }
    });
    const page = await context.newPage();

    console.log('Logging in as super admin...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="email"], input[type="email"]', 'admin@gmail.com');
    await page.fill('input[name="password"], input[type="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/admin/dashboard', { timeout: 15000 });

    console.log('Navigating to /admin/agencies/1...');
    await page.goto('http://127.0.0.1:8000/admin/agencies/1', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);

    const screenPath = path.join(outputDir, 'agency_show_no_akun_terdaftar.png');
    await page.screenshot({ path: screenPath, fullPage: false });
    console.log('Saved screenshot to:', screenPath);

    // Verify text "Akun Terdaftar" is NOT present on page
    const content = await page.content();
    if (content.includes('Akun Terdaftar')) {
        console.error('FAIL: "Akun Terdaftar" still found in page content!');
        process.exit(1);
    } else {
        console.log('SUCCESS: "Akun Terdaftar" is completely absent!');
    }

    await browser.close();
}

main().catch(err => {
    console.error(err);
    process.exit(1);
});

