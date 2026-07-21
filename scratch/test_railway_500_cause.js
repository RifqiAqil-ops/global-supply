import puppeteer from 'puppeteer';

async function testRailway500Cause() {
    console.log('Inspecting Railway Production HTTP 500 error cause...');
    const railwayUrl = 'https://global-supply-production.up.railway.app';

    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();
    const resp = await page.goto(`${railwayUrl}/system-audit-diagnostic`, { waitUntil: 'networkidle2' });
    console.log(`HTTP Status: ${resp.status()}`);
    const text = await resp.text();
    console.log(`Diagnostic Page Output:\n${text}`);

    await browser.close();
}

testRailway500Cause().catch(err => {
    console.error('Error:', err.message);
    process.exit(1);
});
