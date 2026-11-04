import { chromium } from 'playwright';

async function run() {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
    await page.goto('http://127.0.0.1:8000/');
    await page.waitForLoadState('networkidle');

    const result = await page.evaluate(() => {
        const el = document.querySelector('.lg\\:col-span-5 .grid');
        const matched = [];
        
        function scanRules(rules) {
            for (const r of rules) {
                if (r.cssRules) {
                    scanRules(r.cssRules);
                } else if (r.selectorText && el.matches(r.selectorText)) {
                    matched.push({
                        selector: r.selectorText,
                        cssText: r.cssText
                    });
                }
            }
        }

        for (const sheet of document.styleSheets) {
            try {
                scanRules(sheet.cssRules);
            } catch (e) {}
        }
        return matched;
    });

    console.log(result);
    await browser.close();
}

run();

