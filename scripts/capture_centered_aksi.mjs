import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');
const artifactDir = 'C:\\Users\\TK ABA SBY 69 (3)\\.gemini\\antigravity-ide\\brain\\d5a3ea54-dbda-4a67-bb38-f0650133f591';
const publicArtifactsDir = path.join(projectRoot, 'public/test-artifacts');

if (!fs.existsSync(publicArtifactsDir)) {
    fs.mkdirSync(publicArtifactsDir, { recursive: true });
}

async function run() {
    const browser = await chromium.launch({
        headless: true,
        channel: 'chrome'
    });

    const context = await browser.newContext({
        viewport: { width: 1366, height: 768 },
        deviceScaleFactor: 1
    });

    const page = await context.newPage();

    console.log('Logging in as admin...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await page.fill('input[name="email"]', 'admin@surabaya.go.id');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL(url => !url.pathname.includes('/login'), { timeout: 15000 });

    console.log('Capturing /admin/applications at 1366x768...');
    await page.goto('http://127.0.0.1:8000/admin/applications', { waitUntil: 'networkidle' });
    await page.waitForTimeout(1000);
    const appShot = path.join(artifactDir, 'applications_table_centered_aksi.png');
    await page.screenshot({ path: appShot, fullPage: false });
    fs.copyFileSync(appShot, path.join(publicArtifactsDir, 'applications_table_centered_aksi.png'));
    console.log(`Saved: ${appShot}`);

    console.log('Capturing /admin/users at 1366x768...');
    await page.goto('http://127.0.0.1:8000/admin/users', { waitUntil: 'networkidle' });
    await page.waitForTimeout(1000);
    const userShot = path.join(artifactDir, 'users_table_centered_aksi.png');
    await page.screenshot({ path: userShot, fullPage: false });
    fs.copyFileSync(userShot, path.join(publicArtifactsDir, 'users_table_centered_aksi.png'));
    console.log(`Saved: ${userShot}`);

    await browser.close();
    console.log('Done!');
}

run().catch(err => {
    console.error(err);
    process.exit(1);
});
