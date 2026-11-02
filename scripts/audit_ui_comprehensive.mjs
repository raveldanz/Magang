import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');

const brainDir = 'C:\\Users\\TK ABA SBY 69 (3)\\.gemini\\antigravity\\brain\\c2e976da-daef-460d-9e0b-ba7235481f95';
const publicDir = path.resolve(projectRoot, 'public/test-artifacts');

if (!fs.existsSync(publicDir)) {
    fs.mkdirSync(publicDir, { recursive: true });
}
if (!fs.existsSync(brainDir)) {
    fs.mkdirSync(brainDir, { recursive: true });
}

function saveArtifact(srcFilename) {
    const srcPath = path.join(publicDir, srcFilename);
    const destPath = path.join(brainDir, srcFilename);
    try {
        if (fs.existsSync(srcPath)) {
            fs.copyFileSync(srcPath, destPath);
        }
    } catch (e) {
        console.error(`[Artifact Copy Warning] ${e.message}`);
    }
}

async function run() {
    console.log('🚀 Memulai Comprehensive Visual UI Audit dengan Chrome Channel...');
    const browser = await chromium.launch({
        headless: true,
        channel: 'chrome'
    });

    const issues = [];
    const report = (pageName, type, message) => {
        issues.push({ page: pageName, type, message });
        console.log(`[${type}] ${pageName}: ${message}`);
    };

    async function auditPage(page, name, filename) {
        // 1. Check console errors
        // 2. Check broken images
        const brokenImages = await page.evaluate(() => {
            const imgs = Array.from(document.querySelectorAll('img'));
            return imgs.filter(img => img.src && !img.complete || (img.naturalWidth === 0 && !img.src.endsWith('.svg'))).map(img => img.src);
        });
        if (brokenImages.length > 0) {
            report(name, 'BROKEN_IMAGE', `Ditemukan ${brokenImages.length} gambar rusak: ${brokenImages.slice(0, 3).join(', ')}`);
        }

        // 3. Check horizontal scroll overflow
        const hasOverflow = await page.evaluate(() => {
            return document.documentElement.scrollWidth > window.innerWidth || document.body.scrollWidth > window.innerWidth;
        });
        if (hasOverflow) {
            const overflowDetails = await page.evaluate(() => {
                const docWidth = window.innerWidth;
                const badEls = [];
                for (const el of document.querySelectorAll('*')) {
                    const rect = el.getBoundingClientRect();
                    if (rect.right > docWidth + 2) {
                        badEls.push({
                            tag: el.tagName,
                            class: (el.className || '').toString().slice(0, 50),
                            right: rect.right,
                            width: rect.width
                        });
                    }
                }
                return badEls.slice(0, 3);
            });
            report(name, 'OVERFLOW', `Horizontal overflow terdeteksi (${overflowDetails.map(e => `${e.tag}.${e.class} [right:${Math.round(e.right)}px]`).join('; ')})`);
        } else {
            console.log(`  ✓ Bebas horizontal overflow`);
        }

        // Take screenshot
        const shotPath = path.join(publicDir, filename);
        await page.screenshot({ path: shotPath, fullPage: true });
        saveArtifact(filename);
        console.log(`  📸 Screenshot tersimpan: ${filename}`);
    }

    // -------------------------------------------------------------
    // Test 1: Landing Page (Desktop & Mobile)
    // -------------------------------------------------------------
    console.log('\n--- 1. Testing Landing Page (/) ---');
    const guestDesktop = await browser.newPage({ viewport: { width: 1440, height: 900 } });
    guestDesktop.on('console', msg => {
        if (msg.type() === 'error') report('Landing Page Desktop', 'CONSOLE_ERROR', msg.text());
    });
    await guestDesktop.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle' });
    await auditPage(guestDesktop, 'Landing Page (Desktop)', 'audit_landing_desktop.png');

    const guestMobile = await browser.newPage({ viewport: { width: 390, height: 844 }, isMobile: true });
    guestMobile.on('console', msg => {
        if (msg.type() === 'error') report('Landing Page Mobile', 'CONSOLE_ERROR', msg.text());
    });
    await guestMobile.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle' });
    await auditPage(guestMobile, 'Landing Page (Mobile 390px)', 'audit_landing_mobile.png');

    // -------------------------------------------------------------
    // Test 2: Katalog Proyek Dinas (/proyek-dinas)
    // -------------------------------------------------------------
    console.log('\n--- 2. Testing Katalog Proyek Dinas (/proyek-dinas) ---');
    const projDesktop = await browser.newPage({ viewport: { width: 1440, height: 900 } });
    projDesktop.on('console', msg => {
        if (msg.type() === 'error') report('Katalog Desktop', 'CONSOLE_ERROR', msg.text());
    });
    await projDesktop.goto('http://127.0.0.1:8000/proyek-dinas', { waitUntil: 'networkidle' });
    await auditPage(projDesktop, 'Katalog Proyek Dinas (Desktop)', 'audit_katalog_desktop.png');

    const projMobile = await browser.newPage({ viewport: { width: 390, height: 844 }, isMobile: true });
    projMobile.on('console', msg => {
        if (msg.type() === 'error') report('Katalog Mobile', 'CONSOLE_ERROR', msg.text());
    });
    await projMobile.goto('http://127.0.0.1:8000/proyek-dinas', { waitUntil: 'networkidle' });
    await auditPage(projMobile, 'Katalog Proyek Dinas (Mobile 390px)', 'audit_katalog_mobile.png');

    // -------------------------------------------------------------
    // Test 3: Detail Proyek Dinas (/projects/2)
    // -------------------------------------------------------------
    console.log('\n--- 3. Testing Detail Proyek Dinas (/projects/2) ---');
    const detailDesktop = await browser.newPage({ viewport: { width: 1440, height: 900 } });
    detailDesktop.on('console', msg => {
        if (msg.type() === 'error') report('Detail Proyek Desktop', 'CONSOLE_ERROR', msg.text());
    });
    await detailDesktop.goto('http://127.0.0.1:8000/projects/2', { waitUntil: 'networkidle' });
    await auditPage(detailDesktop, 'Detail Proyek 2 (Desktop)', 'audit_project_detail_desktop.png');

    const detailMobile = await browser.newPage({ viewport: { width: 390, height: 844 }, isMobile: true });
    detailMobile.on('console', msg => {
        if (msg.type() === 'error') report('Detail Proyek Mobile', 'CONSOLE_ERROR', msg.text());
    });
    await detailMobile.goto('http://127.0.0.1:8000/projects/2', { waitUntil: 'networkidle' });
    await auditPage(detailMobile, 'Detail Proyek 2 (Mobile 390px)', 'audit_project_detail_mobile.png');

    // -------------------------------------------------------------
    // Test 4: Super Admin Applications & Users
    // -------------------------------------------------------------
    console.log('\n--- 4. Testing Super Admin Module (/admin/applications & /admin/users) ---');
    const adminCtx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const adminPage = await adminCtx.newPage();
    adminPage.on('console', msg => {
        if (msg.type() === 'error') report('Admin Portal', 'CONSOLE_ERROR', msg.text());
    });
    await adminPage.goto('http://127.0.0.1:8000/login');
    await adminPage.fill('input[name="email"]', 'admin@surabaya.go.id');
    await adminPage.fill('input[name="password"]', 'password');
    await adminPage.click('button[type="submit"]');
    await adminPage.waitForURL('**/admin/dashboard');

    await adminPage.goto('http://127.0.0.1:8000/admin/applications');
    await auditPage(adminPage, 'Admin Applications (Desktop)', 'audit_admin_applications_desktop.png');

    await adminPage.setViewportSize({ width: 390, height: 844 });
    await auditPage(adminPage, 'Admin Applications (Mobile 390px)', 'audit_admin_applications_mobile.png');

    await adminPage.setViewportSize({ width: 1440, height: 900 });
    await adminPage.goto('http://127.0.0.1:8000/admin/users');
    await auditPage(adminPage, 'Admin Users (Desktop)', 'audit_admin_users_desktop.png');

    // -------------------------------------------------------------
    // Test 5: Mahasiswa Dashboard & Final Report
    // -------------------------------------------------------------
    console.log('\n--- 5. Testing Mahasiswa Portal (/dashboard & /student/final-report) ---');
    const mhsCtx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const mhsPage = await mhsCtx.newPage();
    mhsPage.on('console', msg => {
        if (msg.type() === 'error') report('Mahasiswa Portal', 'CONSOLE_ERROR', msg.text());
    });
    await mhsPage.goto('http://127.0.0.1:8000/login');
    await mhsPage.fill('input[name="email"]', 'mahasiswa.aktif@unesa.ac.id');
    await mhsPage.fill('input[name="password"]', 'password');
    await mhsPage.click('button[type="submit"]');
    await mhsPage.waitForURL('**/dashboard');

    await auditPage(mhsPage, 'Mahasiswa Dashboard (Desktop)', 'audit_student_dashboard_desktop.png');

    await mhsPage.goto('http://127.0.0.1:8000/student/final-report');
    await auditPage(mhsPage, 'Mahasiswa Final Report (Desktop)', 'audit_student_final_report_desktop.png');

    await mhsPage.setViewportSize({ width: 390, height: 844 });
    await auditPage(mhsPage, 'Mahasiswa Final Report (Mobile 390px)', 'audit_student_final_report_mobile.png');

    await browser.close();

    console.log('\n=============================================================');
    console.log(`📊 AUDIT VISUAL SELESAI. Total Masalah Ditemukan: ${issues.length}`);
    console.log('=============================================================');
    if (issues.length > 0) {
        console.table(issues);
    } else {
        console.log('🎉 SELURUH HALAMAN BEBAS ERROR CONSOLE, BEBAS BROKEN IMAGE, DAN BEBAS HORIZONTAL OVERFLOW!');
    }
}

run().catch(err => {
    console.error('Fatal Audit Error:', err);
    process.exit(1);
});

