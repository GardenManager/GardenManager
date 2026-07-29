<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Domain;

use GardenManager\Permission\Domain\Enum\DefaultGroupsEnum;
use GardenManager\Permission\Domain\PermissionProviderInterface;

final class CustomAttributePermissionProvider implements PermissionProviderInterface
{
    public function getCategory(): string
    {
        return 'Custom Attributes';
    }

    public function getPermissions(): array
    {
        return [
            CustomAttributePermissions::VIEW => DefaultGroupsEnum::Viewer,
            CustomAttributePermissions::LIST => DefaultGroupsEnum::Viewer,
            CustomAttributePermissions::CREATE => DefaultGroupsEnum::Admin,
            CustomAttributePermissions::EDIT => DefaultGroupsEnum::Admin,
            CustomAttributePermissions::DELETE => DefaultGroupsEnum::Admin,
            CustomAttributePermissions::SET_VALUES => DefaultGroupsEnum::Editor,
        ];
    }
}
