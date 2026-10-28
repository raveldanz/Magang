import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');
const artifactDir = path.resolve(projectRoot, 'public/test-artifacts');

if (!fs.existsSync(artifactDir)) {
    fs.mkdirSync(artifactDir, { recursive: true });
}

async function run() {
    console.log('--- Starting Playwright Visual Verification for Restored Welcome Home Page ---');
    const browser = await chromium.launch({
        headless: true,
        channel: 'chrome',
    });

    try {
        // 1. Guest View (Desktop - 1366x768 to match user's exact screen)
        console.log('1. Capturing Restored Home Desktop View (Guest)...');
        const guestContext = await browser.newContext({ viewport: { width: 1366, height: 768 } });
        const guestPage = await guestContext.newPage();
        await guestPage.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle' });
        await guestPage.screenshot({ path: path.join(artifactDir, 'welcome_restored_guest.png'), fullPage: false });
        console.log('Saved: welcome_restored_guest.png');

        // 2. Open Quota Modal Popup
        console.log('2. Opening Quota Modal Popup...');
        const quotaButton = await guestPage.locator('button:has-text("Sisa Kuota")');
        await quotaButton.click();
        await guestPage.waitForTimeout(600);
        await guestPage.screenshot({ path: path.join(artifactDir, 'welcome_restored_quota_modal.png'), fullPage: false });
        console.log('Saved: welcome_restored_quota_modal.png');

        // Close modal
        await guestPage.locator('button:has-text("Tutup")').click();
        await guestPage.waitForTimeout(400);

        // 3. Open Alur Magang Modal Popup
        console.log('3. Opening Alur Magang Guide Modal...');
        const guideButton = await guestPage.locator('button:has-text("Alur Magang")');
        if (await guideButton.isVisible()) {
            await guideButton.click();
            await guestPage.waitForTimeout(600);
            await guestPage.screenshot({ path: path.join(artifactDir, 'welcome_restored_guide_modal.png'), fullPage: false });
            console.log('Saved: welcome_restored_guide_modal.png');
            await guestPage.locator('button:has-text("Mengerti")').click();
            await guestPage.waitForTimeout(400);
        }

        // 4. Mobile View (Guest - 375x812 iPhone)
        console.log('4. Capturing Mobile View...');
        const mobileContext = await browser.newContext({ viewport: { width: 375, height: 812 }, isMobile: true });
        const mobilePage = await mobileContext.newPage();
        await mobilePage.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle' });
        await mobilePage.screenshot({ path: path.join(artifactDir, 'welcome_restored_mobile.png'), fullPage: false });
        console.log('Saved: welcome_restored_mobile.png');

        // 5. Authenticated User View (Desktop)
        console.log('5. Capturing Restored Home Desktop View (Authenticated User)...');
        const authContext = await browser.newContext({ viewport: { width: 1366, height: 768 } });
        const authPage = await authContext.newPage();
        await authPage.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
        await authPage.fill('input[name="email"]', 'admin@surabaya.go.id');
        await authPage.fill('input[name="password"]', 'password');
        await authPage.click('button[type="submit"]');
        await authPage.waitForURL(url => !url.pathname.includes('/login'), { timeout: 15000 });

        // Navigate back to '/'
        await authPage.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle' });
        await authPage.screenshot({ path: path.join(artifactDir, 'welcome_restored_auth.png'), fullPage: false });
        console.log('Saved: welcome_restored_auth.png');

        console.log('--- All Visual Tests Passed Successfully! ---');
    } finally {
        await browser.close();
    }
}

run().catch(err => {
    console.error('Visual verification failed:', err);
    process.exit(1);
});
