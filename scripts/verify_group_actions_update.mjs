import { chromium } from 'playwright';

async function run() {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const page = await browser.newPage({ viewport: { width: 1440, height: 950 } });

    console.log('Logging in as admin...');
    await page.goto('http://127.0.0.1:8000/login');
    await page.fill('input[name="email"]', 'admin.dispusip@surabaya.go.id');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    console.log('Navigating to application 20...');
    let res = await page.goto('http://127.0.0.1:8000/admin/applications/20');
    await page.waitForLoadState('networkidle');

    // If 20 is not a group or doesn't exist, find an application with a group from index
    const hasGroup = await page.$('button:has-text("Proses Pengajuan Tim")');
    if (!hasGroup) {
        console.log('Finding group application from index...');
        await page.goto('http://127.0.0.1:8000/admin/applications');
        await page.waitForLoadState('networkidle');
        const groupRow = await page.$('tr:has-text("JALUR KELOMPOK")');
        if (groupRow) {
            const detailBtn = await groupRow.$('a:has-text("Detail")');
            if (detailBtn) {
                const href = await detailBtn.getAttribute('href');
                await page.goto(href);
                await page.waitForLoadState('networkidle');
            }
        }
    }

    // Scroll to group card if needed
    const processBtn = await page.$('button:has-text("Proses Pengajuan Tim")');
    if (processBtn) {
        await processBtn.scrollIntoViewIfNeeded();
        await page.waitForTimeout(300);

        // Screenshot 1: Group header with new enlarged button and no role badge above
        await page.screenshot({ path: 'public/test-artifacts/group_process_button.png', fullPage: false });
        console.log('Saved group_process_button.png');

        // Click the button to open modal
        await processBtn.click();
        await page.waitForTimeout(400);

        // Screenshot 2: Modal showing "Terima Kelompok", "Tolak Kelompok", and "Simpan Keputusan"
        await page.screenshot({ path: 'public/test-artifacts/group_process_modal.png', fullPage: false });
        console.log('Saved group_process_modal.png');
    } else {
        console.log('Warning: Button "Proses Pengajuan Tim" not found on page.');
    }

    await browser.close();
    console.log('Done!');
}

run().catch(err => {
    console.error(err);
    process.exit(1);
});
