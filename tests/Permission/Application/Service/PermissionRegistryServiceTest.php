<?php

declare(strict_types=1);

namespace GardenManager\Tests\Permission\Application\Service;

use GardenManager\Permission\Application\Service\PermissionRegistryService;
use GardenManager\Permission\Domain\PermissionProviderInterface;
use GardenManager\Plant\Domain\PlantPermissionProvider;
use GardenManager\Seller\Domain\SellerPermissionProvider;
use GardenManager\Tenant\Domain\MemberPermissionProvider;
use GardenManager\Tenant\Domain\TenantPermissionProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class PermissionRegistryServiceTest extends TestCase
{
    private PermissionRegistryService $registry;

    protected function setUp(): void
    {
        $this->registry = new PermissionRegistryService(self::providers());
    }

    #[Test]
    public function getAllGroupedReturnsCategorizedPermissions(): void
    {
        $grouped = $this->registry->getAllGrouped();

        self::assertNotEmpty($grouped);
        self::assertArrayHasKey('Plants', $grouped);
        self::assertArrayHasKey('Sellers', $grouped);
        self::assertContains('plant.view', $grouped['Plants']);
        self::assertContains('seller.view', $grouped['Sellers']);
    }

    #[Test]
    public function getAllReturnsFlatList(): void
    {
        $all = $this->registry->getAll();

        self::assertNotEmpty($all);
        self::assertContains('plant.view', $all);
        self::assertContains('seller.view', $all);
        self::assertContains('tenant.edit', $all);
        self::assertContains('member.invite', $all);
    }

    #[Test]
    public function getChoicesReturnsPermissionKeyedMap(): void
    {
        $choices = $this->registry->getChoices();

        self::assertNotEmpty($choices);
        foreach ($choices as $key => $value) {
            self::assertSame($key, $value);
        }
        self::assertArrayHasKey('plant.view', $choices);
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
