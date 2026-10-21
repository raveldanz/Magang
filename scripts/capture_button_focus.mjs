import { chromium } from 'playwright';
import path from 'path';

(async () => {
    const browser = await chromium.launch({
        channel: 'chrome',
        headless: true
    });
    const context = await browser.newContext({
        viewport: { width: 1440, height: 950 },
        deviceScaleFactor: 1
    });
    const page = await context.newPage();

    await page.goto('http://127.0.0.1:8000/login');
    await page.waitForLoadState('networkidle');
    await page.fill('#email', 'eka.aktif@unesa.ac.id');
    await page.fill('#password', 'password');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    await page.goto('http://127.0.0.1:8000/student/final-report');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(600);

    // Scroll slightly so the 3 cards and the toggle button are clearly in view
    await page.evaluate(() => {
        window.scrollBy(0, 320);
    });
    await page.waitForTimeout(400);

    const outPath = path.resolve('public/test-artifacts/student_final_report_button_focus.png');
    await page.screenshot({
        path: outPath,
        fullPage: false
    });

    await browser.close();
    process.exit(0);
})();
