import puppeteer from 'puppeteer';

async function testCountriesRisk500() {
    console.log('Inspecting /countries and /risk 500 error causes...');
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

    // Test 1: /countries
    console.log('\n--- Testing /countries ---');
    const resp1 = await page.goto(`${railwayUrl}/countries`, { waitUntil: 'networkidle2' });
    console.log(`HTTP Status of /countries: ${resp1.status()}`);
    const text1 = await resp1.text();
    try {
        const j1 = JSON.parse(text1);
        console.log(`ERROR: ${j1.error}`);
        console.log(`FILE: ${j1.file}:${j1.line}`);
    } catch(e) {
        console.log(text1.slice(0, 500));
    }

    // Test 2: /risk
    console.log('\n--- Testing /risk ---');
    const resp2 = await page.goto(`${railwayUrl}/risk`, { waitUntil: 'networkidle2' });
    console.log(`HTTP Status of /risk: ${resp2.status()}`);
    const text2 = await resp2.text();
    try {
        const j2 = JSON.parse(text2);
        console.log(`ERROR: ${j2.error}`);
        console.log(`FILE: ${j2.file}:${j2.line}`);
    } catch(e) {
        console.log(text2.slice(0, 500));
    }

    await browser.close();
}

testCountriesRisk500().catch(err => {
    console.error('Error:', err.message);
    process.exit(1);
});
