<?php

declare(strict_types=1);

namespace GardenManager\Tests\Tenant\Domain;

use GardenManager\Tenant\Domain\Enum\TenantMembershipRole;
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
            role: TenantMembershipRole::OWNER,
        );

        self::assertInstanceOf(Ulid::class, $membership->getId());
        self::assertSame($tenant, $membership->getTenant());
        self::assertTrue($userId->equals($membership->getUserId()));
        self::assertSame(TenantMembershipRole::OWNER, $membership->getRole());
        self::assertNotNull($membership->getCreatedAt());
    }

    #[Test]
    public function createMemberMembership(): void
    {
        $tenant = Tenant::create(name: 'Test Tenant');
        $userId = new Ulid();

        $membership = TenantMembership::create(
            tenant: $tenant,
            userId: $userId,
            role: TenantMembershipRole::MEMBER,
        );

        self::assertSame(TenantMembershipRole::MEMBER, $membership->getRole());
    }

    #[Test]
    public function createWithExplicitId(): void
    {
        $tenant = Tenant::create(name: 'Test Tenant');
        $id = new Ulid();

        $membership = TenantMembership::create(
            tenant: $tenant,
            userId: new Ulid(),
            role: TenantMembershipRole::MEMBER,
            id: $id,
        );

        self::assertSame($id, $membership->getId());
    }
}
