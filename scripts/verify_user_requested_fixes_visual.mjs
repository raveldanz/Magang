import { chromium } from 'playwright';

async function run() {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const context = await browser.newContext({ 
        viewport: { width: 1440, height: 950 },
        extraHTTPHeaders: { 'Connection': 'close' }
    });
    const page = await context.newPage();

    console.log('1. Logging in as Super Admin to check agency show page...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="email"]', 'admin@gmail.com');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForNavigation({ waitUntil: 'domcontentloaded' }).catch(() => {});

    console.log('Navigating to admin/agencies/1...');
    await page.goto('http://127.0.0.1:8000/admin/agencies/1', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(600);

    // Screenshot 1: Pejabat Penandatangan Resmi with "Masuk Sebagai Supervisor"
    const signeeBox = await page.$('.cc-side');
    if (signeeBox) {
        await signeeBox.scrollIntoViewIfNeeded();
        await page.screenshot({ 
            path: 'public/test-artifacts/admin_agency_masuk_sebagai_supervisor.png' 
        });
        console.log('Saved admin_agency_masuk_sebagai_supervisor.png');
    }

    console.log('2. Navigating to /proyek-dinas to check solid category badge...');
    await page.goto('http://127.0.0.1:8000/proyek-dinas', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(600);

    const klinikCard = await page.$('article:has-text("Klinik"), div:has-text("Klinik Literasi Inklusif")');
    if (klinikCard) {
        await klinikCard.scrollIntoViewIfNeeded();
    }
    await page.screenshot({ 
        path: 'public/test-artifacts/proyek_dinas_solid_category_badge.png',
        fullPage: false 
    });
    console.log('Saved proyek_dinas_solid_category_badge.png');

    console.log('3. Navigating to /projects/18 to check gallery and Buka Resolusi Penuh...');
    await page.goto('http://127.0.0.1:8000/projects/18', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(800);

    const gallerySection = await page.$('text=Galeri Tangkapan Layar & Dokumentasi Visual Proyek');
    if (gallerySection) {
        await gallerySection.scrollIntoViewIfNeeded();
        await page.waitForTimeout(400);
    }
    await page.screenshot({ 
        path: 'public/test-artifacts/project_detail_buka_resolusi_penuh.png',
        fullPage: false 
    });
    console.log('Saved project_detail_buka_resolusi_penuh.png');

    await browser.close();
    console.log('All visual audits finished successfully.');
}

run().catch(err => {
    console.error(err);
    process.exit(1);
});

