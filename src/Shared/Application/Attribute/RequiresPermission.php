<?php

declare(strict_types=1);

namespace GardenManager\Shared\Application\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class RequiresPermission
{
    public function __construct(
        public string $permission,
    ) {
    }
}
