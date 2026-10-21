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
    console.log('[Runner] Launching Chrome channel...');
    const browser = await chromium.launch({
        headless: true,
        channel: 'chrome'
    });

    const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const page = await context.newPage();

    // 1. Login Mahasiswa Eka Putri Lestari
    console.log('[Runner] Login Mahasiswa Eka Putri Lestari (eka.aktif@unesa.ac.id)...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await page.fill('input[name="email"]', 'eka.aktif@unesa.ac.id');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForTimeout(2000);

    // 2. Buka Halaman Laporan Akhir (/student/final-report) yang berisi 10 kartu proyek
    console.log('[Runner] Navigasi ke /student/final-report...');
    await page.goto('http://127.0.0.1:8000/student/final-report', { waitUntil: 'networkidle' });
    await page.waitForTimeout(1500);

    const shotFinalReportFull = path.join(publicDir, 'eka_10_projects_full.png');
    await page.screenshot({ path: shotFinalReportFull, fullPage: true });
    try { fs.copyFileSync(shotFinalReportFull, path.join(brainDir, 'eka_10_projects_full.png')); } catch (_) {}
    console.log('[Runner] Saved: eka_10_projects_full.png');

    // Scroll ke bagian katalog kartu proyek untuk viewport fokus
    await page.evaluate(() => {
        const el = document.querySelector('.grid.grid-cols-1');
        if (el) el.scrollIntoView({ behavior: 'instant', block: 'start' });
    });
    await page.waitForTimeout(500);
    const shotCatalogFocus = path.join(publicDir, 'eka_10_projects_catalog_focus.png');
    await page.screenshot({ path: shotCatalogFocus });
    try { fs.copyFileSync(shotCatalogFocus, path.join(brainDir, 'eka_10_projects_catalog_focus.png')); } catch (_) {}
    console.log('[Runner] Saved: eka_10_projects_catalog_focus.png');

    // 3. Klik tombol Ubah pada Proyek #1 untuk membuka formulir edit proyek
    console.log('[Runner] Klik tombol Ubah pada proyek pertama...');
    const editBtn = page.locator('a[title="Ubah data proyek"]').first();
    const editUrl = await editBtn.getAttribute('href');
    await page.goto(editUrl, { waitUntil: 'networkidle' });
    await page.waitForTimeout(1500);
    const shotEditBag2 = path.join(publicDir, 'student_project_edit_bag2_standalone.png');
    await page.screenshot({ path: shotEditBag2, fullPage: true });
    try { fs.copyFileSync(shotEditBag2, path.join(brainDir, 'student_project_edit_bag2_standalone.png')); } catch (_) {}
    console.log('[Runner] Saved: student_project_edit_bag2_standalone.png (' + editUrl + ')');

    // 4. Kembali ke /student/final-report dan buka Detail Proyek Non-IT (#2: Buku Anak)
    console.log('[Runner] Navigasi ke detail proyek buku anak...');
    await page.goto('http://127.0.0.1:8000/student/final-report', { waitUntil: 'networkidle' });
    await page.waitForTimeout(1000);
    const detailBtn2 = page.locator('a:has-text("Lihat Detail")').nth(1);
    const detailUrl2 = await detailBtn2.getAttribute('href');
    await page.goto(detailUrl2, { waitUntil: 'networkidle' });
    await page.waitForTimeout(1500);
    const shotDetailNonIT = path.join(publicDir, 'project_detail_buku_anak_non_it.png');
    await page.screenshot({ path: shotDetailNonIT, fullPage: true });
    try { fs.copyFileSync(shotDetailNonIT, path.join(brainDir, 'project_detail_buku_anak_non_it.png')); } catch (_) {}
    console.log('[Runner] Saved: project_detail_buku_anak_non_it.png (' + detailUrl2 + ')');

    await browser.close();
    console.log('[Runner] All captures completed successfully.');
}

run().catch(err => {
    console.error('[Runner Error]', err);
    process.exit(1);
});
