<?php

/**
 * Rector - Instant Upgrades and Automated Refactoring
 * Rector instantly upgrades and refactors the PHP code of your application.
 * see: https://github.com/rectorphp/rector
 *
 * call like this:  php vendor/bin/rector process  --clear-cache --dry-run
 */

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Php80\Rector\FunctionLike\MixedTypeRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Symfony\Set\SymfonyLevelSetList;
use Rector\Symfony\Set\SymfonySetList;


return static function (RectorConfig $rectorConfig): void {

    $rectorConfig->phpVersion(PhpVersion::PHP_82);

    $rectorConfig->phpstanConfig(__DIR__ . '/phpstan.neon');

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81,

        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonyLevelSetList::UP_TO_SYMFONY_62,
    ]);

    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/templates',
#        __DIR__ . '/tests',
    ]);

    $rectorConfig->skip([

        /**
         * @see https://github.com/rectorphp/rector/blob/main/docs/rector_rules_overview.md
         */

        // Preserve legibility
        ClosureToArrowFunctionRector::class,
        AddLiteralSeparatorToNumberRector::class,

        // removes PhpDoc parameter definitions
        MixedTypeRector::class,
        RenameClassRector::class,
    ]);

    $rectorConfig->parallel(300);
};
