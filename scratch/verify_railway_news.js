import puppeteer from 'puppeteer';
import fs from 'fs';
import path from 'path';

const outDir = 'd:/final project web/documentation/screenshots';
if (!fs.existsSync(outDir)) {
    fs.mkdirSync(outDir, { recursive: true });
}

async function verifyProduction() {
    console.log('Connecting to Railway Production Deployment...');
    const railwayUrl = 'https://global-supply-production.up.railway.app';

    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1920,1080']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080, deviceScaleFactor: 2 });

    try {
        console.log(`Navigating to Railway Production: ${railwayUrl}/login...`);
        const resp = await page.goto(`${railwayUrl}/login`, { waitUntil: 'networkidle2', timeout: 45000 });
        console.log(`HTTP Status: ${resp ? resp.status() : 'Unknown'}`);

        const emailInput = await page.$('#email');
        if (emailInput) {
            console.log('Authenticating on Railway Production...');
            await page.type('#email', 'admin@gscrip.com');
            await page.type('#password', 'password');
            await Promise.all([
                page.click('button[type="submit"]'),
                page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 30000 }).catch(() => {})
            ]);
            await new Promise(r => setTimeout(r, 2000));
        }

        // Navigate to User Dashboard (where News feed is rendered)
        console.log('Navigating to Production Dashboard News Feed...');
        await page.goto(`${railwayUrl}/user/dashboard`, { waitUntil: 'networkidle2', timeout: 30000 });
        await new Promise(r => setTimeout(r, 3000));

        // Capture Production News Section Screenshot
        const newsScreenshotPath = path.join(outDir, 'railway-news-production.png');
        await page.screenshot({ path: newsScreenshotPath, fullPage: false });
        console.log(`Saved production news screenshot to: ${newsScreenshotPath}`);

        // Check Api Monitoring / Observability page on Railway
        console.log('Navigating to Railway API Monitoring / Operations page...');
        await page.goto(`${railwayUrl}/admin/api-monitoring`, { waitUntil: 'networkidle2', timeout: 30000 });
        await new Promise(r => setTimeout(r, 2000));

        const logsScreenshotPath = path.join(outDir, 'railway-logs-production.png');
        await page.screenshot({ path: logsScreenshotPath, fullPage: false });
        console.log(`Saved production logs screenshot to: ${logsScreenshotPath}`);

        await browser.close();
        console.log('Railway Production Verification Complete!');

    } catch (err) {
        console.error('Production connection error:', err.message);
        await browser.close();
    }
}

verifyProduction();
