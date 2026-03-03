<?php

declare(strict_types=1);

namespace GardenManager\Permission\Application\Service;

use GardenManager\Permission\Domain\PermissionProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class PermissionRegistryService
{
    /** @var list<PermissionProviderInterface> */
    private array $providers;

    /**
     * @param iterable<int, PermissionProviderInterface> $providers
     */
    public function __construct(
        #[AutowireIterator('app.permission_provider')]
        iterable $providers,
    ) {
        $this->providers = [...$providers];
    }

    /**
     * @return array<string, list<string>>
     */
    public function getAllGrouped(): array
    {
        $grouped = [];

        foreach ($this->providers as $provider) {
            $grouped[$provider->getCategory()] = array_keys($provider->getPermissions());
        }

        return $grouped;
    }

    /**
     * @return list<string>
     */
    public function getAll(): array
    {
        return array_merge(...array_values($this->getAllGrouped()));
    }

    /**
     * @return array<string, string>
     */
    public function getChoices(): array
    {
        $choices = [];
        foreach ($this->getAll() as $permission) {
            $choices[$permission] = $permission;
        }

        return $choices;
    }
}
