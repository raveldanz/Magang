import { chromium } from 'playwright';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');

async function testWaveDivider() {
    console.log('🚀 Memulai pengujian visual komponen wave divider yang diperbaiki...');
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    
    // 1. Desktop Test: Context in Catalog (1440x900)
    const pageDesktop = await browser.newPage({ viewport: { width: 1440, height: 900 } });
    await pageDesktop.goto('http://127.0.0.1:8000/proyek-dinas', { waitUntil: 'networkidle' });
    await pageDesktop.waitForTimeout(800);
    
    const dividerSelector = '.group[style*="--wave-c1"]';
    await pageDesktop.waitForSelector(dividerSelector);
    
    const dividerEl = await pageDesktop.$(dividerSelector);
    await dividerEl.scrollIntoViewIfNeeded();
    await pageDesktop.waitForTimeout(400);

    // Tangkap tangkapan layar Normal Desktop (Divider Closeup)
    const desktopScreenshot = path.resolve(projectRoot, 'public/test-artifacts/wave_divider_desktop_normal.png');
    await dividerEl.screenshot({ path: desktopScreenshot });
    console.log('✅ Screenshot Normal Desktop:', desktopScreenshot);

    // Tangkap tangkapan layar Context di Halaman (Melihat Keseimbangan Ruang Kosong / Space)
    await pageDesktop.evaluate(() => {
        window.scrollBy(0, -180);
    });
    await pageDesktop.waitForTimeout(300);
    const contextScreenshot = path.resolve(projectRoot, 'public/test-artifacts/wave_divider_page_context.png');
    await pageDesktop.screenshot({ path: contextScreenshot });
    console.log('✅ Screenshot Page Context:', contextScreenshot);

    // Hover ke ornamen tengah untuk menguji efek micro-interaction halus (spring + floating)
    await dividerEl.scrollIntoViewIfNeeded();
    await pageDesktop.waitForTimeout(200);
    const centerDiamond = await dividerEl.$('.relative.w-6, .relative.w-7');
    if (centerDiamond) {
        await centerDiamond.hover();
    } else {
        await dividerEl.hover();
    }
    await pageDesktop.waitForTimeout(600);

    const hoverScreenshot = path.resolve(projectRoot, 'public/test-artifacts/wave_divider_desktop_hover.png');
    await dividerEl.screenshot({ path: hoverScreenshot });
    console.log('✅ Screenshot Hover Desktop:', hoverScreenshot);

    // 2. Mobile Test (390x844)
    const pageMobile = await browser.newPage({ viewport: { width: 390, height: 844 } });
    await pageMobile.goto('http://127.0.0.1:8000/proyek-dinas', { waitUntil: 'networkidle' });
    await pageMobile.waitForTimeout(800);

    const dividerMobile = await pageMobile.$(dividerSelector);
    await dividerMobile.scrollIntoViewIfNeeded();
    await pageMobile.waitForTimeout(400);

    const mobileScreenshot = path.resolve(projectRoot, 'public/test-artifacts/wave_divider_mobile.png');
    await dividerMobile.screenshot({ path: mobileScreenshot });
    console.log('✅ Screenshot Mobile 390px:', mobileScreenshot);

    // 3. Dark Mode Test dengan Background Gelap
    await pageDesktop.evaluate(() => {
        document.documentElement.classList.add('dark');
        document.body.style.backgroundColor = '#0b1329';
        const parent = document.querySelector('.group[style*="--wave-c1"]').parentElement;
        if (parent) {
            parent.style.backgroundColor = '#0b1329';
            parent.style.padding = '20px';
            parent.style.borderRadius = '16px';
        }
    });
    await pageDesktop.waitForTimeout(400);

    const darkScreenshot = path.resolve(projectRoot, 'public/test-artifacts/wave_divider_dark_mode.png');
    const parentContainer = await pageDesktop.$('.group[style*="--wave-c1"]');
    await parentContainer.screenshot({ path: darkScreenshot });
    console.log('✅ Screenshot Dark Mode:', darkScreenshot);

    await browser.close();
    console.log('🎉 Seluruh pengujian visual Playwright selesai dengan sukses!');
}

testWaveDivider().catch(err => {
    console.error('❌ Error visual testing:', err);
    process.exit(1);
});
