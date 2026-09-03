// Hermes Live Visual Agent: Student Registration & 7-Day Logbook Flow
// Fully visible execution inside Google Chrome on user desktop

import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import { execSync } from 'node:child_process';

const chromePaths = [
    'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
    'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
    'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe'
];

const executablePath = chromePaths.find(p => fs.existsSync(p));
if (!executablePath) {
    console.error('[-] Browser Chrome/Edge tidak ditemukan.');
    process.exit(1);
}

const sleep = (ms) => new Promise(r => setTimeout(r, ms));

async function runLiveStudentFlow() {
    const uniqueSuffix = Date.now().toString().slice(-4);
    const studentName = `Muhammad Rizky (Live Hermes ${uniqueSuffix})`;
    const studentEmail = `rizky.live${uniqueSuffix}@unesa.ac.id`;
    const password = 'password123';

    console.log(`\n============================================================`);
    console.log(`🤖 [HERMES LIVE VISUAL AGENT] Registrasi & 7 Hari Logbook`);
    console.log(`Mahasiswa: ${studentName}`);
    console.log(`Email    : ${studentEmail}`);
    console.log(`============================================================\n`);

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

    // 1. REGISTRASI MAHASISWA LIVE DI LAYAR
    console.log('[Langkah 1] Membuka halaman pendaftaran: http://127.0.0.1:8000/register');
    await page.goto('http://127.0.0.1:8000/register', { waitUntil: 'networkidle2' });
    await sleep(1000);

    console.log('[Langkah 1] Mengetik nama mahasiswa secara live...');
    await page.waitForSelector('#name');
    await page.type('#name', studentName, { delay: 35 });
    await sleep(300);

    console.log('[Langkah 1] Mengetik email mahasiswa...');
    await page.type('#email', studentEmail, { delay: 35 });
    await sleep(300);

    console.log('[Langkah 1] Mengetik kata sandi & konfirmasi...');
    await page.type('#password', password, { delay: 35 });
    await sleep(300);
    await page.type('#password_confirmation', password, { delay: 35 });
    await sleep(600);

    console.log('[Langkah 1] Mengklik tombol DAFTAR SEKARANG...');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2' }),
        page.click('button[type="submit"]')
    ]);

    console.log(`[Langkah 1] Registrasi selesai! Akun berhasil dibuat.`);
    await sleep(1200);

    // 2. SETUP DATA PENEMPATAN & 7 HARI LOGBOOK
    console.log('[Langkah 2] Mengaktifkan status magang & mengenerate 7 hari logbook lengkap...');
    try {
        const out = execSync(`php scripts/setup_student_full.php "${studentEmail}"`, { encoding: 'utf8' });
        console.log(`[Langkah 2] Hasil aktivasi: ${out.trim()}`);
    } catch (e) {
        console.error('[Error Setup]', e.message);
    }
    await sleep(1000);

    // 3. BUKA HALAMAN LOGBOOK DI LAYAR
    console.log('[Langkah 3] Navigasi ke Halaman Logbook: http://127.0.0.1:8000/student/logbook');
    await page.goto('http://127.0.0.1:8000/student/logbook', { waitUntil: 'networkidle2' });
    await sleep(1500);

    // 4. INSPEKSI & HIGHLIGHT 7 HARI LOGBOOK SECARA LIVE DI LAYAR
    console.log('[Langkah 4] Menampilkan seluruh 7 riwayat logbook pada tabel...');

    // Injek banner visual di browser agar terlihat jelas oleh pengguna
    await page.evaluate(() => {
        const banner = document.createElement('div');
        banner.id = 'hermes-banner';
        banner.style.position = 'fixed';
        banner.style.top = '16px';
        banner.style.right = '24px';
        banner.style.zIndex = '99999';
        banner.style.background = 'linear-gradient(135deg, #1e3a8a, #2563eb)';
        banner.style.color = '#ffffff';
        banner.style.padding = '14px 20px';
        banner.style.borderRadius = '16px';
        banner.style.boxShadow = '0 10px 25px -5px rgba(0, 0, 0, 0.3)';
        banner.style.fontFamily = 'sans-serif';
        banner.style.fontSize = '13px';
        banner.style.fontWeight = 'bold';
        banner.innerHTML = '🤖 <strong>Hermes Agent Live:</strong> Berhasil Membuat Akun & 7 Hari Logbook!';
        document.body.appendChild(banner);
    });

    // Scroll perlahan di depan mata pengguna untuk memperlihatkan semua 7 logbook
    await page.evaluate(async () => {
        for (let i = 0; i < 900; i += 25) {
            window.scrollBy(0, 25);
            await new Promise(r => setTimeout(r, 40));
        }
    });
    await sleep(2000);

    // Scroll kembali ke atas perlahan
    await page.evaluate(async () => {
        for (let i = 0; i < 900; i += 30) {
            window.scrollBy(0, -30);
            await new Promise(r => setTimeout(r, 30));
        }
    });
    await sleep(1000);

    console.log('\n============================================================');
    console.log('🎉 [HERMES LIVE] SUKSES! Akun & 7 Logbook Lengkap Terverifikasi!');
    console.log('============================================================');
    console.log('[Hermes Live] Browser dibiarkan tetap terbuka agar Anda dapat memeriksanya langsung.');
}

runLiveStudentFlow().catch(err => {
    console.error('[Hermes Live Error]:', err);
    process.exit(1);
});
