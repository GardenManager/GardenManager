<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Domain;

use GardenManager\Permission\Domain\Enum\DefaultGroupsEnum;
use GardenManager\Permission\Domain\PermissionProviderInterface;

final class TenantPermissionProvider implements PermissionProviderInterface
{
    public function getCategory(): string
    {
        return 'Tenant';
    }

    public function getPermissions(): array
    {
        return [
            TenantPermissions::VIEW => DefaultGroupsEnum::Viewer,
            TenantPermissions::EDIT => DefaultGroupsEnum::Admin,
            TenantPermissions::EDIT_RAW => DefaultGroupsEnum::Admin,
        ];
    }
}
