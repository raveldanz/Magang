import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';

(async () => {
  const browser = await chromium.launch({
    channel: 'chrome',
    headless: true,
  });

  const context = await browser.newContext({
    viewport: { width: 1280, height: 900 },
  });
  const page = await context.newPage();

  console.log('1. Navigating to login...');
  await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });

  console.log('2. Logging in as karina.anggota.tolak@unesa.ac.id...');
  await page.locator('#email').fill('karina.anggota.tolak@unesa.ac.id');
  await page.locator('#password').fill('password');
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle' }),
    page.locator('button[type="submit"]').click(),
  ]);

  console.log('3. Navigating to /student/application...');
  await page.goto('http://127.0.0.1:8000/student/application', { waitUntil: 'networkidle' });

  const outDir = path.resolve('public/test-artifacts');
  if (!fs.existsSync(outDir)) {
    fs.mkdirSync(outDir, { recursive: true });
  }

  const outPath = path.join(outDir, 'student_application_rejected_clean.png');
  await page.screenshot({ path: outPath, fullPage: true });
  console.log('4. Screenshot captured to:', outPath);

  // Copy to brain artifact directory as required by artifact rules
  const brainDir = 'C:/Users/TK ABA SBY 69 (3)/.gemini/antigravity/brain/2310afd9-9bad-4e16-8744-a9754ef87b94';
  if (fs.existsSync(brainDir)) {
    fs.copyFileSync(outPath, path.join(brainDir, 'student_application_rejected_clean.png'));
    console.log('5. Copied to brain artifact dir');
  }

  await browser.close();
  console.log('DONE');
})();
