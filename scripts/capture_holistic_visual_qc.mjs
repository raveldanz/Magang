import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';

const outDir = path.resolve('public/test-artifacts');
if (!fs.existsSync(outDir)) {
  fs.mkdirSync(outDir, { recursive: true });
}
const brainDir = 'C:/Users/TK ABA SBY 69 (3)/.gemini/antigravity/brain/2310afd9-9bad-4e16-8744-a9754ef87b94';

async function captureView(email, targetUrl, filename) {
  const browser = await chromium.launch({ channel: 'chrome', headless: true });
  const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });

  console.log(`[QC] Logging in as ${email}...`);
  await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
  await page.locator('#email').fill(email);
  await page.locator('#password').fill('password');
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle' }),
    page.locator('button[type="submit"]').click(),
  ]);

  console.log(`[QC] Navigating to ${targetUrl}...`);
  await page.goto(`http://127.0.0.1:8000${targetUrl}`, { waitUntil: 'networkidle' });

  const outPath = path.join(outDir, filename);
  await page.screenshot({ path: outPath, fullPage: true });
  console.log(`[QC] Screenshot saved: ${filename}`);

  if (fs.existsSync(brainDir)) {
    fs.copyFileSync(outPath, path.join(brainDir, filename));
  }

  await browser.close();
}

(async () => {
  // 1. Notification Center (Admin)
  await captureView('admin@surabaya.go.id', '/notifications', 'qc_notifications_center.png');

  // 2. Chat (Mentor)
  await captureView('mentor.dispusip@surabaya.go.id', '/chat', 'qc_chat_mentor.png');

  // 3. Supervisor Dashboard
  await captureView('supervisor@dispusip.surabaya.go.id', '/supervisor/dashboard', 'qc_supervisor_dashboard.png');

  // 4. Lecturer Dashboard
  await captureView('dosen.unesa@unesa.ac.id', '/lecturer/dashboard', 'qc_lecturer_dashboard.png');

  console.log('ALL HOLISTIC CAPTURES COMPLETED');
})();
