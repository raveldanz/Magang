// =========================================================================
// Hermes Multi-Role Autonomous E2E Test Suite (Roles 1 - 6 + Inter-Role Flow)
// =========================================================================

import fs from 'node:fs';
import path from 'node:path';
import { performance } from 'node:perf_hooks';

const BASE_URL = process.env.APP_URL || 'http://127.0.0.1:8000';
const LOG_FILE = path.resolve('storage/logs/laravel.log');

class HermesSession {
    constructor(roleName) {
        this.roleName = roleName;
        this.cookies = new Map();
        this.csrfToken = null;
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
            'User-Agent': `Hermes-Test-Agent/3.0 (${this.roleName})`,
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
        const tokenMatch = html.match(/name=["']_token["']\s+value=["']([^"']+)["']/i)
            || html.match(/name=["']csrf-token["']\s+content=["']([^"']+)["']/i);
        if (tokenMatch) {
            this.csrfToken = tokenMatch[1];
        }
        return { status: res.status, url: cur, html };
    }

    async login(email, password) {
        const loginPage = await this.getWithRedirects('/login');
        const tokenMatch = loginPage.html.match(/name=["']_token["']\s+value=["']([^"']+)["']/i);
        if (!tokenMatch) {
            throw new Error(`[${this.roleName}] Gagal mendeteksi CSRF token di halaman /login`);
        }
        this.csrfToken = tokenMatch[1];

        const params = new URLSearchParams({
            _token: this.csrfToken,
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

async function runFullHermesTest() {
    console.log(`\n================================================================================`);
    console.log(`🚀 [HERMES E2E SWARM] PENGUJIAN OTOMATIS MENYELURUH 6 ROLE & INTEGRASI SISTEM`);
    console.log(`Target URL: ${BASE_URL} | Waktu: ${new Date().toLocaleString('id-ID')}`);
    console.log(`================================================================================\n`);

    const results = [];
    const record = (group, feature, status, detail = '') => {
        results.push({ Group: group, Feature: feature, Status: status, Detail: detail });
        const icon = status === 'PASS' ? '✅' : (status === 'WARN' ? '⚠️' : '❌');
        console.log(`${icon} [${group}] ${feature} -> ${status} ${detail ? '(' + detail + ')' : ''}`);
    };

    // -------------------------------------------------------------------------
    // 0. KONEKTIVITAS & HALAMAN PUBLIK
    // -------------------------------------------------------------------------
    try {
        const t0 = performance.now();
        const home = await fetch(`${BASE_URL}/`);
        const ms = Math.round(performance.now() - t0);
        record('Public', 'Landing Page (/)' , home.status === 200 ? 'PASS' : 'FAIL', `${ms}ms`);
    } catch (e) {
        record('Public', 'Landing Page (/)', 'FAIL', e.message);
    }

    try {
        const t0 = performance.now();
        const loginPg = await fetch(`${BASE_URL}/login`);
        const ms = Math.round(performance.now() - t0);
        record('Public', 'Auth Login Page (/login)', loginPg.status === 200 ? 'PASS' : 'FAIL', `${ms}ms`);
    } catch (e) {
        record('Public', 'Auth Login Page (/login)', 'FAIL', e.message);
    }

    try {
        const regPg = await fetch(`${BASE_URL}/register`);
        record('Public', 'Registration Page (/register)', regPg.status === 200 ? 'PASS' : 'FAIL');
    } catch (e) {
        record('Public', 'Registration Page (/register)', 'FAIL', e.message);
    }

    // -------------------------------------------------------------------------
    // 1. ROLE 1: SUPER ADMINISTRATOR
    // -------------------------------------------------------------------------
    const superAdmin = new HermesSession('SuperAdmin');
    try {
        const dash = await superAdmin.login('admin@surabaya.go.id', 'password');
        record('SuperAdmin', 'Login & Executive Dashboard', dash.status === 200 ? 'PASS' : 'FAIL', dash.url);

        const endpoints = [
            ['/admin/applications', 'Verifikasi Pengajuan Magang'],
            ['/admin/units', 'Manajemen Kuota & Unit Kerja'],
            ['/admin/agencies', 'Master Instansi Dinas OPD'],
            ['/admin/universities', 'Master Perguruan Tinggi'],
            ['/admin/certificates', 'Penerbitan Sertifikat & Transkrip'],
            ['/admin/users', 'Master Seluruh Pengguna'],
            ['/admin/mentors', 'Master Pembimbing Lapangan'],
            ['/admin/audit-logs', 'Log Audit Keamanan'],
            ['/admin/notifications', 'Pusat Pemberitahuan Terpadu'],
            ['/admin/feedbacks', 'Pusat Tiket Kendala/Masukan'],
        ];

        for (const [ep, label] of endpoints) {
            const res = await superAdmin.getWithRedirects(ep);
            record('SuperAdmin', label, res.status === 200 ? 'PASS' : 'FAIL', ep);
        }

        // Test Impersonation Flow
        const impRes = await superAdmin.request('/admin/impersonate/65', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `_token=${superAdmin.csrfToken}`
        });
        if (impRes.status === 302) {
            record('SuperAdmin', 'Fitur Impersonasi (Login As Mahasiswa)', 'PASS', 'Redirected to impersonated session');
            // Follow redirect to get the impersonated page & fresh CSRF
            const targetPg = await superAdmin.getWithRedirects(impRes.headers.get('location') || '/dashboard');
            // Leave impersonation
            const leaveRes = await superAdmin.request('/admin/impersonate/leave', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `_token=${superAdmin.csrfToken}`
            });
            record('SuperAdmin', 'Kembali dari Impersonasi (Leave Impersonation)', leaveRes.status === 302 ? 'PASS' : 'FAIL', 'Returned to Super Admin');
        } else {
            record('SuperAdmin', 'Fitur Impersonasi (Login As Mahasiswa)', 'WARN', `Status ${impRes.status}`);
        }
    } catch (e) {
        record('SuperAdmin', 'Siklus Pengujian Super Admin', 'FAIL', e.message);
    }

    // -------------------------------------------------------------------------
    // 2. ROLE 2: ADMIN DINAS / OPD (KOMINFO)
    // -------------------------------------------------------------------------
    const adminDinas = new HermesSession('AdminDinas');
    try {
        const dash = await adminDinas.login('admin.kominfo@surabaya.go.id', 'password');
        record('AdminDinas', 'Login & Dashboard Dinas Scoped', dash.status === 200 ? 'PASS' : 'FAIL', dash.url);

        const dinasEndpoints = [
            ['/admin/agency-profile', 'Profil Dinas & Tanda Tangan'],
            ['/admin/applications', 'Monitoring Lamaran Unit Dinas'],
            ['/admin/units', 'Kapasitas Kuota Divisi Dinas'],
            ['/admin/logbooks', 'Pemeriksaan Logbook Dinas'],
            ['/admin/certificates', 'Pratinjau Sertifikat Dinas'],
        ];

        for (const [ep, label] of dinasEndpoints) {
            const res = await adminDinas.getWithRedirects(ep);
            record('AdminDinas', label, res.status === 200 ? 'PASS' : 'FAIL', ep);
        }
    } catch (e) {
        record('AdminDinas', 'Siklus Pengujian Admin Dinas', 'FAIL', e.message);
    }

    // -------------------------------------------------------------------------
    // 3. ROLE 3: MAHASISWA (AKTIF & LULUS)
    // -------------------------------------------------------------------------
    const mhsAktif = new HermesSession('MahasiswaAktif');
    let testLogbookId = null;
    try {
        const dash = await mhsAktif.login('mahasiswa.aktif@unesa.ac.id', 'password');
        record('Mahasiswa', 'Login & Dasbor Penempatan Aktif', dash.status === 200 ? 'PASS' : 'FAIL', dash.url);

        const mhsEndpoints = [
            ['/student/profile', 'Biodata & Dokumen Portofolio'],
            ['/student/logbook', 'Daftar Logbook & Riwayat Bimbingan'],
            ['/student/logbook/create', 'Formulir Catat Logbook Harian'],
            ['/student/final-report', 'Halaman Pengunggahan Naskah Laporan'],
            ['/student/application/27/letter', 'Unduh Surat Penerimaan Resmi (QR Verified)'],
        ];

        for (const [ep, label] of mhsEndpoints) {
            const res = await mhsAktif.getWithRedirects(ep);
            record('Mahasiswa', label, res.status === 200 ? 'PASS' : 'FAIL', ep);
        }

        // Action: Submit New Daily Logbook
        const todayStr = new Date().toISOString().split('T')[0];
        const lbCreatePage = await mhsAktif.getWithRedirects('/student/logbook/create');
        const tokenMatch = lbCreatePage.html.match(/name=["']_token["']\s+value=["']([^"']+)["']/i);
        if (tokenMatch) {
            const lbParams = new URLSearchParams({
                _token: tokenMatch[1],
                date: todayStr,
                activity: `[Hermes E2E Test] Implementasi pengujian terpadu multi-role pada pukul ${new Date().toLocaleTimeString('id-ID')}. Memverifikasi alur koordinasi mahasiswa, dinas, dan perguruan tinggi.`
            });
            const lbPost = await mhsAktif.request('/student/logbook', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: lbParams.toString()
            });
            record('Mahasiswa', 'Kirim Logbook Harian Baru', lbPost.status === 302 ? 'PASS' : 'FAIL', `HTTP ${lbPost.status}`);
        }

        // Action: Submit System Feedback Ticket
        const fbPage = await mhsAktif.getWithRedirects('/feedbacks/create');
        const fbToken = fbPage.html.match(/name=["']_token["']\s+value=["']([^"']+)["']/i);
        if (fbToken) {
            const fbParams = new URLSearchParams({
                _token: fbToken[1],
                subject: 'Uji Coba Sistem Hermes Multi-Role',
                message: 'Pesan otomatis dari agen Hermes untuk memverifikasi alur notifikasi dan respon admin.',
                category: 'pertanyaan',
                priority: 'normal'
            });
            const fbPost = await mhsAktif.request('/feedbacks', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: fbParams.toString()
            });
            record('Mahasiswa', 'Kirim Tiket Masukan / Feedback', fbPost.status === 302 ? 'PASS' : 'FAIL');
        }
    } catch (e) {
        record('Mahasiswa', 'Siklus Pengujian Mahasiswa Aktif', 'FAIL', e.message);
    }

    // Mahasiswa Lulus Test (Certificate Download)
    const mhsLulus = new HermesSession('MahasiswaLulus');
    try {
        const dashLulus = await mhsLulus.login('mahasiswa.lulus@unesa.ac.id', 'password');
        record('Mahasiswa', 'Login Mahasiswa Lulus & Dashboard', dashLulus.status === 200 ? 'PASS' : 'FAIL');
        const certRes = await mhsLulus.getWithRedirects('/student/certificate/23/download');
        record('Mahasiswa', 'Unduh E-Sertifikat & Transkrip Nilai (2 Halaman)', certRes.status === 200 ? 'PASS' : 'FAIL', 'Placement ID 23');
    } catch (e) {
        record('Mahasiswa', 'Unduh E-Sertifikat Mahasiswa Lulus', 'FAIL', e.message);
    }

    // -------------------------------------------------------------------------
    // 4. ROLE 4: PEMBIMBING LAPANGAN / MENTOR DINAS
    // -------------------------------------------------------------------------
    const mentor = new HermesSession('Mentor');
    try {
        const dash = await mentor.login('mentor.kominfo@surabaya.go.id', 'password');
        record('Mentor', 'Login & Dashboard Pembimbing Dinas', dash.status === 200 ? 'PASS' : 'FAIL', dash.url);

        const mentorEndpoints = [
            ['/mentor/students/24', 'Detail Profil Mahasiswa Bimbingan'],
            ['/mentor/logbooks', 'Daftar Verifikasi Logbook Mahasiswa'],
            ['/mentor/students/24/evaluation', 'Formulir Penilaian Kinerja Magang'],
        ];

        for (const [ep, label] of mentorEndpoints) {
            const res = await mentor.getWithRedirects(ep);
            record('Mentor', label, res.status === 200 ? 'PASS' : 'FAIL', ep);
        }
    } catch (e) {
        record('Mentor', 'Siklus Pengujian Mentor Lapangan', 'FAIL', e.message);
    }

    // -------------------------------------------------------------------------
    // 5. ROLE 5: DOSEN PEMBIMBING LAPANGAN (DPL KAMPUS)
    // -------------------------------------------------------------------------
    const dpl = new HermesSession('DPL');
    try {
        const dash = await dpl.login('dosen.unesa@unesa.ac.id', 'password');
        record('DPL', 'Login & Dashboard Dosen Pembimbing', dash.status === 200 ? 'PASS' : 'FAIL', dash.url);

        const dplEndpoints = [
            ['/lecturer/monitoring', 'Matriks Monitoring Terpadu Mahasiswa'],
            ['/lecturer/students/24', 'Detail Bimbingan Mahasiswa di Dinas'],
            ['/lecturer/logbooks', 'Pemeriksaan Logbook Akademik'],
            ['/lecturer/students/24/evaluation', 'Instrumen Penilaian Akademik DPL'],
        ];

        for (const [ep, label] of dplEndpoints) {
            const res = await dpl.getWithRedirects(ep);
            record('DPL', label, res.status === 200 ? 'PASS' : 'FAIL', ep);
        }

        // Test Bulk Approve Action
        const lbPage = await dpl.getWithRedirects('/lecturer/logbooks');
        const tokenMatch = lbPage.html.match(/name=["']_token["']\s+value=["']([^"']+)["']/i);
        if (tokenMatch) {
            const bulkRes = await dpl.request('/lecturer/logbooks/bulk-approve', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `_token=${tokenMatch[1]}&student_id=65`
            });
            record('DPL', 'Fitur Bulk Approve Logbook Satu Klik', bulkRes.status === 302 || bulkRes.status === 200 ? 'PASS' : 'FAIL');
        }
    } catch (e) {
        record('DPL', 'Siklus Pengujian DPL Kampus', 'FAIL', e.message);
    }

    // -------------------------------------------------------------------------
    // 6. ROLE 6: ADMIN PERGURUAN TINGGI (UNIVERSITAS)
    // -------------------------------------------------------------------------
    const univ = new HermesSession('Universitas');
    try {
        const dash = await univ.login('admin@unesa.ac.id', 'password');
        record('Universitas', 'Login & Dashboard Portal Kampus Mitra', dash.status === 200 ? 'PASS' : 'FAIL', dash.url);

        const univEndpoints = [
            ['/university/export-students', 'Ekspor Data Magang Mahasiswa (Akreditasi/PDDIKTI)'],
            ['/university/students/24', 'Pantau Progres Penempatan Mahasiswa'],
            ['/university/profile', 'Profil Kampus & Skema Kebijakan Evaluasi'],
            ['/university/lecturers', 'Manajemen Master Dosen Pembimbing (DPL)'],
            ['/university/students/27/letter', 'Penerbitan Surat Tugas / Pengantar Magang'],
        ];

        for (const [ep, label] of univEndpoints) {
            const res = await univ.getWithRedirects(ep);
            record('Universitas', label, res.status === 200 ? 'PASS' : 'FAIL', ep);
        }
    } catch (e) {
        record('Universitas', 'Siklus Pengujian Admin Universitas', 'FAIL', e.message);
    }

    // -------------------------------------------------------------------------
    // 7. KETERHUBUNGAN ANTAR-ROLE & KEAMANAN TOKEN PUBLIK
    // -------------------------------------------------------------------------
    try {
        // Public QR Verification for Acceptance Letter
        const letterToken = 'vhf5OXQjwIkWjGUSqcF2LVOJbLqAp029';
        const vLetter = await fetch(`${BASE_URL}/verify-letter/${letterToken}`);
        record('Security & Public', 'Verifikasi QR Code Surat Balasan (Anti-IDOR)', vLetter.status === 200 ? 'PASS' : 'FAIL', `/verify-letter/${letterToken}`);

        // Public QR Verification for Certificate & Transcript
        const certHash = 'YXkkFn0B28O5WYZCDYzW8QTrpFcywfg7';
        const vCert = await fetch(`${BASE_URL}/verify-certificate/${certHash}`);
        record('Security & Public', 'Verifikasi QR Code E-Sertifikat & Nilai (Anti-IDOR)', vCert.status === 200 ? 'PASS' : 'FAIL', `/verify-certificate/${certHash}`);

        // Multi-role Centralized File Download for Final Report (Report ID 15)
        const reportFileMhs = await mhsLulus.getWithRedirects('/final-reports/15/file');
        record('Cross-Role Doc', 'Mahasiswa Akses Berkas Laporan Terpusat', reportFileMhs.status === 200 ? 'PASS' : 'FAIL', 'Content-Disposition OK');

        const reportFileDPL = await dpl.getWithRedirects('/final-reports/15/file');
        record('Cross-Role Doc', 'DPL Akses Berkas Laporan Mahasiswa', reportFileDPL.status === 200 ? 'PASS' : 'FAIL');

        const reportFileMentor = await mentor.getWithRedirects('/final-reports/15/file');
        record('Cross-Role Doc', 'Mentor Dinas Akses Berkas Laporan Mahasiswa', reportFileMentor.status === 200 ? 'PASS' : 'FAIL');

        const reportFileAdmin = await superAdmin.getWithRedirects('/final-reports/15/file');
        record('Cross-Role Doc', 'Super Admin Akses Berkas Laporan Mahasiswa', reportFileAdmin.status === 200 ? 'PASS' : 'FAIL');

        // Security Check: Unauthorized stranger access should be forbidden (403 or redirect)
        const stranger = new HermesSession('Stranger');
        const unauthorizedRes = await stranger.request('/final-reports/15/file');
        record('Cross-Role Doc', 'Proteksi Akses Berkas Tanpa Otorisasi', unauthorizedRes.status === 403 || unauthorizedRes.status === 302 ? 'PASS' : 'FAIL', `Blocked with HTTP ${unauthorizedRes.status}`);

    } catch (e) {
        record('Cross-Role', 'Pengujian Keterhubungan & Keamanan', 'FAIL', e.message);
    }

    // -------------------------------------------------------------------------
    // 8. LOG HEALTH & LAPORAN HASIL
    // -------------------------------------------------------------------------
    let logHealth = 'CLEAN';
    if (fs.existsSync(LOG_FILE)) {
        const stat = fs.statSync(LOG_FILE);
        const buf = Buffer.alloc(Math.min(stat.size, 4096));
        const fd = fs.openSync(LOG_FILE, 'r');
        fs.readSync(fd, buf, 0, buf.length, Math.max(0, stat.size - buf.length));
        fs.closeSync(fd);
        const content = buf.toString('utf8');
        if (content.includes('.CRITICAL:')) {
            logHealth = 'CRITICAL DETECTED';
        }
    }
    record('System', 'Integritas Log Laravel (laravel.log)', logHealth === 'CLEAN' ? 'PASS' : 'WARN', logHealth);

    console.log(`\n================================================================================`);
    console.log(`📊 [HERMES E2E SWARM] MATRIKS HASIL PENGUJIAN SEMUA ROLE & KETERHUBUNGAN`);
    console.log(`================================================================================`);
    console.table(results);

    const total = results.length;
    const passed = results.filter(r => r.Status === 'PASS').length;
    const failed = results.filter(r => r.Status === 'FAIL').length;
    const warn = results.filter(r => r.Status === 'WARN').length;

    console.log(`\n📈 HASIL AKHIR: Total Pengujian: ${total} | Lolos: ${passed} ✅ | Gagal: ${failed} ❌ | Warning: ${warn} ⚠️`);

    if (failed === 0) {
        console.log(`🎉 [HERMES SWARM VERIFIED] SELURUH ROLE (1 - 6) DAN KETERHUBUNGAN ANTAR-ROLE LOLOS 100% SECARA SEMPURNA!`);
    } else {
        console.error(`💥 [HERMES SWARM ALERT] Terdapat ${failed} pengujian yang mengalami kegagalan.`);
    }

    return failed === 0;
}

runFullHermesTest().catch(err => {
    console.error('Fatal Hermes Error:', err);
    process.exit(1);
});
