import puppeteer from 'puppeteer';

async function triggerRailwaySync() {
    console.log('Connecting to Railway Admin Panel to trigger GNews Live API Sync...');
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

    // Find and click Sync All News or Sync All
    const syncButtons = await page.$$('button');
    console.log(`Found ${syncButtons.length} buttons on Sync Manager page.`);

    let clicked = false;
    for (const btn of syncButtons) {
        const text = await page.evaluate(el => el.innerText, btn);
        if (text.includes('News') || text.includes('Sync All')) {
            console.log(`Clicking button: "${text.trim()}"...`);
            await btn.click();
            clicked = true;
            await new Promise(r => setTimeout(r, 5000));
            break;
        }
    }

    if (!clicked) {
        console.log('Trying fallback form submit for news sync...');
        await page.evaluate(() => {
            const forms = Array.from(document.querySelectorAll('form'));
            const newsForm = forms.find(f => f.action.includes('sync') || f.innerHTML.includes('news'));
            if (newsForm) newsForm.submit();
        });
        await new Promise(r => setTimeout(r, 5000));
    }

    await browser.close();
    console.log('Railway Admin Sync Trigger Completed!');
}

triggerRailwaySync().catch(err => {
    console.error('Admin sync trigger error:', err.message);
    process.exit(1);
});
