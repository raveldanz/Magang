/**
 * Audit visual generik per tahap (3A-9): overflow, elemen bertumpuk, jarak mepet,
 * target sentuh kecil, teks terpotong, error console, serta pembukaan otomatis
 * dropdown/modal ("probe"). Tidak mengubah data: dialog confirm() selalu dibatalkan
 * dan tombol submit tidak pernah diklik.
 *
 * Pakai:
 *   node scripts/ui_stage_audit.mjs --stage 3a --tag before
 *   node scripts/ui_stage_audit.mjs --stage 3a --tag after --strict
 *   (opsi: --only <kunci-skenario> --viewport desktop|mobile)
 *
 * Skenario: scripts/stage_scenarios/<stage>.json
 * Hasil:    public/test-artifacts/ui-stage/<stage>/<tag>-*.png + <tag>-report.json
 */
import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { assertManifestBuilt } from './visual-guard.mjs';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..');
const args = process.argv.slice(2);
const arg = (f, d) => { const i = args.indexOf(f); return i !== -1 && args[i + 1] ? args[i + 1] : d; };
const stage = arg('--stage', '3a');
const tag = arg('--tag', 'before');
const only = arg('--only', null);
const onlyVp = arg('--viewport', null);
const strict = args.includes('--strict');
const baseUrl = arg('--base', 'http://127.0.0.1:8000');
const channel = arg('--channel', 'chrome');
const maxProbes = Number(arg('--probes', 14));
const scenarios = JSON.parse(fs.readFileSync(path.join(__dirname, 'stage_scenarios', `${stage}.json`), 'utf8'));
const outDir = path.join(root, 'public/test-artifacts/ui-stage', stage);
fs.mkdirSync(outDir, { recursive: true });

const VIEWPORTS = { desktop: { width: 1440, height: 900 }, mobile: { width: 375, height: 812 } };

async function login(page, email) {
    await page.goto(`${baseUrl}/login`, { waitUntil: 'domcontentloaded', timeout: 60000 });
    const token = await page.locator('form input[name="_token"]').inputValue();
    const res = await page.request.post(`${baseUrl}/login`, {
        form: { _token: token, email, password: process.env.STAGE_DEMO_PASSWORD || 'password' },
        maxRedirects: 0, timeout: 60000,
    });
    const loc = res.headers().location;
    if (res.status() !== 302 || !loc || new URL(loc, baseUrl).pathname === '/login') {
        throw new Error(`Login ${email} gagal (HTTP ${res.status()}).`);
    }
}

