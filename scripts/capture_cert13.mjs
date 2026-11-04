import { chromium } from 'playwright';

(async () => {
  const browser = await chromium.launch({ channel: 'chrome', headless: true });
  const context = await browser.newContext({ viewport: { width: 1440, height: 960 } });
  const page = await context.newPage();

  console.log('1. Logging in as admin...');
  await page.goto('http://127.0.0.1:8000/login');
  await page.fill('input[name="email"]', 'admin@gmail.com');
  await page.fill('input[name="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForNavigation({ waitUntil: 'networkidle' }).catch(() => {});
  await page.waitForTimeout(1000);

  console.log('2. Opening /admin/certificates/13/generate...');
  await page.goto('http://127.0.0.1:8000/admin/certificates/13/generate');
  await page.waitForTimeout(2000);

  console.log('3. Capturing full page...');
  await page.screenshot({ path: 'public/test-artifacts/cert13_updated_view.png', fullPage: false });

  console.log('4. Capturing focused certificate sheet...');
  const sheet = await page.locator('.certificate-sheet').first();
  if (await sheet.count() > 0) {
    await sheet.screenshot({ path: 'public/test-artifacts/cert13_sheet_focused.png' });
  }

  await browser.close();
  console.log('Capture completed successfully!');
})();

