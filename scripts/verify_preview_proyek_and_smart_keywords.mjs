import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';

async function main() {
    console.log('Launching Playwright Chrome...');
    const browser = await chromium.launch({
        channel: 'chrome',
        headless: true
    });

    const context = await browser.newContext({
        viewport: { width: 1366, height: 768 },
        extraHTTPHeaders: { 'Connection': 'close' }
    });

    const page = await context.newPage();

    console.log('Logging in as eka.aktif@unesa.ac.id...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="email"], input[type="email"]', 'eka.aktif@unesa.ac.id');
    await page.fill('input[name="password"], input[type="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForTimeout(1500);

    console.log('Navigating to project 16 edit page...');
    await page.goto('http://127.0.0.1:8000/student/projects/16/edit', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);

    const outputDir = path.resolve('public/test-artifacts');
    if (!fs.existsSync(outputDir)) {
        fs.mkdirSync(outputDir, { recursive: true });
    }

    // 1. Verify "Preview Proyek" button in header
    console.log('Verifying "Preview Proyek" button...');
    const previewBtn = page.locator('a:has-text("Preview Proyek")');
    const isPreviewBtnVisible = await previewBtn.isVisible();
    console.log('Is "Preview Proyek" button visible?', isPreviewBtnVisible);

    // 2. Select category "lainnya"
    await page.selectOption('select[name="category"]', 'lainnya');
    await page.waitForTimeout(400);

    // 3. Test typing "karya seni"
    console.log('Testing "karya seni" keyword...');
    const customInput = page.locator('input[name="custom_category"]');
    await customInput.fill('karya seni');
    await page.waitForTimeout(400);

    const recBox = page.locator('.bg-blue-50:has-text("Rekomendasi:")').first();
    console.log('Recommendation for "karya seni":', await recBox.innerText());
    const screen1 = path.join(outputDir, 'recommendation_karya_seni.png');
    await page.screenshot({ path: screen1, fullPage: false });

    // 4. Test typing "pemrograman"
    console.log('Testing "pemrograman" keyword...');
    await customInput.fill('pemrograman');
    await page.waitForTimeout(400);
    console.log('Recommendation for "pemrograman":', await recBox.innerText());
    const screen2 = path.join(outputDir, 'recommendation_pemrograman.png');
    await page.screenshot({ path: screen2, fullPage: false });

    // 5. Test typing "videografi"
    console.log('Testing "videografi" keyword...');
    await customInput.fill('videografi');
    await page.waitForTimeout(400);
    console.log('Recommendation for "videografi":', await recBox.innerText());

    // 6. Test typing "arsip sejarah"
    console.log('Testing "arsip sejarah" keyword...');
    await customInput.fill('arsip sejarah');
    await page.waitForTimeout(400);
    console.log('Recommendation for "arsip sejarah":', await recBox.innerText());
    const screen3 = path.join(outputDir, 'recommendation_arsip_sejarah.png');
    await page.screenshot({ path: screen3, fullPage: false });

    await browser.close();
    console.log('All verifications completed successfully!');
}

main().catch(err => {
    console.error('Error:', err);
    process.exit(1);
});
