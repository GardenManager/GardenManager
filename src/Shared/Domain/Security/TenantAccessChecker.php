<?php

declare(strict_types=1);

namespace GardenManager\Shared\Domain\Security;

use GardenManager\Shared\Domain\Exception\TenantAccessException;
use GardenManager\Shared\Domain\TenantScoped;
use Symfony\Component\Uid\Ulid;

final class TenantAccessChecker
{
    public function ensureTenantAccess(TenantScoped $entity, Ulid $tenantId): void
    {
        if (!$entity->getTenantId()->equals($tenantId)) {
            throw TenantAccessException::fromEntityClassNameEntityIdAndTenantId(
                $entity::class,
                $entity->getId(),
                $tenantId,
            );
        }
    }
}
