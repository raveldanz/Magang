import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');

async function render() {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const page = await browser.newPage({ viewport: { width: 1000, height: 200 } });
    
    const svgPath = path.resolve(projectRoot, 'public/images/divider_dinas_vector.svg');
    const svgContent = fs.readFileSync(svgPath, 'utf-8');
    
    await page.setContent(`
        <!DOCTYPE html>
        <html>
        <body style="margin: 0; padding: 40px 20px; background: #ffffff; display: flex; justify-content: center; align-items: center;">
            <div style="width: 800px;">
                ${svgContent}
            </div>
        </body>
        </html>
    `);
    
    await page.screenshot({ path: path.resolve(projectRoot, 'public/test-artifacts/divider_vector_preview.png') });
    await browser.close();
    console.log('Saved public/test-artifacts/divider_vector_preview.png');
}

render();
