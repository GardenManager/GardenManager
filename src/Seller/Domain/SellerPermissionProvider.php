<?php

declare(strict_types=1);

namespace GardenManager\Seller\Domain;

use GardenManager\Permission\Domain\Enum\DefaultGroupsEnum;
use GardenManager\Permission\Domain\PermissionProviderInterface;

final class SellerPermissionProvider implements PermissionProviderInterface
{
    public function getCategory(): string
    {
        return 'Sellers';
    }

    public function getPermissions(): array
    {
        return [
            SellerPermissions::VIEW => DefaultGroupsEnum::Viewer,
            SellerPermissions::LIST => DefaultGroupsEnum::Viewer,
            SellerPermissions::CREATE => DefaultGroupsEnum::Editor,
            SellerPermissions::EDIT => DefaultGroupsEnum::Editor,
            SellerPermissions::DELETE => DefaultGroupsEnum::Editor,
        ];
    }
}
