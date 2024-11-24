/***********************************************
 *
 * Download a list of pages
 * given by 'urls.txt'
 *
 ***********************************************/
const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

console.log(`Download pages for set numbers of "set-numbers.txt"`);

const baseUrl = 'https://www.lego.com/de-de/service/buildinginstructions/';

(async () => {
    // load URLs from 'urls.txt'
    const filePath = path.join(__dirname, 'set-numbers.txt');
    const urls = fs.readFileSync(filePath, 'utf-8').split('\n').filter(Boolean);

    // start Chrome in headless mode
    const browser = await puppeteer.launch({
        executablePath: '/usr/bin/google-chrome-stable',
        args: ['--no-sandbox'],
    });

    for (let i = 0; i < urls.length; i++) {
        const page = await browser.newPage();
        const url = baseUrl + urls[i].trim();  // Leerzeichen entfernen
        console.log(`Loading page ${i + 1} of ${urls.length}: ${url}`);

        // load page with all assets and run javascript, etc.
        await page.goto(url, { waitUntil: 'networkidle2' });
        const htmlContent = await page.content();

		// extract number from URL
        const urlParts = url.split('/');
        const fileNumber = urlParts[urlParts.length - 1];

        // save HTML content to file
        const filename = `${fileNumber}.html`;
		fs.writeFileSync(path.join(__dirname, 'results', filename), htmlContent);
        console.log(`saved to ${filename}`);

        await page.close();
    }

    await browser.close();
})();
