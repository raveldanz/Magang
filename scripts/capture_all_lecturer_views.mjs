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
    console.log('[*] Launching Chrome via Playwright (channel: chrome)...');
    const browser = await chromium.launch({
        headless: true,
        channel: 'chrome'
    });

    try {
        const context = await browser.newContext({
            viewport: { width: 1440, height: 900 }
        });
        const page = await context.newPage();

        console.log('[*] Logging in as dosen...');
        await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'load' });
        await page.fill('input[name="email"]', 'dosen.unesa@unesa.ac.id');
        await page.fill('input[name="password"]', 'password');
        await page.click('button[type="submit"]');
        await page.waitForURL(url => !url.pathname.includes('/login'), { timeout: 15000 });

        // 1. Dashboard Dosen
        console.log('[*] Capturing /lecturer/dashboard...');
        await page.goto('http://127.0.0.1:8000/lecturer/dashboard', { waitUntil: 'networkidle' });
        await page.waitForTimeout(500);
        const dashPath = path.join(publicDir, 'lecturer_dashboard_clean.png');
        await page.screenshot({ path: dashPath, fullPage: true });
        try { fs.copyFileSync(dashPath, path.join(brainDir, 'lecturer_dashboard_clean.png')); } catch (_) {}
        console.log(`[+] Dashboard captured: ${dashPath}`);

        // 2. Monitoring Mahasiswa
        console.log('[*] Capturing /lecturer/monitoring...');
        await page.goto('http://127.0.0.1:8000/lecturer/monitoring', { waitUntil: 'networkidle' });
        await page.waitForTimeout(500);
        const monPath = path.join(publicDir, 'lecturer_monitoring_clean.png');
        await page.screenshot({ path: monPath, fullPage: true });
        try { fs.copyFileSync(monPath, path.join(brainDir, 'lecturer_monitoring_clean.png')); } catch (_) {}
        console.log(`[+] Monitoring captured: ${monPath}`);

        // 3. Detail Mahasiswa Bimbingan
        console.log('[*] Capturing /lecturer/students/3...');
        await page.goto('http://127.0.0.1:8000/lecturer/students/3', { waitUntil: 'networkidle' });
        await page.waitForTimeout(500);
        const detailPath = path.join(publicDir, 'lecturer_student_detail_clean.png');
        await page.screenshot({ path: detailPath, fullPage: true });
        try { fs.copyFileSync(detailPath, path.join(brainDir, 'lecturer_student_detail_clean.png')); } catch (_) {}
        console.log(`[+] Student Detail captured: ${detailPath}`);

        await context.close();
        console.log('[+] All views captured successfully!');
    } catch (err) {
        console.error('Error during capture:', err);
    } finally {
        await browser.close();
    }
}

run();
