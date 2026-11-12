import { chromium } from 'playwright';

const accounts = [
  { name: 'Super Admin Dispusip (Pass: password)', email: 'admin@gmail.com', pass: 'password', expectedUrl: '/admin/dashboard' },
  { name: 'Super Admin Dispusip (Pass: admin123)', email: 'admin@gmail.com', pass: 'admin123', expectedUrl: '/admin/dashboard' },
  { name: 'Super Admin Pemkot (admin@surabaya.go.id)', email: 'admin@surabaya.go.id', pass: 'password', expectedUrl: '/admin/dashboard' },
  { name: 'Tim Kerja dan Kepegawaian (Supervisor)', email: 'kepegawaian@dispusip.surabaya.go.id', pass: 'password', expectedUrl: '/supervisor/dashboard' },
  { name: 'Supervisor Utama (Bambang Supriyadi)', email: 'supervisor@dispusip.surabaya.go.id', pass: 'password', expectedUrl: '/supervisor/dashboard' },
  { name: 'Admin Dispusip Surabaya', email: 'admin.dispusip@surabaya.go.id', pass: 'password', expectedUrl: '/admin/dashboard' },
  { name: 'Mentor Tim Kerja', email: 'mentor.dispusip@surabaya.go.id', pass: 'password', expectedUrl: '/mentor/dashboard' },
  { name: 'Portal Kampus UNESA', email: 'admin@unesa.ac.id', pass: 'password', expectedUrl: '/university/dashboard' },
  { name: 'Dosen Pembimbing UNESA', email: 'dosen.unesa@unesa.ac.id', pass: 'password', expectedUrl: '/lecturer/dashboard' },
  { name: 'Mahasiswa Aktif (Dimas)', email: 'mahasiswa.aktif@unesa.ac.id', pass: 'password', expectedUrl: '/dashboard' },
];

async function main() {
  const browser = await chromium.launch({
    headless: true,
    channel: 'chrome',
  });

  console.log('--- STARTING COMPREHENSIVE LOGIN VERIFICATION ---');
  let passCount = 0;
  let failCount = 0;

  for (const acc of accounts) {
    const context = await browser.newContext();
    const page = await context.newPage();

    try {
      const t0 = Date.now();
      await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'domcontentloaded' });
      await page.fill('input[name="email"]', acc.email);
      await page.fill('input[name="password"]', acc.pass);
      await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type="submit"]'),
      ]);

      const currentUrl = page.url();
      const duration = Date.now() - t0;

      if (currentUrl.includes(acc.expectedUrl)) {
        console.log(`[PASS] ${acc.name} -> ${currentUrl} (${duration}ms)`);
        passCount++;

        // Ambil screenshot bukti login Super Admin & Supervisor
        if (acc.email === 'admin@gmail.com' && acc.pass === 'password') {
          await page.screenshot({ path: 'public/test-artifacts/admin_gmail_logged_in.png', fullPage: false });
        }
        if (acc.email === 'kepegawaian@dispusip.surabaya.go.id') {
          await page.screenshot({ path: 'public/test-artifacts/supervisor_kepegawaian_logged_in.png', fullPage: false });
        }
      } else {
        const errorText = await page.locator('.text-red-500, .text-danger, .invalid-feedback, div:has-text("credentials")').innerText().catch(() => 'No text');
        console.error(`[FAIL] ${acc.name} -> Got ${currentUrl}, Expected: ${acc.expectedUrl}. Error: ${errorText}`);
        failCount++;
      }
    } catch (err) {
      console.error(`[ERROR] ${acc.name} -> ${err.message}`);
      failCount++;
    } finally {
      await context.close();
    }
  }

  await browser.close();
  console.log(`\n=== SUMMARY: ${passCount} PASSED, ${failCount} FAILED ===`);
  if (failCount > 0) {
    process.exit(1);
  }
}

main().catch(err => {
  console.error(err);
  process.exit(1);
});