/** Dijalankan di browser. Mengembalikan metrik tata letak halaman saat ini. */
function measureInPage(opts) {
    const viewportOnly = !!(opts && opts.viewportOnly);
    const vw = window.innerWidth;
    const vh = window.innerHeight;
    const vis = (el) => {
        const r = el.getBoundingClientRect();
        if (r.width < 1 || r.height < 1) return null;
        const cs = getComputedStyle(el);
        if (cs.display === 'none' || cs.visibility === 'hidden' || cs.opacity === '0') return null;
        // Di luar layar secara horizontal = bagian strip yang dapat digulir, bukan cacat.
        if (r.right < 0 || r.left > vw) return null;
        if (viewportOnly && (r.bottom < 0 || r.top > vh)) return null;
        // Tertutup overlay/modal: bukan bagian yang terlihat.
        const cx = Math.min(Math.max(r.left + r.width / 2, 0), vw - 1);
        const cy = r.top + r.height / 2;
        if (cy >= 0 && cy < vh) {
            const t = document.elementFromPoint(cx, cy);
            if (t && !(el.contains(t) || t.contains(el))) return null;
        }
        return { r, cs };
    };
    const label = (el) => `${el.tagName.toLowerCase()}[${(el.innerText || el.getAttribute('aria-label') || el.getAttribute('title') || el.name || '').trim().replace(/\s+/g, ' ').slice(0, 30)}]`;
    const inScrollClip = (el) => {
        for (let p = el.parentElement; p && p !== document.body; p = p.parentElement) {
            const o = getComputedStyle(p);
            if (['auto', 'scroll', 'hidden', 'clip'].includes(o.overflowX) && p.getBoundingClientRect().width <= vw + 1) return true;
        }
        return false;
    };

    const offenders = [];
    for (const el of document.querySelectorAll('body *')) {
        const v = vis(el);
        if (!v || v.cs.position === 'fixed') continue;
        if (v.r.right > vw + 1 && !inScrollClip(el)) offenders.push(`${el.tagName.toLowerCase()}.${String(el.className).slice(0, 60)} right=${Math.round(v.r.right)}`);
    }

    const inter = [];
    for (const el of document.querySelectorAll('a[href], button, input:not([type=hidden]), select, textarea, [role="button"], summary')) {
        const v = vis(el);
        if (!v || v.cs.position === 'fixed' || el.closest('[aria-hidden="true"]')) continue;
        if (el.tagName === 'A' && v.cs.display === 'inline') continue;
        if (el.type === 'checkbox' || el.type === 'radio') continue;
        inter.push({ el, r: v.r });
    }

    const overlaps = [];
    const tight = [];
    for (let i = 0; i < inter.length; i++) {
        for (let j = i + 1; j < inter.length; j++) {
            const a = inter[i], b = inter[j];
            if (a.el.contains(b.el) || b.el.contains(a.el)) continue;
            const ox = Math.min(a.r.right, b.r.right) - Math.max(a.r.left, b.r.left);
            const oy = Math.min(a.r.bottom, b.r.bottom) - Math.max(a.r.top, b.r.top);
            if (ox > 2 && oy > 2) { overlaps.push(`${label(a.el)} x ${label(b.el)} (${Math.round(ox)}x${Math.round(oy)})`); continue; }
            const sameRow = oy > Math.min(a.r.height, b.r.height) * 0.5;
            const sameCol = ox > Math.min(a.r.width, b.r.width) * 0.5;
            const gapX = sameRow ? Math.max(a.r.left, b.r.left) - Math.min(a.r.right, b.r.right) : 99;
            const gapY = sameCol ? Math.max(a.r.top, b.r.top) - Math.min(a.r.bottom, b.r.bottom) : 99;
            const gap = Math.min(gapX, gapY);
            if (gap >= 0 && gap < 4 && !(a.el.closest('nav[aria-label="Pagination Navigation"], .pagination') && b.el.closest('nav[aria-label="Pagination Navigation"], .pagination'))) {
                tight.push(`${label(a.el)} | ${label(b.el)} gap=${Math.round(gap)}`);
            }
        }
    }

    const textBoxes = [];
    for (const el of document.querySelectorAll('h1,h2,h3,h4,p,span,label,td,th,li,a,button,small,strong,div')) {
        if (el.children.length) continue;
        if (!(el.textContent || '').trim()) continue;
        const v = vis(el);
        if (!v || v.cs.position === 'fixed') continue;
        textBoxes.push({ el, r: v.r });
    }
    const textOverlaps = [];
    for (let i = 0; i < textBoxes.length && textOverlaps.length < 12; i++) {
        for (let j = i + 1; j < textBoxes.length; j++) {
            const a = textBoxes[i], b = textBoxes[j];
            if (a.el.contains(b.el) || b.el.contains(a.el)) continue;
            const ox = Math.min(a.r.right, b.r.right) - Math.max(a.r.left, b.r.left);
            const oy = Math.min(a.r.bottom, b.r.bottom) - Math.max(a.r.top, b.r.top);
            if (ox > 3 && oy > 3) { textOverlaps.push(`"${a.el.textContent.trim().slice(0, 22)}" x "${b.el.textContent.trim().slice(0, 22)}" (${Math.round(ox)}x${Math.round(oy)})`); if (textOverlaps.length >= 12) break; }
        }
    }

    const edge = [];
    const small = [];
    for (const { el, r } of inter) {
        if ((r.left < 8 || r.right > vw - 8) && !inScrollClip(el)) edge.push(`${label(el)} L=${Math.round(r.left)} R=${Math.round(vw - r.right)}`);
        if (vw <= 480 && r.height < 38 && !(el.tagName === 'INPUT' && ['checkbox', 'radio'].includes(el.type))) small.push(`${label(el)} ${Math.round(r.width)}x${Math.round(r.height)}`);
    }

    const clipped = [];
    for (const { el } of textBoxes) {
        const cs = getComputedStyle(el);
        if (cs.textOverflow === 'ellipsis' && el.scrollWidth > el.clientWidth + 1 && !el.getAttribute('title')) clipped.push(`${label(el)} ${el.scrollWidth}>${el.clientWidth}`);
    }

    const broken = [...document.querySelectorAll('img')].filter((i) => { const v = vis(i); return v && i.complete && i.naturalWidth === 0; }).map((i) => i.getAttribute('src'));

    return {
        innerWidth: vw,
        scrollWidth: document.documentElement.scrollWidth,
        overflow: document.documentElement.scrollWidth > vw || document.body.scrollWidth > vw,
        offenders: offenders.slice(0, 8),
        overlaps: overlaps.slice(0, 12),
        textOverlaps,
        tight: tight.slice(0, 12),
        edge: edge.slice(0, 10),
        small: small.slice(0, 12),
        clipped: clipped.slice(0, 8),
        broken: broken.slice(0, 5),
    };
}

