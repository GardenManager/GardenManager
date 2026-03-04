<?php

declare(strict_types=1);

namespace GardenManager\Permission\Infrastructure\Cache;

use GardenManager\Permission\Domain\Service\PermissionMatcher;
use GardenManager\Permission\Domain\Service\PermissionResolverInterface;
use GardenManager\Permission\Infrastructure\Profiler\PermissionProfilerDataStore;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\Uid\Ulid;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[AsDecorator(decorates: PermissionResolverInterface::class)]
final class CachedPermissionResolver implements PermissionResolverInterface
{
    /** @var array<string, array<string, bool>> */
    private array $l1Cache = [];

    public function __construct(
        #[AutowireDecorated]
        private readonly PermissionResolverInterface $innerResolver,
        #[Autowire(service: 'permission.cache')]
        private readonly TagAwareCacheInterface $cache,
        private readonly PermissionMatcher $matcher,
        private readonly PermissionCacheKeyGenerator $keyGenerator,
        private readonly ?PermissionProfilerDataStore $dataStore = null,
    ) {
    }

    public function hasPermission(Ulid $userId, Ulid $tenantId, string $permission): bool
    {
        $resolved = $this->resolvePermissions($userId, $tenantId);

        return $this->matcher->evaluate($resolved, $permission);
    }

    /** @return array<string, bool> */
    public function resolvePermissions(Ulid $userId, Ulid $tenantId): array
    {
        $cacheKey = $this->keyGenerator->forUser($userId, $tenantId);

        if (isset($this->l1Cache[$cacheKey])) {
            $this->dataStore?->recordCacheStatus('l1');

            return $this->l1Cache[$cacheKey];
        }

        $wasMiss = false;

        /** @var array<string, bool> $resolved */
        $resolved = $this->cache->get($cacheKey, function (ItemInterface $item) use ($userId, $tenantId, &$wasMiss): array {
            $wasMiss = true;
            $item->tag([$this->keyGenerator->globalTag(), $this->keyGenerator->tenantTag($tenantId)]);

            return $this->innerResolver->resolvePermissions($userId, $tenantId);
        });

        $this->dataStore?->recordCacheStatus($wasMiss ? 'miss' : 'l2');

        $this->l1Cache[$cacheKey] = $resolved;

        return $resolved;
    }

    public function clearL1Cache(): void
    {
        $this->l1Cache = [];
    }
}
