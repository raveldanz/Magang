/**
 * Uji nyata fitur "Unduh CV / KTP-KTM seluruh anggota (PDF)" di peramban.
 * Login sebagai admin, buka detail pengajuan kelompok, klik kedua tombol, lalu periksa PDF hasil unduhan.
 *
 * Pakai: node scripts/verify_group_bundle.mjs [--app 152] [--email admin.dispusip@surabaya.go.id] [--base http://127.0.0.1:8000]
 */
import { chromium } from 'playwright';
import { PDFDocument } from 'pdf-lib';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';

const args = process.argv.slice(2);
const arg = (f, d) => { const i = args.indexOf(f); return i !== -1 && args[i + 1] ? args[i + 1] : d; };
const baseUrl = arg('--base', 'http://127.0.0.1:8000');
const appId = arg('--app', '152');
const email = arg('--email', 'admin.dispusip@surabaya.go.id');
const pagePath = arg('--path', `/admin/applications/${appId}`);
const viewport = args.includes('--mobile') ? { width: 375, height: 812 } : { width: 1440, height: 900 };

const browser = await chromium.launch({ channel: arg('--channel', 'chrome') });
const context = await browser.newContext({ acceptDownloads: true, viewport });
const page = await context.newPage();
const errors = [];
page.on('pageerror', (e) => errors.push(e.message));
page.on('console', (m) => { if (m.type() === 'error') errors.push(m.text()); });

await page.goto(`${baseUrl}/login`, { waitUntil: 'domcontentloaded' });
const token = await page.locator('form input[name="_token"]').inputValue();
const res = await page.request.post(`${baseUrl}/login`, {
    form: { _token: token, email, password: process.env.STAGE_DEMO_PASSWORD || 'password' }, maxRedirects: 0,
});
if (res.status() !== 302) throw new Error(`Login gagal (HTTP ${res.status()})`);

await page.goto(`${baseUrl}${pagePath}`, { waitUntil: 'networkidle' });
let failed = false;
for (const label of ['Unduh CV seluruh anggota (PDF)', 'Unduh KTP/KTM seluruh anggota (PDF)']) {
    const link = page.getByRole('link', { name: label });
    if (!(await link.count())) { console.log(`GAGAL  tombol "${label}" tidak ditemukan`); failed = true; continue; }
    const started = Date.now();
    const [download] = await Promise.all([page.waitForEvent('download', { timeout: 30000 }), link.first().click()]);
    const file = path.join(os.tmpdir(), download.suggestedFilename());
    await download.saveAs(file);
    const bytes = fs.readFileSync(file);
    const pdf = await PDFDocument.load(bytes);
    console.log(`OK     ${label}: ${download.suggestedFilename()} — ${pdf.getPageCount()} halaman, ${(bytes.length / 1024).toFixed(0)} KB, ${Date.now() - started} ms`);
    fs.unlinkSync(file);
}
const message = await page.locator('[data-group-bundle-message]:not([hidden])').allTextContents();
if (message.length) { console.log('PESAN  ' + message.join(' | ')); failed = true; }
if (errors.length) { console.log('ERROR CONSOLE  ' + errors.join(' | ')); failed = true; }
await browser.close();
process.exit(failed ? 1 : 0);
