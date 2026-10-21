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

    console.log('1. Login sebagai mahasiswa...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await page.fill('input[type="email"], input[name="email"]', 'calon.magang@unesa.ac.id');
    await page.fill('input[type="password"], input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    console.log('1.5. Buka profil mahasiswa untuk periksa upload dokumen personal...');
    await page.goto('http://127.0.0.1:8000/student/profile', { waitUntil: 'networkidle' });
    const profilePath = path.join(artifactDir, 'profil_dokumen_pribadi.png');
    await page.screenshot({ path: profilePath, fullPage: true });
    console.log('Screenshot Profil disimpan di:', profilePath);

    console.log('2. Buka halaman Pendaftaran Magang...');
    await page.goto('http://127.0.0.1:8000/student/application', { waitUntil: 'networkidle' });

    // Jika ada draft kelompok sebelumnya, batalkan terlebih dahulu agar kembali ke tampilan awal
    const cancelBtn = await page.$('form[action*="group/cancel"] button[type="submit"], button:has-text("Batalkan Kelompok")');
    if (cancelBtn) {
        console.log('Membatalkan draft kelompok sebelumnya untuk mendapatkan tampilan awal bersih...');
        page.on('dialog', async dialog => await dialog.accept());
        await cancelBtn.click();
        await page.waitForLoadState('networkidle');
    }

    // 1. Screenshot Tampilan Awal: 2 Card Besar
    const landingPath = path.join(artifactDir, 'pilihan_jalur_pendaftaran_cards.png');
    await page.screenshot({ path: landingPath, fullPage: true });
    console.log('Screenshot 1 (Landing Cards) disimpan di:', landingPath);

    // 2. Klik Card Pendaftaran Individu
    console.log('Mengklik Card Pendaftaran Individu...');
    await page.click('h4:has-text("Pendaftaran Individu")');
    await page.waitForTimeout(300);
    const individualPath = path.join(artifactDir, 'formulir_pendaftaran_individu.png');
    await page.screenshot({ path: individualPath, fullPage: true });
    console.log('Screenshot 2 (Formulir Individu) disimpan di:', individualPath);

    // 3. Klik tombol Ganti Jalur Pendaftaran
    console.log('Mengklik tombol Ganti Jalur Pendaftaran...');
    await page.click('button:has-text("Ganti Jalur Pendaftaran")');
    await page.waitForTimeout(300);

    // 4. Klik Card Pendaftaran Kelompok
    console.log('Mengklik Card Pendaftaran Kelompok...');
    await page.click('h4:has-text("Pendaftaran Kelompok")');
    await page.waitForTimeout(300);
    const groupPath = path.join(artifactDir, 'formulir_pendaftaran_kelompok.png');
    await page.screenshot({ path: groupPath, fullPage: true });
    console.log('Screenshot 3 (Formulir Kelompok) disimpan di:', groupPath);

    await browser.close();
}

run().catch(err => {
    console.error('Error running capture:', err);
    process.exit(1);
});
