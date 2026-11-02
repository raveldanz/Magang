import { chromium } from 'playwright';

async function run() {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const page = await browser.newPage({ viewport: { width: 768, height: 1024 } });
    await page.goto('http://127.0.0.1:8000/');
    await page.waitForLoadState('networkidle');

    const result = await page.evaluate(() => {
        const c1 = document.querySelector('.space-y-2\\.5');
        const c2 = document.querySelector('.hidden.md\\:grid');
        return {
            c1_display: c1 ? window.getComputedStyle(c1).display : 'null',
            c2_display: c2 ? window.getComputedStyle(c2).display : 'null',
        };
    });

    console.log('Display at 768px:', result);
    await browser.close();
}

run();
