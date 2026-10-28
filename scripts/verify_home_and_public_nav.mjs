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
    console.log('--- Starting Visual Verification for Home Menu & Public/Auth Navigation ---');
    const browser = await chromium.launch({
        headless: true,
        channel: 'chrome',
    });

    try {
        // SCENARIO 1: Guest visits Welcome page ('/')
        console.log('1. Testing Welcome page as Guest...');
        const guestContext = await browser.newContext({ viewport: { width: 1440, height: 900 } });
        const guestPage = await guestContext.newPage();
        await guestPage.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle' });
        await guestPage.screenshot({ path: path.join(artifactDir, 'welcome_guest_desktop.png'), fullPage: false });
        console.log('Saved: welcome_guest_desktop.png');

        // SCENARIO 2: Guest visits Public Project Detail ('/projects/16')
        console.log('2. Testing Project Detail as Guest...');
        await guestPage.goto('http://127.0.0.1:8000/projects/16', { waitUntil: 'networkidle' });
        await guestPage.screenshot({ path: path.join(artifactDir, 'public_project_guest_view.png'), fullPage: false });
        console.log('Saved: public_project_guest_view.png');

        // SCENARIO 3: Authenticated user (Super Admin) visits Dashboard and opens dropdown
        console.log('3. Testing Authenticated User and Profile Dropdown with Home...');
        const authContext = await browser.newContext({ viewport: { width: 1440, height: 900 } });
        const authPage = await authContext.newPage();
        await authPage.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
        await authPage.fill('input[name="email"]', 'admin@surabaya.go.id');
        await authPage.fill('input[name="password"]', 'password');
        await authPage.click('button[type="submit"]');
        await authPage.waitForURL(url => !url.pathname.includes('/login'), { timeout: 15000 });
        
        // Click profile dropdown on top-right
        const profileTrigger = await authPage.locator('button:has-text("Super Admin"), button:has-text("Admin")').first();
        if (await profileTrigger.isVisible()) {
            await profileTrigger.click();
            await authPage.waitForTimeout(500);
            await authPage.screenshot({ path: path.join(artifactDir, 'nav_dropdown_home_item.png'), fullPage: false });
            console.log('Saved: nav_dropdown_home_item.png');
        }

        // SCENARIO 4: Authenticated user visits Welcome page ('/')
        console.log('4. Testing Welcome page as Authenticated User...');
        await authPage.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle' });
        await authPage.screenshot({ path: path.join(artifactDir, 'welcome_auth_desktop.png'), fullPage: false });
        console.log('Saved: welcome_auth_desktop.png');

        console.log('--- All Visual Verifications Completed Successfully ---');
    } finally {
        await browser.close();
    }
}

run().catch(err => {
    console.error('Error during visual verification:', err);
    process.exit(1);
});
