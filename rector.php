<?php

/**
 * Rector - Instant Upgrades and Automated Refactoring
 * Rector instantly upgrades and refactors the PHP code of your application.
 * see: https://github.com/rectorphp/rector
 *
 * call like this:  vendor/bin/rector process --dry-run
 */

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withoutParallel()
    ->withPHPStanConfigs([__DIR__ . '/phpstan.neon'])
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/templates',
        __DIR__ . '/tests',
    ])
    ->withImportNames()
    ->withComposerBased(twig: true)
    ->withAttributesSets(
        symfony: true,
        doctrine: true,
        phpunit: true
    )
    ->withSets([
        LevelSetList::UP_TO_PHP_84,
        DoctrineSetList::DOCTRINE_CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::TYPE_DECLARATION,
    ])
    ->withSkip([
        /**
         * @see: https://github.com/rectorphp/rector/blob/main/docs/rector_rules_overview.md
         */
        // for better reading
        ClosureToArrowFunctionRector::class,
        ClassPropertyAssignToConstructorPromotionRector::class,
    ]);
