import puppeteer from 'puppeteer';
import fs from 'fs';
import path from 'path';

const outDir = 'd:/final project web/documentation/screenshots';

async function captureCleanNews() {
    console.log('Capturing clean /news page screenshot...');
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

    // Navigate to /news
    await page.goto(`${baseUrl}/news`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1500));

    const screenshotPath = path.join(outDir, 'news-clean-no-demo.png');
    await page.screenshot({ path: screenshotPath, fullPage: false });
    console.log(`Saved clean news screenshot to: ${screenshotPath}`);

    // Count DOM cards and check badges
    const cards = await page.$$eval('#news-articles-grid .card', cardElements => {
        return cardElements.map((card, idx) => {
            const titleEl = card.querySelector('h6');
            const title = titleEl ? titleEl.innerText.trim() : 'N/A';
            const sourceBtn = card.querySelector('a[target="_blank"]');
            const demoBadge = card.querySelector('.badge');
            const sourceNameEl = card.querySelector('.card-footer');

            return {
                index: idx + 1,
                title,
                publisher: sourceNameEl ? sourceNameEl.innerText.trim() : 'N/A',
                hasSourceBtn: !!sourceBtn,
                url: sourceBtn ? sourceBtn.href : 'N/A',
                badgeText: demoBadge ? demoBadge.innerText.trim() : 'N/A'
            };
        });
    });

    console.log('\n=======================================================================================================================');
    console.log(`RENDERED REAL CARDS ON /NEWS PAGE (${cards.length} TOTAL):`);
    console.log('=======================================================================================================================\n');

    let demoBadgeFound = false;

    cards.forEach(c => {
        if (c.badgeText.includes('DEMO')) demoBadgeFound = true;
        console.log(`Card #${c.index} | Publisher: ${c.publisher.padEnd(20)} | Source Btn: ${c.hasSourceBtn ? 'YES' : 'NO'} | Title: ${c.title.slice(0, 45)}...`);
        console.log(`        └── Original URL: ${c.url}`);
    });

    console.log('\n=======================================================================================================================');
    console.log(`DEMO DATA BADGE FOUND ANYWHERE ON PAGE: ${demoBadgeFound ? 'YES (ERROR)' : 'NO (PASSED 100%)'}`);
    console.log('=======================================================================================================================\n');

    await browser.close();
}

captureCleanNews().catch(err => {
    console.error('Fatal error:', err);
    process.exit(1);
});
