<?php

declare(strict_types=1);

namespace GardenManager\Tests\Permission\Domain\Service;

use GardenManager\Permission\Application\Service\PermissionResolver;
use GardenManager\Permission\Domain\Service\PermissionMatcher;
use GardenManager\Permission\Domain\ValueObject\PermissionGroupData;
use GardenManager\Permission\Domain\ValueObject\TenantPermissionConfig;
use GardenManager\Tenant\Domain\Tenant;
use GardenManager\Tenant\Domain\TenantMembership;
use GardenManager\Tenant\Domain\TenantMembershipRepositoryInterface;
use GardenManager\Tenant\Domain\TenantRepositoryInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class PermissionResolverTest extends TestCase
{
    private Ulid $userId;
    private Ulid $tenantId;
    private TenantMembershipRepositoryInterface $membershipRepo;

    protected function setUp(): void
    {
        $this->userId = new Ulid();
        $this->tenantId = new Ulid();
        $this->membershipRepo = $this->createStub(TenantMembershipRepositoryInterface::class);
    }

    private function createResolver(TenantPermissionConfig $config): PermissionResolver
    {
        $tenant = Tenant::create(name: 'Test', id: $this->tenantId);
        $tenant->updatePermissionsConfig($config);

        $tenantRepo = $this->createStub(TenantRepositoryInterface::class);
        $tenantRepo->method('getById')->willReturn($tenant);

        return new PermissionResolver($tenantRepo, $this->membershipRepo, new PermissionMatcher());
    }

    #[Test]
    public function ownerBypassesAllPermissionChecks(): void
    {
        $tenant = Tenant::create(name: 'Test', id: $this->tenantId);
        $membership = TenantMembership::create(tenant: $tenant, userId: $this->userId, isOwner: true);
        $this->membershipRepo->method('findByTenantIdAndUserId')->willReturn($membership);

        $resolver = $this->createResolver(TenantPermissionConfig::createEmpty());

        self::assertTrue($resolver->hasPermission($this->userId, $this->tenantId, 'plant.edit'));
        self::assertTrue($resolver->hasPermission($this->userId, $this->tenantId, 'anything.at.all'));
    }

    #[Test]
    public function groupPermissionsAreParsedFromPrefixedStrings(): void
    {
        $this->membershipRepo->method('findByTenantIdAndUserId')->willReturn(
            TenantMembership::create(tenant: Tenant::create(name: 'Test', id: $this->tenantId), userId: $this->userId),
        );

        $config = new TenantPermissionConfig(
            groups: [
                'viewer' => new PermissionGroupData(name: 'Viewer', priority: 0, parents: [], permissions: ['+plant.view', '+plant.list']),
            ],
            userAssignments: [(string) $this->userId => ['viewer']],
        );

        $resolver = $this->createResolver($config);

        self::assertTrue($resolver->hasPermission($this->userId, $this->tenantId, 'plant.view'));
        self::assertTrue($resolver->hasPermission($this->userId, $this->tenantId, 'plant.list'));
        self::assertFalse($resolver->hasPermission($this->userId, $this->tenantId, 'plant.edit'));
    }

    #[Test]
    public function inheritanceIsResolved(): void
    {
        $this->membershipRepo->method('findByTenantIdAndUserId')->willReturn(
            TenantMembership::create(tenant: Tenant::create(name: 'Test', id: $this->tenantId), userId: $this->userId),
        );

        $config = new TenantPermissionConfig(
            groups: [
                'viewer' => new PermissionGroupData(name: 'Viewer', priority: 0, parents: [], permissions: ['+plant.view']),
                'editor' => new PermissionGroupData(name: 'Editor', priority: 50, parents: ['viewer'], permissions: ['+plant.edit']),
            ],
            userAssignments: [(string) $this->userId => ['editor']],
        );

        $resolver = $this->createResolver($config);

        self::assertTrue($resolver->hasPermission($this->userId, $this->tenantId, 'plant.view'));
        self::assertTrue($resolver->hasPermission($this->userId, $this->tenantId, 'plant.edit'));
    }

    #[Test]
    public function childGroupCanRevokeParentPermission(): void
    {
        $this->membershipRepo->method('findByTenantIdAndUserId')->willReturn(
            TenantMembership::create(tenant: Tenant::create(name: 'Test', id: $this->tenantId), userId: $this->userId),
        );

        $config = new TenantPermissionConfig(
            groups: [
                'editor' => new PermissionGroupData(name: 'Editor', priority: 50, parents: [], permissions: ['+plant.view', '+plant.delete']),
                'restricted' => new PermissionGroupData(name: 'Restricted', priority: 60, parents: ['editor'], permissions: ['-plant.delete']),
            ],
            userAssignments: [(string) $this->userId => ['restricted']],
        );

        $resolver = $this->createResolver($config);

        self::assertTrue($resolver->hasPermission($this->userId, $this->tenantId, 'plant.view'));
        self::assertFalse($resolver->hasPermission($this->userId, $this->tenantId, 'plant.delete'));
    }

    #[Test]
    public function higherPriorityOverridesLower(): void
    {
        $this->membershipRepo->method('findByTenantIdAndUserId')->willReturn(
            TenantMembership::create(tenant: Tenant::create(name: 'Test', id: $this->tenantId), userId: $this->userId),
        );

        $config = new TenantPermissionConfig(
            groups: [
                'low' => new PermissionGroupData(name: 'Low', priority: 0, parents: [], permissions: ['+plant.edit']),
                'high' => new PermissionGroupData(name: 'High', priority: 100, parents: [], permissions: ['-plant.edit']),
            ],
            userAssignments: [(string) $this->userId => ['low', 'high']],
        );

        $resolver = $this->createResolver($config);

        self::assertFalse($resolver->hasPermission($this->userId, $this->tenantId, 'plant.edit'));
    }

    #[Test]
    public function userOverrideWinsOverGroupPermission(): void
    {
        $this->membershipRepo->method('findByTenantIdAndUserId')->willReturn(
            TenantMembership::create(tenant: Tenant::create(name: 'Test', id: $this->tenantId), userId: $this->userId),
        );

        $config = new TenantPermissionConfig(
            groups: [
                'editor' => new PermissionGroupData(name: 'Editor', priority: 50, parents: [], permissions: ['+plant.edit', '+plant.delete']),
            ],
            userAssignments: [(string) $this->userId => ['editor']],
            userOverrides: [(string) $this->userId => ['-plant.delete']],
        );

        $resolver = $this->createResolver($config);

        self::assertTrue($resolver->hasPermission($this->userId, $this->tenantId, 'plant.edit'));
        self::assertFalse($resolver->hasPermission($this->userId, $this->tenantId, 'plant.delete'));
    }

    #[Test]
    public function userOverrideCanGrantDeniedPermission(): void
    {
        $this->membershipRepo->method('findByTenantIdAndUserId')->willReturn(
            TenantMembership::create(tenant: Tenant::create(name: 'Test', id: $this->tenantId), userId: $this->userId),
        );

        $config = new TenantPermissionConfig(
            groups: [
                'viewer' => new PermissionGroupData(name: 'Viewer', priority: 0, parents: [], permissions: ['+plant.view']),
            ],
            userAssignments: [(string) $this->userId => ['viewer']],
            userOverrides: [(string) $this->userId => ['+seller.create']],
        );

        $resolver = $this->createResolver($config);

        // seller.create is granted by the user override
        self::assertTrue($resolver->hasPermission($this->userId, $this->tenantId, 'seller.create'));
    }

    #[Test]
    public function denyByDefaultWhenNoMatchingPermission(): void
    {
        $this->membershipRepo->method('findByTenantIdAndUserId')->willReturn(
            TenantMembership::create(tenant: Tenant::create(name: 'Test', id: $this->tenantId), userId: $this->userId),
        );

        $resolver = $this->createResolver(TenantPermissionConfig::createEmpty());

        self::assertFalse($resolver->hasPermission($this->userId, $this->tenantId, 'plant.edit'));
    }

    #[Test]
    public function wildcardGrantMatchesSpecificPermission(): void
    {
        $this->membershipRepo->method('findByTenantIdAndUserId')->willReturn(
            TenantMembership::create(tenant: Tenant::create(name: 'Test', id: $this->tenantId), userId: $this->userId),
        );

        $config = new TenantPermissionConfig(
            groups: [
                'plant-admin' => new PermissionGroupData(name: 'Plant Admin', priority: 50, parents: [], permissions: ['+plant.*']),
            ],
            userAssignments: [(string) $this->userId => ['plant-admin']],
        );

        $resolver = $this->createResolver($config);

        self::assertTrue($resolver->hasPermission($this->userId, $this->tenantId, 'plant.edit'));
        self::assertTrue($resolver->hasPermission($this->userId, $this->tenantId, 'plant.delete'));
        self::assertFalse($resolver->hasPermission($this->userId, $this->tenantId, 'seller.edit'));
    }
}
