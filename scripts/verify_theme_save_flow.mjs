import { chromium } from 'playwright';

(async () => {
  const browser = await chromium.launch({ channel: 'chrome', headless: true });
  const context = await browser.newContext({ viewport: { width: 1400, height: 950 } });
  const page = await context.newPage();

  // 1. Login Admin
  await page.goto('http://127.0.0.1:8000/login');
  await page.fill('input[name="email"]', 'admin@gmail.com');
  await page.fill('input[name="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForNavigation({ waitUntil: 'networkidle' }).catch(() => {});
  await page.waitForTimeout(1000);

  // 2. Buka Tab 2 Settings langsung
  await page.goto('http://127.0.0.1:8000/admin/certificates?tab=settings');
  await page.waitForTimeout(1000);

  // 3. Klik Preset Emerald Gold
  console.log('Testing Preset button click...');
  await page.click('button:has-text("Emerald Gold")');
  await page.waitForTimeout(1000);

  // Kembalikan ke preset Dispusip 2026
  console.log('Restoring to Dispusip 2026 preset...');
  await page.click('button:has-text("Dispusip 2026")');
  await page.waitForTimeout(1000);

  // 4. Submit form
  console.log('Submitting form...');
  await page.click('button:has-text("Simpan Pengaturan Tema")');
  await page.waitForNavigation({ waitUntil: 'networkidle' }).catch(() => {});
  await page.waitForTimeout(1500);

  await page.screenshot({ path: 'public/test-artifacts/cert_admin_theme_saved.png', fullPage: true });
  console.log('Saved screenshot captured to public/test-artifacts/cert_admin_theme_saved.png');

  await browser.close();
  console.log('THEME SAVE FLOW COMPLETED SUCCESSFULLY!');
})();

