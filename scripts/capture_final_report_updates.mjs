import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';

(async () => {
    const browser = await chromium.launch({
        channel: 'chrome',
        headless: true
    });
    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 },
        deviceScaleFactor: 1
    });
    const page = await context.newPage();

    console.log('1. Logging in as Eka Putri Lestari...');
    await page.goto('http://127.0.0.1:8000/login');
    await page.waitForLoadState('networkidle');
    await page.fill('#email', 'eka.aktif@unesa.ac.id');
    await page.fill('#password', 'password');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    console.log('2. Navigating to /student/final-report...');
    await page.goto('http://127.0.0.1:8000/student/final-report');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);

    // Ensure output directory exists
    const outDir = path.resolve('public/test-artifacts');
    if (!fs.existsSync(outDir)) {
        fs.mkdirSync(outDir, { recursive: true });
    }

    console.log('3. Capturing initial 3-card preview & updated guide info...');
    await page.screenshot({
        path: path.join(outDir, 'student_final_report_3_cards_toggle.png'),
        fullPage: false
    });

    console.log('4. Clicking "Tampilkan Semua" button...');
    const toggleBtn = page.locator('button:has-text("Tampilkan Semua")');
    if (await toggleBtn.count() > 0) {
        await toggleBtn.click();
        await page.waitForTimeout(600);

        console.log('5. Capturing expanded view of all 10 cards...');
        await page.screenshot({
            path: path.join(outDir, 'student_final_report_all_expanded.png'),
            fullPage: false
        });
    }

    console.log('6. Navigating to /student/projects/create to verify 10 documents limit hint...');
    await page.goto('http://127.0.0.1:8000/student/projects/create');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(500);

    // Scroll down to Bagian 5
    await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
    await page.waitForTimeout(500);

    await page.screenshot({
        path: path.join(outDir, 'student_project_create_10_docs.png'),
        fullPage: false
    });

    console.log('All screenshots captured successfully!');
    await browser.close();
    process.exit(0);
})();
