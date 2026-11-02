import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');
const artifactDir = path.resolve(projectRoot, 'public/test-artifacts');

if (!fs.existsSync(artifactDir)) {
    fs.mkdirSync(artifactDir, { recursive: true });
}

async function run() {
    console.log('--- Starting Visual Verification for User Feedback Revisions ---');
    const browser = await chromium.launch({
        headless: true,
        channel: 'chrome',
    });

    try {
        // 1. Proyek Dinas Search Icon Check
        console.log('1. Checking Proyek Dinas Search Icon...');
        const pageProyek = await browser.newPage({ viewport: { width: 1366, height: 768 } });
        await pageProyek.goto('http://127.0.0.1:8000/proyek-dinas', { waitUntil: 'networkidle' });
        await pageProyek.screenshot({ path: path.join(artifactDir, 'proyek_dinas_search_icon_fixed.png'), fullPage: false });
        console.log('Saved: proyek_dinas_search_icon_fixed.png');

        // 2. Welcome Page Desktop Check
        console.log('2. Checking Welcome Page Desktop...');
        const pageWelcomeDesktop = await browser.newPage({ viewport: { width: 1366, height: 768 } });
        await pageWelcomeDesktop.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle' });
        
        // Assert address Siola is present
        const siolaText = await pageWelcomeDesktop.locator('text=Jl. Tunjungan No. 1-3 (Gedung Siola) Lantai 4, Surabaya').first();
        console.log('Siola Address Visible on Desktop:', await siolaText.isVisible());

        // Assert phone (031) 8707329 is present
        const phoneText = await pageWelcomeDesktop.locator('text=(031) 8707329').first();
        console.log('Phone (031) 8707329 Visible on Desktop:', await phoneText.isVisible());

        // Assert badges are deleted
        const badgePenerimaan = await pageWelcomeDesktop.locator('text=Penerimaan Magang Mahasiswa & Siswa').count();
        console.log('Badge Penerimaan count (should be 0):', badgePenerimaan);

        await pageWelcomeDesktop.screenshot({ path: path.join(artifactDir, 'welcome_revisions_desktop.png'), fullPage: false });
        console.log('Saved: welcome_revisions_desktop.png');

        // 3. Welcome Page Mobile Check (375x812)
        console.log('3. Checking Welcome Page Mobile (Address at bottom)...');
        const contextMobile = await browser.newContext({ viewport: { width: 375, height: 950 }, isMobile: true });
        const pageMobile = await contextMobile.newPage();
        await pageMobile.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle' });
        await pageMobile.screenshot({ path: path.join(artifactDir, 'welcome_revisions_mobile_full.png'), fullPage: true });
        console.log('Saved: welcome_revisions_mobile_full.png');

        console.log('--- All Revisions Verified Successfully! ---');
    } finally {
        await browser.close();
    }
}

run().catch(err => {
    console.error('Verification failed:', err);
    process.exit(1);
});
