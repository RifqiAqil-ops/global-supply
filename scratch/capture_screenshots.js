import puppeteer from 'puppeteer';
import fs from 'fs';
import path from 'path';

const outDir = 'd:/final project web/documentation/screenshots';
if (!fs.existsSync(outDir)) {
    fs.mkdirSync(outDir, { recursive: true });
}

async function capture() {
    console.log('Launching headless browser...');
    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1920,1080']
    });

    const baseUrl = 'http://127.0.0.1:8000';

    // 1. Halaman Login (Guest Context)
    console.log('[1/8] Capturing 3.1-login.png...');
    const guestContext = await browser.createBrowserContext();
    const guestPage = await guestContext.newPage();
    await guestPage.setViewport({ width: 1920, height: 1080, deviceScaleFactor: 2 });
    
    await guestPage.goto(`${baseUrl}/login`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1000));
    await guestPage.screenshot({ path: path.join(outDir, '3.1-login.png'), fullPage: false });
    await guestContext.close();

    // 2. Authenticated Context for All Remaining Dashboards
    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080, deviceScaleFactor: 2 });

    console.log('Navigating to login / authenticating...');
    await page.goto(`${baseUrl}/login`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 500));

    const emailInput = await page.$('#email');
    if (emailInput) {
        console.log('Filling login form as admin@gscrip.com...');
        await page.type('#email', 'admin@gscrip.com');
        await page.type('#password', 'password');
        await Promise.all([
            page.click('button[type="submit"]'),
            page.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {})
        ]);
        await new Promise(r => setTimeout(r, 1000));
    }

    // 2. Dashboard Utama (User / Global Overview Dashboard)
    console.log('[2/8] Capturing 3.2-dashboard.png...');
    await page.goto(`${baseUrl}/user/dashboard`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 2500)); // Wait for World Map & Charts
    await page.screenshot({ path: path.join(outDir, '3.2-dashboard.png'), fullPage: false });

    // 3. Halaman Country
    console.log('[3/8] Capturing 3.3-country.png...');
    await page.goto(`${baseUrl}/countries`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1500));
    await page.screenshot({ path: path.join(outDir, '3.3-country.png'), fullPage: false });

    // 4. Halaman Detail Country (Indonesia - ID)
    console.log('[4/8] Capturing 3.4-country-detail.png...');
    await page.goto(`${baseUrl}/countries/ID`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 2500));
    await page.screenshot({ path: path.join(outDir, '3.4-country-detail.png'), fullPage: false });

    // 5. Dashboard Currency
    console.log('[5/8] Capturing 3.5-currency.png...');
    await page.goto(`${baseUrl}/currency`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 2000));
    await page.screenshot({ path: path.join(outDir, '3.5-currency.png'), fullPage: false });

    // 6. Dashboard Risk
    console.log('[6/8] Capturing 3.6-risk.png...');
    await page.goto(`${baseUrl}/risk-history`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1500));
    await page.screenshot({ path: path.join(outDir, '3.6-risk.png'), fullPage: false });

    // 7. Monitoring Dashboard (Observability Center)
    console.log('[7/8] Capturing 3.7-monitoring.png...');
    await page.goto(`${baseUrl}/admin/observability`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1500));
    await page.screenshot({ path: path.join(outDir, '3.7-monitoring.png'), fullPage: false });

    // 8. Halaman Deployment (System Health / Railway Status)
    console.log('[8/8] Capturing 3.8-deployment.png...');
    await page.goto(`${baseUrl}/admin/health`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1500));
    await page.screenshot({ path: path.join(outDir, '3.8-deployment.png'), fullPage: false });

    await browser.close();
    console.log('🎉 ALL 8 SCREENSHOTS CAPTURED SUCCESSFULLY!');
}

capture().catch(err => {
    console.error('Fatal error capturing screenshots:', err);
    process.exit(1);
});
