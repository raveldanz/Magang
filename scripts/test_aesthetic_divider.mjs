import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');

async function render() {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const page = await browser.newPage({ viewport: { width: 1200, height: 300 } });
    
    const svgPath = path.resolve(projectRoot, 'public/images/pembatas_dinas_vector.svg');
    const svgContent = fs.readFileSync(svgPath, 'utf-8');
    
    await page.setContent(`
        <!DOCTYPE html>
        <html>
        <head>
            <script src="https://cdn.tailwindcss.com"></script>
        </head>
        <body class="bg-[#F8FAFC] p-12 flex flex-col justify-center items-center">
            <div class="w-full max-w-5xl">
                <!-- Concept: Connecting Left & Right Lines with delicate center ornament -->
                <div class="flex items-center justify-center gap-4 w-full my-6">
                    <!-- Left gradient line -->
                    <div class="flex-1 h-[1.5px] bg-gradient-to-r from-transparent via-blue-200/60 to-blue-400/80 rounded-full"></div>
                    
                    <!-- Center Ornament SVG -->
                    <div class="shrink-0 w-80 sm:w-96 flex items-center justify-center">
                        ${svgContent}
                    </div>

                    <!-- Right gradient line -->
                    <div class="flex-1 h-[1.5px] bg-gradient-to-l from-transparent via-blue-200/60 to-blue-400/80 rounded-full"></div>
                </div>
            </div>
        </body>
        </html>
    `);
    
    await page.waitForTimeout(1000);
    const screenshotPath = path.resolve(projectRoot, 'public/test-artifacts/aesthetic_divider_concept.png');
    await page.screenshot({ path: screenshotPath });
    console.log('Saved:', screenshotPath);
    await browser.close();
}

render();
