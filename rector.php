<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\CodingStyle\Rector\If_\NullableCompareToNullRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/bootstrap',
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/resources/views',
        __DIR__.'/routes',
    ])
    ->withSkipPath(__DIR__.'/bootstrap/cache')
    ->withPhpSets()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        typeDeclarationDocblocks: true,
        privatization: true,
        earlyReturn: true,
    )
    ->withSkip([
        EncapsedStringsToSprintfRector::class,
        NullableCompareToNullRector::class,
    ]);

// bool $naming = \false
// bool $instanceOf = \false
// bool $carbon = \false
// bool $rectorPreset = \false
// bool $phpunitCodeQuality = \false

// with rector-laravel
