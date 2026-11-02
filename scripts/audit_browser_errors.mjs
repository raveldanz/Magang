import { chromium } from 'playwright';

async function auditPages() {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    
    const urls = [
        'http://127.0.0.1:8000/proyek-dinas',
        'http://127.0.0.1:8000/projects/2',
        'http://127.0.0.1:8000/'
    ];

    for (const url of urls) {
        console.log(`\n🔍 Memeriksa: ${url}`);
        const page = await browser.newPage();
        const errors = [];
        const warnings = [];

        page.on('console', msg => {
            if (msg.type() === 'error') errors.push(msg.text());
            if (msg.type() === 'warning') warnings.push(msg.text());
        });

        page.on('pageerror', err => {
            errors.push(err.message);
        });

        const resp = await page.goto(url, { waitUntil: 'networkidle' });
        console.log(`Status HTTP: ${resp.status()}`);

        if (errors.length > 0) {
            console.log(`❌ Ditemukan ${errors.length} error console:`, errors);
        } else {
            console.log(`✅ Bebas error console`);
        }

        if (warnings.length > 0) {
            console.log(`⚠️ Peringatan (${warnings.length}):`, warnings);
        }

        await page.close();
    }

    await browser.close();
}

auditPages().catch(console.error);

