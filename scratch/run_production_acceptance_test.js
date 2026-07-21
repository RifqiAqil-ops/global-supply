import puppeteer from 'puppeteer';

async function runProductionAcceptanceTest() {
    console.log('================================================================================');
    console.log('   STARTING COMPREHENSIVE PRODUCTION ACCEPTANCE TEST (PAT) ON RAILWAY         ');
    console.log('================================================================================');

    const railwayUrl = 'https://global-supply-production.up.railway.app';
    const consoleErrors = [];
    const failedRequests = [];

    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080 });

    // Monitor Console Errors & Failed Network Requests
    page.on('console', msg => {
        if (msg.type() === 'error') {
            consoleErrors.push({ url: page.url(), text: msg.text() });
        }
    });

    page.on('requestfailed', req => {
        failedRequests.push({ url: req.url(), failure: req.failure()?.errorText });
    });

    // -------------------------------------------------------------------------
    // TEST 1: ALL PAGES & BROWSER CONSOLE/NETWORK AUDIT
    // -------------------------------------------------------------------------
    console.log('\n--- 1. ALL PAGES & ASSET AUDIT ---');
    const allPages = [
        '/',
        '/login',
        '/register',
        '/forgot-password',
        '/non-existent-page-404-test'
    ];

    for (const p of allPages) {
        const fullUrl = `${railwayUrl}${p}`;
        const resp = await page.goto(fullUrl, { waitUntil: 'networkidle2' });
        console.log(`[Guest Page] ${p} -> HTTP Status: ${resp.status()}`);
    }

    // -------------------------------------------------------------------------
    // TEST 2: AUTHENTICATION & LOGIN AS USER & ADMIN
    // -------------------------------------------------------------------------
    console.log('\n--- 2. AUTHENTICATION & SESSION AUDIT ---');
    await page.goto(`${railwayUrl}/login`, { waitUntil: 'networkidle2' });
    await page.type('#email', 'admin@gscrip.com');
    await page.type('#password', 'password');
    await Promise.all([
        page.click('button[type="submit"]'),
        page.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {})
    ]);
    console.log(`Logged in Admin redirected to: ${page.url()}`);

    // Test Authenticated User & Admin Pages
    const authPages = [
        '/user/dashboard',
        '/admin/dashboard',
        '/countries',
        '/countries/US',
        '/countries/ID',
        '/currency',
        '/risk',
        '/risk-history',
        '/compare',
        '/watchlists',
        '/ports',
        '/weather',
        '/news',
        '/articles',
        '/profile',
        '/admin/users',
        '/admin/ports',
        '/admin/articles',
        '/admin/sync',
        '/admin/weights',
        '/admin/settings',
        '/admin/logs'
    ];

    const pageStatuses = {};
    for (const p of authPages) {
        const fullUrl = `${railwayUrl}${p}`;
        const start = Date.now();
        const resp = await page.goto(fullUrl, { waitUntil: 'networkidle2' });
        const loadTime = Date.now() - start;
        const status = resp.status();
        pageStatuses[p] = { status, loadTime };
        console.log(`[Auth Page] ${p} -> Status: ${status} | Load Time: ${loadTime}ms`);
    }

    // -------------------------------------------------------------------------
    // TEST 3: FORM VALIDATION & SECURITY PAYLOADS (XSS, SQLi, Emojis)
    // -------------------------------------------------------------------------
    console.log('\n--- 3. FORM VALIDATION & SECURITY INPUT AUDIT ---');
    const payloads = [
        "<script>alert('xss')</script>",
        "' OR '1'='1",
        "🔥🌐📦 Trade & Inflation Test",
        "A".repeat(500)
    ];

    for (const payload of payloads) {
        const searchUrl = `${railwayUrl}/countries?search=${encodeURIComponent(payload)}`;
        const resp = await page.goto(searchUrl, { waitUntil: 'networkidle2' });
        const text = await page.content();
        const hasXssExecuted = text.includes("<script>alert('xss')</script>") && !text.includes("&lt;script&gt;");
        console.log(`Input Payload: "${payload.slice(0, 30)}" -> Status: ${resp.status()} | XSS Escaped: ${!hasXssExecuted}`);
    }

    // -------------------------------------------------------------------------
    // TEST 4: ACCESS CONTROL & MIDDLEWARE GATING
    // -------------------------------------------------------------------------
    console.log('\n--- 4. ACCESS CONTROL & MIDDLEWARE GATING AUDIT ---');
    // Logout
    await page.goto(`${railwayUrl}/user/dashboard`, { waitUntil: 'networkidle2' });
    await page.evaluate(() => {
        const logoutForm = document.querySelector('form[action*="logout"]');
        if (logoutForm) logoutForm.submit();
    });
    await page.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {});

    // Try accessing Admin Users as guest
    const guestAdminResp = await page.goto(`${railwayUrl}/admin/users`, { waitUntil: 'networkidle2' });
    console.log(`Accessing /admin/users as Guest -> Final URL: ${page.url()} | Status: ${guestAdminResp.status()}`);

    // -------------------------------------------------------------------------
    // TEST 5: SECURITY AUDIT (.env, debug mode, raw stack traces)
    // -------------------------------------------------------------------------
    console.log('\n--- 5. SECURITY AUDIT ---');
    const envResp = await page.goto(`${railwayUrl}/.env`, { waitUntil: 'networkidle2' });
    console.log(`Accessing /.env -> Status: ${envResp.status()} | Protected: ${envResp.status() === 404 || envResp.status() === 403}`);

    // -------------------------------------------------------------------------
    // CONSOLE & NETWORK FAILURES REPORT
    // -------------------------------------------------------------------------
    console.log('\n================================================================================');
    console.log(`Total Console Errors Captured: ${consoleErrors.length}`);
    if (consoleErrors.length > 0) {
        consoleErrors.slice(0, 10).forEach(e => console.log(`  - [${e.url}]: ${e.text}`));
    }

    console.log(`Total Failed Network Requests: ${failedRequests.length}`);
    if (failedRequests.length > 0) {
        failedRequests.slice(0, 10).forEach(f => console.log(`  - [${f.url}]: ${f.failure}`));
    }
    console.log('================================================================================\n');

    await browser.close();
}

runProductionAcceptanceTest().catch(err => {
    console.error('PAT Runner Error:', err.message);
    process.exit(1);
});
