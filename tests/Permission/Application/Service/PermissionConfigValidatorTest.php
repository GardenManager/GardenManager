<?php

declare(strict_types=1);

namespace GardenManager\Tests\Permission\Application\Service;

use GardenManager\Permission\Application\Service\PermissionConfigValidator;
use GardenManager\Permission\Application\Service\PermissionRegistryService;
use GardenManager\Permission\Domain\Exception\PermissionException;
use GardenManager\Permission\Domain\Service\PermissionMatcher;
use GardenManager\Permission\Domain\PermissionProviderInterface;
use GardenManager\Permission\Domain\ValueObject\PermissionGroupData;
use GardenManager\Permission\Domain\ValueObject\TenantPermissionConfig;
use GardenManager\Plant\Domain\PlantPermissionProvider;
use GardenManager\Seller\Domain\SellerPermissionProvider;
use GardenManager\Tenant\Domain\MemberPermissionProvider;
use GardenManager\Tenant\Domain\TenantPermissionProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class PermissionConfigValidatorTest extends TestCase
{
    private PermissionConfigValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new PermissionConfigValidator(new PermissionRegistryService(self::providers()), new PermissionMatcher());
    }

    #[Test]
    public function acceptsValidConfig(): void
    {
        $config = new TenantPermissionConfig(
            groups: [
                'viewer' => new PermissionGroupData(name: 'Viewer', priority: 0, parents: [], permissions: ['+plant.view', '+plant.list']),
                'editor' => new PermissionGroupData(name: 'Editor', priority: 50, parents: ['viewer'], permissions: ['+plant.create']),
            ],
            userAssignments: ['user1' => ['editor']],
            userOverrides: ['user1' => ['-plant.create']],
        );

        $errors = $this->validator->collectErrors($config);
        self::assertSame([], $errors);
    }

    #[Test]
    public function rejectsBarePermissionStrings(): void
    {
        $config = new TenantPermissionConfig(
            groups: [
                'viewer' => new PermissionGroupData(name: 'Viewer', priority: 0, parents: [], permissions: ['plant.view']),
            ],
        );

        $errors = $this->validator->collectErrors($config);
        self::assertNotEmpty($errors);
        self::assertStringContainsString('without a "+" or "-" prefix', $errors[0]);
    }

    #[Test]
    public function rejectsUnknownPermissionStrings(): void
    {
        $config = new TenantPermissionConfig(
            groups: [
                'viewer' => new PermissionGroupData(name: 'Viewer', priority: 0, parents: [], permissions: ['+nonexistent.permission']),
            ],
        );

        $errors = $this->validator->collectErrors($config);
        self::assertNotEmpty($errors);
        self::assertStringContainsString('unrecognized permission', $errors[0]);
    }

    #[Test]
    public function rejectsReferencesToNonexistentGroupSlugs(): void
    {
        $config = new TenantPermissionConfig(
            groups: [
                'editor' => new PermissionGroupData(name: 'Editor', priority: 50, parents: ['nonexistent'], permissions: ['+plant.view']),
            ],
        );

        $errors = $this->validator->collectErrors($config);
        self::assertNotEmpty($errors);
        self::assertStringContainsString('nonexistent parent group', $errors[0]);
    }

    #[Test]
    public function rejectsUserAssignmentsToNonexistentGroups(): void
    {
        $config = new TenantPermissionConfig(
            groups: [
                'viewer' => new PermissionGroupData(name: 'Viewer', priority: 0, parents: [], permissions: ['+plant.view']),
            ],
            userAssignments: ['user1' => ['nonexistent']],
        );

        $errors = $this->validator->collectErrors($config);
        self::assertNotEmpty($errors);
        self::assertStringContainsString('nonexistent group', $errors[0]);
    }

    #[Test]
    public function detectsCircularInheritance(): void
    {
        $config = new TenantPermissionConfig(
            groups: [
                'a' => new PermissionGroupData(name: 'A', priority: 0, parents: ['b'], permissions: ['+plant.view']),
                'b' => new PermissionGroupData(name: 'B', priority: 10, parents: ['a'], permissions: ['+plant.edit']),
            ],
        );

        $errors = $this->validator->collectErrors($config);
        self::assertNotEmpty($errors);
        self::assertStringContainsString('Circular inheritance', $errors[0]);
    }

    #[Test]
    public function validateThrowsOnErrors(): void
    {
        $config = new TenantPermissionConfig(
            groups: [
                'viewer' => new PermissionGroupData(name: 'Viewer', priority: 0, parents: [], permissions: ['plant.view']),
            ],
        );

        $this->expectException(PermissionException::class);
        $this->validator->validate($config);
    }

    #[Test]
    public function acceptsWildcardPermissionPatterns(): void
    {
        $config = new TenantPermissionConfig(
            groups: [
                'viewer' => new PermissionGroupData(name: 'Viewer', priority: 0, parents: [], permissions: ['+plant.*']),
            ],
        );

        $errors = $this->validator->collectErrors($config);
        self::assertSame([], $errors);
    }

    #[Test]
    public function rejectsWildcardPatternMatchingNoKnownPermissions(): void
    {
        $config = new TenantPermissionConfig(
            groups: [
                'viewer' => new PermissionGroupData(name: 'Viewer', priority: 0, parents: [], permissions: ['+nonexistent.*']),
            ],
        );

        $errors = $this->validator->collectErrors($config);
        self::assertNotEmpty($errors);
        self::assertStringContainsString('unrecognized permission', $errors[0]);
    }

    #[Test]
    public function acceptsDiamondInheritanceWithoutFalsePositive(): void
    {
        // Diamond: both "editor" and "moderator" inherit from "viewer"
        // "admin" inherits from both — this is valid (not circular)
        $config = new TenantPermissionConfig(
            groups: [
                'viewer' => new PermissionGroupData(name: 'Viewer', priority: 0, parents: [], permissions: ['+plant.view']),
                'editor' => new PermissionGroupData(name: 'Editor', priority: 50, parents: ['viewer'], permissions: ['+plant.edit']),
                'moderator' => new PermissionGroupData(name: 'Moderator', priority: 50, parents: ['viewer'], permissions: ['+plant.delete']),
                'admin' => new PermissionGroupData(name: 'Admin', priority: 100, parents: ['editor', 'moderator'], permissions: ['+tenant.edit']),
            ],
        );

        $errors = $this->validator->collectErrors($config);
        self::assertSame([], $errors);
    }

    #[Test]
    public function acceptsGlobalWildcard(): void
    {
        $config = new TenantPermissionConfig(
            groups: [
                'superadmin' => new PermissionGroupData(name: 'Super Admin', priority: 100, parents: [], permissions: ['+**']),
            ],
        );

        $errors = $this->validator->collectErrors($config);
        self::assertSame([], $errors);
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
