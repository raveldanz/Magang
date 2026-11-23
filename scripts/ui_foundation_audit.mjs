/**
 * Audit Visual Fondasi UI Global (Tahap 0)
 * Lokasi: scripts/ui_foundation_audit.mjs
 *
 * Mengambil screenshot Desktop (1440x900) & Mobile (375x812) halaman representatif
 * (publik/guest + dashboard terautentikasi), memeriksa horizontal overflow, ukuran
 * area sentuh tombol menu mobile, dan perilaku drawer navigasi.
 *
 * Penggunaan:
 *   node scripts/ui_foundation_audit.mjs --tag before
 *   node scripts/ui_foundation_audit.mjs --tag after
 * Hasil: public/test-artifacts/ui-foundation/<tag>-*.png + <tag>-report.json
 * Exit code 0 bila 0 overflow mobile & semua cek lulus (mode --strict), selain itu 1.
 */

import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { assertManifestBuilt } from './visual-guard.mjs';

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
const outDir = path.resolve(projectRoot, 'public/test-artifacts/ui-foundation');
fs.mkdirSync(outDir, { recursive: true });

const VIEWPORTS = {
    desktop: { width: 1440, height: 900 },
    mobile: { width: 375, height: 812 },
};

const SCENARIOS = [
    { key: 'login', path: '/login', role: null },
    { key: 'welcome', path: '/', role: null },
    { key: 'admin-dashboard', path: '/admin/dashboard', role: 'admin@surabaya.go.id' },
    { key: 'student-dashboard', path: '/dashboard', role: 'mahasiswa.aktif@unesa.ac.id' },
];

async function login(page, email) {
    await page.goto(`${baseUrl}/login`, { waitUntil: 'domcontentloaded', timeout: 60000 });
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="password"]', 'password');
    await Promise.all([
        page.waitForURL((url) => !url.pathname.includes('/login'), { timeout: 60000 }),
        page.click('button[type="submit"]'),
    ]);
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
                // Lewati elemen yang terpotong rapi oleh kontainer scroll/clip (bukan penyebab overflow halaman)
                let clipped = false;
                for (let p = el.parentElement; p && p !== document.body; p = p.parentElement) {
                    const pcs = getComputedStyle(p);
                    if (['auto', 'scroll', 'hidden', 'clip'].includes(pcs.overflowX) && p.getBoundingClientRect().right <= vw + 1) { clipped = true; break; }
                }
                if (!clipped) offenders.push(`${el.tagName.toLowerCase()}.${String(el.className).slice(0, 80)} right=${Math.round(r.right)}`);
            }
        }
        const burger = document.querySelector('button[aria-label="Buka Menu"]');
        const br = burger ? burger.getBoundingClientRect() : null;
        const header = document.querySelector('nav');
        return {
            innerWidth: vw,
            scrollWidth: document.documentElement.scrollWidth,
            bodyScrollWidth: document.body.scrollWidth,
            overflow: document.documentElement.scrollWidth > vw || document.body.scrollWidth > vw,
            offenders: offenders.slice(0, 12),
            burger: br && br.width ? { w: Math.round(br.width), h: Math.round(br.height) } : null,
            navHeight: header ? Math.round(header.getBoundingClientRect().height) : null,
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
            for (const sc of SCENARIOS) {
                const sessionKey = sc.role || 'guest';
                if (!sessions[sessionKey]) {
                    const ctx = await browser.newContext({ viewport, deviceScaleFactor: 1, locale: 'id-ID' });
                    const pg = await ctx.newPage();
                    pg.on('console', (m) => { if (m.type() === 'error') consoleErrors.push(`[${vpName}/${sessionKey}] ${m.text()}`); });
                    pg.on('pageerror', (e) => consoleErrors.push(`[${vpName}/${sessionKey}] PAGEERROR ${e.message}`));
                    if (sc.role) await login(pg, sc.role);
                    sessions[sessionKey] = pg;
                }
                const page = sessions[sessionKey];
                // 'load' (bukan networkidle): polling chat/notifikasi membuat jaringan tidak pernah idle
                await page.goto(`${baseUrl}${sc.path}`, { waitUntil: 'load', timeout: 120000 });
                await page.waitForTimeout(1200);
                const shot = path.join(outDir, `${tag}-${sc.key}-${vpName}.png`);
                await page.screenshot({ path: shot, fullPage: false });
                const m = await measure(page);
                const entry = { scenario: sc.key, viewport: vpName, screenshot: path.relative(projectRoot, shot), ...m };

                // Mobile: buka drawer navigasi (layout app) dan ukur
                if (vpName === 'mobile' && m.burger) {
                    await page.click('button[aria-label="Buka Menu"]');
                    await page.waitForTimeout(450);
                    const openShot = path.join(outDir, `${tag}-${sc.key}-mobile-menu.png`);
                    await page.screenshot({ path: openShot, fullPage: false });
                    entry.menuScreenshot = path.relative(projectRoot, openShot);
                    entry.menu = await page.evaluate(() => {
                        const close = document.querySelector('[data-nav-drawer-close]');
                        const cr = close ? close.getBoundingClientRect() : null;
                        const panel = document.querySelector('[data-nav-drawer]');
                        const pr = panel ? panel.getBoundingClientRect() : null;
                        return {
                            closeButton: cr && cr.width ? { w: Math.round(cr.width), h: Math.round(cr.height) } : null,
                            panel: pr ? { left: Math.round(pr.left), right: Math.round(pr.right), width: Math.round(pr.width), height: Math.round(pr.height) } : null,
                            scrollWidth: document.documentElement.scrollWidth,
                            innerWidth: window.innerWidth,
                            bodyLocked: document.body.classList.contains('overflow-hidden'),
                        };
                    });
                    await page.keyboard.press('Escape');
                    await page.waitForTimeout(350);
                    entry.menuClosedByEscape = await page.evaluate(() => !document.body.classList.contains('overflow-hidden'));
                }
                report.results.push(entry);
                const flag = m.overflow ? 'OVERFLOW' : 'ok';
                console.log(`[${tag}] ${vpName.padEnd(7)} ${sc.key.padEnd(18)} scrollW=${m.scrollWidth}/${m.innerWidth} ${flag}${m.burger ? ` burger=${m.burger.w}x${m.burger.h}` : ''}${entry.menu ? ` closeBtn=${JSON.stringify(entry.menu.closeButton)} panel=${JSON.stringify(entry.menu.panel)}` : ''}`);
                if (m.offenders.length) console.log('   offenders:', m.offenders.slice(0, 5).join(' | '));
            }
            for (const pg of Object.values(sessions)) await pg.context().close();
        }
    } finally {
        await browser.close();
    }

    report.consoleErrors = consoleErrors;
    fs.writeFileSync(path.join(outDir, `${tag}-report.json`), JSON.stringify(report, null, 2));
    console.log(`Console errors: ${consoleErrors.length}`);
    consoleErrors.slice(0, 10).forEach((e) => console.log('  ', e));

    const mobileOverflow = report.results.filter((r) => r.viewport === 'mobile' && r.overflow);
    const smallTargets = report.results.filter((r) => r.burger && (r.burger.w < 44 || r.burger.h < 44));
    console.log(`Mobile overflow: ${mobileOverflow.length} | Burger < 44px: ${smallTargets.length}`);
    if (strict && (mobileOverflow.length || smallTargets.length)) process.exit(1);
}

run().catch((e) => { console.error(e); process.exit(1); });
