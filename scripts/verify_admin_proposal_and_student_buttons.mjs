import { chromium } from 'playwright';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import fs from 'node:fs';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');
const artifactDir = path.resolve(projectRoot, 'public/test-artifacts');

if (!fs.existsSync(artifactDir)) {
    fs.mkdirSync(artifactDir, { recursive: true });
}

async function run() {
    const browser = await chromium.launch({
        channel: 'chrome',
        headless: true,
    });

    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 }
    });

    const page = await context.newPage();

    console.log('1. Login sebagai Admin Dinas...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await page.fill('input[type="email"], input[name="email"]', 'admin.dispusip@surabaya.go.id');
    await page.fill('input[type="password"], input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    console.log('2. Buka halaman Detail Pengajuan 30 (Ahmad Yasin)...');
    await page.goto('http://127.0.0.1:8000/admin/applications/30', { waitUntil: 'networkidle' });
    const adminPath = path.join(artifactDir, 'admin_application_30_fixed.png');
    await page.screenshot({ path: adminPath, fullPage: true });
    console.log('Screenshot Admin Detail disimpan di:', adminPath);

    console.log('3. Login sebagai mahasiswa...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await page.fill('input[type="email"], input[name="email"]', 'calon.magang@unesa.ac.id');
    await page.fill('input[type="password"], input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    console.log('4. Buka halaman Pendaftaran Magang Jalur Kelompok...');
    await page.goto('http://127.0.0.1:8000/student/application?mode=group', { waitUntil: 'networkidle' });
    const studentGroupPath = path.join(artifactDir, 'student_group_no_switch_button.png');
    await page.screenshot({ path: studentGroupPath, fullPage: true });
    console.log('Screenshot Mahasiswa Kelompok disimpan di:', studentGroupPath);

    await browser.close();
}

run().catch(err => {
    console.error('Error running capture:', err);
    process.exit(1);
});
