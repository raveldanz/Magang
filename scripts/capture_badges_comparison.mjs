import { chromium } from 'playwright';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');
const publicDir = path.resolve(projectRoot, 'public/test-artifacts');

async function run() {
    const browser = await chromium.launch({ headless: true, channel: 'chrome' });

    try {
        const ctx = await browser.newContext({ viewport: { width: 1000, height: 750 } });
        const page = await ctx.newPage();

        await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'load' });
        await page.fill('input[name="email"]', 'dosen.unesa@unesa.ac.id');
        await page.fill('input[name="password"]', 'password');
        await page.click('button[type="submit"]');
        await page.waitForURL(url => !url.pathname.includes('/login'), { timeout: 15000 });

        await page.goto('http://127.0.0.1:8000/lecturer/monitoring', { waitUntil: 'networkidle' });

        await page.evaluate(() => {
            const container = document.createElement('div');
            container.id = 'badge-showcase-box';
            container.style.cssText = 'position: fixed; top: 40px; left: 40px; width: 750px; z-index: 999999; background: #ffffff; padding: 28px; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.15); border: 1px solid #e2e8f0; font-family: Inter, system-ui, sans-serif;';
            container.innerHTML = `
                <div style="border-bottom: 1px solid #f1f5f9; padding-bottom: 16px; margin-bottom: 24px;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0;">Pembaruan Ikon Status: Tanpa Titik Lingkaran</h2>
                        <span style="background: #eff6ff; color: #1d4ed8; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 9999px;">Clean SVG Micro-Icons</span>
                    </div>
                    <p style="font-size: 12px; color: #64748b; margin-top: 6px; margin-bottom: 0;">Menggantikan titik lingkaran monoton ("●") dengan simbol mikro semantik yang tegas, manusiawi, dan elegan di seluruh dashboard.</p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <!-- BEFORE -->
                    <div style="background: #f8fafc; padding: 18px; border-radius: 14px; border: 1px dashed #cbd5e1;">
                        <div style="font-size: 11px; font-weight: 800; color: #e11d48; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 14px; display: flex; align-items: center; gap: 6px;">
                            <span style="display: inline-block; width: 7px; height: 7px; border-radius: 9999px; background: #f43f5e;"></span>
                            Sebelumnya (Titik Lingkaran Monoton)
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <div>
                                <span style="display: block; font-size: 11px; color: #94a3b8; font-weight: 500; margin-bottom: 4px;">Status Ditolak</span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full border bg-rose-50 text-rose-700 border-rose-300">
                                    <span style="display: inline-block; width: 6px; height: 6px; border-radius: 9999px; background: #f43f5e;"></span>
                                    REJECTED
                                </span>
                            </div>
                            <div>
                                <span style="display: block; font-size: 11px; color: #94a3b8; font-weight: 500; margin-bottom: 4px;">Status Disetujui</span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full border bg-emerald-50 text-emerald-700 border-emerald-300">
                                    <span style="display: inline-block; width: 6px; height: 6px; border-radius: 9999px; background: #10b981;"></span>
                                    APPROVED
                                </span>
                            </div>
                            <div>
                                <span style="display: block; font-size: 11px; color: #94a3b8; font-weight: 500; margin-bottom: 4px;">Status Menunggu</span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full border bg-amber-50 text-amber-700 border-amber-300">
                                    <span style="display: inline-block; width: 6px; height: 6px; border-radius: 9999px; background: #f59e0b;"></span>
                                    PENDING
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- AFTER -->
                    <div style="background: #f0fdf4; padding: 18px; border-radius: 14px; border: 1px solid #bbf7d0;">
                        <div style="font-size: 11px; font-weight: 800; color: #15803d; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 14px; display: flex; align-items: center; gap: 6px;">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="currentColor"><path fill-rule="evenodd" d="M12.416 3.376a.75.75 0 0 1 .208 1.04l-5 7.5a.75.75 0 0 1-1.154.114l-3-3a.75.75 0 0 1 1.06-1.06l2.353 2.353 4.493-6.74a.75.75 0 0 1 1.04-.207Z" clip-rule="evenodd"/></svg>
                            Sekarang (Ikon Semantik Kontekstual)
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <div>
                                <span style="display: block; font-size: 11px; color: #166534; font-weight: 500; margin-bottom: 4px;">Tanda Silang Tegas (✕)</span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full border bg-rose-50 text-rose-700 border-rose-300">
                                    <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 16 16" fill="currentColor"><path d="M3.72 3.72a.75.75 0 0 1 1.06 0L8 6.94l3.22-3.22a.75.75 0 1 1 1.06 1.06L9.06 8l3.22 3.22a.75.75 0 1 1-1.06 1.06L8 9.06l-3.22 3.22a.75.75 0 0 1-1.06-1.06L6.94 8 3.72 4.78a.75.75 0 0 1 0-1.06Z"/></svg>
                                    REJECTED
                                </span>
                            </div>
                            <div>
                                <span style="display: block; font-size: 11px; color: #166534; font-weight: 500; margin-bottom: 4px;">Tanda Centang Valid (✓)</span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full border bg-emerald-50 text-emerald-700 border-emerald-300">
                                    <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 16 16" fill="currentColor"><path fill-rule="evenodd" d="M12.416 3.376a.75.75 0 0 1 .208 1.04l-5 7.5a.75.75 0 0 1-1.154.114l-3-3a.75.75 0 0 1 1.06-1.06l2.353 2.353 4.493-6.74a.75.75 0 0 1 1.04-.207Z" clip-rule="evenodd"/></svg>
                                    APPROVED
                                </span>
                            </div>
                            <div>
                                <span style="display: block; font-size: 11px; color: #166534; font-weight: 500; margin-bottom: 4px;">Indikator Waktu / Jam (◷)</span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full border bg-amber-50 text-amber-700 border-amber-300">
                                    <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 16 16" fill="currentColor"><path fill-rule="evenodd" d="M1 8a7 7 0 1 1 14 0A7 7 0 0 1 1 8Zm7.75-4.25a.75.75 0 0 0-1.5 0v4.25c0 .414.336.75.75.75h3.25a.75.75 0 0 0 0-1.5h-2.5V3.75Z" clip-rule="evenodd"/></svg>
                                    PENDING
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seluruh Status -->
                <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid #f1f5f9;">
                    <span style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 12px;">Katalog Lengkap Seluruh Status Sistem (Global Component):</span>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full border bg-emerald-50 text-emerald-700 border-emerald-300">
                            <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 16 16" fill="currentColor"><path fill-rule="evenodd" d="M12.416 3.376a.75.75 0 0 1 .208 1.04l-5 7.5a.75.75 0 0 1-1.154.114l-3-3a.75.75 0 0 1 1.06-1.06l2.353 2.353 4.493-6.74a.75.75 0 0 1 1.04-.207Z" clip-rule="evenodd"/></svg>
                            APPROVED
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full border bg-emerald-50 text-emerald-700 border-emerald-300">
                            <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 16 16" fill="currentColor"><path fill-rule="evenodd" d="M12.416 3.376a.75.75 0 0 1 .208 1.04l-5 7.5a.75.75 0 0 1-1.154.114l-3-3a.75.75 0 0 1 1.06-1.06l2.353 2.353 4.493-6.74a.75.75 0 0 1 1.04-.207Z" clip-rule="evenodd"/></svg>
                            ACTIVE
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full border bg-rose-50 text-rose-700 border-rose-300">
                            <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 16 16" fill="currentColor"><path d="M3.72 3.72a.75.75 0 0 1 1.06 0L8 6.94l3.22-3.22a.75.75 0 1 1 1.06 1.06L9.06 8l3.22 3.22a.75.75 0 1 1-1.06 1.06L8 9.06l-3.22 3.22a.75.75 0 0 1-1.06-1.06L6.94 8 3.72 4.78a.75.75 0 0 1 0-1.06Z"/></svg>
                            REJECTED
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full border bg-amber-50 text-amber-700 border-amber-300">
                            <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 16 16" fill="currentColor"><path fill-rule="evenodd" d="M1 8a7 7 0 1 1 14 0A7 7 0 0 1 1 8Zm7.75-4.25a.75.75 0 0 0-1.5 0v4.25c0 .414.336.75.75.75h3.25a.75.75 0 0 0 0-1.5h-2.5V3.75Z" clip-rule="evenodd"/></svg>
                            PENDING
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full border bg-orange-50 text-orange-700 border-orange-300">
                            <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 16 16" fill="currentColor"><path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.75.75 0 0 1 1.363-.628A6.5 6.5 0 1 1 8 1.5v1.5z"/><path d="M8 4.5V.5a.25.25 0 0 1 .41-.19l2.5 2a.25.25 0 0 1 0 .38l-2.5 2A.25.25 0 0 1 8 4.5z"/></svg>
                            REVISION
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full border bg-slate-100 text-slate-700 border-slate-300">
                            <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 16 16" fill="currentColor"><path fill-rule="evenodd" d="M2 8a.75.75 0 0 1 .75-.75h10.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 8Z" clip-rule="evenodd"/></svg>
                            RESIGNED
                        </span>
                    </div>
                </div>
            `;
            document.body.appendChild(container);
        });

        await page.waitForTimeout(300);
        const showcaseEl = await page.$('#badge-showcase-box');
        if (showcaseEl) {
            await showcaseEl.screenshot({ path: path.join(publicDir, 'status_badges_redesign_showcase.png') });
            console.log('[+] Status badges showcase screenshot saved successfully!');
        }

        await ctx.close();
    } catch (err) {
        console.error('Error during showcase capture:', err);
    } finally {
        await browser.close();
    }
}

run();
