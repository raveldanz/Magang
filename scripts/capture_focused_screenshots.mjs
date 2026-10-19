/**
 * Focused Element Screenshot Capturer for PPT Presentation
 * Filename: scripts/capture_focused_screenshots.mjs
 */

import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');

const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const brainArtifactDir = 'C:\\Users\\EVAN\\.gemini\\antigravity-ide\\brain\\8092418a-f984-466a-be09-71afeec1e1b8';
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

async function saveElementShot(element, filename) {
    const publicPath = path.join(publicArtifactDir, filename);
    const brainPath = path.join(brainArtifactDir, filename);
    await element.screenshot({ path: publicPath });
    try { fs.copyFileSync(publicPath, brainPath); } catch (_) {}
    console.log(`[+] Captured Element: ${filename}`);
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

async function logout(page) {
    try {
        await page.goto('http://127.0.0.1:8000/dashboard', { waitUntil: 'networkidle2' });
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
    console.log('--- CAPTURING FOCUSED CRISP SCREENSHOTS FOR PPT ---');
    const browser = await puppeteer.launch({
        executablePath: chromePath,
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1440,900']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1440, height: 850 });

    try {
        // 1. Dosen UNESA
        await loginAs(page, 'dosen.unesa@unesa.ac.id');

        // tc01: Dashboard before filter (Viewport 1440x780)
        await page.goto('http://127.0.0.1:8000/lecturer/dashboard', { waitUntil: 'networkidle2' });
        await savePageShot(page, 'tc01_dashboard_before_filter.png', { x: 0, y: 0, width: 1440, height: 750 });

        // tc02: Dashboard after filter
        await page.goto('http://127.0.0.1:8000/lecturer/dashboard?search=Nurul', { waitUntil: 'networkidle2' });
        await savePageShot(page, 'tc02_dashboard_after_filter.png', { x: 0, y: 0, width: 1440, height: 750 });

        // tc03: Monitoring Tab Active
        await page.goto('http://127.0.0.1:8000/lecturer/monitoring?tab=active', { waitUntil: 'networkidle2' });
        await savePageShot(page, 'tc03_monitoring_tab_active.png', { x: 0, y: 0, width: 1440, height: 720 });

        // tc04: Detail Mahasiswa - Card Laporan Akhir Magang (Section 2)
        await page.goto('http://127.0.0.1:8000/lecturer/students/3', { waitUntil: 'networkidle2' });
        // Scroll to Laporan Akhir section
        const cards = await page.$$('.bg-white.rounded-3xl');
        for (const card of cards) {
            const text = await page.evaluate(el => el.innerText, card);
            if (text.includes('Verifikasi Dokumen Laporan Akhir Magang')) {
                await saveElementShot(card, 'tc04_student_detail_report_acc.png');
                break;
            }
        }

        await logout(page);

        // 2. Dosen UNITOMO
        await loginAs(page, 'dosen.unitomo@unitomo.ac.id');

        // tc07: Evaluation Form Before
        await page.goto('http://127.0.0.1:8000/lecturer/students/2/evaluation', { waitUntil: 'networkidle2' });
        await savePageShot(page, 'tc07_evaluation_form_before.png', { x: 120, y: 80, width: 1200, height: 740 });

        // tc08: Detail Mahasiswa - Card Nilai DPL & Mutu (Section 3)
        await page.goto('http://127.0.0.1:8000/lecturer/students/2', { waitUntil: 'networkidle2' });
        const cards2 = await page.$$('.bg-white.rounded-3xl');
        for (const card of cards2) {
            const text = await page.evaluate(el => el.innerText, card);
            if (text.includes('Form Evaluasi & Penilaian Akademik DPL') || text.includes('Kalkulasi Nilai Akhir Otomatis')) {
                await saveElementShot(card, 'tc08_evaluation_submitted_after.png');
                break;
            }
        }

        // tc06: Card Logbook Riwayat (Section 4)
        for (const card of cards2) {
            const text = await page.evaluate(el => el.innerText, card);
            if (text.includes('Riwayat Logbook Aktivitas Mahasiswa')) {
                await saveElementShot(card, 'tc06_logbook_after_verification.png');
                break;
            }
        }

        await logout(page);

        // 3. Dosen UNAIR
        await loginAs(page, 'dosen.unair1@unair.ac.id');

        // tc09: Monitoring Tab Completed
        await page.goto('http://127.0.0.1:8000/lecturer/monitoring?tab=completed', { waitUntil: 'networkidle2' });
        await savePageShot(page, 'tc09_monitoring_tab_completed.png', { x: 0, y: 0, width: 1440, height: 720 });

        // tc10: Security IDOR 403
        await page.goto('http://127.0.0.1:8000/lecturer/students/2', { waitUntil: 'networkidle2' });
        await savePageShot(page, 'tc10_security_idor_forbidden_403.png', { x: 220, y: 100, width: 1000, height: 650 });

        console.log('--- ALL FOCUSED SCREENSHOTS READY ---');
    } finally {
        await browser.close();
    }
}

main().catch(console.error);
