import puppeteer from 'puppeteer';

async function testRailwayDirectNews() {
    console.log('Testing Railway Production Live API News Endpoint...');
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

    // Request /live-api/news
    console.log('Fetching /live-api/news...');
    const resp = await page.goto(`${railwayUrl}/live-api/news`, { waitUntil: 'networkidle2' });
    console.log(`Live API HTTP Status: ${resp.status()}`);
    const jsonText = await resp.text();
    console.log(`Live API News Response:\n${jsonText.slice(0, 1000)}`);

    await browser.close();
}

testRailwayDirectNews().catch(err => {
    console.error('Error:', err.message);
    process.exit(1);
});
