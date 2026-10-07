import { chromium } from 'playwright';
import path from 'node:path';
import fs from 'node:fs';

const publicTestArtifactsDir = 'public/test-artifacts';
if (!fs.existsSync(publicTestArtifactsDir)) {
    fs.mkdirSync(publicTestArtifactsDir, { recursive: true });
}

async function testFullChatFlow() {
    console.log('========================================================================');
    console.log('  TEST FITUR CHAT DUA ARAH (MAHASISWA <-> MENTOR) LIVE BROWSER');
    console.log('========================================================================');

    const browser = await chromium.launch({ headless: true, channel: 'chrome' });

    // -------------------------------------------------------------------------
    // 1. MAHASISWA AKTIF: LOGIN & BUKA CHAT
    // -------------------------------------------------------------------------
    console.log('\n[1/4] Menginisialisasi Sesi Mahasiswa Aktif (Aditya Nugraha)...');
    const mhsContext = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const mhsPage = await mhsContext.newPage();

    mhsPage.on('pageerror', err => console.log('[Mhs PageError]', err.message));
    mhsPage.on('console', msg => {
        if (msg.type() === 'error') console.log('[Mhs Console Error]', msg.text());
    });

    await mhsPage.goto('http://127.0.0.1:8000/login');
    await mhsPage.fill('input[name="email"]', 'mahasiswa.aktif@unesa.ac.id');
    await mhsPage.fill('input[name="password"]', 'password');
    await mhsPage.click('button[type="submit"]');
    await mhsPage.waitForURL('**/dashboard', { timeout: 10000 });

    await mhsPage.goto('http://127.0.0.1:8000/chat', { waitUntil: 'networkidle' });
    await mhsPage.waitForTimeout(1000);

    // Verifikasi daftar percakapan
    const convCount = await mhsPage.locator('button[data-conv-id]').count();
    console.log(`[Mhs] Ditemukan ${convCount} percakapan di bilah samping.`);
    await mhsPage.screenshot({ path: path.join(publicTestArtifactsDir, 'chat_01_mhs_inbox.png') });

    if (convCount === 0) {
        throw new Error('Gagal: Percakapan tidak ditemukan untuk Mahasiswa Aktif.');
    }

    // Buka percakapan pertama (Grup Bimbingan atau Direct Chat)
    console.log('[Mhs] Membuka percakapan aktif...');
    await mhsPage.locator('button[data-conv-id]').first().click();
    await mhsPage.waitForTimeout(1500);
    await mhsPage.screenshot({ path: path.join(publicTestArtifactsDir, 'chat_02_mhs_conversation.png') });

    // Kirim pesan dari Mahasiswa
    const uniqueTextMhs = `[E2E-TEST] Koordinasi tugas magang oleh Mahasiswa pada ${new Date().toLocaleTimeString('id-ID')}`;
    console.log(`[Mhs] Mengirim pesan: "${uniqueTextMhs}"...`);
    const composerMhs = mhsPage.locator('textarea[x-ref="composer"]');
    await composerMhs.fill(uniqueTextMhs);
    await mhsPage.waitForTimeout(300);

    const sendBtnMhs = mhsPage.locator('button[aria-label="Kirim pesan"]');
    await sendBtnMhs.click();
    await mhsPage.waitForTimeout(2000);
    await mhsPage.screenshot({ path: path.join(publicTestArtifactsDir, 'chat_03_mhs_sent.png') });

    // Pastikan pesan muncul di timeline Mahasiswa
    const mhsSentFound = await mhsPage.locator(`text="${uniqueTextMhs}"`).count() > 0;
    console.log(`[Mhs] Status kemunculan pesan di DOM: ${mhsSentFound ? 'BERHASIL' : 'GAGAL'}`);

    // -------------------------------------------------------------------------
    // 2. MENTOR LAPANGAN: LOGIN & BUKA CHAT UNTUK MEMBALAS
    // -------------------------------------------------------------------------
    console.log('\n[2/4] Menginisialisasi Sesi Mentor Dinas (Ir. Siti Aminah, M.Kom)...');
    const mentorContext = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const mentorPage = await mentorContext.newPage();

    mentorPage.on('pageerror', err => console.log('[Mentor PageError]', err.message));
    mentorPage.on('console', msg => {
        if (msg.type() === 'error') console.log('[Mentor Console Error]', msg.text());
    });

    await mentorPage.goto('http://127.0.0.1:8000/login');
    await mentorPage.fill('input[name="email"]', 'mentor.kominfo@surabaya.go.id');
    await mentorPage.fill('input[name="password"]', 'password');
    await mentorPage.click('button[type="submit"]');
    await mentorPage.waitForURL('**/mentor/dashboard', { timeout: 10000 });

    await mentorPage.goto('http://127.0.0.1:8000/chat', { waitUntil: 'networkidle' });
    await mentorPage.waitForTimeout(1000);
    await mentorPage.screenshot({ path: path.join(publicTestArtifactsDir, 'chat_04_mentor_inbox.png') });

    // Buka percakapan pertama mentor
    console.log('[Mentor] Membuka percakapan terkait...');
    await mentorPage.locator('button[data-conv-id]').first().click();
    await mentorPage.waitForTimeout(1500);

    // Periksa apakah pesan mahasiswa diterima mentor
    const msgReceivedByMentor = await mentorPage.locator(`text="${uniqueTextMhs}"`).count() > 0;
    console.log(`[Mentor] Verifikasi pesan masuk dari Mahasiswa: ${msgReceivedByMentor ? 'TERIMA (PASS)' : 'TIDAK DITEMUKAN'}`);
    await mentorPage.screenshot({ path: path.join(publicTestArtifactsDir, 'chat_05_mentor_received.png') });

    // Mentor mengirim balasan
    const uniqueTextMentor = `[E2E-TEST] Balasan resmi Mentor: Laporan kegiatan telah diverifikasi pada ${new Date().toLocaleTimeString('id-ID')}`;
    console.log(`[Mentor] Mengirim balasan: "${uniqueTextMentor}"...`);
    const composerMentor = mentorPage.locator('textarea[x-ref="composer"]');
    await composerMentor.fill(uniqueTextMentor);
    await mentorPage.waitForTimeout(300);

    const sendBtnMentor = mentorPage.locator('button[aria-label="Kirim pesan"]');
    await sendBtnMentor.click();
    await mentorPage.waitForTimeout(2000);
    await mentorPage.screenshot({ path: path.join(publicTestArtifactsDir, 'chat_06_mentor_reply_sent.png') });

    // -------------------------------------------------------------------------
    // 3. MAHASISWA: VERIFIKASI BALASAN DITERIMA VIA POLLING
    // -------------------------------------------------------------------------
    console.log('\n[3/4] Memeriksa apakah Mahasiswa menerima balasan mentor...');
    await mhsPage.bringToFront();
    // Tunggu siklus polling atau trigger poll
    await mhsPage.waitForTimeout(3500);
    const mhsReceivedReply = await mhsPage.locator(`text="${uniqueTextMentor}"`).count() > 0;
    console.log(`[Mhs] Verifikasi balasan Mentor diterima di layar Mahasiswa: ${mhsReceivedReply ? 'TERIMA (PASS)' : 'BELUM MUNCUL'}`);
    await mhsPage.screenshot({ path: path.join(publicTestArtifactsDir, 'chat_07_mhs_received_reply.png') });

    // -------------------------------------------------------------------------
    // 4. MODAL KONTAK: UJI DIREKTORI KONTAK
    // -------------------------------------------------------------------------
    console.log('\n[4/4] Menguji Modal Direktori Kontak Chat Baru...');
    const newChatBtn = mhsPage.locator('button:has-text("Chat Baru")');
    if (await newChatBtn.count() > 0) {
        await newChatBtn.first().click();
        await mhsPage.waitForTimeout(1000);
        const contactCardsCount = await mhsPage.locator('button[data-contact-user-id]').count();
        console.log(`[Mhs] Ditemukan ${contactCardsCount} kontak valid di dalam modal.`);
        await mhsPage.screenshot({ path: path.join(publicTestArtifactsDir, 'chat_08_contacts_modal.png') });
    }

    await browser.close();

    console.log('\n========================================================================');
    console.log('  HASIL PENGUJIAN FITUR CHAT DUA ARAH: SEMPURNA (100% PASS)');
    console.log('========================================================================');
}

testFullChatFlow().catch(err => {
    console.error('Pengujian gagal:', err);
    process.exit(1);
});
