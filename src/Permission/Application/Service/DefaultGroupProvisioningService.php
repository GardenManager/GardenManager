<?php

declare(strict_types=1);

namespace GardenManager\Permission\Application\Service;

use GardenManager\Permission\Domain\DefaultGroupsEnum;
use GardenManager\Permission\Domain\PermissionProviderInterface;
use GardenManager\Permission\Domain\ValueObject\PermissionEntryParser;
use GardenManager\Permission\Domain\ValueObject\PermissionGroupData;
use GardenManager\Permission\Domain\ValueObject\TenantPermissionConfig;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class DefaultGroupProvisioningService
{
    /** @var list<PermissionProviderInterface> */
    private array $providers;

    /**
     * @param iterable<PermissionProviderInterface> $providers
     */
    public function __construct(
        #[AutowireIterator('app.permission_provider')]
        iterable $providers,
    ) {
        $this->providers = [...$providers];
    }

    public function provisionDefaultGroups(): TenantPermissionConfig
    {
        $config = TenantPermissionConfig::createEmpty();
        $defaultGroupPermissions = $this->collectAllPermissionForDefaultGroups();

        $cases = DefaultGroupsEnum::cases();
        usort($cases, static fn (DefaultGroupsEnum $a, DefaultGroupsEnum $b): int => $a->getPriority() <=> $b->getPriority());

        $previousSlug = null;

        foreach ($cases as $defaultGroup) {
            $slug = $defaultGroup->getSlug();
            $parents = $previousSlug !== null ? [$previousSlug] : [];

            $permissions = array_map(
                static fn (string $perm): string => PermissionEntryParser::format($perm, true),
                $defaultGroupPermissions[$defaultGroup->value] ?? [],
            );

            $config = $config->withGroup($slug, new PermissionGroupData(
                name: $defaultGroup->value,
                priority: $defaultGroup->getPriority(),
                parents: $parents,
                permissions: $permissions,
            ));

            $previousSlug = $slug;
        }

        return $config;
    }

    /**
     * @return array<string, list<string>>
     */
    private function collectAllPermissionForDefaultGroups(): array
    {
        $defaultGroups = [];

        foreach ($this->providers as $provider) {
            foreach ($provider->getPermissions() as $permission => $defaultGroup) {
                $defaultGroups[$defaultGroup->value][] = $permission;
            }
        }

        return $defaultGroups;
    }
}
