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

    console.log('2. Buka Halaman Pengajuan Admin...');
    await page.goto('http://127.0.0.1:8000/admin/applications', { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(artifactDir, 'admin_applications_index_desktop.png'), fullPage: false });

    // Coba klik tombol accordion jika ada baris kelompok
    const accordionBtn = page.locator('button:has-text("Daftar")').first();
    if (await accordionBtn.isVisible()) {
        console.log('3. Klik Accordion Daftar Anggota...');
        await accordionBtn.click();
        await page.waitForTimeout(500);
        await page.screenshot({ path: path.join(artifactDir, 'admin_applications_accordion_expanded.png'), fullPage: false });
    }

    // Buka Halaman Profil Instansi untuk verifikasi batas maksimal kelompok
    console.log('4. Buka Halaman Profil Instansi...');
    await page.goto('http://127.0.0.1:8000/admin/agency-profile', { waitUntil: 'networkidle' });
    await page.locator('#max_group_members').scrollIntoViewIfNeeded();
    await page.screenshot({ path: path.join(artifactDir, 'admin_agency_profile_max_members.png'), fullPage: false });

    await browser.close();
    console.log('Verifikasi visual selesai 100%!');
}

run().catch(err => {
    console.error('Error:', err);
    process.exit(1);
});
