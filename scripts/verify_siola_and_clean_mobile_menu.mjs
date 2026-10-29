import { chromium } from 'playwright';

async function verify() {
    console.log('--- Verifying Siola Address & Clean Mobile Menu ---');
    const browser = await chromium.launch({
        headless: true,
        channel: 'chrome',
    });

    try {
        // 1. Mobile Menu Verification (Viewport 400x700 like user screenshot)
        const mobileContext = await browser.newContext({ viewport: { width: 400, height: 700 } });
        const mobilePage = await mobileContext.newPage();
        await mobilePage.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle' });

        // Click the hamburger menu button
        const hamburgerBtn = mobilePage.locator('button[aria-label="Toggle navigasi menu"]');
        await hamburgerBtn.click();
        await mobilePage.waitForTimeout(500);

        // Check if address is present in mobile menu
        const mobileMenu = mobilePage.locator('header div[x-show="mobileMenuOpen"]');
        const menuAddressCheck = await mobileMenu.getByText('Siola').count();
        console.log('Mobile Menu Siola text count (should be 0):', menuAddressCheck);
        if (menuAddressCheck === 0) {
            console.log('✅ PASS: Alamat berhasil dihapus dari mobile menu dropdown!');
        } else {
            console.error('❌ FAIL: Alamat masih ada di mobile menu dropdown!');
        }

        // Take screenshot of mobile menu open
        await mobilePage.screenshot({ path: 'public/test-artifacts/mobile_menu_clean.png' });
        console.log('✅ Screenshot mobile menu saved to public/test-artifacts/mobile_menu_clean.png');

        // Close menu to inspect page content
        await hamburgerBtn.click();
        await mobilePage.waitForTimeout(300);

        // Check mobile bottom address
        const mobileBottomSiola = mobilePage.locator('.dispusip-address-mobile').getByText('Jl. Tunjungan No. 1-3 (Gedung Siola) Lantai 4, Surabaya');
        const isMobileAddressVisible = await mobileBottomSiola.isVisible();
        console.log('Mobile bottom Siola address visible:', isMobileAddressVisible);

        // 2. Desktop Verification (Viewport 1366x768)
        const desktopContext = await browser.newContext({ viewport: { width: 1366, height: 768 } });
        const desktopPage = await desktopContext.newPage();
        await desktopPage.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle' });

        const desktopSiola = desktopPage.locator('.dispusip-address-desktop').getByText('Jl. Tunjungan No. 1-3 (Gedung Siola) Lantai 4, Surabaya');
        const isDesktopAddressVisible = await desktopSiola.isVisible();
        console.log('Desktop Siola address visible:', isDesktopAddressVisible);

        await desktopPage.screenshot({ path: 'public/test-artifacts/desktop_siola_welcome.png' });
        console.log('✅ Screenshot desktop saved to public/test-artifacts/desktop_siola_welcome.png');

    } finally {
        await browser.close();
    }
}

verify().catch(err => {
    console.error(err);
    process.exit(1);
});
