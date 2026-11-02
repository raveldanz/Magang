import { chromium } from 'playwright';

(async () => {
  const browser = await chromium.launch({ channel: 'chrome', headless: true });
  const context = await browser.newContext({ viewport: { width: 390, height: 844 }, isMobile: true });
  const page = await context.newPage();

  await page.goto('http://127.0.0.1:8000/login');
  await page.fill('input[name="email"]', 'admin@gmail.com');
  await page.fill('input[name="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForTimeout(1500);

  await page.goto('http://127.0.0.1:8000/admin/certificates');
  await page.waitForTimeout(1000);
  await page.screenshot({ path: 'public/test-artifacts/cert_admin_mobile_tab1.png', fullPage: true });

  const tab2Btn = await page.locator('button', { hasText: 'Desain' });
  await tab2Btn.click();
  await page.waitForTimeout(1000);
  await page.screenshot({ path: 'public/test-artifacts/cert_admin_mobile_tab2.png', fullPage: true });

  await browser.close();
  console.log('Mobile screenshots captured!');
})();

