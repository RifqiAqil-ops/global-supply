import puppeteer from 'puppeteer';

async function inspectRailwayNewsResponse() {
    console.log('Inspecting Railway Production /news Page HTML content...');
    const railwayUrl = 'https://global-supply-production.up.railway.app';

    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080 });

    // Authenticate
    await page.goto(`${railwayUrl}/login`, { waitUntil: 'networkidle2' });
    await page.type('#email', 'admin@gscrip.com');
    await page.type('#password', 'password');
    await Promise.all([
        page.click('button[type="submit"]'),
        page.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {})
    ]);

    // Go to /news
    const response = await page.goto(`${railwayUrl}/news`, { waitUntil: 'networkidle2' });
    console.log(`HTTP Status: ${response.status()}`);

    const html = await page.content();
    console.log(`HTML Snippet of /news page:\n${html.slice(0, 1500)}`);

    const hasCards = html.includes('card-premium');
    console.log(`Contains card-premium: ${hasCards}`);
    console.log(`Contains 'No news is currently available': ${html.includes('No news is currently available')}`);

    await browser.close();
}

inspectRailwayNewsResponse().catch(err => {
    console.error('Error:', err.message);
    process.exit(1);
});
