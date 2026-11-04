import { chromium } from 'playwright';

async function run() {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    
    // Mobile Viewport: iPhone 16 (393x852)
    const pageMobile = await browser.newPage({ viewport: { width: 393, height: 852 } });
    await pageMobile.goto('http://127.0.0.1:8000/');
    await pageMobile.waitForLoadState('networkidle');
    await pageMobile.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
    await pageMobile.waitForTimeout(400);
    await pageMobile.screenshot({ path: 'public/test-artifacts/welcome_footer_mobile_after.png', fullPage: false });

    // Narrow Mobile Viewport: 360x740 (Small Android)
    const pageNarrow = await browser.newPage({ viewport: { width: 360, height: 740 } });
    await pageNarrow.goto('http://127.0.0.1:8000/');
    await pageNarrow.waitForLoadState('networkidle');
    await pageNarrow.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
    await pageNarrow.waitForTimeout(400);
    await pageNarrow.screenshot({ path: 'public/test-artifacts/welcome_footer_mobile_360.png', fullPage: false });

    await browser.close();
    console.log('Saved screenshots!');
}

run().catch(err => {
    console.error(err);
    process.exit(1);
});
