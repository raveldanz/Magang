import { chromium } from 'playwright';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');
const artifactDir = path.resolve(projectRoot, 'public/test-artifacts');

async function run() {
    const browser = await chromium.launch({
        channel: 'chrome',
        headless: true,
    });

    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 }
    });

    const page = await context.newPage();

    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await page.fill('input[type="email"], input[name="email"]', 'calon.magang@unesa.ac.id');
    await page.fill('input[type="password"], input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    await page.goto('http://127.0.0.1:8000/student/application', { waitUntil: 'networkidle' });
    await page.click('h4:has-text("Pendaftaran Individu")');
    await page.waitForTimeout(300);

    const studentIndivPath = path.join(artifactDir, 'student_individual_no_switch_button.png');
    await page.screenshot({ path: studentIndivPath, fullPage: true });
    console.log('Saved individual screenshot:', studentIndivPath);

    await browser.close();
}

run().catch(err => {
    console.error(err);
    process.exit(1);
});
