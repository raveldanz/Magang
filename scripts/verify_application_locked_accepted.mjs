import { chromium } from 'playwright';

async function run() {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const context = await browser.newContext({ 
        viewport: { width: 1440, height: 950 },
        extraHTTPHeaders: { 'Connection': 'close' }
    });
    const page = await context.newPage();

    console.log('Logging in as admin...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="email"]', 'admin.dispusip@surabaya.go.id');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForNavigation({ waitUntil: 'domcontentloaded' }).catch(() => {});

    console.log('Navigating to pending application #4...');
    await page.goto('http://127.0.0.1:8000/admin/applications/4', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);

    // Element screenshot of verification box
    const cardContainer = await page.$('div:has(h3:has-text("Aksi Verifikasi & Seleksi"))');
    if (cardContainer) {
        await cardContainer.scrollIntoViewIfNeeded();
        await page.waitForTimeout(500);

        await cardContainer.screenshot({ 
            path: 'public/test-artifacts/admin_application_pending_locked_accepted_focused.png'
        });
        console.log('Saved close-up screenshot to public/test-artifacts/admin_application_pending_locked_accepted_focused.png');
    }

    await browser.close();
    console.log('Visual verification complete.');
}

run().catch(err => {
    console.error(err);
    process.exit(1);
});
