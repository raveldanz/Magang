import { chromium } from 'playwright';
import path from 'node:path';

async function testMobileChat() {
    console.log('--- TESTING CHAT ON MOBILE VIEWPORT (375x812) ---');
    const browser = await chromium.launch({ headless: true, channel: 'chrome' });
    const context = await browser.newContext({
        viewport: { width: 375, height: 812 },
        isMobile: true,
        hasTouch: true
    });
    const page = await context.newPage();

    // 1. Login
    await page.goto('http://127.0.0.1:8000/login');
    await page.fill('input[name="email"]', 'mahasiswa.aktif@unesa.ac.id');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');

    // 2. Open /chat
    await page.goto('http://127.0.0.1:8000/chat', { waitUntil: 'networkidle' });
    await page.waitForTimeout(1000);
    await page.screenshot({ path: 'public/test-artifacts/chat_mobile_01_inbox.png' });
    console.log('[Mobile] Inbox captured.');

    // 3. Open conversation
    const convBtn = page.locator('button[data-conv-id]').first();
    await convBtn.click();
    await page.waitForTimeout(1000);
    await page.screenshot({ path: 'public/test-artifacts/chat_mobile_02_conversation.png' });
    console.log('[Mobile] Conversation opened.');

    // Verify back button is visible
    const backBtn = page.locator('button[aria-label="Kembali ke daftar percakapan"]');
    const backVisible = await backBtn.isVisible();
    console.log('[Mobile] Back button visible:', backVisible);

    // 4. Click back button
    await backBtn.click();
    await page.waitForTimeout(1000);
    await page.screenshot({ path: 'public/test-artifacts/chat_mobile_03_back_to_inbox.png' });
    console.log('[Mobile] Back to inbox captured.');

    await browser.close();
    console.log('--- MOBILE CHAT TEST FINISHED SUCCESSFULLY ---');
}

testMobileChat().catch(err => {
    console.error('Mobile test failed:', err);
    process.exit(1);
});
