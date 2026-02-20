<?php

declare(strict_types=1);

namespace GardenManager\Tests\Tenant\Application\Command;

use GardenManager\Tenant\Application\Command\RemoveMemberCommand;
use GardenManager\Tenant\Application\Command\RemoveMemberCommandHandler;
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
final class RemoveMemberHandlerTest extends TestCase
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
        $this->actorMembership = TenantMembership::create(
            tenant: $this->tenant,
            userId: $this->actorUserId,
            role: TenantMembershipRole::OWNER,
        );
    }

    private function createCheckerStub(?TenantMembership $actorMembership): TenantAuthorizationChecker
    {
        $repo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $repo->method('findByTenantIdAndUserId')->willReturn($actorMembership);

        return new TenantAuthorizationChecker($repo);
    }

    #[Test]
    public function ownerCanRemoveMember(): void
    {
        $memberUserId = new Ulid();
        $memberMembership = TenantMembership::create(
            tenant: $this->tenant,
            userId: $memberUserId,
            role: TenantMembershipRole::MEMBER,
        );

        $membershipRepo = $this->createMock(TenantMembershipRepositoryInterface::class);
        $membershipRepo->method('findByTenantIdAndUserId')
            ->with($this->tenantId, $memberUserId)
            ->willReturn($memberMembership);
        $membershipRepo->expects(self::once())
            ->method('remove')
            ->with($memberMembership);

        $checker = $this->createCheckerStub($this->actorMembership);
        $handler = new RemoveMemberCommandHandler($membershipRepo, $checker);

        $handler(new RemoveMemberCommand(
            tenantId: $this->tenantId,
            memberUserId: $memberUserId,
            actorUserId: $this->actorUserId,
        ));
    }

    #[Test]
    public function nonOwnerCannotRemoveMember(): void
    {
        $actorMembership = TenantMembership::create(
            tenant: $this->tenant,
            userId: $this->actorUserId,
            role: TenantMembershipRole::MEMBER,
        );

        $membershipRepo = $this->createStub(TenantMembershipRepositoryInterface::class);

        $checker = $this->createCheckerStub($actorMembership);
        $handler = new RemoveMemberCommandHandler($membershipRepo, $checker);

        $this->expectException(TenantException::class);

        $handler(new RemoveMemberCommand(
            tenantId: $this->tenantId,
            memberUserId: new Ulid(),
            actorUserId: $this->actorUserId,
        ));
    }

    #[Test]
    public function throwsWhenMemberNotFound(): void
    {
        $memberUserId = new Ulid();

        $membershipRepo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $membershipRepo->method('findByTenantIdAndUserId')->willReturn(null);

        $checker = $this->createCheckerStub($this->actorMembership);
        $handler = new RemoveMemberCommandHandler($membershipRepo, $checker);

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

        $checker = $this->createCheckerStub($this->actorMembership);
        $handler = new RemoveMemberCommandHandler($membershipRepo, $checker);

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
            role: TenantMembershipRole::OWNER,
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

        $checker = $this->createCheckerStub($this->actorMembership);
        $handler = new RemoveMemberCommandHandler($membershipRepo, $checker);

        $handler(new RemoveMemberCommand(
            tenantId: $this->tenantId,
            memberUserId: $secondOwnerId,
            actorUserId: $this->actorUserId,
        ));
    }
}
