<?php

declare(strict_types=1);

namespace GardenManager\Permission\Infrastructure\Cache;

use GardenManager\Permission\Application\Service\PermissionCacheInvalidatorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Uid\Ulid;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final readonly class PermissionCacheInvalidator implements PermissionCacheInvalidatorInterface
{
    public function __construct(
        #[Autowire(service: 'permission.cache')]
        private TagAwareCacheInterface $cache,
        private CachedPermissionResolver $cachedResolver,
    ) {
    }

    public function invalidateForTenant(Ulid $tenantId): void
    {
        $this->cache->invalidateTags(['perm_tenant_' . $tenantId->toString()]);
        $this->cachedResolver->clearL1Cache();
    }

    public function invalidateAll(): void
    {
        $this->cache->invalidateTags(['perm_all']);
        $this->cachedResolver->clearL1Cache();
    }
}
