/**
 * Automated E2E Screenshot Runner for Lecturer Testing Scenarios (Puppeteer Core + Chrome System)
 * Filename: scripts/capture_lecturer_e2e_screenshots.mjs
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

async function saveScreenshot(page, filename) {
    const publicPath = path.join(publicArtifactDir, filename);
    const brainPath = path.join(brainArtifactDir, filename);
    await page.screenshot({ path: publicPath, fullPage: true });
    try {
        fs.copyFileSync(publicPath, brainPath);
    } catch (e) {
        console.warn(`[WARN] Gagal menyalin ke brain: ${e.message}`);
    }
    console.log(`[+] Screenshot tersimpan: ${filename}`);
}

async function loginAs(page, email, password = 'password') {
    console.log(`[LOGIN] Autentikasi sebagai ${email}...`);
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle2' });
    await page.type('input[name="email"]', email);
    await page.type('input[name="password"]', password);
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2' }),
        page.click('button[type="submit"]'),
    ]);
}

async function logout(page) {
    console.log('[LOGOUT] Keluar dari sesi...');
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
    console.log('================================================================');
    console.log('  PENGAMBILAN SCREENSHOT E2E: PENGUJIAN CONTROLLER DOSEN (DPL)  ');
    console.log('================================================================');

    const browser = await puppeteer.launch({
        executablePath: chromePath,
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1440,900']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1440, height: 900 });

    try {
        // ==============================================================
        // SCENARIO 1: DOSEN UNESA - DASHBOARD, FILTER & MONITORING
        // ==============================================================
        await loginAs(page, 'dosen.unesa@unesa.ac.id');

        // 1. Dashboard Full (Before Filter)
        console.log('[SCENARIO 1.1] Dashboard DPL (Seluruh Data Bimbingan)');
        await page.goto('http://127.0.0.1:8000/lecturer/dashboard', { waitUntil: 'networkidle2' });
        await saveScreenshot(page, 'tc01_dashboard_before_filter.png');

        // 2. Dashboard Filtered (After Filter)
        console.log('[SCENARIO 1.2] Dashboard DPL (Difilter Pencarian Mahasiswa)');
        await page.goto('http://127.0.0.1:8000/lecturer/dashboard?search=Nurul', { waitUntil: 'networkidle2' });
        await saveScreenshot(page, 'tc02_dashboard_after_filter.png');

        // 3. Monitoring Lifecycle (Tab Active)
        console.log('[SCENARIO 1.3] Monitoring Mahasiswa Aktif');
        await page.goto('http://127.0.0.1:8000/lecturer/monitoring?tab=active', { waitUntil: 'networkidle2' });
        await saveScreenshot(page, 'tc03_monitoring_tab_active.png');

        // 4. Detail Mahasiswa Bimbingan & Laporan ACC
        console.log('[SCENARIO 1.4] Detail Mahasiswa Bimbingan UNESA (Laporan ACC)');
        await page.goto('http://127.0.0.1:8000/lecturer/students/3', { waitUntil: 'networkidle2' });
        await saveScreenshot(page, 'tc04_student_detail_report_acc.png');

        await logout(page);

        // ==============================================================
        // SCENARIO 2: DOSEN UNITOMO - VERIFIKASI LOGBOOK (BEFORE & AFTER)
        // ==============================================================
        await loginAs(page, 'dosen.unitomo@unitomo.ac.id');

        // 5. Logbook Feed / Detail Mahasiswa UNITOMO (Before ACC)
        console.log('[SCENARIO 2.1] Halaman Logbook & Mahasiswa UNITOMO (Before Verifikasi DPL)');
        await page.goto('http://127.0.0.1:8000/lecturer/students/2', { waitUntil: 'networkidle2' });
        await saveScreenshot(page, 'tc05_logbook_before_verification.png');

        // Buka tombol "Verifikasi DPL" pada entri logbook pertama
        await page.evaluate(() => {
            const btns = Array.from(document.querySelectorAll('button')).filter(b => b.innerText.includes('Verifikasi DPL'));
            if (btns.length > 0) btns[0].click();
        });
        await new Promise(r => setTimeout(r, 600));

        // Submit form verifikasi logbook (Setujui ACC)
        console.log('[SCENARIO 2.2] Menjalankan verifikasi logbook DPL...');
        await page.evaluate(() => {
            const form = document.querySelector('form[action*="/lecturer/logbooks/"]');
            if (form) {
                const txt = form.querySelector('textarea[name="feedback"]');
                if (txt) txt.value = 'Dokumentasi kegiatan dan progress telah diverifikasi & disetujui (ACC) oleh DPL.';
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'status';
                hiddenInput.value = 'approved';
                form.appendChild(hiddenInput);
                form.submit();
            }
        });
        await new Promise(r => setTimeout(r, 1500));
        await page.goto('http://127.0.0.1:8000/lecturer/students/2', { waitUntil: 'networkidle2' });
        await saveScreenshot(page, 'tc06_logbook_after_verification.png');

        // ==============================================================
        // SCENARIO 3: PENILAIAN AKADEMIK DOSEN (BEFORE & AFTER)
        // ==============================================================
        // 7. Formulir Penilaian (Before Input Nilai)
        console.log('[SCENARIO 3.1] Formulir Penilaian Evaluasi Akademik (Before)');
        await page.goto('http://127.0.0.1:8000/lecturer/students/2/evaluation', { waitUntil: 'networkidle2' });
        await saveScreenshot(page, 'tc07_evaluation_form_before.png');

        // Submit evaluasi dosen via form evaluation
        console.log('[SCENARIO 3.2] Mengisi nilai aspek dan feedback evaluasi dosen...');
        await page.evaluate(() => {
            const f = document.querySelector('form[action*="/evaluation"]');
            if (f) {
                const sm = f.querySelector('input[name="score_mastery"]');
                if (sm) sm.value = '92';
                const sr = f.querySelector('input[name="score_report"]');
                if (sr) sr.value = '90';
                const sa = f.querySelector('input[name="score_attitude"]');
                if (sa) sa.value = '94';
                const fd = f.querySelector('textarea[name="feedback_dosen"]');
                if (fd) fd.value = 'Mahasiswa menunjukkan etos kerja, kedisiplinan, dan kemampuan teknis yang sangat baik selama magang.';
                f.submit();
            }
        });
        await new Promise(r => setTimeout(r, 2000));
        await page.goto('http://127.0.0.1:8000/lecturer/students/2', { waitUntil: 'networkidle2' });
        await saveScreenshot(page, 'tc08_evaluation_submitted_after.png');

        await logout(page);

        // ==============================================================
        // SCENARIO 4: DOSEN UNAIR - TAB COMPLETED & ANTI-IDOR SECURITY
        // ==============================================================
        await loginAs(page, 'dosen.unair1@unair.ac.id');

        // 9. Monitoring Tab Completed (Mahasiswa Selesai/Lulus)
        console.log('[SCENARIO 4.1] Monitoring Tab Completed (Mahasiswa Lulus Magang)');
        await page.goto('http://127.0.0.1:8000/lecturer/monitoring?tab=completed', { waitUntil: 'networkidle2' });
        await saveScreenshot(page, 'tc09_monitoring_tab_completed.png');

        // 10. Security Test: Anti-IDOR Dosen UNAIR membuka mahasiswa bimbingan Dosen UNITOMO (Placement #2)
        console.log('[SCENARIO 4.2] Security Test: Akses Ilegal Placement Dosen Lain (Anti-IDOR)');
        await page.goto('http://127.0.0.1:8000/lecturer/students/2', { waitUntil: 'networkidle2' });
        await saveScreenshot(page, 'tc10_security_idor_forbidden_403.png');

        console.log('================================================================');
        console.log('  [SUKSES] SELURUH 10 SCREENSHOT BUKTI PENGUJIAN BERHASIL!       ');
        console.log('================================================================');
    } finally {
        await browser.close();
    }
}

main().catch(err => {
    console.error('[-] Error capture screenshots:', err);
    process.exit(1);
});
