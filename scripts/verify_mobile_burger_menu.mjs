import { chromium } from 'playwright';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

async function verifyBurgerMenu() {
    console.log('🚀 Memulai verifikasi responsif mobile burger menu...');
    const browser = await chromium.launch({
        channel: 'chrome',
        headless: true
    });

    try {
        // 1. Pengujian Mobile Viewport (375x812) - State Tertutup
        const mobileContext = await browser.newContext({
            viewport: { width: 375, height: 812 },
            userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.5 Mobile/15E148 Safari/604.1'
        });
        const mobilePage = await mobileContext.newPage();
        await mobilePage.goto('http://127.0.0.1:8000', { waitUntil: 'networkidle' });
        await mobilePage.waitForTimeout(1000);

        // Screenshot Mobile Closed
        const closedPath = path.resolve(__dirname, '../public/test-artifacts/welcome_mobile_burger_closed.png');
        await mobilePage.screenshot({ path: closedPath, fullPage: false });
        console.log(`✅ Tangkapan layar mobile (tertutup) disimpan ke: ${closedPath}`);

        // 2. Klik tombol hamburger untuk membuka mobile menu
        const burgerBtn = mobilePage.locator('button[aria-label="Toggle navigasi menu"]');
        await burgerBtn.click();
        await mobilePage.waitForTimeout(600); // Tunggu animasi x-transition selesai

        // Screenshot Mobile Opened
        const openedPath = path.resolve(__dirname, '../public/test-artifacts/welcome_mobile_burger_opened.png');
        await mobilePage.screenshot({ path: openedPath, fullPage: false });
        console.log(`✅ Tangkapan layar mobile (terbuka) disimpan ke: ${openedPath}`);

        await mobileContext.close();

        // 3. Pengujian Desktop Viewport (1280x800) - Memastikan tidak terganggu
        const desktopContext = await browser.newContext({
            viewport: { width: 1280, height: 800 }
        });
        const desktopPage = await desktopContext.newPage();
        await desktopPage.goto('http://127.0.0.1:8000', { waitUntil: 'networkidle' });
        await desktopPage.waitForTimeout(800);

        const desktopPath = path.resolve(__dirname, '../public/test-artifacts/welcome_desktop_nav_intact.png');
        await desktopPage.screenshot({ path: desktopPath, fullPage: false });
        console.log(`✅ Tangkapan layar desktop disimpan ke: ${desktopPath}`);

        await desktopContext.close();

        console.log('🎉 Seluruh pengujian mobile burger menu selesai dengan sukses!');
    } catch (err) {
        console.error('❌ Gagal dalam pengujian visual:', err);
        process.exit(1);
    } finally {
        await browser.close();
    }
}

verifyBurgerMenu();
