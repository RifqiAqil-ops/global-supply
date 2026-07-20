import puppeteer from 'puppeteer';
import fs from 'fs';
import path from 'path';

const outDir = 'd:/final project web/documentation/screenshots';

// Empty directory or recreate it
if (fs.existsSync(outDir)) {
    fs.rmSync(outDir, { recursive: true, force: true });
}
fs.mkdirSync(outDir, { recursive: true });

async function run() {
    console.log('Launching Puppeteer browser at 1920x1080...');
    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1920,1080']
    });

    const baseUrl = 'http://127.0.0.1:8000';

    // 1. Register Page (Guest Context)
    console.log('[1/12] Capturing 3.1-register.png...');
    const guestCtx1 = await browser.createBrowserContext();
    const p1 = await guestCtx1.newPage();
    await p1.setViewport({ width: 1920, height: 1080, deviceScaleFactor: 2 });
    await p1.goto(`${baseUrl}/register`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1000));
    await p1.screenshot({ path: path.join(outDir, '3.1-register.png') });
    await guestCtx1.close();

    // 2. Login Page (Guest Context)
    console.log('[2/12] Capturing 3.2-login.png...');
    const guestCtx2 = await browser.createBrowserContext();
    const p2 = await guestCtx2.newPage();
    await p2.setViewport({ width: 1920, height: 1080, deviceScaleFactor: 2 });
    await p2.goto(`${baseUrl}/login`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1000));
    await p2.screenshot({ path: path.join(outDir, '3.2-login.png') });
    await guestCtx2.close();

    // Authenticated session for all main dashboards
    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080, deviceScaleFactor: 2 });
    await page.goto(`${baseUrl}/login`, { waitUntil: 'networkidle2' });
    const emailInput = await page.$('#email');
    if (emailInput) {
        console.log('Authenticating as admin@gscrip.com...');
        await page.type('#email', 'admin@gscrip.com');
        await page.type('#password', 'password');
        await Promise.all([
            page.click('button[type="submit"]'),
            page.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {})
        ]);
        await new Promise(r => setTimeout(r, 1000));
    }

    // 3. Dashboard Screenshots (3 sections)
    console.log('Navigating to Main Overview Dashboard...');
    await page.goto(`${baseUrl}/user/dashboard`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 3000)); // Wait for map & charts

    console.log('[3/12] Capturing 3.3-dashboard-1.png (Main Overview)...');
    await page.evaluate(() => window.scrollTo(0, 0));
    await new Promise(r => setTimeout(r, 500));
    await page.screenshot({ path: path.join(outDir, '3.3-dashboard-1.png') });

    console.log('[4/12] Capturing 3.3-dashboard-2.png (Statistics Cards & Map)...');
    await page.evaluate(() => window.scrollTo(0, 450));
    await new Promise(r => setTimeout(r, 500));
    await page.screenshot({ path: path.join(outDir, '3.3-dashboard-2.png') });

    console.log('[5/12] Capturing 3.3-dashboard-3.png (Charts & Widgets)...');
    await page.evaluate(() => window.scrollTo(0, 950));
    await new Promise(r => setTimeout(r, 500));
    await page.screenshot({ path: path.join(outDir, '3.3-dashboard-3.png') });

    // 4. Global Countries
    console.log('[6/12] Capturing 3.4-global-countries.png...');
    await page.goto(`${baseUrl}/countries`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1500));
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.screenshot({ path: path.join(outDir, '3.4-global-countries.png') });

    // 5. Country Detail (Indonesia - ID) (2 sections)
    console.log('Navigating to Country Detail (Indonesia)...');
    await page.goto(`${baseUrl}/countries/ID`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 2500));

    console.log('[7/12] Capturing 3.5-country-detail-1.png (Profile Header)...');
    await page.evaluate(() => window.scrollTo(0, 0));
    await new Promise(r => setTimeout(r, 500));
    await page.screenshot({ path: path.join(outDir, '3.5-country-detail-1.png') });

    console.log('[8/12] Capturing 3.5-country-detail-2.png (Weather, Currency & Ports)...');
    await page.evaluate(() => window.scrollTo(0, 600));
    await new Promise(r => setTimeout(r, 500));
    await page.screenshot({ path: path.join(outDir, '3.5-country-detail-2.png') });

    // 6. Currency Dashboard
    console.log('[9/12] Capturing 3.6-currency-dashboard.png...');
    await page.goto(`${baseUrl}/currency`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 2000));
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.screenshot({ path: path.join(outDir, '3.6-currency-dashboard.png') });

    // 7. Risk Dashboard
    console.log('[10/12] Capturing 3.7-risk-dashboard.png...');
    await page.goto(`${baseUrl}/risk-history`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1500));
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.screenshot({ path: path.join(outDir, '3.7-risk-dashboard.png') });

    // 8. Monitoring Dashboard
    console.log('[11/12] Capturing 3.8-monitoring-dashboard.png...');
    await page.goto(`${baseUrl}/admin/observability`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1500));
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.screenshot({ path: path.join(outDir, '3.8-monitoring-dashboard.png') });

    // 9. Deployment Page
    console.log('[12/12] Capturing 3.9-deployment.png...');
    await page.goto(`${baseUrl}/admin/health`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1500));
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.screenshot({ path: path.join(outDir, '3.9-deployment.png') });

    await browser.close();
    console.log('🎉 ALL 12 BAB III DOCUMENTATION SCREENSHOTS GENERATED SUCCESSFULLY!');
}

run().catch(err => {
    console.error('Fatal error:', err);
    process.exit(1);
});
