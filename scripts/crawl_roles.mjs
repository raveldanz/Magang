/**
 * Crawler semua role: login, telusuri tautan internal (GET saja), catat halaman yang error.
 * Tautan yang tampil di UI tapi menghasilkan 403/404/500 dianggap bug (tautan rusak / hak akses tidak sinkron).
 * Tidak mengubah data: route GET yang berefek samping dilewati (lihat SKIP).
 *
 * Pakai: node scripts/crawl_roles.mjs [--base http://127.0.0.1:8000] [--max 220] [--role mentor.dispusip@surabaya.go.id]
 * Hasil: public/test-artifacts/crawl/report.json
 */
import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';

const args = process.argv.slice(2);
const arg = (f, d) => { const i = args.indexOf(f); return i !== -1 && args[i + 1] ? args[i + 1] : d; };
const baseUrl = arg('--base', 'http://127.0.0.1:8000');
const maxPages = Number(arg('--max', 220));
const perPattern = Number(arg('--per-pattern', 2));
const onlyRole = arg('--role', null);
const ROLES = [
    'admin@surabaya.go.id', 'admin.dispusip@surabaya.go.id', 'supervisor@dispusip.surabaya.go.id',
    'mentor.dispusip@surabaya.go.id', 'dosen.unesa@unesa.ac.id', 'admin@unesa.ac.id',
    'mahasiswa.aktif@unesa.ac.id', 'mahasiswa.lulus@unesa.ac.id',
].filter((r) => !onlyRole || r === onlyRole);
const SKIP = [/\/logout/, /\/generate(\b|\/|$)/, /verify-email/, /\/impersonat/, /login-as/, /batch-print/, /reset-password/,
    /\/forgot-password/, /\/register/, /^\/login/, /\/sanctum\//, /\/up$/, /wa\.me|whatsapp/i, /\/storage\//, /\/build\//];
const ERROR_TEXT = /(Whoops|ErrorException|QueryException|Undefined (variable|array key|property)|Call to a member function|SQLSTATE|Stack trace|could not be converted to string)/;

const pattern = (u) => u.replace(/\/\d+(?=\/|$)/g, '/{id}').replace(/[?&](page|sort|direction)=[^&]*/g, '');

async function crawl(browser, email) {
    const context = await browser.newContext();
    const page = await context.newPage();
    await page.goto(`${baseUrl}/login`, { waitUntil: 'domcontentloaded' });
    const token = await page.locator('form input[name="_token"]').inputValue();
    const login = await page.request.post(`${baseUrl}/login`, {
        form: { _token: token, email, password: process.env.STAGE_DEMO_PASSWORD || 'password' }, maxRedirects: 0,
    });
    if (login.status() !== 302) return { email, error: `login HTTP ${login.status()}` };

    const queue = [{ url: '/dashboard', from: '(awal)' }];
    const seen = new Set();
    const patternCount = new Map();
    const problems = [];
    let visited = 0;

    while (queue.length && visited < maxPages) {
        const { url, from } = queue.shift();
        if (seen.has(url)) continue;
        seen.add(url);
        const pat = pattern(url);
        if ((patternCount.get(pat) || 0) >= perPattern) continue;
        patternCount.set(pat, (patternCount.get(pat) || 0) + 1);

        let res;
        try {
            res = await page.request.get(baseUrl + url, { maxRedirects: 5, timeout: 60000 });
        } catch (e) {
            problems.push({ url, from, status: 'timeout', detail: String(e.message).slice(0, 120) });
            continue;
        }
        visited++;
        const status = res.status();
        const type = res.headers()['content-type'] || '';
        const finalPath = new URL(res.url()).pathname;
        if (status >= 400) {
            let detail = '';
            if (type.includes('html')) {
                const body = await res.text();
                detail = (body.match(/<title>([^<]*)<\/title>/)?.[1] || '') + ' ' + (body.match(ERROR_TEXT)?.[0] || '');
                const msg = body.match(/class="[^"]*(?:exception|message)[^"]*"[^>]*>([^<]{5,200})</)?.[1];
                if (msg) detail += ' | ' + msg.trim();
            }
            problems.push({ url, from, status, detail: detail.trim() });
            continue;
        }
        if (finalPath === '/login') { problems.push({ url, from, status: 'redirect-login' }); continue; }
        if (!type.includes('text/html')) continue;

        const body = await res.text();
        const err = body.match(ERROR_TEXT);
        if (err) problems.push({ url, from, status, detail: 'teks error di halaman: ' + err[0] });

        for (const m of body.matchAll(/(?<![:\w.-])href="([^"#]+)"/g)) {
            let href = m[1].replace(/&amp;/g, '&');
            try {
                const u = new URL(href, baseUrl + url);
                if (u.origin !== new URL(baseUrl).origin) continue;
                href = u.pathname + u.search;
            } catch { continue; }
            if (SKIP.some((re) => re.test(href)) || seen.has(href)) continue;
            queue.push({ url: href, from: url });
        }
    }
    await context.close();
    return { email, visited, problems };
}

const browser = await chromium.launch({ channel: arg('--channel', 'chrome') });
const report = [];
for (const email of ROLES) {
    const r = await crawl(browser, email);
    report.push(r);
    console.log(`${email.padEnd(38)} dikunjungi=${r.visited ?? 0} masalah=${r.problems?.length ?? r.error}`);
    for (const p of r.problems ?? []) console.log(`   [${p.status}] ${p.url}  <- ${p.from}  ${p.detail ?? ''}`);
}
await browser.close();
const outDir = path.resolve('public/test-artifacts/crawl');
fs.mkdirSync(outDir, { recursive: true });
fs.writeFileSync(path.join(outDir, 'report.json'), JSON.stringify(report, null, 2));
