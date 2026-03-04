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
        private PermissionCacheKeyGenerator $keyGenerator,
    ) {
    }

    public function invalidateForUser(Ulid $userId, Ulid $tenantId): void
    {
        $this->cache->delete($this->keyGenerator->forUser($userId, $tenantId));
        $this->cachedResolver->clearL1Cache();
    }

    public function invalidateForTenant(Ulid $tenantId): void
    {
        $this->cache->invalidateTags([$this->keyGenerator->tenantTag($tenantId)]);
        $this->cachedResolver->clearL1Cache();
    }

    public function invalidateAll(): void
    {
        $this->cache->invalidateTags([$this->keyGenerator->globalTag()]);
        $this->cachedResolver->clearL1Cache();
    }
}
