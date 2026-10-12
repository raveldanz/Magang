import { chromium } from 'playwright';
import path from 'path';

(async () => {
    // Launch Chrome explicitly as per AGENTS.md rule
    const browser = await chromium.launch({ headless: true, channel: 'chrome' });
    const context = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await context.newPage();

    try {
        console.log("Navigating to login page...");
        await page.goto('http://localhost:8000/login');
        
        console.log("Logging in as admin...");
        await page.fill('input[name="email"]', 'admin@test.com');
        await page.fill('input[name="password"]', 'password');
        await page.click('button[type="submit"]');

        console.log("Waiting for dashboard...");
        await page.waitForTimeout(2000);

        console.log("Navigating to applications index...");
        await page.goto('http://localhost:8000/admin/applications');

        console.log("Taking screenshot of initial applications view...");
        await page.waitForTimeout(1000);
        await page.screenshot({ path: path.join(process.cwd(), 'public/test-artifacts/bulk_approve_1.png'), fullPage: true });

        console.log("Checking the 'select all' checkbox...");
        const selectAllCb = await page.$('input[x-model="selectAll"]');
        if (selectAllCb) {
            await selectAllCb.check();
        }

        console.log("Taking screenshot with floating action bar...");
        await page.waitForTimeout(500); // Wait for transition
        await page.screenshot({ path: path.join(process.cwd(), 'public/test-artifacts/bulk_approve_2.png'), fullPage: true });

        console.log("Clicking 'Tolak' to show the modal...");
        const tolakBtn = await page.getByRole('button', { name: 'Tolak', exact: true }).first();
        if (tolakBtn) {
            await tolakBtn.click();
        }

        console.log("Taking screenshot of the modal...");
        await page.waitForTimeout(500);
        await page.screenshot({ path: path.join(process.cwd(), 'public/test-artifacts/bulk_approve_3.png'), fullPage: true });

        console.log("Done.");
    } catch (e) {
        console.error("Test failed:", e);
    } finally {
        await browser.close();
    }
})();
