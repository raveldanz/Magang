const fs = require('fs');

const BASE_URL = 'http://127.0.0.1:8000';

async function getSession(email, password) {
    const res1 = await fetch(`${BASE_URL}/login`);
    const html1 = await res1.text();
    const tokenMatch = html1.match(/name=["']_token["']\s+value=["']([^"']+)["']/i);
    const csrfToken = tokenMatch[1];
    
    const cookieHeader = res1.headers.get('set-cookie');
    const cookies = cookieHeader.split(',').map(c => c.split(';')[0]).join('; ');

    const loginRes = await fetch(`${BASE_URL}/login`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Cookie': cookies
        },
        body: new URLSearchParams({ _token: csrfToken, email, password }),
        redirect: 'manual'
    });

    const loginCookieHeader = loginRes.headers.get('set-cookie');
    return loginCookieHeader ? loginCookieHeader.split(',').map(c => c.split(';')[0]).join('; ') : cookies;
}

async function runDeepScan() {
    console.log('--- STARTING DEEP ROUTE SCAN ACROSS ALL ROLES ---');
    const { execSync } = require('child_process');
    const routesRaw = execSync('php artisan route:list --json', { encoding: 'utf8' });
    const routesData = JSON.parse(routesRaw);

    const sessions = {
        public: null,
        superadmin: await getSession('admin@surabaya.go.id', 'password'),
        admindinas: await getSession('admin.diskominfo@surabaya.go.id', 'password'),
        mahasiswa: await getSession('mahasiswa.aktif@unesa.ac.id', 'password'),
        mahasiswalulus: await getSession('mahasiswa.lulus@unesa.ac.id', 'password'),
        mentor: await getSession('mentor.kominfo@surabaya.go.id', 'password'),
        dosen: await getSession('dosen.unesa@unesa.ac.id', 'password'),
        universitas: await getSession('universitas.unesa@unesa.ac.id', 'password'),
    };

    console.log('All 7 authenticated sessions established successfully.');

    const errors = [];
    const tested = [];

    for (const r of routesData) {
        if (!r.method.includes('GET') && !r.method.includes('HEAD')) continue;
        const uri = r.uri;
        if (uri.startsWith('_ignition') || uri.startsWith('sanctum') || uri.startsWith('telescope') || uri.startsWith('pulse')) continue;

        // Determine which role to test with based on prefix
        let role = 'public';
        let path = uri;

        if (uri.startsWith('admin')) role = 'superadmin';
        else if (uri.startsWith('student')) role = 'mahasiswa';
        else if (uri.startsWith('mentor')) role = 'mentor';
        else if (uri.startsWith('lecturer')) role = 'dosen';
        else if (uri.startsWith('university')) role = 'universitas';
        else if (uri.startsWith('profile') || uri.startsWith('dashboard') || uri.startsWith('feedbacks')) role = 'mahasiswa';

        // Replace parameters
        let resolvedUri = uri
            .replace('{id}', '47')
            .replace('{application}', '47')
            .replace('{placement}', '37')
            .replace('{student}', '45')
            .replace('{unit}', '11')
            .replace('{user}', '1')
            .replace('{agency}', '7')
            .replace('{university}', '1')
            .replace('{mentor}', '28')
            .replace('{token}', 'MUTY8U4Q6TSaGDd6NW4SSts6kYdVALMT')
            .replace('{certificate_hash}', 'XoFjguLTmZ9ScbZWUxuQSENWX8DGZOFO');

        if (resolvedUri.includes('{')) {
            // Still has unreplaced params, skip
            continue;
        }

        const cookie = sessions[role];
        const headers = cookie ? { 'Cookie': cookie } : {};

        try {
            const res = await fetch(`${BASE_URL}/${resolvedUri}`, { headers, redirect: 'manual' });
            if (res.status === 500) {
                const html = await res.text();
                const titleMatch = html.match(/<title>(.*?)<\/title>/i);
                const title = titleMatch ? titleMatch[1] : 'Unknown 500 error';
                console.log(`❌ 500 ERROR: GET /${resolvedUri} [${role}] -> ${title}`);
                errors.push({ uri: resolvedUri, role, title, htmlSnippet: html.substring(0, 400) });
            } else {
                tested.push({ uri: resolvedUri, role, status: res.status });
            }
        } catch (err) {
            console.log(`❌ FETCH ERROR: GET /${resolvedUri} -> ${err.message}`);
            errors.push({ uri: resolvedUri, role, title: err.message });
        }
    }

    console.log(`\n--- SCAN RESULTS ---`);
    console.log(`Total Routes Tested: ${tested.length + errors.length}`);
    console.log(`Successful/Non-500 Routes: ${tested.length}`);
    console.log(`500 Error Routes: ${errors.length}`);

    if (errors.length > 0) {
        console.log('\n--- DETAILED ERRORS ---');
        for (const e of errors) {
            console.log(`\n[${e.role}] /${e.uri}: ${e.title}`);
            console.log(e.htmlSnippet);
        }
    }
}

runDeepScan().catch(console.error);
