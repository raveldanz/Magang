/**
 * Audit Visual Tahap 1: Halaman Publik, Galeri Proyek, Autentikasi & Verifikasi QR
 * Lokasi: scripts/ui_stage1_audit.mjs
 *
 * Penggunaan:
 *   Siapkan STAGE1_LETTER_TOKEN dan STAGE1_CERTIFICATE_TOKEN dari fixture lokal.
 *   node scripts/ui_stage1_audit.mjs --tag before
 *   node scripts/ui_stage1_audit.mjs --tag after --strict
 *
 * Hasil: public/test-artifacts/ui-stage1/<tag>-*.png + <tag>-report.json
 */

import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { assertManifestBuilt, assertVisualStyles } from './visual-guard.mjs';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(__dirname, '..');
const args = process.argv.slice(2);
const argValue = (flag, fallback) => {
    const i = args.indexOf(flag);
    return i !== -1 && args[i + 1] ? args[i + 1] : fallback;
};
const tag = argValue('--tag', 'before');
const strict = args.includes('--strict');
const baseUrl = argValue('--base', 'http://127.0.0.1:8000');
const outDir = path.resolve(projectRoot, 'public/test-artifacts/ui-stage1');
fs.mkdirSync(outDir, { recursive: true });

const VIEWPORTS = {
    desktop: { width: 1440, height: 900 },
    mobile: { width: 375, height: 812 },
};

function requiredFixture(name) {
    const value = process.env[name];
    if (!value) throw new Error(`Fixture lokal belum tersedia: ${name}`);
    return encodeURIComponent(value);
}

const groupPath = `/projects/${process.env.STAGE1_GROUP_PROJECT_ID || '15'}`;
const soloPath = `/projects/${process.env.STAGE1_SOLO_PROJECT_ID || '17'}`;
const SCENARIOS = [
    { key: 'welcome-guest', path: '/', role: null, fullPage: true, requireBtn: false },
    { key: 'welcome-auth', path: '/', role: 'mahasiswa.aktif@unesa.ac.id', fullPage: true, requireBtn: false },
    { key: 'projects-index', path: '/proyek-dinas', role: null, fullPage: true, requireBtn: true },
    { key: 'projects-index-filtered', path: '/proyek-dinas?search=Sistem&category=teknologi&sort=popular', role: null, fullPage: true, requireBtn: true },
    { key: 'projects-index-empty', path: '/proyek-dinas?search=zzztidakadaproyekzzz', role: null, fullPage: true, requireBtn: true },
    { key: 'project-detail-group', path: groupPath, role: null, fullPage: true, requireBtn: true },
    { key: 'project-detail-solo', path: soloPath, role: null, fullPage: true, requireBtn: true },
    { key: 'project-detail-admin', path: groupPath, role: 'admin@surabaya.go.id', fullPage: true, requireBtn: false },
    { key: 'auth-login', path: '/login', role: null, fullPage: true, requireBtn: true },
    { key: 'auth-register-student', path: '/register', role: null, fullPage: true, requireBtn: true },
    { key: 'auth-register-advisor', path: '/register', role: null, fullPage: true, requireBtn: true, action: 'register-advisor' },
    { key: 'auth-forgot-password', path: '/forgot-password', role: null, fullPage: true, requireBtn: true },
    { key: 'auth-reset-password', path: '/reset-password/demo-token-stage1?email=mahasiswa.aktif@unesa.ac.id', role: null, fullPage: true, requireBtn: true },
    { key: 'auth-verify-email', path: '/verify-email', role: 'admin.diskominfo@surabaya.go.id', fullPage: true, requireBtn: true },
    { key: 'auth-confirm-password', path: '/confirm-password', role: 'mahasiswa.aktif@unesa.ac.id', fullPage: true, requireBtn: true },
    { key: 'verify-letter', path: `/verify-letter/${requiredFixture('STAGE1_LETTER_TOKEN')}`, role: null, fullPage: true, requireBtn: false },
    { key: 'verify-certificate', path: `/verify-certificate/${requiredFixture('STAGE1_CERTIFICATE_TOKEN')}`, role: null, fullPage: true, requireBtn: false },
];

