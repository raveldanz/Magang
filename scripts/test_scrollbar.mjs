import { chromium } from 'playwright';

async function testScrollbar() {
    console.log('--- STARTING PLAYWRIGHT SCROLLBAR TEST ---');
    const browser = await chromium.launch({ headless: true, channel: 'chrome' });

    const context = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await context.newPage();

    console.log('[1] Logging in as Mahasiswa...');
    await page.goto('http://127.0.0.1:8000/login');
    await page.fill('input[name="email"]', 'mahasiswa.aktif@unesa.ac.id');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 10000 });

    console.log('[2] Navigating to /chat...');
    await page.goto('http://127.0.0.1:8000/chat', { waitUntil: 'networkidle' });

    console.log('[3] Waiting for chat sidebar...');
    await page.waitForSelector('[data-conv-id]');
    
    // Open info panel to see all scrollbars
    await page.locator('[data-conv-id]').first().click();
    await page.waitForTimeout(1000);
    
    const infoBtn = page.locator('button[aria-label="Info percakapan"]');
    if (await infoBtn.count() > 0) {
        await infoBtn.click();
        await page.waitForTimeout(500);
    }

    console.log('[4] Taking screenshot...');
    await page.screenshot({ path: 'public/test-artifacts/chat_scrollbar_fixed.png' });

    await browser.close();
    console.log('--- TEST FINISHED ---');
}

testScrollbar().catch(err => {
    console.error('Test failed:', err);
    process.exit(1);
});
