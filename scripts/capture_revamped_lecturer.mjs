import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');

const publicDir = path.resolve(projectRoot, 'public/test-artifacts');
const brainDir = process.env.ARTIFACT_DIR || publicDir;

if (!fs.existsSync(publicDir)) fs.mkdirSync(publicDir, { recursive: true });
try {
    if (!fs.existsSync(brainDir)) fs.mkdirSync(brainDir, { recursive: true });
} catch (_) {}

async function run() {
    const browser = await chromium.launch({
        headless: true,
        channel: 'chrome',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage']
    });

    try {
        console.log('[*] Capturing Desktop Viewports (1440x900)...');
        const ctxDesk = await browser.newContext({ viewport: { width: 1440, height: 900 } });
        const page = await ctxDesk.newPage();

        await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'domcontentloaded' });
        await page.fill('input[name="email"]', 'dosen.unesa@unesa.ac.id');
        await page.fill('input[name="password"]', 'password');
        await page.click('button[type="submit"]');
        await page.waitForURL(url => !url.pathname.includes('/login'), { timeout: 15000 });

        // Monitoring Desktop
        await page.goto('http://127.0.0.1:8000/lecturer/monitoring', { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(600);
        await page.screenshot({ path: path.join(publicDir, 'monitoring_desktop_revamped.png'), fullPage: true });

        // Logbook Desktop
        await page.goto('http://127.0.0.1:8000/lecturer/logbooks', { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(600);
        await page.screenshot({ path: path.join(publicDir, 'logbook_desktop_revamped.png'), fullPage: true });

        // Student Detail Desktop
        await page.goto('http://127.0.0.1:8000/lecturer/students/3', { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(600);
        await page.screenshot({ path: path.join(publicDir, 'student_detail_desktop_revamped.png'), fullPage: true });

        // --- 2. MOBILE VIEWPORT (390x844 via resize) ---
        console.log('[*] Capturing Mobile Viewports (390x844)...');
        await page.setViewportSize({ width: 390, height: 844 });

        // Monitoring Mobile
        await page.goto('http://127.0.0.1:8000/lecturer/monitoring', { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(600);
        await page.screenshot({ path: path.join(publicDir, 'monitoring_mobile_revamped.png'), fullPage: true });

        // Logbook Mobile
        await page.goto('http://127.0.0.1:8000/lecturer/logbooks', { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(600);
        await page.screenshot({ path: path.join(publicDir, 'logbook_mobile_revamped.png'), fullPage: true });

        // Student Detail Mobile
        await page.goto('http://127.0.0.1:8000/lecturer/students/3', { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(600);
        await page.screenshot({ path: path.join(publicDir, 'student_detail_mobile_revamped.png'), fullPage: true });

        await ctxDesk.close();
        console.log('[+] All desktop and mobile captures finished successfully!');
    } catch (err) {
        console.error('Error during capture:', err);
    } finally {
        await browser.close();
    }
}

run();
