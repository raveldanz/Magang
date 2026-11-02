import { chromium } from 'playwright';

(async () => {
  const browser = await chromium.launch({ channel: 'chrome', headless: true });
  const context = await browser.newContext({ viewport: { width: 1400, height: 950 } });
  const page = await context.newPage();

  console.log('1. Navigating to login...');
  await page.goto('http://127.0.0.1:8000/login');
  await page.fill('input[name="email"]', 'admin@gmail.com');
  await page.fill('input[name="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForNavigation({ waitUntil: 'networkidle' }).catch(() => {});
  await page.waitForTimeout(1000);

  console.log('2. Opening /admin/certificates (Tab 1: Daftar Mahasiswa)...');
  await page.goto('http://127.0.0.1:8000/admin/certificates');
  await page.waitForTimeout(1500);
  await page.screenshot({ path: 'public/test-artifacts/cert_admin_tab1_students.png', fullPage: true });
  console.log('Tab 1 captured to public/test-artifacts/cert_admin_tab1_students.png');

  console.log('3. Clicking Tab 2 (Desain & TTE Tahunan)...');
  const tab2Btn = await page.locator('button', { hasText: 'Desain' });
  await tab2Btn.click();
  await page.waitForTimeout(1500);
  await page.screenshot({ path: 'public/test-artifacts/cert_admin_tab2_settings.png', fullPage: true });
  console.log('Tab 2 captured to public/test-artifacts/cert_admin_tab2_settings.png');

  console.log('4. Opening Certificate Preview (Placement 1)...');
  await page.goto('http://127.0.0.1:8000/admin/certificates/1/preview');
  await page.waitForTimeout(2000);
  await page.screenshot({ path: 'public/test-artifacts/cert_preview_new_design.png', fullPage: true });
  console.log('Certificate Preview captured to public/test-artifacts/cert_preview_new_design.png');

  await browser.close();
  console.log('ALL VISUAL AUDITS COMPLETED SUCCESSFULLY!');
})();

