import puppeteer from 'puppeteer';
import fs from 'fs';
import path from 'path';

async function verifySourceButtons() {
    console.log('Starting Source Button & URL Verification...');

    const baseUrl = 'http://127.0.0.1:8000';
    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1920,1080']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080 });

    // Authenticate
    await page.goto(`${baseUrl}/login`, { waitUntil: 'networkidle2' });
    const emailInput = await page.$('#email');
    if (emailInput) {
        await page.type('#email', 'admin@gscrip.com');
        await page.type('#password', 'password');
        await Promise.all([
            page.click('button[type="submit"]'),
            page.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {})
        ]);
    }

    // 1. Verify News Page (/news)
    console.log('Navigating to /news page...');
    await page.goto(`${baseUrl}/news`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1000));

    const sourceLinks = await page.$$eval('a[target="_blank"]', links => links.map(l => ({
        href: l.href,
        text: l.innerText.trim(),
        target: l.target,
        rel: l.rel
    })));

    console.log(`Found ${sourceLinks.length} target="_blank" links on /news page.`);
    let exampleDotComCount = 0;

    sourceLinks.forEach((link, idx) => {
        console.log(`[Link ${idx + 1}] Text: "${link.text}" | HREF: ${link.href} | Target: ${link.target} | Rel: ${link.rel}`);
        if (link.href.includes('example.com')) {
            exampleDotComCount++;
        }
    });

    // 2. Verify Country Detail Page (/countries/ID)
    console.log('Navigating to /countries/ID page...');
    await page.goto(`${baseUrl}/countries/ID`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1000));

    const countryDetailLinks = await page.$$eval('.card a[target="_blank"]', links => links.map(l => ({
        href: l.href,
        text: l.innerText.trim()
    })));

    console.log(`Found ${countryDetailLinks.length} target="_blank" links on /countries/ID page.`);
    countryDetailLinks.forEach((link, idx) => {
        console.log(`[Country Link ${idx + 1}] Text: "${link.text}" | HREF: ${link.href}`);
        if (link.href.includes('example.com')) {
            exampleDotComCount++;
        }
    });

    await browser.close();

    console.log('=============================================');
    console.log(`VERIFICATION RESULT:`);
    console.log(`Total "example.com" URLs found across app: ${exampleDotComCount}`);
    if (exampleDotComCount === 0) {
        console.log('🎉 PASSED! ZERO example.com URLs exist in the application!');
    } else {
        console.error('❌ FAILED! example.com URLs detected!');
        process.exit(1);
    }
}

verifySourceButtons().catch(err => {
    console.error('Fatal error verifying source buttons:', err);
    process.exit(1);
});
