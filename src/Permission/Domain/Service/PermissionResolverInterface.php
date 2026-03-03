<?php

declare(strict_types=1);

namespace GardenManager\Permission\Domain\Service;

use Symfony\Component\Uid\Ulid;

interface PermissionResolverInterface
{
    public function hasPermission(Ulid $userId, Ulid $tenantId, string $permission): bool;

    /** @return array<string, bool> */
    public function resolvePermissions(Ulid $userId, Ulid $tenantId): array;
}
