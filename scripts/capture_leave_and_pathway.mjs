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

  // Handle dialog confirm
  page.on('dialog', async dialog => {
    await dialog.accept();
  });

  // Click "Keluar dari Kelompok"
  const leaveBtn = page.locator('button:has-text("Keluar dari Kelompok")');
  if (await leaveBtn.count() > 0) {
    console.log('4. Clicking Keluar dari Kelompok...');
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle' }),
      leaveBtn.click(),
    ]);
  }

  const outDir = path.resolve('public/test-artifacts');
  const outPath = path.join(outDir, 'student_pathway_selection_clean.png');
  await page.screenshot({ path: outPath, fullPage: true });
  console.log('5. Screenshot saved:', outPath);

  const brainDir = 'C:/Users/TK ABA SBY 69 (3)/.gemini/antigravity/brain/2310afd9-9bad-4e16-8744-a9754ef87b94';
  if (fs.existsSync(brainDir)) {
    fs.copyFileSync(outPath, path.join(brainDir, 'student_pathway_selection_clean.png'));
    console.log('6. Copied to brain');
  }

  // Also navigate to group mode
  const groupCardBtn = page.locator('button:has-text("Pilih Pendaftaran Kelompok")');
  if (await groupCardBtn.count() > 0) {
    console.log('7. Clicking Pilih Pendaftaran Kelompok...');
    await groupCardBtn.click();
    await page.waitForTimeout(500);

    const groupModePath = path.join(outDir, 'student_group_cards_clean.png');
    await page.screenshot({ path: groupModePath, fullPage: true });
    fs.copyFileSync(groupModePath, path.join(brainDir, 'student_group_cards_clean.png'));
    console.log('8. Saved group cards screenshot');
  }

  await browser.close();
  console.log('DONE');
})();

