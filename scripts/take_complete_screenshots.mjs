import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');

const brainDir = 'C:\\Users\\TK ABA SBY 69 (3)\\.gemini\\antigravity-ide\\brain\\49f22495-dbe7-417d-b0e2-10b44c961920';
const publicDir = path.resolve(projectRoot, 'public/test-artifacts');

async function run() {
    console.log('[Runner] Launching Chrome...');
    const browser = await chromium.launch({
        headless: true,
        channel: 'chrome'
    });

    // --- SESI 1: SUPERVISOR ---
    {
        const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
        const page = await context.newPage();

        console.log('[Runner] Login Supervisor supervisor@dispusip.surabaya.go.id...');
        await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
        await page.fill('input[name="email"]', 'supervisor@dispusip.surabaya.go.id');
        await page.fill('input[name="password"]', 'password');
        await page.click('button[type="submit"]');
        await page.waitForTimeout(2000);

        console.log('[Runner] Navigasi ke /supervisor/reports/9...');
        await page.goto('http://127.0.0.1:8000/supervisor/reports/9', { waitUntil: 'networkidle' });
        await page.waitForTimeout(1000);

        const shotPath = path.join(publicDir, 'supervisor_reports_9_with_projects.png');
        await page.screenshot({ path: shotPath, fullPage: true });
        try { fs.copyFileSync(shotPath, path.join(brainDir, 'supervisor_reports_9_with_projects.png')); } catch (_) {}
        console.log('[Runner] Saved: supervisor_reports_9_with_projects.png');

        await context.close();
    }

    // --- SESI 2: MAHASISWA ---
    {
        const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
        const page = await context.newPage();

        console.log('[Runner] Login Mahasiswa eka.aktif@unesa.ac.id...');
        await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
        await page.fill('input[name="email"]', 'eka.aktif@unesa.ac.id');
        await page.fill('input[name="password"]', 'password');
        await page.click('button[type="submit"]');
        await page.waitForTimeout(2000);

        console.log('[Runner] Navigasi ke /student/final-report...');
        await page.goto('http://127.0.0.1:8000/student/final-report', { waitUntil: 'networkidle' });
        await page.waitForTimeout(1000);

        const shotStudent = path.join(publicDir, 'student_final_report_projects.png');
        await page.screenshot({ path: shotStudent, fullPage: true });
        try { fs.copyFileSync(shotStudent, path.join(brainDir, 'student_final_report_projects.png')); } catch (_) {}
        console.log('[Runner] Saved: student_final_report_projects.png');

        // Buka Modal Tambah Proyek
        const tambahBtn = await page.$('button:has-text("Tambah Proyek")');
        if (tambahBtn) {
            console.log('[Runner] Membuka modal Tambah Proyek...');
            await tambahBtn.click();
            await page.waitForTimeout(800);

            const shotModal = path.join(publicDir, 'student_project_modal_form.png');
            await page.screenshot({ path: shotModal });
            try { fs.copyFileSync(shotModal, path.join(brainDir, 'student_project_modal_form.png')); } catch (_) {}
            console.log('[Runner] Saved: student_project_modal_form.png');
        }

        await context.close();
    }

    await browser.close();
    console.log('[Runner] All Done!');
}

run().catch(err => {
    console.error('[Runner] Error:', err);
    process.exit(1);
});
