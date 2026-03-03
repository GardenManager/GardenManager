<?php

declare(strict_types=1);

namespace GardenManager\Permission\Infrastructure\Cache;

use GardenManager\Permission\Domain\Service\PermissionMatcher;
use GardenManager\Permission\Domain\Service\PermissionResolverInterface;
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
        $cacheKey = 't_' . $tenantId->toString() . '-u_' . $userId->toString();

        if (isset($this->l1Cache[$cacheKey])) {
            return $this->l1Cache[$cacheKey];
        }

        /** @var array<string, bool> $resolved */
        $resolved = $this->cache->get($cacheKey, function (ItemInterface $item) use ($userId, $tenantId): array {
            $item->tag(['perm_all', 'perm_tenant_' . $tenantId->toString()]);

            return $this->innerResolver->resolvePermissions($userId, $tenantId);
        });

        $this->l1Cache[$cacheKey] = $resolved;

        return $resolved;
    }

    public function clearL1Cache(): void
    {
        $this->l1Cache = [];
    }
}
