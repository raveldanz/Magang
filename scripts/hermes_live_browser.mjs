// Hermes Live Visual Browser Agent
// Opens real browser window (Chrome/Edge) visibly on screen and performs actions live

import puppeteer from 'puppeteer-core';
import fs from 'node:fs';

// Deteksi path browser Google Chrome atau Microsoft Edge
const chromePaths = [
    'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
    'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
    'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe'
];

const executablePath = chromePaths.find(p => fs.existsSync(p));

if (!executablePath) {
    console.error('[-] Browser Chrome atau Edge tidak ditemukan di path standar Windows.');
    process.exit(1);
}

const sleep = (ms) => new Promise(r => setTimeout(r, ms));

async function runLiveHermes() {
    console.log(`[Hermes Live] Menjalankan browser visual via: ${executablePath}`);
    
    // Buka browser ASLI secara NON-HEADLESS (terlihat di layar desktop)
    const browser = await puppeteer.launch({
        executablePath,
        headless: false,
        defaultViewport: null,
        args: [
            '--start-maximized',
            '--disable-blink-features=AutomationControlled',
            '--no-default-browser-check'
        ]
    });

    const pages = await browser.pages();
    const page = pages.length > 0 ? pages[0] : await browser.newPage();

    console.log('[Hermes Live] Mengunjungi http://127.0.0.1:8000/login ...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle2' });
    await sleep(1500);

    // Cari input email
    const emailSelector = 'input[name="email"], input[type="email"], #email';
    await page.waitForSelector(emailSelector);

    console.log('[Hermes Live] Mengetik email dosen secara live...');
    await page.click(emailSelector);
    // Ketik perlahan dengan delay 50ms per huruf agar terlihat jelas oleh mata
    await page.type(emailSelector, 'dosen.unesa@unesa.ac.id', { delay: 50 });
    await sleep(800);

    // Cari input password
    const passSelector = 'input[name="password"], input[type="password"], #password';
    await page.waitForSelector(passSelector);

    console.log('[Hermes Live] Mengetik password secara live...');
    await page.click(passSelector);
    await page.type(passSelector, 'password', { delay: 60 });
    await sleep(1000);

    // Klik tombol submit
    console.log('[Hermes Live] Mengklik tombol Masuk / Login...');
    const submitBtn = 'button[type="submit"], input[type="submit"]';
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2' }),
        page.click(submitBtn)
    ]);

    console.log(`[Hermes Live] Berhasil masuk! URL sekarang: ${page.url()}`);
    await sleep(2000);

    // Scroll perlahan di dashboard untuk memeriksa data bimbingan
    console.log('[Hermes Live] Memeriksa daftar mahasiswa di dashboard...');
    await page.evaluate(async () => {
        for (let i = 0; i < 600; i += 25) {
            window.scrollBy(0, 25);
            await new Promise(r => setTimeout(r, 40));
        }
    });
    await sleep(2000);

    // Buka menu Logbook
    console.log('[Hermes Live] Membuka halaman Logbook Mahasiswa...');
    await page.goto('http://127.0.0.1:8000/lecturer/logbooks', { waitUntil: 'networkidle2' });
    await sleep(1500);

    // Scroll logbook
    await page.evaluate(async () => {
        for (let i = 0; i < 500; i += 25) {
            window.scrollBy(0, 25);
            await new Promise(r => setTimeout(r, 40));
        }
    });
    await sleep(2000);

    // Buka menu Monitoring
    console.log('[Hermes Live] Membuka halaman Monitoring Mahasiswa...');
    await page.goto('http://127.0.0.1:8000/lecturer/monitoring', { waitUntil: 'networkidle2' });
    await sleep(1500);

    // Kembali ke Dashboard
    console.log('[Hermes Live] Navigasi kembali ke Dashboard Dosen...');
    await page.goto('http://127.0.0.1:8000/lecturer/dashboard', { waitUntil: 'networkidle2' });
    await sleep(2000);

    console.log('[Hermes Live] Sesi visual selesai. Browser tetap terbuka untuk Anda gunakan.');
}

runLiveHermes().catch(err => {
    console.error('[Hermes Live Error]:', err);
    process.exit(1);
});
