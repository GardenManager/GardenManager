<?php

declare(strict_types=1);

namespace GardenManager\Tests\Tenant\Application\Query;

use GardenManager\Tenant\Application\Query\ListUserTenantsQuery;
use GardenManager\Tenant\Application\Query\ListUserTenantsQueryHandler;
use GardenManager\Tenant\Application\View\UserTenantView;
use GardenManager\Tenant\Domain\Tenant;
use GardenManager\Tenant\Domain\TenantMembership;
use GardenManager\Tenant\Domain\TenantMembershipRepositoryInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class ListUserTenantsHandlerTest extends TestCase
{
    #[Test]
    public function returnsUserTenantViews(): void
    {
        $userId = new Ulid();
        $tenant1 = Tenant::create(name: 'Tenant One');
        $tenant2 = Tenant::create(name: 'Tenant Two');
        $membership1 = TenantMembership::create(tenant: $tenant1, userId: $userId, isOwner: true);
        $membership2 = TenantMembership::create(tenant: $tenant2, userId: $userId);

        $repo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $repo->method('findByUserId')->willReturn([$membership1, $membership2]);

        $handler = new ListUserTenantsQueryHandler($repo);
        $result = $handler(new ListUserTenantsQuery($userId));

        self::assertCount(2, $result);
        self::assertContainsOnlyInstancesOf(UserTenantView::class, $result);
        self::assertSame('Tenant One', $result[0]->tenantName);
        self::assertTrue($result[0]->isOwner);
        self::assertSame('Tenant Two', $result[1]->tenantName);
        self::assertFalse($result[1]->isOwner);
    }

    #[Test]
    public function returnsEmptyListWhenNoMemberships(): void
    {
        $userId = new Ulid();

        $repo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $repo->method('findByUserId')->willReturn([]);

        $handler = new ListUserTenantsQueryHandler($repo);
        $result = $handler(new ListUserTenantsQuery($userId));

        self::assertSame([], $result);
    }
}
