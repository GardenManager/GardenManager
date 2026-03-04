<?php

declare(strict_types=1);

namespace GardenManager\Tests\Permission\Infrastructure\Cache;

use GardenManager\Permission\Domain\Service\PermissionMatcher;
use GardenManager\Permission\Domain\Service\PermissionResolverInterface;
use GardenManager\Permission\Infrastructure\Cache\CachedPermissionResolver;
use GardenManager\Permission\Infrastructure\Cache\PermissionCacheKeyGenerator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class CachedPermissionResolverTest extends TestCase
{
    private Ulid $userId;
    private Ulid $tenantId;
    private TagAwareAdapter $cache;
    private PermissionCacheKeyGenerator $keyGenerator;

    protected function setUp(): void
    {
        $this->userId = new Ulid();
        $this->tenantId = new Ulid();
        $this->cache = new TagAwareAdapter(new ArrayAdapter());
        $this->keyGenerator = new PermissionCacheKeyGenerator();
    }

    #[Test]
    public function cacheMissDelegatesToInnerResolver(): void
    {
        $permissions = ['plant.view' => true, 'plant.edit' => false];

        $inner = $this->createMock(PermissionResolverInterface::class);
        $inner->expects(self::once())
            ->method('resolvePermissions')
            ->with($this->userId, $this->tenantId)
            ->willReturn($permissions);

        $resolver = new CachedPermissionResolver($inner, $this->cache, new PermissionMatcher(), $this->keyGenerator);

        $result = $resolver->resolvePermissions($this->userId, $this->tenantId);

        self::assertSame($permissions, $result);
    }

    #[Test]
    public function l1CachePreventsRepeatedCallsToInner(): void
    {
        $permissions = ['plant.view' => true];

        $inner = $this->createMock(PermissionResolverInterface::class);
        $inner->expects(self::once())
            ->method('resolvePermissions')
            ->willReturn($permissions);

        $resolver = new CachedPermissionResolver($inner, $this->cache, new PermissionMatcher(), $this->keyGenerator);

        $resolver->resolvePermissions($this->userId, $this->tenantId);
        $result = $resolver->resolvePermissions($this->userId, $this->tenantId);

        self::assertSame($permissions, $result);
    }

    #[Test]
    public function l2CachePreventsCallsToInnerOnNewInstance(): void
    {
        $permissions = ['plant.view' => true, 'seller.list' => true];

        $inner = $this->createMock(PermissionResolverInterface::class);
        $inner->expects(self::once())
            ->method('resolvePermissions')
            ->willReturn($permissions);

        // First instance populates L2
        $resolver1 = new CachedPermissionResolver($inner, $this->cache, new PermissionMatcher(), $this->keyGenerator);
        $resolver1->resolvePermissions($this->userId, $this->tenantId);

        // Second instance (simulates new request) should read from L2, not inner
        $resolver2 = new CachedPermissionResolver($inner, $this->cache, new PermissionMatcher(), $this->keyGenerator);
        $result = $resolver2->resolvePermissions($this->userId, $this->tenantId);

        self::assertSame($permissions, $result);
    }

    #[Test]
    public function hasPermissionWorksWithCachedData(): void
    {
        $permissions = ['plant.view' => true, 'plant.edit' => false, 'seller.*' => true];

        $inner = $this->createStub(PermissionResolverInterface::class);
        $inner->method('resolvePermissions')->willReturn($permissions);

        $resolver = new CachedPermissionResolver($inner, $this->cache, new PermissionMatcher(), $this->keyGenerator);

        self::assertTrue($resolver->hasPermission($this->userId, $this->tenantId, 'plant.view'));
        self::assertFalse($resolver->hasPermission($this->userId, $this->tenantId, 'plant.edit'));
        self::assertTrue($resolver->hasPermission($this->userId, $this->tenantId, 'seller.list'));
        self::assertFalse($resolver->hasPermission($this->userId, $this->tenantId, 'unknown.perm'));
    }

    #[Test]
    public function tagInvalidationEvictsCachedEntry(): void
    {
        $permissions1 = ['plant.view' => true];
        $permissions2 = ['plant.view' => true, 'plant.edit' => true];

        $callCount = 0;
        $inner = $this->createMock(PermissionResolverInterface::class);
        $inner->expects(self::exactly(2))
            ->method('resolvePermissions')
            ->willReturnCallback(static function () use (&$callCount, $permissions1, $permissions2): array {
                ++$callCount;

                return $callCount === 1 ? $permissions1 : $permissions2;
            });

        $resolver = new CachedPermissionResolver($inner, $this->cache, new PermissionMatcher(), $this->keyGenerator);

        // First call populates cache
        $result1 = $resolver->resolvePermissions($this->userId, $this->tenantId);
        self::assertSame($permissions1, $result1);

        // Invalidate tenant tag
        $this->cache->invalidateTags([$this->keyGenerator->tenantTag($this->tenantId)]);

        // New instance (simulates new request after invalidation)
        $resolver2 = new CachedPermissionResolver($inner, $this->cache, new PermissionMatcher(), $this->keyGenerator);
        $result2 = $resolver2->resolvePermissions($this->userId, $this->tenantId);
        self::assertSame($permissions2, $result2);
    }
}
