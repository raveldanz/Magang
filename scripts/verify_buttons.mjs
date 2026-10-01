import { chromium } from 'playwright';
import path from 'node:path';
import fs from 'node:fs';

const artifactDir = 'C:\\Users\\TK ABA SBY 69 (3)\\.gemini\\antigravity-ide\\brain\\ed18134e-ea3c-4bbc-9577-17ad6dc36bec';

async function run() {
    console.log("Starting Playwright...");
    const browser = await chromium.launch({ headless: true, channel: 'chrome' });
    const context = await browser.newContext({ viewport: { width: 1920, height: 1080 } });
    const page = await context.newPage();
    
    // Login
    console.log("Logging in...");
    await page.goto('http://127.0.0.1:8000/login');
    await page.fill('input[name="email"]', 'admin@gmail.com');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 10000 });

    console.log("Navigating to /admin/users...");
    await page.goto('http://127.0.0.1:8000/admin/users', { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(artifactDir, 'users_table_standardized.png'), fullPage: true });

    console.log("Navigating to /admin/agencies/1...");
    // Just find the first agency ID
    await page.goto('http://127.0.0.1:8000/admin/agencies', { waitUntil: 'networkidle' });
    const agencyHref = await page.getAttribute('table tbody tr:first-child a.bg-blue-600', 'href').catch(() => null);
    if(agencyHref) {
        await page.goto(agencyHref, { waitUntil: 'networkidle' });
        await page.screenshot({ path: path.join(artifactDir, 'agency_show_redesigned.png'), fullPage: true });
    } else {
        await page.goto('http://127.0.0.1:8000/admin/agencies/1', { waitUntil: 'networkidle' });
        await page.screenshot({ path: path.join(artifactDir, 'agency_show_redesigned.png'), fullPage: true });
    }

    console.log("Navigating to /admin/universities/1...");
    await page.goto('http://127.0.0.1:8000/admin/universities/1', { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(artifactDir, 'univ_show_comparison.png'), fullPage: true });

    await browser.close();
    console.log("Done!");
}
run().catch(console.error);
