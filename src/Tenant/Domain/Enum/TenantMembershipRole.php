<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Domain\Enum;

enum TenantMembershipRole: string
{
    case OWNER = 'owner';
    case MEMBER = 'member';
    case VIEWER = 'viewer';
}
