/*******************************************************
 * Lego Bauanleitungs-Seite herunterladen und PDF-URLs extrahieren
 *******************************************************/
const puppeteer = require('puppeteer');
const argv = require('minimist')(process.argv.slice(2));
const {JSDOM} = require('jsdom');

if (argv['set'] === undefined) {
    console.log('{"error": "Fehlender Parameter \'--set <set-nummer>\'"}');
    return (1);
}
let setNumber = argv['set'];


//const baseUrl = 'https://www.lego.com/de-de/service/buildinginstructions/';
const baseUrl = 'https://www.lego.com/de-de/service/building-instructions/';

(async () => {

    console.log("{");
    console.log('  "set-number": ' + setNumber + ',');

    // Starte Chrome im Headless-Modus
    const browser = await puppeteer.launch({
        executablePath: '/usr/bin/google-chrome-stable',
        args: ['--no-sandbox'],
    });

    const page = await browser.newPage();

    // Setze einen realistischen User-Agent, um Bot-Erkennung zu vermeiden
    await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36');

    // Entferne das "navigator.webdriver" Flag, um weniger wie ein Bot zu wirken
    await page.evaluateOnNewDocument(() => {
        Object.defineProperty(navigator, 'webdriver', {
            get: () => false,
        });
    });

    const url = baseUrl + setNumber;  // Leerzeichen entfernen
    console.log('  "building-instructions-page": "' + url + '",');

    // Seite mit allen Assets laden und JavaScript ausführen, etc.
    await page.goto(url, {waitUntil: 'networkidle2'});
    const htmlContent = await page.content();

    const dom = new JSDOM(htmlContent);
    const document = dom.window.document;

    // H1-Elemente suchen (enthält normalerweise den Set-Namen)
    const h1Elements = Array.from(document.querySelectorAll('h1')).map(h1 => h1.textContent.trim());
    if (h1Elements.length > 1) {
        console.error('  "error": "Mehrere H1-Elemente gefunden"');
        console.log('}');
        return (1);
    } else if (h1Elements.length === 0) {
        console.error('  "error": "Kein H1-Element gefunden"');
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

    // PDF-Dokument-Links suchen
    console.log('  "documents": [');
    let i = 0;
    // Sucht nach allen Links, die auf ein PDF in den product-assets verweisen
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
