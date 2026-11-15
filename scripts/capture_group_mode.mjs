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

  await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
  await page.locator('#email').fill('mahasiswa.aktif@unesa.ac.id');
  await page.locator('#password').fill('password');
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle' }),
    page.locator('button[type="submit"]').click(),
  ]);

  await page.goto('http://127.0.0.1:8000/student/application?mode=group', { waitUntil: 'networkidle' });

  const outDir = path.resolve('public/test-artifacts');
  if (!fs.existsSync(outDir)) {
    fs.mkdirSync(outDir, { recursive: true });
  }

  const outPath = path.join(outDir, 'student_group_mode_clean.png');
  await page.screenshot({ path: outPath, fullPage: true });
  console.log('Screenshot captured to:', outPath);

  const brainDir = 'C:/Users/TK ABA SBY 69 (3)/.gemini/antigravity/brain/2310afd9-9bad-4e16-8744-a9754ef87b94';
  if (fs.existsSync(brainDir)) {
    fs.copyFileSync(outPath, path.join(brainDir, 'student_group_mode_clean.png'));
    console.log('Copied to brain artifact dir');
  }

  await browser.close();
  console.log('DONE');
})();

