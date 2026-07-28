<?php

declare(strict_types=1);

namespace GardenManager\Tests\Tenant\Application\Command;

use GardenManager\Permission\Application\Service\PermissionCacheInvalidatorInterface;
use GardenManager\Permission\Domain\ValueObject\TenantPermissionConfig;
use GardenManager\Tenant\Application\Command\RemoveMemberCommand;
use GardenManager\Tenant\Application\Command\RemoveMemberCommandHandler;
use GardenManager\Tenant\Domain\Entity\Tenant;
use GardenManager\Tenant\Domain\Entity\TenantMembership;
use GardenManager\Tenant\Domain\Exception\TenantException;
use GardenManager\Tenant\Domain\Persistence\TenantMembershipRepositoryInterface;
use GardenManager\Tenant\Domain\Persistence\TenantRepositoryInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class RemoveMemberCommandHandlerTest extends TestCase
{
    private Ulid $tenantId;
    private Ulid $actorUserId;
    private Tenant $tenant;
    private TenantMembership $actorMembership;

    protected function setUp(): void
    {
        $this->tenantId = new Ulid();
        $this->actorUserId = new Ulid();
        $this->tenant = Tenant::create(name: 'Test Tenant', id: $this->tenantId);
        $this->tenant->updatePermissionsConfig(TenantPermissionConfig::createEmpty());
        $this->actorMembership = TenantMembership::create(
            tenant: $this->tenant,
            userId: $this->actorUserId,
            isOwner: true,
        );
    }

    #[Test]
    public function removesNonOwnerMember(): void
    {
        $memberUserId = new Ulid();
        $memberMembership = TenantMembership::create(
            tenant: $this->tenant,
            userId: $memberUserId,
        );

        $membershipRepo = $this->createMock(TenantMembershipRepositoryInterface::class);
        $membershipRepo->method('findByTenantIdAndUserId')
            ->with($this->tenantId, $memberUserId)
            ->willReturn($memberMembership);
        $membershipRepo->expects(self::once())
            ->method('remove')
            ->with($memberMembership);

        $tenantRepo = $this->createMock(TenantRepositoryInterface::class);
        $tenantRepo->method('getById')->willReturn($this->tenant);
        $tenantRepo->expects(self::once())->method('save');

        $handler = new RemoveMemberCommandHandler(
            $membershipRepo,
            $tenantRepo,
            $this->createStub(PermissionCacheInvalidatorInterface::class),
        );

        $handler(new RemoveMemberCommand(
            tenantId: $this->tenantId,
            memberUserId: $memberUserId,
            actorUserId: $this->actorUserId,
        ));
    }

    #[Test]
    public function throwsWhenMemberNotFound(): void
    {
        $memberUserId = new Ulid();

        $membershipRepo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $membershipRepo->method('findByTenantIdAndUserId')->willReturn(null);
        $tenantRepo = $this->createStub(TenantRepositoryInterface::class);

        $handler = new RemoveMemberCommandHandler(
            $membershipRepo,
            $tenantRepo,
            $this->createStub(PermissionCacheInvalidatorInterface::class),
        );

        $this->expectException(TenantException::class);

        $handler(new RemoveMemberCommand(
            tenantId: $this->tenantId,
            memberUserId: $memberUserId,
            actorUserId: $this->actorUserId,
        ));
    }

    #[Test]
    public function cannotRemoveLastOwner(): void
    {
        $membershipRepo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $membershipRepo->method('findByTenantIdAndUserId')->willReturn($this->actorMembership);
        $membershipRepo->method('findByTenantId')->willReturn([$this->actorMembership]);
        $tenantRepo = $this->createStub(TenantRepositoryInterface::class);

        $handler = new RemoveMemberCommandHandler(
            $membershipRepo,
            $tenantRepo,
            $this->createStub(PermissionCacheInvalidatorInterface::class),
        );

        $this->expectException(TenantException::class);

        $handler(new RemoveMemberCommand(
            tenantId: $this->tenantId,
            memberUserId: $this->actorUserId,
            actorUserId: $this->actorUserId,
        ));
    }

    #[Test]
    public function canRemoveOwnerWhenMultipleOwnersExist(): void
    {
        $secondOwnerId = new Ulid();
        $secondOwnerMembership = TenantMembership::create(
            tenant: $this->tenant,
            userId: $secondOwnerId,
            isOwner: true,
        );

        $membershipRepo = $this->createMock(TenantMembershipRepositoryInterface::class);
        $membershipRepo->method('findByTenantIdAndUserId')
            ->with($this->tenantId, $secondOwnerId)
            ->willReturn($secondOwnerMembership);
        $membershipRepo->method('findByTenantId')
            ->willReturn([$this->actorMembership, $secondOwnerMembership]);
        $membershipRepo->expects(self::once())
            ->method('remove')
            ->with($secondOwnerMembership);

        $tenantRepo = $this->createMock(TenantRepositoryInterface::class);
        $tenantRepo->method('getById')->willReturn($this->tenant);
        $tenantRepo->expects(self::once())->method('save');

        $handler = new RemoveMemberCommandHandler(
            $membershipRepo,
            $tenantRepo,
            $this->createStub(PermissionCacheInvalidatorInterface::class),
        );

        $handler(new RemoveMemberCommand(
            tenantId: $this->tenantId,
            memberUserId: $secondOwnerId,
            actorUserId: $this->actorUserId,
        ));
    }
}
