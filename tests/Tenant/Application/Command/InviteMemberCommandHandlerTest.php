<?php

declare(strict_types=1);

namespace GardenManager\Tests\Tenant\Application\Command;

use GardenManager\Permission\Application\Service\PermissionCacheInvalidatorInterface;
use GardenManager\Permission\Domain\ValueObject\PermissionGroupData;
use GardenManager\Permission\Domain\ValueObject\TenantPermissionConfig;
use GardenManager\Tenant\Application\Command\InviteMemberCommand;
use GardenManager\Tenant\Application\Command\InviteMemberCommandHandler;
use GardenManager\Tenant\Application\Dto\MemberUserInfoDto;
use GardenManager\Tenant\Application\Port\MemberUserResolverInterface;
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
final class InviteMemberCommandHandlerTest extends TestCase
{
    private Ulid $tenantId;
    private Ulid $actorUserId;
    private Tenant $tenant;

    protected function setUp(): void
    {
        $this->tenantId = new Ulid();
        $this->actorUserId = new Ulid();
        $this->tenant = Tenant::create(name: 'Test Tenant', id: $this->tenantId);

        $config = new TenantPermissionConfig(
            groups: [
                'member' => new PermissionGroupData(
                    name: 'Member',
                    priority: 10,
                    parents: [],
                    permissions: ['+plant.view'],
                ),
            ],
        );
        $this->tenant->updatePermissionsConfig($config);
    }

    #[Test]
    public function authorizedUserCanInviteMember(): void
    {
        $inviteeId = new Ulid();
        $invitee = new MemberUserInfoDto($inviteeId, 'invitee@example.com', 'Invitee');
        $membershipId = new Ulid();
        $savedMembership = null;

        $membershipRepo = $this->createMock(TenantMembershipRepositoryInterface::class);
        $membershipRepo->method('findByTenantIdAndUserId')
            ->with($this->tenantId, $inviteeId)
            ->willReturn(null);
        $membershipRepo->expects(self::once())
            ->method('save')
            ->willReturnCallback(static function (TenantMembership $m) use (&$savedMembership): void {
                $savedMembership = $m;
            });

        $resolver = $this->createStub(MemberUserResolverInterface::class);
        $resolver->method('resolveByEmail')->willReturn($invitee);

        $tenantRepo = $this->createMock(TenantRepositoryInterface::class);
        $tenantRepo->method('getById')->willReturn($this->tenant);
        $tenantRepo->expects(self::once())->method('save');

        $handler = new InviteMemberCommandHandler(
            $tenantRepo,
            $membershipRepo,
            $resolver,
            $this->createStub(PermissionCacheInvalidatorInterface::class),
        );

        $handler(new InviteMemberCommand(
            membershipId: $membershipId,
            tenantId: $this->tenantId,
            inviteeEmail: 'invitee@example.com',
            groupSlug: 'member',
            actorUserId: $this->actorUserId,
        ));

        self::assertInstanceOf(TenantMembership::class, $savedMembership);
        self::assertTrue($inviteeId->equals($savedMembership->getUserId()));
        self::assertFalse($savedMembership->isOwner());

        // Verify user was assigned to the group in config
        $config = $this->tenant->getPermissionsConfig();
        self::assertSame(['member'], $config->getUserAssignments((string) $inviteeId));
    }

    #[Test]
    public function throwsWhenInviteeNotFound(): void
    {
        $membershipRepo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $tenantRepo = $this->createStub(TenantRepositoryInterface::class);

        $resolver = $this->createStub(MemberUserResolverInterface::class);
        $resolver->method('resolveByEmail')->willReturn(null);

        $handler = new InviteMemberCommandHandler(
            $tenantRepo,
            $membershipRepo,
            $resolver,
            $this->createStub(PermissionCacheInvalidatorInterface::class),
        );

        $this->expectException(TenantException::class);

        $handler(new InviteMemberCommand(
            membershipId: new Ulid(),
            tenantId: $this->tenantId,
            inviteeEmail: 'nonexistent@example.com',
            groupSlug: 'member',
            actorUserId: $this->actorUserId,
        ));
    }

    #[Test]
    public function throwsWhenUserAlreadyMember(): void
    {
        $inviteeId = new Ulid();
        $invitee = new MemberUserInfoDto($inviteeId, 'invitee@example.com', 'Invitee');
        $existingMembership = TenantMembership::create(
            tenant: $this->tenant,
            userId: $inviteeId,
        );

        $membershipRepo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $membershipRepo->method('findByTenantIdAndUserId')->willReturn($existingMembership);

        $resolver = $this->createStub(MemberUserResolverInterface::class);
        $resolver->method('resolveByEmail')->willReturn($invitee);

        $tenantRepo = $this->createStub(TenantRepositoryInterface::class);

        $handler = new InviteMemberCommandHandler(
            $tenantRepo,
            $membershipRepo,
            $resolver,
            $this->createStub(PermissionCacheInvalidatorInterface::class),
        );

        $this->expectException(TenantException::class);

        $handler(new InviteMemberCommand(
            membershipId: new Ulid(),
            tenantId: $this->tenantId,
            inviteeEmail: 'invitee@example.com',
            groupSlug: 'member',
            actorUserId: $this->actorUserId,
        ));
    }
}
