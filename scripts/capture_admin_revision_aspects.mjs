import { chromium } from 'playwright';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');
const artifactDir = path.resolve(projectRoot, 'public/test-artifacts');

async function run() {
    const browser = await chromium.launch({ headless: true, channel: 'chrome' });
    const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });

    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await page.fill('input[name="email"]', 'admin.dispusip@surabaya.go.id');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL(url => !url.pathname.includes('/login'), { timeout: 15000 });

    await page.goto('http://127.0.0.1:8000/admin/certificates/12/revision', { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(artifactDir, '06_admin_viewing_supervisor_aspects.png'), fullPage: false });
    await browser.close();
    console.log('06_admin_viewing_supervisor_aspects.png successfully captured!');
}

run().catch(err => {
    console.error(err);
    process.exit(1);
});
