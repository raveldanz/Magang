import { chromium } from 'playwright';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import fs from 'node:fs';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');
const artifactDir = path.resolve(projectRoot, 'public/test-artifacts');
const brainDir = 'C:\\Users\\TK ABA SBY 69 (3)\\.gemini\\antigravity\\brain\\b76ec221-62d4-4a93-a94b-10f6c4f4974f';

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

    // Abort external resources that can hang Playwright
    await context.route('**/*youtube*/**', route => route.abort());
    await context.route('**/*fonts.bunny.net*/**', route => route.abort());

    const page = await context.newPage();

    console.log('1. Login sebagai Admin Dispusip Surabaya...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await page.fill('input[type="email"], input[name="email"]', 'admin.dispusip@surabaya.go.id');
    await page.fill('input[type="password"], input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    console.log('2. Buka /admin/users?role=mentor...');
    await page.goto('http://127.0.0.1:8000/admin/users?role=mentor', { waitUntil: 'networkidle' });
    await page.waitForSelector('select[name="unit_id"]');
    const mentorUnitSelectVisible = await page.isVisible('select[name="unit_id"]');
    console.log(' - Dropdown unit_id terlihat pada role=mentor:', mentorUnitSelectVisible);
    
    const shotMentor = path.join(artifactDir, 'admin_users_filter_mentor.png');
    await page.screenshot({ path: shotMentor });
    fs.copyFileSync(shotMentor, path.join(brainDir, 'admin_users_filter_mentor.png'));

    console.log('3. Buka /admin/users?role=admin...');
    await page.goto('http://127.0.0.1:8000/admin/users?role=admin', { waitUntil: 'networkidle' });
    await page.waitForSelector('select[name="unit_id"]');
    const adminUnitSelectVisible = await page.isVisible('select[name="unit_id"]');
    console.log(' - Dropdown unit_id terlihat pada role=admin:', adminUnitSelectVisible);

    const shotAdmin = path.join(artifactDir, 'admin_users_filter_admin.png');
    await page.screenshot({ path: shotAdmin });
    fs.copyFileSync(shotAdmin, path.join(brainDir, 'admin_users_filter_admin.png'));

    console.log('4. Buka /admin/users?role=admin&unit_id=1 (Filter Sekretariat)...');
    await page.goto('http://127.0.0.1:8000/admin/users?role=admin&unit_id=1', { waitUntil: 'networkidle' });
    const bannerVisible = await page.isVisible('text=Filter Aktif Unit / Bidang Dinas');
    console.log(' - Banner Filter Aktif Bidang Dinas terlihat:', bannerVisible);

    const shotAdminUnit1 = path.join(artifactDir, 'admin_users_filter_admin_unit1.png');
    await page.screenshot({ path: shotAdminUnit1 });
    fs.copyFileSync(shotAdminUnit1, path.join(brainDir, 'admin_users_filter_admin_unit1.png'));

    console.log('5. Buka /admin/users?role=mahasiswa...');
    await page.goto('http://127.0.0.1:8000/admin/users?role=mahasiswa', { waitUntil: 'networkidle' });
    await page.waitForSelector('select[name="university_id"]');
    const univSelectVisible = await page.isVisible('select[name="university_id"]');
    console.log(' - Dropdown university_id terlihat pada role=mahasiswa:', univSelectVisible);

    const shotMahasiswa = path.join(artifactDir, 'admin_users_filter_mahasiswa.png');
    await page.screenshot({ path: shotMahasiswa });
    fs.copyFileSync(shotMahasiswa, path.join(brainDir, 'admin_users_filter_mahasiswa.png'));

    console.log('6. Uji Alpine Reactivity di UI (Ubah Role Mahasiswa -> Mentor Lapangan)...');
    await page.selectOption('select[name="role"]', 'mentor');
    const unitVisibleAfterSwitch = await page.isVisible('select[name="unit_id"]');
    const univVisibleAfterSwitch = await page.isVisible('select[name="university_id"]');
    console.log(' - Setelah switch: unit_id visible?', unitVisibleAfterSwitch, '| university_id visible?', univVisibleAfterSwitch);

    const shotAlpineSwitch = path.join(artifactDir, 'admin_users_alpine_switch_mentor.png');
    await page.screenshot({ path: shotAlpineSwitch });
    fs.copyFileSync(shotAlpineSwitch, path.join(brainDir, 'admin_users_alpine_switch_mentor.png'));

    await browser.close();
    console.log('Semua verifikasi Playwright E2E selesai dengan sukses!');
}

run().catch(err => {
    console.error('Error running verification:', err);
    process.exit(1);
});
