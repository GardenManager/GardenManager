<?php

declare(strict_types=1);

namespace GardenManager\Tests\Permission\Application\Command;

use GardenManager\Permission\Application\Command\UpdateRawPermissionsCommand;
use GardenManager\Permission\Application\Command\UpdateRawPermissionsCommandHandler;
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
use GardenManager\Tenant\Domain\MemberPermissionProvider;
use GardenManager\Tenant\Domain\Persistence\TenantRepositoryInterface;
use GardenManager\Tenant\Domain\TenantPermissionProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class UpdateRawPermissionsCommandHandlerTest extends TestCase
{
    #[Test]
    public function updatesConfigSuccessfully(): void
    {
        $tenantId = new Ulid();
        $tenant = Tenant::create(name: 'Test', id: $tenantId);
        $tenant->updatePermissionsConfig(TenantPermissionConfig::createEmpty());

        $tenantRepo = $this->createMock(TenantRepositoryInterface::class);
        $tenantRepo->method('getById')->willReturn($tenant);
        $tenantRepo->expects(self::once())->method('save');

        $handler = new UpdateRawPermissionsCommandHandler(
            $tenantRepo,
            $this->createStub(PermissionCacheInvalidatorInterface::class),
            new PermissionConfigValidator(new PermissionRegistryService(self::providers()), new PermissionMatcher()),
        );

        $handler(new UpdateRawPermissionsCommand(
            tenantId: $tenantId,
            actorUserId: new Ulid(),
            configData: [
                'groups' => [
                    'viewer' => [
                        'name' => 'Viewer',
                        'priority' => 0,
                        'parents' => [],
                        'permissions' => ['+plant.view'],
                    ],
                ],
                'userAssignments' => [],
                'userOverrides' => [],
            ],
        ));

        $savedConfig = $tenant->getPermissionsConfig();
        self::assertTrue($savedConfig->hasGroup('viewer'));
    }

    #[Test]
    public function throwsOnInvalidConfig(): void
    {
        $tenantId = new Ulid();
        $tenant = Tenant::create(name: 'Test', id: $tenantId);
        $tenant->updatePermissionsConfig(TenantPermissionConfig::createEmpty());

        $tenantRepo = $this->createStub(TenantRepositoryInterface::class);
        $tenantRepo->method('getById')->willReturn($tenant);

        $handler = new UpdateRawPermissionsCommandHandler(
            $tenantRepo,
            $this->createStub(PermissionCacheInvalidatorInterface::class),
            new PermissionConfigValidator(new PermissionRegistryService(self::providers()), new PermissionMatcher()),
        );

        $this->expectException(PermissionException::class);

        $handler(new UpdateRawPermissionsCommand(
            tenantId: $tenantId,
            actorUserId: new Ulid(),
            configData: [
                'groups' => [
                    'viewer' => [
                        'name' => 'Viewer',
                        'priority' => 0,
                        'parents' => [],
                        'permissions' => ['+nonexistent.permission'],
                    ],
                ],
            ],
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
