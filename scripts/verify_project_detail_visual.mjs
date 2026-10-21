import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');

const brainDir = 'C:\\Users\\TK ABA SBY 69 (3)\\.gemini\\antigravity-ide\\brain\\49f22495-dbe7-417d-b0e2-10b44c961920';
const publicDir = path.resolve(projectRoot, 'public/test-artifacts');

if (!fs.existsSync(publicDir)) {
    fs.mkdirSync(publicDir, { recursive: true });
}

async function run() {
    console.log('[Visual Test] Menjalankan Chromium channel: chrome...');
    const browser = await chromium.launch({
        headless: true,
        channel: 'chrome'
    });

    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 }
    });
    const page = await context.newPage();

    // 1. Login sebagai Supervisor
    console.log('[Visual Test] Login sebagai atasan.dispusip@surabaya.go.id...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await page.fill('input[name="email"]', 'atasan.dispusip@surabaya.go.id');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForTimeout(2000);

    // 2. Buka Halaman Laporan Supervisor Detail
    console.log('[Visual Test] Buka /supervisor/reports/1...');
    await page.goto('http://127.0.0.1:8000/supervisor/reports/1', { waitUntil: 'networkidle' });
    const shotSup = path.join(publicDir, 'supervisor_report_detail.png');
    await page.screenshot({ path: shotSup, fullPage: true });
    try { fs.copyFileSync(shotSup, path.join(brainDir, 'supervisor_report_detail.png')); } catch (_) {}
    console.log('[Visual Test] Screenshot tersimpan: supervisor_report_detail.png');

    // 3. Buka Halaman Dedicated Detail Proyek (/projects/1)
    console.log('[Visual Test] Buka Halaman Dedicated Proyek /projects/1...');
    await page.goto('http://127.0.0.1:8000/projects/1', { waitUntil: 'networkidle' });
    await page.waitForTimeout(1000);
    const shotProj = path.join(publicDir, 'project_detail_dedicated.png');
    await page.screenshot({ path: shotProj, fullPage: true });
    try { fs.copyFileSync(shotProj, path.join(brainDir, 'project_detail_dedicated.png')); } catch (_) {}
    console.log('[Visual Test] Screenshot tersimpan: project_detail_dedicated.png');

    // 4. Logout dan Login Mahasiswa
    console.log('[Visual Test] Logout dan Login Mahasiswa...');
    // Direct navigate to login
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await page.fill('input[name="email"]', 'eka.aktif@unesa.ac.id');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForTimeout(2000);

    // Buka Laporan Akhir Mahasiswa
    console.log('[Visual Test] Buka /student/final-report...');
    await page.goto('http://127.0.0.1:8000/student/final-report', { waitUntil: 'networkidle' });
    const shotStudent = path.join(publicDir, 'student_final_report.png');
    await page.screenshot({ path: shotStudent, fullPage: true });
    try { fs.copyFileSync(shotStudent, path.join(brainDir, 'student_final_report.png')); } catch (_) {}
    console.log('[Visual Test] Screenshot tersimpan: student_final_report.png');

    // Klik tombol Tambah Proyek untuk memunculkan modal multi-upload
    const tambahBtn = await page.$('button:has-text("Tambah Proyek")');
    if (tambahBtn) {
        console.log('[Visual Test] Buka Modal Tambah Proyek...');
        await tambahBtn.click();
        await page.waitForTimeout(500);
        const shotModal = path.join(publicDir, 'student_project_modal.png');
        await page.screenshot({ path: shotModal, fullPage: true });
        try { fs.copyFileSync(shotModal, path.join(brainDir, 'student_project_modal.png')); } catch (_) {}
        console.log('[Visual Test] Screenshot modal tersimpan: student_project_modal.png');
    }

    await browser.close();
    console.log('[Visual Test] Sukses 100%!');
}

run().catch(err => {
    console.error('[Visual Test] Error:', err);
    process.exit(1);
});
