const puppeteer = require('puppeteer');

(async () => {
    // Check if the required command-line argument is provided
    if (process.argv.length !== 3) {
        console.error('Usage: node script.js <id>');
        return;
    }

    const id = process.argv[2];
    const website_url = `http://billing.asus/bills/view?id=${id}`;

    const browser = await puppeteer.launch();
    const page = await browser.newPage();
    await page.setViewport({ width: 1280, height: 1024 });

    // Open URL in the current page
    await page.goto(website_url, { waitUntil: 'networkidle0' });
    await page.pdf({
        path: `screenshot_${id}.pdf`,
        format: 'A4',
        printBackground: true
    });

    await browser.close();
})();
