import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');

const publicDir = path.resolve(projectRoot, 'public/test-artifacts');
const brainDir = process.env.ARTIFACT_DIR || publicDir;

async function run() {
    const browser = await chromium.launch({ headless: true, channel: 'chrome' });

    try {
        const context = await browser.newContext({
            viewport: { width: 390, height: 844 },
            isMobile: true
        });
        const page = await context.newPage();

        await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'load' });
        await page.fill('input[name="email"]', 'dosen.unesa@unesa.ac.id');
        await page.fill('input[name="password"]', 'password');
        await page.click('button[type="submit"]');
        await page.waitForURL(url => !url.pathname.includes('/login'), { timeout: 15000 });

        // Mobile Dashboard
        await page.goto('http://127.0.0.1:8000/lecturer/dashboard', { waitUntil: 'networkidle' });
        await page.waitForTimeout(400);
        const dashMobile = path.join(publicDir, 'lecturer_dashboard_mobile.png');
        await page.screenshot({ path: dashMobile, fullPage: true });
        try { fs.copyFileSync(dashMobile, path.join(brainDir, 'lecturer_dashboard_mobile.png')); } catch (_) {}

        // Mobile Monitoring
        await page.goto('http://127.0.0.1:8000/lecturer/monitoring', { waitUntil: 'networkidle' });
        await page.waitForTimeout(400);
        const monMobile = path.join(publicDir, 'lecturer_monitoring_mobile.png');
        await page.screenshot({ path: monMobile, fullPage: true });
        try { fs.copyFileSync(monMobile, path.join(brainDir, 'lecturer_monitoring_mobile.png')); } catch (_) {}

        await context.close();
        console.log('[+] Mobile captures complete!');
    } finally {
        await browser.close();
    }
}

run();
