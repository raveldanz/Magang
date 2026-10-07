import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');
const artifactDir = path.resolve(projectRoot, 'public/test-artifacts');

if (!fs.existsSync(artifactDir)) {
    fs.mkdirSync(artifactDir, { recursive: true });
}

async function run() {
    console.log('[Verification] Launching Chrome channel for UI parity check...');
    const browser = await chromium.launch({
        headless: true,
        channel: 'chrome'
    });

    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 }
    });
    const page = await context.newPage();

    // 1. LOGIN AS MENTOR
    console.log('[Verification] 1. Logging in as Mentor (mentor.kominfo@surabaya.go.id)...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await page.fill('input[name="email"]', 'mentor.kominfo@surabaya.go.id');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL(url => !url.pathname.includes('/login'), { timeout: 15000 });

    // Mentor Dashboard
    console.log('[Verification] Checking Mentor Dashboard...');
    await page.goto('http://127.0.0.1:8000/mentor/dashboard', { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(artifactDir, 'mentor_dashboard.png'), fullPage: true });

    // Mentor Logbooks
    console.log('[Verification] Checking Mentor Logbooks Index...');
    await page.goto('http://127.0.0.1:8000/mentor/logbooks', { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(artifactDir, 'mentor_logbooks_weekly.png'), fullPage: true });

    // Mentor Student Detail (find first student detail from dashboard or placement)
    await page.goto('http://127.0.0.1:8000/mentor/dashboard', { waitUntil: 'networkidle' });
    const mentorDetailLink = await page.$('a[href*="/mentor/students/"]');
    if (mentorDetailLink) {
        const detailHref = await mentorDetailLink.getAttribute('href');
        console.log(`[Verification] Navigating to Mentor Student Detail: ${detailHref}`);
        await page.goto(detailHref, { waitUntil: 'networkidle' });
        await page.screenshot({ path: path.join(artifactDir, 'mentor_student_detail.png'), fullPage: true });

        // Mentor Evaluation Form
        const evalLink = await page.$('a[href*="/evaluation"]');
        if (evalLink) {
            const evalHref = await evalLink.getAttribute('href');
            console.log(`[Verification] Navigating to Mentor Evaluation: ${evalHref}`);
            await page.goto(evalHref, { waitUntil: 'networkidle' });
            await page.screenshot({ path: path.join(artifactDir, 'mentor_evaluation_form.png'), fullPage: true });
        }
    }

    // 2. LOGOUT & LOGIN AS LECTURER (DPL)
    console.log('[Verification] 2. Clearing session and logging in as Lecturer (dosen.unesa@unesa.ac.id)...');
    await context.clearCookies();
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await page.fill('input[name="email"]', 'dosen.unesa@unesa.ac.id');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL(url => !url.pathname.includes('/login'), { timeout: 15000 });

    // Lecturer Logbooks
    console.log('[Verification] Checking Lecturer Logbooks Index...');
    await page.goto('http://127.0.0.1:8000/lecturer/logbooks', { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(artifactDir, 'lecturer_logbooks_weekly.png'), fullPage: true });

    // Lecturer Dashboard
    await page.goto('http://127.0.0.1:8000/lecturer/dashboard', { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(artifactDir, 'lecturer_dashboard.png'), fullPage: true });
    const lecturerDetailLink = await page.$('a[href*="/lecturer/students/"]');
    if (lecturerDetailLink) {
        const detailHref = await lecturerDetailLink.getAttribute('href');
        console.log(`[Verification] Navigating to Lecturer Student Detail: ${detailHref}`);
        await page.goto(detailHref, { waitUntil: 'networkidle' });
        await page.screenshot({ path: path.join(artifactDir, 'lecturer_student_detail.png'), fullPage: true });

        // Lecturer Evaluation Form
        const evalLink = await page.$('a[href*="/evaluation"]');
        if (evalLink) {
            const evalHref = await evalLink.getAttribute('href');
            console.log(`[Verification] Navigating to Lecturer Evaluation: ${evalHref}`);
            await page.goto(evalHref, { waitUntil: 'networkidle' });
            await page.screenshot({ path: path.join(artifactDir, 'lecturer_evaluation_form.png'), fullPage: true });
        }
    }

    await browser.close();
    console.log('[Verification] All screenshots successfully generated in public/test-artifacts/');
}

run().catch(err => {
    console.error('[Verification] Error:', err);
    process.exit(1);
});
