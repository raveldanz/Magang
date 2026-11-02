import { chromium } from 'playwright';

async function run() {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
    await page.goto('http://127.0.0.1:8000/');
    await page.waitForLoadState('networkidle');

    const result = await page.evaluate(() => {
        const el = document.querySelector('.lg\\:col-span-5 .grid');
        if (!el) return 'Element not found';
        const style = window.getComputedStyle(el);
        return {
            className: el.className,
            gridTemplateColumns: style.gridTemplateColumns,
            display: style.display,
        };
    });

    console.log('Result at 1440px:', result);
    await browser.close();
}

run();

