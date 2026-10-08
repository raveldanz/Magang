import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');

const brainDir = 'C:\\Users\\EVAN\\.gemini\\antigravity-ide\\brain\\860a2603-0d83-4450-8014-53b0e6df4d3a';
const publicDir = path.resolve(projectRoot, 'public/test-artifacts');

if (!fs.existsSync(publicDir)) fs.mkdirSync(publicDir, { recursive: true });
if (!fs.existsSync(brainDir)) fs.mkdirSync(brainDir, { recursive: true });

async function run() {
    const browser = await chromium.launch({ headless: true, channel: 'chrome' });

    try {
        // --- 1. DESKTOP VIEWPORT ---
        console.log('[*] Capturing Desktop Viewports...');
        const ctxDesk = await browser.newContext({ viewport: { width: 1440, height: 900 } });
        const pageDesk = await ctxDesk.newPage();

        await pageDesk.goto('http://127.0.0.1:8000/login', { waitUntil: 'load' });
        await pageDesk.fill('input[name="email"]', 'dosen.unesa@unesa.ac.id');
        await pageDesk.fill('input[name="password"]', 'password');
        await pageDesk.click('button[type="submit"]');
        await pageDesk.waitForURL(url => !url.pathname.includes('/login'), { timeout: 15000 });

        // Dashboard Desktop
        await pageDesk.goto('http://127.0.0.1:8000/lecturer/dashboard', { waitUntil: 'networkidle' });
        await pageDesk.waitForTimeout(400);
        await pageDesk.screenshot({ path: path.join(publicDir, 'lecturer_dashboard_desktop_revamped.png'), fullPage: true });

        // Monitoring Desktop
        await pageDesk.goto('http://127.0.0.1:8000/lecturer/monitoring', { waitUntil: 'networkidle' });
        await pageDesk.waitForTimeout(400);
        await pageDesk.screenshot({ path: path.join(publicDir, 'monitoring_desktop_revamped.png'), fullPage: true });

        // Logbook Desktop
        await pageDesk.goto('http://127.0.0.1:8000/lecturer/logbooks', { waitUntil: 'networkidle' });
        await pageDesk.waitForTimeout(400);
        await pageDesk.screenshot({ path: path.join(publicDir, 'logbook_desktop_revamped.png'), fullPage: true });

        // Student Detail Desktop
        await pageDesk.goto('http://127.0.0.1:8000/lecturer/students/3', { waitUntil: 'networkidle' });
        await pageDesk.waitForTimeout(400);
        await pageDesk.screenshot({ path: path.join(publicDir, 'student_detail_desktop_revamped.png'), fullPage: true });

        await ctxDesk.close();

        // --- 2. MOBILE VIEWPORT (390x844) ---
        console.log('[*] Capturing Mobile Viewports...');
        const ctxMob = await browser.newContext({ viewport: { width: 390, height: 844 }, isMobile: true });
        const pageMob = await ctxMob.newPage();

        await pageMob.goto('http://127.0.0.1:8000/login', { waitUntil: 'load' });
        await pageMob.fill('input[name="email"]', 'dosen.unesa@unesa.ac.id');
        await pageMob.fill('input[name="password"]', 'password');
        await pageMob.click('button[type="submit"]');
        await pageMob.waitForURL(url => !url.pathname.includes('/login'), { timeout: 15000 });

        // Monitoring Mobile
        await pageMob.goto('http://127.0.0.1:8000/lecturer/monitoring', { waitUntil: 'networkidle' });
        await pageMob.waitForTimeout(400);
        await pageMob.screenshot({ path: path.join(publicDir, 'monitoring_mobile_revamped.png'), fullPage: true });

        // Logbook Mobile
        await pageMob.goto('http://127.0.0.1:8000/lecturer/logbooks', { waitUntil: 'networkidle' });
        await pageMob.waitForTimeout(400);
        await pageMob.screenshot({ path: path.join(publicDir, 'logbook_mobile_revamped.png'), fullPage: true });

        // Student Detail Mobile
        await pageMob.goto('http://127.0.0.1:8000/lecturer/students/3', { waitUntil: 'networkidle' });
        await pageMob.waitForTimeout(400);
        await pageMob.screenshot({ path: path.join(publicDir, 'student_detail_mobile_revamped.png'), fullPage: true });

        await ctxMob.close();
        console.log('[+] All desktop and mobile captures finished successfully!');
    } catch (err) {
        console.error('Error during capture:', err);
    } finally {
        await browser.close();
    }
}

run();
