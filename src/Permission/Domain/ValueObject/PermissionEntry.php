<?php

declare(strict_types=1);

namespace GardenManager\Permission\Domain\ValueObject;

final readonly class PermissionEntry
{
    public function __construct(
        public string $permission,
        public bool $granted,
    ) {
    }
}
