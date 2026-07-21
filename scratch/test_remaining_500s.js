import puppeteer from 'puppeteer';

async function testRemaining500s() {
    console.log('Inspecting remaining 500 error causes...');
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

    const targetPaths = ['/admin/dashboard', '/countries', '/risk', '/admin/sync'];

    for (const p of targetPaths) {
        console.log(`\n--- Testing ${p} ---`);
        const resp = await page.goto(`${railwayUrl}${p}`, { waitUntil: 'networkidle2' });
        console.log(`HTTP Status of ${p}: ${resp.status()}`);
        const text = await resp.text();
        try {
            const j = JSON.parse(text);
            console.log(`ERROR: ${j.error}`);
            console.log(`FILE: ${j.file}:${j.line}`);
        } catch(e) {
            console.log(text.slice(0, 400));
        }
    }

    await browser.close();
}

testRemaining500s().catch(err => {
    console.error('Error:', err.message);
    process.exit(1);
});
