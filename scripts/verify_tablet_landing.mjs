import { chromium } from 'playwright';

async function run() {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    
    // 1. Tablet Viewport: iPad Mini (768x1024)
    console.log('Capturing iPad Mini (768x1024)...');
    const pageTablet = await browser.newPage({ viewport: { width: 768, height: 1024 } });
    await pageTablet.goto('http://127.0.0.1:8000/');
    await pageTablet.waitForLoadState('networkidle');
    await pageTablet.screenshot({ path: 'public/test-artifacts/welcome_tablet_768_after.png', fullPage: true });

    // 2. Tablet Viewport: iPad Air (820x1180)
    console.log('Capturing iPad Air (820x1180)...');
    const pageTabletAir = await browser.newPage({ viewport: { width: 820, height: 1180 } });
    await pageTabletAir.goto('http://127.0.0.1:8000/');
    await pageTabletAir.waitForLoadState('networkidle');
    await pageTabletAir.screenshot({ path: 'public/test-artifacts/welcome_tablet_820_after.png', fullPage: true });

    // 3. Mobile Viewport: iPhone 16 (393x852)
    console.log('Capturing Mobile (393x852)...');
    const pageMobile = await browser.newPage({ viewport: { width: 393, height: 852 } });
    await pageMobile.goto('http://127.0.0.1:8000/');
    await pageMobile.waitForLoadState('networkidle');
    await pageMobile.screenshot({ path: 'public/test-artifacts/welcome_mobile_393_after.png', fullPage: true });

    // 4. Desktop Viewport: 1440x900
    console.log('Capturing Desktop (1440x900)...');
    const pageDesktop = await browser.newPage({ viewport: { width: 1440, height: 900 } });
    await pageDesktop.goto('http://127.0.0.1:8000/');
    await pageDesktop.waitForLoadState('networkidle');
    await pageDesktop.screenshot({ path: 'public/test-artifacts/welcome_desktop_1440_after.png', fullPage: true });

    await browser.close();
    console.log('All 4 tier screenshots saved!');
}

run().catch(err => {
    console.error(err);
    process.exit(1);
});
