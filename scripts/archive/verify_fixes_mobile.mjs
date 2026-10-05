import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';

const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const artifactDir = 'C:\\Users\\TK ABA SBY 69 (3)\\.gemini\\antigravity-ide\\brain\\1d047126-45bc-4d82-b4de-39626a6fe824';

const sleep = (ms) => new Promise(r => setTimeout(r, ms));

async function recheckRole(browser, email, password, roleName) {
    const context = await browser.createBrowserContext();
    const page = await context.newPage();

    await page.setViewport({ width: 1920, height: 1080 });
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle2' });
    
    await page.waitForSelector('input[name="email"]');
    await page.type('input[name="email"]', email);
    await page.type('input[name="password"]', password);
    await Promise.all([
        page.click('button[type="submit"]'),
        page.waitForNavigation({ waitUntil: 'networkidle2' })
    ]);

    // Set to Mobile Viewport 375x812
    await page.setViewport({ width: 375, height: 812, isMobile: true, hasTouch: true });
    await sleep(800);
    const mobShot = path.join(artifactDir, `dashboard_${roleName}_mobile_fixed.png`);
    await page.screenshot({ path: mobShot, fullPage: true });

    const audit = await page.evaluate(() => {
        const docWidth = document.documentElement.clientWidth;
        const scrollWidth = document.documentElement.scrollWidth;
        const bodyScrollWidth = document.body.scrollWidth;

        const overflowing = [];
        document.querySelectorAll('*').forEach(el => {
            const r = el.getBoundingClientRect();
            if (r.right > docWidth + 3) {
                const comp = window.getComputedStyle(el);
                if (comp.display !== 'none' && comp.visibility !== 'hidden' && comp.position !== 'fixed') {
                    overflowing.push({
                        tag: el.tagName,
                        className: el.className ? String(el.className).slice(0, 50) : '',
                        right: Math.round(r.right),
                        width: Math.round(r.width)
                    });
                }
            }
        });

        return {
            docWidth,
            scrollWidth,
            bodyScrollWidth,
            hasHorizontalScroll: scrollWidth > docWidth,
            overflowCount: overflowing.length,
            overflowSample: overflowing.slice(0, 3)
        };
    });

    console.log(`[${roleName}] Post-fix audit:`, JSON.stringify(audit));
    await context.close();
}

async function main() {
    const browser = await puppeteer.launch({
        executablePath: chromePath,
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-gpu']
    });

    try {
        await recheckRole(browser, 'mentor.kominfo@surabaya.go.id', 'password', 'mentor');
        await recheckRole(browser, 'dosen.unesa@unesa.ac.id', 'password', 'dosen');
        await recheckRole(browser, 'admin@gmail.com', 'admin123', 'admin');
    } finally {
        await browser.close();
    }
}

main().catch(err => {
    console.error(err);
    process.exit(1);
});
