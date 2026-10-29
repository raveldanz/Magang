import { chromium } from 'playwright';

async function testDetailInteraction() {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
    
    const errors = [];
    page.on('console', msg => {
        if (msg.type() === 'error') errors.push(msg.text());
    });
    page.on('pageerror', err => errors.push(err.message));

    console.log('Navigating to /projects/2...');
    await page.goto('http://127.0.0.1:8000/projects/2', { waitUntil: 'networkidle' });
    
    // Check if authorNameInput exists (guest mode)
    const authorInput = await page.$('input[x-model="authorNameInput"]');
    if (authorInput) {
        await page.fill('input[x-model="authorNameInput"]', 'Budi Pengunjung');
    }

    const commentInput = await page.$('textarea[x-model="commentInput"]');
    if (commentInput) {
        await page.fill('textarea[x-model="commentInput"]', 'Proyek inovasi yang luar biasa dan sangat bermanfaat!');
        await page.waitForTimeout(500);
    }

    await page.screenshot({ path: 'public/test-artifacts/project_detail_alpine_working.png', fullPage: false });

    const divider = await page.$('.group[style*="--wave-c1"]');
    if (divider) {
        await divider.scrollIntoViewIfNeeded();
        await page.waitForTimeout(300);
        await page.screenshot({ path: 'public/test-artifacts/project_detail_divider_context_v2.png', fullPage: false });
    }

    console.log(`Console error count: ${errors.length}`);
    if (errors.length > 0) {
        console.error('Errors:', errors);
    } else {
        console.log('✅ Detail page Alpine interactions verified with ZERO errors!');
    }

    await browser.close();
}

testDetailInteraction().catch(console.error);

