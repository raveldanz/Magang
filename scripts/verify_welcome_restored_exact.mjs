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
    console.log('--- Starting Exact Visual Verification for Restored Welcome Page ---');
    const browser = await chromium.launch({
        headless: true,
        channel: 'chrome',
    });

    try {
        // 1. Guest View Desktop (1366x768)
        console.log('1. Capturing Guest Desktop View...');
        const guestContext = await browser.newContext({ viewport: { width: 1366, height: 768 } });
        const guestPage = await guestContext.newPage();
        await guestPage.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle' });
        
        // Assertions for Guest
        const rungkutText = await guestPage.locator('text=Jl. Rungkut Asri Tengah No. 5-7, Surabaya').first();
        if (await rungkutText.isVisible()) {
            console.log('PASS: Rungkut Asri address is visible on Welcome page.');
        } else {
            console.error('FAIL: Rungkut Asri address not found!');
        }

        const proyekDinasNav = await guestPage.locator('header a:has-text("Proyek Dinas")').first();
        if (await proyekDinasNav.isVisible()) {
            console.log('PASS: Proyek Dinas menu link is visible in header.');
        } else {
            console.error('FAIL: Proyek Dinas menu link not found in header!');
        }

        const daftarHeader = await guestPage.locator('header a:has-text("Daftar")').first();
        const masukHeader = await guestPage.locator('header a:has-text("Masuk")').first();
        console.log('Guest Header Buttons:', {
            daftarVisible: await daftarHeader.isVisible(),
            masukVisible: await masukHeader.isVisible()
        });

        await guestPage.screenshot({ path: path.join(artifactDir, 'welcome_exact_guest_desktop.png'), fullPage: false });
        console.log('Saved: welcome_exact_guest_desktop.png');

        // 2. Auth View Desktop (1366x768)
        console.log('2. Authenticating as Student / Admin...');
        const authContext = await browser.newContext({ viewport: { width: 1366, height: 768 } });
        const authPage = await authContext.newPage();
        await authPage.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
        await authPage.fill('input[name="email"]', 'admin@surabaya.go.id');
        await authPage.fill('input[name="password"]', 'password');
        await authPage.click('button[type="submit"]');
        await authPage.waitForURL(url => !url.pathname.includes('/login'), { timeout: 15000 });

        console.log('3. Capturing Authenticated View of Welcome Page...');
        await authPage.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle' });

        const dashboardHeader = await authPage.locator('header a:has-text("Dashboard")').first();
        const proyekDinasAuth = await authPage.locator('header a:has-text("Proyek Dinas")').first();
        console.log('Auth Header Elements:', {
            dashboardVisible: await dashboardHeader.isVisible(),
            proyekDinasVisible: await proyekDinasAuth.isVisible()
        });

        await authPage.screenshot({ path: path.join(artifactDir, 'welcome_exact_auth_desktop.png'), fullPage: false });
        console.log('Saved: welcome_exact_auth_desktop.png');

        console.log('--- Visual Verification Completed Successfully! ---');
    } finally {
        await browser.close();
    }
}

run().catch(err => {
    console.error('Visual verification failed:', err);
    process.exit(1);
});
