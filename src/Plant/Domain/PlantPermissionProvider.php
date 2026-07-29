<?php

declare(strict_types=1);

namespace GardenManager\Plant\Domain;

use GardenManager\Permission\Domain\Enum\DefaultGroupsEnum;
use GardenManager\Permission\Domain\PermissionProviderInterface;

final class PlantPermissionProvider implements PermissionProviderInterface
{
    public function getCategory(): string
    {
        return 'Plants';
    }

    public function getPermissions(): array
    {
        return [
            PlantPermissions::VIEW => DefaultGroupsEnum::Viewer,
            PlantPermissions::LIST => DefaultGroupsEnum::Viewer,
            PlantPermissions::CREATE => DefaultGroupsEnum::Editor,
            PlantPermissions::EDIT => DefaultGroupsEnum::Editor,
            PlantPermissions::DELETE => DefaultGroupsEnum::Editor,
        ];
    }
}
