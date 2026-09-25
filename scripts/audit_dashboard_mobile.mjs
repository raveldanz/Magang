import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';

const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const artifactDir = 'C:\\Users\\TK ABA SBY 69 (3)\\.gemini\\antigravity-ide\\brain\\1d047126-45bc-4d82-b4de-39626a6fe824';

const sleep = (ms) => new Promise(r => setTimeout(r, ms));

async function auditRole(browser, email, password, roleName) {
    console.log(`\n======================================================`);
    console.log(`  Auditing Role: ${roleName.toUpperCase()} (${email})`);
    console.log(`======================================================`);
    
    // Create isolated incognito context so no session interference occurs
    const context = await browser.createBrowserContext();
    const page = await context.newPage();

    // 1. Login
    await page.setViewport({ width: 1920, height: 1080 });
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle2' });
    
    await page.waitForSelector('input[name="email"]');
    await page.type('input[name="email"]', email);
    await page.type('input[name="password"]', password);
    await Promise.all([
        page.click('button[type="submit"]'),
        page.waitForNavigation({ waitUntil: 'networkidle2' })
    ]);

    const currentUrl = page.url();
    console.log(`[Logged in URL]: ${currentUrl}`);
    await sleep(1200);

    // 2. Desktop Screenshot (1920x1080)
    await page.setViewport({ width: 1920, height: 1080 });
    await sleep(800);
    const desktopScreenshot = path.join(artifactDir, `dashboard_${roleName}_desktop_1920x1080.png`);
    await page.screenshot({ path: desktopScreenshot, fullPage: true });
    console.log(`[Screenshot Desktop Saved]: ${desktopScreenshot}`);

    // Desktop Audit
    const desktopAudit = await page.evaluate(() => {
        const docWidth = document.documentElement.clientWidth;
        const scrollWidth = document.documentElement.scrollWidth;
        const tables = Array.from(document.querySelectorAll('table')).map((t, idx) => {
            const rect = t.getBoundingClientRect();
            const parent = t.parentElement;
            const parentComp = window.getComputedStyle(parent);
            return {
                index: idx,
                width: Math.round(rect.width),
                parentWidth: Math.round(parent.getBoundingClientRect().width),
                parentOverflowX: parentComp.overflowX,
                isWrappedInScroll: parentComp.overflowX === 'auto' || parentComp.overflowX === 'scroll'
            };
        });

        return {
            docWidth,
            scrollWidth,
            hasHorizontalScroll: scrollWidth > docWidth,
            tablesCount: tables.length,
            tables
        };
    });

    // 3. Switch to Mobile Viewport (375x812)
    console.log(`[Switching to Mobile Viewport 375x812]`);
    await page.setViewport({ width: 375, height: 812, isMobile: true, hasTouch: true });
    await sleep(1000);

    const mobileScreenshot = path.join(artifactDir, `dashboard_${roleName}_mobile_375x812.png`);
    await page.screenshot({ path: mobileScreenshot, fullPage: true });
    console.log(`[Screenshot Mobile Saved]: ${mobileScreenshot}`);

    // Mobile Audit
    const mobileAudit = await page.evaluate(() => {
        const docWidth = document.documentElement.clientWidth;
        const scrollWidth = document.documentElement.scrollWidth;

        // Find elements that overflow 375px
        const allElements = document.querySelectorAll('*');
        const overflowing = [];
        allElements.forEach(el => {
            const r = el.getBoundingClientRect();
            if (r.right > docWidth + 3) {
                const comp = window.getComputedStyle(el);
                if (comp.display !== 'none' && comp.visibility !== 'hidden' && comp.position !== 'fixed') {
                    overflowing.push({
                        tag: el.tagName,
                        id: el.id,
                        className: el.className ? String(el.className).slice(0, 70) : '',
                        rectRight: Math.round(r.right),
                        rectWidth: Math.round(r.width),
                        docWidth
                    });
                }
            }
        });

        // Audit tables on mobile
        const tables = Array.from(document.querySelectorAll('table')).map((t, idx) => {
            const rect = t.getBoundingClientRect();
            const parent = t.parentElement;
            const parentComp = window.getComputedStyle(parent);
            const grandParent = parent.parentElement;
            const grandParentComp = grandParent ? window.getComputedStyle(grandParent) : null;
            const hasProperScroll = parentComp.overflowX === 'auto' || parentComp.overflowX === 'scroll' || 
                                   (grandParentComp && (grandParentComp.overflowX === 'auto' || grandParentComp.overflowX === 'scroll'));
            return {
                index: idx,
                tableWidth: Math.round(rect.width),
                tableRight: Math.round(rect.right),
                parentWidth: Math.round(parent.getBoundingClientRect().width),
                parentOverflowX: parentComp.overflowX,
                hasProperScrollWrapper: hasProperScroll,
                isClippingOrOverflowing: rect.right > docWidth + 2 && !hasProperScroll
            };
        });

        // Check cards
        const cards = Array.from(document.querySelectorAll('.grid > div, [class*="card"], .rounded-2xl, .rounded-3xl')).map(c => {
            const r = c.getBoundingClientRect();
            return {
                className: c.className ? String(c.className).slice(0, 50) : '',
                width: Math.round(r.width),
                left: Math.round(r.left),
                right: Math.round(r.right),
                isOverflowing: r.right > docWidth + 2
            };
        }).filter(c => c.isOverflowing);

        return {
            docWidth,
            scrollWidth,
            hasHorizontalScroll: scrollWidth > docWidth,
            overflowElementsCount: overflowing.length,
            overflowSample: overflowing.slice(0, 6),
            tablesCount: tables.length,
            tables,
            overflowingCards: cards
        };
    });

    // 4. Test Mobile Hamburger Menu (Toggle Navbar)
    const hamburgerBtn = await page.$('button[aria-label="Buka Menu"]');
    let navOpenScreenshot = null;
    if (hamburgerBtn) {
        console.log(`[Mobile] Clicking hamburger menu toggle...`);
        await hamburgerBtn.click();
        await sleep(600);
        navOpenScreenshot = path.join(artifactDir, `dashboard_${roleName}_mobile_menu_open_375x812.png`);
        await page.screenshot({ path: navOpenScreenshot, fullPage: false });
        console.log(`[Screenshot Mobile Menu Open Saved]: ${navOpenScreenshot}`);

        await hamburgerBtn.click();
        await sleep(300);
    }

    await context.close();

    return {
        role: roleName,
        url: currentUrl,
        desktop: {
            screenshot: desktopScreenshot,
            audit: desktopAudit
        },
        mobile: {
            screenshot: mobileScreenshot,
            menuScreenshot: navOpenScreenshot,
            audit: mobileAudit
        }
    };
}

async function main() {
    console.log('[Audit] Launching Chrome headless...');
    const browser = await puppeteer.launch({
        executablePath: chromePath,
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-gpu']
    });

    try {
        const studentReport = await auditRole(
            browser,
            'mahasiswa.aktif@unesa.ac.id',
            'password',
            'mahasiswa'
        );

        const adminReport = await auditRole(
            browser,
            'admin@gmail.com',
            'admin123',
            'admin'
        );

        const fullReport = {
            timestamp: new Date().toISOString(),
            student: studentReport,
            admin: adminReport
        };

        const outPath = path.join(artifactDir, 'dashboard_mobile_audit_report.json');
        fs.writeFileSync(outPath, JSON.stringify(fullReport, null, 2));
        console.log(`\nReport successfully written to ${outPath}`);
    } finally {
        await browser.close();
    }
}

main().catch(err => {
    console.error(err);
    process.exit(1);
});
