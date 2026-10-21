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

    const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const page = await context.newPage();

    // Login Mahasiswa Eka Putri Lestari
    console.log('[Runner] Login Mahasiswa eka.aktif@unesa.ac.id...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await page.fill('input[name="email"]', 'eka.aktif@unesa.ac.id');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForTimeout(2000);

    // 1. Screenshot Halaman Ubah Proyek (/student/projects/1/edit)
    console.log('[Runner] Navigasi ke /student/projects/1/edit...');
    await page.goto('http://127.0.0.1:8000/student/projects/1/edit', { waitUntil: 'networkidle' });
    await page.waitForTimeout(1500);
    const shotEdit = path.join(publicDir, 'student_project_edit_access_section.png');
    await page.screenshot({ path: shotEdit, fullPage: true });
    try { fs.copyFileSync(shotEdit, path.join(brainDir, 'student_project_edit_access_section.png')); } catch (_) {}
    console.log('[Runner] Saved: student_project_edit_access_section.png');

    // 2. Screenshot Halaman Detail / Showcase (/projects/1)
    console.log('[Runner] Navigasi ke /projects/1...');
    await page.goto('http://127.0.0.1:8000/projects/1', { waitUntil: 'networkidle' });
    await page.waitForTimeout(1500);
    const shotDetail = path.join(publicDir, 'project_detail_access_section.png');
    await page.screenshot({ path: shotDetail, fullPage: true });
    try { fs.copyFileSync(shotDetail, path.join(brainDir, 'project_detail_access_section.png')); } catch (_) {}
    console.log('[Runner] Saved: project_detail_access_section.png');

    // 3. Screenshot Halaman Tambah Proyek (/student/projects/create)
    console.log('[Runner] Navigasi ke /student/projects/create...');
    await page.goto('http://127.0.0.1:8000/student/projects/create', { waitUntil: 'networkidle' });
    await page.waitForTimeout(1000);
    const shotCreate = path.join(publicDir, 'student_project_create_access_section.png');
    await page.screenshot({ path: shotCreate, fullPage: true });
    try { fs.copyFileSync(shotCreate, path.join(brainDir, 'student_project_create_access_section.png')); } catch (_) {}
    console.log('[Runner] Saved: student_project_create_access_section.png');

    await browser.close();
    console.log('[Runner] All visual captures complete.');
}

run().catch(err => {
    console.error('[Runner Error]', err);
    process.exit(1);
});
