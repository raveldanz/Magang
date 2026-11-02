import { chromium } from 'playwright';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');

async function testMobileAndTablet() {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    
    // Mobile
    const pageMobile = await browser.newPage({ viewport: { width: 375, height: 900 } });
    await pageMobile.goto('http://127.0.0.1:8000/proyek-dinas', { waitUntil: 'networkidle' });
    await pageMobile.waitForTimeout(600);
    
    const divider = pageMobile.locator('svg[viewBox="-130 -18 260 36"]').first();
    if (await divider.isVisible()) {
        await divider.scrollIntoViewIfNeeded();
        await pageMobile.waitForTimeout(400);
    }
    
    await pageMobile.screenshot({ path: path.resolve(projectRoot, 'public/test-artifacts/pembatas_mobile_hd.png') });
    await pageMobile.close();

    console.log('Mobile HD screenshot saved.');
    await browser.close();
}

testMobileAndTablet();
