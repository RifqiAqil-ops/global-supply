import puppeteer from 'puppeteer';

async function testDiagRaw() {
    console.log('Fetching raw /system-audit-diagnostic text...');
    const railwayUrl = 'https://global-supply-production.up.railway.app';

    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();
    const resp = await page.goto(`${railwayUrl}/system-audit-diagnostic`, { waitUntil: 'networkidle2' });
    console.log(`HTTP Status: ${resp.status()}`);
    console.log(`Final Response URL: ${resp.url()}`);
    const text = await resp.text();
    console.log(`Response Snippet:\n${text.slice(0, 1000)}`);

    await browser.close();
}

testDiagRaw().catch(err => {
    console.error('Error:', err.message);
    process.exit(1);
});
