import puppeteer from 'puppeteer';

async function testProfile500() {
    console.log('Inspecting /profile 500 error cause...');
    const railwayUrl = 'https://global-supply-production.up.railway.app';

    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080 });

    await page.goto(`${railwayUrl}/login`, { waitUntil: 'networkidle2' });
    await page.type('#email', 'admin@gscrip.com');
    await page.type('#password', 'password');
    await Promise.all([
        page.click('button[type="submit"]'),
        page.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {})
    ]);

    const resp = await page.goto(`${railwayUrl}/profile`, { waitUntil: 'networkidle2' });
    console.log(`HTTP Status: ${resp.status()}`);
    const text = await resp.text();
    console.log(`Response Snippet:\n${text.slice(0, 800)}`);

    await browser.close();
}

testProfile500().catch(err => {
    console.error('Error:', err.message);
    process.exit(1);
});
