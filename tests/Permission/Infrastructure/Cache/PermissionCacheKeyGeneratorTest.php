<?php

declare(strict_types=1);

namespace GardenManager\Tests\Permission\Infrastructure\Cache;

use GardenManager\Permission\Infrastructure\Cache\PermissionCacheKeyGenerator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class PermissionCacheKeyGeneratorTest extends TestCase
{
    private PermissionCacheKeyGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new PermissionCacheKeyGenerator();
    }

    #[Test]
    public function forUserReturnsCacheKeyWithTenantAndUser(): void
    {
        $userId = new Ulid();
        $tenantId = new Ulid();

        $key = $this->generator->forUser($userId, $tenantId);

        self::assertSame('perm_tenant_' . $tenantId->toString() . '-user_' . $userId->toString(), $key);
    }

    #[Test]
    public function tenantTagReturnsTenantScopedTag(): void
    {
        $tenantId = new Ulid();

        $tag = $this->generator->tenantTag($tenantId);

        self::assertSame('perm_tenant_' . $tenantId->toString(), $tag);
    }

    #[Test]
    public function globalTagReturnsPermAll(): void
    {
        self::assertSame('perm_all', $this->generator->globalTag());
    }
}
