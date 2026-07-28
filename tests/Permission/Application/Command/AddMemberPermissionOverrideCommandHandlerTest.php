<?php

declare(strict_types=1);

namespace GardenManager\Tests\Permission\Application\Command;

use GardenManager\Permission\Application\Command\AddMemberPermissionOverrideCommand;
use GardenManager\Permission\Application\Command\AddMemberPermissionOverrideCommandHandler;
use GardenManager\Permission\Application\Service\PermissionCacheInvalidatorInterface;
use GardenManager\Permission\Application\Service\PermissionConfigValidator;
use GardenManager\Permission\Application\Service\PermissionRegistryService;
use GardenManager\Permission\Domain\Exception\PermissionException;
use GardenManager\Permission\Domain\PermissionProviderInterface;
use GardenManager\Permission\Domain\Service\PermissionMatcher;
use GardenManager\Permission\Domain\ValueObject\TenantPermissionConfig;
use GardenManager\Plant\Domain\PlantPermissionProvider;
use GardenManager\Seller\Domain\SellerPermissionProvider;
use GardenManager\Tenant\Domain\Entity\Tenant;
use GardenManager\Tenant\Domain\Entity\TenantMembership;
use GardenManager\Tenant\Domain\Exception\TenantException;
use GardenManager\Tenant\Domain\MemberPermissionProvider;
use GardenManager\Tenant\Domain\Persistence\TenantMembershipRepositoryInterface;
use GardenManager\Tenant\Domain\Persistence\TenantRepositoryInterface;
use GardenManager\Tenant\Domain\TenantPermissionProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class AddMemberPermissionOverrideCommandHandlerTest extends TestCase
{
    #[Test]
    public function addsOverrideSuccessfully(): void
    {
        $tenantId = new Ulid();
        $userId = new Ulid();

        $tenant = Tenant::create(name: 'Test', id: $tenantId);
        $tenant->updatePermissionsConfig(TenantPermissionConfig::createEmpty());

        $membership = TenantMembership::create(tenant: $tenant, userId: $userId);

        $tenantRepo = $this->createMock(TenantRepositoryInterface::class);
        $tenantRepo->method('getById')->willReturn($tenant);
        $tenantRepo->expects(self::once())->method('save');

        $membershipRepo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $membershipRepo->method('findByTenantIdAndUserId')->willReturn($membership);

        $handler = new AddMemberPermissionOverrideCommandHandler(
            $tenantRepo,
            $this->createStub(PermissionCacheInvalidatorInterface::class),
            new PermissionConfigValidator(new PermissionRegistryService(self::providers()), new PermissionMatcher()),
            $membershipRepo,
        );

        $handler(new AddMemberPermissionOverrideCommand(
            tenantId: $tenantId,
            userId: $userId,
            prefixedPermission: '-plant.edit',
            actorUserId: new Ulid(),
        ));

        $config = $tenant->getPermissionsConfig();
        $overrides = $config->getUserOverrides((string) $userId);
        self::assertCount(1, $overrides);
        self::assertSame('-plant.edit', $overrides[0]);
    }

    #[Test]
    public function throwsOnInvalidPermission(): void
    {
        $tenantId = new Ulid();
        $userId = new Ulid();

        $tenant = Tenant::create(name: 'Test', id: $tenantId);
        $tenant->updatePermissionsConfig(TenantPermissionConfig::createEmpty());

        $membership = TenantMembership::create(tenant: $tenant, userId: $userId);

        $tenantRepo = $this->createStub(TenantRepositoryInterface::class);
        $tenantRepo->method('getById')->willReturn($tenant);

        $membershipRepo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $membershipRepo->method('findByTenantIdAndUserId')->willReturn($membership);

        $handler = new AddMemberPermissionOverrideCommandHandler(
            $tenantRepo,
            $this->createStub(PermissionCacheInvalidatorInterface::class),
            new PermissionConfigValidator(new PermissionRegistryService(self::providers()), new PermissionMatcher()),
            $membershipRepo,
        );

        $this->expectException(PermissionException::class);

        $handler(new AddMemberPermissionOverrideCommand(
            tenantId: $tenantId,
            userId: $userId,
            prefixedPermission: '+nonexistent.permission',
            actorUserId: new Ulid(),
        ));
    }

    #[Test]
    public function throwsWhenUserNotAMember(): void
    {
        $tenantId = new Ulid();
        $userId = new Ulid();

        $membershipRepo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $membershipRepo->method('findByTenantIdAndUserId')->willReturn(null);

        $handler = new AddMemberPermissionOverrideCommandHandler(
            $this->createStub(TenantRepositoryInterface::class),
            $this->createStub(PermissionCacheInvalidatorInterface::class),
            new PermissionConfigValidator(new PermissionRegistryService(self::providers()), new PermissionMatcher()),
            $membershipRepo,
        );

        $this->expectException(TenantException::class);

        $handler(new AddMemberPermissionOverrideCommand(
            tenantId: $tenantId,
            userId: $userId,
            prefixedPermission: '-plant.edit',
            actorUserId: new Ulid(),
        ));
    }

    /**
     * @return list<PermissionProviderInterface>
     */
    private static function providers(): array
    {
        return [
            new PlantPermissionProvider(),
            new SellerPermissionProvider(),
            new TenantPermissionProvider(),
            new MemberPermissionProvider(),
        ];
    }
}
