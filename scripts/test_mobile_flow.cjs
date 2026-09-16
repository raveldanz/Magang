const puppeteer = require('puppeteer-core');
const path = require('path');

const CHROME_PATH = 'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe';
const ARTIFACTS_DIR = 'C:\\Users\\TOSHIBA\\.gemini\\antigravity-ide\\brain\\8416f342-75d5-41f9-b410-318c914d9c7d';

async function clickSafely(page, selector) {
    await page.waitForSelector(selector, { visible: true });
    await page.evaluate((sel) => {
        const el = document.querySelector(sel);
        if (el) el.scrollIntoView({ behavior: 'instant', block: 'center' });
    }, selector);
    await new Promise(r => setTimeout(r, 400));
    await page.click(selector);
}

async function run() {
    console.log('Launching Chrome for Mobile Flow (390x844)...');
    const browser = await puppeteer.launch({
        executablePath: CHROME_PATH,
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-gpu']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });

    // -------------------------------------------------------------
    // STEP 1: MAHASISWA UNITOMO SUBMIT LOGBOOK
    // -------------------------------------------------------------
    console.log('\n--- STEP 1: Mahasiswa UNITOMO Mobile Logbook ---');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle2' });
    await page.type('input[name="email"], input[type="email"]', 'mhs.unitomo.mobile@unitomo.ac.id');
    await page.type('input[name="password"], input[type="password"]', 'Password123!');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2' }),
        page.click('button[type="submit"]')
    ]);
    console.log('Logged in as Mahasiswa:', page.url());

    console.log('Navigating to /student/logbook/create...');
    await page.goto('http://127.0.0.1:8000/student/logbook/create', { waitUntil: 'networkidle2' });
    console.log('Current URL is:', page.url());

    await page.evaluate(() => {
        const dateInput = document.querySelector('input[name="date"]');
        if (dateInput) dateInput.value = '2026-09-02';
        const actInput = document.querySelector('textarea[name="activity"]');
        if (actInput) actInput.value = 'Melakukan digitalisasi dan preservasi arsip statis dokumen bersejarah Kota Surabaya di Dispusip.';
    });

    console.log('Submitting logbook form via form.submit()...');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2' }),
        page.evaluate(() => {
            document.querySelector('form[action*="logbook"]').submit();
        })
    ]);
    console.log('Logbook submitted. Current URL:', page.url());

    const shotLogbook = path.join(ARTIFACTS_DIR, 'mobile_student_logbook.png');
    await page.screenshot({ path: shotLogbook, fullPage: false });
    console.log('Saved logbook screenshot:', shotLogbook);

    // Logout
    await page.evaluate(() => {
        const f = document.querySelector('form[action*="logout"]');
        if (f) f.submit();
    });
    await new Promise(r => setTimeout(r, 1200));

    // -------------------------------------------------------------
    // STEP 2: MENTOR DISPUSIP APPROVE LOGBOOK & EVALUATION
    // -------------------------------------------------------------
    console.log('\n--- STEP 2: Mentor Dispusip Mobile Approval & Evaluation ---');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle2' });
    await page.type('input[name="email"], input[type="email"]', 'mentor.dispusip.mobile@surabaya.go.id');
    await page.type('input[name="password"], input[type="password"]', 'Password123!');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2' }),
        page.click('button[type="submit"]')
    ]);
    console.log('Logged in as Mentor:', page.url());

    // Approve Logbook
    console.log('Visiting /mentor/logbooks...');
    await page.goto('http://127.0.0.1:8000/mentor/logbooks', { waitUntil: 'networkidle2' });
    
    try {
        const hasForm = await page.evaluate(() => {
            const btn = document.querySelector('form[action*="/mentor/logbooks/"] button[type="submit"]');
            if (btn) {
                btn.closest('form').submit();
                return true;
            }
            return false;
        });
        if (hasForm) {
            console.log('Logbook approval submitted.');
            await page.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {});
        } else {
            console.log('No pending logbook form found (may already be approved).');
        }
    } catch (e) {
        console.log('Approve logbook note:', e.message);
    }

    // Go to Mentor Evaluation Form
    console.log('Visiting /mentor/students/38/evaluation...');
    await page.goto('http://127.0.0.1:8000/mentor/students/38/evaluation', { waitUntil: 'networkidle2' });

    console.log('Filling Mentor Evaluation Form...');
    await page.evaluate(() => {
        const d = document.querySelector('input[name="nilai_disiplin"]');
        if (d) { d.value = '92'; d.dispatchEvent(new Event('input', { bubbles: true })); }
        const k = document.querySelector('input[name="nilai_kinerja"]');
        if (k) { k.value = '94'; k.dispatchEvent(new Event('input', { bubbles: true })); }
        const l = document.querySelector('input[name="nilai_laporan"]');
        if (l) { l.value = '90'; l.dispatchEvent(new Event('input', { bubbles: true })); }
        const c = document.querySelector('textarea[name="catatan_pembimbing"], textarea[name="catatan"]');
        if (c) { c.value = 'Kinerja sangat baik, aktif berinisiatif dalam preservasi arsip elektronik.'; }
    });

    const shotMentorForm = path.join(ARTIFACTS_DIR, 'mobile_mentor_evaluated.png');
    await page.screenshot({ path: shotMentorForm, fullPage: false });
    console.log('Saved mentor evaluation form screenshot:', shotMentorForm);

    console.log('Submitting Mentor Evaluation...');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2' }),
        page.evaluate(() => {
            document.querySelector('form[action*="evaluation"]').submit();
        })
    ]);
    console.log('Mentor evaluation submitted. Current URL:', page.url());

    // Logout
    await page.evaluate(() => {
        const f = document.querySelector('form[action*="logout"]');
        if (f) f.submit();
    });
    await new Promise(r => setTimeout(r, 1200));

    // -------------------------------------------------------------
    // STEP 3: DOSEN DPL UNITOMO EVALUATION
    // -------------------------------------------------------------
    console.log('\n--- STEP 3: DPL UNITOMO Mobile Academic Evaluation ---');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle2' });
    await page.type('input[name="email"], input[type="email"]', 'dosen.unitomo.mobile@unitomo.ac.id');
    await page.type('input[name="password"], input[type="password"]', 'Password123!');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2' }),
        page.click('button[type="submit"]')
    ]);
    console.log('Logged in as DPL:', page.url());

    console.log('Visiting /lecturer/students/38/evaluation...');
    await page.goto('http://127.0.0.1:8000/lecturer/students/38/evaluation', { waitUntil: 'networkidle2' });

    console.log('Filling DPL Evaluation Form...');
    await page.evaluate(() => {
        const sm = document.querySelector('input[name="score_mastery"]');
        if (sm) { sm.value = '94'; sm.dispatchEvent(new Event('input', { bubbles: true })); }
        const sr = document.querySelector('input[name="score_report"]');
        if (sr) { sr.value = '95'; sr.dispatchEvent(new Event('input', { bubbles: true })); }
        const sa = document.querySelector('input[name="score_attitude"]');
        if (sa) { sa.value = '96'; sa.dispatchEvent(new Event('input', { bubbles: true })); }
        const dn = document.querySelector('textarea[name="dosen_notes"]');
        if (dn) { dn.value = 'Laporan ilmiah disusun sistematis sesuai format akademik.'; }
    });

    const shotDplForm = path.join(ARTIFACTS_DIR, 'mobile_dpl_evaluated.png');
    await page.screenshot({ path: shotDplForm, fullPage: false });
    console.log('Saved DPL evaluation form screenshot:', shotDplForm);

    console.log('Submitting DPL Evaluation...');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2' }),
        page.evaluate(() => {
            document.querySelector('form[action*="evaluation"]').submit();
        })
    ]);
    console.log('DPL evaluation submitted. Current URL:', page.url());

    // Logout
    await page.evaluate(() => {
        const f = document.querySelector('form[action*="logout"]');
        if (f) f.submit();
    });
    await new Promise(r => setTimeout(r, 1200));

    // -------------------------------------------------------------
    // STEP 4: MAHASISWA E-CERTIFICATE VERIFICATION
    // -------------------------------------------------------------
    console.log('\n--- STEP 4: Mahasiswa UNITOMO Mobile E-Certificate ---');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle2' });
    await page.type('input[name="email"], input[type="email"]', 'mhs.unitomo.mobile@unitomo.ac.id');
    await page.type('input[name="password"], input[type="password"]', 'Password123!');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2' }),
        page.click('button[type="submit"]')
    ]);
    console.log('Logged in as Mahasiswa:', page.url());

    console.log('Visiting /student/certificate/38...');
    await page.goto('http://127.0.0.1:8000/student/certificate/38', { waitUntil: 'networkidle2' });

    const shotCert = path.join(ARTIFACTS_DIR, 'mobile_student_certificate_final.png');
    await page.screenshot({ path: shotCert, fullPage: false });
    console.log('Saved Certificate screenshot:', shotCert);

    await browser.close();
    console.log('\n=== ALL MOBILE FLOW COMPLETED SUCCESSFULLY! ===');
}

run().catch(err => {
    console.error('Test run failed:', err);
    process.exit(1);
});
