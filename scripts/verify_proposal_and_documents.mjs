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
    console.log('[E2E Test] Launching Chrome channel for proposal and documents audit...');
    const browser = await chromium.launch({
        headless: true,
        channel: 'chrome'
    });

    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 }
    });
    const page = await context.newPage();

    try {
        // 1. LOGIN AS SUPERVISOR
        console.log('[E2E Test] 1. Logging in as Supervisor (supervisor@dispusip.surabaya.go.id)...');
        await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
        await page.fill('input[name="email"]', 'supervisor@dispusip.surabaya.go.id');
        await page.fill('input[name="password"]', 'password');
        await page.click('button[type="submit"]');
        await page.waitForURL(url => !url.pathname.includes('/login'), { timeout: 15000 });

        // 2. TEST PROPOSAL KELOMPOK 3 (The exact URL from user screenshot)
        console.log('[E2E Test] 2. Verifying URL: http://127.0.0.1:8000/documents/groups/3/proposal ...');
        const responseGroup3 = await page.goto('http://127.0.0.1:8000/documents/groups/3/proposal', { waitUntil: 'load' });
        console.log(`[E2E Test] Response status: ${responseGroup3.status()}, Content-Type: ${responseGroup3.headers()['content-type']}`);
        
        if (responseGroup3.status() !== 200) {
            throw new Error(`Expected HTTP 200 for group 3 proposal, got ${responseGroup3.status()}`);
        }
        await page.screenshot({ path: path.join(artifactDir, 'proposal_group_3_verified.png') });

        // 3. TEST PROPOSAL KELOMPOK 2
        console.log('[E2E Test] 3. Verifying URL: http://127.0.0.1:8000/documents/groups/2/proposal ...');
        const responseGroup2 = await page.goto('http://127.0.0.1:8000/documents/groups/2/proposal', { waitUntil: 'load' });
        console.log(`[E2E Test] Response status: ${responseGroup2.status()}`);
        if (responseGroup2.status() !== 200) {
            throw new Error(`Expected HTTP 200 for group 2 proposal, got ${responseGroup2.status()}`);
        }

        // 4. TEST APPLICATION DOCUMENT (CV / KTM / Transkrip)
        console.log('[E2E Test] 4. Verifying URL: http://127.0.0.1:8000/documents/applications/8 ...');
        const responseDoc8 = await page.goto('http://127.0.0.1:8000/documents/applications/8', { waitUntil: 'load' });
        console.log(`[E2E Test] Response status: ${responseDoc8.status()}, Content-Type: ${responseDoc8.headers()['content-type']}`);
        if (responseDoc8.status() !== 200) {
            throw new Error(`Expected HTTP 200 for application document 8, got ${responseDoc8.status()}`);
        }
        await page.screenshot({ path: path.join(artifactDir, 'application_document_verified.png') });

        // 5. TEST SUPERVISOR APPLICATIONS SHOW PAGE
        console.log('[E2E Test] 5. Checking Supervisor Applications Show Page for Group 3...');
        await page.goto('http://127.0.0.1:8000/supervisor/applications/23', { waitUntil: 'networkidle' });
        await page.screenshot({ path: path.join(artifactDir, 'supervisor_app_group3_detail.png'), fullPage: true });

        console.log('[E2E Test] ALL PROPOSAL AND DOCUMENT CHECKS PASSED SUCCESSFULLY (Exit Code 0)!');
    } finally {
        await browser.close();
    }
}

run().catch(err => {
    console.error('[E2E Test ERROR]', err);
    process.exit(1);
});
