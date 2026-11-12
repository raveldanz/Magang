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
    console.log('--- Starting Visual Verification for Home Page Keluar Akun Menu ---');
    const browser = await chromium.launch({
        headless: true,
        channel: 'chrome',
    });

    try {
        // 1. Authenticate user
        console.log('1. Logging in as Admin/User...');
        const desktopContext = await browser.newContext({ viewport: { width: 1440, height: 900 } });
        const desktopPage = await desktopContext.newPage();
        
        await desktopPage.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
        await desktopPage.fill('input[name="email"]', 'admin@surabaya.go.id');
        await desktopPage.fill('input[name="password"]', 'password');
        await desktopPage.click('button[type="submit"]');
        await desktopPage.waitForURL(url => !url.pathname.includes('/login'), { timeout: 15000 });

        // 2. Visit Home Page on Desktop
        console.log('2. Navigating to Home Page on Desktop...');
        await desktopPage.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle' });
        await desktopPage.screenshot({ path: path.join(artifactDir, 'home_auth_desktop_logout.png'), fullPage: false });
        console.log('Saved: home_auth_desktop_logout.png');

        // 3. Mobile View with open drawer
        console.log('3. Testing Mobile View with Drawer Open...');
        const mobileContext = await browser.newContext({
            viewport: { width: 375, height: 812 },
            userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1'
        });
        const mobilePage = await mobileContext.newPage();
        
        // Copy cookies from desktop to mobile context for authentication
        const cookies = await desktopContext.cookies();
        await mobileContext.addCookies(cookies);

        await mobilePage.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle' });
        
        // Click hamburger button to open mobile menu drawer
        const hamburgerBtn = mobilePage.locator('button[aria-label="Toggle navigasi menu"]');
        await hamburgerBtn.click();
        await mobilePage.waitForTimeout(500);

        await mobilePage.screenshot({ path: path.join(artifactDir, 'home_auth_mobile_drawer_logout.png'), fullPage: false });
        console.log('Saved: home_auth_mobile_drawer_logout.png');

        // 4. Test clicking 'Keluar Akun' from Mobile Drawer
        console.log('4. Clicking Keluar Akun button from Mobile Drawer...');
        const logoutBtn = mobilePage.locator('div[x-show="mobileMenuOpen"] button:has-text("Keluar Akun")');
        await Promise.all([
            mobilePage.waitForNavigation({ waitUntil: 'networkidle' }),
            logoutBtn.click(),
        ]);
        await mobilePage.waitForTimeout(500);

        await mobilePage.screenshot({ path: path.join(artifactDir, 'home_post_logout_guest.png'), fullPage: false });
        console.log('Saved: home_post_logout_guest.png');

        console.log('--- All Visual Verifications Completed Successfully (Exit Code 0) ---');
    } finally {
        await browser.close();
    }
}

run().catch(err => {
    console.error('Error during visual verification:', err);
    process.exit(1);
});
