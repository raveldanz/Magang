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
    console.log('🚀 Memulai audit visual tabel desktop /admin/applications...');
    const browser = await chromium.launch({
        channel: 'chrome',
        headless: true,
    });

    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 }
    });

    const page = await context.newPage();

    console.log('1. Login Admin Dispusip...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await page.fill('input[type="email"], input[name="email"]', 'admin.dispusip@surabaya.go.id');
    await page.fill('input[type="password"], input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    console.log('2. Buka Halaman /admin/applications (Desktop Viewport)...');
    await page.goto('http://127.0.0.1:8000/admin/applications', { waitUntil: 'networkidle' });

    // Tunggu tabel termuat
    await page.waitForSelector('table');

    console.log('3. Tangkap layar tabel desktop utama...');
    const tableElement = page.locator('.hidden.md\\:block').first();
    await tableElement.scrollIntoViewIfNeeded();
    await page.screenshot({ 
        path: path.join(artifactDir, 'desktop_applications_table_clean.png'),
        fullPage: false 
    });

    // Evaluasi DOM: periksa konsistensi baris dan ketiadaan duplikasi
    const auditMetrics = await page.evaluate(() => {
        const rows = Array.from(document.querySelectorAll('.hidden.md\\:block tbody > tr:not([x-show])'));
        const results = {
            totalRows: rows.length,
            rowHeights: [],
            col2HasAnggotaBadge: false,
            col4HasDuplicateMentorWarning: false,
            groupPillFound: false,
        };

        rows.forEach((row, idx) => {
            const rect = row.getBoundingClientRect();
            const cells = row.querySelectorAll('td');
            results.rowHeights.push({
                index: idx,
                height: rect.height,
            });

            if (cells.length >= 5) {
                // Kolom 2 (index 2): Bidang & Mentor
                const col2Text = cells[2]?.innerText || '';
                if (col2Text.includes('Anggota Terdaftar')) {
                    results.col2HasAnggotaBadge = true;
                }

                // Kolom 4 (index 4): Status
                const col4Text = cells[4]?.innerText || '';
                if (col4Text.includes('Mentor belum ditetapkan') || col4Text.includes('Pra-Magang')) {
                    results.col4HasDuplicateMentorWarning = true;
                }
            }
        });

        const pillBtn = document.querySelector('.hidden.md\\:block button[title*="anggota"], .hidden.md\\:block button:has(svg)');
        results.groupPillFound = !!pillBtn;

        return results;
    });

    console.log('📊 Metrik Audit DOM:', JSON.stringify(auditMetrics, null, 2));

    // Periksa apakah ada tombol pill accordion kelompok
    const accordionBtn = page.locator('.hidden.md\\:block button[title*="anggota"], .hidden.md\\:block button:has-text("Anggota")').first();
    if (await accordionBtn.isVisible()) {
        console.log('4. Klik Pill Button [👥 X Anggota ⌄] untuk membuka accordion...');
        await accordionBtn.click();
        await page.waitForTimeout(400);

        console.log('5. Tangkap layar tabel dengan accordion terbuka...');
        await page.screenshot({ 
            path: path.join(artifactDir, 'desktop_applications_accordion_expanded.png'),
            fullPage: false 
        });
    } else {
        console.log('ℹ️ Tidak ada baris kelompok pada halaman pertama, mencoba filter kelompok...');
        await page.goto('http://127.0.0.1:8000/admin/applications?registration_type=group', { waitUntil: 'networkidle' });
        const groupAccordionBtn = page.locator('.hidden.md\\:block button:has-text("Anggota")').first();
        if (await groupAccordionBtn.isVisible()) {
            console.log('4b. Klik Pill Button pada filter kelompok...');
            await page.screenshot({ 
                path: path.join(artifactDir, 'desktop_applications_group_filtered.png'),
                fullPage: false 
            });
            await groupAccordionBtn.click();
            await page.waitForTimeout(400);
            await page.screenshot({ 
                path: path.join(artifactDir, 'desktop_applications_accordion_expanded.png'),
                fullPage: false 
            });
        }
    }

    console.log('6. Verifikasi Mobile Viewport (390x844)...');
    const mobilePage = await context.newPage();
    await mobilePage.setViewportSize({ width: 390, height: 844 });
    await mobilePage.goto('http://127.0.0.1:8000/admin/applications', { waitUntil: 'networkidle' });
    await mobilePage.screenshot({ 
        path: path.join(artifactDir, 'mobile_applications_verified_untouched.png'),
        fullPage: false 
    });

    await browser.close();
    console.log('✅ Visual verification selesai tanpa error!');
}

run().catch(err => {
    console.error('❌ Error during visual verification:', err);
    process.exit(1);
});
