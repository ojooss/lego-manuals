# Plan: Einführung automatisierter Tests

Stand: 2026-08-26

## Ist-Zustand

Das Projekt ist eine Symfony-7.4/PHP-8.4-Anwendung (SQLite, EasyAdmin, Webpack Encore) ohne jegliche automatisierte Tests. Interessant: Die Symfony-Flex-Rezepte für Tests sind **bereits vorhanden** (`when@test`-Blöcke in `config/packages/{framework,doctrine,security,twig,validator,monolog,web_profiler}.yaml`), aber:

- `phpunit/phpunit` bzw. `symfony/test-pack` fehlen in `composer.json` (require-dev)
- Es gibt kein `tests/`-Verzeichnis, keine `phpunit.xml.dist`, kein `.env.test`
- `.junie/guidelines.md` beschreibt bereits PHPUnit- und Cypress-Konventionen (Gruppen-Feature, `npm run cypress:run`, Kennzeichnung generierter Tests, wenig Mocking) — das sind offenbar Vorgaben aus einem früheren Anlauf, der nie umgesetzt wurde. Diese Konventionen sollten eingehalten werden.
- Cypress taucht in `package.json` nirgends auf.

Fachlich überschaubarer Kern: 2 Entities (`Set`, `Manual`) mit etwas Logik (`getPdfFileName`, `getCoverFileName`), 3 Services (`DownloadService`, `PdfService`, `ManualService`), 2 Repositories, 3 Controller (`Index`, `Import`, `Status`), 3 Konsolenkommandos, EasyAdmin-CRUD, und ein kleines Vanilla-JS-Frontend (Suche/Filter, dynamische Formularzeilen, AJAX-Autoload gegen `try-to-get-pdf-urls.js`).

## Empfehlung: PHPUnit (Backend) + Cypress (E2E), gestuft eingeführt

PHPUnit deckt die fachliche Logik ab (Entities, Services, Repositories, Controller), Cypress die tatsächlich nutzerkritischen Pfade (Set anlegen, Manuals durchsuchen/öffnen). Component-Level-JS-Tests (Vitest/Jest) lohnen sich bei diesem schlanken `app.js` (169 Zeilen, reine DOM-Manipulation) nicht — das deckt Cypress mit ab.

## Phase 0 – Test-Infrastruktur (Voraussetzung für alles Weitere)

- `composer require --dev symfony/test-pack` (zieht `phpunit/phpunit`, `symfony/phpunit-bridge`, `symfony/browser-kit`/`css-selector` sind schon da)
- `tests/` anlegen, `phpunit.xml.dist` per `symfony/test-pack`-Recipe generieren lassen
- **Eigene Test-Datenbank**: `.env.test` mit `DATABASE_URL="sqlite:///%kernel.project_dir%/var/test.db"` — bewusst *nicht* der `dbname_suffix`-Mechanismus aus `doctrine.yaml`, da der bei SQLite-Dateipfaden nicht sauber funktioniert, und *nicht* `public/data/sqlite.db`, um echte Produktionsdaten nie anzurühren
- `DATA_DIR`/`DATA_PATH` in `.env.test` auf ein Testverzeichnis umbiegen (z. B. `var/test-data`), damit `ManualService`/`PdfService`-Tests keine echten PDFs unter `public/data` erzeugen
- Composer-Skript ergänzen, z. B. `"phpunit": "vendor/bin/phpunit --testdox"`, passend zu den bestehenden Skripten `phpstan`/`rector`
- `.github/workflows/tests.yml`: neuer CI-Workflow, der bei jedem Push/PR `composer install`, `bin/console doctrine:migrations:migrate --env=test`, `vendor/bin/phpunit` sowie später Cypress ausführt (aktuell existiert nur `docker-image.yml` fürs Bauen des Images und `composer-update.yml`)

## Phase 1 – PHPUnit: Unit-Tests (kein Doctrine/Kernel nötig, schnell)

Kandidaten mit reiner Logik, ideal zum Einstieg:

- `Manual::getPdfFileName()` / `getCoverFileName()` — Namensbildung inkl. der `LogicException`-Fälle ("Set has no number", "Save entity before calling …")
- `DownloadService::getSaveFilename()` — reine String-Sanitisierung, leicht zu testen
- `Set::__toString()` / `Manual::__toString()`

