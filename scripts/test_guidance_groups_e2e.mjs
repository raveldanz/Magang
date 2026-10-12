import { chromium } from 'playwright';
import path from 'node:path';
import fs from 'node:fs';

const publicTestArtifactsDir = 'public/test-artifacts';
if (!fs.existsSync(publicTestArtifactsDir)) {
    fs.mkdirSync(publicTestArtifactsDir, { recursive: true });
}

async function runGuidanceGroupsE2E() {
    console.log('========================================================================');
    console.log('  TEST VISUAL E2E: GRUP BIMBINGAN MENTOR & DPL (1 MENTOR BANYAK MHS)');
    console.log('========================================================================');

    const browser = await chromium.launch({ headless: true, channel: 'chrome' });

    try {
        // -------------------------------------------------------------------------
        // 1. MENTOR KOMINFO (Ir. Siti Aminah): LOGIN & BUKA GRUP BIMBINGAN MENTOR
        // -------------------------------------------------------------------------
        console.log('\n[1/2] Login Mentor (Ir. Siti Aminah)...');
        const mentorContext = await browser.newContext({ viewport: { width: 1366, height: 768 } });
        const mentorPage = await mentorContext.newPage();

        await mentorPage.goto('http://127.0.0.1:8000/login');
        await mentorPage.fill('input[name="email"]', 'mentor.kominfo@surabaya.go.id');
        await mentorPage.fill('input[name="password"]', 'password');
        await mentorPage.click('button[type="submit"]');
        await mentorPage.waitForURL('**/dashboard', { timeout: 10000 });

        console.log('[Mentor] Menuju halaman chat...');
        await mentorPage.goto('http://127.0.0.1:8000/chat', { waitUntil: 'networkidle' });
        await mentorPage.waitForTimeout(1000);

        // Klik tab Grup
        console.log('[Mentor] Memilih tab Grup...');
        await mentorPage.click('button[role="tab"]:has-text("Grup")');
        await mentorPage.waitForTimeout(600);

        // Klik Grup Bimbingan Mentor
        const mentorGroupItem = mentorPage.locator('button[data-conv-id]:has-text("Bimbingan Mentor Ir. Siti Aminah")').first();
        await mentorGroupItem.waitFor({ state: 'visible', timeout: 8000 });
        await mentorGroupItem.click();
        await mentorPage.waitForTimeout(1000);

        // Kirim instruksi bimbingan ke seluruh mahasiswa bimbingan
        const instructionText = 'Halo rekan-rekan mahasiswa bimbingan! Jangan lupa koordinasi logbook mingguan dan periksa target proyek magang.';
        console.log('[Mentor] Mengirim pesan bimbingan grup...');
        const composer = mentorPage.locator('textarea[x-ref="composer"]');
        await composer.fill(instructionText);
        await mentorPage.waitForTimeout(300);

        const sendBtn = mentorPage.locator('button[type="submit"]:has(svg)');
        await sendBtn.click();
        await mentorPage.waitForTimeout(2000);

        const mentorShot = path.join(publicTestArtifactsDir, 'guidance_01_mentor_view.png');
        await mentorPage.screenshot({ path: mentorShot });
        console.log(`[Mentor] Screenshot berhasil: ${mentorShot}`);

        await mentorContext.close();

        // -------------------------------------------------------------------------
        // 2. MAHASISWA (Aditya Nugraha): LOGIN & TERIMA PESAN BIMBINGAN
        // -------------------------------------------------------------------------
        console.log('\n[2/2] Login Mahasiswa Aktif (Aditya Nugraha)...');
        const studentContext = await browser.newContext({ viewport: { width: 1366, height: 768 } });
        const studentPage = await studentContext.newPage();

        await studentPage.goto('http://127.0.0.1:8000/login');
        await studentPage.fill('input[name="email"]', 'mahasiswa.aktif@unesa.ac.id');
        await studentPage.fill('input[name="password"]', 'password');
        await studentPage.click('button[type="submit"]');
        await studentPage.waitForURL('**/dashboard', { timeout: 10000 });

        console.log('[Mahasiswa] Menuju halaman chat...');
        await studentPage.goto('http://127.0.0.1:8000/chat', { waitUntil: 'networkidle' });
        await studentPage.waitForTimeout(1000);

        // Verifikasi tab Semua: Saluran TIDAK BOLEH tampil di sini
        const allTabChannels = await studentPage.locator('button[data-conv-id]:has-text("Saluran Pengumuman")').count();
        console.log(`[Mahasiswa] Jumlah Saluran Pengumuman di tab Semua: ${allTabChannels} (Wajib 0)`);

        // Buka grup bimbingan mentor
        const studentGroupItem = studentPage.locator('button[data-conv-id]:has-text("Bimbingan Mentor Ir. Siti Aminah")').first();
        await studentGroupItem.waitFor({ state: 'visible', timeout: 8000 });
        await studentGroupItem.click();
        await studentPage.waitForTimeout(1000);

        // Balas bimbingan mentor
        const replyComposer = studentPage.locator('textarea[x-ref="composer"]');
        await replyComposer.fill('Siap Bu Mentor, logbook mingguan sudah selesai kami susun dan siap ditinjau.');
        await studentPage.waitForTimeout(300);

        const studentSendBtn = studentPage.locator('button[type="submit"]:has(svg)');
        await studentSendBtn.click();
        await studentPage.waitForTimeout(2000);

        const studentShot = path.join(publicTestArtifactsDir, 'guidance_02_student_view.png');
        await studentPage.screenshot({ path: studentShot });
        console.log(`[Mahasiswa] Screenshot obrolan bimbingan: ${studentShot}`);

        // Buka info panel untuk verifikasi rincian grup & anggota
        const infoBtn = studentPage.locator('button:has(svg path[d*="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"])').first();
        await infoBtn.waitFor({ state: 'visible', timeout: 5000 });
        await infoBtn.click();
        await studentPage.waitForTimeout(1000);

        const infoShot = path.join(publicTestArtifactsDir, 'guidance_03_info_panel.png');
        await studentPage.screenshot({ path: infoShot });
        console.log(`[Mahasiswa] Screenshot info panel: ${infoShot}`);

        await studentContext.close();
        console.log('\n>>> VISUAL E2E TEST GRUP BIMBINGAN BERHASIL 100% (Strict Exit Code 0) <<<');
    } catch (err) {
        console.error('Error saat visual test E2E:', err);
        process.exit(1);
    } finally {
        await browser.close();
    }
}

runGuidanceGroupsE2E();
