import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { assertManifestBuilt, assertVisualStyles } from './visual-guard.mjs';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');

const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const artifactDir = process.env.ARTIFACT_DIR || path.resolve(projectRoot, 'public/test-artifacts');

if (!fs.existsSync(artifactDir)) {
    fs.mkdirSync(artifactDir, { recursive: true });
}

async function runTier1Audit() {
    console.log('[Tier 1 - Puppeteer Core] Memulai audit visual login page...');

    // 1. HARD VISUAL GUARD: Periksa build manifest Vite terlebih dahulu
    console.log('[Tier 1] Memvalidasi status Vite manifest...');
    assertManifestBuilt(projectRoot);
    console.log('[Tier 1] [+] Manifest terverifikasi. Melanjutkan audit browser.');

    const browser = await puppeteer.launch({
        executablePath: chromePath,
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const report = {
        tier: 'Tier 1 (Puppeteer Core)',
        timestamp: new Date().toISOString(),
        desktop: null,
        mobile: null,
        wcagPass: false,
        overflowPass: false,
        visualGuardPass: false
    };

    try {
        // --- 1. DESKTOP VIEWPORT (1920x1080) ---
        console.log('[Tier 1] Membuka Desktop Viewport (1920x1080)...');
        const pageDesktop = await browser.newPage();
        await pageDesktop.setViewport({ width: 1920, height: 1080, deviceScaleFactor: 1 });
        await pageDesktop.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle2' });

        // Evaluasi Hard Visual Guard pada Desktop
        const desktopVisual = await assertVisualStyles(pageDesktop);
        const desktopScreenshot = path.join(artifactDir, 'tier1_desktop_1920x1080.png');
        await pageDesktop.screenshot({ path: desktopScreenshot, fullPage: true });

        report.desktop = {
            viewport: '1920x1080',
            bodyFont: desktopVisual.bodyFont,
            submitBtn: desktopVisual.primaryBtn,
            contrastRatio: desktopVisual.contrastRatio,
            wcagPass: desktopVisual.wcagPass,
            isHorizontallyCentered: desktopVisual.card?.isHorizontallyCentered ?? false,
            centerOffsetPx: desktopVisual.card?.centerOffsetPx ?? 0,
            hasHorizontalScroll: desktopVisual.hasHorizontalScroll,
            screenshot: desktopScreenshot
        };
        await pageDesktop.close();

        // --- 2. MOBILE VIEWPORT (375x812) ---
        console.log('[Tier 1] Membuka Mobile Viewport (375x812)...');
        const pageMobile = await browser.newPage();
        await pageMobile.setViewport({ width: 375, height: 812, deviceScaleFactor: 2, isMobile: true, hasTouch: true });
        await pageMobile.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle2' });

        // Evaluasi Hard Visual Guard pada Mobile
        const mobileVisual = await assertVisualStyles(pageMobile);
        const mobileScreenshot = path.join(artifactDir, 'tier1_mobile_375x812.png');
        await pageMobile.screenshot({ path: mobileScreenshot, fullPage: true });

        report.mobile = {
            viewport: '375x812',
            bodyFont: mobileVisual.bodyFont,
            submitBtn: mobileVisual.primaryBtn,
            contrastRatio: mobileVisual.contrastRatio,
            wcagPass: mobileVisual.wcagPass,
            isHorizontallyCentered: mobileVisual.card?.isHorizontallyCentered ?? false,
            centerOffsetPx: mobileVisual.card?.centerOffsetPx ?? 0,
            hasHorizontalScroll: mobileVisual.hasHorizontalScroll,
            screenshot: mobileScreenshot
        };
        await pageMobile.close();

        report.wcagPass = report.desktop.wcagPass && report.mobile.wcagPass;
        report.overflowPass = !report.desktop.hasHorizontalScroll && !report.mobile.hasHorizontalScroll;
        report.visualGuardPass = true;

        console.log('\n[Tier 1] HASIL AUDIT VISUAL RESMI:');
        console.log(JSON.stringify(report, null, 2));
    } finally {
        await browser.close();
    }
}

runTier1Audit().catch(err => {
    console.error('\n[-] Tier 1 Audit FAILED dengan Visual Guard Error:', err.message);
    process.exit(1);
});
