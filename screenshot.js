require('dotenv').config();
// Check the Node.js version
const [major, minor] = process.versions.node.split('.').map(Number);

// Check if the version is below 16
if (major < 16) {
    console.error('This script requires Node.js version 16 or above.');
    process.exit(1);
}

// Rest of the script goes here

const puppeteer = require('puppeteer');
const PDFDocument = require('pdfkit');
const fs = require('fs');

(async () => {
    // Check if the required command-line argument is provided
    if (process.argv.length !== 4) {
        console.error('Usage: node script.js <id> <proj-code>');
        return;
    }

    const id = process.argv[2];
    const proj = process.argv[3];
    const website_url = process.env.FREELANCER_WEB_URL + `/bills/view?id=${id}`;
    console.log(`Generating screenshot for ${website_url}...`);

    const browser = await puppeteer.launch();
    const page = await browser.newPage();
    await page.setViewport({ width: 1280, height: 1024 });

    // Open URL in the current page
    await page.goto(website_url, { waitUntil: 'networkidle0' });
    const jpgfile = process.env.BILLS_JPG_DIR + `/Invoice-${id}-${proj}.jpg`;
    const pdffile = process.env.BILLS_PDF_DIR + `/Invoice-${id}-${proj}.pdf`;

    //if pdffile exists, confirm to overwrite
    if (fs.existsSync(pdffile)) {
        const readline = require('readline').createInterface({
            input: process.stdin,
            output: process.stdout,
        });

        console.log(`File ${pdffile} exists. Overwrite? (y/n)`);

        readline.question(`?`, (answer) => {
            if (answer.toLowerCase() !== 'y') {
                console.log('Not overwriting ${pdffile}. Exiting...');
                readline.close();
                browser.close();
                return;
            }
            readline.close();
        });
    }

    await page.screenshot({
        path: jpgfile,
    });

    await browser.close();

    // Create a new PDF document with the same size as the screenshot
    const doc = new PDFDocument({ size: [1280, 1024] });
    //const page = pdfDoc.addPage([1280, 1024]);
    const stream = fs.createWriteStream(pdffile);

    // Pipe the PDF document to a write stream
    doc.pipe(stream);

    // Embed the screenshot image into the PDF without resizing
    doc.image(jpgfile, 0, 0, { fit: [1280, 1024] });

    // Finalize the PDF and close the write stream
    doc.end();
})();
