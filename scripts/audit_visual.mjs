import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';

const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const artifactDir = 'C:\\Users\\TK ABA SBY 69 (3)\\.gemini\\antigravity-ide\\brain\\1d047126-45bc-4d82-b4de-39626a6fe824';

if (!fs.existsSync(artifactDir)) {
    fs.mkdirSync(artifactDir, { recursive: true });
}

// WCAG Contrast calculation helper
function getLuminance(r, g, b) {
    const a = [r, g, b].map(v => {
        v /= 255;
        return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
    });
    return a[0] * 0.2126 + a[1] * 0.7152 + a[2] * 0.0722;
}

function parseRgb(colorStr) {
    const match = colorStr.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
    if (match) {
        return [parseInt(match[1]), parseInt(match[2]), parseInt(match[3])];
    }
    return [0, 0, 0];
}

function getContrastRatio(rgb1, rgb2) {
    const lum1 = getLuminance(rgb1[0], rgb1[1], rgb1[2]);
    const lum2 = getLuminance(rgb2[0], rgb2[1], rgb2[2]);
    const brightest = Math.max(lum1, lum2);
    const darkest = Math.min(lum1, lum2);
    return ((brightest + 0.05) / (darkest + 0.05)).toFixed(2);
}

async function auditPage(browser, url, pageName) {
    const results = { page: pageName, url, desktop: {}, mobile: {} };

    // --- 1. DESKTOP VIEWPORT (1920x1080) ---
    {
        const page = await browser.newPage();
        await page.setViewport({ width: 1920, height: 1080, deviceScaleFactor: 1 });
        await page.goto(url, { waitUntil: 'networkidle2' });
        await new Promise(r => setTimeout(r, 800));

        const screenshotPath = path.join(artifactDir, `${pageName}_desktop_1920x1080.png`);
        await page.screenshot({ path: screenshotPath, fullPage: true });
        results.desktop.screenshot = screenshotPath;

        // Visual DOM Audit
        const auditData = await page.evaluate(() => {
            const docWidth = document.documentElement.clientWidth;
            const scrollWidth = document.documentElement.scrollWidth;
            const hasHorizontalScroll = scrollWidth > docWidth;

            // Check card / form container centering
            const card = document.querySelector('.bg-white, form, main > div');
            let cardInfo = null;
            if (card) {
                const rect = card.getBoundingClientRect();
                const centerOffset = Math.abs((rect.left + rect.width / 2) - (docWidth / 2));
                cardInfo = {
                    tagName: card.tagName,
                    className: card.className,
                    width: rect.width,
                    height: rect.height,
                    left: rect.left,
                    top: rect.top,
                    centerX: rect.left + rect.width / 2,
                    docCenterX: docWidth / 2,
                    centerOffsetPx: Math.round(centerOffset),
                    isHorizontallyCentered: centerOffset < 15
                };
            }

            // Check inputs and buttons
            const elements = [];
            const interactiveEls = document.querySelectorAll('input:not([type="hidden"]), button, a.bg-blue-600, a.border');
            interactiveEls.forEach(el => {
                const rect = el.getBoundingClientRect();
                const comp = window.getComputedStyle(el);
                elements.push({
                    tag: el.tagName,
                    type: el.getAttribute('type') || el.innerText.trim().slice(0, 30),
                    text: el.innerText ? el.innerText.trim().replace(/\s+/g, ' ').slice(0, 40) : (el.placeholder || el.name),
                    color: comp.color,
                    backgroundColor: comp.backgroundColor,
                    fontSize: comp.fontSize,
                    fontWeight: comp.fontWeight,
                    width: Math.round(rect.width),
                    height: Math.round(rect.height),
                    left: Math.round(rect.left),
                    top: Math.round(rect.top),
                    isOverflowingX: rect.right > docWidth,
                    isOverflowingY: rect.bottom > document.documentElement.scrollHeight
                });
            });

            // Check any overflowing elements in DOM
            const allElements = document.querySelectorAll('*');
            const overflowingElements = [];
            allElements.forEach(el => {
                const r = el.getBoundingClientRect();
                if (r.right > docWidth + 2) {
                    overflowingElements.push({
                        tag: el.tagName,
                        class: el.className ? String(el.className).slice(0, 50) : '',
                        right: r.right,
                        docWidth
                    });
                }
            });

            return {
                docWidth,
                scrollWidth,
                hasHorizontalScroll,
                cardInfo,
                elements,
                overflowingElementsCount: overflowingElements.length,
                overflowingSample: overflowingElements.slice(0, 3)
            };
        });

        // Compute contrast ratios for elements
        auditData.elements = auditData.elements.map(el => {
            const fg = parseRgb(el.color);
            const bg = parseRgb(el.backgroundColor);
            return {
                ...el,
                contrastRatio: getContrastRatio(fg, bg)
            };
        });

        results.desktop.audit = auditData;
        await page.close();
    }

    // --- 2. MOBILE VIEWPORT (375x812) ---
    {
        const page = await browser.newPage();
        await page.setViewport({ width: 375, height: 812, deviceScaleFactor: 2, isMobile: true, hasTouch: true });
        await page.goto(url, { waitUntil: 'networkidle2' });
        await new Promise(r => setTimeout(r, 800));

        const screenshotPath = path.join(artifactDir, `${pageName}_mobile_375x812.png`);
        await page.screenshot({ path: screenshotPath, fullPage: true });
        results.mobile.screenshot = screenshotPath;

        // Visual DOM Audit
        const auditData = await page.evaluate(() => {
            const docWidth = document.documentElement.clientWidth;
            const scrollWidth = document.documentElement.scrollWidth;
            const hasHorizontalScroll = scrollWidth > docWidth;

            // Check card / form container centering
            const card = document.querySelector('.bg-white, form, main > div');
            let cardInfo = null;
            if (card) {
                const rect = card.getBoundingClientRect();
                const centerOffset = Math.abs((rect.left + rect.width / 2) - (docWidth / 2));
                cardInfo = {
                    tagName: card.tagName,
                    className: card.className,
                    width: rect.width,
                    height: rect.height,
                    left: rect.left,
                    top: rect.top,
                    centerX: rect.left + rect.width / 2,
                    docCenterX: docWidth / 2,
                    centerOffsetPx: Math.round(centerOffset),
                    isHorizontallyCentered: centerOffset < 15
                };
            }

            // Check inputs and buttons
            const elements = [];
            const interactiveEls = document.querySelectorAll('input:not([type="hidden"]), button, a.bg-blue-600, a.border');
            interactiveEls.forEach(el => {
                const rect = el.getBoundingClientRect();
                const comp = window.getComputedStyle(el);
                elements.push({
                    tag: el.tagName,
                    type: el.getAttribute('type') || el.innerText.trim().slice(0, 30),
                    text: el.innerText ? el.innerText.trim().replace(/\s+/g, ' ').slice(0, 40) : (el.placeholder || el.name),
                    color: comp.color,
                    backgroundColor: comp.backgroundColor,
                    fontSize: comp.fontSize,
                    fontWeight: comp.fontWeight,
                    width: Math.round(rect.width),
                    height: Math.round(rect.height),
                    left: Math.round(rect.left),
                    top: Math.round(rect.top),
                    isOverflowingX: rect.right > docWidth,
                    isOverflowingY: rect.bottom > document.documentElement.scrollHeight
                });
            });

            // Check any overflowing elements in DOM
            const allElements = document.querySelectorAll('*');
            const overflowingElements = [];
            allElements.forEach(el => {
                const r = el.getBoundingClientRect();
                if (r.right > docWidth + 2) {
                    overflowingElements.push({
                        tag: el.tagName,
                        class: el.className ? String(el.className).slice(0, 50) : '',
                        right: r.right,
                        docWidth
                    });
                }
            });

            return {
                docWidth,
                scrollWidth,
                hasHorizontalScroll,
                cardInfo,
                elements,
                overflowingElementsCount: overflowingElements.length,
                overflowingSample: overflowingElements.slice(0, 3)
            };
        });

        // Compute contrast ratios for elements
        auditData.elements = auditData.elements.map(el => {
            const fg = parseRgb(el.color);
            const bg = parseRgb(el.backgroundColor);
            return {
                ...el,
                contrastRatio: getContrastRatio(fg, bg)
            };
        });

        results.mobile.audit = auditData;
        await page.close();
    }

    return results;
}

async function main() {
    console.log('[Audit] Launching Chrome headless...');
    const browser = await puppeteer.launch({
        executablePath: chromePath,
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-gpu']
    });

    try {
        console.log('[Audit] Auditing http://127.0.0.1:8000/login ...');
        const loginResults = await auditPage(browser, 'http://127.0.0.1:8000/login', 'login');

        console.log('[Audit] Auditing http://127.0.0.1:8000/ ...');
        const homeResults = await auditPage(browser, 'http://127.0.0.1:8000/', 'home');

        const report = {
            timestamp: new Date().toISOString(),
            login: loginResults,
            home: homeResults
        };

        const reportPath = path.join(artifactDir, 'visual_audit_report.json');
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        console.log(`[Audit] Completed successfully! Report saved to ${reportPath}`);
        console.log(JSON.stringify(report, null, 2));
    } finally {
        await browser.close();
    }
}

main().catch(err => {
    console.error('[Audit Error]', err);
    process.exit(1);
});
