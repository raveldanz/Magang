import { chromium } from 'playwright';

(async () => {
  const browser = await chromium.launch({ channel: 'chrome', headless: true });
  const context = await browser.newContext({ viewport: { width: 1400, height: 950 } });
  const page = await context.newPage();

  console.log('1. Logging in as Tiara Maharani (mhs.lulus2@unair.ac.id)...');
  await page.goto('http://127.0.0.1:8000/login');
  await page.fill('input[name="email"]', 'mhs.lulus2@unair.ac.id');
  await page.fill('input[name="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForNavigation({ waitUntil: 'networkidle' }).catch(() => {});
  await page.waitForTimeout(1000);

  console.log('2. Navigating to student dashboard...');
  await page.goto('http://127.0.0.1:8000/dashboard');
  await page.waitForTimeout(1000);
  await page.screenshot({ path: 'public/test-artifacts/tiara_dashboard.png', fullPage: false });

  // Find certificate link if available
  const certLink = await page.locator('a[href*="certificate"]').first();
  if (await certLink.count() > 0) {
    const href = await certLink.getAttribute('href');
    console.log('Found certificate link:', href);
    await page.goto(href, { waitUntil: 'networkidle' });
    await page.screenshot({ path: 'public/test-artifacts/tiara_certificate.png', fullPage: false });
  } else {
    console.log('Directly testing /student/certificate/12...');
    const resp = await page.goto('http://127.0.0.1:8000/student/certificate/12', { waitUntil: 'networkidle' });
    console.log('Status:', resp.status());
    await page.screenshot({ path: 'public/test-artifacts/tiara_cert_12.png', fullPage: false });
  }

  await browser.close();
  console.log('Done!');
})();
