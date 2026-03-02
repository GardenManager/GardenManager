<?php

declare(strict_types=1);

namespace GardenManager\Permission\Domain;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.permission_provider')]
interface PermissionProviderInterface
{
    public function getCategory(): string;

    /**
     * @return array<string, DefaultGroupsEnum>
     */
    public function getPermissions(): array;
}
