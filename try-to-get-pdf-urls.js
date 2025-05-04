/*******************************************************
 * Download a lego manual page and extract PDF urls
 *******************************************************/
const puppeteer = require('puppeteer');
const argv = require('minimist')(process.argv.slice(2));
const {JSDOM} = require('jsdom');

if (argv['set'] === undefined) {
    console.log('{"error": "missing param \'--set <set-number>\'"}');
    return (1);
}
let setNumber = argv['set'];


const baseUrl = 'https://www.lego.com/de-de/service/buildinginstructions/';

(async () => {

    console.log("{");
    console.log('  "set-number": ' + setNumber + ',');

    // start Chrome in headless mode
    const browser = await puppeteer.launch({
        executablePath: '/usr/bin/google-chrome-stable',
        args: ['--no-sandbox'],
    });

    const page = await browser.newPage();
    const url = baseUrl + setNumber;  // Leerzeichen entfernen
    console.log('  "building-instructions-page": "' + url + '",');

    // load page with all assets and run javascript, etc.
    await page.goto(url, {waitUntil: 'networkidle2'});
    const htmlContent = await page.content();

    const dom = new JSDOM(htmlContent);
    const document = dom.window.document;

    // H1-Elemente suchen
    const h1Elements = Array.from(document.querySelectorAll('h1')).map(h1 => h1.textContent.trim());
    if (h1Elements.length > 1) {
        console.error('  "error": "multiple h1 elements found"');
        console.log('}');
        return (1);
    } else if (h1Elements.length === 0) {
        console.error('  "error": "no h1 element found"');
        console.log('}');
        return (1);
    } else {
        let title = h1Elements[0];
        let parts = title.split(',');
        if (parts.length === 3) {
            title = parts.at(1).trim();
        } else if (parts.length > 1) {
            parts.shift();
            title = parts.join(',').trim();
        }
        console.log('  "title": "' + title + '",');
    }

    // Div-Elemente suchen
    console.log('  "documents": [');
    let i = 0;
    //document.querySelectorAll('div[class="c-bi-booklet"] a')
    document.querySelectorAll('a')
        .forEach(function (element) {
            if (element.href.slice(-4) === '.pdf' && element.href.indexOf('product-assets') > -1) {
                console.log('    ' + (i++ > 0 ? ',' : '') + '"' + element.href + '"');
            }
        });
    console.log('  ]');
    console.log('}');

    await page.close();
    await browser.close();
})();
