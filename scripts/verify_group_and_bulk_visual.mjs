import { chromium } from 'playwright';

async function run() {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    
    // 1. Desktop Test
    const page = await browser.newPage({ viewport: { width: 1440, height: 950 } });

    console.log('Logging in as admin...');
    await page.goto('http://127.0.0.1:8000/login');
    await page.fill('input[name="email"]', 'admin.dispusip@surabaya.go.id');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    console.log('Navigating to applications index (Desktop)...');
    await page.goto('http://127.0.0.1:8000/admin/applications');
    await page.waitForLoadState('networkidle');

    // Screenshot 1: Desktop index table overview
    await page.screenshot({ path: 'public/test-artifacts/admin_applications_desktop_overview.png', fullPage: false });

    // Click accordion toggle to expand group members
    const accordionBtn = await page.$('button:has-text("Anggota")');
    if (accordionBtn) {
        await accordionBtn.click();
        await page.waitForTimeout(400);
        await page.evaluate(() => window.scrollBy(0, 260));
        await page.waitForTimeout(500);
        await page.screenshot({ path: 'public/test-artifacts/admin_applications_group_expanded.png', fullPage: false });
    }

    // Scroll back up and select a checkbox to show bulk action floating bar
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.waitForTimeout(300);
    const firstCheckbox = await page.$('tbody input[type="checkbox"]');
    if (firstCheckbox) {
        await firstCheckbox.check();
        await page.waitForTimeout(500);
        await page.screenshot({ path: 'public/test-artifacts/admin_applications_bulk_toolbar.png', fullPage: false });
    }

    // Find a group application detail link
    const groupRow = await page.$('tr:has-text("JALUR KELOMPOK")');
    if (groupRow) {
        const detailBtn = await groupRow.$('a:has-text("Detail")');
        if (detailBtn) {
            const href = await detailBtn.getAttribute('href');
            await page.goto(href);
            await page.waitForLoadState('networkidle');

            // Top view showing "Aksi Serentak Kelompok"
            await page.screenshot({ path: 'public/test-artifacts/admin_application_detail_group_header.png', fullPage: false });

            // Scroll down to group panel and decision form
            await page.evaluate(() => window.scrollBy(0, 500));
            await page.waitForTimeout(500);

            await page.screenshot({ path: 'public/test-artifacts/admin_application_detail_group_actions.png', fullPage: false });
        }
    }

    // 2. Mobile Test (reuse logged-in page and resize viewport)
    console.log('Testing Mobile Viewport...');
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('http://127.0.0.1:8000/admin/applications');
    await page.waitForLoadState('networkidle');

    // Scroll to group card and expand
    const mobileAccordionBtn = await page.$('.md\\:hidden button:has-text("Anggota")');
    if (mobileAccordionBtn) {
        await mobileAccordionBtn.scrollIntoViewIfNeeded();
        await page.evaluate(() => window.scrollBy(0, -100));
        await mobileAccordionBtn.click();
        await page.waitForTimeout(600);
    }

    await page.screenshot({ path: 'public/test-artifacts/admin_applications_mobile_view.png', fullPage: false });
    console.log('Saved admin_applications_mobile_view.png');

    await browser.close();
    console.log('All verification tasks completed successfully!');
}

run().catch(err => {
    console.error('Test error:', err);
    process.exit(1);
});
