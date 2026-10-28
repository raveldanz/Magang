import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { execSync } from 'node:child_process';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');

const publicDir = path.resolve(projectRoot, 'public/test-artifacts');
if (!fs.existsSync(publicDir)) fs.mkdirSync(publicDir, { recursive: true });

async function run() {
    const browser = await chromium.launch({
        headless: true,
        channel: 'chrome',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage']
    });

    try {
        // --- 1. DESKTOP VIEWPORT (1440x900) ---
        console.log('[*] Capturing Desktop Viewport...');
        const ctxDesk = await browser.newContext({ viewport: { width: 1440, height: 900 } });
        const pageDesk = await ctxDesk.newPage();

        await pageDesk.goto('http://127.0.0.1:8000/login', { waitUntil: 'domcontentloaded' });
        await pageDesk.fill('input[name="email"]', 'admin@surabaya.go.id');
        await pageDesk.fill('input[name="password"]', 'password');
        await pageDesk.click('button[type="submit"]');
        await pageDesk.waitForURL(url => !url.pathname.includes('/login'), { timeout: 15000 });

        await pageDesk.goto('http://127.0.0.1:8000/admin/feedbacks', { waitUntil: 'domcontentloaded' });
        await pageDesk.waitForTimeout(1000);
        await pageDesk.screenshot({ path: path.join(publicDir, 'feedbacks_desktop_clean.png'), fullPage: true });

        // Also capture filtered tab: in_progress
        await pageDesk.goto('http://127.0.0.1:8000/admin/feedbacks?status=in_progress', { waitUntil: 'domcontentloaded' });
        await pageDesk.waitForTimeout(1000);
        await pageDesk.screenshot({ path: path.join(publicDir, 'feedbacks_desktop_filtered.png'), fullPage: true });

        // --- 2. MOBILE VIEWPORT (390x844) ---
        console.log('[*] Capturing Mobile Viewport (via viewport resize)...');
        await pageDesk.setViewportSize({ width: 390, height: 844 });
        await pageDesk.goto('http://127.0.0.1:8000/admin/feedbacks', { waitUntil: 'domcontentloaded' });
        await pageDesk.waitForTimeout(600);
        await pageDesk.screenshot({ path: path.join(publicDir, 'feedbacks_mobile_clean.png'), fullPage: true });

        await ctxDesk.close();
        console.log('[+] Visual verification captures completed successfully!');
    } catch (err) {
        console.error('Error during capture:', err);
    } finally {
        await browser.close();
    }
}

run();
