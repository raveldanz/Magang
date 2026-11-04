import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');
const artifactDir = path.resolve(projectRoot, 'public/test-artifacts');

if (!fs.existsSync(artifactDir)) {
    fs.mkdirSync(artifactDir, { recursive: true });
}

async function run() {
    console.log('[Visual Test] Launching Chrome channel for Supervisor Certificate Revision...');
    const browser = await chromium.launch({
        headless: true,
        channel: 'chrome'
    });

    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 }
    });
    const page = await context.newPage();

    // 1. LOGIN AS SUPERVISOR
    console.log('[Visual Test] 1. Logging in as Supervisor (supervisor@dispusip.surabaya.go.id)...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await page.fill('input[name="email"]', 'supervisor@dispusip.surabaya.go.id');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL(url => !url.pathname.includes('/login'), { timeout: 15000 });

    // 2. GO TO SUPERVISOR CERTIFICATES INDEX
    console.log('[Visual Test] 2. Checking /supervisor/certificates for clean buttons (Revisi without popover overlap)...');
    await page.goto('http://127.0.0.1:8000/supervisor/certificates', { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(artifactDir, '01_supervisor_certificates_index_clean.png'), fullPage: true });

    // 3. FIND AND CLICK 'Revisi' BUTTON IN TABLE
    console.log('[Visual Test] 3. Clicking "Revisi" button in table row...');
    const revisiButton = page.locator('tbody tr a:has-text("Revisi")').first();
    const count = await revisiButton.count();
    
    if (count > 0) {
        await revisiButton.click();
        await page.waitForURL(url => url.pathname.includes('/revision'), { timeout: 15000 });
        console.log('[Visual Test] Successfully navigated to revision page:', page.url());

        // 4. CAPTURE DEDICATED REVISION PAGE
        console.log('[Visual Test] 4. Capturing dedicated revision page with 4 aspects...');
        await page.screenshot({ path: path.join(artifactDir, '02_supervisor_certificate_revision_page.png'), fullPage: true });

        // 5. SELECT ASPECTS AND FILL NOTES
        console.log('[Visual Test] 5. Interacting with aspect checkboxes and quick suggestion chips...');
        // Check "Data Mahasiswa & Magang" (identitas)
        const identitasCheckbox = page.locator('input[value="identitas"]');
        if (await identitasCheckbox.count() > 0 && !(await identitasCheckbox.isChecked())) {
            await identitasCheckbox.check();
        }

        // Click a quick recommendation chip
        const chipBtn = page.locator('button:has-text("Ejaan nama sesuai KTP")').first();
        if (await chipBtn.count() > 0) {
            await chipBtn.click();
        }

        await page.screenshot({ path: path.join(artifactDir, '03_supervisor_aspects_selected_interaction.png'), fullPage: false });

        // 6. SUBMIT REVISION
        console.log('[Visual Test] 6. Submitting revision...');
        await page.click('button[type="submit"]:has-text("Kirim Catatan Revisi")');
        await page.waitForURL(url => url.pathname.includes('/supervisor/certificates'), { timeout: 15000 });

        // 7. CAPTURE INDEX WITH TOAST SUCCESS
        console.log('[Visual Test] 7. Capturing supervisor certificates index after revision submitted...');
        await page.screenshot({ path: path.join(artifactDir, '04_supervisor_certificates_index_toast_success.png'), fullPage: true });

        // 8. CHECK PERLU REVISI TAB
        console.log('[Visual Test] 8. Checking "Perlu Revisi" tab...');
        await page.click('a:has-text("Perlu Revisi")');
        await page.waitForLoadState('networkidle');
        await page.screenshot({ path: path.join(artifactDir, '05_supervisor_certificates_tab_perlu_revisi.png'), fullPage: true });

        // 9. LOGIN AS ADMIN DINAS AND VERIFY REVISION NOTES WITH ASPECTS
        console.log('[Visual Test] 9. Verifying Admin Dinas view of returned certificate revision...');
        const adminPage = await context.newPage();
        await adminPage.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
        await adminPage.fill('input[name="email"]', 'admin.dispusip@surabaya.go.id');
        await adminPage.fill('input[name="password"]', 'password');
        await adminPage.click('button[type="submit"]');
        await adminPage.waitForURL(url => !url.pathname.includes('/login'), { timeout: 15000 });

        await adminPage.goto('http://127.0.0.1:8000/admin/certificates/12/revision', { waitUntil: 'networkidle' });
        await adminPage.screenshot({ path: path.join(artifactDir, '06_admin_viewing_supervisor_aspects.png'), fullPage: false });
        await adminPage.close();
    } else {
        console.log('[Visual Test] No certificates waiting approval found on first tab, capturing current state.');
    }

    console.log('[Visual Test] Visual audit completed successfully.');
    await browser.close();
}

run().catch(err => {
    console.error('[Visual Test] Error during execution:', err);
    process.exit(1);
});
