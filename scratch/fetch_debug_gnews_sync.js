import puppeteer from 'puppeteer';

async function fetchDebugGNewsSync() {
    console.log('Hitting Railway Production /debug-gnews-sync Endpoint...');
    const railwayUrl = 'https://global-supply-production.up.railway.app';

    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080 });

    // Login as Admin
    await page.goto(`${railwayUrl}/login`, { waitUntil: 'networkidle2' });
    await page.type('#email', 'admin@gscrip.com');
    await page.type('#password', 'password');
    await Promise.all([
        page.click('button[type="submit"]'),
        page.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {})
    ]);

    // Go to /debug-gnews-sync
    console.log('Navigating to /debug-gnews-sync...');
    const response = await page.goto(`${railwayUrl}/debug-gnews-sync`, { waitUntil: 'networkidle2' });
    console.log(`HTTP Status: ${response.status()}`);

    const text = await page.evaluate(() => document.body.innerText);
    console.log('\n=======================================================================================================================');
    console.log('RAILWAY PRODUCTION /DEBUG-GNEWS-SYNC DIAGNOSTIC OUTPUT:');
    console.log('=======================================================================================================================\n');
    console.log(text);
    console.log('=======================================================================================================================\n');

    await browser.close();
}

fetchDebugGNewsSync().catch(err => {
    console.error('Error:', err.message);
    process.exit(1);
});
