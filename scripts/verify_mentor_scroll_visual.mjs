import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';

async function main() {
    console.log('Launching Playwright Chrome...');
    const browser = await chromium.launch({
        channel: 'chrome',
        headless: true
    });

    const context = await browser.newContext({
        viewport: { width: 1366, height: 768 },
        extraHTTPHeaders: {
            'Connection': 'close'
        }
    });

    const page = await context.newPage();

    console.log('Navigating to login page...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'domcontentloaded' });

    console.log('Logging in as admin.perpustakaan@surabaya.go.id...');
    await page.fill('input[name="email"], input[type="email"]', 'admin.perpustakaan@surabaya.go.id');
    await page.fill('input[name="password"], input[type="password"]', 'password');
    await page.click('button[type="submit"]');

    await page.waitForURL('**/admin/dashboard', { timeout: 15000 });
    console.log('Arrived at dashboard:', page.url());

    await page.waitForSelector('text=PANEL PROFIL BIDANG', { timeout: 10000 });
    await page.waitForTimeout(1000);

    const outputDir = path.resolve('public/test-artifacts');
    if (!fs.existsSync(outputDir)) {
        fs.mkdirSync(outputDir, { recursive: true });
    }

    const panelCard = page.locator('div.bg-white.rounded-3xl:has-text("PANEL PROFIL BIDANG")').first();
    await panelCard.scrollIntoViewIfNeeded();
    await page.waitForTimeout(500);

    const cardScreenshotPath = path.join(outputDir, 'admin_dashboard_mentor_panel_card.png');
    await panelCard.screenshot({ path: cardScreenshotPath });
    console.log('Saved card screenshot to:', cardScreenshotPath);

    const fullViewPath = path.join(outputDir, 'admin_dashboard_mentor_scroll.png');
    await page.screenshot({ path: fullViewPath, fullPage: false });

    // Test scrolling inside mentor container
    console.log('Testing scroll within mentor container...');
    const mentorScrollContainer = page.locator('.max-h-\\[220px\\]').first();
    if (await mentorScrollContainer.count() > 0) {
        await mentorScrollContainer.evaluate((el) => {
            el.scrollTop = el.scrollHeight;
        });
        await page.waitForTimeout(500);
        const scrolledScreenshotPath = path.join(outputDir, 'admin_dashboard_mentor_scrolled.png');
        await panelCard.screenshot({ path: scrolledScreenshotPath });
        console.log('Saved scrolled view screenshot to:', scrolledScreenshotPath);
    }

    await browser.close();
    console.log('Visual verification completed successfully!');
}

main().catch(err => {
    console.error('Error running script:', err);
    process.exit(1);
});