async function login(page, email) {
    await page.goto(`${baseUrl}/login`, { waitUntil: 'domcontentloaded', timeout: 60000 });
    const token = await page.locator('form input[name="_token"]').inputValue();
    const response = await page.request.post(`${baseUrl}/login`, {
        form: { _token: token, email, password: process.env.STAGE1_DEMO_PASSWORD || 'password' },
        maxRedirects: 0,
        timeout: 60000,
    });
    const destination = response.headers().location;
    if (response.status() !== 302 || !destination || new URL(destination, baseUrl).pathname === '/login') {
        throw new Error(`Login akun demo gagal (HTTP ${response.status()}).`);
    }

}

async function measure(page) {
    return page.evaluate(() => {
        const vw = window.innerWidth;
        const offenders = [];
        for (const el of document.querySelectorAll('body *')) {
            const r = el.getBoundingClientRect();
            if (r.width === 0 || r.height === 0) continue;
            const cs = getComputedStyle(el);
            if (cs.visibility === 'hidden' || cs.display === 'none' || cs.position === 'fixed') continue;
            if (r.right > vw + 1) {
                let clipped = false;
                for (let p = el.parentElement; p && p !== document.body; p = p.parentElement) {
                    const pcs = getComputedStyle(p);
                    if (['auto', 'scroll', 'hidden', 'clip'].includes(pcs.overflowX) && p.getBoundingClientRect().right <= vw + 1) {
                        clipped = true;
                        break;
                    }
                }
                if (!clipped) {
                    offenders.push(`${el.tagName.toLowerCase()}.${String(el.className).slice(0, 70)} right=${Math.round(r.right)}`);
                }
            }
        }

        const brokenImages = [];
        const distortedImages = [];
        for (const img of document.querySelectorAll('img')) {
            const r = img.getBoundingClientRect();
            const cs = getComputedStyle(img);
            if (cs.display === 'none' || cs.visibility === 'hidden' || r.width === 0 || r.height === 0) continue;
            if (img.complete && img.naturalWidth === 0) {
                brokenImages.push(img.getAttribute('src') || img.alt);
            } else if (img.naturalWidth > 0 && img.naturalHeight > 0 && cs.objectFit !== 'cover' && cs.objectFit !== 'contain') {
                const natRatio = img.naturalWidth / img.naturalHeight;
                const renRatio = r.width / r.height;
                if (Math.abs(natRatio - renRatio) / natRatio > 0.15) {
                    distortedImages.push(`${img.getAttribute('src')} nat=${natRatio.toFixed(2)} ren=${renRatio.toFixed(2)}`);
                }
            }
        }

        const smallTouchTargets = [];
        if (vw <= 480) {
            for (const el of document.querySelectorAll('a, button, input[type="submit"], select')) {
                const r = el.getBoundingClientRect();
                const cs = getComputedStyle(el);
                if (cs.display === 'none' || cs.visibility === 'hidden' || r.width === 0 || r.height === 0) continue;
                // Skip inline text links inside paragraphs
                if (el.tagName === 'A' && cs.display === 'inline') continue;
                if (r.height < 38) {
                    const label = (el.innerText || el.getAttribute('aria-label') || el.getAttribute('title') || el.name || el.tagName).trim().slice(0, 35);
                    smallTouchTargets.push(`${el.tagName.toLowerCase()}[${label}] ${Math.round(r.width)}x${Math.round(r.height)}`);
                }
            }
        }

        return {
            innerWidth: vw,
            scrollWidth: document.documentElement.scrollWidth,
            bodyScrollWidth: document.body.scrollWidth,
            overflow: document.documentElement.scrollWidth > vw || document.body.scrollWidth > vw,
            offenders: offenders.slice(0, 10),
            brokenImages: brokenImages.slice(0, 10),
            distortedImages: distortedImages.slice(0, 10),
            smallTouchTargets: smallTouchTargets.slice(0, 15),
            bodyFont: getComputedStyle(document.body).fontFamily,
        };
    });
}

