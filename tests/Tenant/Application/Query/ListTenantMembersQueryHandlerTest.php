<?php

declare(strict_types=1);

namespace GardenManager\Tests\Tenant\Application\Query;

use GardenManager\Permission\Domain\ValueObject\PermissionGroupData;
use GardenManager\Permission\Domain\ValueObject\TenantPermissionConfig;
use GardenManager\Tenant\Application\Dto\MemberUserInfoDto;
use GardenManager\Tenant\Application\Port\MemberUserResolverInterface;
use GardenManager\Tenant\Application\Query\ListTenantMembersQuery;
use GardenManager\Tenant\Application\Query\ListTenantMembersQueryHandler;
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
final class ListTenantMembersQueryHandlerTest extends TestCase
{
    #[Test]
    public function returnsMemberViews(): void
    {
        $tenantId = new Ulid();
        $actorUserId = new Ulid();
        $memberId = new Ulid();

        $config = new TenantPermissionConfig(
            groups: [
                'admin' => new PermissionGroupData(
                    name: 'Admin',
                    priority: 100,
                    parents: [],
                    permissions: ['+tenant.edit'],
                ),
                'member' => new PermissionGroupData(
                    name: 'Member',
                    priority: 10,
                    parents: [],
                    permissions: ['+plant.view'],
                ),
            ],
            userAssignments: [
                (string) $actorUserId => ['admin'],
                (string) $memberId => ['member'],
            ],
        );

        $tenant = Tenant::create(name: 'Test Tenant', id: $tenantId);
        $tenant->updatePermissionsConfig($config);

        $actorMembership = TenantMembership::create(
            tenant: $tenant,
            userId: $actorUserId,
            isOwner: true,
        );
        $memberMembership = TenantMembership::create(
            tenant: $tenant,
            userId: $memberId,
        );

        $actorUser = new MemberUserInfoDto($actorUserId, 'owner@example.com', 'Owner');
        $memberUser = new MemberUserInfoDto($memberId, 'member@example.com', 'Member');

        $membershipRepo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $membershipRepo->method('findByTenantIdAndUserId')->willReturn($actorMembership);
        $membershipRepo->method('findByTenantId')->willReturn([$actorMembership, $memberMembership]);

        $resolver = $this->createStub(MemberUserResolverInterface::class);
        $resolver->method('resolveByIds')
            ->willReturn([
                (string) $actorUserId => $actorUser,
                (string) $memberId => $memberUser,
            ]);

        $tenantRepo = $this->createStub(TenantRepositoryInterface::class);
        $tenantRepo->method('getById')->willReturn($tenant);

        $handler = new ListTenantMembersQueryHandler($membershipRepo, $resolver, $tenantRepo);

        $result = $handler(new ListTenantMembersQuery(
            tenantId: $tenantId,
            actorUserId: $actorUserId,
        ));

        self::assertCount(2, $result);
        self::assertSame('owner@example.com', $result[0]->userEmail);
        self::assertTrue($result[0]->isOwner);
        self::assertSame('Admin', $result[0]->groupName);
        self::assertSame('member@example.com', $result[1]->userEmail);
        self::assertFalse($result[1]->isOwner);
        self::assertSame('Member', $result[1]->groupName);
    }

    #[Test]
    public function throwsWhenNotAMember(): void
    {
        $tenantId = new Ulid();
        $actorUserId = new Ulid();

        $membershipRepo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $membershipRepo->method('findByTenantIdAndUserId')->willReturn(null);

        $resolver = $this->createStub(MemberUserResolverInterface::class);
        $tenantRepo = $this->createStub(TenantRepositoryInterface::class);

        $handler = new ListTenantMembersQueryHandler($membershipRepo, $resolver, $tenantRepo);

        $this->expectException(TenantException::class);

        $handler(new ListTenantMembersQuery(
            tenantId: $tenantId,
            actorUserId: $actorUserId,
        ));
    }
}
