import puppeteer from 'puppeteer';

async function clearRailwayCache() {
    console.log('Connecting to Railway Admin Panel to clear cache and refresh news...');
    const railwayUrl = 'https://global-supply-production.up.railway.app';

    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080 });

    // Login as Admin
    await page.goto(`${railwayUrl}/login`, { waitUntil: 'networkidle2' });
    const emailInput = await page.$('#email');
    if (emailInput) {
        await page.type('#email', 'admin@gscrip.com');
        await page.type('#password', 'password');
        await Promise.all([
            page.click('button[type="submit"]'),
            page.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {})
        ]);
    }

    // Go to Admin Operations / Health page
    console.log('Navigating to /admin/operations...');
    await page.goto(`${railwayUrl}/admin/operations`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 2000));

    // Also navigate to /admin/sync
    console.log('Navigating to /admin/sync...');
    await page.goto(`${railwayUrl}/admin/sync`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 2000));

    await browser.close();
    console.log('Railway Admin Cache Clear Operation Complete!');
}

clearRailwayCache().catch(err => {
    console.error('Error:', err.message);
    process.exit(1);
});
