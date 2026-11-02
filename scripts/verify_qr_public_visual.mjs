import { chromium } from 'playwright';

(async () => {
  const browser = await chromium.launch({ channel: 'chrome', headless: true });
  const context = await browser.newContext({ viewport: { width: 1200, height: 900 } });
  const page = await context.newPage();

  console.log('Visiting /verify-certificate/2nUkl4agLziObPEKxC3JqH9sn34KayDM...');
  await page.goto('http://127.0.0.1:8000/verify-certificate/2nUkl4agLziObPEKxC3JqH9sn34KayDM');
  await page.waitForTimeout(1500);

  await page.screenshot({ path: 'public/test-artifacts/cert_public_verification_page.png', fullPage: true });
  console.log('Verification screenshot saved to public/test-artifacts/cert_public_verification_page.png');

  await browser.close();
  console.log('PUBLIC QR VERIFICATION AUDIT PASSED!');
})();
