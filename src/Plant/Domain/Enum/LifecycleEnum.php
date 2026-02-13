<?php

declare(strict_types=1);

namespace GardenManager\Plant\Domain\Enum;

use GardenManager\Shared\Infrastructure\Validation\GetEnumValuesTrait;

enum LifecycleEnum: string
{
    use GetEnumValuesTrait;

    case EVERGREEN = 'evergreen';
    case BIENNIAL = 'biennial';
    case ANNUAL = 'annual';
    case PERENNIAL = 'perennial';
}
