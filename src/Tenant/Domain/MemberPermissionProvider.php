<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Domain;

use GardenManager\Permission\Domain\DefaultGroupsEnum;
use GardenManager\Permission\Domain\PermissionProviderInterface;

final class MemberPermissionProvider implements PermissionProviderInterface
{
    public function getCategory(): string
    {
        return 'Members';
    }

    public function getPermissions(): array
    {
        return [
            MemberPermissions::LIST => DefaultGroupsEnum::Viewer,
            MemberPermissions::INVITE => DefaultGroupsEnum::Admin,
            MemberPermissions::REMOVE => DefaultGroupsEnum::Admin,
        ];
    }
}
