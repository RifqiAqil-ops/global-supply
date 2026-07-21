import puppeteer from 'puppeteer';

async function triggerRailwayNewsSync() {
    console.log('Connecting to Railway Admin Panel to trigger GNews News Sync...');
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

    // Go to Admin Sync Manager
    console.log('Navigating to /admin/sync...');
    await page.goto(`${railwayUrl}/admin/sync`, { waitUntil: 'networkidle2' });

    // Find and click "Sync GNews Articles" or "Sync News"
    const syncButtons = await page.$$('button');
    let clicked = false;
    for (const btn of syncButtons) {
        const text = await page.evaluate(el => el.innerText, btn);
        if (text.toLowerCase().includes('news')) {
            console.log(`Clicking button: "${text.trim()}"...`);
            await btn.click();
            clicked = true;
            await new Promise(r => setTimeout(r, 8000));
            break;
        }
    }

    await browser.close();
    console.log('Railway News Sync Trigger Completed!');
}

triggerRailwayNewsSync().catch(err => {
    console.error('Error:', err.message);
    process.exit(1);
});
