import { chromium } from 'playwright';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import fs from 'node:fs';

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

    console.log('2. Buka halaman Pendaftaran Magang Jalur Kelompok...');
    await page.goto('http://127.0.0.1:8000/student/application', { waitUntil: 'networkidle' });
    
    // Klik card Pendaftaran Kelompok
    await page.click('h4:has-text("Pendaftaran Kelompok")');
    await page.waitForTimeout(300);

    const studentGroupPath = path.join(artifactDir, 'student_group_no_switch_button.png');
    await page.screenshot({ path: studentGroupPath, fullPage: true });
    console.log('Screenshot Mahasiswa Kelompok disimpan di:', studentGroupPath);

    // Klik tombol Ganti Jalur Pendaftaran
    await page.click('button:has-text("Ganti Jalur Pendaftaran")');
    await page.waitForTimeout(300);

    // Klik card Pendaftaran Individu
    await page.click('h4:has-text("Pendaftaran Individu")');
    await page.waitForTimeout(300);

    const studentIndivPath = path.join(artifactDir, 'student_individual_no_switch_button.png');
    await page.screenshot({ path: studentIndivPath, fullPage: true });
    console.log('Screenshot Mahasiswa Individu disimpan di:', studentIndivPath);

    await browser.close();
}

run().catch(err => {
    console.error('Error running capture:', err);
    process.exit(1);
});
