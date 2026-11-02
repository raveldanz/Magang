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
    console.log('[E2E Test] Launching Chrome channel for supervisor verification...');
    const browser = await chromium.launch({
        headless: true,
        channel: 'chrome'
    });

    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 }
    });
    const page = await context.newPage();

    // 1. LOGIN AS SUPERVISOR
    console.log('[E2E Test] 1. Logging in as Supervisor (supervisor@dispusip.surabaya.go.id)...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await page.fill('input[name="email"]', 'supervisor@dispusip.surabaya.go.id');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL(url => !url.pathname.includes('/login'), { timeout: 15000 });

    // 2. CHECK SUPERVISOR DASHBOARD
    console.log('[E2E Test] 2. Checking Supervisor Dashboard and Menu Navbar...');
    await page.goto('http://127.0.0.1:8000/supervisor/dashboard', { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(artifactDir, 'supervisor_dashboard_menu.png'), fullPage: true });

    // 3. CHECK SUPERVISOR APPLICATIONS SHOW PAGE FOR APP 23
    console.log('[E2E Test] 3. Checking Supervisor Application Detail (#23 Intan Permatasari - Kelompok)...');
    await page.goto('http://127.0.0.1:8000/supervisor/applications/23', { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(artifactDir, 'supervisor_application_show_direct_button.png'), fullPage: true });

    // 4. CLICK APPROVE BUTTON DIRECTLY
    console.log('[E2E Test] 4. Clicking Setujui & Terbitkan Surat directly...');
    await page.click('#approve-form button[type="submit"]');
    await page.waitForURL(url => url.pathname.includes('/supervisor/applications'), { timeout: 15000 });

    // 5. CHECK FLASH SUCCESS MESSAGE & INDEX PAGE
    console.log('[E2E Test] 5. Capturing Supervisor Applications Index after group approval...');
    await page.screenshot({ path: path.join(artifactDir, 'supervisor_applications_approved_success.png'), fullPage: true });

    // 6. RE-VISIT APP 23 DETAIL TO VERIFY APPROVED STATUS CARD
    console.log('[E2E Test] 6. Verifying approved card on Application #23...');
    await page.goto('http://127.0.0.1:8000/supervisor/applications/23', { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(artifactDir, 'supervisor_application_show_after_approved.png'), fullPage: true });

    console.log('[E2E Test] All screenshots captured successfully in public/test-artifacts/');
    await browser.close();
}

run().catch(err => {
    console.error('[E2E Test] Error during execution:', err);
    process.exit(1);
});
