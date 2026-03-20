<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/bootstrap/app.php',
        __DIR__.'/database',
        __DIR__.'/public',
    ])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        earlyReturn: true,
    )
    ->withSkip([
    EncapsedStringsToSprintfRector::class
    ])
    ->withPhpSets();
