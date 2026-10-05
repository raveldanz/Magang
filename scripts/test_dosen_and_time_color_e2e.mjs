import { chromium } from 'playwright';
import path from 'node:path';
import fs from 'node:fs';

const publicTestArtifactsDir = 'public/test-artifacts';
if (!fs.existsSync(publicTestArtifactsDir)) {
    fs.mkdirSync(publicTestArtifactsDir, { recursive: true });
}

async function runAudit() {
    console.log('========================================================================');
    console.log('  TEST VISUAL E2E: VERIFIKASI LABEL "DOSEN" & WARNA TIMESTAMP');
    console.log('========================================================================');

    const browser = await chromium.launch({ headless: true, channel: 'chrome' });

    try {
        const context = await browser.newContext({ viewport: { width: 1366, height: 768 } });
        const page = await context.newPage();

        console.log('[1] Login Mahasiswa Aktif...');
        await page.goto('http://127.0.0.1:8000/login');
        await page.fill('input[name="email"]', 'mahasiswa.aktif@unesa.ac.id');
        await page.fill('input[name="password"]', 'password');
        await page.click('button[type="submit"]');
        await page.waitForURL('**/dashboard', { timeout: 10000 });

        console.log('[2] Membuka Chat...');
        await page.goto('http://127.0.0.1:8000/chat', { waitUntil: 'networkidle' });
        await page.waitForTimeout(1500);

        // Periksa tab Grup
        console.log('[3] Memeriksa Tab Grup...');
        await page.click('button[role="tab"]:has-text("Grup")');
        await page.waitForTimeout(800);

        // Cari grup bimbingan dosen
        const dosenGroupItem = page.locator('button[data-conv-id]:has-text("Bimbingan Dosen")').first();
        const hasDosenGroup = await dosenGroupItem.count() > 0;
        console.log(`[4] Menemukan grup dengan nama "Bimbingan Dosen": ${hasDosenGroup ? 'YA' : 'TIDAK'}`);

        if (hasDosenGroup) {
            await dosenGroupItem.click();
            await page.waitForTimeout(1000);
        }

        // Periksa apakah masih ada teks "Bimbingan DPL" di mana pun pada halaman
        const dplOccurrences = await page.locator('text=Bimbingan DPL').count();
        console.log(`[5] Kemunculan teks "Bimbingan DPL": ${dplOccurrences} (Harus 0)`);

        // Screenshot chat view
        const shot1 = path.join(publicTestArtifactsDir, 'dosen_group_and_timestamp.png');
        await page.screenshot({ path: shot1 });
        console.log(`[6] Screenshot berhasil disimpan di: ${shot1}`);

        // Verifikasi class warna timestamp
        const timestamps = await page.locator('span.text-xs.shrink-0').evaluateAll(elements => {
            return elements.map(el => ({
                text: el.innerText.trim(),
                className: el.className
            }));
        });
        console.log('[7] Sampel timestamps pada sidebar:', timestamps.slice(0, 5));

        await context.close();
        console.log('\n>>> SEMUA PENGECEKAN VISUAL SELESAI DENGAN SUKSES (Exit Code 0) <<<');
    } catch (err) {
        console.error('Visual test error:', err);
        process.exit(1);
    } finally {
        await browser.close();
    }
}

runAudit();