/** Kandidat pemicu dropdown/modal/tab; indeks stabil karena DOM dimuat ulang tiap probe. */
const PROBE_SELECTOR = 'button[type="button"], [role="button"]:not(a), summary, [aria-haspopup], [role="tab"]';
function probeCandidates({ includeNav, selector }) {
    const out = [];
    document.querySelectorAll(selector).forEach((el, idx) => {
        const r = el.getBoundingClientRect();
        const cs = getComputedStyle(el);
        if (r.width < 1 || r.height < 1 || cs.display === 'none' || cs.visibility === 'hidden') return;
        if (el.closest('[aria-hidden="true"], [x-cloak]')) return;
        if (!includeNav && el.closest('nav, header')) return;
        if (el.disabled) return;
        out.push({ idx, text: (el.innerText || el.getAttribute('aria-label') || el.title || '').trim().replace(/\s+/g, ' ').slice(0, 40) });
    });
    return out;
}

async function resolvePath(page, sc) {
    if (!sc.follow) return sc.path;
    await page.goto(new URL(sc.follow.from, baseUrl).href, { waitUntil: 'load', timeout: 120000 });
    const href = await page.evaluate(({ pattern }) => {
        const re = new RegExp(pattern);
        for (const a of document.querySelectorAll('a[href]')) {
            const p = new URL(a.href).pathname + new URL(a.href).search;
            if (re.test(p)) return p;
        }
        return null;
    }, { pattern: sc.follow.pattern });
    if (!href) throw new Error(`Tautan "${sc.follow.pattern}" tidak ditemukan di ${sc.follow.from}`);
    return href + (sc.follow.suffix || '');
}

