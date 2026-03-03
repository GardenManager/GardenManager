<?php

declare(strict_types=1);

namespace GardenManager\Tests\Tenant\Domain;

use GardenManager\Tenant\Domain\Tenant;
use GardenManager\Tenant\Domain\TenantMembership;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class TenantMembershipTest extends TestCase
{
    #[Test]
    public function createOwnerMembership(): void
    {
        $tenant = Tenant::create(name: 'Test Tenant');
        $userId = new Ulid();

        $membership = TenantMembership::create(
            tenant: $tenant,
            userId: $userId,
            isOwner: true,
        );

        self::assertSame($tenant, $membership->getTenant());
        self::assertTrue($userId->equals($membership->getUserId()));
        self::assertTrue($membership->isOwner());
    }

    #[Test]
    public function createRegularMembership(): void
    {
        $tenant = Tenant::create(name: 'Test Tenant');
        $userId = new Ulid();

        $membership = TenantMembership::create(
            tenant: $tenant,
            userId: $userId,
        );

        self::assertFalse($membership->isOwner());
    }

    #[Test]
    public function createWithExplicitId(): void
    {
        $tenant = Tenant::create(name: 'Test Tenant');
        $id = new Ulid();

        $membership = TenantMembership::create(
            tenant: $tenant,
            userId: new Ulid(),
            id: $id,
        );

        self::assertSame($id, $membership->getId());
    }
}
