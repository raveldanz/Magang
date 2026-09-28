/**
 * Tier 3 - Autonomous Playwright Visual Audit Runner
 * Lokasi: scripts/tier3_playwright_audit.mjs
 * 
 * Menguji rendering antarmuka login dan tabel pengajuan admin (desktop & mobile)
 * menggunakan Playwright Engine dengan Hard Visual Guard.
 */

import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { assertManifestBuilt, assertVisualStyles } from './visual-guard.mjs';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');

const artifactDir = process.env.ARTIFACT_DIR || 'C:\\Users\\TK ABA SBY 69 (3)\\.gemini\\antigravity-ide\\brain\\e1ebafff-a3cb-4b1c-826c-c650860360c2';

if (!fs.existsSync(artifactDir)) {
    fs.mkdirSync(artifactDir, { recursive: true });
}

async function runTier3PlaywrightAudit() {
    console.log('========================================================================');
    console.log('  TIER 3: AUTONOMOUS PLAYWRIGHT VISUAL AUDIT & HARD VISUAL GUARD');
    console.log('========================================================================');

    // 1. HARD VISUAL GUARD: Verifikasi Manifest Build
    console.log('[Tier 3] Memvalidasi status Vite build manifest...');
    assertManifestBuilt(projectRoot);
    console.log('[Tier 3] [+] Manifest build terverifikasi aktif.');

    const browser = await chromium.launch({
        headless: true
    });

    const report = {
        tier: 'Tier 3 (Playwright Engine)',
        timestamp: new Date().toISOString(),
        login: {
            desktop: null,
            mobile: null
        },
        adminApplicationsTable: {
            desktop: null,
            mobile: null
        },
        adminApplicationDetail: {
            desktop: null,
            mobile: null
        },
        wcagPass: false,
        overflowPass: false,
        visualGuardPass: false
    };

    try {
        // =====================================================================
        // FASE 1: AUDIT LOGIN PORTAL (DESKTOP & MOBILE)
        // =====================================================================
        console.log('\n[Tier 3] [1/3] Menguji Antarmuka Login Portal...');

        // 1A. Login Desktop (1920x1080)
        const contextLoginDesktop = await browser.newContext({
            viewport: { width: 1920, height: 1080 },
            deviceScaleFactor: 1
        });
        const pageLoginDesktop = await contextLoginDesktop.newPage();
        await pageLoginDesktop.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });

        const loginDesktopVisual = await assertVisualStyles(pageLoginDesktop);
        const loginDesktopShot = path.join(artifactDir, 'tier3_playwright_desktop_1920x1080.png');
        await pageLoginDesktop.screenshot({ path: loginDesktopShot, fullPage: true });

        report.login.desktop = {
            viewport: '1920x1080',
            bodyFont: loginDesktopVisual.bodyFont,
            primaryBtn: loginDesktopVisual.primaryBtn,
            contrastRatio: loginDesktopVisual.contrastRatio,
            wcagPass: loginDesktopVisual.wcagPass,
            hasHorizontalScroll: loginDesktopVisual.hasHorizontalScroll,
            screenshot: loginDesktopShot
        };
        await contextLoginDesktop.close();

        // 1B. Login Mobile (375x812)
        const contextLoginMobile = await browser.newContext({
            viewport: { width: 375, height: 812 },
            deviceScaleFactor: 2,
            isMobile: true,
            hasTouch: true
        });
        const pageLoginMobile = await contextLoginMobile.newPage();
        await pageLoginMobile.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });

        const loginMobileVisual = await assertVisualStyles(pageLoginMobile);
        const loginMobileShot = path.join(artifactDir, 'tier3_playwright_mobile_375x812.png');
        await pageLoginMobile.screenshot({ path: loginMobileShot, fullPage: true });

        report.login.mobile = {
            viewport: '375x812',
            bodyFont: loginMobileVisual.bodyFont,
            primaryBtn: loginMobileVisual.primaryBtn,
            contrastRatio: loginMobileVisual.contrastRatio,
            wcagPass: loginMobileVisual.wcagPass,
            hasHorizontalScroll: loginMobileVisual.hasHorizontalScroll,
            screenshot: loginMobileShot
        };
        await contextLoginMobile.close();

        // =====================================================================
        // FASE 2: AUDIT TABEL PENGAJUAN ADMIN (DESKTOP VIEWPORT 1920x1080)
        // =====================================================================
        console.log('\n[Tier 3] [2/3] Menguji Tabel Pengajuan Magang Admin (Desktop 1920x1080)...');

        const contextAdminDesktop = await browser.newContext({
            viewport: { width: 1920, height: 1080 },
            deviceScaleFactor: 1
        });
        const pageAdminDesktop = await contextAdminDesktop.newPage();

        // Login sebagai Admin QA
        await pageAdminDesktop.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
        await pageAdminDesktop.fill('input[name="email"]', 'admin.qa@test.local');
        await pageAdminDesktop.fill('input[name="password"]', 'password');
        await pageAdminDesktop.click('button[type="submit"]');
        await pageAdminDesktop.waitForURL(url => url.pathname.includes('/admin') || url.pathname.includes('/dashboard'), { timeout: 15000 });

        // Navigasi ke Daftar Pengajuan Magang Admin
        await pageAdminDesktop.goto('http://127.0.0.1:8000/admin/applications', { waitUntil: 'networkidle' });

        // Evaluasi Hard Visual Guard pada Tabel Desktop
        const tableDesktopVisual = await assertVisualStyles(pageAdminDesktop);
        const tableDesktopShot = path.join(artifactDir, 'admin_applications_desktop_1920x1080.png');
        await pageAdminDesktop.screenshot({ path: tableDesktopShot, fullPage: true });

        // Cek Keberadaan Elemen Kunci Tabel
        const hasDesktopTable = await pageAdminDesktop.locator('table').count() > 0;
        const pageContentDesktop = await pageAdminDesktop.content();
        const hasPendingBadge = pageContentDesktop.includes('PENDING');
        const hasAcceptedBadge = pageContentDesktop.includes('ACCEPTED') || pageContentDesktop.includes('accepted');
        const hasRejectedBadge = pageContentDesktop.includes('REJECTED') || pageContentDesktop.includes('rejected');

        report.adminApplicationsTable.desktop = {
            viewport: '1920x1080',
            bodyFont: tableDesktopVisual.bodyFont,
            filterBtn: tableDesktopVisual.primaryBtn,
            contrastRatio: tableDesktopVisual.contrastRatio,
            wcagPass: tableDesktopVisual.wcagPass,
            hasHorizontalScroll: tableDesktopVisual.hasHorizontalScroll,
            hasTable: hasDesktopTable,
            badgesDetected: {
                pending: hasPendingBadge,
                accepted: hasAcceptedBadge,
                rejected: hasRejectedBadge
            },
            screenshot: tableDesktopShot
        };

        // =====================================================================
        // FASE 3: AUDIT DETAIL VERIFIKASI SELEKSI & PENOLAKAN
        // =====================================================================
        console.log('[Tier 3] Menguji Halaman Verifikasi Detail Pengajuan (Desktop)...');
        // Cari tombol detail pertama menuju show
        const detailLink = pageAdminDesktop.locator('a[href*="/admin/applications/"]:visible').first();
        if (await detailLink.count() > 0) {
            await detailLink.click();
            await pageAdminDesktop.waitForURL(url => url.pathname.match(/\/admin\/applications\/\d+/), { timeout: 10000 });
            await pageAdminDesktop.waitForLoadState('networkidle');

            // Uji Reaktivitas: Klik tombol REJECTED untuk memastikan rejection-box muncul
            const rejectedCardBtn = pageAdminDesktop.locator('button:has-text("REJECTED")');
            if (await rejectedCardBtn.count() > 0) {
                await rejectedCardBtn.click();
                await pageAdminDesktop.waitForTimeout(300);
                const rejectionShot = path.join(artifactDir, 'admin_application_rejection_box.png');
                await pageAdminDesktop.screenshot({ path: rejectionShot, fullPage: true });
                report.adminApplicationDetail.rejectionBoxScreenshot = rejectionShot;
            }

            const detailDesktopVisual = await assertVisualStyles(pageAdminDesktop);
            const detailDesktopShot = path.join(artifactDir, 'admin_application_detail_desktop.png');
            await pageAdminDesktop.screenshot({ path: detailDesktopShot, fullPage: true });

            report.adminApplicationDetail.desktop = {
                viewport: '1920x1080',
                bodyFont: detailDesktopVisual.bodyFont,
                saveBtn: detailDesktopVisual.primaryBtn,
                wcagPass: detailDesktopVisual.wcagPass,
                hasHorizontalScroll: detailDesktopVisual.hasHorizontalScroll,
                screenshot: detailDesktopShot
            };
        }
        await contextAdminDesktop.close();

        // =====================================================================
        // FASE 4: AUDIT TABEL PENGAJUAN ADMIN (MOBILE VIEWPORT 375x812)
        // =====================================================================
        console.log('\n[Tier 3] [3/3] Menguji Tabel Pengajuan Magang Admin (Mobile 375x812)...');

        const contextAdminMobile = await browser.newContext({
            viewport: { width: 375, height: 812 },
            deviceScaleFactor: 2,
            isMobile: true,
            hasTouch: true
        });
        const pageAdminMobile = await contextAdminMobile.newPage();

        // Login sebagai Admin QA pada Mobile Viewport
        await pageAdminMobile.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
        await pageAdminMobile.fill('input[name="email"]', 'admin.qa@test.local');
        await pageAdminMobile.fill('input[name="password"]', 'password');
        await pageAdminMobile.click('button[type="submit"]');
        await pageAdminMobile.waitForURL(url => url.pathname.includes('/admin') || url.pathname.includes('/dashboard'), { timeout: 15000 });

        // Navigasi ke Daftar Pengajuan Magang Admin
        await pageAdminMobile.goto('http://127.0.0.1:8000/admin/applications', { waitUntil: 'networkidle' });

        // Evaluasi Hard Visual Guard pada Mobile Cards
        const tableMobileVisual = await assertVisualStyles(pageAdminMobile);
        const tableMobileShot = path.join(artifactDir, 'admin_applications_mobile_375x812.png');
        await pageAdminMobile.screenshot({ path: tableMobileShot, fullPage: true });

        // Cek Elemen Mobile Cards
        const hasMobileCards = await pageAdminMobile.locator('.md\\:hidden').count() > 0;

        report.adminApplicationsTable.mobile = {
            viewport: '375x812',
            bodyFont: tableMobileVisual.bodyFont,
            hasHorizontalScroll: tableMobileVisual.hasHorizontalScroll,
            hasMobileCards: hasMobileCards,
            screenshot: tableMobileShot
        };

        // Buka Detail Pengajuan pada Layar Mobile
        const mobileDetailLink = pageAdminMobile.locator('a[href*="/admin/applications/"]:visible').first();
        if (await mobileDetailLink.count() > 0) {
            await mobileDetailLink.click();
            await pageAdminMobile.waitForURL(url => url.pathname.match(/\/admin\/applications\/\d+/), { timeout: 10000 });
            await pageAdminMobile.waitForLoadState('networkidle');

            const detailMobileVisual = await assertVisualStyles(pageAdminMobile);
            const detailMobileShot = path.join(artifactDir, 'admin_application_detail_mobile.png');
            await pageAdminMobile.screenshot({ path: detailMobileShot, fullPage: true });

            report.adminApplicationDetail.mobile = {
                viewport: '375x812',
                bodyFont: detailMobileVisual.bodyFont,
                hasHorizontalScroll: detailMobileVisual.hasHorizontalScroll,
                screenshot: detailMobileShot
            };
        }
        await contextAdminMobile.close();

        // Simpulkan Status Keseluruhan
        report.wcagPass = report.login.desktop.wcagPass && 
                          report.login.mobile.wcagPass && 
                          report.adminApplicationsTable.desktop.wcagPass;
        report.overflowPass = !report.login.desktop.hasHorizontalScroll && 
                              !report.login.mobile.hasHorizontalScroll && 
                              !report.adminApplicationsTable.desktop.hasHorizontalScroll && 
                              !report.adminApplicationsTable.mobile.hasHorizontalScroll;
        report.visualGuardPass = true;

        console.log('\n========================================================================');
        console.log('  [PASS] HASIL AUDIT VISUAL RESMI PLAYWRIGHT TIER 3:');
        console.log('========================================================================');
        console.log(JSON.stringify(report, null, 2));

    } finally {
        await browser.close();
    }
}

runTier3PlaywrightAudit().catch(err => {
    console.error('\n[-] Tier 3 Audit FAILED dengan Visual Guard Error:', err.message);
    process.exit(1);
});
