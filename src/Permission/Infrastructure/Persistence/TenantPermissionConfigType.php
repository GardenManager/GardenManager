<?php

declare(strict_types=1);

namespace GardenManager\Permission\Infrastructure\Persistence;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use GardenManager\Permission\Domain\ValueObject\TenantPermissionConfig;

final class TenantPermissionConfigType extends Type
{
    public const string NAME = 'tenant_permission_config';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'jsonb';
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): TenantPermissionConfig
    {
        if ($value === null || $value === '') {
            return new TenantPermissionConfig();
        }

        $data = json_decode($value, true, 512, \JSON_THROW_ON_ERROR);

        return TenantPermissionConfig::fromArray($data);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): string
    {
        if ($value === null) {
            return json_encode((new TenantPermissionConfig())->toArray(), \JSON_THROW_ON_ERROR);
        }

        \assert($value instanceof TenantPermissionConfig);

        return json_encode($value->toArray(), \JSON_THROW_ON_ERROR);
    }
}
