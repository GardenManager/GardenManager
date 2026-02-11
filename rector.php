<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSkip([
        __DIR__ . '/var',
        __DIR__ . '/vendor',
    ])
    ->withPhpSets(php84: true)
    ->withPreparedSets(
        deadCode: true,
        typeDeclarations: true,
    )
    ->withSymfonyContainerXml(
        __DIR__ . '/var/cache/dev/GardenManager_KernelDevDebugContainer.xml',
    )
;
