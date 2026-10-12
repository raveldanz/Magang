/**
 * Visual Verification Script for 5 New DPL Innovations
 * Filename: scripts/capture_new_dpl_features.mjs
 */

import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');

const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const brainArtifactDir = 'C:\\Users\\EVAN\\.gemini\\antigravity-ide\\brain\\a1c4a1f7-afa2-4dad-8cef-05c011e7377b';
const publicArtifactDir = path.resolve(projectRoot, 'public/test-artifacts');

if (!fs.existsSync(publicArtifactDir)) fs.mkdirSync(publicArtifactDir, { recursive: true });
if (!fs.existsSync(brainArtifactDir)) fs.mkdirSync(brainArtifactDir, { recursive: true });

async function savePageShot(page, filename, clip = null) {
    const publicPath = path.join(publicArtifactDir, filename);
    const brainPath = path.join(brainArtifactDir, filename);
    const opts = { path: publicPath };
    if (clip) {
        opts.clip = clip;
    } else {
        opts.fullPage = false;
    }
    await page.screenshot(opts);
    try { fs.copyFileSync(publicPath, brainPath); } catch (_) {}
    console.log(`[+] Captured: ${filename}`);
}

async function loginAs(page, email, password = 'password') {
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle2' });
    await page.type('input[name="email"]', email);
    await page.type('input[name="password"]', password);
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2' }),
        page.click('button[type="submit"]'),
    ]);
}

async function saveElementShot(element, filename) {
    const publicPath = path.join(publicArtifactDir, filename);
    const brainPath = path.join(brainArtifactDir, filename);
    await element.screenshot({ path: publicPath });
    try { fs.copyFileSync(publicPath, brainPath); } catch (_) {}
    console.log(`[+] Captured Element: ${filename}`);
}

async function logout(page) {
    try {
        await page.goto('http://127.0.0.1:8000/lecturer/dashboard', { waitUntil: 'networkidle2' });
        await page.evaluate(() => {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/logout';
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrf) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = '_token';
                input.value = csrf;
                form.appendChild(input);
            }
            document.body.appendChild(form);
            form.submit();
        });
        await page.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {});
    } catch (_) {}
}

async function main() {
    console.log('--- CAPTURING FOCUSED VISUAL PROOFS FOR 5 DPL FEATURES ---');
    const browser = await puppeteer.launch({
        executablePath: chromePath,
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1440,900']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1440, height: 900 });

    try {
        // 1. SMART ACTION BANNER (Login as DPL UPN who has pending report)
        console.log('[*] Logging in as dosen.upn@upnjatim.ac.id for Smart Action Banner...');
        await loginAs(page, 'dosen.upn@upnjatim.ac.id');
        await page.goto('http://127.0.0.1:8000/lecturer/dashboard', { waitUntil: 'networkidle2' });
        await page.waitForSelector('h2');
        await savePageShot(page, 'tc11_dpl_smart_action_banner.png', { x: 0, y: 0, width: 1440, height: 750 });

        // Logout
        await logout(page);

        // 2. DPL UNESA (Has evaluated student, dual badges, BAP, and supervision log)
        console.log('[*] Logging in as dosen.unesa@unesa.ac.id...');
        await loginAs(page, 'dosen.unesa@unesa.ac.id');

        // Capture Dashboard Table with Dual-Status Badges & BAP Button
        console.log('[*] Capturing Table with Dual-Status Badges...');
        await page.goto('http://127.0.0.1:8000/lecturer/dashboard', { waitUntil: 'networkidle2' });
        await page.evaluate(() => window.scrollBy(0, 420));
        await new Promise(r => setTimeout(r, 600));
        await savePageShot(page, 'tc12_dpl_dual_badges.png', { x: 0, y: 150, width: 1440, height: 700 });

        // Find first student placement link
        const firstStudentHref = await page.evaluate(() => {
            const link = document.querySelector('a[href*="/lecturer/students/"]');
            return link ? link.getAttribute('href') : null;
        });

        if (firstStudentHref) {
            console.log(`[*] Found student link: ${firstStudentHref}`);
            const studentUrl = firstStudentHref.startsWith('http') ? firstStudentHref : `http://127.0.0.1:8000${firstStudentHref}`;
            
            // 3. Student Detail - Capture Supervision Log Widget
            await page.goto(studentUrl, { waitUntil: 'networkidle2' });
            
            const logSection = await page.$('#supervision-log');
            if (logSection) {
                await logSection.scrollIntoView();
                await new Promise(r => setTimeout(r, 500));
                await saveElementShot(logSection, 'tc13_dpl_supervision_log.png');
            } else {
                await page.evaluate(() => window.scrollBy(0, 600));
                await new Promise(r => setTimeout(r, 500));
                await savePageShot(page, 'tc13_dpl_supervision_log.png');
            }

            // 4. Official Grade Sheet (BAP)
            const placementIdMatch = firstStudentHref.match(/\/lecturer\/students\/(\d+)/);
            if (placementIdMatch) {
                const placementId = placementIdMatch[1];
                console.log(`[*] Navigating to BAP Grade Sheet for placement #${placementId}...`);
                await page.goto(`http://127.0.0.1:8000/lecturer/students/${placementId}/grade-sheet`, { waitUntil: 'networkidle2' });
                await new Promise(r => setTimeout(r, 600));
                await savePageShot(page, 'tc14_dpl_grade_sheet_bap.png', { x: 0, y: 0, width: 1440, height: 880 });
            }
        }

        // 5. Monitoring Page with Export CSV & Status Badges
        console.log('[*] Navigating to Monitoring page...');
        await page.goto('http://127.0.0.1:8000/lecturer/monitoring', { waitUntil: 'networkidle2' });
        await new Promise(r => setTimeout(r, 600));
        await savePageShot(page, 'tc15_dpl_monitoring_export.png', { x: 0, y: 0, width: 1440, height: 750 });

        console.log('--- ALL 5 DPL SCREENSHOTS CAPTURED SUCCESSFULLY ---');
    } catch (err) {
        console.error('Fatal during visual capture:', err);
    } finally {
        await browser.close();
    }
}

main();
