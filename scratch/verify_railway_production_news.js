import puppeteer from 'puppeteer';
import fs from 'fs';
import path from 'path';

const outDir = 'd:/final project web/documentation/screenshots';

async function verifyRailwayProductionNews() {
    console.log('===========================================================');
    console.log('   RAILWAY PRODUCTION NEWS PAGE AUDIT & VERIFICATION       ');
    console.log('===========================================================');

    const railwayUrl = 'https://global-supply-production.up.railway.app';
    console.log(`Target Railway Deployment: ${railwayUrl}`);

    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1920,1080']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080, deviceScaleFactor: 2 });

    try {
        console.log(`Navigating to ${railwayUrl}/login...`);
        const resp = await page.goto(`${railwayUrl}/login`, { waitUntil: 'networkidle2', timeout: 45000 });
        console.log(`Login Page Response HTTP Status: ${resp ? resp.status() : 'N/A'}`);

        const emailInput = await page.$('#email');
        if (emailInput) {
            console.log('Logging in as admin@gscrip.com...');
            await page.type('#email', 'admin@gscrip.com');
            await page.type('#password', 'password');
            await Promise.all([
                page.click('button[type="submit"]'),
                page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 35000 }).catch(() => {})
            ]);
            await new Promise(r => setTimeout(r, 1500));
        }

        console.log(`Navigating to Production News Page: ${railwayUrl}/news...`);
        await page.goto(`${railwayUrl}/news`, { waitUntil: 'networkidle2', timeout: 35000 });
        await new Promise(r => setTimeout(r, 2000));

        // Screenshot of Deployed Production News Page
        const screenshotPath = path.join(outDir, 'railway-news-verified.png');
        await page.screenshot({ path: screenshotPath, fullPage: false });
        console.log(`Production screenshot saved to: ${screenshotPath}`);

        // Extract cards from DOM
        const cards = await page.$$eval('#news-articles-grid .card', cardElements => {
            return cardElements.map((card, idx) => {
                const titleEl = card.querySelector('h6');
                const title = titleEl ? titleEl.innerText.trim() : 'N/A';

                const sourceBtn = card.querySelector('a[target="_blank"]');
                const demoBadge = card.querySelector('.badge.bg-secondary');

                let uiElement = 'UNKNOWN';
                let sourceUrl = 'N/A';

                if (sourceBtn) {
                    uiElement = 'SOURCE BUTTON';
                    sourceUrl = sourceBtn.href;
                } else if (demoBadge && demoBadge.innerText.includes('DEMO DATA')) {
                    uiElement = 'DEMO DATA BADGE';
                    sourceUrl = '(None - Demo Data Fallback)';
                }

                const sourceNameEl = card.querySelector('.card-footer');
                const sourceName = sourceNameEl ? sourceNameEl.innerText.trim() : 'N/A';

                return {
                    index: idx + 1,
                    title,
                    sourceName,
                    uiElement,
                    sourceUrl
                };
            });
        });

        console.log('\n=======================================================================================================================');
        console.log('                                RAILWAY PRODUCTION DEPLOYED /NEWS CARDS AUDIT                                           ');
        console.log('=======================================================================================================================\n');
        console.log(`Total Rendered Cards on Production Page: ${cards.length}\n`);

        cards.forEach(c => {
            console.log(`[Card #${c.index}]`);
            console.log(`  Title       : ${c.title}`);
            console.log(`  Source Name : ${c.sourceName}`);
            console.log(`  UI Element  : ${c.uiElement}`);
            console.log(`  Source URL  : ${c.sourceUrl}`);
            console.log('-----------------------------------------------------------------------------------------------------------------------');
        });

        await browser.close();
        console.log('\n🎉 Production Verification Completed Successfully!');

    } catch (err) {
        console.error('Fatal error during production verification:', err.message);
        await browser.close();
        process.exit(1);
    }
}

verifyRailwayProductionNews();
