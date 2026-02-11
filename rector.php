<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php82\Rector\Class_\ReadOnlyClassRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSkip([
        __DIR__ . '/var',
        __DIR__ . '/vendor',
        ReadOnlyClassRector::class => [
            __DIR__ . '/src/Auth/Infrastructure/Security/EmailVerificationService.php'
        ],
        ClassPropertyAssignToConstructorPromotionRector::class => [
            __DIR__ . '/src/Shared/Domain/ValueObject/Address.php'
        ]
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
