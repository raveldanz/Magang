import { chromium } from 'playwright';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');
const artifactDir = path.resolve(projectRoot, 'public/test-artifacts');

async function run() {
    const browser = await chromium.launch({
        channel: 'chrome',
        headless: true,
    });

    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 }
    });

    const page = await context.newPage();

    console.log('1. Login sebagai mahasiswa...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await page.fill('input[type="email"], input[name="email"]', 'calon.magang@unesa.ac.id');
    await page.fill('input[type="password"], input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    console.log('2. Buka halaman Pendaftaran Magang...');
    await page.goto('http://127.0.0.1:8000/student/application', { waitUntil: 'networkidle' });

    console.log('3. Klik tab Kelompok (Tim)...');
    await page.click('button:has-text("Kelompok (Tim)")');
    await page.waitForTimeout(400);

    console.log('4. Buat kelompok baru...');
    await page.fill('form[action*="group/create"] input[name="name"]', 'Tim Inovasi Digital MBKM');
    await page.selectOption('form[action*="group/create"] select[name="unit_id"]', { index: 1 });
    await page.fill('form[action*="group/create"] input[name="start_date"]', '2026-11-01');
    await page.fill('form[action*="group/create"] input[name="end_date"]', '2027-01-31');
    await page.click('form[action*="group/create"] button[type="submit"]');
    await page.waitForLoadState('networkidle');

    const screenshotPath = path.join(artifactDir, 'group_leader_dashboard_ui.png');
    await page.screenshot({ path: screenshotPath, fullPage: true });
    console.log('Screenshot saved to:', screenshotPath);

    await browser.close();
}

run().catch(err => {
    console.error('Error running capture:', err);
    process.exit(1);
});