async function run() {
    assertManifestBuilt(root);
    const browser = await chromium.launch({ channel, headless: true });
    const report = { stage, tag, generatedAt: new Date().toISOString(), results: [] };
    const consoleErrors = [];
    try {
        for (const [vpName, viewport] of Object.entries(VIEWPORTS)) {
            if (onlyVp && onlyVp !== vpName) continue;
            const sessions = {};
            let active = '';
            for (const sc of scenarios) {
                if (only && sc.key !== only) continue;
                const sk = sc.role || 'guest';
                if (!sessions[sk]) {
                    const ctx = await browser.newContext({ viewport, deviceScaleFactor: 1, locale: 'id-ID' });
                    const pg = await ctx.newPage();
                    pg.on('dialog', (d) => d.dismiss().catch(() => {}));
                    pg.on('console', (m) => { if (m.type() === 'error') consoleErrors.push(`[${vpName}/${active}] ${m.text()} (${(m.location().url || '').split('?')[0]})`); });
                    pg.on('pageerror', (e) => consoleErrors.push(`[${vpName}/${active}] PAGEERROR ${e.message}`));
                    pg.on('response', (r) => { if (r.status() >= 400 && /\.(css|js|png|jpe?g|svg|webp|woff2?)(\?|$)/.test(r.url())) consoleErrors.push(`[${vpName}/${active}] ASET ${r.status()} ${r.url()}`); });
                    if (sc.role) await login(pg, sc.role);
                    sessions[sk] = pg;
                }
                const page = sessions[sk];
                active = sc.key;
                let target;
                try { target = await resolvePath(page, sc); } catch (e) { report.results.push({ scenario: sc.key, viewport: vpName, error: e.message }); console.log(`[${tag}] ${vpName} ${sc.key} SKIP: ${e.message}`); continue; }
                const open = async () => {
                    const res = await page.goto(new URL(target, baseUrl).href, { waitUntil: 'load', timeout: 120000 });
                    await page.waitForTimeout(700);
                    return res;
                };
                const res = await open();
                if (!res?.ok()) { report.results.push({ scenario: sc.key, viewport: vpName, error: `HTTP ${res?.status()}` }); console.log(`[${tag}] ${vpName} ${sc.key} HTTP ${res?.status()}`); continue; }

                const shot = path.join(outDir, `${tag}-${sc.key}-${vpName}.png`);
                await page.screenshot({ path: shot, fullPage: true });
                const m = await page.evaluate(measureInPage);
                const entry = { scenario: sc.key, viewport: vpName, url: page.url().replace(baseUrl, ''), screenshot: path.relative(root, shot), ...m, states: [] };

                if (sc.probe !== false) {
                    const cands = await page.evaluate(probeCandidates, { includeNav: !!sc.probeNav, selector: PROBE_SELECTOR });
                    const seen = new Set();
                    let n = 0;
                    for (const c of cands) {
                        if (n >= maxProbes) break;
                        const key = c.text || `#${c.idx}`;
                        if (seen.has(key)) continue;
                        seen.add(key);
                        n++;
                        const before = page.url();
                        try {
                            await page.locator(PROBE_SELECTOR).nth(c.idx).scrollIntoViewIfNeeded({ timeout: 3000 });
                            await page.locator(PROBE_SELECTOR).nth(c.idx).click({ timeout: 3000 });
                            await page.waitForTimeout(350);
                            if (page.url() !== before) { await open(); continue; }
                            const sm = await page.evaluate(measureInPage, { viewportOnly: true });
                            const name = `${sc.key}-s${n}-${c.text.replace(/[^a-z0-9]+/gi, '-').toLowerCase().slice(0, 24) || c.idx}`;
                            const sshot = path.join(outDir, `${tag}-${name}-${vpName}.png`);
                            await page.screenshot({ path: sshot, fullPage: false });
                            entry.states.push({ name, trigger: c.text, screenshot: path.relative(root, sshot), ...sm });
                        } catch (e) {
                            entry.states.push({ name: `probe-${c.idx}`, trigger: c.text, error: e.message.split('\n')[0] });
                        }
                        await open();
                    }
                }

                report.results.push(entry);
                const all = [entry, ...entry.states.filter((s) => !s.error)];
                const sum = (k) => all.reduce((t, s) => t + (s[k]?.length || 0), 0);
                console.log(`[${tag}] ${vpName.padEnd(7)} ${sc.key.padEnd(28)} sw=${m.scrollWidth}/${m.innerWidth}${all.some((s) => s.overflow) ? ' OVERFLOW' : ''} tumpuk=${sum('overlaps')} teks=${sum('textOverlaps')} mepet=${sum('tight')} tepi=${sum('edge')} kecil=${sum('small')} potong=${sum('clipped')} states=${entry.states.length}`);
            }
            for (const p of Object.values(sessions)) await p.context().close();
        }
    } finally { await browser.close(); }

    report.consoleErrors = [...new Set(consoleErrors)];
    fs.writeFileSync(path.join(outDir, `${tag}-report.json`), JSON.stringify(report, null, 2));
    const flat = report.results.flatMap((r) => r.error ? [] : [r, ...r.states.filter((s) => !s.error).map((s) => ({ ...s, scenario: `${r.scenario}>${s.name}`, viewport: r.viewport }))]);
    const hard = flat.filter((r) => r.overflow || r.overlaps?.length || r.textOverlaps?.length || r.broken?.length);
    const errs = report.results.filter((r) => r.error);
    console.log('------------------------------------------------------------');
    console.log(`Halaman/state: ${flat.length} | Masalah keras (overflow/tumpuk/gambar rusak): ${hard.length} | Halaman gagal: ${errs.length} | Console/aset error: ${report.consoleErrors.length}`);
    report.consoleErrors.slice(0, 10).forEach((e) => console.log('  ', e));
    if (strict && (hard.length || errs.length || report.consoleErrors.length)) process.exit(1);
}

run().catch((e) => { console.error(e); process.exit(2); });
