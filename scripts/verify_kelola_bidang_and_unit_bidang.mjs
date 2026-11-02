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

    // 1. Verify Admin Bidang (/admin/units/2)
    {
        console.log('Testing Admin Bidang view...');
        const context = await browser.newContext({
            viewport: { width: 1366, height: 768 },
            extraHTTPHeaders: { 'Connection': 'close' }
        });
        const page = await context.newPage();

        await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'domcontentloaded' });
        await page.fill('input[name="email"], input[type="email"]', 'admin.perpustakaan@surabaya.go.id');
        await page.fill('input[name="password"], input[type="password"]', 'password');
        await page.click('button[type="submit"]');
        await page.waitForURL('**/admin/dashboard', { timeout: 15000 });

        // Navigate to /admin/units/2
        await page.goto('http://127.0.0.1:8000/admin/units/2', { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(1000);

        const screenPath1 = path.join(outputDir, 'admin_bidang_kelola_bidang.png');
        await page.screenshot({ path: screenPath1, fullPage: false });
        console.log('Saved Admin Bidang screenshot to:', screenPath1);
        await context.close();
    }

    // 2. Verify Super Admin / Admin Dispusip (/admin/units)
    {
        console.log('Testing Admin Dispusip view...');
        const context = await browser.newContext({
            viewport: { width: 1366, height: 768 },
            extraHTTPHeaders: { 'Connection': 'close' }
        });
        const page = await context.newPage();

        await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'domcontentloaded' });
        await page.fill('input[name="email"], input[type="email"]', 'admin.dispusip@surabaya.go.id');
        await page.fill('input[name="password"], input[type="password"]', 'password');
        await page.click('button[type="submit"]');
        await page.waitForURL('**/admin/dashboard', { timeout: 15000 });

        // Navigate to /admin/units
        await page.goto('http://127.0.0.1:8000/admin/units', { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(1000);

        const screenPath2 = path.join(outputDir, 'admin_dispusip_unit_bidang.png');
        await page.screenshot({ path: screenPath2, fullPage: false });
        console.log('Saved Admin Dispusip screenshot to:', screenPath2);
        await context.close();
    }

    await browser.close();
    console.log('Verification completed successfully!');
}

main().catch(err => {
    console.error('Error running script:', err);
    process.exit(1);
});
