<?php

declare(strict_types=1);

namespace GardenManager\Tests\Tenant\Domain\Security;

use GardenManager\Tenant\Domain\Enum\TenantMembershipRole;
use GardenManager\Tenant\Domain\Exception\TenantException;
use GardenManager\Tenant\Domain\Security\TenantAuthorizationChecker;
use GardenManager\Tenant\Domain\Tenant;
use GardenManager\Tenant\Domain\TenantMembership;
use GardenManager\Tenant\Domain\TenantMembershipRepositoryInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class TenantAuthorizationCheckerTest extends TestCase
{
    #[Test]
    public function ownerPassesWithoutException(): void
    {
        $tenantId = new Ulid();
        $actorUserId = new Ulid();

        $tenant = Tenant::create(name: 'Test', id: $tenantId);
        $membership = TenantMembership::create(
            tenant: $tenant,
            userId: $actorUserId,
            role: TenantMembershipRole::OWNER,
        );

        $repo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $repo->method('findByTenantIdAndUserId')->willReturn($membership);

        $checker = new TenantAuthorizationChecker($repo);
        $checker->ensureOwner($tenantId, $actorUserId);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function nonOwnerThrowsException(): void
    {
        $tenantId = new Ulid();
        $actorUserId = new Ulid();

        $tenant = Tenant::create(name: 'Test', id: $tenantId);
        $membership = TenantMembership::create(
            tenant: $tenant,
            userId: $actorUserId,
            role: TenantMembershipRole::MEMBER,
        );

        $repo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $repo->method('findByTenantIdAndUserId')->willReturn($membership);

        $checker = new TenantAuthorizationChecker($repo);

        $this->expectException(TenantException::class);
        $checker->ensureOwner($tenantId, $actorUserId);
    }

    #[Test]
    public function nonMemberThrowsException(): void
    {
        $tenantId = new Ulid();
        $actorUserId = new Ulid();

        $repo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $repo->method('findByTenantIdAndUserId')->willReturn(null);

        $checker = new TenantAuthorizationChecker($repo);

        $this->expectException(TenantException::class);
        $checker->ensureOwner($tenantId, $actorUserId);
    }
}
