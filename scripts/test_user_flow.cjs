const fs = require('fs');

async function testAll() {
    console.log('Testing Admin Letter 47...');
    
    // 1. Get CSRF & Cookie
    const res1 = await fetch('http://127.0.0.1:8000/login');
    const html1 = await res1.text();
    const tokenMatch = html1.match(/name=["']_token["']\s+value=["']([^"']+)["']/i);
    const csrfToken = tokenMatch[1];
    
    const cookieHeader = res1.headers.get('set-cookie');
    const cookies = cookieHeader.split(',').map(c => c.split(';')[0]).join('; ');

    // 2. Login as SuperAdmin
    const loginRes = await fetch('http://127.0.0.1:8000/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Cookie': cookies
        },
        body: new URLSearchParams({ _token: csrfToken, email: 'admin@surabaya.go.id', password: 'password' }),
        redirect: 'manual'
    });

    const loginCookieHeader = loginRes.headers.get('set-cookie');
    const adminCookies = loginCookieHeader ? loginCookieHeader.split(',').map(c => c.split(';')[0]).join('; ') : cookies;

    // 3. Test GET /admin/applications/47/letter
    const letterRes = await fetch('http://127.0.0.1:8000/admin/applications/47/letter', {
        headers: { 'Cookie': adminCookies }
    });
    console.log('GET /admin/applications/47/letter status:', letterRes.status);
    const letterHtml = await letterRes.text();
    if (letterRes.status !== 200) {
        console.log('Letter 47 ERROR snippet:', letterHtml.substring(0, 800));
    } else {
        console.log('Letter 47 OK! Length:', letterHtml.length);
    }

    // 4. Test GET /admin/applications/47
    const app47Res = await fetch('http://127.0.0.1:8000/admin/applications/47', {
        headers: { 'Cookie': adminCookies }
    });
    console.log('GET /admin/applications/47 status:', app47Res.status);

    // 5. Test Student Final Report page
    const mhsLoginRes = await fetch('http://127.0.0.1:8000/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Cookie': cookies
        },
        body: new URLSearchParams({ _token: csrfToken, email: 'mahasiswa.aktif@unesa.ac.id', password: 'password' }),
        redirect: 'manual'
    });
    const mhsCookieHeader = mhsLoginRes.headers.get('set-cookie');
    const mhsCookies = mhsCookieHeader ? mhsCookieHeader.split(',').map(c => c.split(';')[0]).join('; ') : cookies;

    const finalReportRes = await fetch('http://127.0.0.1:8000/student/final-report', {
        headers: { 'Cookie': mhsCookies }
    });
    console.log('GET /student/final-report status:', finalReportRes.status);
    const frHtml = await finalReportRes.text();
    if (finalReportRes.status !== 200) {
        console.log('Final Report ERROR:', frHtml.substring(0, 800));
    } else {
        console.log('Final Report page OK! Length:', frHtml.length);
    }
}

testAll().catch(console.error);
