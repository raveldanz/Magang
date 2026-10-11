import { chromium } from 'playwright';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');
const publicDir = path.resolve(projectRoot, 'public/test-artifacts');

async function main() {
    const browser = await chromium.launch({
        headless: true,
        channel: 'chrome'
    });
    const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const page = await context.newPage();

    // 1. Direct login as Ahmad Pending QA (email: mhs.qa.pending@test.local, password: password)
    console.log('[+] Logging in as Ahmad Pending QA...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await page.fill('input[name="email"]', 'mhs.qa.pending@test.local');
    await page.fill('input[name="password"]', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle' }),
        page.click('button[type="submit"]')
    ]);

    // 2. Go to /student/application to verify rejection alert & clean form
    console.log('[+] Navigating to /student/application...');
    await page.goto('http://127.0.0.1:8000/student/application', { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(publicDir, 'ahmad_application_form_clean.png'), fullPage: false });

    // 3. Go to /dashboard to verify non-redundant placement card
    console.log('[+] Navigating to /dashboard...');
    await page.goto('http://127.0.0.1:8000/dashboard', { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(publicDir, 'ahmad_dashboard_clean.png'), fullPage: false });

    console.log('[+] Screenshots captured successfully!');
    await browser.close();
}

main().catch(err => {
    console.error('[-] Error:', err);
    process.exit(1);
});
