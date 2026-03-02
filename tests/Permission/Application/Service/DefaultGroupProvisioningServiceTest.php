<?php

declare(strict_types=1);

namespace GardenManager\Tests\Permission\Application\Service;

use GardenManager\Permission\Application\Service\DefaultGroupProvisioningService;
use GardenManager\Permission\Domain\PermissionProviderInterface;
use GardenManager\Plant\Domain\PlantPermissionProvider;
use GardenManager\Seller\Domain\SellerPermissionProvider;
use GardenManager\Tenant\Domain\MemberPermissionProvider;
use GardenManager\Tenant\Domain\TenantPermissionProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class DefaultGroupProvisioningServiceTest extends TestCase
{
    #[Test]
    public function provisionDefaultGroupsReturnsConfigWithThreeGroups(): void
    {
        $service = new DefaultGroupProvisioningService(self::providers());
        $config = $service->provisionDefaultGroups();

        $groups = $config->getGroups();
        self::assertCount(3, $groups);

        // Viewer group
        $viewer = $groups['viewer'] ?? null;
        self::assertNotNull($viewer);
        self::assertSame('Viewer', $viewer->name);
        self::assertSame(0, $viewer->priority);
        self::assertSame([], $viewer->parents);
        self::assertNotEmpty($viewer->permissions);

        // Editor group
        $editor = $groups['editor'] ?? null;
        self::assertNotNull($editor);
        self::assertSame('Editor', $editor->name);
        self::assertSame(50, $editor->priority);
        self::assertSame(['viewer'], $editor->parents);
        self::assertNotEmpty($editor->permissions);

        // Admin group
        $admin = $groups['admin'] ?? null;
        self::assertNotNull($admin);
        self::assertSame('Admin', $admin->name);
        self::assertSame(100, $admin->priority);
        self::assertSame(['editor'], $admin->parents);
        self::assertNotEmpty($admin->permissions);
    }

    #[Test]
    public function allPermissionsUsePlusPrefix(): void
    {
        $service = new DefaultGroupProvisioningService(self::providers());
        $config = $service->provisionDefaultGroups();

        foreach ($config->getGroups() as $group) {
            foreach ($group->permissions as $permission) {
                self::assertStringStartsWith('+', $permission, "Permission '$permission' should start with '+'");
            }
        }
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
