<?php

declare(strict_types=1);

namespace GardenManager\Tests\Permission\Application\Command;

use GardenManager\Permission\Application\Command\ChangeMemberGroupsCommand;
use GardenManager\Permission\Application\Command\ChangeMemberGroupsCommandHandler;
use GardenManager\Permission\Application\Service\PermissionCacheInvalidatorInterface;
use GardenManager\Permission\Domain\Exception\PermissionException;
use GardenManager\Permission\Domain\ValueObject\PermissionGroupData;
use GardenManager\Permission\Domain\ValueObject\TenantPermissionConfig;
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
final class ChangeMemberGroupsCommandHandlerTest extends TestCase
{
    private Ulid $tenantId;
    private Ulid $actorUserId;

    protected function setUp(): void
    {
        $this->tenantId = new Ulid();
        $this->actorUserId = new Ulid();
    }

    #[Test]
    public function changesGroupsSuccessfully(): void
    {
        $userId = new Ulid();

        $config = new TenantPermissionConfig(
            groups: [
                'member' => new PermissionGroupData(
                    name: 'Member',
                    priority: 10,
                    parents: [],
                    permissions: ['+plant.view'],
                ),
                'admin' => new PermissionGroupData(
                    name: 'Admin',
                    priority: 100,
                    parents: ['member'],
                    permissions: ['+tenant.edit'],
                ),
            ],
        );

        $tenant = Tenant::create(name: 'Test', id: $this->tenantId);
        $tenant->updatePermissionsConfig($config);

        $membership = TenantMembership::create(tenant: $tenant, userId: $userId);

        $tenantRepo = $this->createMock(TenantRepositoryInterface::class);
        $tenantRepo->method('getById')->willReturn($tenant);
        $tenantRepo->expects(self::once())->method('save');

        $membershipRepo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $membershipRepo->method('findByTenantIdAndUserId')->willReturn($membership);

        $handler = new ChangeMemberGroupsCommandHandler(
            $tenantRepo,
            $this->createStub(PermissionCacheInvalidatorInterface::class),
            $membershipRepo,
        );

        $handler(new ChangeMemberGroupsCommand(
            tenantId: $this->tenantId,
            userId: $userId,
            groupSlugs: ['member', 'admin'],
            actorUserId: $this->actorUserId,
        ));

        $assignments = $tenant->getPermissionsConfig()->getUserAssignments((string) $userId);
        self::assertSame(['member', 'admin'], $assignments);
    }

    #[Test]
    public function throwsWhenGroupNotFound(): void
    {
        $userId = new Ulid();

        $config = TenantPermissionConfig::createEmpty();
        $tenant = Tenant::create(name: 'Test', id: $this->tenantId);
        $tenant->updatePermissionsConfig($config);

        $membership = TenantMembership::create(tenant: $tenant, userId: $userId);

        $tenantRepo = $this->createStub(TenantRepositoryInterface::class);
        $tenantRepo->method('getById')->willReturn($tenant);

        $membershipRepo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $membershipRepo->method('findByTenantIdAndUserId')->willReturn($membership);

        $handler = new ChangeMemberGroupsCommandHandler(
            $tenantRepo,
            $this->createStub(PermissionCacheInvalidatorInterface::class),
            $membershipRepo,
        );

        $this->expectException(PermissionException::class);

        $handler(new ChangeMemberGroupsCommand(
            tenantId: $this->tenantId,
            userId: $userId,
            groupSlugs: ['nonexistent'],
            actorUserId: $this->actorUserId,
        ));
    }

    #[Test]
    public function throwsWhenUserNotAMember(): void
    {
        $userId = new Ulid();

        $membershipRepo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $membershipRepo->method('findByTenantIdAndUserId')->willReturn(null);

        $handler = new ChangeMemberGroupsCommandHandler(
            $this->createStub(TenantRepositoryInterface::class),
            $this->createStub(PermissionCacheInvalidatorInterface::class),
            $membershipRepo,
        );

        $this->expectException(TenantException::class);

        $handler(new ChangeMemberGroupsCommand(
            tenantId: $this->tenantId,
            userId: $userId,
            groupSlugs: ['admin'],
            actorUserId: $this->actorUserId,
        ));
    }
}
