import puppeteer from 'puppeteer';
import fs from 'fs';
import path from 'path';

const outDir = 'd:/final project web/documentation/screenshots';

async function verifyPage1NewsOrder() {
    console.log('Verifying Page 1 /news Card Order...');
    const baseUrl = 'http://127.0.0.1:8000';

    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1920,1080']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080, deviceScaleFactor: 2 });

    // Authenticate
    await page.goto(`${baseUrl}/login`, { waitUntil: 'networkidle2' });
    const emailInput = await page.$('#email');
    if (emailInput) {
        await page.type('#email', 'admin@gscrip.com');
        await page.type('#password', 'password');
        await Promise.all([
            page.click('button[type="submit"]'),
            page.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {})
        ]);
        await new Promise(r => setTimeout(r, 1000));
    }

    // Navigate to /news Page 1
    console.log('Navigating to /news Page 1...');
    await page.goto(`${baseUrl}/news`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1500));

    // Save screenshot
    const screenshotPath = path.join(outDir, 'page1-news-order-verified.png');
    await page.screenshot({ path: screenshotPath, fullPage: false });
    console.log(`Saved screenshot to: ${screenshotPath}`);

    // Extract cards
    const cards = await page.$$eval('#news-articles-grid .card', cardElements => {
        return cardElements.map((card, idx) => {
            const titleEl = card.querySelector('h6');
            const title = titleEl ? titleEl.innerText.trim() : 'N/A';

            const sourceBtn = card.querySelector('a[target="_blank"]');
            const demoBadge = card.querySelector('.badge.bg-secondary');

            let type = 'UNKNOWN';
            let url = 'N/A';

            if (sourceBtn) {
                type = 'LIVE API (Source Button)';
                url = sourceBtn.href;
            } else if (demoBadge) {
                type = 'DEMO DATA (Demo Badge)';
                url = '(None)';
            }

            const sourceNameEl = card.querySelector('.card-footer');
            const sourceName = sourceNameEl ? sourceNameEl.innerText.trim() : 'N/A';

            return {
                index: idx + 1,
                title,
                sourceName,
                type,
                url
            };
        });
    });

    console.log('\n=======================================================================================================================');
    console.log('                                        PAGE 1 /NEWS CARDS ORDER AUDIT REPORT                                          ');
    console.log('=======================================================================================================================\n');

    let liveCount = 0;
    let demoCount = 0;

    cards.forEach(c => {
        if (c.type.includes('LIVE')) liveCount++;
        if (c.type.includes('DEMO')) demoCount++;

        console.log(`Card #${c.index.toString().padStart(2, '0')} | Status: ${c.type.padEnd(25)} | Publisher: ${c.sourceName.padEnd(20)} | Title: ${c.title.slice(0, 45)}...`);
        if (c.url !== '(None)') {
            console.log(`        └── URL: ${c.url}`);
        }
    });

    console.log('\n=======================================================================================================================');
    console.log(`SUMMARY: ${liveCount} Live API Articles (with Source buttons) | ${demoCount} Demo Data Articles`);
    console.log('=======================================================================================================================\n');

    await browser.close();
}

verifyPage1NewsOrder().catch(err => {
    console.error('Fatal error verifying news order:', err);
    process.exit(1);
});
