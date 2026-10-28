import { chromium } from 'playwright';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');

async function testVisual() {
    console.log('🚀 Memulai verifikasi visual pembatas transisi tahun nomor 2...');
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const page = await browser.newPage({ viewport: { width: 1440, height: 1400 } });
    
    await page.goto('http://127.0.0.1:8000/proyek-dinas', { waitUntil: 'networkidle' });
    await page.waitForTimeout(1000);
    
    // Scroll to the transition between Year 2024 and Year 2025
    await page.evaluate(() => {
        window.scrollTo(0, 1450);
    });
    await page.waitForTimeout(600);
    
    const screenshotPath = path.resolve(projectRoot, 'public/test-artifacts/pembatas_tahun_nomor_2_dinas.png');
    await page.screenshot({ path: screenshotPath });
    console.log('✅ Screenshot tersimpan di:', screenshotPath);
    
    await browser.close();
}

testVisual();
