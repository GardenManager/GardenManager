<?php

declare(strict_types=1);

namespace GardenManager\Tests\Permission\Infrastructure\Cache;

use GardenManager\Permission\Domain\Service\PermissionMatcher;
use GardenManager\Permission\Domain\Service\PermissionResolverInterface;
use GardenManager\Permission\Infrastructure\Cache\CachedPermissionResolver;
use GardenManager\Permission\Infrastructure\Cache\PermissionCacheInvalidator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Group('unit')]
final class PermissionCacheInvalidatorTest extends TestCase
{
    #[Test]
    public function invalidateForTenantCallsInvalidateTagsWithTenantTag(): void
    {
        $tenantId = new Ulid();

        $cache = $this->createMock(TagAwareCacheInterface::class);
        $cache->expects(self::once())
            ->method('invalidateTags')
            ->with(['perm_tenant_' . $tenantId]);

        $cachedResolver = new CachedPermissionResolver(
            $this->createStub(PermissionResolverInterface::class),
            $this->createStub(TagAwareCacheInterface::class),
            new PermissionMatcher(),
        );

        $invalidator = new PermissionCacheInvalidator($cache, $cachedResolver);
        $invalidator->invalidateForTenant($tenantId);
    }

    #[Test]
    public function invalidateAllCallsInvalidateTagsWithAllTag(): void
    {
        $cache = $this->createMock(TagAwareCacheInterface::class);
        $cache->expects(self::once())
            ->method('invalidateTags')
            ->with(['perm_all']);

        $cachedResolver = new CachedPermissionResolver(
            $this->createStub(PermissionResolverInterface::class),
            $this->createStub(TagAwareCacheInterface::class),
            new PermissionMatcher(),
        );

        $invalidator = new PermissionCacheInvalidator($cache, $cachedResolver);
        $invalidator->invalidateAll();
    }
}
