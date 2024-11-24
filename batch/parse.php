<?php

$commandTpl = "php bin/console app:maintenance:add --number=%s --name='%s' --url='%s'";

$resultsDir = __DIR__ . DIRECTORY_SEPARATOR .
    'html_loader' . DIRECTORY_SEPARATOR .
    'results' . DIRECTORY_SEPARATOR;
$files = glob($resultsDir . '*.html');

foreach ($files as $file) {

    $setNumber = basename($file, '.html');

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML(file_get_contents($file));
    libxml_clear_errors();
    $xpath = new DOMXPath($doc);
    $nodeList = $xpath->query('//h1');

    if ($nodeList->length <> 1) {
        print "\n[ERROR] 'h1' not found or not unique in $file \n";
#        exit(1);
        continue;
    }
    $setTitle = $nodeList->item(0)->nodeValue;
    // fix title
    $tmp = explode(',', $setTitle);
    if (count($tmp) < 2) {
        print "\n[ERROR] Unexpected set title: $setTitle \n";
#        exit(1);
        continue;
    }
    $setTitle = trim($tmp[1]);

    $nodeList = $xpath->query('//div[@class="c-bi-booklet"]//a[contains(@href, ".pdf")]/@href');
    for ($i = 0; $i < $nodeList->length; $i++) {
        $node = $nodeList->item($i);
        $command = sprintf($commandTpl, $setNumber, $setTitle, $node->nodeValue) . PHP_EOL;
        echo $command;
        #system($command);
    }
}
