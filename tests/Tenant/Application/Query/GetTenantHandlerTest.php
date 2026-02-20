<?php

declare(strict_types=1);

namespace GardenManager\Tests\Tenant\Application\Query;

use GardenManager\Tenant\Application\Query\GetTenantQuery;
use GardenManager\Tenant\Application\Query\GetTenantQueryHandler;
use GardenManager\Tenant\Domain\Enum\TenantMembershipRole;
use GardenManager\Tenant\Domain\Exception\TenantException;
use GardenManager\Tenant\Domain\Tenant;
use GardenManager\Tenant\Domain\TenantMembership;
use GardenManager\Tenant\Domain\TenantMembershipRepositoryInterface;
use GardenManager\Tenant\Domain\TenantRepositoryInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class GetTenantHandlerTest extends TestCase
{
    #[Test]
    public function returnsTenantDetailViewForMember(): void
    {
        $tenantId = new Ulid();
        $actorUserId = new Ulid();
        $tenant = Tenant::create(name: 'My Tenant', id: $tenantId);
        $membership = TenantMembership::create(
            tenant: $tenant,
            userId: $actorUserId,
            role: TenantMembershipRole::MEMBER,
        );

        $membershipRepo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $membershipRepo->method('findByTenantIdAndUserId')->willReturn($membership);

        $tenantRepo = $this->createStub(TenantRepositoryInterface::class);
        $tenantRepo->method('getById')->willReturn($tenant);

        $handler = new GetTenantQueryHandler($tenantRepo, $membershipRepo);

        $result = $handler(new GetTenantQuery(
            tenantId: $tenantId,
            actorUserId: $actorUserId,
        ));

        self::assertTrue($tenantId->equals($result->id));
        self::assertSame('My Tenant', $result->name);
    }

    #[Test]
    public function throwsWhenNotAMember(): void
    {
        $tenantId = new Ulid();
        $actorUserId = new Ulid();

        $membershipRepo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $membershipRepo->method('findByTenantIdAndUserId')->willReturn(null);

        $tenantRepo = $this->createStub(TenantRepositoryInterface::class);

        $handler = new GetTenantQueryHandler($tenantRepo, $membershipRepo);

        $this->expectException(TenantException::class);

        $handler(new GetTenantQuery(
            tenantId: $tenantId,
            actorUserId: $actorUserId,
        ));
    }
}
