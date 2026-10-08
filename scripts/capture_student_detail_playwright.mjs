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
        // --- 1. DESKTOP VIEWPORT ---
        console.log('[*] Capturing Desktop Viewport (1440x900)...');
        const contextDesktop = await browser.newContext({
            viewport: { width: 1440, height: 900 }
        });
        const pageDesktop = await contextDesktop.newPage();

        await pageDesktop.goto('http://127.0.0.1:8000/login', { waitUntil: 'load' });
        await pageDesktop.fill('input[name="email"]', 'dosen.unesa@unesa.ac.id');
        await pageDesktop.fill('input[name="password"]', 'password');
        await pageDesktop.click('button[type="submit"]');
        await pageDesktop.waitForURL(url => !url.pathname.includes('/login'), { timeout: 15000 });

        console.log('[*] Navigating to /lecturer/students/3...');
        await pageDesktop.goto('http://127.0.0.1:8000/lecturer/students/3', { waitUntil: 'networkidle' });
        await pageDesktop.waitForTimeout(500);

        const desktopPath = path.join(publicDir, 'student_detail_desktop.png');
        await pageDesktop.screenshot({ path: desktopPath, fullPage: true });
        try { fs.copyFileSync(desktopPath, path.join(brainDir, 'student_detail_desktop.png')); } catch (_) {}
        console.log(`[+] Desktop captured: ${desktopPath}`);

        await contextDesktop.close();

        // --- 2. MOBILE VIEWPORT ---
        console.log('[*] Capturing Mobile Viewport (390x844)...');
        const contextMobile = await browser.newContext({
            viewport: { width: 390, height: 844 },
            isMobile: true
        });
        const pageMobile = await contextMobile.newPage();

        await pageMobile.goto('http://127.0.0.1:8000/login', { waitUntil: 'load' });
        await pageMobile.fill('input[name="email"]', 'dosen.unesa@unesa.ac.id');
        await pageMobile.fill('input[name="password"]', 'password');
        await pageMobile.click('button[type="submit"]');
        await pageMobile.waitForURL(url => !url.pathname.includes('/login'), { timeout: 15000 });

        await pageMobile.goto('http://127.0.0.1:8000/lecturer/students/3', { waitUntil: 'networkidle' });
        await pageMobile.waitForTimeout(500);

        const mobilePath = path.join(publicDir, 'student_detail_mobile.png');
        await pageMobile.screenshot({ path: mobilePath, fullPage: true });
        try { fs.copyFileSync(mobilePath, path.join(brainDir, 'student_detail_mobile.png')); } catch (_) {}
        console.log(`[+] Mobile captured: ${mobilePath}`);

        await contextMobile.close();
        console.log('[+] Visual verification completed successfully!');
    } catch (err) {
        console.error('Error in capture script:', err);
    } finally {
        await browser.close();
    }
}

run();
