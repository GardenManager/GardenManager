<?php

declare(strict_types=1);

namespace GardenManager\Permission\Infrastructure\Cache;

use Symfony\Component\Uid\Ulid;

final readonly class PermissionCacheKeyGenerator
{
    public function forUser(Ulid $userId, Ulid $tenantId): string
    {
        return 'perm_tenant_' . $tenantId->toString() . '-user_' . $userId->toString();
    }

    public function tenantTag(Ulid $tenantId): string
    {
        return 'perm_tenant_' . $tenantId->toString();
    }

    public function globalTag(): string
    {
        return 'perm_all';
    }
}
