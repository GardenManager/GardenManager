<?php

declare(strict_types=1);

namespace GardenManager\Tests\Permission\Infrastructure\Cache;

use GardenManager\Permission\Domain\Service\PermissionMatcher;
use GardenManager\Permission\Domain\Service\PermissionResolverInterface;
use GardenManager\Permission\Infrastructure\Cache\CachedPermissionResolver;
use GardenManager\Permission\Infrastructure\Cache\PermissionCacheInvalidator;
use GardenManager\Permission\Infrastructure\Cache\PermissionCacheKeyGenerator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Group('unit')]
final class PermissionCacheInvalidatorTest extends TestCase
{
    private PermissionCacheKeyGenerator $keyGenerator;

    protected function setUp(): void
    {
        $this->keyGenerator = new PermissionCacheKeyGenerator();
    }

    private function createCachedResolver(): CachedPermissionResolver
    {
        return new CachedPermissionResolver(
            $this->createStub(PermissionResolverInterface::class),
            $this->createStub(TagAwareCacheInterface::class),
            new PermissionMatcher(),
            $this->keyGenerator,
        );
    }

    #[Test]
    public function invalidateForTenantCallsInvalidateTagsWithTenantTag(): void
    {
        $tenantId = new Ulid();

        $cache = $this->createMock(TagAwareCacheInterface::class);
        $cache->expects(self::once())
            ->method('invalidateTags')
            ->with([$this->keyGenerator->tenantTag($tenantId)]);

        $invalidator = new PermissionCacheInvalidator($cache, $this->createCachedResolver(), $this->keyGenerator);
        $invalidator->invalidateForTenant($tenantId);
    }

    #[Test]
    public function invalidateForUserDeletesSpecificCacheKey(): void
    {
        $tenantId = new Ulid();
        $userId = new Ulid();

        $cache = $this->createMock(TagAwareCacheInterface::class);
        $cache->expects(self::once())
            ->method('delete')
            ->with($this->keyGenerator->forUser($userId, $tenantId));

        $invalidator = new PermissionCacheInvalidator($cache, $this->createCachedResolver(), $this->keyGenerator);
        $invalidator->invalidateForUser($userId, $tenantId);
    }

    #[Test]
    public function invalidateAllCallsInvalidateTagsWithAllTag(): void
    {
        $cache = $this->createMock(TagAwareCacheInterface::class);
        $cache->expects(self::once())
            ->method('invalidateTags')
            ->with([$this->keyGenerator->globalTag()]);

        $invalidator = new PermissionCacheInvalidator($cache, $this->createCachedResolver(), $this->keyGenerator);
        $invalidator->invalidateAll();
    }
}
