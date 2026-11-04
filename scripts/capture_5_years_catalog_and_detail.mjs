import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');

const publicDir = path.resolve(projectRoot, 'public/test-artifacts');
const artifactDir = 'C:\\Users\\TK ABA SBY 69 (3)\\.gemini\\antigravity\\brain\\b76ec221-62d4-4a93-a94b-10f6c4f4974f';

if (!fs.existsSync(publicDir)) {
    fs.mkdirSync(publicDir, { recursive: true });
}

async function run() {
    console.log('[Visual Test] Meluncurkan Chromium channel: chrome...');
    const browser = await chromium.launch({
        headless: true,
        channel: 'chrome'
    });

    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 }
    });

    // Cegah request eksternal yang lambat memblokir render Playwright
    await context.route('**/*youtube*/**', route => route.abort());
    await context.route('**/*googlevideo*/**', route => route.abort());
    await context.route('**/*fonts.bunny.net*/**', route => route.abort());
    await context.route('**/*fonts.googleapis.com*/**', route => route.abort());
    await context.route('**/*gstatic.com*/**', route => route.abort());

    const page = await context.newPage();

    // 1. Tangkap Katalog Publik 5 Tahun (/proyek-dinas)
    console.log('[Visual Test] Membuka Katalog Publik 5 Tahun /proyek-dinas...');
    await page.goto('http://127.0.0.1:8000/proyek-dinas', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(2000);

    const shotCatalogFull = path.join(publicDir, 'catalog_5_years_timeline.png');
    await page.screenshot({ path: shotCatalogFull, fullPage: true });
    try { fs.copyFileSync(shotCatalogFull, path.join(artifactDir, 'catalog_5_years_timeline.png')); } catch (_) {}
    console.log('[Visual Test] Screenshot tersimpan: catalog_5_years_timeline.png');

    // 2. Tangkap Detail Proyek Tahun 2022 (Buku Antologi Anak / Rak Melingkar - ID 8)
    console.log('[Visual Test] Membuka Detail Proyek ID 8 (Buku Antologi)...');
    await page.goto('http://127.0.0.1:8000/projects/8', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1500);

    const shotDetail8 = path.join(publicDir, 'detail_project_8_literasi.png');
    await page.screenshot({ path: shotDetail8, fullPage: true });
    try { fs.copyFileSync(shotDetail8, path.join(artifactDir, 'detail_project_8_literasi.png')); } catch (_) {}
    console.log('[Visual Test] Screenshot tersimpan: detail_project_8_literasi.png');

    // 3. Tangkap Detail Proyek Tahun 2023 (Revitalisasi Ruang Baca Interior - ID 10)
    console.log('[Visual Test] Membuka Detail Proyek ID 10 (Revitalisasi Ruang Baca)...');
    await page.goto('http://127.0.0.1:8000/projects/10', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1500);

    const shotDetail10 = path.join(publicDir, 'detail_project_10_interior.png');
    await page.screenshot({ path: shotDetail10, fullPage: true });
    try { fs.copyFileSync(shotDetail10, path.join(artifactDir, 'detail_project_10_interior.png')); } catch (_) {}
    console.log('[Visual Test] Screenshot tersimpan: detail_project_10_interior.png');

    // 4. Tangkap Detail Proyek Tahun 2026 (SIM-Magang - ID 17)
    console.log('[Visual Test] Membuka Detail Proyek ID 17 (SIM-Magang)...');
    await page.goto('http://127.0.0.1:8000/projects/17', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1500);

    const shotDetail17 = path.join(publicDir, 'detail_project_17_sim_magang.png');
    await page.screenshot({ path: shotDetail17, fullPage: true });
    try { fs.copyFileSync(shotDetail17, path.join(artifactDir, 'detail_project_17_sim_magang.png')); } catch (_) {}
    console.log('[Visual Test] Screenshot tersimpan: detail_project_17_sim_magang.png');

    await browser.close();
    console.log('[Visual Test] Semua penangkapan layar 5 tahun selesai dengan sukses!');
}

run().catch(err => {
    console.error('[Visual Test] Error:', err);
    process.exit(1);
});
