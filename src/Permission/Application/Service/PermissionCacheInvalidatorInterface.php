<?php

declare(strict_types=1);

namespace GardenManager\Permission\Application\Service;

use Symfony\Component\Uid\Ulid;

interface PermissionCacheInvalidatorInterface
{
    public function invalidateForTenant(Ulid $tenantId): void;

    public function invalidateAll(): void;
}
