<?php

declare(strict_types=1);

namespace GardenManager\Tests\Permission\Application\Query;

use GardenManager\Permission\Application\Query\GetMemberPermissionDetailQuery;
use GardenManager\Permission\Application\Query\GetMemberPermissionDetailQueryHandler;
use GardenManager\Permission\Domain\ValueObject\PermissionGroupData;
use GardenManager\Permission\Domain\ValueObject\TenantPermissionConfig;
use GardenManager\Tenant\Application\Dto\MemberUserInfoDto;
use GardenManager\Tenant\Application\Port\MemberUserResolverInterface;
use GardenManager\Tenant\Domain\Tenant;
use GardenManager\Tenant\Domain\TenantMembership;
use GardenManager\Tenant\Domain\TenantMembershipRepositoryInterface;
use GardenManager\Tenant\Domain\TenantRepositoryInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class GetMemberPermissionDetailHandlerTest extends TestCase
{
    #[Test]
    public function returnsMemberWithOverrides(): void
    {
        $tenantId = new Ulid();
        $userId = new Ulid();
        $actorUserId = new Ulid();

        $config = new TenantPermissionConfig(
            groups: [
                'admin' => new PermissionGroupData(
                    name: 'Admin',
                    priority: 100,
                    parents: [],
                    permissions: ['+tenant.edit'],
                ),
            ],
            userAssignments: [(string) $userId => ['admin']],
            userOverrides: [(string) $userId => ['-plant.delete']],
        );

        $tenant = Tenant::create(name: 'Test Tenant', id: $tenantId);
        $tenant->updatePermissionsConfig($config);

        $membership = TenantMembership::create(
            tenant: $tenant,
            userId: $userId,
        );

        $userInfo = new MemberUserInfoDto($userId, 'user@example.com', 'Test User');

        $membershipRepo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $membershipRepo->method('findByTenantIdAndUserId')->willReturn($membership);

        $memberResolver = $this->createStub(MemberUserResolverInterface::class);
        $memberResolver->method('resolveByIds')->willReturn([(string) $userId => $userInfo]);

        $tenantRepo = $this->createStub(TenantRepositoryInterface::class);
        $tenantRepo->method('getById')->willReturn($tenant);

        $handler = new GetMemberPermissionDetailQueryHandler(
            $membershipRepo,
            $memberResolver,
            $tenantRepo,
        );

        $result = $handler(new GetMemberPermissionDetailQuery(
            userId: $userId,
            tenantId: $tenantId,
            actorUserId: $actorUserId,
        ));

        self::assertSame('user@example.com', $result->userEmail);
        self::assertSame('Test User', $result->userDisplayName);
        self::assertFalse($result->isOwner);
        self::assertSame(['admin'], $result->groupSlugs);
        self::assertCount(1, $result->overrides);
        self::assertSame('plant.delete', $result->overrides[0]->permission);
        self::assertFalse($result->overrides[0]->granted);
    }
}
