import puppeteer from 'puppeteer';

async function testRoute500Details() {
    console.log('Inspecting HTTP 500 cause on authenticated pages...');
    const railwayUrl = 'https://global-supply-production.up.railway.app';

    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080 });

    console.log('Navigating to /login...');
    await page.goto(`${railwayUrl}/login`, { waitUntil: 'networkidle2' });
    await page.type('#email', 'admin@gscrip.com');
    await page.type('#password', 'password');

    await Promise.all([
        page.click('button[type="submit"]'),
        page.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {})
    ]);

    console.log(`Current URL after login submit: ${page.url()}`);

    const resp = await page.goto(`${railwayUrl}/user/dashboard`, { waitUntil: 'networkidle2' });
    console.log(`HTTP Status of /user/dashboard: ${resp.status()}`);
    const text = await resp.text();
    console.log(`Response Snippet:\n${text.slice(0, 1500)}`);

    await browser.close();
}

testRoute500Details().catch(err => {
    console.error('Error:', err.message);
    process.exit(1);
});
