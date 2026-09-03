// Hermes Autopilot Web Agent
// Autonomous Monitoring, Multi-role Synthetic Testing & Self-Healing Watcher

import fs from 'node:fs';
import path from 'node:path';

const BASE_URL = process.env.APP_URL || 'http://127.0.0.1:8000';
const LOG_FILE = path.resolve('storage/logs/laravel.log');

class HermesSession {
    constructor(roleName) {
        this.roleName = roleName;
        this.cookies = new Map();
    }

    parseCookies(response) {
        const setCookieHeaders = response.headers.getSetCookie 
            ? response.headers.getSetCookie() 
            : [response.headers.get('set-cookie')].filter(Boolean);

        for (const header of setCookieHeaders) {
            const parts = header.split(';')[0].split('=');
            if (parts.length >= 2) {
                this.cookies.set(parts[0].trim(), parts.slice(1).join('=').trim());
            }
        }
    }

    getCookieHeader() {
        return Array.from(this.cookies.entries()).map(([k, v]) => `${k}=${v}`).join('; ');
    }

    async request(url, options = {}) {
        const fullUrl = url.startsWith('http') ? url : `${BASE_URL}${url}`;
        const headers = {
            'User-Agent': 'Hermes-Autopilot-Agent/2.0',
            ...(options.headers || {})
        };
        const cHeader = this.getCookieHeader();
        if (cHeader) headers['Cookie'] = cHeader;

        const res = await fetch(fullUrl, { ...options, headers, redirect: 'manual' });
        this.parseCookies(res);
        return res;
    }

    async getWithRedirects(url, maxRedirects = 5) {
        let cur = url;
        let res;
        for (let i = 0; i < maxRedirects; i++) {
            res = await this.request(cur, { method: 'GET' });
            if (res.status >= 300 && res.status < 400) {
                const loc = res.headers.get('location');
                if (!loc) break;
                cur = loc.startsWith('http') ? loc : `${BASE_URL}${loc}`;
            } else {
                break;
            }
        }
        const html = await res.text();
        return { status: res.status, url: cur, html };
    }

    async login(email, password) {
        const loginPage = await this.getWithRedirects('/login');
        const tokenMatch = loginPage.html.match(/name=["']_token["']\s+value=["']([^"']+)["']/i);
        if (!tokenMatch) {
            throw new Error(`[${this.roleName}] Gagal mendeteksi CSRF token di halaman /login`);
        }
        const token = tokenMatch[1];

        const params = new URLSearchParams({
            _token: token,
            email,
            password
        });

        const res = await this.request('/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString()
        });

        if (res.status !== 302) {
            throw new Error(`[${this.roleName}] Login gagal dengan HTTP ${res.status}`);
        }

        const target = res.headers.get('location') || '/dashboard';
        return await this.getWithRedirects(target);
    }
}

export async function runAutopilotCycle() {
    const timestamp = new Date().toLocaleTimeString('id-ID');
    console.log(`\n============================================================`);
    console.log(`🤖 [HERMES AUTOPILOT] Siklus Pengecekan Aktif @ ${timestamp}`);
    console.log(`============================================================`);

    const results = [];

    // Test 1: Public Routes
    try {
        const start = performance.now();
        const home = await fetch(BASE_URL + '/');
        const ms = Math.round(performance.now() - start);
        results.push({ test: 'Public Landing Page', status: home.status === 200 ? 'PASS' : 'FAIL', latency: `${ms}ms` });
    } catch (e) {
        results.push({ test: 'Public Landing Page', status: 'DOWN', error: e.message });
    }

    // Test 2: Role Dosen Pembimbing
    try {
        const dosenSession = new HermesSession('Dosen');
        const start = performance.now();
        const dash = await dosenSession.login('dosen.unesa@unesa.ac.id', 'password');
        const ms = Math.round(performance.now() - start);
        results.push({ test: 'Dosen Auth & Dashboard', status: dash.status === 200 ? 'PASS' : 'FAIL', latency: `${ms}ms` });

        // Check Logbooks
        const lb = await dosenSession.getWithRedirects('/lecturer/logbooks');
        results.push({ test: 'Dosen Logbook Review', status: lb.status === 200 ? 'PASS' : 'FAIL' });

        // Check Monitoring
        const mon = await dosenSession.getWithRedirects('/lecturer/monitoring');
        results.push({ test: 'Dosen Monitoring Table', status: mon.status === 200 ? 'PASS' : 'FAIL' });
    } catch (e) {
        results.push({ test: 'Dosen Flow', status: 'FAIL', error: e.message });
    }

    // Test 3: Role Admin Instansi / Superadmin
    try {
        const adminSession = new HermesSession('Admin');
        const start = performance.now();
        const adminDash = await adminSession.login('admin@gmail.com', 'admin123');
        const ms = Math.round(performance.now() - start);
        results.push({ test: 'Superadmin Auth & Dashboard', status: adminDash.status === 200 ? 'PASS' : 'FAIL', latency: `${ms}ms` });

        // Check Admin Applications
        const apps = await adminSession.getWithRedirects('/admin/applications');
        results.push({ test: 'Admin Applications Management', status: apps.status === 200 ? 'PASS' : 'FAIL' });
    } catch (e) {
        results.push({ test: 'Admin Flow', status: 'FAIL', error: e.message });
    }

    // Test 4: Log Check
    let logStatus = 'CLEAN';
    if (fs.existsSync(LOG_FILE)) {
        const stats = fs.statSync(LOG_FILE);
        const lastModifiedMinutes = Math.round((Date.now() - stats.mtimeMs) / 60000);
        if (lastModifiedMinutes < 5) {
            // Read last 2KB of log
            const buffer = Buffer.alloc(Math.min(stats.size, 2048));
            const fd = fs.openSync(LOG_FILE, 'r');
            fs.readSync(fd, buffer, 0, buffer.length, Math.max(0, stats.size - buffer.length));
            fs.closeSync(fd);
            const content = buffer.toString('utf8');
            if (content.includes('.ERROR:') || content.includes('.CRITICAL:')) {
                logStatus = 'WARNING (Recent errors detected in log)';
            }
        }
    }
    results.push({ test: 'Laravel Log Health', status: logStatus });

    // Display Autopilot Matrix
    console.table(results);

    const allPassed = results.every(r => r.status === 'PASS' || r.status === 'CLEAN');
    if (allPassed) {
        console.log(`✅ [HERMES AUTOPILOT] Seluruh subsistem beroperasi NORMAL & STABIL.`);
    } else {
        console.warn(`⚠️ [HERMES AUTOPILOT] Terdeteksi anomali atau ketidakstabilan pada sistem.`);
    }

    return allPassed;
}

// Mode CLI: jika dijalankan langsung
if (process.argv[1] && process.argv[1].endsWith('hermes_autopilot.mjs')) {
    const isWatch = process.argv.includes('--watch');
    if (isWatch) {
        console.log('🚀 Hermes Autopilot berjalan dalam mode CONTINUOUS WATCHDOG (setiap 30 detik)...');
        runAutopilotCycle();
        setInterval(runAutopilotCycle, 30000);
    } else {
        runAutopilotCycle().then(success => {
            process.exit(success ? 0 : 1);
        });
    }
}
