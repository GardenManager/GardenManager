<?php

declare(strict_types=1);

namespace GardenManager\Tests\Tenant\Application\Command;

use GardenManager\Tenant\Application\Command\UpdateTenantCommand;
use GardenManager\Tenant\Application\Command\UpdateTenantCommandHandler;
use GardenManager\Tenant\Domain\Enum\TenantMembershipRole;
use GardenManager\Tenant\Domain\Exception\TenantException;
use GardenManager\Tenant\Domain\Security\TenantAuthorizationChecker;
use GardenManager\Tenant\Domain\Tenant;
use GardenManager\Tenant\Domain\TenantMembership;
use GardenManager\Tenant\Domain\TenantMembershipRepositoryInterface;
use GardenManager\Tenant\Domain\TenantRepositoryInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class UpdateTenantHandlerTest extends TestCase
{
    #[Test]
    public function ownerCanUpdateTenant(): void
    {
        $tenantId = new Ulid();
        $actorUserId = new Ulid();

        $tenant = Tenant::create(name: 'Old Name', id: $tenantId);
        $membership = TenantMembership::create(
            tenant: $tenant,
            userId: $actorUserId,
            role: TenantMembershipRole::OWNER,
        );

        $tenantRepo = $this->createMock(TenantRepositoryInterface::class);
        $tenantRepo->method('getById')->with($tenantId)->willReturn($tenant);
        $tenantRepo->expects(self::once())->method('save')->with($tenant);

        $membershipRepo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $membershipRepo->method('findByTenantIdAndUserId')->willReturn($membership);

        $checker = new TenantAuthorizationChecker($membershipRepo);
        $handler = new UpdateTenantCommandHandler($tenantRepo, $checker);

        $handler(new UpdateTenantCommand(
            tenantId: $tenantId,
            name: 'New Name',
            actorUserId: $actorUserId,
        ));

        self::assertSame('New Name', $tenant->getName());
    }

    #[Test]
    public function memberCannotUpdateTenant(): void
    {
        $tenantId = new Ulid();
        $actorUserId = new Ulid();

        $tenant = Tenant::create(name: 'Test', id: $tenantId);
        $membership = TenantMembership::create(
            tenant: $tenant,
            userId: $actorUserId,
            role: TenantMembershipRole::MEMBER,
        );

        $tenantRepo = $this->createStub(TenantRepositoryInterface::class);

        $membershipRepo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $membershipRepo->method('findByTenantIdAndUserId')->willReturn($membership);

        $checker = new TenantAuthorizationChecker($membershipRepo);
        $handler = new UpdateTenantCommandHandler($tenantRepo, $checker);

        $this->expectException(TenantException::class);

        $handler(new UpdateTenantCommand(
            tenantId: $tenantId,
            name: 'New Name',
            actorUserId: $actorUserId,
        ));
    }

    #[Test]
    public function nonMemberCannotUpdateTenant(): void
    {
        $tenantId = new Ulid();
        $actorUserId = new Ulid();

        $tenantRepo = $this->createStub(TenantRepositoryInterface::class);

        $membershipRepo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $membershipRepo->method('findByTenantIdAndUserId')->willReturn(null);

        $checker = new TenantAuthorizationChecker($membershipRepo);
        $handler = new UpdateTenantCommandHandler($tenantRepo, $checker);

        $this->expectException(TenantException::class);

        $handler(new UpdateTenantCommand(
            tenantId: $tenantId,
            name: 'New Name',
            actorUserId: $actorUserId,
        ));
    }
}
