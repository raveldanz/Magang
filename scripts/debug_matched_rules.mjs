import { chromium } from 'playwright';

async function run() {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
    await page.goto('http://127.0.0.1:8000/');
    await page.waitForLoadState('networkidle');

    const rules = await page.evaluate(() => {
        const el = document.querySelector('.lg\\:col-span-5 .grid');
        const matched = [];
        for (const sheet of document.styleSheets) {
            try {
                for (const rule of sheet.cssRules) {
                    if (rule.selectorText && el.matches(rule.selectorText)) {
                        matched.push({ selector: rule.selectorText, cssText: rule.cssText, media: rule.parentRule ? rule.parentRule.conditionText : null });
                    }
                }
            } catch (e) {}
        }
        return matched;
    });

    console.log('Matched rules:', JSON.stringify(rules, null, 2));
    await browser.close();
}

run();

