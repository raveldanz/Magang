import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');

const publicDir = path.resolve(projectRoot, 'public/test-artifacts');
const artifactDir = process.env.ARTIFACT_DIR || 'C:\\Users\\TK ABA SBY 69 (3)\\.gemini\\antigravity\\brain\\b76ec221-62d4-4a93-a94b-10f6c4f4974f';

if (!fs.existsSync(publicDir)) {
    fs.mkdirSync(publicDir, { recursive: true });
}
if (!fs.existsSync(artifactDir)) {
    fs.mkdirSync(artifactDir, { recursive: true });
}

async function run() {
    console.log('[Visual Test] Meluncurkan Playwright Chromium channel: chrome...');
    const browser = await chromium.launch({
        headless: true,
        channel: 'chrome'
    });

    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 }
    });
    const page = await context.newPage();

    // 1. Uji Halaman Publik Katalog Proyek & Detail Proyek #1
    console.log('[Visual Test] Mengakses katalog publik /proyek...');
    await page.goto('http://127.0.0.1:8000/proyek', { waitUntil: 'networkidle' });
    const shotCatalog = path.join(publicDir, 'visual_project_catalog.png');
    await page.screenshot({ path: shotCatalog, fullPage: false });
    try { fs.copyFileSync(shotCatalog, path.join(artifactDir, 'visual_project_catalog.png')); } catch (_) {}
    console.log('[Visual Test] Screenshot tersimpan: visual_project_catalog.png');

    console.log('[Visual Test] Mengakses halaman detail proyek /projects/1...');
    await page.goto('http://127.0.0.1:8000/projects/1', { waitUntil: 'networkidle' });
    const shotDetail = path.join(publicDir, 'visual_project_detail.png');
    await page.screenshot({ path: shotDetail, fullPage: true });
    try { fs.copyFileSync(shotDetail, path.join(artifactDir, 'visual_project_detail.png')); } catch (_) {}
    console.log('[Visual Test] Screenshot tersimpan: visual_project_detail.png');

    // 2. Login Mahasiswa dan Buka Form Tambah Proyek
    console.log('[Visual Test] Login sebagai fauzan.ketua.aktif@unesa.ac.id...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await page.fill('input[name="email"]', 'fauzan.ketua.aktif@unesa.ac.id');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForTimeout(2000);

    console.log('[Visual Test] Mengakses form tambah proyek /student/projects/create...');
    await page.goto('http://127.0.0.1:8000/student/projects/create', { waitUntil: 'networkidle' });
    console.log('[Visual Test] Current URL:', page.url());
    console.log('[Visual Test] Current Title:', await page.title());
    await page.waitForTimeout(1000);

    // Screenshot default (Individu)
    const shotCreateIndividu = path.join(publicDir, 'visual_project_create_individu.png');
    await page.screenshot({ path: shotCreateIndividu, fullPage: true });
    try { fs.copyFileSync(shotCreateIndividu, path.join(artifactDir, 'visual_project_create_individu.png')); } catch (_) {}
    console.log('[Visual Test] Screenshot tersimpan: visual_project_create_individu.png');

    // Klik mode 'Proyek Kelompok'
    console.log('[Visual Test] Memilih mode Proyek Kelompok & Kategori Media Kreatif...');
    await page.click('input[value="group"]');
    await page.selectOption('select[name="category"]', 'media');
    await page.waitForTimeout(500);

    const shotCreateGroup = path.join(publicDir, 'visual_project_create_group.png');
    await page.screenshot({ path: shotCreateGroup, fullPage: true });
    try { fs.copyFileSync(shotCreateGroup, path.join(artifactDir, 'visual_project_create_group.png')); } catch (_) {}
    console.log('[Visual Test] Screenshot tersimpan: visual_project_create_group.png');

    // Uji pemilihan kategori "lainnya" dan deteksi cerdas
    console.log('[Visual Test] Memilih kategori Inovasi Lainnya & mengetik kata kunci IT...');
    await page.selectOption('select[name="category"]', 'lainnya');
    await page.fill('input[name="custom_category"]', 'Aplikasi Mobile Presensi Kearsipan');
    await page.waitForTimeout(500);

    const shotSmartDetect = path.join(publicDir, 'visual_project_smart_detect.png');
    await page.screenshot({ path: shotSmartDetect, fullPage: true });
    try { fs.copyFileSync(shotSmartDetect, path.join(artifactDir, 'visual_project_smart_detect.png')); } catch (_) {}
    console.log('[Visual Test] Screenshot tersimpan: visual_project_smart_detect.png');

    await browser.close();
    console.log('[Visual Test] Seluruh pengujian visual selesai dengan sukses!');
}

run().catch(err => {
    console.error('[Visual Test] Error:', err);
    process.exit(1);
});

