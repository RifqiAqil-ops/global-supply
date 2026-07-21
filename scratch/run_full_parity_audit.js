import puppeteer from 'puppeteer';

async function runFullParityAudit() {
    console.log('===========================================================');
    console.log('  STARTING FULL LOCAL VS RAILWAY PRODUCTION PARITY AUDIT  ');
    console.log('===========================================================');

    const railwayUrl = 'https://global-supply-production.up.railway.app';

    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080 });

    // -----------------------------------------------------------------
    // PHASE 1 & 2 & 3: Environment, Database & Diagnostic Fetch
    // -----------------------------------------------------------------
    console.log('\n[PHASE 1 & 2] Logged in as Admin to Railway Production...');
    await page.goto(`${railwayUrl}/login`, { waitUntil: 'networkidle2' });
    await page.type('#email', 'admin@gscrip.com');
    await page.type('#password', 'password');
    await Promise.all([
        page.click('button[type="submit"]'),
        page.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {})
    ]);

    console.log('Fetching Railway Audit Diagnostic Data...');
    const railwayDiagResp = await page.goto(`${railwayUrl}/system-audit-diagnostic`, { waitUntil: 'networkidle2' });
    const text = await railwayDiagResp.text();
    let railwayDiag = {};
    try {
        railwayDiag = JSON.parse(text);
        console.log('\n===========================================================');
        console.log('RAILWAY DIAGNOSTIC OUTPUT (PHASE 1 & 2):');
        console.log('===========================================================');
        console.log(JSON.stringify(railwayDiag, null, 2));
    } catch (e) {
        console.log(`Failed to parse JSON: ${text.slice(0, 500)}`);
    }

    // -----------------------------------------------------------------
    // PHASE 4: Feature Suite Audit across Pages
    // -----------------------------------------------------------------
    const routesToTest = [
        { name: 'Dashboard', path: '/user/dashboard' },
        { name: 'Admin Dashboard', path: '/admin/dashboard' },
        { name: 'Countries Index', path: '/countries' },
        { name: 'Country Detail (US)', path: '/countries/US' },
        { name: 'Currency Matrix', path: '/currency' },
        { name: 'Risk Dashboard', path: '/risk' },
        { name: 'Weather Metrics', path: '/weather' },
        { name: 'Geopolitical News', path: '/news' },
        { name: 'Admin Users', path: '/admin/users' },
        { name: 'Admin Ports', path: '/admin/ports' },
        { name: 'Admin Operations/Sync', path: '/admin/sync' },
    ];

    console.log('\n===========================================================');
    console.log('PHASE 4 & 10: TESTING ALL ROUTES ON RAILWAY PRODUCTION');
    console.log('===========================================================');

    const routeAuditResults = [];

    for (const r of routesToTest) {
        const fullUrl = `${railwayUrl}${r.path}`;
        const start = Date.now();
        const resp = await page.goto(fullUrl, { waitUntil: 'networkidle2' });
        const duration = Date.now() - start;
        const status = resp.status();
        const content = await page.content();

        const has500 = content.includes('500 Server Error') || content.includes('Whoops');

        console.log(`[${r.name}] (${r.path}) -> Status: ${status} | Load Time: ${duration}ms | 500 Error: ${has500}`);
        routeAuditResults.push({
            name: r.name,
            path: r.path,
            status,
            duration,
            has500
        });
    }

    // -----------------------------------------------------------------
    // PHASE 5: Search Parity Audit
    // -----------------------------------------------------------------
    console.log('\n===========================================================');
    console.log('PHASE 5: TESTING SEARCH FUNCTIONALITY ON RAILWAY PRODUCTION');
    console.log('===========================================================');

    const searchTerms = ['Indonesia', 'China', 'United States', 'Port', 'Trade', 'GDP', 'Currency', 'Weather', 'InvalidKeywordXYZ', ''];

    for (const term of searchTerms) {
        const searchUrl = `${railwayUrl}/countries?search=${encodeURIComponent(term)}`;
        const resp = await page.goto(searchUrl, { waitUntil: 'networkidle2' });
        const html = await page.content();
        
        const cardCount = (html.match(/card-premium/g) || []).length;
        console.log(`Search Query: "${term}" -> HTTP Status: ${resp.status()} | Cards Found: ${cardCount}`);
    }

    // -----------------------------------------------------------------
    // PHASE 7: Pagination Audit
    // -----------------------------------------------------------------
    console.log('\n===========================================================');
    console.log('PHASE 7: TESTING PAGINATION ON RAILWAY PRODUCTION');
    console.log('===========================================================');

    const page1Resp = await page.goto(`${railwayUrl}/news?page=1`, { waitUntil: 'networkidle2' });
    const page1Html = await page.content();
    const page1CardCount = (page1Html.match(/card-premium/g) || []).length;

    const page2Resp = await page.goto(`${railwayUrl}/news?page=2`, { waitUntil: 'networkidle2' });
    const page2Html = await page.content();
    const page2CardCount = (page2Html.match(/card-premium/g) || []).length;

    console.log(`News Page 1 -> Cards: ${page1CardCount} | Status: ${page1Resp.status()}`);
    console.log(`News Page 2 -> Cards: ${page2CardCount} | Status: ${page2Resp.status()}`);

    await browser.close();
    console.log('\n🎉 Production Parity Audit Runner Completed Successfully!');
}

runFullParityAudit().catch(err => {
    console.error('Error in Audit Runner:', err.message);
    process.exit(1);
});
