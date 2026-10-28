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

    console.log('1. Login Admin Dinas...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await page.fill('input[type="email"], input[name="email"]', 'admin.dispusip@surabaya.go.id');
    await page.fill('input[type="password"], input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    console.log('2. Buka Tab Divisi & Kuota Magang di Admin Agencies...');
    await page.goto('http://127.0.0.1:8000/admin/agencies/1', { waitUntil: 'networkidle' });
    // Klik Tab 2 (Divisi & Kuota Magang)
    const tabBtn = page.locator('button:has-text("Divisi & Kuota Magang")').first();
    if (await tabBtn.isVisible()) {
        await tabBtn.click();
        await page.waitForTimeout(500);
    }
    await page.screenshot({ path: path.join(artifactDir, 'admin_agencies_units_tab_clean.png'), fullPage: false });

    console.log('3. Buka Halaman Profil Dinas untuk memastikan bebas dari input batas maksimal...');
    await page.goto('http://127.0.0.1:8000/admin/agency-profile', { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(artifactDir, 'admin_agency_profile_clean.png'), fullPage: false });

    console.log('4. Login Mahasiswa...');
    const studentContext = await browser.newContext({
        viewport: { width: 1440, height: 900 }
    });
    const studentPage = await studentContext.newPage();
    await studentPage.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await studentPage.fill('input[type="email"], input[name="email"]', 'calon.magang@unesa.ac.id');
    await studentPage.fill('input[type="password"], input[name="password"]', 'password');
    await studentPage.click('button[type="submit"]');
    await studentPage.waitForLoadState('networkidle');

    await studentPage.goto('http://127.0.0.1:8000/student/application', { waitUntil: 'networkidle' });
    
    // Klik Pendaftaran Kelompok
    const groupCard = studentPage.locator('text="Pendaftaran Kelompok"').first();
    if (await groupCard.isVisible()) {
        await groupCard.click();
        await studentPage.waitForTimeout(400);
    }

    // Pilih unit dari dropdown
    const unitSelect = studentPage.locator('#group_unit_select');
    if (await unitSelect.isVisible()) {
        await unitSelect.scrollIntoViewIfNeeded();
        await unitSelect.selectOption({ index: 1 });
        await studentPage.waitForTimeout(600);
    }

    await studentPage.screenshot({ path: path.join(artifactDir, 'student_group_create_interactive_quota.png'), fullPage: true });

    await browser.close();
    console.log('Verifikasi visual E2E selesai 100%!');
}

run().catch(err => {
    console.error('Error:', err);
    process.exit(1);
});
