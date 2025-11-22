<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withoutParallel()
    ->withCache('./var/cache/dev/rector', FileCacheStorage::class)
    ->withPaths([
        __DIR__ . '/config',
        __DIR__ . '/public',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSets([
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        SetList::PHP_82,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,
    ])
    ->withImportNames()
    ;