async function run() {
    assertManifestBuilt(projectRoot);
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const report = { tag, generatedAt: new Date().toISOString(), results: [] };
    const consoleErrors = [];

    try {
        for (const [vpName, viewport] of Object.entries(VIEWPORTS)) {
            const sessions = {};
            let activeScenario;
            for (const sc of SCENARIOS) {
                activeScenario = sc.key;
                const sessionKey = sc.role || 'guest';
                if (!sessions[sessionKey]) {
                    const ctx = await browser.newContext({ viewport, deviceScaleFactor: 1, locale: 'id-ID' });
                    const pg = await ctx.newPage();
                    pg.on('console', (m) => {
                        if (m.type() === 'error') consoleErrors.push(`[${vpName}/${sessionKey}/${activeScenario}] ${m.text()} (${(m.location().url || "").split("?")[0].replace(/(verify-(?:letter|certificate)\/)[^/]+/g, "$1[token]")})`);
                    });
                    pg.on('pageerror', (e) => consoleErrors.push(`[${vpName}/${sessionKey}/${activeScenario}] PAGEERROR ${e.message}`));
                    if (sc.role) await login(pg, sc.role);
                    sessions[sessionKey] = pg;
                }
                const page = sessions[sessionKey];
                const destination = new URL(sc.path, baseUrl);
                const response = await page.goto(destination.href, { waitUntil: 'load', timeout: 120000 });
                if (!response?.ok() || page.url() !== destination.href) {
                    throw new Error(`${vpName}/${sc.key}: halaman tujuan gagal dibuka (HTTP ${response?.status()}).`);
                }
                await page.waitForTimeout(800);

                if (sc.action === 'register-advisor') {
                    await page.getByRole('button', { name: /Pembimbing/i }).click();
                    await page.waitForTimeout(350);
                }

                // Hard Visual Guard
                let styleGuard = null;
                try {
                    styleGuard = await assertVisualStyles(page, { requireButton: sc.requireBtn });
                } catch (err) {
                    styleGuard = { error: err.message };
                    consoleErrors.push(`[${vpName}/${sc.key}] STYLE_GUARD ${err.message}`);
                }

                const shot = path.join(outDir, `${tag}-${sc.key}-${vpName}.png`);
                await page.screenshot({ path: shot, fullPage: sc.fullPage });
                const m = await measure(page);
                const entry = {
                    scenario: sc.key,
                    viewport: vpName,
                    url: sc.key.startsWith('verify-') ? `/${sc.key}/[token]` : page.url(),
                    screenshot: path.relative(projectRoot, shot),
                    ...m,
                    styleGuardError: styleGuard?.error || null,
                };

                entry.states = [];
                async function captureState(name, panel, fileName = `${name}-${vpName}`) {
                    await panel.waitFor({ state: 'visible' });
                    await page.waitForTimeout(250);
                    const bounds = await panel.boundingBox();
                    const screenshot = path.join(outDir, `${tag}-${fileName}.png`);
                    await page.screenshot({ path: screenshot, fullPage: false });
                    const metrics = await measure(page);
                    entry.states.push({
                        name,
                        screenshot: path.relative(projectRoot, screenshot),
                        overflow: metrics.overflow || bounds.x < -1 || bounds.x + bounds.width > viewport.width + 1,
                        scrollWidth: metrics.scrollWidth,
                        brokenImages: metrics.brokenImages,
                    });
                }

                if (vpName === 'mobile' && sc.key === 'welcome-guest') {
                    const menu = page.getByRole('button', { name: /Toggle navigasi menu|Buka Menu/i });
                    await menu.click();
                    await captureState('welcome-mobile-menu', page.locator('[x-show="mobileMenuOpen"]').last(), 'welcome-mobile-menu');
                    await menu.click();
                    await page.getByRole('button', { name: /Sisa Kuota/i }).click();
                    await captureState('welcome-quota-modal', page.getByRole('heading', { name: 'Bidang Pilihan Magang' }).locator('../../..'));
                    await page.getByRole('button', { name: /^Tutup$/i }).click();
                }

                if (sc.key === 'auth-register-advisor') {
                    const field = page.locator('#university');
                    const list = page.getByRole('listbox');
                    await field.click();
                    await list.scrollIntoViewIfNeeded();
                    const option = list.getByRole('option').first();
                    await option.waitFor({ state: 'visible' });
                    const choice = (await option.innerText()).trim();
                    await field.fill(choice.slice(0, 5));
                    await list.scrollIntoViewIfNeeded();
                    await captureState('auth-register-advisor-dropdown', list);
                    await list.getByRole('option', { name: choice, exact: true }).first().click();
                    if (await field.inputValue() !== choice) throw new Error('Pilihan institusi gagal diterapkan.');
                    await field.fill('Institusi Contoh Audit XYZ');
                    await captureState('auth-register-advisor-manual', list.getByText(/Institusi belum ada/));
                    await field.press('Escape');
                    await list.waitFor({ state: 'hidden' });
                }

                if (sc.key === 'project-detail-group') {
                    const share = page.getByRole('button', { name: 'Bagikan', exact: true });
                    await share.click();
                    const shareMenu = page.getByRole('button', { name: 'Salin Tautan', exact: true }).locator('..');
                    await shareMenu.scrollIntoViewIfNeeded();
                    await captureState('project-detail-share', shareMenu);
                    await share.click();
                    const comment = page.locator('textarea[name="comment"]');
                    await comment.scrollIntoViewIfNeeded();
                    await captureState('project-detail-discussion', comment.locator('xpath=ancestor::form'));
                }

                if (sc.key === 'project-detail-admin') {
                    await page.getByRole('button', { name: 'Kelola Proyek' }).click();
                    await captureState('project-detail-manage', page.locator('[x-show="menuOpen"]'));
                    await page.keyboard.press('Escape');
                }

                if (sc.key === 'projects-index') {
                    const year = page.locator('select[name="year"]');
                    const firstYear = await year.locator('option').nth(1).getAttribute('value');
                    await Promise.all([page.waitForURL(url => url.searchParams.get('year') === firstYear), year.selectOption(firstYear)]);
                    for (const sort of ['discussed', 'oldest']) {
                        await Promise.all([
                            page.waitForURL(url => url.searchParams.get('sort') === sort),
                            page.locator('select[name="sort"]').selectOption(sort),
                        ]);
                        await captureState(`projects-index-year-${sort}`, page.locator('select[name="sort"]').locator('xpath=ancestor::form'));
                    }
                    await page.getByRole('link', { name: 'Reset', exact: true }).first().click();
                    await page.waitForURL(url => url.pathname === sc.path && !url.search);
                }

                report.results.push(entry);
                const flag = m.overflow ? 'OVERFLOW' : 'ok';
                console.log(
                    `[${tag}] ${vpName.padEnd(7)} ${sc.key.padEnd(25)} scrollW=${m.scrollWidth}/${m.innerWidth} ${flag}` +
                    (m.brokenImages.length ? ` BROKEN_IMG=${m.brokenImages.length}` : '') +
                    (m.distortedImages.length ? ` DISTORTED=${m.distortedImages.length}` : '') +
                    (m.smallTouchTargets.length ? ` SMALL_TOUCH=${m.smallTouchTargets.length}` : '')
                );
                if (m.offenders.length) console.log('   offenders:', m.offenders.slice(0, 5).join(' | '));
                if (m.brokenImages.length) console.log('   brokenImages:', m.brokenImages.join(' | '));
                if (m.smallTouchTargets.length) console.log('   smallTouch:', m.smallTouchTargets.slice(0, 6).join(' | '));
            }
            for (const pg of Object.values(sessions)) await pg.context().close();
        }
    } finally {
        await browser.close();
    }

    report.consoleErrors = consoleErrors;
    fs.writeFileSync(path.join(outDir, `${tag}-report.json`), JSON.stringify(report, null, 2));

    const mobileOverflow = report.results.filter((r) => r.viewport === 'mobile' && (r.overflow || r.states.some(state => state.overflow)));
    const desktopOverflow = report.results.filter((r) => r.viewport === 'desktop' && (r.overflow || r.states.some(state => state.overflow)));
    const broken = report.results.filter((r) => r.brokenImages.length > 0 || r.states.some(state => state.brokenImages.length));
    const styleErrors = report.results.filter((r) => r.styleGuardError);

    console.log('------------------------------------------------------------');
    console.log(`Console/Style Errors: ${consoleErrors.length}`);
    consoleErrors.slice(0, 12).forEach((e) => console.log('  ', e));
    console.log(`Desktop overflow: ${desktopOverflow.length} | Mobile overflow: ${mobileOverflow.length} | Broken images: ${broken.length} | Style guard fail: ${styleErrors.length}`);

    if (strict && (mobileOverflow.length || desktopOverflow.length || broken.length || styleErrors.length || consoleErrors.length)) {
        process.exit(1);
    }
}

run().catch((e) => {
    console.error(e);
    process.exit(1);
});