## Phase 2 – PHPUnit: Integration/Functional-Tests

- **Repository-Tests** (`KernelTestCase` + Test-SQLite-DB): `SetRepository::doesAlreadyExist()` — Kernstück der Duplikatsprüfung beim Import, aktuell ungetestet und mit prüfenswerter Logik (leerer `$name` wird nicht geprüft)
- **Controller-Tests** (`WebTestCase`):
  - `IndexController` — Sets werden alphabetisch gerendert, `fileCount` stimmt
  - `ImportController::index()` — Formular gültig/ungültig, Duplikat-Fehlermeldung, dass `Manual`s mit leerer URL beim Speichern entfernt werden
  - `StatusController` — dass `/status` bei intakter DB 200 liefert (ohne den `symfony security:check`-Subprozess wirklich auszuführen, siehe Phase 3)
- Für Fixtures bietet sich `doctrine/doctrine-fixtures-bundle` (schon vorhanden) mit einer schlanken Test-Fixture-Klasse an, statt der Produktions-`AppFixtures`

## Phase 3 – Kontrollierte Mocking-Grenzen (Guideline: „so wenig Mocking wie möglich")

Trotz der Vorgabe minimalen Mockings gibt es hier drei Stellen, die **zwingend** isoliert werden müssen, sonst sind Tests nicht deterministisch/CI-tauglich:

- `DownloadService::downloadManualFile()` — nutzt `file_get_contents($url)` gegen echte externe URLs → in Tests per Interface/Fake ersetzen, nicht real gegen `lego.com` aufrufen
- `PdfService::extractCover()` — braucht Imagick/Ghostscript; für Service-Tests eine kleine Test-Fixture-PDF im Repo (`tests/Fixtures/`) verwenden statt Mock, das ist mit „wenig Mocking" vereinbar, weil Imagick real installiert ist (Dockerfile)
- `ImportController::autoload()` — startet einen Node/Puppeteer-Subprozess (`try-to-get-pdf-urls.js`) gegen lego.com; hier ist ein Mock/Fake des `Process`-Aufrufs sinnvoll, ein echter Aufruf gehört nicht in die CI

## Phase 4 – Cypress: E2E-Tests

- `npm install --save-dev cypress`, `cypress.config.js`, Skript `"cypress:run": "cypress run"` / `"cypress:open": "cypress open"` in `package.json` (passend zur bereits in `.junie/guidelines.md` referenzierten Convention)
- Kern-Flows:
  1. **Übersicht & Suche**: `/` lädt, Sets sind sichtbar, Live-Filter (`input_filter`) blendet Boxen korrekt ein/aus
  2. **Set anlegen**: `/import` — Formular ausfüllen, weitere URL-Zeile per „weitere URL hinzufügen" ergänzen, Speichern führt zu Redirect auf `/`
  3. **Duplikat-Fehler**: bereits existierende Set-Nummer im Formular → Fehlermeldung erscheint
  4. Der `/import/autoload/{setNumber}`-AJAX-Call (Puppeteer-Scraping) sollte in Cypress per `cy.intercept()` gestubbt werden — ein echter Scraping-Call in E2E-Tests ist zu instabil/langsam
- Testdaten: eigene SQLite-Fixture-DB, die vor dem Cypress-Lauf geladen wird (z. B. via `cy.task()` + `bin/console doctrine:fixtures:load --env=test`)

## Phase 5 – CI-Integration & Wartung

- Ein Workflow (`tests.yml`) mit zwei Jobs: `phpunit` (PHP 8.4 + Imagick/Ghostscript, wie im `Dockerfile`) und `cypress` (Node + Chrome, ebenfalls schon im `Dockerfile` vorhanden für Puppeteer — lässt sich für Cypress mitnutzen)
- Coverage optional über `XDEBUG_MODE=coverage`, wie in `.junie/guidelines.md` bereits vorgesehen
- `phpstan.neon` / `rector.php` sind schon da — Tests laufen idealerweise im selben CI-Gate mit

## Vorschlag für den Einstieg

Start mit **Phase 0 + einem ersten Slice aus Phase 1/2** (Test-Infra aufsetzen, `Manual`/`DownloadService`-Unit-Tests, ein `IndexController`-Test), damit früh ein grüner CI-Lauf steht, auf dem alles Weitere aufbaut.
