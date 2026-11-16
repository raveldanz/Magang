import { chromium } from 'playwright';

(async () => {
  const browser = await chromium.launch({ channel: 'chrome', headless: true });
  const context = await browser.newContext({ viewport: { width: 1400, height: 950 } });
  const page = await context.newPage();

  console.log('1. Logging in as Administrator Utama (admin@gmail.com)...');
  await page.goto('http://127.0.0.1:8000/login');
  await page.fill('input[name="email"]', 'admin@gmail.com');
  await page.fill('input[name="password"]', 'admin123');
  await page.click('button[type="submit"]');
  await page.waitForNavigation({ waitUntil: 'networkidle' }).catch(() => {});
  await page.waitForTimeout(1000);

  console.log('2. Navigating to /admin/users...');
  await page.goto('http://127.0.0.1:8000/admin/users', { waitUntil: 'networkidle' });
  await page.waitForTimeout(1000);

  // Take screenshot with role filter showing Pembimbing
  await page.selectOption('select[name="role"]', 'dosen');
  await page.click('button:has-text("Terapkan Filter")');
  await page.waitForNavigation({ waitUntil: 'networkidle' }).catch(() => {});
  await page.waitForTimeout(1000);

  await page.screenshot({ path: 'public/test-artifacts/pembimbing_filtered_table.png', fullPage: false });
  console.log('Screenshot saved to public/test-artifacts/pembimbing_filtered_table.png');

  await browser.close();
  console.log('Verification script completed!');
})();

