<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Domain;

final class TenantPermissions
{
    public const string VIEW = 'tenant.view';
    public const string EDIT = 'tenant.edit';
    public const string EDIT_RAW = 'tenant.edit.raw';
}
