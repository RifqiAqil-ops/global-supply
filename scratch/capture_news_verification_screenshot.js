import puppeteer from 'puppeteer';
import fs from 'fs';
import path from 'path';

const outDir = 'd:/final project web/documentation/screenshots';

async function capture() {
    console.log('Capturing verified /news page screenshot...');
    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1920,1080']
    });

    const baseUrl = 'http://127.0.0.1:8000';
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

    const screenshotPath = path.join(outDir, '3.2-news-verification.png');
    await page.screenshot({ path: screenshotPath, fullPage: false });
    console.log(`Saved verification screenshot to: ${screenshotPath}`);

    await browser.close();
}

capture().catch(err => {
    console.error('Fatal error:', err);
    process.exit(1);
});
